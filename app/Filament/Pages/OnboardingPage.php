<?php

namespace App\Filament\Pages;

use App\Models\Branch;
use App\Services\BusinessSettings;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;

class OnboardingPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = null;

    protected static ?string $title = 'Business Onboarding';

    protected static ?string $slug = 'onboarding';

    protected string $view = 'filament.pages.onboarding-page';

    public ?array $data = [];

    public array $timezones = [];

    public function mount(BusinessSettings $businessSettings): void
    {
        if ($businessSettings->isOnboardingComplete()) {
            $this->redirect(route('filament.admin.pages.dashboard'), navigate: true);

            return;
        }

        $defaults = $businessSettings->defaults();
        $settings = $businessSettings->getSettings();

        $this->timezones = timezone_identifiers_list();

        $this->form->fill(array_replace_recursive([
            'business' => $defaults['business'],
            'booking' => $defaults['booking'],
            'labels' => $defaults['labels'],
            'initial_branch' => [
                'name' => 'Main Branch',
                'address' => '',
            ],
        ], $settings));
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
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make('Business Profile')
                        ->schema([
                            TextInput::make('business.name')
                                ->label('Business Name')
                                ->required()
                                ->maxLength(255),
                            Select::make('business.type')
                                ->label('Business Type')
                                ->options([
                                    'spa' => 'Spa',
                                    'clinic' => 'Clinic',
                                    'gym' => 'Gym',
                                    'generic' => 'Generic',
                                ])
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set): void {
                                    if (! is_string($state)) {
                                        return;
                                    }

                                    $preset = app(BusinessSettings::class)->getTemplatePreset($state);

                                    $set('booking.requires_bed', $preset['booking']['requires_bed']);
                                    $set('booking.requires_follow_up', $preset['booking']['requires_follow_up']);
                                    $set('labels.staff', $preset['labels']['staff']);
                                    $set('labels.resource', $preset['labels']['resource']);
                                    $set('labels.service', $preset['labels']['service']);
                                    $set('labels.booking', $preset['labels']['booking']);
                                }),
                            Select::make('business.timezone')
                                ->label('Timezone')
                                ->options(array_combine($this->timezones, $this->timezones))
                                ->searchable()
                                ->required(),
                            TextInput::make('business.currency')
                                ->label('Currency (3 letters)')
                                ->required()
                                ->length(3)
                                ->maxLength(3)
                                ->alpha()
                                ->dehydrateStateUsing(fn ($state) => strtoupper((string) $state)),
                        ])
                        ->columns(2),
                    Step::make('Booking Rules')
                        ->schema([
                            Toggle::make('booking.has_listings')
                                ->label('Enable services/listings')
                                ->required(),
                            Toggle::make('booking.requires_bed')
                                ->label('Require resource selection')
                                ->required(),
                            Toggle::make('booking.requires_follow_up')
                                ->label('Require follow-up workflow')
                                ->required(),
                            TextInput::make('booking.slot_interval_minutes')
                                ->label('Slot Interval (minutes)')
                                ->integer()
                                ->minValue(5)
                                ->maxValue(180)
                                ->required(),
                            TextInput::make('booking.expire_after_hours')
                                ->label('Expire After (hours)')
                                ->integer()
                                ->minValue(1)
                                ->maxValue(168)
                                ->required(),
                            TextInput::make('booking.grace_period_minutes')
                                ->label('Grace Period (minutes)')
                                ->integer()
                                ->minValue(1)
                                ->maxValue(240)
                                ->required(),
                            TextInput::make('booking.day_start')
                                ->label('Day Start')
                                ->type('time')
                                ->required(),
                            TextInput::make('booking.day_end')
                                ->label('Day End')
                                ->type('time')
                                ->required(),
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
                    Step::make('Initial Branch')
                        ->schema([
                            TextInput::make('initial_branch.name')
                                ->label('Branch Name')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('initial_branch.address')
                                ->label('Address')
                                ->maxLength(255),
                        ])
                        ->columns(2),
                ])
                    ->submitAction(view('filament.pages.partials.onboarding-submit')),
            ])
            ->statePath('data');
    }

    public function submit(BusinessSettings $businessSettings): void
    {
        $state = $this->form->getState();

        $branch = Branch::create([
            'name' => data_get($state, 'initial_branch.name'),
            'address' => data_get($state, 'initial_branch.address'),
            'is_active' => true,
        ]);

        $payload = [
            'onboarding' => [
                'completed_at' => now()->toDateTimeString(),
            ],
            'business' => [
                'name' => data_get($state, 'business.name'),
                'type' => data_get($state, 'business.type'),
                'timezone' => data_get($state, 'business.timezone'),
                'currency' => strtoupper((string) data_get($state, 'business.currency')),
            ],
            'booking' => [
                'has_listings' => (bool) data_get($state, 'booking.has_listings'),
                'requires_bed' => (bool) data_get($state, 'booking.requires_bed'),
                'requires_follow_up' => (bool) data_get($state, 'booking.requires_follow_up'),
                'slot_interval_minutes' => (int) data_get($state, 'booking.slot_interval_minutes'),
                'day_start' => data_get($state, 'booking.day_start'),
                'day_end' => data_get($state, 'booking.day_end'),
                'expire_after_hours' => (int) data_get($state, 'booking.expire_after_hours'),
                'grace_period_minutes' => (int) data_get($state, 'booking.grace_period_minutes'),
            ],
            'labels' => [
                'staff' => data_get($state, 'labels.staff'),
                'resource' => data_get($state, 'labels.resource'),
                'service' => data_get($state, 'labels.service'),
                'booking' => data_get($state, 'labels.booking'),
            ],
            'branches' => [
                'default_branch_id' => $branch->id,
            ],
        ];

        $businessSettings->saveSettings($payload);
        $businessSettings->backfillBranchAssignments($branch->id);
        $businessSettings->applyRuntimeConfig();

        Notification::make()
            ->title('Onboarding completed')
            ->success()
            ->send();

        $this->redirect(route('filament.admin.pages.dashboard'), navigate: true);
    }
}
