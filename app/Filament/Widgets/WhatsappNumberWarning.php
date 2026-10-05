<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\Settings\ManageGlobalSettings;
use App\Support\WhatsApp;
use Filament\Widgets\Widget;

/**
 * Peringatan di dashboard selama nomor WhatsApp global belum diisi (semua tombol WA memakai nomor ini).
 */
class WhatsappNumberWarning extends Widget
{
    protected static ?int $sort = -10;

    // Langsung dirender (bukan lazy) supaya peringatan terlihat begitu dashboard dibuka.
    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.whatsapp-number-warning';

    public static function canView(): bool
    {
        return ! WhatsApp::hasNumber();
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return ['settingsUrl' => ManageGlobalSettings::getUrl()];
    }
}
