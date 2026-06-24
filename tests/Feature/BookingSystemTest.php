<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Contact;
use App\Models\Schedule;
use App\Models\SiteSetting;
use App\Models\UmrahPackage;
use App\Models\User;
use App\Services\BookingStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BookingSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_public_guest_can_submit_booking_and_open_token_detail(): void
    {
        $schedule = Schedule::query()->with('umrahPackage')->where('quota', '>', 3)->firstOrFail();

        $response = $this->from('/booking')->post('/booking', [
            'umrah_package_id' => $schedule->umrah_package_id,
            'schedule_id' => $schedule->id,
            'customer_name' => 'Ahmad Fauzi',
            'whatsapp' => '082252239507',
            'email' => 'ahmad@example.test',
            'pilgrims_count' => 3,
            'notes' => 'Berangkat bersama keluarga.',
        ]);

        $booking = Booking::query()->firstOrFail();

        $response->assertRedirect(route('bookings.show', $booking->public_token));

        $this->assertDatabaseHas('bookings', [
            'booking_number' => $booking->booking_number,
            'status' => Booking::STATUS_PENDING,
            'pilgrims_count' => 3,
        ]);

        $this->get(route('bookings.show', $booking->public_token))
            ->assertOk()
            ->assertSee($booking->booking_number)
            ->assertSee('Menunggu Review');
    }

    public function test_booking_page_uses_clear_public_guidance(): void
    {
        $this->get('/booking')
            ->assertOk()
            ->assertSee('Form Pemesanan')
            ->assertSee('Ajukan Pemesanan Kursi Umrah')
            ->assertSee('Form ini adalah pengajuan awal')
            ->assertSee('Kirim form, lalu simpan nomor booking yang muncul')
            ->assertSee('Cek status memakai nomor booking dan nomor WhatsApp')
            ->assertSee('Sudah punya nomor booking?')
            ->assertSee('Cek Status Booking')
            ->assertSee('lookup_booking_number', false)
            ->assertSee('lookup_whatsapp', false)
            ->assertDontSee('Booking Guest');
    }

    public function test_guest_can_lookup_booking_status_with_booking_number_and_whatsapp(): void
    {
        $booking = $this->makeBooking(Schedule::query()->firstOrFail(), 1);

        $this->from('/booking')->post(route('bookings.status.lookup'), [
            'lookup_booking_number' => strtolower($booking->booking_number),
            'lookup_whatsapp' => '+62 822-5223-9507',
        ])->assertRedirect(route('bookings.show', $booking->public_token));
    }

    public function test_booking_status_lookup_fails_with_generic_message_without_changing_data(): void
    {
        $booking = $this->makeBooking(Schedule::query()->firstOrFail(), 1);
        $bookingCount = Booking::query()->count();
        $quota = $booking->schedule->quota;

        $this->from('/booking')->post(route('bookings.status.lookup'), [
            'lookup_booking_number' => $booking->booking_number,
            'lookup_whatsapp' => '081111111111',
        ])
            ->assertRedirect('/booking')
            ->assertSessionHasErrors([
                'lookup' => 'Data booking tidak ditemukan atau nomor WhatsApp tidak sesuai.',
            ], null, 'bookingLookup');

        $this->assertSame($bookingCount, Booking::query()->count());
        $this->assertSame($quota, $booking->schedule->refresh()->quota);
    }

    public function test_booking_submit_and_status_lookup_are_rate_limited(): void
    {
        $submitIp = '198.51.100.21';

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->withServerVariables(['REMOTE_ADDR' => $submitIp])
                ->post('/booking', [])
                ->assertStatus(302);
        }

        $this->withServerVariables(['REMOTE_ADDR' => $submitIp])
            ->post('/booking', [])
            ->assertStatus(429);

        $lookupIp = '198.51.100.22';

        for ($attempt = 1; $attempt <= 10; $attempt++) {
            $this->withServerVariables(['REMOTE_ADDR' => $lookupIp])
                ->post(route('bookings.status.lookup'), [])
                ->assertStatus(302);
        }

        $this->withServerVariables(['REMOTE_ADDR' => $lookupIp])
            ->post(route('bookings.status.lookup'), [])
            ->assertStatus(429);
    }

    public function test_public_booking_status_page_is_rate_limited(): void
    {
        $booking = $this->makeBooking(Schedule::query()->firstOrFail(), 1);
        $statusIp = '198.51.100.23';

        for ($attempt = 1; $attempt <= 60; $attempt++) {
            $this->withServerVariables(['REMOTE_ADDR' => $statusIp])
                ->get(route('bookings.show', $booking->public_token))
                ->assertOk();
        }

        $this->withServerVariables(['REMOTE_ADDR' => $statusIp])
            ->get(route('bookings.show', $booking->public_token))
            ->assertStatus(429);
    }

    public function test_booking_detail_contact_button_opens_whatsapp_with_booking_summary(): void
    {
        Contact::query()->update([
            'whatsapp' => '082252239507',
            'is_active' => true,
        ]);

        $schedule = Schedule::query()->with('umrahPackage')->firstOrFail();
        $booking = $this->makeBooking($schedule, 2);

        $response = $this->get(route('bookings.show', $booking->public_token))
            ->assertOk()
            ->assertSee('https://wa.me/6282252239507?text=', false)
            ->assertSee('target="_blank"', false);

        $href = $this->hubungiAdminHref($response->getContent());
        $this->assertStringStartsWith('https://wa.me/6282252239507?text=', $href);

        parse_str(parse_url($href, PHP_URL_QUERY) ?: '', $query);

        $message = $query['text'] ?? '';
        $this->assertStringContainsString('Assalamu alaikum admin PT Amara Al Medina Travel.', $message);
        $this->assertStringContainsString('Nomor Booking: '.$booking->booking_number, $message);
        $this->assertStringContainsString('Nama Pemesan: Ahmad Fauzi', $message);
        $this->assertStringContainsString('Paket: '.$booking->umrahPackage->name, $message);
        $this->assertStringContainsString('Keberangkatan: '.$booking->schedule->departure_date->translatedFormat('d F Y'), $message);
        $this->assertStringContainsString('Jumlah Jamaah: 2 orang', $message);
        $this->assertStringContainsString('Status: Menunggu Review', $message);
        $this->assertStringContainsString(route('bookings.show', $booking->public_token), $message);
        $this->assertStringContainsString('Mohon bantuan dan konfirmasinya.', $message);
    }

    public function test_booking_detail_contact_button_falls_back_to_contact_page_without_admin_whatsapp(): void
    {
        Contact::query()->update(['is_active' => false]);
        SiteSetting::query()->updateOrCreate(['key' => 'cta_whatsapp'], ['value' => '']);

        $booking = $this->makeBooking(Schedule::query()->firstOrFail(), 1);

        $response = $this->get(route('bookings.show', $booking->public_token))
            ->assertOk();

        $this->assertSame(route('contact'), $this->hubungiAdminHref($response->getContent()));
    }

    public function test_booking_rejects_package_schedule_mismatch_and_excess_quota(): void
    {
        $package = UmrahPackage::query()->firstOrFail();
        $otherSchedule = Schedule::query()
            ->where('umrah_package_id', '!=', $package->id)
            ->firstOrFail();

        $this->from('/booking')->post('/booking', [
            'umrah_package_id' => $package->id,
            'schedule_id' => $otherSchedule->id,
            'customer_name' => 'Ahmad Fauzi',
            'whatsapp' => '082252239507',
            'pilgrims_count' => 1,
        ])->assertRedirect('/booking')->assertSessionHasErrors('schedule_id');

        $schedule = Schedule::query()->firstOrFail();

        $this->from('/booking')->post('/booking', [
            'umrah_package_id' => $schedule->umrah_package_id,
            'schedule_id' => $schedule->id,
            'customer_name' => 'Ahmad Fauzi',
            'whatsapp' => '082252239507',
            'pilgrims_count' => $schedule->quota + 1,
        ])->assertRedirect('/booking')->assertSessionHasErrors('pilgrims_count');
    }

    public function test_approve_booking_decreases_quota_once_and_cancel_restores_it_once(): void
    {
        $reviewer = User::query()->firstOrFail();
        $schedule = Schedule::query()->firstOrFail();
        $initialCapacity = $schedule->capacity;
        $initialQuota = $schedule->quota;
        $booking = $this->makeBooking($schedule, 4);
        $service = app(BookingStatusService::class);

        $approved = $service->approve($booking, $reviewer, 'Data lengkap.');

        $this->assertSame(Booking::STATUS_APPROVED, $approved->status);
        $this->assertSame($initialCapacity, $schedule->refresh()->capacity);
        $this->assertSame($initialQuota - 4, $schedule->refresh()->quota);
        $this->assertNotNull($approved->quota_deducted_at);

        $this->expectException(ValidationException::class);
        $service->approve($approved, $reviewer);
    }

    public function test_reject_does_not_decrease_quota_and_cancel_approved_restores_quota(): void
    {
        $reviewer = User::query()->firstOrFail();
        $schedule = Schedule::query()->firstOrFail();
        $initialCapacity = $schedule->capacity;
        $initialQuota = $schedule->quota;
        $service = app(BookingStatusService::class);

        $rejected = $service->reject($this->makeBooking($schedule, 2), $reviewer, 'Kuota tidak cocok.');

        $this->assertSame(Booking::STATUS_REJECTED, $rejected->status);
        $this->assertSame($initialQuota, $schedule->refresh()->quota);

        $approved = $service->approve($this->makeBooking($schedule, 3), $reviewer);
        $this->assertSame($initialQuota - 3, $schedule->refresh()->quota);

        $cancelled = $service->cancel($approved, $reviewer, 'Batal dari jamaah.');

        $this->assertSame(Booking::STATUS_CANCELLED, $cancelled->status);
        $this->assertSame($initialCapacity, $schedule->refresh()->capacity);
        $this->assertSame($initialQuota, $schedule->refresh()->quota);
        $this->assertNotNull($cancelled->quota_restored_at);
    }

    public function test_approval_fails_when_quota_is_not_enough(): void
    {
        $reviewer = User::query()->firstOrFail();
        $schedule = Schedule::query()->firstOrFail();
        $schedule->update(['quota' => 1, 'status' => Schedule::statusForQuota(1)]);
        $booking = $this->makeBooking($schedule, 2);

        $this->expectException(ValidationException::class);

        app(BookingStatusService::class)->approve($booking, $reviewer);
    }

    private function makeBooking(Schedule $schedule, int $pilgrims): Booking
    {
        return Booking::query()->create([
            'booking_number' => 'TEST-'.fake()->unique()->numerify('######'),
            'public_token' => fake()->unique()->sha256(),
            'umrah_package_id' => $schedule->umrah_package_id,
            'schedule_id' => $schedule->id,
            'customer_name' => 'Ahmad Fauzi',
            'whatsapp' => '082252239507',
            'pilgrims_count' => $pilgrims,
            'status' => Booking::STATUS_PENDING,
        ]);
    }

    private function hubungiAdminHref(string $html): string
    {
        preg_match('/<a[^>]+href="([^"]+)"[^>]*>\s*Hubungi Admin\s*<\/a>/s', $html, $matches);

        $this->assertNotEmpty($matches[1] ?? null, 'Hubungi Admin link was not found.');

        return html_entity_decode($matches[1], ENT_QUOTES);
    }
}
