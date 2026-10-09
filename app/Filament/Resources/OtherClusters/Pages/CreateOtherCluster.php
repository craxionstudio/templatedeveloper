<?php

namespace App\Filament\Resources\OtherClusters\Pages;

use App\Enums\ClusterDisplay;
use App\Filament\Resources\OtherClusters\OtherClusterResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOtherCluster extends CreateRecord
{
    protected static string $resource = OtherClusterResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return [...$data, 'tampil_sebagai' => ClusterDisplay::Daftar, 'is_published' => true];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
