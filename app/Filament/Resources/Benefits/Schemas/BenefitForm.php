<?php

namespace App\Filament\Resources\Benefits\Schemas;

use App\Enums\BenefitCategory;
use App\Filament\Forms\Fields;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class BenefitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema(Fields::titleAndSlug('name', 'Nama benefit'))->columnSpanFull(),
                Select::make('category')
                    ->label('Kategori')
                    ->options(BenefitCategory::class)
                    ->required(),
                Fields::icon(),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->helperText('Benefit nonaktif tidak tampil di website, walau masih dicentang di cluster.')
                    ->default(true),
            ]);
    }
}
