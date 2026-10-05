<?php

namespace App\Filament\Pages\Settings;

use App\Filament\Forms\Fields;
use App\Settings\GlobalSettings;
use App\Support\Indexing;
use App\Support\StructuredData;
use App\Support\WhatsApp;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;

/**
 * Pengaturan Umum: nomor & template pesan WhatsApp, kontak, logo, GA4, dan verifikasi Search Console.
 * Header, footer, CTA, label, dan halaman 404 memakai teks tetap di kode (GlobalSettings::editable()).
 */
class ManageGlobalSettings extends PageSettingsPage
{
    protected static string $settings = GlobalSettings::class;

    protected static ?string $title = 'Pengaturan Umum';

    protected static ?string $navigationLabel = 'Pengaturan Umum';

    protected static ?string $slug = 'pengaturan/umum';

    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $publicPath = null;

    public function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('WhatsApp')->schema([
                TextInput::make('contact.whatsapp')->label('Nomor WhatsApp (62…)')->tel()->required()->regex('/^62\d{8,13}$/')
                    ->placeholder('6281234567890')
                    ->validationMessages([
                        'required' => 'Nomor WhatsApp wajib diisi: semua tombol WhatsApp di website memakai nomor ini.',
                        'regex' => 'Format nomor: 62 diikuti 8–13 digit, tanpa spasi.',
                    ])
                    ->helperText('Satu nomor untuk semua tombol WhatsApp di website (detail cluster, promo, survey, tombol melayang, menu Kontak, footer).'),
                Section::make('Template pesan otomatis')
                    ->description('Kosong = pakai teks bawaan. Tekan Enter untuk baris baru.')
                    ->compact()
                    ->schema([
                        self::template('contact.whatsapp_cluster_message', 'Detail cluster (tombol utama & tombol melayang di Detail Rumah)', 'cluster'),
                        self::template('contact.whatsapp_promo_message', 'Section Promo & Benefit', 'cluster'),
                        self::template('contact.whatsapp_survey_message', 'Tombol jadwal survey', 'cluster'),
                        self::template('contact.whatsapp_kawasan_message', 'Halaman kawasan', 'kawasan'),
                        self::template('contact.whatsapp_message', 'Tombol melayang & menu Kontak (halaman lain)', 'home'),
                    ]),
            ]),
            Section::make('Kontak')->schema([
                Grid::make(2)->schema([
                    TextInput::make('contact.hotline')->label('Hotline')->maxLength(40)->placeholder('021 5315 9000'),
                    TextInput::make('contact.phone')->label('Telepon kantor')->maxLength(40)->placeholder('021 5315 9000'),
                    TextInput::make('contact.email')->label('Email')->email()->maxLength(120)->placeholder('marketing@bsdcity.com'),
                    TextInput::make('contact.opening_hours')->label('Jam buka')->maxLength(80)->placeholder('Setiap hari, 09.00–17.00'),
                ]),
                Fields::textarea('contact.office_address', 'Alamat kantor pemasaran', 2)
                    ->placeholder('Marketing Gallery BSD City, Jl. Grand Boulevard, BSD City, Tangerang 15345'),
                TextInput::make('contact.maps_url')->label('Link Google Maps kantor')->url()->maxLength(500)
                    ->placeholder('https://maps.app.goo.gl/…')
                    ->helperText('Tampil di footer semua halaman. Kosong = link tidak tampil.'),
                TextInput::make('identity.company_name')->label('Nama perusahaan (footer & data Google)')->maxLength(120)->placeholder('PT Bumi Serpong Damai Tbk'),
                Repeater::make('footer.social')->label('Media sosial')
                    ->schema([
                        TextInput::make('label')->label('Nama')->required()->maxLength(40)->placeholder('Instagram'),
                        TextInput::make('url')->label('URL')->required()->url()->maxLength(255)->placeholder('https://instagram.com/bsdcity'),
                    ])
                    ->columns(2)->reorderableWithDragAndDrop()->defaultItems(0)->addActionLabel('Tambah media sosial'),
            ]),
            Section::make('Logo')->schema([
                Grid::make(3)->schema([
                    self::logo('identity.logo_light', 'Logo (latar terang)'),
                    self::logo('identity.logo_dark', 'Logo (latar gelap)'),
                    self::logo('identity.favicon', 'Favicon'),
                ]),
            ]),
            Section::make('Google')->schema([
                // Info saja (bukan field): diatur lewat SITE_INDEXABLE di .env server, tidak dari admin.
                Text::make(fn (): string => 'Status indeks: '.Indexing::label())
                    ->color(fn (): string => Indexing::allowed() ? 'success' : 'warning')
                    ->weight(FontWeight::SemiBold),
                TextInput::make('tracking.ga4_id')->label('GA4 Measurement ID')->placeholder('G-XXXXXXXXXX')
                    ->regex('/^G-[A-Z0-9]{4,20}$/')
                    ->validationMessages(['regex' => 'Format: G- diikuti huruf/angka, mis. G-AB12CD34EF.'])
                    ->helperText('GA4 dipasang langsung (gtag.js). Kosong = tidak ada script tracking yang dimuat. Konversi utama: event click_whatsapp.'),
                TextInput::make('tracking.google_verification')->label('Verifikasi Google Search Console')->maxLength(200)
                    ->placeholder('kode dari meta tag google-site-verification')
                    ->helperText('Isi bagian content="…" saja dari meta tag verifikasi.'),
            ]),
            Fields::advanced([
                FileUpload::make('seo.default_og_image')->label('Gambar share default (1200×630)')->disk('public')->directory('seo')->visibility('public')->image()
                    ->helperText('Dipakai halaman yang tidak punya foto sendiri saat dibagikan.'),
                Grid::make(2)->schema([
                    TextInput::make('contact.latitude')->label('Latitude kantor')->numeric()->placeholder('-6.3017'),
                    TextInput::make('contact.longitude')->label('Longitude kantor')->numeric()->placeholder('106.6527'),
                ]),
            ]),
        ]);
    }

    /**
     * Field template pesan WhatsApp + daftar placeholder + contoh hasil (ikut berubah saat diketik).
     *
     * @param  'cluster'|'kawasan'|'home'  $example  halaman contoh untuk preview
     */
    private static function template(string $path, string $label, string $example): Textarea
    {
        $key = substr($path, strlen('contact.'));

        return Textarea::make($path)->label($label)->rows(3)
            ->placeholder(GlobalSettings::defaults()['contact'][$key])
            ->live(debounce: 600)
            ->helperText(function (?string $state) use ($key, $example): HtmlString {
                $template = filled($state) ? $state : GlobalSettings::defaults()['contact'][$key];
                $values = match ($example) {
                    'cluster' => ['nama_cluster' => 'Castilo at Terravia', 'nama_kawasan' => 'Terravia', 'judul_halaman' => 'Castilo at Terravia, Rumah 2 lantai | BSD City', 'path' => '/properti/castilo-at-terravia'],
                    'kawasan' => ['nama_cluster' => 'BSD City', 'nama_kawasan' => 'Greenwich Park', 'judul_halaman' => 'Greenwich Park | BSD City', 'path' => '/properti/kawasan/greenwich-park'],
                    default => ['nama_cluster' => 'BSD City', 'nama_kawasan' => 'BSD City', 'judul_halaman' => 'Properti BSD City | BSD City', 'path' => '/properti'],
                };
                $preview = WhatsApp::render($template, [...$values, 'link_halaman' => StructuredData::url($values['path'])]);
                $placeholders = collect(WhatsApp::PLACEHOLDERS)->map(fn (string $info, string $tag): string => '<code>'.e($tag).'</code> = '.e($info))->implode('<br>');

                return new HtmlString('Placeholder:<br>'.$placeholders
                    .'<br><br><strong>Contoh hasil</strong> (halaman '.e($values['path']).'):<br>'.nl2br(e($preview)));
            });
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
