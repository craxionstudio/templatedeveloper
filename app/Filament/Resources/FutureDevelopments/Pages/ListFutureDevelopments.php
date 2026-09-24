<?php

namespace App\Filament\Resources\FutureDevelopments\Pages;

use App\Filament\Resources\FutureDevelopments\FutureDevelopmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFutureDevelopments extends ListRecords
{
    protected static string $resource = FutureDevelopmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
