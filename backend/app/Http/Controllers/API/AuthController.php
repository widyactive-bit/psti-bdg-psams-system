<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Athlete;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Info(
 *     title="PSAMS REST API Documentation",
 *     version="1.0.0",
 *     description="Dokumentasi API untuk PSTI Sport Analytics & Management System (PSAMS) Kota Bandung"
 * )
 * @OA\Server(
 *     url="/api",
 *     description="API Base URL"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/auth/register",
     *     summary="Registrasi Pengguna & Atlet Baru",
     *     tags={"Autentikasi"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name","email","password","alamat","no_hp","ktp","kk"},
     *                 @OA\Property(property="name", type="string", description="Nama Lengkap", example="Muhammad Rafli"),
     *                 @OA\Property(property="email", type="string", format="email", description="Email Login", example="rafli@psti.bandung.go.id"),
     *                 @OA\Property(property="password", type="string", format="password", description="Kata Sandi", example="password123"),
     *                 @OA\Property(property="alamat", type="string", description="Alamat Lengkap", example="Jl. Lombok No. 12, Bandung"),
     *                 @OA\Property(property="no_hp", type="string", description="No. Handphone", example="081122334455"),
     *                 @OA\Property(property="ktp", type="string", format="binary", description="Scan KTP"),
     *                 @OA\Property(property="kk", type="string", format="binary", description="Scan Kartu Keluarga"),
     *                 @OA\Property(property="sertifikat[]", type="array", @OA\Items(type="string", format="binary"), description="Sertifikat Medali/Penghargaan (Opsional)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Registrasi Berhasil",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Registrasi berhasil. Skuad menunggu persetujuan admin."),
     *             @OA\Property(property="token", type="string", example="1|token_hash_here"),
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="athlete", type="object")
     *         )
     *     )
     * )
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'alamat' => 'required|string',
            'no_hp' => 'required|string',
            'ktp' => 'required|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'kk' => 'required|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'pas_foto' => 'required|file|mimes:jpeg,png,jpg,webp|max:2048',
            'sertifikat.*' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        // Handle file uploads
        $ktpPath = null;
        if ($request->hasFile('ktp')) {
            $ktpPath = $request->file('ktp')->store('registrasi/ktp', 'public');
        }

        $kkPath = null;
        if ($request->hasFile('kk')) {
            $kkPath = $request->file('kk')->store('registrasi/kk', 'public');
        }

        $pasFotoPath = null;
        if ($request->hasFile('pas_foto')) {
            $pasFotoPath = $request->file('pas_foto')->store('registrasi/pas_foto', 'public');
        }

        $sertifikatPaths = [];
        if ($request->hasFile('sertifikat')) {
            $files = $request->file('sertifikat');
            if (is_array($files)) {
                foreach ($files as $file) {
                    $sertifikatPaths[] = $file->store('registrasi/sertifikat', 'public');
                }
            } else {
                $sertifikatPaths[] = $files->store('registrasi/sertifikat', 'public');
            }
        }

        // Create User
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'Atlet', // Default registered role
            'no_hp' => $request->no_hp,
            'alamat' => $request->alamat,
            'ktp' => $ktpPath,
            'kk' => $kkPath,
            'sertifikat' => $sertifikatPaths,
            'foto' => $pasFotoPath,
            'status' => 'Nonaktif',
        ]);

        // Create Athlete
        $athlete = Athlete::create([
            'nama_lengkap' => $request->name,
            'email' => $request->email,
            'alamat' => $request->alamat,
            'no_hp' => $request->no_hp,
            'ktp' => $ktpPath,
            'kk' => $kkPath,
            'sertifikat' => $sertifikatPaths,
            'foto' => $pasFotoPath,
            'status' => 'Nonaktif', // Require administrative approval
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registrasi berhasil. Profil atlet telah dibuat dalam status Nonaktif menunggu persetujuan admin.',
            'token' => $token,
            'user' => $user,
            'athlete' => $athlete
        ], 201);
    }

    /**
     * @OA\Post(
      *     path="/auth/login",
      *     summary="Masuk ke Sistem (Mendapatkan Token Sanctum)",
      *     tags={"Autentikasi"},
      *     @OA\RequestBody(
      *         required=true,
      *         @OA\JsonContent(
      *             required={"email","password"},
      *             @OA\Property(property="email", type="string", format="email", example="atlet@psti.bandung.go.id"),
      *             @OA\Property(property="password", type="string", format="password", example="password123")
      *         )
      *     ),
      *     @OA\Response(
      *         response=200,
      *         description="Login Berhasil",
      *         @OA\JsonContent(
      *             @OA\Property(property="token", type="string", example="1|token_hash_here"),
      *             @OA\Property(property="user", type="object")
      *         )
      *     ),
      *     @OA\Response(
      *         response=422,
      *         description="Validasi Gagal / Password Salah"
      *     )
      * )
      */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial yang diberikan tidak cocok.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user
        ]);
    }

    /**
     * @OA\Post(
      *     path="/auth/logout",
      *     summary="Keluar dari Sistem (Menghapus Token)",
      *     tags={"Autentikasi"},
      *     security={{"bearerAuth":{}}},
      *     @OA\Response(
      *         response=200,
      *         description="Logout Berhasil",
      *         @OA\JsonContent(
      *             @OA\Property(property="message", type="string", example="Logged out successfully")
      *         )
      *     )
      * )
      */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * @OA\Get(
      *     path="/auth/profile",
      *     summary="Mendapatkan Informasi Profil Pengguna Aktif",
      *     tags={"Autentikasi"},
      *     security={{"bearerAuth":{}}},
      *     @OA\Response(
      *         response=200,
      *         description="Profil Pengguna",
      *         @OA\JsonContent(
      *             @OA\Property(property="id", type="integer", example=1),
      *             @OA\Property(property="name", type="string", example="Muhammad Rafli"),
      *             @OA\Property(property="email", type="string", example="atlet@psti.bandung.go.id"),
      *             @OA\Property(property="role", type="string", example="Atlet")
      *         )
      *     )
      * )
      */
    public function profile(Request $request)
    {
        return response()->json($request->user());
    }
}
