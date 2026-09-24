<?php

namespace App\Filament\Pages\Settings;

use App\Filament\Forms\Fields;
use App\Settings\HomePageSettings;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

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
            Tabs::make('Beranda')->persistTabInQueryString()->tabs([
                Tab::make('Hero')->schema([
                    Fields::enabled('hero'),
                    Fields::text('hero.eyebrow', 'Eyebrow'),
                    Fields::text('hero.title', 'Headline'),
                    Fields::textarea('hero.description', 'Sub-headline'),
                    ...Fields::settingsImage('hero.image', 'hero.image_alt', 'Gambar / poster desktop'),
                    Fields::settingsImage('hero.image_mobile', 'hero.image_alt', 'Gambar mobile (opsional)')[0],
                    TextInput::make('hero.video_url')->label('URL video (opsional)')->url()
                        ->helperText('Video dimuat setelah halaman siap; poster tampil lebih dulu.'),
                    Fields::button('hero.primary', 'tombol utama'),
                    Fields::button('hero.secondary', 'tombol kedua', 'Kosong = link WhatsApp.'),
                ]),
                Tab::make('Tentang Developer')->schema([
                    Fields::enabled('about'),
                    Fields::text('about.eyebrow', 'Eyebrow'),
                    Toggle::make('about.use_profile')->label('Ambil judul, isi, statistik, dan foto dari Profil Developer')->live(),
                    Fields::text('about.title', 'Judul (override)')->hidden(fn (Get $get): bool => (bool) $get('about.use_profile')),
                    Fields::textarea('about.description', 'Isi (override)', 4)->hidden(fn (Get $get): bool => (bool) $get('about.use_profile')),
                    Fields::button('about.link', 'link "Profil lengkap"'),
                ]),
                Tab::make('Banner Promo')->schema([
                    Fields::enabled('promo'),
                    ...Fields::dataSource('promo', fn (): array => Options::homePromos(), 'Otomatis (promo aktif di Beranda)', withLimit: false),
                    Toggle::make('promo.autoplay')->label('Autoplay slider'),
                ]),
                Tab::make('Listing Produk')->schema([
                    Fields::enabled('listing'),
                    Fields::text('listing.eyebrow', 'Eyebrow'),
                    Fields::text('listing.title', 'Judul'),
                    ...Fields::dataSource('listing', fn (): array => Options::clusters(), 'Otomatis (cluster unggulan)'),
                    Fields::button('listing.button', 'tombol "Lihat Semua Listing"'),
                ]),
                Tab::make('Keunggulan Wilayah')->schema([
                    Fields::enabled('region'),
                    Fields::text('region.eyebrow', 'Eyebrow'),
                    Fields::text('region.title', 'Judul'),
                    Fields::textarea('region.description', 'Deskripsi'),
                    Toggle::make('region.use_area')->label('Peta, badge jarak, dan poin keunggulan dari Profil Lokasi')
                        ->helperText('Ubah isinya di Properti → Profil Lokasi.'),
                ]),
                Tab::make('Fasilitas')->schema([
                    Fields::enabled('facilities'),
                    Fields::text('facilities.eyebrow', 'Eyebrow'),
                    Fields::text('facilities.title', 'Judul'),
                    ...Fields::dataSource('facilities', fn (): array => Options::facilities(), 'Otomatis (fasilitas unggulan)'),
                    Fields::button('facilities.link', 'link'),
                ]),
                Tab::make('Pengembangan Mendatang')->schema([
                    Fields::enabled('developments'),
                    Fields::text('developments.eyebrow', 'Eyebrow'),
                    Fields::text('developments.title', 'Judul'),
                    Fields::textarea('developments.description', 'Deskripsi'),
                    Fields::textarea('developments.disclaimer', 'Catatan disclaimer', 2),
                    ...Fields::dataSource('developments', fn (): array => Options::developments(), 'Otomatis (semua, sesuai urutan)'),
                ]),
                Tab::make('Artikel Highlight')->schema([
                    Fields::enabled('articles'),
                    Fields::text('articles.eyebrow', 'Eyebrow'),
                    Fields::text('articles.title', 'Judul'),
                    Select::make('articles.main_article_id')->label('Artikel utama')->options(fn (): array => Options::articles())
                        ->placeholder('Otomatis (highlight terbaru)')->searchable(),
                    ...Fields::dataSource('articles', fn (): array => Options::articles(), 'Otomatis (terbaru)'),
                    Fields::button('articles.link', 'link'),
                ]),
                Tab::make('CTA')->schema([Fields::cta()]),
                Tab::make('SEO')->schema(Fields::settingsSeo('seo', '/')),
            ]),
        ]);
    }
}
