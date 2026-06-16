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
    protected static ?string $title = 'System Update';
    protected static ?string $navigationLabel = 'System Update';
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
                    ->label(fn (): string => ($this->info['github_token']['configured'] ?? false) ? 'Ganti Token FAT' : 'Input Token FAT')
                    ->icon(Heroicon::OutlinedKey)
                    ->color('primary')
                    ->form([
                        TextInput::make('token')
                            ->label('Fine-grained personal access token')
                            ->password()
                            ->revealable()
                            ->required()
                            ->minLength(20)
                            ->helperText('Gunakan Fine-grained PAT GitHub untuk repo ini. Permission minimal: Repository contents read-only. Token disimpan terenkripsi.'),
                    ])
                    ->modalHeading('Simpan Token FAT GitHub')
                    ->modalSubmitActionLabel('Simpan Token')
                    ->action(fn (array $data): null => $this->saveGitHubToken($data)),
                Action::make('forgetGitHubToken')
                    ->label('Hapus Token')
                    ->icon(Heroicon::OutlinedTrash)
                    ->color('danger')
                    ->visible(fn (): bool => $this->info['github_token']['configured'] ?? false)
                    ->requiresConfirmation()
                    ->modalHeading('Hapus token GitHub?')
                    ->modalDescription('Check Update dan Update from GitHub untuk repo private tidak akan bisa berjalan sampai token baru disimpan.')
                    ->modalSubmitActionLabel('Hapus Token')
                    ->action(fn (): null => $this->forgetGitHubToken()),
            ])
                ->label('Token FAT')
                ->icon(Heroicon::OutlinedKey)
                ->button()
                ->color('primary')
                ->visible(fn (): bool => (auth()->user()?->can('updates.run') ?? false) && ($this->info['github_token']['configured'] ?? false))
                ->extraAttributes(['class' => 'system-update-token-action']),
            Action::make('saveGitHubToken')
                ->label('Input Token FAT')
                ->icon(Heroicon::OutlinedKey)
                ->color('primary')
                ->visible(fn (): bool => (auth()->user()?->can('updates.run') ?? false) && ! ($this->info['github_token']['configured'] ?? false))
                ->form([
                    TextInput::make('token')
                        ->label('Fine-grained personal access token')
                        ->password()
                        ->revealable()
                        ->required()
                        ->minLength(20)
                        ->helperText('Gunakan Fine-grained PAT GitHub untuk repo ini. Permission minimal: Repository contents read-only. Token disimpan terenkripsi.'),
                ])
                ->modalHeading('Simpan Token FAT GitHub')
                ->modalSubmitActionLabel('Simpan Token')
                ->action(fn (array $data): null => $this->saveGitHubToken($data)),
            Action::make('checkUpdate')
                ->label('Check Update')
                ->icon(Heroicon::OutlinedMagnifyingGlass)
                ->color('gray')
                ->action(fn (): null => $this->checkUpdate()),
            Action::make('runUpdate')
                ->label('Update from GitHub')
                ->icon(Heroicon::OutlinedCloudArrowDown)
                ->color('danger')
                ->visible(fn (): bool => auth()->user()?->can('updates.run') ?? false)
                ->requiresConfirmation()
                ->modalHeading('Update aplikasi dari GitHub?')
                ->modalDescription('Proses ini akan mengambil source terbaru, reset ke origin/main, install dependency, build asset, menjalankan migrasi, dan optimize cache.')
                ->modalSubmitActionLabel('Jalankan Update')
                ->action(fn (): null => $this->runUpdate()),
        ];
    }

    public function checkUpdate(): null
    {
        $this->lastCheck = app(SystemUpdateService::class)->check();
        $this->refreshInfo();

        Notification::make()
            ->success()
            ->title('Pengecekan update selesai.')
            ->send();

        return null;
    }

    public function runUpdate(): null
    {
        abort_unless(auth()->user()?->can('updates.run'), 403);

        $this->lastUpdate = app(SystemUpdateService::class)->update();
        $this->refreshInfo();

        Notification::make()
            ->title($this->lastUpdate['successful'] ? 'Update selesai.' : 'Update gagal.')
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
            ->title('Token FAT GitHub disimpan.')
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
            ->title('Token FAT GitHub dihapus.')
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
