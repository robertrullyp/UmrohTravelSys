<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class Settings extends Cluster
{
    protected static ?string $slug = 'settings';
    protected static ?string $title = 'Settings';
    protected static ?string $navigationLabel = 'Settings';
    protected static ?string $clusterBreadcrumb = 'Settings';
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
            'permissions.view',
            'updates.view',
        ])->contains(fn (string $permission): bool => $user->can($permission));
    }
}
