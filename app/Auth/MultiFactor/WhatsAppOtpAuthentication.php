<?php

namespace App\Auth\MultiFactor;

use App\Models\SiteSetting;
use App\Models\User;
use App\Services\WhatsAppGatewayService;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Auth\MultiFactor\Contracts\HasBeforeChallengeHook;
use Filament\Auth\MultiFactor\Contracts\MultiFactorAuthenticationProvider;
use Filament\Forms\Components\OneTimeCodeInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use SensitiveParameter;
use Throwable;

class WhatsAppOtpAuthentication implements HasBeforeChallengeHook, MultiFactorAuthenticationProvider
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'whatsapp_otp';
    }

    public function getLoginFormLabel(): string
    {
        return 'OTP WhatsApp';
    }

    public function isEnabled(Authenticatable $user): bool
    {
        return SiteSetting::getBoolean('admin_otp_enabled');
    }

    /**
     * @return array<Component|Action>
     */
    public function getManagementSchemaComponents(): array
    {
        return [];
    }

    public function beforeChallenge(Authenticatable $user): void
    {
        if (! $this->sendCode($user)) {
            $seconds = RateLimiter::availableIn($this->sendRateLimitKey($user));

            throw ValidationException::withMessages([
                'data.email' => 'Kode OTP sudah dikirim. Tunggu '.$seconds.' detik sebelum meminta kode baru.',
            ]);
        }
    }

    /**
     * @return array<Component|Action|ActionGroup>
     */
    public function getChallengeFormComponents(Authenticatable $user): array
    {
        return [
            OneTimeCodeInput::make('code')
                ->label('Kode OTP WhatsApp')
                ->helperText('Masukkan 6 digit kode yang dikirim ke nomor WhatsApp admin.')
                ->validationAttribute('kode OTP')
                ->belowContent(Action::make('resend')
                    ->label('Kirim Ulang OTP')
                    ->link()
                    ->action(function () use ($user): void {
                        if (! $this->sendCode($user)) {
                            $seconds = RateLimiter::availableIn($this->sendRateLimitKey($user));

                            Notification::make()
                                ->danger()
                                ->title('OTP belum bisa dikirim ulang.')
                                ->body('Tunggu '.$seconds.' detik lalu coba lagi.')
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->success()
                            ->title('OTP WhatsApp dikirim ulang.')
                            ->send();
                    }))
                ->required()
                ->rule(function () use ($user): Closure {
                    return function (string $attribute, #[SensitiveParameter] mixed $value, Closure $fail) use ($user): void {
                        if ($this->verifyCode((string) $value, $user)) {
                            return;
                        }

                        $fail('Kode OTP tidak valid atau sudah kedaluwarsa.');
                    };
                }),
        ];
    }

    public function sendCode(Authenticatable $user): bool
    {
        if (! $user instanceof User) {
            throw ValidationException::withMessages([
                'data.email' => 'Akun admin tidak valid untuk OTP WhatsApp.',
            ]);
        }

        if (blank($user->phone)) {
            throw ValidationException::withMessages([
                'data.email' => 'Akun admin ini belum memiliki nomor WhatsApp. Minta super-admin mengisi nomor telepon admin dulu.',
            ]);
        }

        if (! app(WhatsAppGatewayService::class)->isEnabled()) {
            throw ValidationException::withMessages([
                'data.email' => 'OTP WhatsApp aktif, tetapi gateway WhatsApp belum diaktifkan.',
            ]);
        }

        $rateLimitKey = $this->sendRateLimitKey($user);

        if (RateLimiter::tooManyAttempts($rateLimitKey, maxAttempts: 1)) {
            return false;
        }

        $code = $this->generateCode();
        $expiresAt = now()->addMinutes($this->expiryMinutes());

        session()->put('admin_whatsapp_otp_user_id', $user->getAuthIdentifier());
        session()->put('admin_whatsapp_otp_code_hash', Hash::make($code));
        session()->put('admin_whatsapp_otp_expires_at', $expiresAt);

        try {
            app(WhatsAppGatewayService::class)->sendInteractive(
                $user->phone,
                implode("\n", [
                    'Kode OTP login admin PT Amara Al Medina Travel:',
                    $code,
                    '',
                    'Kode berlaku '.$this->expiryMinutes().' menit. Abaikan pesan ini jika Anda tidak sedang login.',
                ]),
                [
                    [
                        'type' => 'copy',
                        'text' => 'Copy OTP',
                        'copyCode' => $code,
                    ],
                ],
                'Login Admin',
                'admin-login-otp-'.$user->getAuthIdentifier().'-'.now()->timestamp,
            );
        } catch (RuntimeException $exception) {
            $this->clearCode();

            throw ValidationException::withMessages([
                'data.email' => $exception->getMessage(),
            ]);
        } catch (Throwable $exception) {
            report($exception);
            $this->clearCode();

            throw ValidationException::withMessages([
                'data.email' => 'OTP gagal dikirim. Periksa koneksi gateway WhatsApp lalu coba lagi.',
            ]);
        }

        RateLimiter::hit($rateLimitKey, $this->resendIntervalSeconds());

        return true;
    }

    public function verifyCode(#[SensitiveParameter] string $code, Authenticatable $user): bool
    {
        $verifyKey = 'admin-whatsapp-otp-verify:'.session()->getId();

        if (RateLimiter::tooManyAttempts($verifyKey, maxAttempts: 5)) {
            return false;
        }

        RateLimiter::hit($verifyKey, 300);

        $expectedUserId = session('admin_whatsapp_otp_user_id');
        $hash = session('admin_whatsapp_otp_code_hash');
        $expiresAt = session('admin_whatsapp_otp_expires_at');

        if (
            blank($expectedUserId)
            || (string) $expectedUserId !== (string) $user->getAuthIdentifier()
            || blank($hash)
            || blank($expiresAt)
            || now()->greaterThan($expiresAt)
            || ! Hash::check($code, $hash)
        ) {
            return false;
        }

        $this->clearCode();
        RateLimiter::clear($verifyKey);

        return true;
    }

    private function clearCode(): void
    {
        session()->forget([
            'admin_whatsapp_otp_user_id',
            'admin_whatsapp_otp_code_hash',
            'admin_whatsapp_otp_expires_at',
        ]);
    }

    private function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    private function expiryMinutes(): int
    {
        return SiteSetting::getInteger('admin_otp_expires_minutes', 5, 1, 30);
    }

    private function resendIntervalSeconds(): int
    {
        return SiteSetting::getInteger('admin_otp_resend_interval_seconds', 60, 30, 600);
    }

    private function sendRateLimitKey(Authenticatable $user): string
    {
        return 'admin-whatsapp-otp-send:'.$user->getAuthIdentifier();
    }
}
