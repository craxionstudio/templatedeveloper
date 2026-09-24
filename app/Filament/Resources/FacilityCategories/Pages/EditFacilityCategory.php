<?php

namespace App\Filament\Resources\FacilityCategories\Pages;

use App\Filament\Resources\FacilityCategories\FacilityCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFacilityCategory extends EditRecord
{
    protected static string $resource = FacilityCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
