<?php

namespace App\Filament\Resources\Clusters\Tables;

use App\Enums\ClusterDisplay;
use App\Enums\ClusterStatus;
use App\Filament\Resources\Clusters\Actions\DuplicateClusterAction;
use App\Filament\Resources\Clusters\Schemas\ClusterForm;
use App\Models\Benefit;
use App\Models\BenefitCluster;
use App\Models\Cluster;
use App\Support\Rupiah;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
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
use Illuminate\Database\Eloquent\Collection;

class ClustersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('kawasan')
                ->withCount('clusterBenefits')
                ->withMax('clusterBenefits', 'updated_at'))
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('name')->label('Cluster')->searchable()->sortable()
                    ->description(fn (Cluster $record): string => $record->building_type ?? ''),
                TextColumn::make('kawasan.name')->label('Kawasan')
                    ->placeholder('Cluster mandiri')
                    ->badge()
                    ->color(fn (Cluster $record): string => $record->isStandalone() ? 'gray' : 'primary'),
                TextColumn::make('tampil_sebagai')->label('Tampil sebagai')->badge()->sortable()->toggleable(),
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
                TextColumn::make('tanggal_launching')->label('Launching')->date('j M Y')->placeholder('—')
                    // Kosong selalu di bawah, baik urut naik maupun turun.
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query
                        ->orderByRaw('CASE WHEN tanggal_launching IS NULL THEN 1 ELSE 0 END')
                        ->orderBy('tanggal_launching', $direction)),
                TextColumn::make('house_types_count')->label('Tipe')->alignCenter(),
                TextColumn::make('cluster_benefits_count')->label('Benefit')->alignCenter()->sortable()
                    ->badge()->color(fn (int $state): string => $state > 0 ? 'primary' : 'gray'),
                TextColumn::make('cluster_benefits_max_updated_at')->label('Benefit diperbarui')
                    ->since()->dateTimeTooltip('j M Y H:i')->placeholder('—')->sortable()
                    ->toggleable(),
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
                SelectFilter::make('tampil_sebagai')->label('Tampil sebagai')->options(ClusterDisplay::class),
                SelectFilter::make('status')->options(ClusterStatus::class),
                SelectFilter::make('benefit')
                    ->label('Benefit')
                    ->options(fn (): array => Benefit::groupedOptions())
                    ->multiple()
                    ->searchable()
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        filled($data['values'] ?? null),
                        fn (Builder $q) => $q->whereHas('clusterBenefits', fn (Builder $b) => $b->whereIn('benefit_id', $data['values'])),
                    )),
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
                DuplicateClusterAction::make()->iconButton()->tooltip('Duplikat cluster'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('tambahBenefit')
                        ->label('Tambah benefit ke cluster terpilih')
                        ->icon(Heroicon::OutlinedPlusCircle)
                        ->schema([
                            Select::make('benefit_id')->label('Benefit')->options(fn (): array => Benefit::groupedOptions())->searchable()->required(),
                            TextInput::make('teks_tampil')->label('Teks tampil')->placeholder('Opsional, contoh: Diskon hingga 13%')->maxLength(40),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $added = 0;

                            foreach ($records as $cluster) {
                                $pivot = BenefitCluster::query()->firstOrNew(['cluster_id' => $cluster->id, 'benefit_id' => $data['benefit_id']]);

                                if (! $pivot->exists) {
                                    $pivot->urutan = (int) BenefitCluster::query()->where('cluster_id', $cluster->id)->max('urutan') + 1;
                                    $added++;
                                }

                                // Teks tampil hanya diganti kalau diisi.
                                if (filled($data['teks_tampil'] ?? null)) {
                                    $pivot->teks_tampil = $data['teks_tampil'];
                                }

                                $pivot->save();
                            }

                            Notification::make()->success()->title("Benefit ditambahkan ke {$added} cluster")->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('lepasBenefit')
                        ->label('Lepas benefit dari cluster terpilih')
                        ->icon(Heroicon::OutlinedMinusCircle)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->schema([
                            Select::make('benefit_id')->label('Benefit')->options(fn (): array => Benefit::groupedOptions())->searchable()->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            // Lewat model supaya event (cache halaman & sitemap) ikut berjalan.
                            $pivots = BenefitCluster::query()->where('benefit_id', $data['benefit_id'])->whereIn('cluster_id', $records->modelKeys())->get();
                            $pivots->each->delete();

                            Notification::make()->success()->title("Benefit dilepas dari {$pivots->count()} cluster")->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
