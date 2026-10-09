<?php

namespace App\Filament\Resources\OtherClusters;

use App\Enums\ClusterDisplay;
use App\Filament\Resources\Clusters\ClusterResource;
use App\Models\Cluster;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

/**
 * Cluster Lainnya → Cluster dengan halaman lengkap, lalu buka form Cluster lengkapnya
 * (data lama yang pernah diisi muncul lagi).
 */
class ActivateClusterAction
{
    public static function make(): Action
    {
        return Action::make('aktifkanHalaman')
            ->label('Aktifkan sebagai halaman')
            ->icon(Heroicon::OutlinedArrowUpOnSquare)
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading(fn (Cluster $record): string => "Aktifkan {$record->name} sebagai halaman?")
            ->modalDescription('Cluster ini akan punya halaman sendiri, tampil sebagai kartu di /properti, filter, dan sitemap. Setelah ini form Cluster lengkapnya dibuka: lengkapi foto, tipe rumah, dan deskripsi.')
            ->modalSubmitActionLabel('Aktifkan')
            ->action(fn (Cluster $record) => $record->update(['tampil_sebagai' => ClusterDisplay::Halaman]))
            ->successNotificationTitle('Cluster diaktifkan sebagai halaman')
            ->successRedirectUrl(fn (Cluster $record): string => ClusterResource::getUrl('edit', ['record' => $record]));
    }
}
