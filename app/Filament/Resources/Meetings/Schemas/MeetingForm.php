<?php

namespace App\Filament\Resources\Meetings\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MeetingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make([

                    Select::make('customer_id')
                        ->relationship('customer', 'name'),
                    Select::make('user_id')
                        ->relationship('user', 'name'),
                    TextInput::make('title')
                        ->required(),
                    Textarea::make('description')
                        ->columnSpanFull(),
                    DateTimePicker::make('start_time')
                        ->required(),
                    DateTimePicker::make('end_time')
                        ->required(),
                    Toggle::make('all_day')
                        ->required(),
                    TextInput::make('location'),

                ])->columnSpan(2),
                Section::make([
                    Select::make('status')
                        ->options([
                            'scheduled' => 'Scheduled',
                            'confirmed' => 'Confirmed',
                            'canceled' => 'Canceled',
                            'completed' => 'Completed',
                        ])
                        ->default('scheduled')
                        ->required(),
                    ColorPicker::make('color'),
                ])->columnSpan(1)


            ])->columns(3);
    }
}
