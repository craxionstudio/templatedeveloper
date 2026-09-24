<?php

namespace App\Filament\Resources\Kawasans\Pages;

use App\Filament\Resources\Kawasans\KawasanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKawasans extends ListRecords
{
    protected static string $resource = KawasanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
