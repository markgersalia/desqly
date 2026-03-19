<?php

namespace App\Filament\Pages;

use App\Models\Branch;
use App\Models\Company;
use App\Services\BusinessSettings;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BusinessSettingsPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = \Filament\Support\Icons\Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Business Settings';

    protected static ?string $title = 'Business Settings';

    protected static ?string $slug = 'business-settings';

    protected static ?int $navigationSort = 99;

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected string $view = 'filament.pages.business-settings-page';

    public ?array $data = [];

    public array $timezones = [];

    public function mount(BusinessSettings $businessSettings): void
    {
        $tenant = Filament::getTenant();
        $company = $tenant instanceof Company ? $tenant : null;

        if (! $businessSettings->isOnboardingComplete($company)) {
            $this->redirect(route('filament.admin.pages.onboarding', filament_tenant_route_params($company)), navigate: true);

            return;
        }

        $defaults = $businessSettings->defaults();
        $settings = $businessSettings->getSettings($company);

        $this->timezones = timezone_identifiers_list();

        $initial = array_replace_recursive([
            'business' => $defaults['business'],
            'booking' => $defaults['booking'],
            'labels' => $defaults['labels'],
            'branches' => $defaults['branches'],
        ], $settings);

        data_set(
            $initial,
            'booking.requires_staff',
            $businessSettings->requiresStaffAssignment($company)
        );

        if ($businessSettings->usesBranches($company) && ! data_get($initial, 'branches.default_branch_id')) {
            data_set(
                $initial,
                'branches.default_branch_id',
                Branch::query()
                    ->when($company, fn ($query) => $query->where('company_id', $company->getKey()))
                    ->value('id')
            );
        }

        $this->form->fill($initial);
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if (method_exists($user, 'hasRole')) {
            return $user->hasRole('Admin') || $user->roles()->count() === 0;
        }

        return true;
    }

    public static function shouldRegisterNavigation(): bool
    {
        try {
            $tenant = Filament::getTenant();
            $company = $tenant instanceof Company ? $tenant : null;

            return app(BusinessSettings::class)->isOnboardingComplete($company);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function form(Schema $schema): Schema
    {
        $timezones = array_combine($this->timezones, $this->timezones);

        return $schema
            ->components([
                Section::make('Business')
                    ->schema([
                        TextInput::make('business.name')
                            ->label('Business Name')
                            ->required()
                            ->maxLength(255)
                            ->disabled(),
                        Select::make('business.type')
                            ->label('Business Type')
                            ->options([
                                'spa' => 'Spa',
                                'clinic' => 'Clinic',
                                'gym' => 'Gym',
                                'generic' => 'Generic',
                            ])
                            ->required()
                            ->disabled(),
                        Select::make('business.entity_type')
                            ->label('Business Entity')
                            ->options([
                                'company' => 'Company',
                                'individual' => 'Individual',
                            ])
                            ->required()
                            ->default('company')
                            ->disabled(),
                        Select::make('business.timezone')
                            ->label('Timezone')
                            ->options($timezones)
                            ->searchable()
                            ->required()
                            ->disabled(),
                        TextInput::make('business.currency')
                            ->label('Currency (3 letters)')
                            ->required()
                            ->length(3)
                            ->maxLength(3)
                            ->alpha()
                            ->disabled()
                            ->dehydrateStateUsing(fn ($state) => strtoupper((string) $state)),
                    ])
                    ->columns(2),
                Section::make('Booking')
                    ->schema([
                        Toggle::make('booking.has_listings')
                            ->label('Enable services/listings')
                            ->required()
                            ->disabled(),
                        Toggle::make('booking.requires_staff')
                            ->label('Require staff')
                            ->helperText('Automatically ON for company and OFF for individual mode.')
                            ->disabled(),
                        Toggle::make('booking.requires_bed')
                            ->label('Require resource')
                            ->required()
                            ->disabled(),
                        Toggle::make('booking.requires_follow_up')
                            ->label('Require follow-up')
                            ->required()
                            ->disabled(),
                        Select::make('booking.mode')
                            ->label('Booking Mode')
                            ->options([
                                'time_slot' => 'Time Slot',
                                'whole_day' => 'Whole Day',
                            ])
                            ->required()
                            ->disabled(),
                        TextInput::make('booking.slot_interval_minutes')
                            ->label('Slot Interval (minutes)')
                            ->integer()
                            ->minValue(5)
                            ->maxValue(180)
                            ->required()
                            ->disabled(),
                        TextInput::make('booking.expire_after_hours')
                            ->label('Expire After (hours)')
                            ->integer()
                            ->minValue(1)
                            ->maxValue(168)
                            ->required()
                            ->disabled(),
                        TextInput::make('booking.grace_period_minutes')
                            ->label('Grace Period (minutes)')
                            ->integer()
                            ->minValue(1)
                            ->maxValue(240)
                            ->required()
                            ->disabled(),
                        TextInput::make('booking.day_start')
                            ->label('Day Start')
                            ->type('time')
                            ->required()
                            ->disabled(),
                        TextInput::make('booking.day_end')
                            ->label('Day End')
                            ->type('time')
                            ->required()
                            ->disabled(),
                    ])
                    ->columns(2),
                Section::make('Labels')
                    ->schema([
                        TextInput::make('labels.staff')
                            ->label('Staff Label')
                            ->required()
                            ->maxLength(50),
                        TextInput::make('labels.resource')
                            ->label('Resource Label')
                            ->required()
                            ->maxLength(50),
                        TextInput::make('labels.service')
                            ->label('Service Label')
                            ->required()
                            ->maxLength(50),
                        TextInput::make('labels.booking')
                            ->label('Booking Label')
                            ->required()
                            ->maxLength(50),
                    ])
                    ->columns(2),
                Section::make('Default Branch')
                    ->visible(fn (callable $get): bool => $get('business.entity_type') === 'company')
                    ->schema([
                        Select::make('branches.default_branch_id')
                            ->label('Default Branch')
                            ->options(function (): array {
                                $tenant = Filament::getTenant();
                                $company = $tenant instanceof Company ? $tenant : null;

                                return Branch::query()
                                    ->when($company, fn ($query) => $query->where('company_id', $company->getKey()))
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->all();
                            })
                            ->searchable()
                            ->preload()
                            ->required(fn (callable $get): bool => $get('business.entity_type') === 'company')
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('address')
                                    ->maxLength(255),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                $tenant = Filament::getTenant();
                                $company = $tenant instanceof Company ? $tenant : null;

                                $branch = Branch::create([
                                    'company_id' => $company?->getKey(),
                                    'name' => $data['name'],
                                    'address' => $data['address'] ?? null,
                                    'is_active' => true,
                                ]);

                                return $branch->id;
                            }),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(BusinessSettings $businessSettings): void
    {
        $state = $this->form->getState();
        $tenant = Filament::getTenant();
        $company = $tenant instanceof Company ? $tenant : null;

        $usesBranches = $businessSettings->usesBranches($company);

        $defaultBranchId = $usesBranches
            ? (int) data_get($state, 'branches.default_branch_id')
            : null;

        $payload = [
            'labels' => [
                'staff' => data_get($state, 'labels.staff'),
                'resource' => data_get($state, 'labels.resource'),
                'service' => data_get($state, 'labels.service'),
                'booking' => data_get($state, 'labels.booking'),
            ],
            'branches' => [
                'default_branch_id' => $defaultBranchId,
            ],
        ];

        $businessSettings->saveSettings($payload, $company);

        if ($usesBranches && $defaultBranchId) {
            $businessSettings->backfillBranchAssignments($defaultBranchId, $company);
        }

        $businessSettings->applyRuntimeConfig($company);

        Notification::make()
            ->title('Business settings saved')
            ->success()
            ->send();
    }
}
