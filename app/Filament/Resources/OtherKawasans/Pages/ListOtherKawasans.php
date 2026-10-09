<?php

namespace App\Filament\Resources\OtherKawasans\Pages;

use App\Filament\Resources\OtherKawasans\OtherKawasanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOtherKawasans extends ListRecords
{
    protected static string $resource = OtherKawasanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
