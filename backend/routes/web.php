<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin'); // Redirect to Filament Admin
});

Route::get('/run-migration-temp', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
        return "Migration and seeding completed successfully!";
    } catch (\Throwable $e) {
        return "Error: " . $e->getMessage() . "\n" . $e->getTraceAsString();
    }
});
