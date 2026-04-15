<?php

namespace App\Filament\Resources\MahasiswaDataResource\Pages;

use App\Filament\Resources\MahasiswaDataResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMahasiswaData extends ListRecords
{
    protected static string $resource = MahasiswaDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
