<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin'); // Redirect to Filament Admin
});

if (env('VERCEL')) {
    Route::get('/storage/{path}', function ($path) {
        $basePath = storage_path('app/public');
        $filePath = realpath($basePath . '/' . $path);
        
        if (!$filePath || !str_starts_with($filePath, realpath($basePath))) {
            abort(404);
        }
        
        if (!file_exists($filePath)) {
            abort(404);
        }
        
        return response()->file($filePath);
    })->where('path', '.*');
}

