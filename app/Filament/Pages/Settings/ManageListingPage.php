<?php

namespace App\Filament\Pages\Settings;

use App\Filament\Forms\Fields;
use App\Settings\ListingPageSettings;
use BackedEnum;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * Dipakai kedua tampilan listing: /properti (Cluster) dan /properti/kawasan (Kawasan).
 */
class ManageListingPage extends PageSettingsPage
{
    protected static string $settings = ListingPageSettings::class;

    protected static ?string $title = 'Properti (Listing)';

    protected static ?string $slug = 'pengaturan/properti';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $publicPath = '/properti';

    public function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Tabs::make('Listing')->persistTabInQueryString()->tabs([
                Tab::make('Header')->schema([
                    Fields::text('header.eyebrow', 'Eyebrow lokasi'),
                    Fields::text('header.title', 'Judul (H1)'),
                    Fields::textarea('header.description', 'Deskripsi'),
                    ...Fields::settingsImage('header.image', 'header.image_alt', 'Gambar desktop'),
                    Fields::settingsImage('header.image_mobile', 'header.image_alt', 'Gambar mobile (opsional)')[0],
                    Section::make('Statistik')->description('Angka dihitung otomatis dari data; hanya label yang bisa diubah.')->columns(3)->schema([
                        TextInput::make('header.stat_kawasan_label')->label('Label jumlah kawasan'),
                        TextInput::make('header.stat_cluster_label')->label('Label jumlah cluster'),
                        TextInput::make('header.stat_price_label')->label('Label harga mulai'),
                    ]),
                ]),
                Tab::make('Toggle tampilan')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('toggle.cluster_label')->label('Label "Cluster"')->helperText('Link ke /properti'),
                        TextInput::make('toggle.kawasan_label')->label('Label "Kawasan"')->helperText('Link ke /properti/kawasan'),
                    ]),
                ]),
                Tab::make('Tampilan Cluster')->schema([
                    CheckboxList::make('cluster_view.filters')
                        ->label('Filter yang ditampilkan')
                        ->options(['kawasan' => 'Kawasan', 'tipe' => 'Tipe properti', 'kamar' => 'Kamar tidur', 'harga' => 'Kisaran harga', 'status' => 'Status'])
                        ->columns(5),
                    Section::make('Label filter')->columns(5)->schema(collect(['kawasan', 'tipe', 'kamar', 'harga', 'status'])
                        ->map(fn (string $key) => TextInput::make("cluster_view.filter_labels.{$key}")->label(ucfirst($key)))
                        ->all()),
                    Grid::make(3)->schema([
                        TextInput::make('cluster_view.all_kawasan_label')->label('Opsi "Semua kawasan"'),
                        TextInput::make('cluster_view.standalone_label')->label('Opsi "Cluster mandiri"'),
                        TextInput::make('cluster_view.all_label')->label('Opsi "Semua"'),
                        TextInput::make('cluster_view.apply_label')->label('Tombol terapkan'),
                        TextInput::make('cluster_view.mobile_filter_label')->label('Tombol filter (mobile)'),
                        TextInput::make('cluster_view.sort_label')->label('Label urutkan'),
                    ]),
                    TagsInput::make('cluster_view.bedroom_options')->label('Opsi jumlah kamar tidur'),
                    Repeater::make('cluster_view.price_ranges')
                        ->label('Rentang harga')
                        ->schema([
                            TextInput::make('label')->required(),
                            TextInput::make('min')->label('Min (Rp)')->numeric(),
                            TextInput::make('max')->label('Maks (Rp)')->numeric(),
                        ])
                        ->columns(3)
                        ->reorderableWithDragAndDrop(),
                    KeyValue::make('cluster_view.sort_options')->label('Opsi urutan')->keyLabel('Kode')->valueLabel('Label')
                        ->addable(false)->deletable(false)->editableKeys(false),
                    Grid::make(2)->schema([
                        Select::make('cluster_view.default_sort')->label('Urutan default')
                            ->options(['terbaru' => 'Terbaru', 'harga-terendah' => 'Harga terendah', 'harga-tertinggi' => 'Harga tertinggi']),
                        TextInput::make('cluster_view.per_page')->label('Jumlah per halaman')->numeric()->minValue(3)->maxValue(48),
                    ]),
                    TextInput::make('cluster_view.result_template')->label('Teks jumlah hasil')->helperText('{clusters} dan {types} diganti angka.'),
                ]),
                Tab::make('Tampilan Kawasan')->schema([
                    TextInput::make('kawasan_view.summary_template')->label('Teks ringkasan')->helperText('{kawasan}, {clusters}, {standalone} diganti angka.'),
                    Grid::make(2)->schema([
                        TextInput::make('kawasan_view.kawasan_eyebrow')->label('Eyebrow kartu kawasan'),
                        TextInput::make('kawasan_view.view_button_label')->label('Label tombol "Lihat kawasan"'),
                    ]),
                    Section::make('Section "Cluster yang berdiri sendiri"')->schema([
                        Fields::enabled('kawasan_view.standalone'),
                        Fields::text('kawasan_view.standalone.eyebrow', 'Eyebrow'),
                        Fields::text('kawasan_view.standalone.title', 'Judul'),
                        Fields::textarea('kawasan_view.standalone.description', 'Deskripsi', 2),
                    ]),
                ]),
                Tab::make('Empty state')->schema([
                    Fields::text('empty_state.title', 'Judul'),
                    Fields::textarea('empty_state.description', 'Deskripsi', 2),
                    Fields::text('empty_state.button_label', 'Label tombol reset'),
                ]),
                Tab::make('CTA')->schema([Fields::cta()]),
                Tab::make('SEO /properti')->schema(Fields::settingsSeo('seo_cluster', '/properti')),
                Tab::make('SEO /properti/kawasan')->schema(Fields::settingsSeo('seo_kawasan', '/properti/kawasan')),
            ]),
        ]);
    }
}
