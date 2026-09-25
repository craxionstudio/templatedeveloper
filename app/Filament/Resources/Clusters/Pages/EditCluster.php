<?php

namespace App\Filament\Resources\Clusters\Pages;

use App\Filament\Actions\PreviewAction;
use App\Filament\Resources\Clusters\ClusterResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditCluster extends EditRecord
{
    protected static string $resource = ClusterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            PreviewAction::make('cluster.preview', 'cluster'),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
