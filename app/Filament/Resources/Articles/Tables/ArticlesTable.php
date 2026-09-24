<?php

namespace App\Filament\Resources\Articles\Tables;

use App\Models\Article;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ArticlesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('category'))
            ->defaultSort('published_at', 'desc')
            ->columns([
                TextColumn::make('title')->label('Judul')->searchable()->limit(60)->wrap(),
                TextColumn::make('category.name')->label('Kategori')->badge(),
                TextColumn::make('published_at')->label('Terbit')->date('d M Y')->sortable(),
                TextColumn::make('reading_minutes')->label('Baca')->suffix(' mnt'),
                IconColumn::make('is_highlight')->label('Highlight')->boolean(),
                ToggleColumn::make('is_published')->label('Publik'),
            ])
            ->filters([
                SelectFilter::make('article_category_id')->label('Kategori')->relationship('category', 'name'),
                TernaryFilter::make('is_published')->label('Dipublikasikan'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Lihat')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->visible(fn (Article $record): bool => $record->isPubliclyVisible())
                    ->url(fn (Article $record): string => url($record->publicPath()), shouldOpenInNewTab: true),
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
