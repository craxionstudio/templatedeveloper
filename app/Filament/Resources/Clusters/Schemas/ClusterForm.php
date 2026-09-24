<?php

namespace App\Filament\Resources\Clusters\Schemas;

use App\Enums\ClusterBadge;
use App\Enums\ClusterStatus;
use App\Enums\PromoPlacement;
use App\Enums\PropertyType;
use App\Filament\Forms\Fields;
use App\Filament\Forms\SeoTab;
use App\Models\Cluster;
use App\Models\Kawasan;
use App\Support\Rupiah;
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
                                    ->label('Status')
                                    ->options(ClusterStatus::class)
                                    ->default(ClusterStatus::ReadyStock)
                                    ->required(),
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
                            TextInput::make('legality')->label('Legalitas')->placeholder('SHGB dipecah per unit · PBG sudah terbit')->maxLength(255),
                        ]),
                        Tab::make('Harga')->schema([
                            Section::make('Dihitung dari tipe rumah')
                                ->description('Rentang harga, luas tanah, kamar tidur, dan cicilan mulai di kartu cluster dihitung otomatis dari tipe rumah yang dipublikasikan (tab Tipe rumah di bawah).')
                                ->schema([
                                    Text::make(fn (?Cluster $record): string => $record && $record->house_types_count > 0
                                        ? sprintf(
                                            '%d tipe · %s · LT %s m² · cicilan mulai %s',
                                            $record->house_types_count,
                                            Rupiah::range($record->price_min, $record->price_max),
                                            $record->land_area_min === $record->land_area_max ? $record->land_area_min : $record->land_area_min.'–'.$record->land_area_max,
                                            Rupiah::short($record->installment_min).'/bln',
                                        )
                                        : 'Belum ada tipe rumah yang dipublikasikan.'),
                                ])
                                ->compact(),
                            TextInput::make('booking_fee')->label('Booking fee')->numeric()->minValue(0)->prefix('Rp'),
                            TextInput::make('price_note')->label('Catatan harga')->placeholder('Kosong = pakai default di Pengaturan Halaman → Detail Rumah')->maxLength(255),
                            TextInput::make('installment_note')->label('Catatan cicilan')->maxLength(255),
                            TextInput::make('booking_fee_note')->label('Catatan booking fee')->maxLength(255),
                        ]),
                        Tab::make('Spesifikasi')->schema([
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
                        Tab::make('Marketing & Promo')->schema([
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
                            Select::make('promos')
                                ->label('Promo yang berlaku')
                                ->relationship('promos', 'title', fn (Builder $query) => $query->where('placement', PromoPlacement::Detail->value))
                                ->multiple()
                                ->preload(),
                        ]),
                        Tab::make('Publikasi')->schema([
                            Toggle::make('is_published')->label('Dipublikasikan')->default(true),
                            DateTimePicker::make('published_at')->label('Tanggal terbit')->native(false)->default(now()),
                            Toggle::make('is_featured')->label('Unggulan (tampil di Beranda)'),
                            TextInput::make('sort_order')->label('Urutan')->numeric()->default(0),
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
