<?php

namespace App\Filament\Resources\NewsletterSubscribers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class NewsletterSubscriberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('email')->label('Email')->email()->required()->unique(ignoreRecord: true)->maxLength(255),
                Select::make('status')->options(['aktif' => 'Aktif', 'berhenti' => 'Berhenti'])->default('aktif')->required(),
                TextInput::make('source')->label('Sumber')->maxLength(255),
            ]);
    }
}
