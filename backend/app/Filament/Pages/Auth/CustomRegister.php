<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Register as BaseRegister;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Model;
use App\Models\Athlete;
use App\Models\Coach;
use App\Models\Referee;
use App\Models\Board;
use App\Models\Club;

class CustomRegister extends BaseRegister
{
    protected function getForms(): array
    {
        return [
            'form' => $this->makeForm()
                ->schema([
                    $this->getNameFormComponent(),
                    $this->getEmailFormComponent(),
                    $this->getPasswordFormComponent(),
                    $this->getPasswordConfirmationFormComponent(),
                    
                    Select::make('role')
                        ->label('Kategori Pendaftaran')
                        ->options([
                            'Admin' => 'Admin (Staf Operasional)',
                            'Pengurus' => 'Pengurus PSTI',
                            'Pelatih' => 'Pelatih',
                            'Atlet' => 'Atlet',
                            'Wasit' => 'Wasit',
                            'Klub' => 'Klub (Perwakilan Klub)',
                        ])
                        ->default('Atlet')
                        ->required()
                        ->live(),
                        
                    TextInput::make('no_hp')
                        ->label('No. Handphone')
                        ->tel()
                        ->required(),
                        
                    Textarea::make('alamat')
                        ->label('Alamat Lengkap')
                        ->required(),
                        
                    FileUpload::make('ktp')
                        ->label('Upload KTP')
                        ->directory('registrasi')
                        ->required()
                        ->image()
                        ->openable()
                        ->downloadable()
                        ->maxSize(4096)
                        ->acceptedFileTypes(['image/jpeg','image/png','image/webp']),
                        
                    FileUpload::make('kk')
                        ->label('Upload Kartu Keluarga (KK)')
                        ->directory('registrasi')
                        ->required()
                        ->image()
                        ->openable()
                        ->downloadable()
                        ->maxSize(4096)
                        ->acceptedFileTypes(['image/jpeg','image/png','image/webp']),
                        
                    FileUpload::make('sertifikat')
                        ->label('Sertifikat Pendukung (Optional)')
                        ->directory('registrasi')
                        ->multiple()
                        ->nullable()
                        ->openable()
                        ->downloadable()
                        ->maxSize(5120)
                        ->acceptedFileTypes(['image/jpeg','image/png','image/webp','application/pdf']),
                    FileUpload::make('pas_foto')
                        ->label('Upload Pas Foto')
                        ->directory('registrasi/pas_foto')
                        ->image()
                        ->required()
                        ->openable()
                        ->downloadable()
                        ->maxSize(2048)
                        ->acceptedFileTypes(['image/jpeg','image/png','image/webp']),
                ])
                ->statePath('data'),
        ];
    }

    protected function handleRegistration(array $data): Model
    {
        // 1. Create the Auth User with status Nonaktif
        $user = $this->getUserModel()::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'],
            'no_hp' => $data['no_hp'],
            'alamat' => $data['alamat'],
            'ktp' => $data['ktp'],
            'kk' => $data['kk'],
            'sertifikat' => $data['sertifikat'] ?? null,
            'foto' => $data['pas_foto'] ?? null,
            'status' => 'Aktif', // Sementara Aktif agar bisa langsung login
        ]);

        // 2. Synchronize to profile tables based on the selected role
        switch ($data['role']) {
            case 'Atlet':
                Athlete::create([
                    'nama_lengkap' => $data['name'],
                    'email' => $data['email'],
                    'no_hp' => $data['no_hp'],
                    'alamat' => $data['alamat'],
                    'ktp' => $data['ktp'],
                    'kk' => $data['kk'],
                        'sertifikat' => $data['sertifikat'] ?? null,
                        'foto' => $data['pas_foto'] ?? null,
                    'status' => 'Nonaktif',
                ]);
                break;
                
            case 'Pelatih':
                Coach::create([
                    'nama' => $data['name'],
                    'email' => $data['email'],
                    'nomor_hp' => $data['no_hp'],
                    'klub' => 'Belum Ditentukan',
                    'lisensi' => 'Dalam Proses Verifikasi',
                    'masa_berlaku_lisensi' => now()->addYear()->toDateString(),
                ]);
                break;
                
            case 'Wasit':
                Referee::create([
                    'nama' => $data['name'],
                    'lisensi' => 'Dalam Proses Verifikasi',
                    'level' => 'Nasional C',
                    'masa_berlaku' => now()->addYear()->toDateString(),
                ]);
                break;
                
            case 'Pengurus':
                Board::create([
                    'nama' => $data['name'],
                    'jabatan' => 'Anggota (Verifikasi)',
                    'periode' => now()->year . ' - ' . (now()->year + 4),
                ]);
                break;
                
            case 'Klub':
                Club::create([
                    'nama_klub' => $data['name'] . ' Club',
                    'alamat' => $data['alamat'],
                    'pelatih' => 'Belum Ditentukan',
                    'jumlah_atlet' => 0,
                ]);
                break;
        }

        return $user;
    }

    public function register(): ?\Filament\Http\Responses\Auth\Contracts\RegistrationResponse
    {
        try {
            $this->rateLimit(2);
        } catch (\DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $user = $this->wrapInDatabaseTransaction(function (): Model {
            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeRegister($data);

            $this->callHook('beforeRegister');

            $user = $this->handleRegistration($data);

            $this->form->model($user)->saveRelationships();

            $this->callHook('afterRegister');

            return $user;
        });

        event(new \Illuminate\Auth\Events\Registered($user));

        $this->sendEmailVerificationNotification($user);

        // Jangan auto-login karena statusnya masih Nonaktif
        // Filament::auth()->login($user);
        // session()->regenerate();

        \Filament\Notifications\Notification::make()
            ->title('Pendaftaran Berhasil')
            ->body('Akun Anda berhasil didaftarkan. Silakan tunggu persetujuan dari Admin sebelum bisa login.')
            ->success()
            ->send();

        $this->redirect(filament()->getLoginUrl());

        return null;
    }
}
