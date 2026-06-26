<?php

namespace App\Providers\Filament;

use App\Auth\MultiFactor\WhatsAppOtpAuthentication;
use App\Filament\Pages\Auth\EditProfile as AdminEditProfile;
use App\Filament\Pages\Auth\Login as AdminLogin;
use App\Filament\Pages\Dashboard;
use App\Http\Middleware\PrivateNoIndex;
use App\Http\Middleware\SecurityHeaders;
use App\Models\SiteSetting;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(AdminLogin::class)
            ->profile(page: AdminEditProfile::class, isSimple: false)
            ->multiFactorAuthentication([WhatsAppOtpAuthentication::make()], setUpRequiredAction: null)
            ->brandName('PT Amara Al Medina Travel')
            ->brandLogo(SiteSetting::assetUrl('brand_logo_path', 'images/site/logo.png'))
            ->brandLogoHeight('4.75rem')
            ->favicon(SiteSetting::assetUrl('favicon_path', 'images/site/logo.png'))
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('18rem')
            ->collapsedSidebarWidth('4.75rem')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->colors([
                'primary' => Color::hex('#d61a6a'),
                'success' => Color::hex('#0b8a4a'),
            ])
            ->userMenuItems([
                'profile' => fn (Action $action): Action => $action->label('Akun Saya'),
                'logout' => fn (Action $action): Action => $action
                    ->label('Keluar')
                    ->color('danger')
                    ->url(null)
                    ->postToUrl(false)
                    ->requiresConfirmation()
                    ->modalHeading('Keluar dari panel admin?')
                    ->modalDescription('Sesi admin akan diakhiri dan Anda perlu login kembali untuk mengakses panel.')
                    ->modalSubmitActionLabel('Keluar')
                    ->action(function () {
                        Filament::auth()->logout();

                        request()->session()->invalidate();
                        request()->session()->regenerateToken();

                        return redirect()->to(Filament::getLoginUrl());
                    }),
            ])
            ->renderHook(
                PanelsRenderHook::USER_MENU_PROFILE_BEFORE,
                fn (): string => view('filament.partials.user-menu-profile-card')->render(),
            )
            ->renderHook(
                PanelsRenderHook::SIDEBAR_START,
                fn (): string => view('filament.partials.sidebar-brand')->render(),
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => '<meta name="robots" content="noindex,nofollow,nosnippet">',
            )
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\Filament\Clusters')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                SecurityHeaders::class,
                PrivateNoIndex::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
