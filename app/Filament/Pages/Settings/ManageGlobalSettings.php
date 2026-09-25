<?php

namespace App\Filament\Pages\Settings;

use App\Filament\Forms\Fields;
use App\Settings\GlobalSettings;
use App\Support\Secret;
use BackedEnum;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
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
                    Toggle::make('cta.modal_enabled')->label('Tombol kunjungan membuka form singkat (modal)')
                        ->helperText('Mati = tombol langsung ke URL tombol kunjungan.'),
                    Fields::text('cta.modal_title', 'Judul form modal'),
                    Fields::textarea('cta.modal_description', 'Deskripsi form modal', 2),
                    TextInput::make('cta.modal_submit_label')->label('Label tombol kirim (modal)')->maxLength(40),
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
                        TextInput::make('tracking.gtm_id')->label('Google Tag Manager ID')->placeholder('GTM-XXXXXXX')
                            ->regex('/^GTM-[A-Z0-9]+$/i')->validationMessages(['regex' => 'Format: GTM-XXXXXXX.'])
                            ->live(onBlur: true)
                            ->helperText('Disarankan. Semua event dikirim ke dataLayer; atur tag GA4 & Meta Pixel di GTM (docs/TRACKING.md).'),
                        TextInput::make('tracking.ga4_id')->label('GA4 Measurement ID (opsional)')->placeholder('G-XXXXXXXXXX')
                            ->regex('/^G-[A-Z0-9]+$/i')->validationMessages(['regex' => 'Format: G-XXXXXXXXXX.'])
                            ->helperText(self::DOUBLE_COUNT_WARNING)
                            ->hint(fn (Get $get): ?string => filled($get('tracking.gtm_id')) && filled($get('tracking.ga4_id')) ? 'GTM juga terisi' : null)
                            ->hintColor('warning')
                            ->hintIcon(fn (Get $get) => filled($get('tracking.gtm_id')) && filled($get('tracking.ga4_id')) ? Heroicon::OutlinedExclamationTriangle : null)
                            ->live(onBlur: true),
                        TextInput::make('tracking.meta_pixel_id')->label('Meta Pixel ID (opsional)')
                            ->regex('/^\d{5,20}$/')->validationMessages(['regex' => 'Pixel ID hanya angka.'])
                            ->helperText(self::DOUBLE_COUNT_WARNING)
                            ->hint(fn (Get $get): ?string => filled($get('tracking.gtm_id')) && filled($get('tracking.meta_pixel_id')) ? 'GTM juga terisi' : null)
                            ->hintColor('warning')
                            ->hintIcon(fn (Get $get) => filled($get('tracking.gtm_id')) && filled($get('tracking.meta_pixel_id')) ? Heroicon::OutlinedExclamationTriangle : null)
                            ->live(onBlur: true),
                    ]),
                    Section::make('Meta Conversions API')
                        ->description('Event Lead dikirim juga dari server dengan event_id yang sama dengan Pixel (deduplikasi). Token kosong = CAPI dilewati.')
                        ->schema([
                            Grid::make(3)->schema([
                                TextInput::make('tracking.meta_capi_pixel_id')->label('Pixel ID untuk Conversions API')
                                    ->regex('/^\d{5,20}$/')->validationMessages(['regex' => 'Pixel ID hanya angka.'])
                                    ->helperText('Pixel yang sama dengan di GTM. Kosong = pakai Meta Pixel ID di atas.'),
                                self::secret('tracking.meta_capi_token', 'Access token Conversions API'),
                                TextInput::make('tracking.meta_test_event_code')->label('Test event code (opsional)')
                                    ->helperText('Dari Events Manager → Test events. Kosongkan setelah uji coba selesai.')
                                    ->maxLength(40),
                            ]),
                        ]),
                    Section::make('Cloudflare Turnstile')
                        ->description('Isi site key dan secret key untuk mengaktifkan Turnstile di semua form. Salah satu kosong = Turnstile dilewati (honeypot dan rate limit tetap jalan).')
                        ->schema([
                            Grid::make(2)->schema([
                                TextInput::make('tracking.turnstile_site_key')->label('Site key')->maxLength(100),
                                self::secret('tracking.turnstile_secret_key', 'Secret key'),
                            ]),
                        ]),
                    Grid::make(2)->schema([
                        TextInput::make('tracking.google_verification')->label('Verifikasi Google Search Console'),
                        TextInput::make('tracking.bing_verification')->label('Verifikasi Bing Webmaster'),
                    ]),
                ]),
                Tab::make('Notifikasi lead')->visible(fn (): bool => self::canManageTracking())->schema([
                    TagsInput::make('notifications.emails')->label('Email penerima notifikasi lead')
                        ->placeholder('marketing@contoh.com')
                        ->helperText('Bisa lebih dari satu. Tekan Enter setelah tiap email. Kosong = tidak ada email notifikasi.')
                        ->nestedRecursiveRules(['email:rfc']),
                    TextInput::make('notifications.webhook_url')->label('Webhook URL (opsional)')->url()->maxLength(500)
                        ->helperText('POST JSON setiap ada lead baru (WA gateway, Google Sheet, dsb). Kosong = tidak dikirim.'),
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
                Tab::make('Halaman 404')->schema([
                    Fields::text('not_found.eyebrow', 'Eyebrow'),
                    Fields::text('not_found.title', 'Judul (H1)'),
                    Fields::textarea('not_found.message', 'Pesan'),
                    Repeater::make('not_found.links')->label('Link lanjutan')->schema([
                        TextInput::make('label')->required()->maxLength(60),
                        TextInput::make('url')->required()->maxLength(255),
                    ])->columns(2)->reorderableWithDragAndDrop()->maxItems(4),
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
     * Key rahasia di tab Tracking: disimpan terenkripsi, tidak pernah ditampilkan ulang.
     */
    public const SECRETS = ['meta_capi_token', 'turnstile_secret_key'];

    public const DOUBLE_COUNT_WARNING = 'Kosongkan jika Pixel/GA4 sudah dipasang lewat GTM, supaya event tidak terhitung dua kali.';

    /**
     * Tab yang hanya untuk Super Admin (tracking & tujuan notifikasi lead).
     */
    public const RESTRICTED = ['tracking', 'notifications'];

    /**
     * ID tracking, kode verifikasi, dan tujuan notifikasi tidak dikirim ke browser untuk role
     * selain Super Admin. Rahasia tidak dikirim ke browser untuk siapa pun.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (! self::canManageTracking()) {
            return array_diff_key($data, array_flip(self::RESTRICTED));
        }

        foreach (self::SECRETS as $key) {
            $data['tracking'][$key] = null;
        }

        return $data;
    }

    /**
     * Penolakan di sisi server: perubahan tracking/notifikasi dari role lain selalu dibuang,
     * walaupun request Livewire dimanipulasi. Nilai tersimpan dipertahankan.
     *
     * Rahasia: field kosong = pertahankan nilai lama; centang "hapus" = kosongkan; isi baru = enkripsi.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! self::canManageTracking()) {
            $data = array_diff_key($data, array_flip(self::RESTRICTED));
        } elseif (isset($data['tracking']) && is_array($data['tracking'])) {
            foreach (self::SECRETS as $key) {
                $clear = (bool) ($data['tracking'][$key.'_clear'] ?? false);
                $value = trim((string) ($data['tracking'][$key] ?? ''));
                unset($data['tracking'][$key.'_clear'], $data['tracking'][$key]);

                if ($clear) {
                    $data['tracking'][$key] = '';
                } elseif ($value !== '') {
                    $data['tracking'][$key] = Secret::encrypt($value);
                }
            }
        }

        return parent::mutateFormDataBeforeSave($data);
    }

    private static function secret(string $path, string $label): Group
    {
        $key = str($path)->after('tracking.')->toString();
        $stored = fn (): bool => filled(app(GlobalSettings::class)->tracking[$key] ?? null);

        return Group::make([
            TextInput::make($path)
                ->label($label)
                ->password()
                ->autocomplete('new-password')
                ->maxLength(500)
                ->placeholder(fn (): string => $stored() ? '•••••••• (tersimpan)' : 'Belum diisi')
                ->helperText(fn (): string => $stored()
                    ? 'Tersimpan terenkripsi dan tidak ditampilkan ulang. Kosongkan untuk mempertahankan, isi untuk mengganti.'
                    : 'Disimpan terenkripsi dan tidak ditampilkan ulang setelah disimpan.'),
            Checkbox::make($path.'_clear')
                ->label('Hapus nilai tersimpan')
                ->visible($stored),
        ]);
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
