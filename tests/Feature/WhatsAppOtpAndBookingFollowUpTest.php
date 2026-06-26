<?php

namespace Tests\Feature;

use App\Auth\MultiFactor\WhatsAppOtpAuthentication;
use App\Filament\Pages\Auth\Login;
use App\Models\Booking;
use App\Models\Schedule;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\BookingFollowUpOtpService;
use App\Services\WhatsAppGatewayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WhatsAppOtpAndBookingFollowUpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_whatsapp_gateway_sends_interactive_payload_with_normalized_phone_and_secret_headers(): void
    {
        $this->enableGateway('header');
        SiteSetting::query()->updateOrCreate(['key' => 'wa_gateway_header_name'], ['value' => 'X-API-Key']);
        SiteSetting::setEncryptedValue('wa_gateway_header_value', 'secret-api-key');

        Http::fake([
            'https://gateway.example/ext/secret/wa' => Http::response(['ok' => true, 'id' => 'message-id']),
        ]);

        app(WhatsAppGatewayService::class)->sendInteractive(
            '0822-5223-9507',
            'Kode OTP login admin',
            [[
                'type' => 'copy',
                'text' => 'Copy OTP',
                'copyCode' => '123456',
            ]],
            'Login Admin',
            'otp-test',
        );

        Http::assertSent(function (Request $request): bool {
            return $request->url() === 'https://gateway.example/ext/secret/wa'
                && $request->hasHeader('X-API-Key', 'secret-api-key')
                && $request->hasHeader('Idempotency-Key', 'otp-test')
                && $request['action'] === 'send'
                && $request['to'] === '6282252239507'
                && $request['interactive']['type'] === 'template'
                && $request['interactive']['buttons'][0]['type'] === 'copy'
                && $request['interactive']['buttons'][0]['copyCode'] === '123456';
        });
    }

    public function test_admin_login_requires_whatsapp_otp_when_enabled(): void
    {
        $this->enableGateway();
        SiteSetting::query()->updateOrCreate(['key' => 'admin_otp_enabled'], ['value' => '1']);

        $admin = User::query()->where('email', config('admin.initial_email'))->firstOrFail();
        $admin->update(['phone' => '082252239507']);

        Http::fake([
            'https://gateway.example/ext/secret/wa' => Http::response(['ok' => true]),
        ]);

        $component = Livewire::test(Login::class)
            ->fillForm([
                'email' => config('admin.initial_email'),
                'password' => config('admin.initial_password'),
            ])
            ->call('authenticate')
            ->assertHasNoFormErrors()
            ->assertSee('Kode OTP WhatsApp')
            ->assertNoRedirect();

        $this->assertGuest();

        $otp = null;
        Http::assertSent(function (Request $request) use (&$otp): bool {
            $otp = $request['interactive']['buttons'][0]['copyCode'] ?? null;

            return $request['to'] === '6282252239507'
                && $request['interactive']['buttons'][0]['type'] === 'copy';
        });

        $this->assertMatchesRegularExpression('/^\d{6}$/', $otp);

        $component
            ->set('data.multiFactor.whatsapp_otp.code', $otp)
            ->call('authenticate')
            ->assertRedirect('/admin');

        $this->assertAuthenticatedAs($admin);
    }

    public function test_whatsapp_otp_provider_rejects_invalid_or_expired_code(): void
    {
        $this->enableGateway();
        SiteSetting::query()->updateOrCreate(['key' => 'admin_otp_enabled'], ['value' => '1']);

        $admin = User::query()->where('email', config('admin.initial_email'))->firstOrFail();
        $admin->update(['phone' => '082252239507']);

        Http::fake([
            'https://gateway.example/ext/secret/wa' => Http::response(['ok' => true]),
        ]);

        $provider = app(WhatsAppOtpAuthentication::class);
        $this->assertTrue($provider->sendCode($admin));

        $otp = null;
        Http::assertSent(function (Request $request) use (&$otp): bool {
            $otp = $request['interactive']['buttons'][0]['copyCode'] ?? null;

            return true;
        });

        $this->assertFalse($provider->verifyCode('000000', $admin));

        session()->put('admin_whatsapp_otp_expires_at', now()->subMinute());
        $this->assertFalse($provider->verifyCode((string) $otp, $admin));
    }

    public function test_public_booking_sends_follow_up_whatsapp_to_booking_admins_only(): void
    {
        $this->enableGateway();

        $recipient = User::query()->where('email', config('admin.initial_email'))->firstOrFail();
        $recipient->update(['phone' => '082252239507']);

        $blocked = User::query()->create([
            'name' => 'No Booking Permission',
            'email' => 'blocked@example.test',
            'phone' => '081111111111',
            'password' => 'password',
        ]);
        $blockedRole = Role::query()->create(['name' => 'blocked', 'guard_name' => 'web']);
        $blockedRole->givePermissionTo(['panel.access']);
        $blocked->assignRole($blockedRole);

        Http::fake([
            'https://gateway.example/ext/secret/wa' => Http::response(['ok' => true]),
        ]);

        $schedule = Schedule::query()->with('umrahPackage')->where('quota', '>', 3)->firstOrFail();

        $this->post('/booking', [
            'umrah_package_id' => $schedule->umrah_package_id,
            'schedule_id' => $schedule->id,
            'customer_name' => 'Ahmad Fauzi',
            'whatsapp' => '082252239507',
            'email' => 'ahmad@example.test',
            'pilgrims_count' => 2,
            'notes' => 'Mohon info pembayaran.',
        ])->assertRedirect();

        $booking = Booking::query()->firstOrFail();

        Http::assertSentCount(1);
        Http::assertSent(function (Request $request) use ($booking): bool {
            $buttonUrl = $request['interactive']['buttons'][0]['url'] ?? '';
            $copyButton = $request['interactive']['buttons'][1] ?? [];

            return $request['to'] === '6282252239507'
                && str_contains($request['interactive']['text'], $booking->booking_number)
                && str_contains($request['interactive']['text'], 'OTP berlaku 60 menit')
                && str_contains($buttonUrl, '/booking-follow-up/'.$booking->getKey())
                && str_contains($buttonUrl, 'signature=')
                && $copyButton['type'] === 'copy'
                && $copyButton['text'] === 'Copy OTP'
                && preg_match('/^\d{6}$/', (string) $copyButton['copyCode']) === 1;
        });
    }

    public function test_booking_follow_up_otp_is_scoped_and_single_use(): void
    {
        $admin = User::query()->where('email', config('admin.initial_email'))->firstOrFail();
        $otherAdmin = User::query()->create([
            'name' => 'Other Admin',
            'email' => 'other-admin@example.test',
            'password' => 'password',
        ]);
        $booking = $this->makePendingBooking();
        $service = app(BookingFollowUpOtpService::class);

        $otp = $service->generate($booking, $admin);

        $this->assertMatchesRegularExpression('/^\d{6}$/', $otp);
        $this->assertTrue($service->verify($booking, $admin, $otp));
        $this->assertFalse($service->verify($booking, $otherAdmin, $otp));

        $service->consume($booking, $admin);

        $this->assertFalse($service->verify($booking, $admin, $otp));
    }

    public function test_signed_follow_up_link_requires_otp_before_approving_without_admin_login(): void
    {
        $admin = User::query()->where('email', config('admin.initial_email'))->firstOrFail();
        $booking = $this->makePendingBooking();
        $otp = app(BookingFollowUpOtpService::class)->generate($booking, $admin);

        $showUrl = URL::temporarySignedRoute('booking-follow-up.show', now()->addHour(), [
            'booking' => $booking,
            'admin' => $admin->getKey(),
        ]);
        $approveUrl = URL::temporarySignedRoute('booking-follow-up.approve', now()->addHour(), [
            'booking' => $booking,
            'admin' => $admin->getKey(),
        ]);

        $this->get($showUrl)
            ->assertOk()
            ->assertSee('Tindak Lanjut Booking')
            ->assertSee($booking->booking_number)
            ->assertSee('Setujui Booking')
            ->assertSee('Kode OTP WhatsApp');

        $this->post($approveUrl, ['admin_notes' => 'Data sudah lengkap.'])
            ->assertSessionHasErrors('follow_up_otp');

        $this->post($approveUrl, [
            'admin_notes' => 'Data sudah lengkap.',
            'follow_up_otp' => '000000',
        ])->assertSessionHasErrors('follow_up_otp');

        $this->post($approveUrl, [
            'admin_notes' => 'Data sudah lengkap.',
            'follow_up_otp' => $otp,
        ])->assertRedirect();

        $booking->refresh();

        $this->assertSame(Booking::STATUS_APPROVED, $booking->status);
        $this->assertSame($admin->getKey(), $booking->reviewed_by);
        $this->assertFalse(app(BookingFollowUpOtpService::class)->verify($booking, $admin, $otp));
    }

    public function test_unsigned_follow_up_link_is_rejected(): void
    {
        $booking = Booking::query()->create([
            'booking_number' => 'TEST-'.fake()->unique()->numerify('######'),
            'public_token' => fake()->unique()->sha256(),
            'umrah_package_id' => Schedule::query()->firstOrFail()->umrah_package_id,
            'schedule_id' => Schedule::query()->firstOrFail()->id,
            'customer_name' => 'Ahmad Fauzi',
            'whatsapp' => '082252239507',
            'pilgrims_count' => 1,
            'status' => Booking::STATUS_PENDING,
        ]);

        $this->get('/booking-follow-up/'.$booking->getKey().'?admin=1')
            ->assertForbidden();
    }

    private function enableGateway(string $authMode = 'none'): void
    {
        SiteSetting::query()->updateOrCreate(['key' => 'wa_gateway_enabled'], ['value' => '1']);
        SiteSetting::query()->updateOrCreate(['key' => 'wa_gateway_auth_mode'], ['value' => $authMode]);
        SiteSetting::setEncryptedValue('wa_gateway_post_url', 'https://gateway.example/ext/secret/wa');
    }

    private function makePendingBooking(): Booking
    {
        $schedule = Schedule::query()->where('quota', '>', 3)->firstOrFail();

        return Booking::query()->create([
            'booking_number' => 'TEST-'.fake()->unique()->numerify('######'),
            'public_token' => fake()->unique()->sha256(),
            'umrah_package_id' => $schedule->umrah_package_id,
            'schedule_id' => $schedule->id,
            'customer_name' => 'Ahmad Fauzi',
            'whatsapp' => '082252239507',
            'pilgrims_count' => 2,
            'status' => Booking::STATUS_PENDING,
        ]);
    }
}
