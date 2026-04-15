<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SintaksBelajarResource\Pages;
use App\Filament\Resources\SintaksBelajarResource\RelationManagers;
use App\Models\SintaksBelajar;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SintaksBelajarResource extends Resource
{
    protected static ?string $model = SintaksBelajar::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('pertemuan_id')
                    ->relationship('pertemuan', 'pertemuan_id') // Assuming you want to show ID or another field from Pertemuan
                    ->required(),
                Forms\Components\TextInput::make('model_pembelajaran')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('sintaks_belajar')
                    ->required()
                    ->maxLength(255),
                
                // Repeater for Kegiatan
                Forms\Components\Repeater::make('kegiatan')
                    ->relationship()
                    ->schema([
                        Forms\Components\TextInput::make('kegiatan')->required(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sintaks_belajar')
                    ->searchable(),
                Tables\Columns\TextColumn::make('model_pembelajaran')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pertemuan.pertemuan_id') // Assuming you want to show Pertemuan ID
                    ->label('Pertemuan')
                    ->searchable(),
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
            'index' => Pages\ListSintaksBelajars::route('/'),
            'create' => Pages\CreateSintaksBelajar::route('/create'),
            'edit' => Pages\EditSintaksBelajar::route('/{record}/edit'),
        ];
    }
}
