<?php

namespace App\Filament\Resources\Kawasans\Actions;

use App\Filament\Resources\OtherKawasans\OtherKawasanResource;
use App\Models\Kawasan;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

/**
 * Kawasan (halaman lengkap) → Kawasan Lainnya (hanya judul grup di /properti/cluster-lainnya). Data lengkap
 * (foto, deskripsi, fasilitas) tidak dihapus, hanya disembunyikan, supaya bisa diaktifkan lagi.
 */
class MoveKawasanToOthersAction
{
    public static function make(bool $redirect = false): Action
    {
        return Action::make('pindahKeLainnya')
            ->label('Pindahkan ke Kawasan Lainnya')
            ->icon(Heroicon::OutlinedArchiveBoxArrowDown)
            ->color('gray')
            ->requiresConfirmation()
            ->modalHeading(fn (Kawasan $record): string => "Pindahkan {$record->name} ke Kawasan Lainnya?")
            ->modalDescription('Halaman kawasan ini tidak tampil lagi di website (daftar kawasan, footer, sitemap); URL lamanya diarahkan ke grupnya di halaman "Cluster Lainnya". Cluster di dalamnya tidak berubah. Foto, deskripsi, dan fasilitas tetap tersimpan, jadi bisa diaktifkan lagi kapan saja.')
            ->modalSubmitActionLabel('Pindahkan')
            ->action(fn (Kawasan $record) => $record->update(['punya_halaman' => false]))
            ->successNotificationTitle('Dipindahkan ke Kawasan Lainnya')
            ->successRedirectUrl($redirect ? fn (): string => OtherKawasanResource::getUrl('index') : null);
    }
}
