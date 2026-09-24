<?php

namespace App\Filament\Resources\FutureDevelopments\Schemas;

use App\Enums\DevelopmentStatus;
use App\Filament\Forms\Fields;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FutureDevelopmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Pengembangan')->schema([
                    Grid::make(3)->schema([
                        TextInput::make('target')->label('Tahun / target')->placeholder('2027')->required()->maxLength(30),
                        TextInput::make('title')->label('Judul')->required()->maxLength(255)->columnSpan(2),
                    ]),
                    Select::make('status')->label('Status')->options(DevelopmentStatus::class)->default(DevelopmentStatus::Perencanaan)->required(),
                    Textarea::make('description')->label('Deskripsi')->rows(2),
                ]),
                Section::make('Gambar (opsional)')->schema(Fields::image('image', 'image_alt', 'Gambar / render')),
                Section::make('Publikasi')->columns(2)->schema([
                    Toggle::make('is_published')->label('Dipublikasikan')->default(true),
                    TextInput::make('sort_order')->label('Urutan')->numeric()->default(0),
                ]),
            ]);
    }
}
