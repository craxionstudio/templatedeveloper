<?php

namespace App\Filament\Resources\Facilities\Schemas;

use App\Filament\Forms\Fields;
use App\Models\Facility;
use App\Support\DummyData;
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
                    TextInput::make('name')->label('Nama fasilitas')->required()->maxLength(255)->placeholder('AEON Mall BSD City'),
                    Grid::make(3)->schema([
                        Select::make('facility_category_id')
                            ->label('Kategori')
                            ->relationship('category', 'name', fn (Builder $query) => $query->orderBy('sort_order'))
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                TextInput::make('name')->label('Nama kategori')->required()->maxLength(255)->placeholder('Pusat belanja'),
                                Fields::icon(),
                            ]),
                        Select::make('kawasan_id')
                            ->label('Kawasan')
                            ->relationship('kawasan', 'name')
                            ->placeholder('Semua kawasan')
                            ->preload()
                            ->dummyHint(fn (?Facility $record, $state): bool => DummyData::isFacilityKawasan($record, $state)),
                        Fields::icon(),
                    ]),
                    Textarea::make('description')->label('Deskripsi')->rows(3)->placeholder('Mal dengan lebih dari 300 tenant, 5 menit dari cluster.'),
                    Fields::imageOnly('photo', 'Foto'),
                ]),
                Section::make('Publikasi')->columns(2)->schema([
                    Toggle::make('is_published')->label('Dipublikasikan')->default(true),
                    Toggle::make('is_featured')->label('Unggulan (Beranda)'),
                ]),
                Fields::advanced([Fields::slug()->placeholder('aeon-mall-bsd-city')]),
            ]);
    }
}
