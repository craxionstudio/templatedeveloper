<?php

namespace App\Filament\Resources\OtherKawasans;

use App\Filament\Resources\Kawasans\KawasanResource;
use App\Models\Kawasan;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

/**
 * Kawasan Lainnya → Kawasan dengan halaman sendiri, lalu buka form Kawasan lengkapnya.
 */
class ActivateKawasanAction
{
    public static function make(): Action
    {
        return Action::make('aktifkanHalaman')
            ->label('Aktifkan sebagai halaman')
            ->icon(Heroicon::OutlinedArrowUpOnSquare)
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading(fn (Kawasan $record): string => "Aktifkan {$record->name} sebagai halaman?")
            ->modalDescription('Kawasan ini akan punya halaman sendiri dan tampil di daftar kawasan, footer, dan sitemap (selama punya minimal 1 cluster dengan halaman). Setelah ini form Kawasan lengkapnya dibuka.')
            ->modalSubmitActionLabel('Aktifkan')
            ->action(fn (Kawasan $record) => $record->update(['punya_halaman' => true]))
            ->successNotificationTitle('Kawasan diaktifkan sebagai halaman')
            ->successRedirectUrl(fn (Kawasan $record): string => KawasanResource::getUrl('edit', ['record' => $record]));
    }
}
