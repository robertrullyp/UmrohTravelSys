<?php

namespace App\Support;

final class AdminPermissions
{
    public const PANEL_ACCESS = 'panel.access';

    public const BOOKING_ACTIONS = [
        'bookings.approve',
        'bookings.reject',
        'bookings.cancel',
    ];

    public const UPDATE_ACTIONS = [
        'updates.view',
        'updates.run',
    ];

    public const RESOURCES = [
        'packages',
        'schedules',
        'galleries',
        'profiles',
        'contacts',
        'settings',
        'bookings',
        'users',
        'roles',
        'permissions',
    ];

    public const OPERATIONS = [
        'view',
        'create',
        'update',
        'delete',
    ];

    /**
     * @return array<int, string>
     */
    public static function all(): array
    {
        $permissions = [self::PANEL_ACCESS, ...self::BOOKING_ACTIONS, ...self::UPDATE_ACTIONS];

        foreach (self::RESOURCES as $resource) {
            foreach (self::OPERATIONS as $operation) {
                $permissions[] = "{$resource}.{$operation}";
            }
        }

        return $permissions;
    }

    /**
     * @return array<int, string>
     */
    public static function operationalAdmin(): array
    {
        return array_values(array_filter(
            self::all(),
            fn (string $permission): bool => ! str_starts_with($permission, 'users.')
                && ! str_starts_with($permission, 'roles.')
                && ! str_starts_with($permission, 'permissions.')
                && ! str_starts_with($permission, 'updates.'),
        ));
    }
}
