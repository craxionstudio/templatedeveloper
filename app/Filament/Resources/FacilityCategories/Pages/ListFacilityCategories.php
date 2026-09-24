<?php

namespace App\Filament\Resources\FacilityCategories\Pages;

use App\Filament\Resources\FacilityCategories\FacilityCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFacilityCategories extends ListRecords
{
    protected static string $resource = FacilityCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
