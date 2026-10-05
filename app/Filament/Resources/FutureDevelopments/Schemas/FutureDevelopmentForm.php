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
                        TextInput::make('title')->label('Judul')->required()->maxLength(255)->columnSpan(2)->placeholder('Stasiun MRT BSD'),
                    ]),
                    Select::make('status')->label('Status')->options(DevelopmentStatus::class)->default(DevelopmentStatus::Perencanaan)->required(),
                    Textarea::make('description')->label('Deskripsi')->rows(2)->placeholder('Rencana jalur MRT yang terhubung ke Jakarta.'),
                    Fields::imageOnly('image', 'Gambar / render (opsional)'),
                    Toggle::make('is_published')->label('Dipublikasikan')->default(true),
                ]),
            ]);
    }
}
