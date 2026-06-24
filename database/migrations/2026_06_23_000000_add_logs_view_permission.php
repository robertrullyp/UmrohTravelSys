<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('permissions') || ! Schema::hasTable('roles')) {
            return;
        }

        $now = now();

        DB::table('permissions')->updateOrInsert(
            ['name' => 'logs.view', 'guard_name' => 'web'],
            ['created_at' => $now, 'updated_at' => $now],
        );

        $permissionId = DB::table('permissions')
            ->where('name', 'logs.view')
            ->where('guard_name', 'web')
            ->value('id');

        if (! $permissionId || ! Schema::hasTable('role_has_permissions')) {
            return;
        }

        DB::table('roles')
            ->whereIn('name', ['super-admin', 'admin'])
            ->where('guard_name', 'web')
            ->pluck('id')
            ->each(fn (int|string $roleId): int => DB::table('role_has_permissions')->insertOrIgnore([
                'permission_id' => $permissionId,
                'role_id' => $roleId,
            ]));
    }

    public function down(): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        $permissionId = DB::table('permissions')
            ->where('name', 'logs.view')
            ->where('guard_name', 'web')
            ->value('id');

        if ($permissionId && Schema::hasTable('role_has_permissions')) {
            DB::table('role_has_permissions')
                ->where('permission_id', $permissionId)
                ->delete();
        }

        DB::table('permissions')
            ->where('name', 'logs.view')
            ->where('guard_name', 'web')
            ->delete();
    }
};
