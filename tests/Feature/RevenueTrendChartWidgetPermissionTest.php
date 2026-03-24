<?php

use App\Filament\Widgets\RevenueTrendChartWidget;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\ShieldSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

function runRevenueTrendWidgetPermissionBackfillMigration(): void
{
    $migration = require database_path('migrations/2026_03_24_120000_grant_admin_revenue_trend_widget_permission.php');
    $migration->up();
}

function callProtectedStatic(string $class, string $method, array $arguments = [])
{
    $reflection = new ReflectionMethod($class, $method);
    $reflection->setAccessible(true);

    return $reflection->invokeArgs(null, $arguments);
}

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

test('backfill migration creates and assigns revenue trend widget permission for admin role', function () {
    $adminRole = Role::query()->firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);

    $legacyPermission = Permission::query()->firstOrCreate(['name' => 'View:Booking', 'guard_name' => 'web']);
    $adminRole->givePermissionTo($legacyPermission);

    // Simulate an existing DB state where new widget permission is missing.
    Permission::query()
        ->where('name', 'View:RevenueTrendChartWidget')
        ->where('guard_name', 'web')
        ->delete();

    app(PermissionRegistrar::class)->forgetCachedPermissions();

    runRevenueTrendWidgetPermissionBackfillMigration();
    runRevenueTrendWidgetPermissionBackfillMigration();

    $adminRole->refresh();
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $widgetPermission = Permission::query()
        ->where('name', 'View:RevenueTrendChartWidget')
        ->where('guard_name', 'web')
        ->first();

    expect($widgetPermission)->not()->toBeNull();
    expect($adminRole->hasPermissionTo('View:RevenueTrendChartWidget'))->toBeTrue();
    expect($adminRole->hasPermissionTo('View:Booking'))->toBeTrue();

    $tableNames = config('permission.table_names');
    $columnNames = config('permission.column_names');

    $permissionPivotKey = $columnNames['permission_pivot_key'] ?? 'permission_id';
    $rolePivotKey = $columnNames['role_pivot_key'] ?? 'role_id';

    $pivotCount = DB::table($tableNames['role_has_permissions'])
        ->where($permissionPivotKey, $widgetPermission->id)
        ->where($rolePivotKey, $adminRole->id)
        ->count();

    expect($pivotCount)->toBe(1);
});

test('shield seeder default grants admin view permission for revenue trend widget', function () {
    $this->seed(ShieldSeeder::class);

    $adminRole = Role::query()
        ->where('name', 'Admin')
        ->where('guard_name', 'web')
        ->first();

    expect($adminRole)->not()->toBeNull();
    expect($adminRole->hasPermissionTo('View:RevenueTrendChartWidget'))->toBeTrue();
});

test('revenue trend chart widget authorization allows admin with permission and denies admin without permission', function () {
    $widgetPermissionKey = callProtectedStatic(RevenueTrendChartWidget::class, 'getWidgetPermission');
    expect($widgetPermissionKey)->toBe('View:RevenueTrendChartWidget');

    $company = Company::factory()->create();

    $adminRole = Role::query()->firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
    $permission = Permission::query()->firstOrCreate(['name' => 'View:RevenueTrendChartWidget', 'guard_name' => 'web']);

    $allowedUser = User::factory()->create(['company_id' => $company->id]);
    $allowedUser->assignRole($adminRole);
    $adminRole->givePermissionTo($permission);

    app(PermissionRegistrar::class)->forgetCachedPermissions();
    $this->actingAs($allowedUser);

    expect(RevenueTrendChartWidget::canView())->toBeTrue();

    $adminRole->revokePermissionTo($permission);

    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $deniedUser = User::factory()->create(['company_id' => $company->id]);
    $deniedUser->assignRole($adminRole);

    $this->actingAs($deniedUser);

    expect(RevenueTrendChartWidget::canView())->toBeFalse();
});
