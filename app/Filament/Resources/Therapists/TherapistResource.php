<?php

namespace App\Filament\Resources\Therapists;

use App\Filament\Clusters\Therapist\TherapistCluster;
use App\Filament\Resources\Therapists\Pages\CreateTherapist;
use App\Filament\Resources\Therapists\Pages\EditTherapist;
use App\Filament\Resources\Therapists\Pages\ListTherapists;
use App\Filament\Resources\Therapists\Schemas\TherapistForm;
use App\Filament\Resources\Therapists\Tables\TherapistsTable;
use App\Models\Therapist;
use App\Services\BusinessSettings;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class TherapistResource extends Resource
{
    protected static ?string $model = Therapist::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Users;

    protected static ?string $cluster = TherapistCluster::class;

    protected static ?string $recordTitleAttribute = 'therapist';

    public static function getModelLabel(): string
    {
        return business_label('staff', 'Therapist');
    }

    public static function getPluralModelLabel(): string
    {
        return Str::plural(self::getModelLabel());
    }

    public static function canAccess(): bool
    {
        return app(BusinessSettings::class)->requiresStaffAssignment();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return self::canAccess();
    }

    public static function form(Schema $schema): Schema
    {
        return TherapistForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TherapistsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTherapists::route('/'),
            'create' => CreateTherapist::route('/create'),
            'edit' => EditTherapist::route('/{record}/edit'),
        ];
    }
}
