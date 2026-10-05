<?php

namespace App\Filament\Resources\Authors\Schemas;

use App\Filament\Forms\Fields;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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
                    TextInput::make('name')->label('Nama')->required()->maxLength(255)->placeholder('Rina Wijaya'),
                    TextInput::make('job_title')->label('Jabatan')->maxLength(80)->placeholder('Property Consultant'),
                    Textarea::make('bio')->label('Bio singkat')->rows(3)->placeholder('Membantu keluarga muda memilih rumah di BSD City sejak 2015.'),
                    Fields::imageOnly('photo', 'Foto'),
                ]),
                Fields::advanced([Fields::slug()->placeholder('rina-wijaya')]),
            ]);
    }
}
