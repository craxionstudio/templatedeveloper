<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

/**
 * Tombol "Pratinjau" di form admin: buka halaman publik (termasuk yang belum dipublikasikan)
 * lewat URL bertanda tangan yang berlaku 1 jam. Halaman pratinjau selalu noindex.
 */
class PreviewAction
{
    public static function make(string $route, string $parameter): Action
    {
        return Action::make('preview')
            ->label('Pratinjau')
            ->icon(Heroicon::OutlinedEye)
            ->color('gray')
            ->url(fn (Model $record): string => URL::temporarySignedRoute($route, now()->addHour(), [$parameter => $record]), shouldOpenInNewTab: true);
    }
}
