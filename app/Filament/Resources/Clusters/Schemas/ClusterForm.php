<?php

namespace App\Filament\Resources\Clusters\Schemas;

use App\Enums\ClusterBadge;
use App\Enums\ClusterDisplay;
use App\Enums\ClusterStatus;
use App\Enums\PropertyType;
use App\Filament\Forms\Fields;
use App\Filament\Forms\SeoTab;
use App\Models\Benefit;
use App\Models\Cluster;
use App\Models\Kawasan;
use App\Support\Rupiah;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ClusterForm
{
    /**
     * Cluster "Daftar Cluster Lainnya saja": hanya nama & kawasan yang diisi, tab lain disembunyikan.
     */
    public static function listOnly(Get $get): bool
    {
        $display = $get('tampil_sebagai');

        return ($display instanceof ClusterDisplay ? $display : ClusterDisplay::tryFrom((string) $display)) === ClusterDisplay::Daftar;
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Tabs::make('Cluster')
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('Umum')->schema([
                            Select::make('tampil_sebagai')
                                ->label('Tampil sebagai')
                                ->options(ClusterDisplay::class)
                                ->default(ClusterDisplay::Halaman)
                                ->selectablePlaceholder(false)
                                ->dehydrateStateUsing(fn ($state) => $state ?? ClusterDisplay::Halaman)
                                ->live()
                                ->helperText('Halaman lengkap = punya halaman detail, kartu di /properti, filter, dan sitemap. Daftar Cluster Lainnya saja = hanya nama di halaman "Cluster Lainnya" (cukup isi nama dan kawasan).'),
                            Select::make('kawasan_id')
                                ->label('Kawasan')
                                ->options(fn (): array => self::kawasanOptions())
                                ->default(fn (): ?string => request()->query('kawasan_id'))
                                ->formatStateUsing(fn (?Cluster $record, $state) => $record?->exists && blank($state) ? Cluster::STANDALONE_FILTER : $state)
                                ->dehydrateStateUsing(fn ($state) => $state === Cluster::STANDALONE_FILTER ? null : $state)
                                ->required()
                                ->searchable()
                                ->helperText('Pilih "Cluster mandiri" kalau cluster tidak masuk kawasan mana pun.'),
                            TextInput::make('name')
                                ->label('Nama cluster')
                                ->required()
                                ->maxLength(255)
                                ->placeholder('Monard of The Armont'),
                            Grid::make(1)->hidden(self::listOnly(...))->schema([
                                Fields::gallery('Foto (foto pertama = foto utama kartu)')->required()->minItems(1),
                                Grid::make(2)->schema([
                                    Select::make('status')
                                        ->label('Status penjualan')
                                        ->options(ClusterStatus::class)
                                        ->placeholder('Tanpa status'),
                                    Select::make('badge')
                                        ->label('Badge')
                                        ->options(ClusterBadge::class)
                                        ->placeholder('Tanpa badge'),
                                ]),
                                RichEditor::make('description')
                                    ->label('Deskripsi rumah')
                                    ->placeholder('Cluster 2 lantai di kawasan The Armont dengan taman tematik, 5 menit ke AEON Mall BSD.')
                                    ->toolbarButtons([['bold', 'italic', 'link'], ['h2', 'h3'], ['bulletList', 'orderedList'], ['undo', 'redo']]),
                                TextInput::make('address')->label('Alamat')->maxLength(255)->placeholder('Jl. BSD Grand Boulevard, BSD City, Tangerang'),
                                Grid::make(2)->schema([
                                    DatePicker::make('tanggal_launching')
                                        ->label('Tanggal launching')
                                        ->native(false)
                                        ->displayFormat('j M Y')
                                        ->placeholder('7 Jul 2026')
                                        ->helperText('Dasar urutan "Terbaru". Tahun launching ikut terisi otomatis.'),
                                    TextInput::make('legality')->label('Legalitas')->placeholder('SHGB dipecah per unit · PBG sudah terbit')->maxLength(255),
                                ]),
                            ]),
                        ]),
                        Tab::make('Harga')->hidden(self::listOnly(...))->schema([
                            Section::make('Dihitung dari tipe rumah')
                                ->description('Rentang harga, luas tanah, kamar tidur, dan cicilan mulai di kartu cluster dihitung otomatis dari tipe rumah yang dipublikasikan (tab Tipe rumah di bawah).')
                                ->schema([
                                    Text::make(fn (?Cluster $record): string => $record && $record->house_types_count > 0
                                        ? collect([
                                            $record->house_types_count.' tipe',
                                            Rupiah::range($record->price_min, $record->price_max) ?? 'belum ada harga',
                                            $record->land_area_min ? 'LT '.($record->land_area_min === $record->land_area_max ? $record->land_area_min : $record->land_area_min.'–'.$record->land_area_max).' m²' : null,
                                            $record->installment_min ? 'cicilan mulai '.Rupiah::short($record->installment_min).'/bln' : null,
                                        ])->filter()->implode(' · ')
                                        : 'Belum ada tipe rumah yang dipublikasikan.'),
                                ])
                                ->compact(),
                            TextInput::make('booking_fee')->label('Booking fee')->numeric()->minValue(0)->prefix('Rp')->placeholder('10000000'),
                        ]),
                        Tab::make('Spesifikasi')->hidden(self::listOnly(...))->schema([
                            Repeater::make('facilities')
                                ->label('Fasilitas cluster')
                                ->helperText('Tampil sebagai daftar di Detail Rumah.')
                                ->simple(TextInput::make('text')->required()->maxLength(200)->placeholder('Clubhouse dengan kolam renang'))
                                ->reorderableWithDragAndDrop()
                                ->defaultItems(0)
                                ->addActionLabel('Tambah fasilitas'),
                            Repeater::make('specifications')
                                ->label('Spesifikasi material')
                                ->schema([
                                    TextInput::make('label')->label('Bagian')->required()->maxLength(60)->placeholder('Lantai'),
                                    TextInput::make('value')->label('Material')->required()->maxLength(120)->placeholder('Granit 60×60'),
                                ])
                                ->columns(2)
                                ->reorderableWithDragAndDrop()
                                ->defaultItems(0)
                                ->addActionLabel('Tambah baris'),
                        ]),
                        Tab::make('Video & Brosur')->hidden(self::listOnly(...))->schema([
                            Grid::make(2)->schema([
                                TextInput::make('video_url')->label('URL video')->url()->maxLength(255)->placeholder('https://www.youtube.com/watch?v=…'),
                                TextInput::make('tour_360_url')->label('URL virtual tour 360°')->url()->maxLength(255)->placeholder('https://my.matterport.com/show/?m=…'),
                            ]),
                            Grid::make(2)->schema([
                                Fields::pdf('brochure', 'Brosur (PDF)'),
                                Fields::pdf('pricelist', 'Pricelist (PDF)'),
                            ]),
                        ]),
                        Tab::make('Promo & Benefit')
                            ->hidden(self::listOnly(...))
                            ->badge(fn (?Cluster $record): ?int => $record?->clusterBenefits()->count() ?: null)
                            ->schema([
                                Text::make('Benefit dari Bank Benefit yang berlaku di cluster ini. Tampil di Detail Rumah (dengan tanda *), sebagai chip di kartu, dan memberi badge "Promo". Tanpa tanggal berakhir: lepas benefit kalau sudah tidak berlaku.'),
                                Repeater::make('clusterBenefits')
                                    ->label('Benefit')
                                    ->relationship()
                                    ->orderColumn('urutan')
                                    ->reorderableWithDragAndDrop()
                                    ->schema([
                                        Select::make('benefit_id')
                                            ->label('Benefit')
                                            ->options(fn (): array => Benefit::groupedOptions())
                                            ->searchable()
                                            ->required()
                                            ->distinct()
                                            ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                                        TextInput::make('teks_tampil')
                                            ->label('Teks tampil')
                                            ->placeholder('Opsional, contoh: Diskon hingga 13%')
                                            ->helperText('Kosong = nama benefit.')
                                            ->maxLength(40),
                                    ])
                                    ->columns(2)
                                    ->defaultItems(0)
                                    ->addActionLabel('Tambah benefit')
                                    ->itemLabel(fn (array $state): ?string => filled($state['benefit_id'] ?? null)
                                        ? (Benefit::query()->find($state['benefit_id'])?->name ?? null)
                                        : null),
                            ]),
                        Tab::make('Marketing')->hidden(self::listOnly(...))->schema([
                            Text::make('Kosongkan untuk memakai marketing default (Pengaturan → Properti). Semua tombol WhatsApp memakai nomor WA di Pengaturan Umum.'),
                            Grid::make(2)->schema([
                                TextInput::make('marketing_name')->label('Nama marketing')->maxLength(80)->placeholder('Rina'),
                            ]),
                        ]),
                        Tab::make('Publikasi')->hidden(self::listOnly(...))->schema([
                            Toggle::make('is_published')->label('Dipublikasikan')->default(true),
                            Toggle::make('is_featured')->label('Unggulan (tampil di Beranda)'),
                        ]),
                        Tab::make('Internal')
                            ->hidden(self::listOnly(...))
                            ->icon(Heroicon::OutlinedLockClosed)
                            ->badge(fn (?Cluster $record): ?int => $record?->perlu_dilengkapi_count ?: null)
                            ->badgeColor('warning')
                            ->schema([
                                Text::make('Hanya terlihat di admin, tidak tampil di website.'),
                                Select::make('prioritas')
                                    ->label('Prioritas')
                                    ->options(array_combine(range(1, 10), range(1, 10)))
                                    ->placeholder('Tanpa prioritas')
                                    ->helperText('1 = paling diprioritaskan untuk dilengkapi & dipasarkan.'),
                                Textarea::make('catatan_internal')
                                    ->label('Catatan internal')
                                    ->rows(5),
                                Repeater::make('perlu_dilengkapi')
                                    ->label('Perlu dilengkapi')
                                    ->helperText('Centang yang sudah dilengkapi. Item yang belum dicentang dihitung di kolom "Belum lengkap" pada daftar cluster.')
                                    ->schema([
                                        Checkbox::make('selesai')->label('Selesai')->inline(),
                                        TextInput::make('item')->hiddenLabel()->required()->maxLength(255)->columnSpan(5),
                                    ])
                                    ->columns(6)
                                    ->reorderableWithDragAndDrop()
                                    ->defaultItems(0)
                                    ->addActionLabel('Tambah item'),
                            ]),
                    ]),
                Fields::advanced([
                    Grid::make(2)->schema([
                        Fields::slug(Cluster::RESERVED_SLUGS)->placeholder('monard-of-the-armont'),
                        Select::make('property_type')
                            ->label('Tipe properti (filter)')
                            ->options(PropertyType::class)
                            ->default(PropertyType::Rumah)
                            ->selectablePlaceholder(false),
                    ]),
                    TextInput::make('building_type')->label('Jenis bangunan')->placeholder('Rumah 2 lantai')->maxLength(80),
                    Textarea::make('summary')
                        ->label('Ringkasan (kartu & meta description)')
                        ->rows(2)
                        ->maxLength(300)
                        ->placeholder('Kosong = otomatis dari deskripsi.'),
                    SeoTab::fields(),
                ])->hidden(self::listOnly(...)),
            ]);
    }

    /**
     * Pilihan kawasan untuk filter tabel, termasuk "Cluster mandiri".
     *
     * @return array<string, string>
     */
    public static function kawasanFilterOptions(): array
    {
        return self::kawasanOptions();
    }

    /**
     * Pilihan kawasan di form: "Cluster mandiri" + kawasan.
     *
     * @return array<string, string>
     */
    public static function kawasanOptions(): array
    {
        return [Cluster::STANDALONE_FILTER => 'Cluster mandiri (tanpa kawasan)'] + Kawasan::query()->orderBy('sort_order')->pluck('name', 'id')->all();
    }
}
