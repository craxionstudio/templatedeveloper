<?php

namespace App\Filament\Resources\Kawasans\Schemas;

use App\Filament\Forms\Fields;
use App\Filament\Forms\SeoTab;
use App\Models\Kawasan;
use App\Support\DummyData;
use App\Support\Rupiah;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class KawasanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Tabs::make('Kawasan')
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('Konten')->schema([
                            Grid::make(2)->schema(Fields::titleAndSlug('name', 'Nama kawasan')),
                            Textarea::make('summary')
                                ->label('Ringkasan (kartu kawasan)')
                                ->rows(2)
                                ->maxLength(300)
                                ->required(),
                            TextInput::make('about_title')
                                ->label('Judul section Tentang Kawasan')
                                ->maxLength(255),
                            RichEditor::make('description')
                                ->label('Deskripsi')
                                ->toolbarButtons([['bold', 'italic', 'link'], ['h2', 'h3'], ['bulletList', 'orderedList', 'blockquote'], ['undo', 'redo']]),
                            TextInput::make('area_ha')
                                ->label('Luas kawasan (ha)')
                                ->numeric()
                                ->minValue(0)
                                ->suffix('ha'),
                            Section::make('Dihitung otomatis')
                                ->description('Jumlah cluster dan harga mulai diambil dari cluster yang dipublikasikan.')
                                ->schema([
                                    Text::make(fn (?Kawasan $record): string => $record
                                        ? $record->publishedClusters()->count().' cluster · harga mulai '.(Rupiah::short($record->publishedClusters()->min('price_min')) ?? '–')
                                        : 'Tersedia setelah kawasan disimpan dan punya cluster.'),
                                ])
                                ->compact(),
                        ]),
                        Tab::make('Media')->schema([
                            ...Fields::image('hero', 'hero_alt', 'Foto hero'),
                            Fields::gallery(),
                            Fields::pdf('brochure', 'Brosur kawasan (PDF)'),
                        ]),
                        Tab::make('Fasilitas kawasan')->schema([
                            Repeater::make('facilities')
                                ->label('Fasilitas kawasan')
                                ->dummyHint(fn (?Kawasan $record, $state): bool => DummyData::isKawasanFacilities($record, $state))
                                ->schema([
                                    Fields::icon(),
                                    TextInput::make('title')->label('Judul')->required()->maxLength(80),
                                    TextInput::make('description')->label('Keterangan')->maxLength(120),
                                ])
                                ->columns(3)
                                ->reorderableWithDragAndDrop()
                                ->defaultItems(0)
                                ->addActionLabel('Tambah fasilitas'),
                        ]),
                        Tab::make('Peta')->schema([
                            Textarea::make('map_embed_url')
                                ->label('URL embed Google Maps')
                                ->rows(2)
                                ->helperText('Dimuat hanya setelah pengunjung menekan "Lihat Peta" (facade).'),
                            Grid::make(2)->schema([
                                TextInput::make('latitude')->numeric(),
                                TextInput::make('longitude')->numeric(),
                            ]),
                        ]),
                        Tab::make('Publikasi')->schema([
                            Toggle::make('is_published')->label('Dipublikasikan')->default(true)
                                ->helperText('Kawasan tampil di publik hanya kalau punya minimal 1 cluster yang dipublikasikan.'),
                            DateTimePicker::make('published_at')->label('Tanggal terbit')->native(false),
                            Toggle::make('is_featured')->label('Unggulan'),
                            TextInput::make('sort_order')->label('Urutan')->numeric()->default(0),
                        ]),
                        SeoTab::make(fn (Get $get): string => '/properti/kawasan/'.$get('../slug')),
                    ]),
            ]);
    }
}
