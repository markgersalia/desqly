<?php

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::query()->firstOrCreate(['name' => 'SystemOwner', 'guard_name' => 'web']);
    Role::query()->firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
    Role::query()->firstOrCreate(['name' => 'Staff', 'guard_name' => 'web']);
});

test('system owner can access super admin panel', function () {
    $owner = User::factory()->create(['company_id' => null]);
    $owner->assignRole('SystemOwner');

    $this->actingAs($owner)
        ->get('/super-admin')
        ->assertOk();
});

test('non system owner is denied access to super admin panel', function () {
    $tenantUser = User::factory()->create();
    $tenantUser->assignRole('Admin');

    $this->actingAs($tenantUser)
        ->get('/super-admin')
        ->assertForbidden();
});

test('system owner is denied access to tenant admin panel', function () {
    $owner = User::factory()->create(['company_id' => null]);
    $owner->assignRole('SystemOwner');

    $this->actingAs($owner)
        ->get('/admin')
        ->assertForbidden();
});

test('system owner sees sales widgets on super admin dashboard', function () {
    $owner = User::factory()->create(['company_id' => null]);
    $owner->assignRole('SystemOwner');

    Company::factory()->count(2)->create();

    $this->actingAs($owner)
        ->get('/super-admin')
        ->assertOk()
        ->assertSee('Total Sales')
        ->assertSee('Sales Trend')
        ->assertSee('Company Sales Breakdown');
});
