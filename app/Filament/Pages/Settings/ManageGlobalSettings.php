<?php

namespace App\Filament\Pages\Settings;

use App\Filament\Forms\Fields;
use App\Settings\GlobalSettings;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageGlobalSettings extends PageSettingsPage
{
    protected static string $settings = GlobalSettings::class;

    protected static ?string $title = 'Pengaturan Global';

    protected static ?string $navigationLabel = 'Pengaturan Global';

    protected static ?string $slug = 'pengaturan/global';

    protected static string|UnitEnum|null $navigationGroup = 'Sistem';

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    public function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Tabs::make('Global')->persistTabInQueryString()->tabs([
                Tab::make('Identitas')->schema([
                    Grid::make(3)->schema([
                        TextInput::make('identity.brand_name')->label('Nama brand')->required()->maxLength(80),
                        TextInput::make('identity.company_name')->label('Nama PT')->maxLength(120),
                        TextInput::make('identity.tagline')->label('Tagline')->maxLength(120),
                    ]),
                    Grid::make(3)->schema([
                        self::logo('identity.logo_light', 'Logo (latar terang)'),
                        self::logo('identity.logo_dark', 'Logo (latar gelap)'),
                        self::logo('identity.favicon', 'Favicon'),
                    ]),
                ]),
                Tab::make('Kontak')->schema([
                    Grid::make(3)->schema([
                        TextInput::make('contact.hotline')->label('Hotline')->maxLength(40),
                        TextInput::make('contact.whatsapp')->label('Nomor WhatsApp (62…)')->tel()->regex('/^62\d{8,13}$/')
                            ->validationMessages(['regex' => 'Format nomor: 62 diikuti 8–13 digit, tanpa spasi.'])
                            ->helperText('Kosong = tombol WhatsApp diarahkan ke halaman Kontak.'),
                        TextInput::make('contact.phone')->label('Telepon kantor')->maxLength(40),
                    ]),
                    Fields::textarea('contact.whatsapp_message', 'Template pesan WhatsApp default', 2),
                    TextInput::make('contact.email')->label('Email')->maxLength(120),
                    Fields::textarea('contact.office_address', 'Alamat kantor pemasaran', 2),
                    Grid::make(3)->schema([
                        TextInput::make('contact.latitude')->label('Latitude')->numeric(),
                        TextInput::make('contact.longitude')->label('Longitude')->numeric(),
                        TextInput::make('contact.opening_hours')->label('Jam buka')->maxLength(80),
                    ]),
                ]),
                Tab::make('Header')->schema([
                    Fields::button('header.cta', 'tombol CTA', 'Kosong = link WhatsApp.'),
                    Toggle::make('header.show_hotline')->label('Tampilkan hotline di header'),
                ]),
                Tab::make('Footer')->schema([
                    Fields::textarea('footer.description', 'Deskripsi singkat', 2),
                    TextInput::make('footer.property_title')->label('Judul kolom Properti')
                        ->helperText('Isi kolom Properti otomatis: kawasan yang dipublikasikan.'),
                    Repeater::make('footer.columns')
                        ->label('Kolom link lain')
                        ->schema([
                            TextInput::make('title')->label('Judul kolom')->required()->maxLength(40),
                            self::links('links', 'Link'),
                        ])
                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                        ->collapsible()
                        ->reorderableWithDragAndDrop()
                        ->maxItems(3),
                    TextInput::make('footer.social_title')->label('Judul kolom sosial')->maxLength(40),
                    Repeater::make('footer.social')
                        ->label('Link sosial media')
                        ->schema([
                            TextInput::make('label')->label('Nama')->required()->maxLength(40),
                            TextInput::make('url')->label('URL')->required()->maxLength(255),
                        ])
                        ->columns(2)
                        ->reorderableWithDragAndDrop(),
                    TextInput::make('footer.office_title')->label('Judul kolom kantor')->maxLength(40),
                    TextInput::make('footer.copyright')->label('Teks copyright')->helperText('{year} diganti tahun berjalan.'),
                    Fields::textarea('footer.disclaimer', 'Disclaimer', 2),
                ]),
                Tab::make('CTA global')->schema([
                    Fields::text('cta.eyebrow', 'Eyebrow'),
                    Fields::text('cta.title', 'Judul'),
                    Fields::textarea('cta.description', 'Deskripsi'),
                    TextInput::make('cta.whatsapp_label')->label('Label tombol WhatsApp')->maxLength(60),
                    Fields::button('cta.visit', 'tombol kunjungan'),
                ]),
                Tab::make('Mobile')->schema([
                    Toggle::make('mobile.show_whatsapp_icon')->label('Tampilkan ikon WhatsApp di header mobile'),
                    Grid::make(3)->schema([
                        TextInput::make('mobile.sticky_price_label')->label('Sticky bar: label harga')->maxLength(30),
                        TextInput::make('mobile.sticky_whatsapp_label')->label('Sticky bar: label WhatsApp (aksesibilitas)')->maxLength(40),
                        TextInput::make('mobile.sticky_survey_label')->label('Sticky bar: tombol survey')->maxLength(40),
                    ]),
                ]),
                Tab::make('Tracking & verifikasi')->visible(fn (): bool => self::canManageTracking())->schema([
                    Grid::make(3)->schema([
                        TextInput::make('tracking.gtm_id')->label('Google Tag Manager ID')->placeholder('GTM-XXXXXXX'),
                        TextInput::make('tracking.ga4_id')->label('GA4 Measurement ID')->placeholder('G-XXXXXXXXXX'),
                        TextInput::make('tracking.meta_pixel_id')->label('Meta Pixel ID'),
                    ]),
                    Grid::make(3)->schema([
                        TextInput::make('tracking.google_verification')->label('Verifikasi Google Search Console'),
                        TextInput::make('tracking.bing_verification')->label('Verifikasi Bing Webmaster'),
                        TextInput::make('tracking.turnstile_site_key')->label('Cloudflare Turnstile site key')
                            ->helperText('Secret key disimpan di .env (TURNSTILE_SECRET_KEY).'),
                    ]),
                ]),
                Tab::make('Label umum')->schema([
                    Grid::make(3)->schema(collect(GlobalSettings::defaults()['labels'])
                        ->map(fn (string $default, string $key) => TextInput::make("labels.{$key}")
                            ->label(str($key)->replace('_', ' ')->ucfirst()->toString())
                            ->placeholder($default)
                            ->maxLength(80))
                        ->values()
                        ->all()),
                ]),
                Tab::make('SEO default')->schema([
                    TextInput::make('seo.title_pattern')->label('Pola title')->helperText('{title} = judul halaman, {brand} = nama brand.'),
                    TextInput::make('seo.home_title_pattern')->label('Pola title Beranda')->helperText('{brand} dan {tagline}.'),
                    FileUpload::make('seo.default_og_image')->label('OG image default (1200×630)')->disk('public')->directory('seo')->visibility('public')->image(),
                    TagsInput::make('seo.same_as')->label('sameAs (URL profil resmi untuk Organization)')->placeholder('https://instagram.com/…'),
                ]),
            ]),
        ]);
    }

    /**
     * Tab "Tracking & verifikasi" hanya untuk Super Admin (keputusan pemilik 24 Sep 2026).
     */
    public static function canManageTracking(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    /**
     * ID tracking & kode verifikasi tidak dikirim ke browser untuk role selain Super Admin.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (! self::canManageTracking()) {
            unset($data['tracking']);
        }

        return $data;
    }

    /**
     * Penolakan di sisi server: perubahan tracking dari role lain selalu dibuang,
     * walaupun request Livewire dimanipulasi. Nilai tersimpan dipertahankan.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! self::canManageTracking()) {
            unset($data['tracking']);
        }

        return parent::mutateFormDataBeforeSave($data);
    }

    private static function logo(string $path, string $label): FileUpload
    {
        return FileUpload::make($path)
            ->label($label)
            ->disk('public')
            ->directory('brand')
            ->visibility('public')
            ->image()
            ->acceptedFileTypes(['image/png', 'image/svg+xml', 'image/webp', 'image/x-icon', 'image/vnd.microsoft.icon']);
    }

    public static function links(string $name, string $label): Repeater
    {
        return Repeater::make($name)
            ->label($label)
            ->schema([
                TextInput::make('label')->label('Label')->required()->maxLength(60),
                TextInput::make('url')->label('URL')->required()->maxLength(255),
                Toggle::make('new_tab')->label('Tab baru')->inline(false),
            ])
            ->columns(3)
            ->reorderableWithDragAndDrop()
            ->defaultItems(0);
    }
}
