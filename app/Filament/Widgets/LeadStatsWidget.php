<?php

namespace App\Filament\Widgets;

use App\Enums\LeadStatus;
use App\Models\Lead;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LeadStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return auth()->user()?->canManageLeads() ?? false;
    }

    protected function getStats(): array
    {
        $today = Lead::query()->whereDate('created_at', today())->count();
        $week = Lead::query()->where('created_at', '>=', now()->startOfWeek())->count();
        $new = Lead::query()->where('status', LeadStatus::Baru->value)->count();

        return [
            Stat::make('Lead hari ini', $today),
            Stat::make('Lead minggu ini', $week),
            Stat::make('Belum dihubungi', $new)->color($new > 0 ? 'warning' : 'success'),
        ];
    }
}
