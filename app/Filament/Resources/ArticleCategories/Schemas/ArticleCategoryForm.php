<?php

namespace App\Filament\Resources\ArticleCategories\Schemas;

use App\Filament\Forms\Fields;
use App\Filament\Forms\SeoTab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ArticleCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('name')->label('Nama kategori')->required()->maxLength(255)->placeholder('Tips KPR'),
                Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(3)
                    ->placeholder('Panduan mengajukan KPR rumah di BSD City.')
                    ->helperText('Dipakai di halaman kategori dan sebagai meta description.'),
                Fields::advanced([
                    Fields::slug()->placeholder('tips-kpr'),
                    SeoTab::fields(),
                ]),
            ]);
    }
}
