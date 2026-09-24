<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use Filament\Widgets\ChartWidget;

class LeadsPerSourceChart extends ChartWidget
{
    protected ?string $heading = 'Lead per sumber UTM (30 hari)';

    protected static ?int $sort = 3;

    public static function canView(): bool
    {
        return auth()->user()?->canManageLeads() ?? false;
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $rows = Lead::query()
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw("COALESCE(utm_source, 'langsung') as label, COUNT(*) as total")
            ->groupBy('label')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        return [
            'datasets' => [[
                'label' => 'Lead',
                'data' => $rows->pluck('total')->all(),
                'backgroundColor' => ['#A94F2A', '#23392E', '#E9A07F', '#4A5650', '#2C4538', '#7E3A1E', '#C9D6CD', '#DDD6C8'],
            ]],
            'labels' => $rows->pluck('label')->all(),
        ];
    }
}
