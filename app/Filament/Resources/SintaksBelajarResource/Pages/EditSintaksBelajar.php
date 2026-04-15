<?php

namespace App\Filament\Resources\SintaksBelajarResource\Pages;

use App\Filament\Resources\SintaksBelajarResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSintaksBelajar extends EditRecord
{
    protected static string $resource = SintaksBelajarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
