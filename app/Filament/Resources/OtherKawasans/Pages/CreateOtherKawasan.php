<?php

namespace App\Filament\Resources\OtherKawasans\Pages;

use App\Filament\Resources\OtherKawasans\OtherKawasanResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOtherKawasan extends CreateRecord
{
    protected static string $resource = OtherKawasanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return [...$data, 'punya_halaman' => false, 'is_published' => true];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
