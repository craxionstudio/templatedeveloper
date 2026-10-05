<?php

namespace App\Filament\Resources\Clusters\Actions;

use App\Filament\Resources\Clusters\ClusterResource;
use App\Models\Cluster;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

/**
 * "Duplikat cluster": salin cluster beserta tipe, benefit, dan foto, lalu buka form salinannya.
 */
class DuplicateClusterAction
{
    public static function make(): Action
    {
        return Action::make('duplikat')
            ->label('Duplikat cluster')
            ->icon(Heroicon::OutlinedDocumentDuplicate)
            ->color('gray')
            ->requiresConfirmation()
            ->modalDescription('Salinan berisi data, tipe rumah, benefit, foto, dan file yang sama, belum dipublikasikan. Ganti nama dan isinya, lalu publikasikan.')
            ->modalSubmitActionLabel('Duplikat')
            ->action(function (Cluster $record, Action $action): void {
                $copy = $record->duplicate();

                Notification::make()->success()->title('Cluster diduplikat')->body($copy->name)->send();

                $action->redirect(ClusterResource::getUrl('edit', ['record' => $copy]));
            });
    }
}
