<?php

namespace App\Filament\Pages\Settings;

use App\Filament\Forms\Fields;
use App\Settings\KawasanDetailPageSettings;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * Template semua halaman Detail Kawasan. Konten per kawasan diatur di resource Kawasan.
 */
class ManageKawasanDetailPage extends PageSettingsPage
{
    protected static string $settings = KawasanDetailPageSettings::class;

    protected static ?string $title = 'Detail Kawasan (template)';

    protected static ?string $navigationLabel = 'Detail Kawasan';

    protected static ?string $slug = 'pengaturan/detail-kawasan';

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;

    protected static ?string $publicPath = '/properti/kawasan';

    public function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Tabs::make('Detail Kawasan')->persistTabInQueryString()->tabs([
                Tab::make('Label section')->schema([
                    Section::make('Hero')->columns(2)->schema([
                        Fields::text('hero.eyebrow', 'Eyebrow'),
                        Fields::text('hero.stat_area_label', 'Label luas'),
                        Fields::text('hero.stat_cluster_label', 'Label jumlah cluster'),
                        Fields::text('hero.stat_price_label', 'Label harga mulai'),
                    ]),
                    Section::make('Tentang Kawasan')->schema([
                        Fields::enabled('about'),
                        Grid::make(3)->schema([
                            Fields::text('about.eyebrow', 'Eyebrow'),
                            Fields::text('about.brochure_label', 'Tombol brosur'),
                            Fields::text('about.map_label', 'Tombol peta'),
                        ]),
                    ]),
                    Section::make('Fasilitas kawasan')->schema([
                        Fields::enabled('facilities'),
                        Fields::text('facilities.title', 'Judul'),
                    ]),
                    Section::make('Cluster di kawasan')->schema([
                        Fields::enabled('clusters'),
                        Fields::text('clusters.eyebrow', 'Eyebrow', '{kawasan} diganti nama kawasan.'),
                        Fields::text('clusters.title', 'Judul', '{clusters} dan {types} diganti angka.'),
                        Fields::button('clusters.link', 'link'),
                    ]),
                    Section::make('Kawasan lainnya')->schema([
                        Fields::enabled('others'),
                        Fields::text('others.eyebrow', 'Eyebrow'),
                        Fields::text('others.title', 'Judul'),
                        Fields::button('others.link', 'link'),
                        TextInput::make('others.limit')->label('Jumlah')->numeric()->minValue(1)->maxValue(6),
                    ]),
                ]),
                Tab::make('CTA')->schema([Fields::cta()]),
                Tab::make('SEO default')->schema([
                    Fields::text('seo.title_pattern', 'Pola title', '{name} = nama kawasan. Tab SEO di resource Kawasan menimpa pola ini.'),
                    Fields::text('seo.description_pattern', 'Pola description', '{summary} = ringkasan kawasan.'),
                ]),
            ]),
        ]);
    }
}
