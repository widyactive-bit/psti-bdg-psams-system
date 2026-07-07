<?php

namespace App\Filament\Resources;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\UserResource\Pages;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Manajemen Pengguna';

    protected static ?string $pluralLabel = 'Pengguna';

    protected static ?string $navigationGroup = 'Sistem';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Kredensial & Peran')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->label('Nama Pengguna'),
                        Forms\Components\TextInput::make('email')
                            ->required()
                            ->email()
                            ->disabled()
                            ->label('Email'),
                        Forms\Components\Select::make('role')
                            ->options([
                                'Super Admin' => 'Super Admin',
                                'Admin' => 'Admin (Staf Operasional)',
                                'Pengurus' => 'Pengurus PSTI',
                                'Pelatih' => 'Pelatih',
                                'Atlet' => 'Atlet',
                                'Wasit' => 'Wasit',
                                'Klub' => 'Klub (Perwakilan Klub)',
                            ])
                            ->required()
                            ->label('Peran / Kategori'),
                        Forms\Components\Select::make('status')
                            ->options([
                                'Aktif' => 'Aktif (Disetujui)',
                                'Nonaktif' => 'Nonaktif (Ditangguhkan)',
                            ])
                            ->required()
                            ->label('Status Akun'),
                    ])->columns(2),

                Forms\Components\Section::make('Informasi Kontak & Dokumen Registrasi')
                    ->schema([
                        Forms\Components\FileUpload::make('foto')
                            ->image()
                            ->label('Pas Foto')
                            ->openable()
                            ->downloadable(),
                        Forms\Components\TextInput::make('no_hp')
                            ->disabled()
                            ->label('No. Handphone'),
                        Forms\Components\Textarea::make('alamat')
                            ->disabled()
                            ->columnSpanFull()
                            ->label('Alamat Lengkap'),
                        Forms\Components\FileUpload::make('ktp')
                            ->disabled()
                            ->image()
                            ->openable()
                            ->downloadable()
                            ->label('Dokumen KTP'),
                        Forms\Components\FileUpload::make('kk')
                            ->disabled()
                            ->image()
                            ->openable()
                            ->downloadable()
                            ->label('Dokumen Kartu Keluarga (KK)'),
                        Forms\Components\FileUpload::make('sertifikat')
                            ->disabled()
                            ->multiple()
                            ->openable()
                            ->downloadable()
                            ->label('Sertifikat Pendukung'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Nama'),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->label('Email'),
                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Super Admin' => 'danger',
                        'Admin' => 'warning',
                        'Pengurus' => 'info',
                        'Pelatih' => 'success',
                        'Wasit' => 'primary',
                        'Klub' => 'gray',
                        default => 'gray',
                    })
                    ->label('Kategori'),
                Tables\Columns\SelectColumn::make('status')
                    ->options([
                        'Aktif' => 'Aktif',
                        'Nonaktif' => 'Nonaktif',
                    ])
                    ->label('Status'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Tanggal Registrasi'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'Super Admin' => 'Super Admin',
                        'Admin' => 'Admin',
                        'Pengurus' => 'Pengurus',
                        'Pelatih' => 'Pelatih',
                        'Atlet' => 'Atlet',
                        'Wasit' => 'Wasit',
                        'Klub' => 'Klub',
                    ])
                    ->label('Kategori'),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Aktif' => 'Aktif',
                        'Nonaktif' => 'Nonaktif',
                    ])
                    ->label('Status'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
        ];
    }

    // RBAC: Only Super Admin and Admin can access User Management
    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user && ($user->role === 'Super Admin' || $user->role === 'Admin');
    }
}
