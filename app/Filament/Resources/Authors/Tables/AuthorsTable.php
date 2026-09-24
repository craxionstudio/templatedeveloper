<?php

namespace App\Filament\Resources\Authors\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AuthorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount('articles'))
            ->columns([
                SpatieMediaLibraryImageColumn::make('photo')->collection('photo')->label('')->circular()->size(40),
                TextColumn::make('name')->label('Nama')->searchable(),
                TextColumn::make('job_title')->label('Jabatan'),
                TextColumn::make('articles_count')->label('Artikel')->badge(),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
