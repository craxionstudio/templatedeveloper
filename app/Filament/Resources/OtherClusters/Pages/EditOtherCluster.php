<?php

namespace App\Filament\Resources\OtherClusters\Pages;

use App\Filament\Resources\OtherClusters\ActivateClusterAction;
use App\Filament\Resources\OtherClusters\OtherClusterResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOtherCluster extends EditRecord
{
    protected static string $resource = OtherClusterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ActivateClusterAction::make(),
            DeleteAction::make(),
        ];
    }
}
