<?php

namespace App\Filament\Forms;

use App\Support\IconOptions;
use Closure;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Komponen form reusable untuk resource & settings page (brief 7 & 7A).
 */
class Fields
{
    /**
     * Judul + slug otomatis (tetap bisa diedit). Redirect 301 dari slug lama dibuat oleh model.
     *
     * @param  list<string>  $reserved
     * @return array<int, TextInput>
     */
    public static function titleAndSlug(string $source = 'name', string $label = 'Nama', array $reserved = [], ?Closure $modifyUniqueRule = null): array
    {
        return [
            TextInput::make($source)
                ->label($label)
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state): void {
                    // Slug hanya ikut berubah selama belum diedit manual.
                    if (filled($get('slug')) && $get('slug') !== Str::slug((string) $old)) {
                        return;
                    }

                    $set('slug', Str::slug((string) $state));
                }),
            TextInput::make('slug')
                ->label('Slug URL')
                ->required()
                ->maxLength(255)
                ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                ->notIn($reserved)
                ->unique(ignoreRecord: true, modifyRuleUsing: $modifyUniqueRule)
                ->validationMessages([
                    'regex' => 'Slug hanya boleh huruf kecil, angka, dan tanda hubung.',
                    'not_in' => 'Slug ini dipakai sistem, pilih slug lain.',
                ])
                ->helperText('Huruf kecil dan tanda hubung. Kalau diubah, redirect 301 dari URL lama dibuat otomatis.'),
        ];
    }

    /**
     * Satu gambar (media library) + alt text yang wajib diisi kalau ada gambar.
     * Nama file diubah jadi slug dari alt text.
     *
     * @return array<int, mixed>
     */
    public static function image(string $collection, string $altField, string $label = 'Gambar', ?string $helper = null): array
    {
        return [
            SpatieMediaLibraryFileUpload::make($collection)
                ->label($label)
                ->collection($collection)
                ->disk('public')
                ->image()
                ->imageEditor()
                ->maxSize(8192)
                ->live()
                ->helperText($helper)
                ->getUploadedFileNameForStorageUsing(fn (TemporaryUploadedFile $file, Get $get): string => self::descriptiveFileName($file, $get($altField)))
                ->customProperties(fn (Get $get): array => ['alt' => $get($altField)]),
            TextInput::make($altField)
                ->label('Alt text '.Str::lower($label))
                ->maxLength(255)
                ->required(fn (Get $get): bool => filled($get($collection)))
                ->helperText('Wajib diisi kalau ada gambar. Jelaskan isi gambar untuk pembaca layar & SEO.'),
        ];
    }

    /**
     * File PDF (brosur / pricelist).
     */
    public static function pdf(string $collection, string $label): SpatieMediaLibraryFileUpload
    {
        return SpatieMediaLibraryFileUpload::make($collection)
            ->label($label)
            ->collection($collection)
            ->disk('public')
            ->acceptedFileTypes(['application/pdf'])
            ->maxSize(20480)
            ->downloadable()
            ->openable();
    }

    /**
     * Galeri foto: satu item per foto dengan alt text wajib, bisa diurutkan drag.
     */
    public static function gallery(string $label = 'Galeri foto'): Repeater
    {
        return Repeater::make('galleryItems')
            ->label($label)
            ->relationship()
            ->orderColumn('sort_order')
            ->reorderableWithDragAndDrop()
            ->collapsible()
            ->grid(2)
            ->defaultItems(0)
            ->addActionLabel('Tambah foto')
            ->itemLabel(fn (array $state): ?string => $state['alt'] ?? null)
            ->schema([
                SpatieMediaLibraryFileUpload::make('image')
                    ->label('Foto')
                    ->collection('image')
                    ->disk('public')
                    ->image()
                    ->imageEditor()
                    ->maxSize(8192)
                    ->required()
                    ->getUploadedFileNameForStorageUsing(fn (TemporaryUploadedFile $file, Get $get): string => self::descriptiveFileName($file, $get('alt'))),
                TextInput::make('alt')
                    ->label('Alt text')
                    ->required()
                    ->maxLength(255),
                TextInput::make('caption')
                    ->label('Keterangan (opsional)')
                    ->maxLength(255),
            ]);
    }

    /**
     * Gambar untuk settings halaman (disimpan sebagai path di disk public) + alt text.
     *
     * @return array<int, mixed>
     */
    public static function settingsImage(string $path, string $altPath, string $label = 'Gambar'): array
    {
        return [
            FileUpload::make($path)
                ->label($label)
                ->disk('public')
                ->directory('settings')
                ->visibility('public')
                ->image()
                ->imageEditor()
                ->maxSize(8192)
                ->live()
                ->getUploadedFileNameForStorageUsing(fn (TemporaryUploadedFile $file, Get $get): string => self::descriptiveFileName($file, $get($altPath))),
            TextInput::make($altPath)
                ->label('Alt text '.Str::lower($label))
                ->maxLength(255)
                ->required(fn (Get $get): bool => filled($get($path))),
        ];
    }

    public static function icon(string $name = 'icon', string $label = 'Ikon'): Select
    {
        return Select::make($name)->label($label)->options(IconOptions::all())->searchable();
    }

    /**
     * Toggle "Tampilkan section".
     */
    public static function enabled(string $section): Toggle
    {
        return Toggle::make("{$section}.enabled")
            ->label('Tampilkan section')
            ->helperText('Section yang dimatikan tidak dirender di halaman.')
            ->default(true)
            ->columnSpanFull();
    }

    public static function text(string $path, string $label, ?string $helper = null): TextInput
    {
        return TextInput::make($path)->label($label)->maxLength(255)->helperText($helper);
    }

    public static function textarea(string $path, string $label, int $rows = 3, ?string $helper = null): Textarea
    {
        return Textarea::make($path)->label($label)->rows($rows)->helperText($helper);
    }

    /**
     * Pasangan label + URL tombol.
     */
    public static function button(string $prefix, string $label, ?string $urlHelper = null): Grid
    {
        return Grid::make(2)->schema([
            TextInput::make("{$prefix}_label")->label("Label {$label}")->maxLength(80),
            TextInput::make("{$prefix}_url")->label("URL {$label}")->maxLength(255)->helperText($urlHelper),
        ]);
    }

    /**
     * Sumber data section: otomatis (unggulan/terbaru) atau pilih manual + urutan drag.
     *
     * @param  Closure(): array<int|string, string>  $options
     * @return array<int, mixed>
     */
    public static function dataSource(string $section, Closure $options, string $autoLabel = 'Otomatis (unggulan / terbaru)', bool $withLimit = true): array
    {
        return array_values(array_filter([
            ToggleButtons::make("{$section}.source")
                ->label('Sumber data')
                ->options(['auto' => $autoLabel, 'manual' => 'Pilih manual'])
                ->inline()
                ->live()
                ->default('auto'),
            Repeater::make("{$section}.items")
                ->label('Item yang ditampilkan (urutkan dengan drag)')
                ->simple(
                    Select::make('id')->label('Item')->options($options)->searchable()->required()->distinct(),
                )
                ->reorderableWithDragAndDrop()
                ->defaultItems(0)
                ->addActionLabel('Tambah item')
                ->visible(fn (Get $get): bool => $get("{$section}.source") === 'manual')
                ->columnSpanFull(),
            $withLimit
                ? TextInput::make("{$section}.limit")->label('Jumlah item')->numeric()->minValue(1)->maxValue(24)
                : null,
        ]));
    }

    /**
     * CTA per halaman: pakai CTA global atau override.
     */
    public static function cta(string $section = 'cta'): Section
    {
        return Section::make('CTA')
            ->schema([
                self::enabled($section),
                Toggle::make("{$section}.use_global")
                    ->label('Pakai CTA global')
                    ->helperText('Teks diambil dari Pengaturan Global → CTA global.')
                    ->live()
                    ->default(true),
                Grid::make(1)
                    ->schema([
                        self::text("{$section}.eyebrow", 'Eyebrow'),
                        self::text("{$section}.title", 'Judul'),
                        self::textarea("{$section}.description", 'Deskripsi'),
                    ])
                    ->hidden(fn (Get $get): bool => (bool) $get("{$section}.use_global")),
            ]);
    }

    /**
     * Field SEO untuk settings halaman (+ preview snippet Google).
     *
     * @return array<int, mixed>
     */
    public static function settingsSeo(string $section = 'seo', string $path = '/', bool $lockNoindex = false): array
    {
        return [
            self::metaTitle("{$section}.meta_title"),
            self::metaDescription("{$section}.meta_description"),
            FileUpload::make("{$section}.og_image")
                ->label('Gambar Open Graph (1200×630)')
                ->disk('public')
                ->directory('seo')
                ->visibility('public')
                ->image()
                ->maxSize(4096),
            TextInput::make("{$section}.canonical_url")
                ->label('Canonical override (opsional)')
                ->url()
                ->maxLength(255)
                ->helperText('Kosongkan untuk memakai URL halaman ini.'),
            Toggle::make("{$section}.noindex")
                ->label('Noindex (sembunyikan dari mesin pencari)')
                ->disabled($lockNoindex)
                ->helperText($lockNoindex ? 'Dikunci: halaman ini selalu noindex.' : null),
            self::snippetPreview(
                fn (Get $get) => $get("{$section}.meta_title"),
                fn (Get $get) => $get("{$section}.meta_description"),
                fn () => $path,
            ),
        ];
    }

    public static function metaTitle(string $path = 'meta_title'): TextInput
    {
        return TextInput::make($path)
            ->label('Meta title')
            ->maxLength(255)
            ->live(debounce: 400)
            ->hint(fn (?string $state): string => mb_strlen((string) $state).'/60')
            ->hintColor(fn (?string $state): string => mb_strlen((string) $state) > 60 ? 'danger' : 'gray')
            ->helperText('Idealnya ≤ 60 karakter. Kosongkan untuk memakai pola default.');
    }

    public static function metaDescription(string $path = 'meta_description'): Textarea
    {
        return Textarea::make($path)
            ->label('Meta description')
            ->rows(3)
            ->maxLength(320)
            ->live(debounce: 400)
            ->hint(fn (?string $state): string => mb_strlen((string) $state).'/160')
            ->hintColor(fn (?string $state): string => mb_strlen((string) $state) > 160 ? 'danger' : 'gray')
            ->helperText('Idealnya ≤ 160 karakter. Kosongkan untuk memakai ringkasan otomatis.');
    }

    /**
     * Preview hasil pencarian Google.
     */
    public static function snippetPreview(Closure $title, Closure $description, Closure $path): Html
    {
        return Html::make(function (Get $get) use ($title, $description, $path): HtmlString {
            $t = e(Str::limit((string) ($title($get) ?: 'Judul halaman'), 60));
            $d = e(Str::limit((string) ($description($get) ?: 'Deskripsi otomatis dari ringkasan konten.'), 160));
            $url = e(rtrim((string) config('app.url'), '/').$path($get));

            return new HtmlString(<<<HTML
                <div style="border:1px solid rgb(0 0 0 / .1);border-radius:12px;padding:14px 16px;font-family:arial,sans-serif;max-width:600px">
                    <div style="font-size:12px;color:#4d5156;margin-bottom:4px">Preview Google</div>
                    <div style="font-size:14px;color:#202124">{$url}</div>
                    <div style="font-size:20px;line-height:1.3;color:#1a0dab;margin:2px 0">{$t}</div>
                    <div style="font-size:14px;line-height:1.58;color:#4d5156">{$d}</div>
                </div>
            HTML);
        })->columnSpanFull();
    }

    /**
     * Nama file deskriptif dari alt text / nama asli: "fasad-vega-garden-a1b2.jpg".
     */
    public static function descriptiveFileName(TemporaryUploadedFile $file, ?string $alt): string
    {
        $base = Str::slug(Str::limit((string) ($alt ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)), 60, '')) ?: 'gambar';

        return $base.'-'.Str::lower(Str::random(4)).'.'.$file->getClientOriginalExtension();
    }
}
