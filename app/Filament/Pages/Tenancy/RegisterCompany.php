<?php

namespace App\Filament\Pages\Tenancy;

use App\Models\Company;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Tenancy\RegisterTenant;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class RegisterCompany extends RegisterTenant
{
    public static function getLabel(): string
    {
        return 'Create company';
    }

    public static function canView(): bool
    {
        $user = auth('web')->user();

        return $user && ! $user->company_id;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Company Name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->label('Company Slug')
                    ->helperText('Used in your admin URL.')
                    ->required()
                    ->maxLength(255)
                    ->unique(Company::class, 'slug')
                    ->dehydrateStateUsing(fn (?string $state): string => Str::slug((string) $state)),
            ]);
    }

    protected function mutateFormDataBeforeRegister(array $data): array
    {
        $name = (string) ($data['name'] ?? 'Company');
        $slug = Str::slug((string) ($data['slug'] ?? $name));

        if ($slug === '') {
            $slug = 'company';
        }

        $baseSlug = $slug;
        $suffix = 2;

        while (Company::query()->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        $data['slug'] = $slug;
        $data['plan_code'] = 'starter';
        $data['subscription_status'] = 'trialing';
        $data['trial_ends_at'] = now()->addDays(14);
        $data['current_period_ends_at'] = now()->addMonth();
        $data['is_active'] = true;

        return $data;
    }

    protected function handleRegistration(array $data): Model
    {
        $company = Company::query()->create($data);

        $user = auth('web')->user();

        $user?->forceFill([
            'company_id' => $company->getKey(),
        ])->save();

        if ($user) {
            $adminRole = Role::query()->firstOrCreate([
                'name' => 'Admin',
                'guard_name' => 'web',
            ]);

            $user->assignRole($adminRole);
        }

        return $company;
    }
}
