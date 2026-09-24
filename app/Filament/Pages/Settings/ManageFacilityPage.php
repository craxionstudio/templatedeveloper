<?php

namespace App\Filament\Pages\Settings;

use App\Filament\Forms\Fields;
use App\Settings\FacilityPageSettings;
use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageFacilityPage extends PageSettingsPage
{
    protected static string $settings = FacilityPageSettings::class;

    protected static ?string $title = 'Fasilitas';

    protected static ?string $slug = 'pengaturan/fasilitas';

    protected static ?int $navigationSort = 5;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static ?string $publicPath = '/fasilitas';

    public function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Tabs::make('Fasilitas')->persistTabInQueryString()->tabs([
                Tab::make('Header')->schema([
                    Fields::text('header.eyebrow', 'Eyebrow'),
                    Fields::text('header.title', 'Judul (H1)'),
                    Fields::textarea('header.description', 'Deskripsi'),
                    Repeater::make('header.stats')->label('Statistik')->schema([
                        TextInput::make('value')->label('Nilai')->required(),
                        TextInput::make('label')->label('Label')->required(),
                    ])->columns(2)->maxItems(3)->reorderableWithDragAndDrop(),
                    Repeater::make('header.images')->label('Gambar (utama + 2 pendamping)')->schema(
                        Fields::settingsImage('image', 'alt', 'Gambar'),
                    )->columns(2)->maxItems(3)->reorderableWithDragAndDrop(),
                ]),
                Tab::make('Daftar')->schema([
                    Select::make('list.categories')->label('Kategori yang tampil sebagai filter')
                        ->options(fn (): array => Options::facilityCategories())->multiple()
                        ->helperText('Kosong = semua kategori yang punya fasilitas.'),
                    Grid::make(2)->schema([
                        Fields::text('list.all_label', 'Chip "Semua"'),
                        Fields::text('list.everywhere_label', 'Label fasilitas tanpa kawasan'),
                    ]),
                    Toggle::make('list.show_kawasan_filter')->label('Tampilkan filter kawasan'),
                    Grid::make(2)->schema([
                        Fields::text('list.kawasan_filter_label', 'Label filter kawasan'),
                        Fields::text('list.all_kawasan_label', 'Opsi "Semua kawasan"'),
                    ]),
                ]),
                Tab::make('CTA')->schema([Fields::cta()]),
                Tab::make('SEO')->schema(Fields::settingsSeo('seo', '/fasilitas')),
            ]),
        ]);
    }
}
