<?php

namespace App\Filament\Resources\Tags\Schemas;

use App\Filament\Forms\Fields;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class TagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema(Fields::titleAndSlug('name', 'Nama tag'))->columnSpanFull(),
            ]);
    }
}
