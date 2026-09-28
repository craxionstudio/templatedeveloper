<?php

namespace App\Filament\Resources\Clusters\Tables;

use App\Enums\ClusterStatus;
use App\Filament\Resources\Clusters\Schemas\ClusterForm;
use App\Models\Cluster;
use App\Support\Rupiah;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ClustersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('kawasan'))
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('name')->label('Cluster')->searchable()->sortable()
                    ->description(fn (Cluster $record): string => $record->building_type ?? ''),
                TextColumn::make('kawasan.name')->label('Kawasan')
                    ->placeholder('Cluster mandiri')
                    ->badge()
                    ->color(fn (Cluster $record): string => $record->isStandalone() ? 'gray' : 'primary'),
                TextColumn::make('prioritas')->label('Prioritas')->alignCenter()->placeholder('—')
                    // Tanpa prioritas selalu di bawah, baik urut naik maupun turun.
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query
                        ->orderByRaw('CASE WHEN prioritas IS NULL THEN 1 ELSE 0 END')
                        ->orderBy('prioritas', $direction))
                    ->badge()->color('primary'),
                TextColumn::make('perlu_dilengkapi_count')->label('Kelengkapan')->sortable()
                    ->badge()
                    ->formatStateUsing(fn (int $state): string => $state > 0 ? "Belum lengkap ({$state})" : 'Lengkap')
                    ->color(fn (int $state): string => $state > 0 ? 'warning' : 'success')
                    ->tooltip(fn (Cluster $record): ?string => collect($record->perlu_dilengkapi ?? [])
                        ->reject(fn (array $item) => $item['selesai'] ?? false)
                        ->pluck('item')->implode(' · ') ?: null),
                TextColumn::make('house_types_count')->label('Tipe')->alignCenter(),
                TextColumn::make('price_min')->label('Harga')->sortable()
                    ->formatStateUsing(fn ($state, Cluster $record): ?string => Rupiah::range($record->price_min, $record->price_max)),
                TextColumn::make('badge')->label('Badge')->badge(),
                TextColumn::make('status')->label('Status')->badge()->placeholder('—'),
                IconColumn::make('is_featured')->label('Unggulan')->boolean(),
                ToggleColumn::make('is_published')->label('Publik'),
            ])
            ->filters([
                SelectFilter::make('kawasan')
                    ->label('Kawasan')
                    ->options(fn (): array => ClusterForm::kawasanFilterOptions())
                    ->query(fn (Builder $query, array $data): Builder => match ($data['value'] ?? null) {
                        null, '' => $query,
                        Cluster::STANDALONE_FILTER => $query->whereNull('kawasan_id'),
                        default => $query->where('kawasan_id', $data['value']),
                    }),
                SelectFilter::make('status')->options(ClusterStatus::class),
                Filter::make('belum_lengkap')
                    ->label('Belum lengkap')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->where('perlu_dilengkapi_count', '>', 0)),
                TernaryFilter::make('is_published')->label('Dipublikasikan'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Lihat')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (Cluster $record): string => url($record->publicPath()), shouldOpenInNewTab: true),
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
