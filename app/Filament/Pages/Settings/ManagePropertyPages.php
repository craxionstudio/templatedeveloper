<?php

namespace App\Filament\Pages\Settings;

use App\Filament\Forms\Fields;
use App\Settings\ClusterDetailPageSettings;
use App\Settings\KawasanDetailPageSettings;
use App\Settings\ListingPageSettings;
use BackedEnum;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * Properti: halaman listing (/properti, /properti/kawasan), Detail Kawasan, dan Detail Rumah.
 */
class ManagePropertyPages extends GroupedSettingsPage
{
    protected static ?string $title = 'Properti';

    protected static ?string $navigationLabel = 'Properti';

    protected static ?string $slug = 'pengaturan/properti';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $publicPath = '/properti';

    protected static function settingsGroups(): array
    {
        return [
            'listing' => ListingPageSettings::class,
            'kawasan' => KawasanDetailPageSettings::class,
            'cluster' => ClusterDetailPageSettings::class,
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Halaman Properti (listing)')->schema([
                Fields::text('listing.header.title', 'Judul (H1)')->placeholder('Properti BSD City'),
                Fields::textarea('listing.header.description', 'Teks di bawah judul', 2)->placeholder('Pilih rumah berdasarkan cluster, atau jelajahi dulu kawasan-kawasan di BSD City.'),
                Fields::settingsImageOnly('listing.header.image', 'Foto header'),
            ]),
            Section::make('Detail Rumah')->schema([
                Fields::textarea('cluster.sections.benefits.disclaimer', 'Syarat & ketentuan Promo & Benefit', 2)->placeholder('*Syarat dan ketentuan berlaku. Hubungi marketing untuk detail promo.'),
                Fields::text('cluster.pricing.price_note', 'Catatan harga')->placeholder('Harga dapat berubah sewaktu-waktu.'),
                Fields::text('cluster.form.marketing_name', 'Nama marketing default')->placeholder('Tim Marketing BSD City')
                    ->helperText('Nomor WhatsApp: nomor cluster (form Cluster → Marketing), kalau kosong nomor di Pengaturan Umum.'),
                Fields::settingsImageOnly('cluster.form.marketing_photo', 'Foto marketing default')->avatar(),
            ]),
            Fields::advanced([
                Section::make('Meta /properti')->schema(Fields::settingsMeta('listing.seo_cluster'))->compact(),
                Section::make('Meta /properti/kawasan')->schema(Fields::settingsMeta('listing.seo_kawasan'))->compact(),
            ]),
        ]);
    }
}
