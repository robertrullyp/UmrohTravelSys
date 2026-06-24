<?php

namespace App\Filament\Widgets;

use App\Models\VisitorLog;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;

class VisitorChart extends ChartWidget
{
    protected ?string $heading = 'Grafik Pengunjung';

    protected ?string $maxHeight = '320px';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    protected static bool $isLazy = false;

    public ?string $filter = '14d';

    protected function getData(): array
    {
        $filter = $this->filter ?: '14d';
        $isMonthly = in_array($filter, ['12m', 'all'], true);
        $endDate = now()->endOfDay();
        $startDate = $this->getStartDate($filter);

        $rows = VisitorLog::query()
            ->select(['visited_on', 'ip_hash'])
            ->when($startDate, fn ($query) => $query->whereDate('visited_on', '>=', $startDate->toDateString()))
            ->whereDate('visited_on', '<=', $endDate->toDateString())
            ->orderBy('visited_on')
            ->get()
            ->groupBy(fn (VisitorLog $row): string => $this->getBucketKey($row->visited_on, $isMonthly));

        if ($isMonthly) {
            [$labels, $uniqueVisitors, $pageViews] = $this->buildMonthlySeries($rows, $startDate ?? now()->startOfMonth(), $endDate);
        } else {
            [$labels, $uniqueVisitors, $pageViews] = $this->buildDailySeries($rows, $startDate ?? now()->subDays(13)->startOfDay(), $endDate);
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

    public function getDescription(): string|Htmlable|null
    {
        return match ($this->filter ?: '14d') {
            '30d' => 'Pengunjung unik dan page views 30 hari terakhir.',
            '90d' => 'Pengunjung unik dan page views 90 hari terakhir.',
            '12m' => 'Pengunjung unik dan page views 12 bulan terakhir.',
            'all' => 'Pengunjung unik dan page views seluruh data.',
            default => 'Pengunjung unik dan page views 14 hari terakhir.',
        };
    }

    public function updatedFilter(): void
    {
        $this->cachedData = null;
    }

    protected function getFilters(): ?array
    {
        return [
            '14d' => '14 Hari',
            '30d' => '30 Hari',
            '90d' => '90 Hari',
            '12m' => '12 Bulan',
            'all' => 'Semua Data',
        ];
    }

    protected function getStartDate(string $filter): ?Carbon
    {
        return match ($filter) {
            '30d' => now()->subDays(29)->startOfDay(),
            '90d' => now()->subDays(89)->startOfDay(),
            '12m' => now()->subMonths(11)->startOfMonth(),
            'all' => $this->getEarliestVisitorDate(),
            default => now()->subDays(13)->startOfDay(),
        };
    }

    protected function getEarliestVisitorDate(): Carbon
    {
        $date = VisitorLog::query()->min('visited_on');

        return $date ? Carbon::parse($date)->startOfMonth() : now()->startOfMonth();
    }

    protected function getBucketKey(CarbonInterface|string|null $date, bool $isMonthly): string
    {
        $carbon = $date instanceof CarbonInterface ? $date->copy() : Carbon::parse($date);

        return $isMonthly ? $carbon->format('Y-m') : $carbon->toDateString();
    }

    /**
     * @param  Collection<string, Collection<int, VisitorLog>>  $rows
     * @return array{array<int, string>, array<int, int>, array<int, int>}
     */
    protected function buildDailySeries(Collection $rows, CarbonInterface $startDate, CarbonInterface $endDate): array
    {
        $labels = [];
        $uniqueVisitors = [];
        $pageViews = [];

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $key = $date->toDateString();
            $row = $rows->get($key);

            $labels[] = $date->translatedFormat('d M');
            $uniqueVisitors[] = $row ? $row->pluck('ip_hash')->unique()->count() : 0;
            $pageViews[] = $row ? $row->count() : 0;
        }

        return [$labels, $uniqueVisitors, $pageViews];
    }

    /**
     * @param  Collection<string, Collection<int, VisitorLog>>  $rows
     * @return array{array<int, string>, array<int, int>, array<int, int>}
     */
    protected function buildMonthlySeries(Collection $rows, CarbonInterface $startDate, CarbonInterface $endDate): array
    {
        $labels = [];
        $uniqueVisitors = [];
        $pageViews = [];

        for ($date = $startDate->copy()->startOfMonth(); $date->lte($endDate); $date->addMonth()) {
            $key = $date->format('Y-m');
            $row = $rows->get($key);

            $labels[] = $date->translatedFormat('M Y');
            $uniqueVisitors[] = $row ? $row->pluck('ip_hash')->unique()->count() : 0;
            $pageViews[] = $row ? $row->count() : 0;
        }

        return [$labels, $uniqueVisitors, $pageViews];
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
