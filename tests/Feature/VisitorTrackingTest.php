<?php

namespace Tests\Feature;

use App\Filament\Widgets\VisitorChart;
use App\Models\User;
use App\Models\VisitorLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $admin = User::query()->create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => 'password',
            'is_admin' => true,
        ]);
        $admin->syncRoles([Role::findByName('super-admin')]);

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
}
