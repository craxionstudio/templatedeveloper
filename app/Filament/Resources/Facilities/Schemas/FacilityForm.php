<?php

namespace App\Filament\Resources\Facilities\Schemas;

use App\Filament\Forms\Fields;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class FacilityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Fasilitas')->schema([
                    Grid::make(2)->schema(Fields::titleAndSlug('name', 'Nama fasilitas')),
                    Grid::make(3)->schema([
                        Select::make('facility_category_id')
                            ->label('Kategori')
                            ->relationship('category', 'name', fn (Builder $query) => $query->orderBy('sort_order'))
                            ->preload()
                            ->required(),
                        Select::make('kawasan_id')
                            ->label('Kawasan')
                            ->relationship('kawasan', 'name')
                            ->placeholder('Semua kawasan')
                            ->preload(),
                        Fields::icon(),
                    ]),
                    Textarea::make('description')->label('Deskripsi')->rows(3),
                ]),
                Section::make('Foto')->schema(Fields::image('photo', 'photo_alt', 'Foto')),
                Section::make('Publikasi')->columns(3)->schema([
                    Toggle::make('is_published')->label('Dipublikasikan')->default(true),
                    Toggle::make('is_featured')->label('Unggulan (Beranda)'),
                    TextInput::make('sort_order')->label('Urutan')->numeric()->default(0),
                ]),
            ]);
    }
}
