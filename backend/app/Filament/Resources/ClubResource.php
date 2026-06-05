<?php

namespace App\Filament\Resources;

use App\Models\Club;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\ClubResource\Pages;

class ClubResource extends Resource
{
    protected static ?string $model = Club::class;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    protected static ?string $navigationLabel = 'Data Klub';

    protected static ?string $pluralLabel = 'Klub';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_klub')
                    ->required()
                    ->label('Nama Klub'),
                Forms\Components\TextInput::make('alamat')
                    ->required()
                    ->label('Alamat Sekretariat'),
                Forms\Components\TextInput::make('pelatih')
                    ->required()
                    ->label('Pelatih Kepala'),
                Forms\Components\TextInput::make('jumlah_atlet')
                    ->numeric()
                    ->required()
                    ->label('Jumlah Atlet Terdaftar'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_klub')
                    ->searchable()
                    ->sortable()
                    ->label('Nama Klub'),
                Tables\Columns\TextColumn::make('alamat')
                    ->searchable()
                    ->label('Alamat'),
                Tables\Columns\TextColumn::make('pelatih')
                    ->searchable()
                    ->label('Pelatih Kepala'),
                Tables\Columns\TextColumn::make('jumlah_atlet')
                    ->sortable()
                    ->label('Jumlah Atlet'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClubs::route('/'),
        ];
    }
}
