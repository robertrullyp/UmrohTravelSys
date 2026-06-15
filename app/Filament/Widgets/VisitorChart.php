<?php

namespace App\Filament\Widgets;

use App\Models\VisitorLog;
use Carbon\CarbonInterface;
use Filament\Widgets\ChartWidget;

class VisitorChart extends ChartWidget
{
    protected ?string $heading = 'Grafik Pengunjung';

    protected ?string $description = 'Pengunjung unik dan page views 14 hari terakhir.';

    protected ?string $maxHeight = '320px';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    protected static bool $isLazy = false;

    protected function getData(): array
    {
        $startDate = now()->subDays(13)->startOfDay();

        $rows = VisitorLog::query()
            ->selectRaw('visited_on, COUNT(*) as page_views, COUNT(DISTINCT ip_hash) as unique_visitors')
            ->whereDate('visited_on', '>=', $startDate->toDateString())
            ->groupBy('visited_on')
            ->orderBy('visited_on')
            ->get()
            ->keyBy(function (VisitorLog $row): string {
                if ($row->visited_on instanceof CarbonInterface) {
                    return $row->visited_on->toDateString();
                }

                return (string) $row->visited_on;
            });

        $labels = [];
        $uniqueVisitors = [];
        $pageViews = [];

        for ($date = $startDate->copy(); $date->lte(now()); $date->addDay()) {
            $key = $date->toDateString();
            $row = $rows->get($key);

            $labels[] = $date->translatedFormat('d M');
            $uniqueVisitors[] = (int) ($row?->unique_visitors ?? 0);
            $pageViews[] = (int) ($row?->page_views ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pengunjung unik',
                    'data' => $uniqueVisitors,
                    'borderColor' => '#d61a6a',
                    'backgroundColor' => 'rgba(214, 26, 106, 0.12)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
                [
                    'label' => 'Page views',
                    'data' => $pageViews,
                    'borderColor' => '#078143',
                    'backgroundColor' => 'rgba(7, 129, 67, 0.1)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
