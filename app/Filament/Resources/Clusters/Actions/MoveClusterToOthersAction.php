<?php

namespace App\Filament\Resources\Clusters\Actions;

use App\Enums\ClusterDisplay;
use App\Filament\Resources\OtherClusters\OtherClusterResource;
use App\Models\Cluster;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

/**
 * Cluster (halaman lengkap) → Cluster Lainnya (nama saja). Data lengkap (tipe, foto, deskripsi, benefit)
 * tidak dihapus, hanya disembunyikan dari website, supaya bisa diaktifkan lagi.
 */
class MoveClusterToOthersAction
{
    public static function make(bool $redirect = false): Action
    {
        return Action::make('pindahKeLainnya')
            ->label('Pindahkan ke Cluster Lainnya')
            ->icon(Heroicon::OutlinedArchiveBoxArrowDown)
            ->color('gray')
            ->requiresConfirmation()
            ->modalHeading(fn (Cluster $record): string => "Pindahkan {$record->name} ke Cluster Lainnya?")
            ->modalDescription('Halaman cluster ini tidak tampil lagi di website: hanya namanya di halaman "Cluster Lainnya", dan URL lamanya diarahkan ke sana. Tipe rumah, foto, deskripsi, dan benefit tetap tersimpan, jadi bisa diaktifkan lagi kapan saja.')
            ->modalSubmitActionLabel('Pindahkan')
            ->action(fn (Cluster $record) => $record->update(['tampil_sebagai' => ClusterDisplay::Daftar]))
            ->successNotificationTitle('Dipindahkan ke Cluster Lainnya')
            ->successRedirectUrl($redirect ? fn (): string => OtherClusterResource::getUrl('index') : null);
    }
}
