<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MasterSoalResource\Pages;
use App\Filament\Resources\MasterSoalResource\RelationManagers;
use App\Models\MasterSoal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MasterSoalResource extends Resource
{
    protected static ?string $model = MasterSoal::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Utama')
                    ->schema([
                        Forms\Components\TextInput::make('master_soal')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('sintaks_belajar_id')
                            ->relationship('sintaksBelajar', 'sintaks_belajar')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Pengaturan Tampilan')
                    ->schema([
                        Forms\Components\Toggle::make('is_diskusi')->required(),
                        Forms\Components\Toggle::make('is_show_jawaban')->required(),
                        Forms\Components\Toggle::make('is_show_kunci_jawaban')->required(),
                        Forms\Components\Toggle::make('is_show_master_soal')->default(true)->required(),
                        Forms\Components\Toggle::make('is_shared')->required(),
                    ])->columns(3),

                Forms\Components\Section::make('Daftar Soal')
                    ->schema([
                        Forms\Components\Repeater::make('mainSoal')
                            ->relationship()
                            ->schema([
                                Forms\Components\RichEditor::make('main_soal')
                                    ->required()
                                    ->label('Kasus / Narasi Induk Soal')
                                    ->columnSpanFull(),
                                
                                Forms\Components\Repeater::make('soal')
                                    ->relationship()
                                    ->schema([
                                        Forms\Components\RichEditor::make('soal')
                                            ->required()
                                            ->label('Pertanyaan / Sub Soal')
                                            ->columnSpanFull(),
                                        Forms\Components\TextInput::make('bobot')
                                            ->numeric()
                                            ->default(10)
                                            ->required()
                                            ->label('Bobot Nilai'),
                                    ])
                                    ->itemLabel(fn (array $state): ?string => strip_tags($state['soal'] ?? null))
                                    ->collapsible()
                                    ->cloneable(),
                            ])
                            ->itemLabel(fn (array $state): ?string => strip_tags($state['main_soal'] ?? null))
                            ->collapsible()
                            ->cloneable(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('master_soal')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sintaksBelajar.sintaks_belajar')
                    ->label('Sintaks Belajar')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_diskusi')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_show_jawaban')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_show_kunci_jawaban')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_show_master_soal')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_shared')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMasterSoals::route('/'),
            'create' => Pages\CreateMasterSoal::route('/create'),
            'edit' => Pages\EditMasterSoal::route('/{record}/edit'),
        ];
    }
}
