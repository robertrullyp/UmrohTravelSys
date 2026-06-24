<?php

namespace App\Filament\Resources;

use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;

abstract class PermissionResource extends Resource
{
    abstract protected static function permissionPrefix(): string;

    public static function canViewAny(): bool
    {
        return auth()->user()?->can(static::permissionPrefix().'.view') ?? false;
    }

    public static function canView(Model $record): bool
    {
        return static::canViewAny();
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can(static::permissionPrefix().'.create') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can(static::permissionPrefix().'.update') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can(static::permissionPrefix().'.delete') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can(static::permissionPrefix().'.delete') ?? false;
    }
}
