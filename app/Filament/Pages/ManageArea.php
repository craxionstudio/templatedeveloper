<?php

namespace App\Filament\Pages;

use App\Filament\Forms\Fields;
use App\Models\Area;
use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

/**
 * Profil lokasi / kota mandiri (satu baris). Dipakai di Beranda (Keunggulan Wilayah)
 * dan header Produk Listing.
 */
class ManageArea extends SingletonRecordPage
{
    protected static ?string $title = 'Profil Lokasi';

    protected static ?string $navigationLabel = 'Profil Lokasi';

    protected static ?string $slug = 'profil-lokasi';

    protected static string|UnitEnum|null $navigationGroup = 'Properti';

    protected static ?int $navigationSort = 5;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static function resolveRecord(): Model
    {
        return Area::current();
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Lokasi')->schema([
                Grid::make(2)->schema([
                    TextInput::make('name')->label('Nama lokasi / kota mandiri')->required()->maxLength(255)->placeholder('BSD City'),
                    TextInput::make('location')->label('Lokasi')->placeholder('Serpong, Tangerang Selatan')->maxLength(255),
                ]),
                Textarea::make('description')->label('Deskripsi')->rows(3)->placeholder('Kota mandiri seluas sekitar 6.000 hektare di Serpong.'),
                TextInput::make('area_ha')->label('Luas (ha)')->numeric()->minValue(0)->suffix('ha')->placeholder('6000'),
                Fields::imageOnly('hero', 'Foto hero / aerial'),
            ]),
            Section::make('Peta & aksesibilitas')->schema([
                Textarea::make('map_embed_url')->label('URL embed Google Maps')->rows(2)->placeholder('https://www.google.com/maps/embed?pb=…'),
                Fields::imageOnly('map', 'Gambar peta statis (facade)'),
                Grid::make(2)->schema([
                    TextInput::make('map_badge_label')->label('Label badge di peta')->placeholder('Akses tol langsung')->maxLength(60),
                    TextInput::make('map_badge_value')->label('Nilai badge')->placeholder('± 5 menit')->maxLength(30),
                ]),
            ]),
            Section::make('Poin keunggulan wilayah')->schema([
                Repeater::make('advantages')
                    ->hiddenLabel()
                    ->schema([
                        Fields::icon(),
                        TextInput::make('title')->label('Judul')->required()->maxLength(80)->placeholder('Tol Jakarta–Serpong'),
                        TextInput::make('distance')->label('Jarak / waktu')->maxLength(40)->placeholder('± 5 menit'),
                        Textarea::make('description')->label('Deskripsi')->rows(2)->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->reorderableWithDragAndDrop()
                    ->defaultItems(0)
                    ->addActionLabel('Tambah poin'),
            ]),
            Fields::advanced([
                Grid::make(2)->schema([
                    TextInput::make('latitude')->numeric()->placeholder('-6.3017'),
                    TextInput::make('longitude')->numeric()->placeholder('106.6527'),
                ]),
            ]),
        ]);
    }
}
