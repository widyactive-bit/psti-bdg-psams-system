<?php

namespace App\Filament\Resources;

use App\Models\Attendance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\AttendanceResource\Pages;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Kehadiran / Absensi';

    protected static ?string $pluralLabel = 'Absensi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('athlete_id')
                    ->relationship('athlete', 'nama_lengkap')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Atlet'),
                Forms\Components\DateTimePicker::make('checkin_time')
                    ->required()
                    ->label('Waktu Check In'),
                Forms\Components\DateTimePicker::make('checkout_time')
                    ->label('Waktu Check Out'),
                Forms\Components\TextInput::make('duration')
                    ->numeric()
                    ->suffix('menit')
                    ->label('Durasi Latihan'),
                Forms\Components\TextInput::make('latitude')
                    ->numeric()
                    ->required()
                    ->label('Latitude'),
                Forms\Components\TextInput::make('longitude')
                    ->numeric()
                    ->required()
                    ->label('Longitude'),
                Forms\Components\FileUpload::make('selfie')
                    ->image()
                    ->directory('selfies')
                    ->required()
                    ->label('Foto Selfie PAP'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('athlete.nama_lengkap')
                    ->searchable()
                    ->sortable()
                    ->label('Nama Atlet'),
                Tables\Columns\TextColumn::make('checkin_time')
                    ->dateTime()
                    ->sortable()
                    ->label('Check In'),
                Tables\Columns\TextColumn::make('checkout_time')
                    ->dateTime()
                    ->label('Check Out'),
                Tables\Columns\TextColumn::make('duration')
                    ->suffix(' menit')
                    ->label('Durasi'),
                Tables\Columns\TextColumn::make('coordinates')
                    ->getStateUsing(fn ($record) => $record->latitude . ', ' . $record->longitude)
                    ->label('GPS Koordinat'),
                Tables\Columns\ImageColumn::make('selfie')
                    ->circular()
                    ->label('Selfie'),
            ])
            ->filters([
                // Filter by date
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendances::route('/'),
        ];
    }
}
