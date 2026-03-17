<?php

namespace App\Filament\Resources\Beds;

// use App\Filament\Clusters\Booking\BookingCluster;
use App\Filament\Resources\Beds\Pages\ManageBeds;
use App\Models\Branch;
use App\Models\Bed;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class BedResource extends Resource
{
    protected static ?string $model = Bed::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'bed';

    protected static ?int $navigationSort = 3;
    protected static UnitEnum|string|null $navigationGroup = 'Booking Management';

    public static function getModelLabel(): string
    {
        return business_label('resource', 'Bed');
    }

    public static function getPluralModelLabel(): string
    {
        return Str::plural(self::getModelLabel());
    }

    /**
     * Hide this resource if beds are not required
     */
    public static function shouldRegisterNavigation(): bool
    {
        return config('booking.requires_bed', false);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Toggle::make('is_available')->default(true),
                Select::make('branch_id')
                    ->label('Branch')
                    ->options(Branch::query()->orderBy('name')->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('name')
                    ->required(),
                Textarea::make('description')
                    ->placeholder("Add bed description"),
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('bed')
            ->columns([
                TextColumn::make('branch.name')
                    ->label('Branch')
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('description'),
                ToggleColumn::make('is_available'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('branch_id')
                    ->relationship('branch', 'name')
                    ->label('Branch'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageBeds::route('/'),
        ];
    }
}
