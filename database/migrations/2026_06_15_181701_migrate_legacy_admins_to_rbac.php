<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('roles') || ! Schema::hasTable('permissions')) {
            return;
        }

        $now = now();

        DB::table('permissions')->updateOrInsert([
            'name' => 'panel.access',
            'guard_name' => 'web',
        ], [
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('roles')->updateOrInsert([
            'name' => 'super-admin',
            'guard_name' => 'web',
        ], [
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $permissionId = DB::table('permissions')
            ->where('name', 'panel.access')
            ->where('guard_name', 'web')
            ->value('id');

        $roleId = DB::table('roles')
            ->where('name', 'super-admin')
            ->where('guard_name', 'web')
            ->value('id');

        DB::table('role_has_permissions')->insertOrIgnore([
            'permission_id' => $permissionId,
            'role_id' => $roleId,
        ]);

        DB::table('users')
            ->where('is_admin', true)
            ->orderBy('id')
            ->pluck('id')
            ->each(function (int $userId) use ($roleId): void {
                DB::table('model_has_roles')->insertOrIgnore([
                    'role_id' => $roleId,
                    'model_type' => \App\Models\User::class,
                    'model_id' => $userId,
                ]);
            });
    }

    public function down(): void
    {
        // Role data is intentionally retained when rolling back feature migrations.
    }
};
