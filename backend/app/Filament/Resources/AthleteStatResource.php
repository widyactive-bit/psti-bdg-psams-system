<?php

namespace App\Filament\Resources;

use App\Models\AthleteStat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\AthleteStatResource\Pages;

class AthleteStatResource extends Resource
{
    protected static ?string $model = AthleteStat::class;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?string $navigationLabel = 'Statistik Analitik';

    protected static ?string $pluralLabel = 'Statistik';

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
                Forms\Components\DatePicker::make('record_date')
                    ->required()
                    ->label('Tanggal Evaluasi'),
                
                Forms\Components\Section::make('Metrik Teknik (0-100)')
                    ->schema([
                        Forms\Components\TextInput::make('tendangan')
                            ->numeric()
                            ->required()
                            ->label('Tendangan'),
                        Forms\Components\TextInput::make('pukulan')
                            ->numeric()
                            ->required()
                            ->label('Blok / Hadangan (Dada/Lengan)'),
                        Forms\Components\TextInput::make('akurasi')
                            ->numeric()
                            ->required()
                            ->label('Akurasi'),
                        Forms\Components\TextInput::make('kecepatan')
                            ->numeric()
                            ->required()
                            ->label('Kecepatan'),
                    ])->columns(2),

                Forms\Components\Section::make('Metrik Fisik (0-100)')
                    ->schema([
                        Forms\Components\TextInput::make('endurance')
                            ->numeric()
                            ->required()
                            ->label('Daya Tahan (Endurance)'),
                        Forms\Components\TextInput::make('agility')
                            ->numeric()
                            ->required()
                            ->label('Kelincahan (Agility)'),
                        Forms\Components\TextInput::make('flexibility')
                            ->numeric()
                            ->required()
                            ->label('Kelenturan (Flexibility)'),
                        Forms\Components\TextInput::make('strength')
                            ->numeric()
                            ->required()
                            ->label('Kekuatan (Strength)'),
                    ])->columns(2),

                Forms\Components\Section::make('Metrik Mental (0-100)')
                    ->schema([
                        Forms\Components\TextInput::make('disiplin')
                            ->numeric()
                            ->required()
                            ->label('Disiplin'),
                        Forms\Components\TextInput::make('fokus')
                            ->numeric()
                            ->required()
                            ->label('Fokus'),
                        Forms\Components\TextInput::make('leadership')
                            ->numeric()
                            ->required()
                            ->label('Kepemimpinan (Leadership)'),
                    ])->columns(3),
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
                Tables\Columns\TextColumn::make('record_date')
                    ->date()
                    ->sortable()
                    ->label('Bulan Evaluasi'),
                Tables\Columns\TextColumn::make('score')
                    ->getStateUsing(fn ($record) => round($record->athlete->calculateRankingScore(), 2))
                    ->label('Skor PSAMS (Ranking)'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAthleteStats::route('/'),
        ];
    }
}
