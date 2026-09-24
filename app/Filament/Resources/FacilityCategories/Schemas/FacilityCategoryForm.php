<?php

namespace App\Filament\Resources\FacilityCategories\Schemas;

use App\Filament\Forms\Fields;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class FacilityCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema(Fields::titleAndSlug('name', 'Nama kategori'))->columnSpanFull(),
                Fields::icon(),
                TextInput::make('sort_order')->label('Urutan')->numeric()->default(0),
            ]);
    }
}
