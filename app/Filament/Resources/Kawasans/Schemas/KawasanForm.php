<?php

namespace App\Filament\Resources\Kawasans\Schemas;

use App\Filament\Forms\Fields;
use App\Filament\Forms\SeoTab;
use App\Models\Kawasan;
use App\Support\DummyData;
use App\Support\Rupiah;
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
                            TextInput::make('name')->label('Nama kawasan')->required()->maxLength(255)->placeholder('Vireya'),
                            TextInput::make('about_title')
                                ->label('Judul section Tentang Kawasan')
                                ->placeholder('Kawasan hunian hijau di barat BSD City')
                                ->maxLength(255),
                            RichEditor::make('description')
                                ->label('Deskripsi')
                                ->placeholder('Vireya adalah kawasan hunian seluas 60 ha dengan danau dan jalur sepeda.')
                                ->toolbarButtons([['bold', 'italic', 'link'], ['h2', 'h3'], ['bulletList', 'orderedList', 'blockquote'], ['undo', 'redo']]),
                            TextInput::make('opened_year')
                                ->label('Tahun dibuka')
                                ->numeric()
                                ->minValue(1900)
                                ->maxValue(2100)
                                ->placeholder('2024'),
                            TextInput::make('area_ha')
                                ->label('Luas kawasan (ha)')
                                ->numeric()
                                ->minValue(0)
                                ->suffix('ha')
                                ->placeholder('60'),
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
                            Fields::imageOnly('hero', 'Foto hero'),
                            Fields::gallery(),
                            Fields::pdf('brochure', 'Brosur kawasan (PDF)'),
                        ]),
                        Tab::make('Fasilitas kawasan')->schema([
                            Repeater::make('facilities')
                                ->label('Fasilitas kawasan')
                                ->dummyHint(fn (?Kawasan $record, $state): bool => DummyData::isKawasanFacilities($record, $state))
                                ->schema([
                                    Fields::icon(),
                                    TextInput::make('title')->label('Judul')->required()->maxLength(160)->placeholder('Danau 5 ha'),
                                    TextInput::make('description')->label('Keterangan')->maxLength(160),
                                ])
                                ->columns(3)
                                ->reorderableWithDragAndDrop()
                                ->defaultItems(0)
                                ->addActionLabel('Tambah fasilitas'),
                            Repeater::make('access')
                                ->label('Lokasi & akses')
                                ->helperText('Tampil sebagai daftar di Detail Kawasan (bagian Tentang Kawasan).')
                                ->simple(TextInput::make('text')->required()->maxLength(200)->placeholder('10 menit ke pintu tol BSD'))
                                ->reorderableWithDragAndDrop()
                                ->defaultItems(0)
                                ->addActionLabel('Tambah poin akses'),
                        ]),
                        Tab::make('Peta')->schema([
                            Textarea::make('map_embed_url')
                                ->label('URL embed Google Maps')
                                ->rows(2)
                                ->placeholder('https://www.google.com/maps/embed?pb=…')
                                ->helperText('Dimuat hanya setelah pengunjung menekan "Lihat Peta" (facade).'),
                        ]),
                        Tab::make('Publikasi')->schema([
                            Toggle::make('is_published')->label('Dipublikasikan')->default(true)
                                ->helperText('Kawasan tampil di publik hanya kalau punya minimal 1 cluster yang dipublikasikan.'),
                            Toggle::make('punya_halaman')->label('Punya halaman sendiri')->default(true)
                                ->helperText('Mati = tidak punya halaman detail, tidak tampil di daftar kawasan, footer, atau sitemap; hanya jadi judul grup di halaman "Cluster Lainnya". URL lamanya diarahkan (301) ke grup itu.'),
                            Toggle::make('is_featured')->label('Unggulan'),
                        ]),
                    ]),
                Fields::advanced([
                    Fields::slug()->placeholder('vireya'),
                    Textarea::make('summary')
                        ->label('Ringkasan (kartu kawasan & meta description)')
                        ->rows(2)
                        ->maxLength(300)
                        ->placeholder('Kosong = otomatis dari deskripsi.'),
                    Grid::make(2)->schema([
                        TextInput::make('latitude')->numeric()->placeholder('-6.3017'),
                        TextInput::make('longitude')->numeric()->placeholder('106.6527'),
                    ]),
                    SeoTab::fields(),
                ]),
            ]);
    }
}
