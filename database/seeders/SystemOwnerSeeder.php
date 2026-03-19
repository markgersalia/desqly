<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class SystemOwnerSeeder extends Seeder
{
    public function run(): void
    {
        $email = (string) env('SYSTEM_OWNER_EMAIL', 'owner@example.com');
        $name = (string) env('SYSTEM_OWNER_NAME', 'System Owner');
        $password = (string) env('SYSTEM_OWNER_PASSWORD', 'password');

        $role = Role::query()->firstOrCreate([
            'name' => 'SystemOwner',
            'guard_name' => 'web',
        ]);

        $owner = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'company_id' => null,
                'name' => $name,
                'password' => $password,
                'email_verified_at' => now(),
            ],
        );

        $owner->syncRoles([$role->name]);
    }
}

