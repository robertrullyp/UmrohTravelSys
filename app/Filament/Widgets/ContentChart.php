<?php

namespace App\Filament\Widgets;

use App\Models\Gallery;
use App\Models\Schedule;
use App\Models\UmrahPackage;
use Filament\Widgets\ChartWidget;

class ContentChart extends ChartWidget
{
    protected ?string $heading = 'Grafik Data';

    protected ?string $description = 'Ringkasan jumlah konten aktif di website.';

    protected ?string $maxHeight = '300px';

    protected int|string|array $columnSpan = 1;

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Total Konten',
                    'data' => [
                        UmrahPackage::query()->count(),
                        Schedule::query()->count(),
                        Gallery::query()->count(),
                    ],
                    'borderColor' => '#e61f73',
                    'backgroundColor' => ['#e61f73', '#0b8a4a', '#2563eb'],
                    'borderRadius' => 8,
                ],
            ],
            'labels' => ['Paket', 'Jadwal', 'Galeri'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
