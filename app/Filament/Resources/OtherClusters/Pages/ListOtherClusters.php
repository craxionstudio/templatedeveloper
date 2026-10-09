<?php

namespace App\Filament\Resources\OtherClusters\Pages;

use App\Filament\Resources\OtherClusters\OtherClusterResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOtherClusters extends ListRecords
{
    protected static string $resource = OtherClusterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
