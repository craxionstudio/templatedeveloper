<?php

namespace App\Filament\Resources\Promos\Schemas;

use App\Enums\PromoPlacement;
use App\Filament\Forms\Fields;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class PromoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Promo')->schema([
                    Select::make('placement')
                        ->label('Ditampilkan di')
                        ->options(PromoPlacement::class)
                        ->default(PromoPlacement::HomeBanner)
                        ->live()
                        ->required(),
                    TextInput::make('label')->label('Label kecil')->placeholder('Promo September')->maxLength(60),
                    TextInput::make('title')->label('Judul')->required()->maxLength(255),
                    Textarea::make('description')->label('Deskripsi')->rows(2),
                    Grid::make(2)->schema([
                        DateTimePicker::make('starts_at')->label('Mulai')->native(false),
                        DateTimePicker::make('ends_at')->label('Berakhir')->native(false)->after('starts_at')
                            ->helperText('Setelah tanggal ini promo otomatis tidak tampil.'),
                    ]),
                    TextInput::make('period_label')->label('Teks periode')->placeholder('[TANGGAL]')->maxLength(80),
                    Fields::button('cta', 'tombol', 'Contoh: /properti'),
                ]),
                Section::make('Isi promo (Detail Rumah)')
                    ->description('Daftar benefit yang tampil di kotak "Promo rumah ini".')
                    ->visible(fn (Get $get): bool => ($get('placement')?->value ?? $get('placement')) === PromoPlacement::Detail->value)
                    ->schema([
                        Repeater::make('items')
                            ->hiddenLabel()
                            ->schema([
                                Fields::icon(),
                                TextInput::make('title')->label('Judul')->required()->maxLength(80),
                                TextInput::make('description')->label('Keterangan')->maxLength(160),
                            ])
                            ->columns(3)
                            ->reorderableWithDragAndDrop()
                            ->defaultItems(0)
                            ->addActionLabel('Tambah benefit'),
                        Select::make('clusters')
                            ->label('Berlaku untuk cluster')
                            ->relationship('clusters', 'name')
                            ->multiple()
                            ->preload(),
                    ]),
                Section::make('Gambar')->schema([
                    ...Fields::image('image_desktop', 'image_alt', 'Gambar desktop'),
                    Fields::image('image_mobile', 'image_alt', 'Gambar mobile (opsional)')[0],
                ]),
                Section::make('Publikasi')->columns(2)->schema([
                    Toggle::make('is_published')->label('Dipublikasikan')->default(true),
                    TextInput::make('sort_order')->label('Urutan')->numeric()->default(0),
                ]),
            ]);
    }
}
