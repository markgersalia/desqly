<?php

use App\Filament\Widgets\CustomerGrowthWidget;
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

function runCustomerGrowthWidgetPermissionBackfillMigration(): void
{
    $migration = require database_path('migrations/2026_03_24_130000_grant_admin_customer_growth_widget_permission.php');
    $migration->up();
}

function callCustomerGrowthProtectedStatic(string $class, string $method, array $arguments = [])
{
    $reflection = new ReflectionMethod($class, $method);
    $reflection->setAccessible(true);

    return $reflection->invokeArgs(null, $arguments);
}

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

test('backfill migration creates and assigns customer growth widget permission for admin role', function () {
    $adminRole = Role::query()->firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);

    $legacyPermission = Permission::query()->firstOrCreate(['name' => 'View:Booking', 'guard_name' => 'web']);
    $adminRole->givePermissionTo($legacyPermission);

    Permission::query()
        ->where('name', 'View:CustomerGrowthWidget')
        ->where('guard_name', 'web')
        ->delete();

    app(PermissionRegistrar::class)->forgetCachedPermissions();

    runCustomerGrowthWidgetPermissionBackfillMigration();
    runCustomerGrowthWidgetPermissionBackfillMigration();

    $adminRole->refresh();
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $widgetPermission = Permission::query()
        ->where('name', 'View:CustomerGrowthWidget')
        ->where('guard_name', 'web')
        ->first();

    expect($widgetPermission)->not()->toBeNull();
    expect($adminRole->hasPermissionTo('View:CustomerGrowthWidget'))->toBeTrue();
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

test('shield seeder default grants admin view permission for customer growth widget', function () {
    $this->seed(ShieldSeeder::class);

    $adminRole = Role::query()
        ->where('name', 'Admin')
        ->where('guard_name', 'web')
        ->first();

    expect($adminRole)->not()->toBeNull();
    expect($adminRole->hasPermissionTo('View:CustomerGrowthWidget'))->toBeTrue();
});

test('customer growth widget authorization allows admin with permission and denies admin without permission', function () {
    $widgetPermissionKey = callCustomerGrowthProtectedStatic(CustomerGrowthWidget::class, 'getWidgetPermission');
    expect($widgetPermissionKey)->toBe('View:CustomerGrowthWidget');

    $company = Company::factory()->create();

    $adminRole = Role::query()->firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
    $permission = Permission::query()->firstOrCreate(['name' => 'View:CustomerGrowthWidget', 'guard_name' => 'web']);

    $allowedUser = User::factory()->create(['company_id' => $company->id]);
    $allowedUser->assignRole($adminRole);
    $adminRole->givePermissionTo($permission);

    app(PermissionRegistrar::class)->forgetCachedPermissions();
    $this->actingAs($allowedUser);

    expect(CustomerGrowthWidget::canView())->toBeTrue();

    $adminRole->revokePermissionTo($permission);

    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $deniedUser = User::factory()->create(['company_id' => $company->id]);
    $deniedUser->assignRole($adminRole);

    $this->actingAs($deniedUser);

    expect(CustomerGrowthWidget::canView())->toBeFalse();
});
