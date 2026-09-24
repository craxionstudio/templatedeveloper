<?php

namespace App\Filament\Resources\Redirects\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class RedirectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('from_path')->label('Dari')->searchable(),
                TextColumn::make('to_path')->label('Ke')->searchable()->placeholder('— (410)'),
                TextColumn::make('status_code')->label('Kode')->badge(),
                TextColumn::make('hits')->label('Hit')->sortable(),
                IconColumn::make('is_automatic')->label('Otomatis')->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_automatic')->label('Dibuat otomatis (slug berubah)'),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
