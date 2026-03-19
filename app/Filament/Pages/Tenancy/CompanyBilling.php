<?php

namespace App\Filament\Pages\Tenancy;

use App\Models\Company;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\Tenancy\EditTenantProfile;
use Filament\Panel;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class CompanyBilling extends EditTenantProfile
{
    public static function getLabel(): string
    {
        return 'Billing';
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return 'billing';
    }

    public static function isTenantSubscriptionRequired(Panel $panel): bool
    {
        return false;
    }

    public static function canView(Model $tenant): bool
    {
        $user = auth('web')->user();

        return $user && $tenant instanceof Company && (int) $user->company_id === (int) $tenant->getKey();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('plan_code')
                    ->label('Plan Code')
                    ->required()
                    ->maxLength(64),
                Select::make('subscription_status')
                    ->label('Subscription Status')
                    ->required()
                    ->options([
                        'trialing' => 'Trialing',
                        'active' => 'Active',
                        'past_due' => 'Past Due',
                        'unpaid' => 'Unpaid',
                        'canceled' => 'Canceled',
                    ]),
                DateTimePicker::make('trial_ends_at')
                    ->label('Trial Ends At'),
                DateTimePicker::make('current_period_ends_at')
                    ->label('Current Period Ends At'),
                Toggle::make('is_active')
                    ->label('Company Active')
                    ->required(),
            ]);
    }
}
