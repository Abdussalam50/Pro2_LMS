<?php

namespace App\Filament\Resources\SintaksBelajarResource\Pages;

use App\Filament\Resources\SintaksBelajarResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSintaksBelajars extends ListRecords
{
    protected static string $resource = SintaksBelajarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
