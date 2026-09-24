<?php

namespace App\Filament\Resources\Promos\Tables;

use App\Enums\PromoPlacement;
use App\Models\Promo;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PromosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('title')->label('Judul')->searchable()->limit(50)
                    ->description(fn (Promo $record): ?string => $record->label),
                TextColumn::make('placement')->label('Tempat')->badge(),
                TextColumn::make('ends_at')->label('Berakhir')->dateTime('d M Y')->sortable()
                    ->color(fn (Promo $record): ?string => $record->ends_at?->isPast() ? 'danger' : null)
                    ->description(fn (Promo $record): ?string => $record->ends_at?->isPast() ? 'Kedaluwarsa' : null),
                ToggleColumn::make('is_published')->label('Publik'),
            ])
            ->filters([
                SelectFilter::make('placement')->label('Tempat')->options(PromoPlacement::class),
                TrashedFilter::make(),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
