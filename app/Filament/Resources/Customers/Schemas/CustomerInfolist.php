<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make([
                    ImageEntry::make('image')->circular(),
                    TextEntry::make('name'),
                    TextEntry::make('email')
                        ->label('Email address'),
                    TextEntry::make('phone')
                        ->placeholder('-'),
                    TextEntry::make('address')
                        ->placeholder('-')
                        ->columnSpanFull(),
                ])->columnSpan(2),
                Section::make([

                    IconEntry::make('is_vip')
                        ->boolean(),
                    TextEntry::make('created_at')
                        ->dateTime()
                        ->placeholder('-'),
                    TextEntry::make('updated_at')
                        ->dateTime()
                        ->placeholder('-'),
                ]),

            ])->columns(3);
    }
}
