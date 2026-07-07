<?php

namespace App\Filament\Resources;

use App\Models\Athlete;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\AthleteResource\Pages;

class AthleteResource extends Resource
{
    protected static ?string $model = Athlete::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    
    protected static ?string $navigationLabel = 'Database Atlet';
    
    protected static ?string $pluralLabel = 'Atlet';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pribadi')
                    ->schema([
                        Forms\Components\TextInput::make('nomor_induk_atlet')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->label('Nomor Induk Atlet (NIA)'),
                        Forms\Components\TextInput::make('nama_lengkap')
                            ->required()
                            ->label('Nama Lengkap'),
                        Forms\Components\TextInput::make('nik')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->label('NIK'),
                        Forms\Components\TextInput::make('tempat_lahir')
                            ->required()
                            ->label('Tempat Lahir'),
                        Forms\Components\DatePicker::make('tanggal_lahir')
                            ->required()
                            ->label('Tanggal Lahir'),
                        Forms\Components\Select::make('jenis_kelamin')
                            ->options([
                                'Laki-laki' => 'Laki-laki',
                                'Perempuan' => 'Perempuan',
                            ])
                            ->required()
                            ->label('Jenis Kelamin'),
                    ])->columns(2),

                Forms\Components\Section::make('Kontak & Afiliasi')
                    ->schema([
                        Forms\Components\Textarea::make('alamat')
                            ->required()
                            ->columnSpanFull()
                            ->label('Alamat Lengkap'),
                        Forms\Components\TextInput::make('no_hp')
                            ->required()
                            ->tel()
                            ->label('No. Handphone'),
                        Forms\Components\TextInput::make('email')
                            ->required()
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->label('Email'),
                        Forms\Components\TextInput::make('klub')
                            ->required()
                            ->label('Klub Takraw'),
                        Forms\Components\Select::make('pelatih_id')
                            ->relationship('coach', 'nama')
                            ->searchable()
                            ->preload()
                            ->label('Pelatih Pendamping'),
                    ])->columns(2),

                Forms\Components\Section::make('Data Fisik & Tanding')
                    ->schema([
                        Forms\Components\TextInput::make('tinggi_badan')
                            ->numeric()
                            ->required()
                            ->suffix('cm')
                            ->label('Tinggi Badan'),
                        Forms\Components\TextInput::make('berat_badan')
                            ->numeric()
                            ->required()
                            ->suffix('kg')
                            ->label('Berat Badan'),
                        Forms\Components\Select::make('kelas_tanding')
                            ->options([
                                'Regu Putra (Tekong)' => 'Regu Putra (Tekong)',
                                'Regu Putra (Feeder)' => 'Regu Putra (Feeder)',
                                'Regu Putra (Killer)' => 'Regu Putra (Killer)',
                                'Regu Putri (Tekong)' => 'Regu Putri (Tekong)',
                                'Regu Putri (Feeder)' => 'Regu Putri (Feeder)',
                                'Regu Putri (Killer)' => 'Regu Putri (Killer)',
                                'Double Event Putra' => 'Double Event Putra',
                                'Double Event Putri' => 'Double Event Putri',
                            ])
                            ->required()
                            ->label('Kategori / Kelas Tanding'),
                        Forms\Components\Select::make('sabuk')
                            ->options([
                                'Pratama (Level C)' => 'Pratama (Level C)',
                                'Madya (Level B)' => 'Madya (Level B)',
                                'Utama (Level A)' => 'Utama (Level A)',
                            ])
                            ->required()
                            ->label('Tingkatan (Level/Sabuk)'),
                        Forms\Components\Select::make('status')
                            ->options([
                                'Aktif' => 'Aktif',
                                'Nonaktif' => 'Nonaktif',
                            ])
                            ->required()
                            ->label('Status Atlet'),
                        Forms\Components\FileUpload::make('foto')
                            ->image()
                            ->directory('athletes-photos')
                            ->label('Foto Profil')
                            ->maxSize(2048) // 2 MB
                            ->acceptedFileTypes(['image/jpeg','image/png','image/webp']),
                    ])->columns(2),
                Forms\Components\Section::make('Prestasi & Kegiatan')
                    ->schema([
Forms\Components\Repeater::make('achievement_entries')
                     ->label('Prestasi / Target Pencapaian')
                     ->schema([
                         Forms\Components\TextInput::make('title')
                             ->required()
                             ->label('Judul Prestasi / Target'),
                         Forms\Components\TextInput::make('tingkat')
                             ->required()
                             ->label('Tingkat'),
                         Forms\Components\TextInput::make('lokasi')
                             ->required()
                             ->label('Lokasi'),
                                Forms\Components\DatePicker::make('date')
                                    ->label('Tanggal'),
                                Forms\Components\Textarea::make('notes')
                                    ->label('Keterangan'),
                            ])
                            ->columns(1)
                            ->createItemButtonLabel('Tambah Prestasi'),

                        Forms\Components\Repeater::make('activity_photos')
                            ->label('Foto Kegiatan & Geotag')
                            ->schema([
                                Forms\Components\FileUpload::make('photo')
                                    ->image()
                                    ->directory('activity-photos')
                                    ->label('Foto Kegiatan')
                                    ->openable()
                                    ->downloadable()
                                    ->maxSize(5120) // 5 MB
                                    ->acceptedFileTypes(['image/jpeg','image/png','image/webp']),
                                Forms\Components\TextInput::make('latitude')
                                    ->numeric()
                                    ->label('Latitude'),
                                Forms\Components\TextInput::make('longitude')
                                    ->numeric()
                                    ->label('Longitude'),
                                Forms\Components\Textarea::make('description')
                                    ->label('Keterangan Foto'),
                            ])
                            ->columns(1)
                            ->createItemButtonLabel('Tambah Foto Kegiatan'),
                    ])->columns(1),

                Forms\Components\Section::make('Dokumen Registrasi (Uploads)')
                    ->schema([
                        Forms\Components\FileUpload::make('ktp')
                            ->directory('registrasi/ktp')
                            ->label('Scan KTP')
                            ->openable()
                            ->downloadable(),
                        Forms\Components\FileUpload::make('kk')
                            ->directory('registrasi/kk')
                            ->label('Scan Kartu Keluarga')
                            ->openable()
                            ->downloadable(),
                        Forms\Components\FileUpload::make('sertifikat')
                            ->multiple()
                            ->directory('registrasi/sertifikat')
                            ->label('Sertifikat-Sertifikat Penghargaan (Opsional)')
                            ->openable()
                            ->downloadable(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('foto')
                    ->circular()
                    ->label('Foto'),
                Tables\Columns\TextColumn::make('nomor_induk_atlet')
                    ->searchable()
                    ->sortable()
                    ->label('NIA'),
                Tables\Columns\TextColumn::make('nama_lengkap')
                    ->searchable()
                    ->sortable()
                    ->label('Nama'),
                Tables\Columns\TextColumn::make('klub')
                    ->searchable()
                    ->label('Klub'),
                Tables\Columns\TextColumn::make('kelas_tanding')
                    ->label('Posisi'),
                Tables\Columns\TextColumn::make('sabuk')
                    ->label('Level'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'Aktif',
                        'danger' => 'Nonaktif',
                    ])
                    ->label('Status'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Aktif' => 'Aktif',
                        'Nonaktif' => 'Nonaktif',
                    ]),
                Tables\Filters\SelectFilter::make('kelas_tanding')
                    ->label('Posisi/Kelas'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if (!$user) {
            return $query;
        }

        // Super Admin, Admin, Pengurus, Pelatih, and Wasit can see all athletes
        if (in_array($user->role, ['Super Admin', 'Admin', 'Pengurus', 'Pelatih', 'Wasit'])) {
            return $query;
        }

        // Athlete can only see their own profile
        if ($user->role === 'Atlet') {
            return $query->where('email', $user->email);
        }

        // Club representative can only see athletes in their club
        if ($user->role === 'Klub') {
            return $query->where('klub', 'like', '%' . $user->name . '%');
        }

        // Others see nothing
        return $query->whereRaw('1 = 0');
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();
        return $user && in_array($user->role, ['Super Admin', 'Admin']);
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        
        if (in_array($user->role, ['Super Admin', 'Admin'])) {
            return true;
        }

        if ($user->role === 'Atlet' && $record->email === $user->email) {
            return true;
        }

        return false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        $user = auth()->user();
        return $user && in_array($user->role, ['Super Admin', 'Admin']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAthletes::route('/'),
            'create' => Pages\CreateAthlete::route('/create'),
            'edit' => Pages\EditAthlete::route('/{record}/edit'),
        ];
    }
}
