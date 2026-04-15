<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DosenResource\Pages;
use App\Filament\Resources\DosenResource\RelationManagers;
use App\Models\DosenData;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DosenResource extends Resource
{
    protected static ?string $model = DosenData::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Data Dosen';
    protected static ?string $modelLabel = 'Dosen';
    protected static ?string $pluralModelLabel = 'Data Dosen';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Akun User')
                    ->description('Informasi login dosen')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->unique('users', 'email')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('password')
                                    ->password()
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Hidden::make('role')
                                    ->default('dosen'),
                            ]),
                    ]),
                Forms\Components\Section::make('Detail Profil')
                    ->description('Informasi biodata dosen')
                    ->schema([
                        Forms\Components\TextInput::make('dosen_id')
                            ->label('ID Dosen (UUID)')
                            ->default(fn () => \Illuminate\Support\Str::uuid())
                            ->required()
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\TextInput::make('nama')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('kode')
                            ->label('Kode Dosen')
                            ->required()
                            ->unique('dosens', 'kode', ignoreRecord: true)
                            ->maxLength(50),
                        Forms\Components\TextInput::make('no_wa')
                            ->label('Nomor WhatsApp')
                            ->tel()
                            ->maxLength(20),
                        Forms\Components\FileUpload::make('foto')
                            ->image()
                            ->directory('foto-dosen')
                            ->visibility('public'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('foto')
                    ->circular(),
                Tables\Columns\TextColumn::make('nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('kode')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('no_wa')
                    ->label('WhatsApp'),
            ])
            ->filters([
                //
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDosens::route('/'),
            'create' => Pages\CreateDosen::route('/create'),
            'edit' => Pages\EditDosen::route('/{record}/edit'),
        ];
    }
}
