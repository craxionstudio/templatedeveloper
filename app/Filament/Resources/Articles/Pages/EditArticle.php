<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\URL;

class EditArticle extends EditRecord
{
    protected static string $resource = ArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Pratinjau (termasuk draft): URL bertanda tangan 1 jam, noindex.
            Action::make('preview')
                ->label('Pratinjau')
                ->icon(Heroicon::OutlinedEye)
                ->color('gray')
                ->url(fn (): string => URL::temporarySignedRoute('artikel.preview', now()->addHour(), ['article' => $this->record]), shouldOpenInNewTab: true),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
