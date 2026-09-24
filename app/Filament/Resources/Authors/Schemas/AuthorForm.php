<?php

namespace App\Filament\Resources\Authors\Schemas;

use App\Filament\Forms\Fields;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AuthorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Penulis')->schema([
                    Grid::make(2)->schema(Fields::titleAndSlug('name', 'Nama')),
                    TextInput::make('job_title')->label('Jabatan')->maxLength(80),
                    Textarea::make('bio')->label('Bio singkat')->rows(3),
                ]),
                Section::make('Foto')->schema(Fields::image('photo', 'photo_alt', 'Foto')),
            ]);
    }
}
