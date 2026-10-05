<?php

namespace App\Filament\Resources\Tags\Schemas;

use App\Filament\Forms\Fields;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Nama tag')->required()->maxLength(60)->placeholder('KPR'),
                Fields::advanced([Fields::slug()->placeholder('kpr')]),
            ]);
    }
}
