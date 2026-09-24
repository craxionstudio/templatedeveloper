<?php

namespace App\Filament\Resources\Redirects\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class RedirectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('from_path')
                    ->label('Dari path')
                    ->placeholder('/properti/nama-lama')
                    ->required()
                    ->startsWith('/')
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Select::make('status_code')
                    ->label('Kode')
                    ->options([301 => '301 — pindah permanen', 302 => '302 — sementara', 410 => '410 — dihapus'])
                    ->default(301)
                    ->live()
                    ->required(),
                TextInput::make('to_path')
                    ->label('Ke path / URL')
                    ->placeholder('/properti/nama-baru')
                    ->required(fn (Get $get): bool => (int) $get('status_code') !== 410)
                    ->hidden(fn (Get $get): bool => (int) $get('status_code') === 410)
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }
}
