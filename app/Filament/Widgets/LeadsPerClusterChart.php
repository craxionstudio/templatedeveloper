<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use Filament\Widgets\ChartWidget;

class LeadsPerClusterChart extends ChartWidget
{
    protected ?string $heading = 'Lead per cluster (30 hari)';

    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return auth()->user()?->canManageLeads() ?? false;
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $rows = Lead::query()
            ->leftJoin('clusters', 'clusters.id', '=', 'leads.cluster_id')
            ->where('leads.created_at', '>=', now()->subDays(30))
            ->selectRaw("COALESCE(clusters.name, 'Tanpa cluster') as label, COUNT(*) as total")
            ->groupBy('label')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return [
            'datasets' => [['label' => 'Lead', 'data' => $rows->pluck('total')->all(), 'backgroundColor' => '#A94F2A']],
            'labels' => $rows->pluck('label')->all(),
        ];
    }
}
