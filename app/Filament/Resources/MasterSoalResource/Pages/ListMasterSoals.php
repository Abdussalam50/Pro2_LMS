<?php

namespace App\Filament\Resources\MasterSoalResource\Pages;

use App\Filament\Resources\MasterSoalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterSoals extends ListRecords
{
    protected static string $resource = MasterSoalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
