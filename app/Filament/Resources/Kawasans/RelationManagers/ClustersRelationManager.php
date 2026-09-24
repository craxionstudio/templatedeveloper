<?php

namespace App\Filament\Resources\Kawasans\RelationManagers;

use App\Filament\Resources\Clusters\ClusterResource;
use App\Models\Cluster;
use App\Support\Rupiah;
use Filament\Actions\Action;
use Filament\Actions\AssociateAction;
use Filament\Actions\DissociateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Cluster di dalam kawasan. "Lepas dari kawasan" menjadikan cluster mandiri.
 */
class ClustersRelationManager extends RelationManager
{
    protected static string $relationship = 'clusters';

    protected static ?string $title = 'Cluster di kawasan ini';

    protected static ?string $modelLabel = 'cluster';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('name')->label('Cluster')->searchable(),
                TextColumn::make('house_types_count')->label('Tipe')->badge(),
                TextColumn::make('price_min')->label('Harga')
                    ->formatStateUsing(fn ($state, Cluster $record): ?string => Rupiah::range($record->price_min, $record->price_max)),
                ToggleColumn::make('is_published')->label('Publik'),
            ])
            ->headerActions([
                AssociateAction::make()
                    ->label('Masukkan cluster')
                    ->recordSelectOptionsQuery(fn (Builder $query) => $query->whereNull('kawasan_id'))
                    ->preloadRecordSelect(),
                Action::make('create')
                    ->label('Cluster baru')
                    ->url(fn (): string => ClusterResource::getUrl('create', ['kawasan_id' => $this->getOwnerRecord()->getKey()])),
            ])
            ->recordActions([
                Action::make('edit')
                    ->label('Edit')
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->url(fn (Cluster $record): string => ClusterResource::getUrl('edit', ['record' => $record])),
                DissociateAction::make()
                    ->label('Jadikan mandiri')
                    ->modalDescription('Cluster akan dilepas dari kawasan ini dan menjadi cluster mandiri. URL cluster tidak berubah.'),
            ]);
    }
}
