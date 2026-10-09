<?php

namespace App\Filament\Resources\OtherKawasans\Pages;

use App\Filament\Resources\OtherKawasans\ActivateKawasanAction;
use App\Filament\Resources\OtherKawasans\OtherKawasanResource;
use Filament\Resources\Pages\EditRecord;

class EditOtherKawasan extends EditRecord
{
    protected static string $resource = OtherKawasanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ActivateKawasanAction::make(),
        ];
    }
}
