<?php

namespace App\Filament\Resources\Clusters\Schemas;

use App\Enums\ClusterBadge;
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
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
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
use Illuminate\Database\Eloquent\Builder;

class ClusterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Tabs::make('Cluster')
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('Umum')->schema([
                            Select::make('kawasan_id')
                                ->label('Kawasan')
                                ->relationship('kawasan', 'name', fn (Builder $query) => $query->orderBy('sort_order'))
                                ->placeholder('Cluster mandiri (tanpa kawasan)')
                                ->default(fn (): ?string => request()->query('kawasan_id'))
                                ->helperText('Kosongkan untuk cluster mandiri. URL cluster tidak ikut berubah kalau kawasannya diganti.')
                                ->preload()
                                ->searchable(),
                            Grid::make(2)->schema(Fields::titleAndSlug('name', 'Nama cluster', Cluster::RESERVED_SLUGS)),
                            Grid::make(3)->schema([
                                TextInput::make('building_type')
                                    ->label('Jenis bangunan')
                                    ->placeholder('Rumah 2 lantai')
                                    ->maxLength(80),
                                Select::make('property_type')
                                    ->label('Tipe properti (filter)')
                                    ->options(PropertyType::class)
                                    ->default(PropertyType::Rumah)
                                    ->required(),
                                Select::make('status')
                                    ->label('Status penjualan (opsional)')
                                    ->options(ClusterStatus::class)
                                    ->placeholder('Tanpa status')
                                    ->helperText('Kosong = tidak ada badge status di kartu & Detail Rumah.'),
                            ]),
                            Select::make('badge')
                                ->label('Badge')
                                ->options(ClusterBadge::class)
                                ->placeholder('Tanpa badge'),
                            Textarea::make('summary')
                                ->label('Ringkasan (meta description & kartu)')
                                ->rows(2)
                                ->maxLength(300),
                            RichEditor::make('description')
                                ->label('Deskripsi rumah')
                                ->toolbarButtons([['bold', 'italic', 'link'], ['h2', 'h3'], ['bulletList', 'orderedList'], ['undo', 'redo']]),
                            TextInput::make('address')->label('Alamat')->maxLength(255),
                            Grid::make(2)->schema([
                                DatePicker::make('tanggal_launching')
                                    ->label('Tanggal launching')
                                    ->native(false)
                                    ->displayFormat('j M Y')
                                    ->helperText('Dasar urutan "Terbaru" di listing. Kosong = paling bawah.'),
                                TextInput::make('launch_year')->label('Tahun launching')->numeric()->minValue(1900)->maxValue(2100),
                            ]),
                            TextInput::make('legality')->label('Legalitas')->placeholder('SHGB dipecah per unit · PBG sudah terbit')->maxLength(255),
                        ]),
                        Tab::make('Harga')->schema([
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
                            TextInput::make('booking_fee')->label('Booking fee')->numeric()->minValue(0)->prefix('Rp'),
                            TextInput::make('price_note')->label('Catatan harga')->placeholder('Kosong = pakai default di Pengaturan Halaman → Detail Rumah')->maxLength(255),
                            TextInput::make('installment_note')->label('Catatan cicilan')->maxLength(255),
                            TextInput::make('booking_fee_note')->label('Catatan booking fee')->maxLength(255),
                        ]),
                        Tab::make('Spesifikasi')->schema([
                            Repeater::make('facilities')
                                ->label('Fasilitas cluster')
                                ->helperText('Tampil sebagai daftar di Detail Rumah.')
                                ->simple(TextInput::make('text')->required()->maxLength(200))
                                ->reorderableWithDragAndDrop()
                                ->defaultItems(0)
                                ->addActionLabel('Tambah fasilitas'),
                            Repeater::make('specifications')
                                ->label('Spesifikasi material')
                                ->schema([
                                    TextInput::make('label')->label('Bagian')->required()->maxLength(60),
                                    TextInput::make('value')->label('Material')->required()->maxLength(120),
                                ])
                                ->columns(2)
                                ->reorderableWithDragAndDrop()
                                ->defaultItems(0)
                                ->addActionLabel('Tambah baris'),
                        ]),
                        Tab::make('Media')->schema([
                            Fields::gallery('Galeri foto (foto pertama = foto utama kartu)'),
                            Grid::make(2)->schema([
                                TextInput::make('video_url')->label('URL video')->url()->maxLength(255),
                                TextInput::make('tour_360_url')->label('URL virtual tour 360°')->url()->maxLength(255),
                            ]),
                            Grid::make(2)->schema([
                                Fields::pdf('brochure', 'Brosur (PDF)'),
                                Fields::pdf('pricelist', 'Pricelist (PDF)'),
                            ]),
                        ]),
                        Tab::make('Promo & Benefit')
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
                        Tab::make('Marketing')->schema([
                            Section::make('Marketing cluster')
                                ->description('Kosongkan untuk memakai marketing default di Pengaturan Halaman → Detail Rumah.')
                                ->schema([
                                    Grid::make(3)->schema([
                                        TextInput::make('marketing_name')->label('Nama')->maxLength(80),
                                        TextInput::make('marketing_title')->label('Jabatan')->maxLength(80),
                                        TextInput::make('marketing_whatsapp')->label('WhatsApp (62…)')->tel()->maxLength(20),
                                    ]),
                                    SpatieMediaLibraryFileUpload::make('marketing_photo')
                                        ->label('Foto marketing')
                                        ->collection('marketing_photo')
                                        ->disk('public')
                                        ->image()
                                        ->avatar()
                                        ->customProperties(fn (Get $get): array => ['alt' => 'Foto '.($get('marketing_name') ?: 'marketing')]),
                                ]),
                        ]),
                        Tab::make('Publikasi')->schema([
                            Toggle::make('is_published')->label('Dipublikasikan')->default(true),
                            DateTimePicker::make('published_at')->label('Tanggal terbit')->native(false)->default(now()),
                            Toggle::make('is_featured')->label('Unggulan (tampil di Beranda)'),
                            TextInput::make('sort_order')->label('Urutan')->numeric()->default(0),
                        ]),
                        Tab::make('Internal')
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
                        SeoTab::make(fn (Get $get): string => '/properti/'.$get('../slug')),
                    ]),
            ]);
    }

    /**
     * Pilihan kawasan untuk filter tabel, termasuk "Cluster mandiri".
     *
     * @return array<string, string>
     */
    public static function kawasanFilterOptions(): array
    {
        return [Cluster::STANDALONE_FILTER => 'Cluster mandiri'] + Kawasan::query()->orderBy('sort_order')->pluck('name', 'id')->all();
    }
}
