<?php

namespace App\Filament\Resources\Kawasans\Tables;

use App\Support\Rupiah;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class KawasansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->withCount(['clusters as published_clusters_count' => fn (Builder $q) => $q->published()])
                ->withMin(['clusters as price_from' => fn (Builder $q) => $q->published()], 'price_min'))
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                SpatieMediaLibraryImageColumn::make('hero')->collection('hero')->label('')->square()->size(48),
                TextColumn::make('name')->label('Nama')->searchable()->sortable()
                    ->description(fn ($record): string => '/properti/kawasan/'.$record->slug),
                TextColumn::make('published_clusters_count')->label('Cluster')->badge(),
                TextColumn::make('price_from')->label('Harga mulai')
                    ->formatStateUsing(fn ($state): ?string => Rupiah::short($state === null ? null : (int) $state)),
                IconColumn::make('is_featured')->label('Unggulan')->boolean(),
                ToggleColumn::make('is_published')->label('Publik'),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Lihat')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn ($record): string => url($record->publicPath()), shouldOpenInNewTab: true),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
