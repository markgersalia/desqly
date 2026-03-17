<?php

namespace App\Filament\Resources\Listings\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ListingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->schema([
                    Select::make('category_id')
                        ->label('Category')
                        ->relationship('category', 'name'),
                    TextInput::make('title')
                        ->required(),
                    RichEditor::make('description')
                        
                        ->columnSpanFull(),
                    // Select::make('type')
                    //     ->options([
                    //         'room' => 'Room',
                    //         'service' => 'Service',
                    //         'event' => 'Event',
                    //         'apartment' => 'Apartment',
                    //         'house' => 'House',
                    //         'studio' => 'Studio',
                    //         'transport' => 'Transport',
                    //         'equipment' => 'Equipment',
                    //         'experience' => 'Experience',
                    //         'misc' => 'Misc',
                    //     ])
                    //     ->default('service')
                    //     ->required(),
                    TextInput::make('price')
                        ->numeric()
                        ->prefix('PHP'), 
                        Toggle::make('is_always_available')
                        ->default(true)
                    ->label('Always Available')
                    ->reactive(),
                    DateTimePicker::make('available_from')
                        ->hidden(fn (callable $get) => $get('is_always_available'))
                        ->required(),
                    DateTimePicker::make('available_to')
                        ->hidden(fn (callable $get) => $get('is_always_available'))
                        ->required(),
                ])->columnSpan(2),
                Section::make()->schema([

                    FileUpload::make('images')
                        ->multiple()
                        // ->required(),
                ])->columnSpan(1),

            ])->columns(3);
    }
}
