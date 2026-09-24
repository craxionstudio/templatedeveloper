<?php

namespace App\Filament\Resources\FutureDevelopments\Pages;

use App\Filament\Resources\FutureDevelopments\FutureDevelopmentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditFutureDevelopment extends EditRecord
{
    protected static string $resource = FutureDevelopmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
