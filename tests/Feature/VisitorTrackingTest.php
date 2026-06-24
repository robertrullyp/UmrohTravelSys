<?php

namespace Tests\Feature;

use App\Filament\Resources\VisitorLogs\Pages\ListVisitorLogs;
use App\Filament\Resources\VisitorLogs\VisitorLogResource;
use App\Filament\Widgets\VisitorChart;
use App\Models\User;
use App\Models\VisitorLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use ReflectionClass;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class VisitorTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_public_get_records_visitor_log(): void
    {
        $this->withServerVariables([
            'REMOTE_ADDR' => '203.0.113.10',
            'HTTP_USER_AGENT' => 'Feature Test Browser',
        ])->get('/')->assertOk();

        $this->assertDatabaseHas('visitor_logs', [
            'path' => '/',
            'route_name' => 'home',
        ]);

        $this->assertSame(1, VisitorLog::query()->count());
    }

    public function test_admin_asset_and_non_get_requests_are_not_tracked(): void
    {
        $this->get('/admin/login')->assertOk();
        $this->get('/build/assets/missing.css')->assertNotFound();
        $this->post('/kontak')->assertStatus(405);

        $this->assertDatabaseCount('visitor_logs', 0);
    }

    public function test_admin_dashboard_contains_visitor_chart(): void
    {
        $admin = User::query()->where('email', config('admin.initial_email'))->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Grafik Pengunjung')
            ->assertSee('14 Hari')
            ->assertSee('30 Hari')
            ->assertSee('90 Hari')
            ->assertSee('12 Bulan')
            ->assertSee('Semua Data');
    }

    public function test_visitor_chart_supports_monthly_and_all_filters(): void
    {
        VisitorLog::query()->create([
            'visited_on' => now()->subMonths(13)->toDateString(),
            'visited_at' => now()->subMonths(13),
            'path' => '/',
            'route_name' => 'home',
            'ip_hash' => hash('sha256', '203.0.113.10'),
            'user_agent_hash' => hash('sha256', 'Feature Test Browser'),
        ]);

        VisitorLog::query()->create([
            'visited_on' => now()->toDateString(),
            'visited_at' => now(),
            'path' => '/galeri',
            'route_name' => 'galleries',
            'ip_hash' => hash('sha256', '203.0.113.11'),
            'user_agent_hash' => hash('sha256', 'Feature Test Browser'),
        ]);

        $chart = app(VisitorChart::class);
        $method = (new ReflectionClass($chart))->getMethod('getData');
        $method->setAccessible(true);

        foreach (['12m', 'all'] as $filter) {
            $chart->filter = $filter;
            $data = $method->invoke($chart);

            $this->assertNotEmpty($data['labels']);
            $this->assertCount(2, $data['datasets']);
            $this->assertContains('Pengunjung unik', array_column($data['datasets'], 'label'));
            $this->assertContains('Page views', array_column($data['datasets'], 'label'));
        }
    }

    public function test_log_page_uses_simple_admin_labels_and_period_filter(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-23 10:00:00'));

        $admin = User::query()->where('email', config('admin.initial_email'))->firstOrFail();
        $todayLog = $this->createVisitorLog(now(), '/', 'home', '203.0.113.10');
        $lastWeekLog = $this->createVisitorLog(now()->subDays(6), '/galeri', 'galleries', '203.0.113.11');
        $oldLog = $this->createVisitorLog(now()->subDays(100), '/profil', 'profile', '203.0.113.12');

        $this->actingAs($admin)
            ->get(VisitorLogResource::getUrl())
            ->assertOk()
            ->assertSee('Waktu Kunjungan')
            ->assertSee('Halaman Dibuka')
            ->assertSee('Jenis Halaman')
            ->assertSee('Pengunjung Anonim')
            ->assertSee('Periode')
            ->assertSee('90 hari')
            ->assertSee('Semua');

        $this->actingAs($admin);

        Livewire::test(ListVisitorLogs::class)
            ->assertTableFilterExists('period')
            ->assertTableColumnVisible('ip_hash')
            ->assertTableColumnExists('user_agent_hash', fn ($column): bool => $column->isToggledHiddenByDefault())
            ->assertCanSeeTableRecords([$todayLog, $lastWeekLog])
            ->assertCanNotSeeTableRecords([$oldLog])
            ->filterTable('period', 'all')
            ->assertCanSeeTableRecords([$todayLog, $lastWeekLog, $oldLog])
            ->filterTable('period', 'today')
            ->assertCanSeeTableRecords([$todayLog])
            ->assertCanNotSeeTableRecords([$lastWeekLog, $oldLog]);

        Carbon::setTestNow();
    }

    public function test_clear_old_logs_action_requires_delete_permission_and_prunes_selected_age(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-23 10:00:00'));

        $viewer = User::query()->create([
            'name' => 'Log Viewer',
            'email' => 'log-viewer-action@example.test',
            'password' => 'password',
        ]);
        $viewerRole = Role::query()->create(['name' => 'log-viewer-action', 'guard_name' => 'web']);
        $viewerRole->givePermissionTo(['panel.access', 'logs.view']);
        $viewer->assignRole($viewerRole);

        $cleaner = User::query()->create([
            'name' => 'Log Cleaner',
            'email' => 'log-cleaner@example.test',
            'password' => 'password',
        ]);
        $cleanerRole = Role::query()->create(['name' => 'log-cleaner', 'guard_name' => 'web']);
        $cleanerRole->givePermissionTo(['panel.access', 'logs.view', 'logs.delete']);
        $cleaner->assignRole($cleanerRole);

        $oldLog = $this->createVisitorLog(now()->subDays(181), '/profil', 'profile', '203.0.113.20');
        $twoDaysLog = $this->createVisitorLog(now()->subDays(2), '/galeri', 'galleries', '203.0.113.21');
        $recentLog = $this->createVisitorLog(now()->subHours(12), '/', 'home', '203.0.113.22');

        $this->actingAs($viewer);
        Livewire::test(ListVisitorLogs::class)
            ->assertActionHidden('clearOldLogs');

        $this->actingAs($cleaner);
        Livewire::test(ListVisitorLogs::class)
            ->assertActionVisible('clearOldLogs')
            ->callAction('clearOldLogs', ['days' => 1])
            ->assertHasNoActionErrors();

        $this->assertDatabaseMissing('visitor_logs', ['id' => $oldLog->id]);
        $this->assertDatabaseMissing('visitor_logs', ['id' => $twoDaysLog->id]);
        $this->assertDatabaseHas('visitor_logs', ['id' => $recentLog->id]);

        Carbon::setTestNow();
    }

    public function test_prune_visitors_command_deletes_only_logs_older_than_selected_days(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-23 10:00:00'));

        $olderThanSixtyDays = $this->createVisitorLog(now()->subDays(61), '/profil', 'profile', '203.0.113.30');
        $withinSixtyDays = $this->createVisitorLog(now()->subDays(60), '/galeri', 'galleries', '203.0.113.31');
        $recentLog = $this->createVisitorLog(now()->subDays(5), '/', 'home', '203.0.113.32');

        $this->artisan('logs:prune-visitors', ['--days' => 60])
            ->expectsOutput('1 log kunjungan lebih lama dari 60 hari telah dihapus.')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('visitor_logs', ['id' => $olderThanSixtyDays->id]);
        $this->assertDatabaseHas('visitor_logs', ['id' => $withinSixtyDays->id]);
        $this->assertDatabaseHas('visitor_logs', ['id' => $recentLog->id]);

        Carbon::setTestNow();
    }

    private function createVisitorLog(Carbon $visitedAt, string $path, ?string $routeName, string $ip): VisitorLog
    {
        return VisitorLog::query()->create([
            'visited_on' => $visitedAt->toDateString(),
            'visited_at' => $visitedAt,
            'path' => $path,
            'route_name' => $routeName,
            'ip_hash' => hash('sha256', $ip),
            'user_agent_hash' => hash('sha256', 'Feature Test Browser'),
        ]);
    }
}
