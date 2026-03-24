<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        $tableNames = config('permission.table_names', []);
        $columnNames = config('permission.column_names', []);

        $permissionsTable = $tableNames['permissions'] ?? 'permissions';
        $rolesTable = $tableNames['roles'] ?? 'roles';
        $roleHasPermissionsTable = $tableNames['role_has_permissions'] ?? 'role_has_permissions';
        $permissionPivotKey = $columnNames['permission_pivot_key'] ?? 'permission_id';
        $rolePivotKey = $columnNames['role_pivot_key'] ?? 'role_id';

        $permissionId = DB::table($permissionsTable)
            ->where('name', 'View:CustomerGrowthWidget')
            ->where('guard_name', 'web')
            ->value('id');

        if (! $permissionId) {
            $permissionId = DB::table($permissionsTable)->insertGetId([
                'name' => 'View:CustomerGrowthWidget',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $adminRoleIds = DB::table($rolesTable)
            ->where('name', 'Admin')
            ->where('guard_name', 'web')
            ->pluck('id');

        foreach ($adminRoleIds as $roleId) {
            $exists = DB::table($roleHasPermissionsTable)
                ->where($permissionPivotKey, $permissionId)
                ->where($rolePivotKey, $roleId)
                ->exists();

            if (! $exists) {
                DB::table($roleHasPermissionsTable)->insert([
                    $permissionPivotKey => $permissionId,
                    $rolePivotKey => $roleId,
                ]);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $tableNames = config('permission.table_names', []);
        $columnNames = config('permission.column_names', []);

        $permissionsTable = $tableNames['permissions'] ?? 'permissions';
        $rolesTable = $tableNames['roles'] ?? 'roles';
        $roleHasPermissionsTable = $tableNames['role_has_permissions'] ?? 'role_has_permissions';
        $permissionPivotKey = $columnNames['permission_pivot_key'] ?? 'permission_id';
        $rolePivotKey = $columnNames['role_pivot_key'] ?? 'role_id';

        $permissionId = DB::table($permissionsTable)
            ->where('name', 'View:CustomerGrowthWidget')
            ->where('guard_name', 'web')
            ->value('id');

        if (! $permissionId) {
            return;
        }

        $adminRoleIds = DB::table($rolesTable)
            ->where('name', 'Admin')
            ->where('guard_name', 'web')
            ->pluck('id');

        if ($adminRoleIds->isNotEmpty()) {
            DB::table($roleHasPermissionsTable)
                ->where($permissionPivotKey, $permissionId)
                ->whereIn($rolePivotKey, $adminRoleIds)
                ->delete();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
