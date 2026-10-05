<?php

namespace App\Filament\Resources\FacilityCategories\Schemas;

use App\Filament\Forms\Fields;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FacilityCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Nama kategori')->required()->maxLength(255)->placeholder('Pusat belanja'),
                Fields::icon(),
                Fields::advanced([Fields::slug()->placeholder('pusat-belanja')])->columnSpanFull(),
            ]);
    }
}
