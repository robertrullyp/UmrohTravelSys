<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VisitorLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Grafik Pengunjung');
    }
}
