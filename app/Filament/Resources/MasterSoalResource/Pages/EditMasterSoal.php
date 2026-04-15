<?php

namespace App\Filament\Resources\MasterSoalResource\Pages;

use App\Filament\Resources\MasterSoalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterSoal extends EditRecord
{
    protected static string $resource = MasterSoalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
