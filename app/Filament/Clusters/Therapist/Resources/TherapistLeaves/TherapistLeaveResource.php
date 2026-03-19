<?php

namespace App\Filament\Clusters\Therapist\Resources\TherapistLeaves;

use App\Filament\Clusters\Therapist\Resources\TherapistLeaves\Pages\ManageTherapistLeaves;
use App\Filament\Clusters\Therapist\TherapistCluster;
use App\Models\Therapist;
use App\Models\TherapistLeave;
use App\Services\BusinessSettings;
use App\TherapistLeaveType;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TherapistLeaveResource extends Resource
{
    protected static ?string $model = TherapistLeave::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = TherapistCluster::class;

    public static function canAccess(): bool
    {
        return app(BusinessSettings::class)->requiresStaffAssignment();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return self::canAccess();
    }

    public static function schema(): array
    {
        return [
            Select::make('therapist_id')
                ->label('Therapist')
                ->reactive()
                ->options(fn () => Therapist::active()->pluck('name', 'id'))
                ->disableOptionWhen(function ($value, callable $get, $record) {
                    $start = $get('start_date');
                    $end = $get('end_date');

                    if ($record && (int) $record->therapist_id === (int) $value) {
                        return false;
                    }

                    if (! $start || ! $end) {
                        return false;
                    }

                    return TherapistLeave::where('therapist_id', $value)
                        ->where(function ($q) use ($start, $end) {
                            $q->whereBetween('start_date', [$start, $end])
                                ->orWhereBetween('end_date', [$start, $end])
                                ->orWhere(function ($q) use ($start, $end) {
                                    $q->where('start_date', '<=', $start)
                                        ->where('end_date', '>=', $end);
                                });
                        })
                        ->exists();
                })
                ->required(fn ($record) => ! $record)
                ->dehydrated()
                ->columnSpanFull(),
            DateTimePicker::make('start_date')
                ->required()
                ->reactive()
                ->columnSpan(1),
            DateTimePicker::make('end_date')
                ->required()
                ->reactive()
                ->columnSpan(1),
            Select::make('status')
                ->default('pending')
                ->options(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'])
                ->required(),
            Select::make('type')
                ->label('Leave Type')
                ->options(TherapistLeaveType::options())
                ->required(),
            Textarea::make('reason')
                ->columnSpanFull()
                ->rows(5),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components(self::schema())->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('therapist.name')
                    ->sortable(),
                TextColumn::make('reason')
                    ->searchable(),
                TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
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
            'index' => ManageTherapistLeaves::route('/'),
        ];
    }
}
