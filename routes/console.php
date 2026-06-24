<?php

use App\Models\VisitorLog;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('logs:prune-visitors {--days=90 : Hapus log kunjungan yang lebih lama dari jumlah hari ini}', function (): int {
    $days = max(1, (int) $this->option('days'));

    $deleted = VisitorLog::query()
        ->where('visited_at', '<', now()->subDays($days))
        ->delete();

    $this->info($deleted.' log kunjungan lebih lama dari '.$days.' hari telah dihapus.');

    return 0;
})->purpose('Hapus log kunjungan publik lama.');

Schedule::command('logs:prune-visitors --days=90')
    ->dailyAt('02:30')
    ->withoutOverlapping();
