<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings;
use App\Services\SystemUpdateService;
use Filament\Actions\Action;
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

    private function refreshInfo(): void
    {
        $this->info = app(SystemUpdateService::class)->info();
    }
}
