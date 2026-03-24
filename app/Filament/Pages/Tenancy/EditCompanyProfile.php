<?php

namespace App\Filament\Pages\Tenancy;

use App\Models\Company;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Tenancy\EditTenantProfile;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EditCompanyProfile extends EditTenantProfile
{
    public static function getLabel(): string
    {
        return 'Company profile';
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
                TextInput::make('name')
                    ->label('Company Name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->label('Company Slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(Company::class, 'slug', ignoreRecord: true)
                    ->dehydrateStateUsing(fn (?string $state): string => Str::slug((string) $state)),
                FileUpload::make('avatar')
                    ->label('Company Avatar')
                    ->avatar()
                    ->image()
                    ->imageEditor()
                    ->disk('public')
                    ->directory('companies/avatars')
                    ->visibility('public')
                    ->maxSize(2048),
            ]);
    }
}
