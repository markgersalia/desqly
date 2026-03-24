<?php

namespace App\Filament\SuperAdmin\Resources\Companies;

use App\Filament\SuperAdmin\Resources\Companies\Pages\CreateCompany;
use App\Filament\SuperAdmin\Resources\Companies\Pages\EditCompany;
use App\Filament\SuperAdmin\Resources\Companies\Pages\ListCompanies;
use App\Filament\SuperAdmin\Resources\Companies\RelationManagers\UsersRelationManager;
use App\Models\Company;
use BackedEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Operation;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::BuildingOffice2;

    protected static UnitEnum | string | null $navigationGroup = 'SaaS Management';

    public static function form(Schema $schema): Schema
    {
        $timezones = array_combine(timezone_identifiers_list(), timezone_identifiers_list());

        return $schema
            ->components([
            Group::make([
                Section::make('Company')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, callable $set, callable $get): void {
                                if (filled($get('slug'))) {
                                    return;
                                }

                                $set('slug', Str::slug((string) $state));
                            }),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->dehydrateStateUsing(fn (?string $state): string => Str::slug((string) $state))
                            ->unique(Company::class, 'slug', ignoreRecord: true),
                        FileUpload::make('avatar')
                            ->label('Company Avatar')
                            ->avatar()
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('companies/avatars')
                            ->visibility('public')
                            ->maxSize(2048),
                        Select::make('plan_code')
                            ->required()
                            ->options([
                                'starter' => 'Starter',
                                'growth' => 'Growth',
                                'pro' => 'Pro',
                                'enterprise' => 'Enterprise',
                            ])
                            ->default('starter'),
                        Select::make('subscription_status')
                            ->required()
                            ->options([
                                'trialing' => 'Trialing',
                                'active' => 'Active',
                                'past_due' => 'Past due',
                                'canceled' => 'Canceled',
                                'unpaid' => 'Unpaid',
                            ])
                            ->default('trialing'),
                        DateTimePicker::make('trial_ends_at'),
                        DateTimePicker::make('current_period_ends_at'),
                        Toggle::make('is_active')
                            ->required()
                            ->default(true),
                    ])
                    ->columns(2),
                      Section::make('Initial Company Admin')
                    ->description('These fields are used only during company creation.')
                    ->schema([
                        TextInput::make('admin_name')
                            ->label('Admin Name')
                            ->required()
                            ->maxLength(255)
                            ->hiddenOn(Operation::Edit),
                        TextInput::make('admin_email')
                            ->label('Admin Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique('users', 'email')
                            ->hiddenOn(Operation::Edit),
                        TextInput::make('admin_password')
                            ->label('Admin Password')
                            ->password()
                            ->required()
                            ->minLength(8)
                            ->maxLength(255)
                            ->hiddenOn(Operation::Edit),
                    ]),
            ]),
                Section::make('Core Settings')
                    ->schema([
                        Select::make('business.entity_type')
                            ->label('Business Entity')
                            ->required()
                            ->options([
                                'company' => 'Company',
                                'individual' => 'Individual',
                            ])
                            ->default('company')
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set): void {
                                $set('booking.requires_staff', $state === 'company');
                            }),
                        Select::make('business.timezone')
                            ->label('Timezone')
                            ->required()
                            ->options($timezones)
                            ->searchable(),
                        TextInput::make('business.currency')
                            ->label('Currency (3 letters)')
                            ->required()
                            ->length(3)
                            ->maxLength(3)
                            ->alpha()
                            ->dehydrateStateUsing(fn ($state) => strtoupper((string) $state)),
                        Toggle::make('booking.has_listings')
                            ->label('Enable services/listings')
                            ->required(),
                        Toggle::make('booking.requires_staff')
                            ->label('Require staff')
                            ->helperText('Automatically ON for company and OFF for individual mode.')
                            ->disabled()
                            ->dehydrated(true),
                        Toggle::make('booking.requires_bed')
                            ->label('Require resource')
                            ->required(),
                        Toggle::make('booking.requires_follow_up')
                            ->label('Require follow-up')
                            ->required(),
                        Select::make('booking.mode')
                            ->label('Booking Mode')
                            ->options([
                                'time_slot' => 'Time Slot',
                                'whole_day' => 'Whole Day',
                            ])
                            ->required()
                            ->default('time_slot'),
                        TextInput::make('booking.slot_interval_minutes')
                            ->label('Slot Interval (minutes)')
                            ->integer()
                            ->minValue(5)
                            ->maxValue(180)
                            ->required(),
                        TextInput::make('booking.day_start')
                            ->label('Day Start')
                            ->type('time')
                            ->required(),
                        TextInput::make('booking.day_end')
                            ->label('Day End')
                            ->type('time')
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
                    ])
                    ->columns(2),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar')
                    ->label('Avatar') 
                    ->circular()
                    ->defaultImageUrl(asset('images/logo.png')),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('plan_code')
                    ->badge()
                    ->sortable(),
                TextColumn::make('subscription_status')
                    ->badge()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Users')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('subscription_status')
                    ->options([
                        'trialing' => 'Trialing',
                        'active' => 'Active',
                        'past_due' => 'Past due',
                        'canceled' => 'Canceled',
                        'unpaid' => 'Unpaid',
                    ]),
                SelectFilter::make('plan_code')
                    ->options([
                        'starter' => 'Starter',
                        'growth' => 'Growth',
                        'pro' => 'Pro',
                        'enterprise' => 'Enterprise',
                    ]),
            ])
            ->recordActions([
                \Filament\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            UsersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCompanies::route('/'),
            'create' => CreateCompany::route('/create'),
            'edit' => EditCompany::route('/{record}/edit'),
        ];
    }
}



