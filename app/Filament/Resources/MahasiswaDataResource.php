<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MahasiswaDataResource\Pages;
use App\Filament\Resources\MahasiswaDataResource\RelationManagers;
use App\Models\MahasiswaData;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MahasiswaDataResource extends Resource
{
    protected static ?string $model = MahasiswaData::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Data Mahasiswa';
    protected static ?string $modelLabel = 'Mahasiswa';
    protected static ?string $pluralModelLabel = 'Data Mahasiswa';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Akun User')
                    ->description('Informasi login mahasiswa')
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
                                    ->default('mahasiswa'),
                            ]),
                    ]),
                Forms\Components\Section::make('Detail Profil')
                    ->description('Informasi biodata mahasiswa')
                    ->schema([
                        Forms\Components\TextInput::make('mahasiswa_id')
                            ->label('ID Mahasiswa (UUID)')
                            ->default(fn () => \Illuminate\Support\Str::uuid())
                            ->required()
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\TextInput::make('nama')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('nim')
                            ->label('NIM')
                            ->required()
                            ->unique('mahasiswas', 'nim', ignoreRecord: true)
                            ->maxLength(50),
                        Forms\Components\Select::make('kelas_id')
                            ->relationship('kelas', 'nama_kelas')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('program_studi')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('angkatan')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('no_wa')
                            ->label('Nomor WhatsApp')
                            ->tel()
                            ->maxLength(20),
                        Forms\Components\FileUpload::make('foto')
                            ->image()
                            ->directory('foto-mahasiswa')
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
                Tables\Columns\TextColumn::make('nim')
                    ->label('NIM')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('kelas.nama_kelas')
                    ->label('Kelas')
                    ->sortable(),
                Tables\Columns\TextColumn::make('program_studi')
                    ->label('Prodi')
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kelas_id')
                    ->relationship('kelas', 'nama_kelas'),
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
            'index' => Pages\ListMahasiswaData::route('/'),
            'create' => Pages\CreateMahasiswaData::route('/create'),
            'edit' => Pages\EditMahasiswaData::route('/{record}/edit'),
        ];
    }
}
