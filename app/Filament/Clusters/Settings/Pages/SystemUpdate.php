<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings;
use App\Services\SystemUpdateService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class SystemUpdate extends Page
{
    protected static ?string $cluster = Settings::class;

    protected static ?string $slug = 'system-update';

    protected static ?string $title = 'Pembaruan Sistem';

    protected static ?string $navigationLabel = 'Pembaruan Sistem';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPathRoundedSquare;

    protected static ?int $navigationSort = 80;

    protected string $view = 'filament.clusters.settings.pages.system-update';

    /**
     * @var array<string, mixed>
     */
    public array $info = [];

    /**
     * @var array<string, mixed>|null
     */
    public ?array $lastCheck = null;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $lastUpdate = null;

    /**
     * @var array{version: string, date: string|null, notes: array<int, string>}
     */
    public array $releaseNotes = [];

    public function mount(): void
    {
        $this->refreshInfo();
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('updates.view') ?? false;
    }

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('saveGitHubToken')
                    ->label('Ganti Token')
                    ->icon(Heroicon::OutlinedKey)
                    ->color('primary')
                    ->form([
                        TextInput::make('token')
                            ->label('Token akses GitHub')
                            ->password()
                            ->revealable()
                            ->required()
                            ->minLength(20)
                            ->helperText('Token disimpan dengan aman dan hanya dipakai untuk mengambil pembaruan.'),
                    ])
                    ->modalHeading('Ganti token akses GitHub')
                    ->modalSubmitActionLabel('Simpan Token')
                    ->action(fn (array $data): null => $this->saveGitHubToken($data)),
                Action::make('forgetGitHubToken')
                    ->label('Hapus Token')
                    ->icon(Heroicon::OutlinedTrash)
                    ->color('danger')
                    ->visible(fn (): bool => $this->info['github_token']['configured'] ?? false)
                    ->requiresConfirmation()
                    ->modalHeading('Hapus token akses?')
                    ->modalDescription('Pembaruan dari GitHub tidak dapat dijalankan sampai token baru disimpan.')
                    ->modalSubmitActionLabel('Hapus Token')
                    ->action(fn (): null => $this->forgetGitHubToken()),
            ])
                ->label('Akses GitHub')
                ->icon(Heroicon::OutlinedKey)
                ->button()
                ->color('gray')
                ->visible(fn (): bool => (auth()->user()?->can('updates.run') ?? false) && ($this->info['github_token']['configured'] ?? false))
                ->extraAttributes(['class' => 'system-update-token-action']),
            Action::make('saveGitHubToken')
                ->label('Atur Akses GitHub')
                ->icon(Heroicon::OutlinedKey)
                ->color('primary')
                ->visible(fn (): bool => (auth()->user()?->can('updates.run') ?? false) && ! ($this->info['github_token']['configured'] ?? false))
                ->form([
                    TextInput::make('token')
                        ->label('Token akses GitHub')
                        ->password()
                        ->revealable()
                        ->required()
                        ->minLength(20)
                        ->helperText('Token disimpan dengan aman dan hanya dipakai untuk mengambil pembaruan.'),
                ])
                ->modalHeading('Atur akses pembaruan GitHub')
                ->modalSubmitActionLabel('Simpan Token')
                ->action(fn (array $data): null => $this->saveGitHubToken($data)),
            Action::make('checkUpdate')
                ->label('Cek Pembaruan')
                ->icon(Heroicon::OutlinedMagnifyingGlass)
                ->color('gray')
                ->action(fn (): null => $this->checkUpdate()),
            Action::make('runUpdate')
                ->label('Perbarui Sekarang')
                ->icon(Heroicon::OutlinedCloudArrowDown)
                ->color('warning')
                ->visible(fn (): bool => auth()->user()?->can('updates.run') ?? false)
                ->disabled(fn (): bool => ($this->lastCheck['status'] ?? null) !== 'update_available')
                ->tooltip(fn (): ?string => ($this->lastCheck['status'] ?? null) !== 'update_available' ? 'Cek pembaruan terlebih dahulu.' : null)
                ->requiresConfirmation()
                ->modalHeading('Perbarui aplikasi sekarang?')
                ->modalDescription('Proses dapat memerlukan beberapa menit. Jangan tutup halaman sampai pembaruan selesai.')
                ->modalSubmitActionLabel('Mulai Pembaruan')
                ->action(fn (): null => $this->runUpdate()),
        ];
    }

    public function checkUpdate(): null
    {
        $this->lastCheck = app(SystemUpdateService::class)->check();
        $this->refreshInfo();

        $notification = Notification::make();

        if (($this->lastCheck['status'] ?? null) === 'remote_empty_or_unreachable') {
            $notification
                ->danger()
                ->title('Pemeriksaan belum berhasil.')
                ->body('Periksa akses GitHub lalu coba kembali.');
        } else {
            $notification
                ->success()
                ->title('Pengecekan pembaruan selesai.');
        }

        $notification->send();

        return null;
    }

    public function runUpdate(): null
    {
        abort_unless(auth()->user()?->can('updates.run'), 403);

        $this->lastUpdate = app(SystemUpdateService::class)->update();
        $this->refreshInfo();

        Notification::make()
            ->title($this->lastUpdate['successful'] ? 'Pembaruan selesai.' : 'Pembaruan gagal.')
            ->{$this->lastUpdate['successful'] ? 'success' : 'danger'}()
            ->send();

        return null;
    }

    /**
     * @param  array{token: string}  $data
     */
    public function saveGitHubToken(array $data): null
    {
        abort_unless(auth()->user()?->can('updates.run'), 403);

        app(SystemUpdateService::class)->storeGitHubToken($data['token']);
        $this->refreshInfo();

        Notification::make()
            ->success()
            ->title('Akses GitHub disimpan.')
            ->send();

        return null;
    }

    public function forgetGitHubToken(): null
    {
        abort_unless(auth()->user()?->can('updates.run'), 403);

        app(SystemUpdateService::class)->forgetGitHubToken();
        $this->refreshInfo();

        Notification::make()
            ->success()
            ->title('Akses GitHub dihapus.')
            ->send();

        return null;
    }

    private function refreshInfo(): void
    {
        $service = app(SystemUpdateService::class);

        $this->info = $service->info();
        $this->releaseNotes = $service->latestReleaseNotes();
    }
}
