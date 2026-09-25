<?php

namespace App\Filament\Resources\Kawasans\Pages;

use App\Filament\Actions\PreviewAction;
use App\Filament\Resources\Kawasans\KawasanResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditKawasan extends EditRecord
{
    protected static string $resource = KawasanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            PreviewAction::make('kawasan.preview', 'kawasan'),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
