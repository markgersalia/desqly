<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure($schema)
    {
        return $schema->schema(self::schema())->columns(3);
    }
    public static function schema(): array
    {
        return [
            Group::make([
                Section::make('Customer Details')
                    ->schema([

                        TextInput::make('name')
                            ->required(),


                        Select::make('gender')
                            ->options([
                                'male',
                                'female'
                            ]),

                        TextInput::make('occupation')->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Contact Details')
                    ->schema([

                        TextInput::make('messenger'),

                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->required(),
                        TextInput::make('phone')
                            ->required()
                            ->tel(),
                        Textarea::make('address')
                            ->columnSpanFull(),
                    ])->columns(3),

               
            ])
                ->columnSpan(2),

            Section::make('Details')
                ->schema([

                    Toggle::make('is_vip')
                        ->required(),
                    FileUpload::make('image')->avatar(),
                ])
        ];
    }
}
