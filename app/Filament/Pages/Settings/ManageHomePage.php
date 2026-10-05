<?php

namespace App\Filament\Pages\Settings;

use App\Filament\Forms\Fields;
use App\Settings\HomePageSettings;
use BackedEnum;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * Beranda: hanya teks yang sering diubah. Section tampil otomatis kalau datanya ada
 * (cluster, fasilitas, pengembangan, artikel yang dipublikasikan); sisanya teks tetap di kode.
 */
class ManageHomePage extends PageSettingsPage
{
    protected static string $settings = HomePageSettings::class;

    protected static ?string $title = 'Beranda';

    protected static ?string $slug = 'pengaturan/beranda';

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?string $publicPath = '/';

    public function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Hero')->schema([
                Fields::text('hero.eyebrow', 'Subjudul kecil di atas judul')->placeholder('Serpong, Tangerang'),
                Fields::text('hero.title', 'Judul (H1)')->placeholder('Pilih rumah di kota seluas 6.000 hektare.'),
                Fields::textarea('hero.description', 'Teks di bawah judul', 2)->placeholder('Lebih dari 20 kawasan hunian, dari cluster baru di Vireya dan Terravia sampai NavaPark.'),
                Fields::settingsImageOnly('hero.image', 'Foto hero'),
            ]),
            Section::make('Judul section')
                ->description('Section tampil otomatis kalau ada isinya (cluster, fasilitas, pengembangan, artikel yang dipublikasikan).')
                ->schema([
                    Fields::text('listing.title', 'Pilihan properti')->placeholder('Temukan rumah di BSD City'),
                    Fields::text('region.title', 'Keunggulan wilayah')->placeholder('Lokasi yang terhubung ke pusat kota'),
                    Fields::textarea('region.description', 'Teks keunggulan wilayah', 2)->placeholder('Terhubung ke tol Jakarta-Serpong, JORR, dan Commuter Line.'),
                    Fields::text('facilities.title', 'Fasilitas')->placeholder('Semua kebutuhan harian, ada di dalam kawasan'),
                    Fields::text('developments.title', 'Pengembangan mendatang')->placeholder('Kawasan yang terus bertumbuh'),
                    Fields::textarea('developments.description', 'Teks pengembangan mendatang', 2)->placeholder('Rencana pengembangan BSD City untuk beberapa tahun ke depan.'),
                    Fields::text('articles.title', 'Artikel')->placeholder('Kabar terbaru dari BSD City'),
                ]),
            Fields::advanced(Fields::settingsMeta()),
        ]);
    }
}
