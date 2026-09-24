<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Articles\ArticleResource;
use App\Models\Article;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestArticlesWidget extends TableWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->canManageContent() ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Artikel terbaru')
            ->query(Article::query()->with('category')->latest('updated_at')->limit(5))
            ->paginated(false)
            ->recordUrl(fn (Article $record): string => ArticleResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('title')->label('Judul')->limit(70),
                TextColumn::make('category.name')->label('Kategori')->badge(),
                TextColumn::make('published_at')->label('Terbit')->date('d M Y'),
                IconColumn::make('is_published')->label('Publik')->boolean(),
            ]);
    }
}
