<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class Settings extends Cluster
{
    protected static ?string $slug = 'settings';

    protected static ?string $title = 'Pengaturan';

    protected static ?string $navigationLabel = 'Pengaturan';

    protected static ?string $clusterBreadcrumb = 'Pengaturan';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?int $navigationSort = 90;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return collect([
            'settings.view',
            'settings.update',
            'users.view',
            'roles.view',
            'updates.view',
            'logs.view',
        ])->contains(fn (string $permission): bool => $user->can($permission));
    }
}
