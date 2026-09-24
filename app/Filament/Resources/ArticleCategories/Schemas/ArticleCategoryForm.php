<?php

namespace App\Filament\Resources\ArticleCategories\Schemas;

use App\Filament\Forms\Fields;
use App\Filament\Forms\SeoTab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ArticleCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Tabs::make('Kategori')->tabs([
                    Tab::make('Kategori')->schema([
                        Grid::make(2)->schema(Fields::titleAndSlug('name', 'Nama kategori')),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->helperText('Dipakai di halaman kategori dan sebagai meta description.'),
                        TextInput::make('sort_order')->label('Urutan')->numeric()->default(0),
                    ]),
                    SeoTab::make(fn (Get $get): string => '/artikel/kategori/'.$get('../slug'), 'name', 'description'),
                ]),
            ]);
    }
}
