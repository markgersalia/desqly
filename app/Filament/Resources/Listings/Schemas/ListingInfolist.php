<?php

namespace App\Filament\Resources\Listings\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ListingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make([
                    Section::make([

                        ImageEntry::make('images')->columnSpanFull(),
                        TextEntry::make('title')->columnSpanFull(),
                        TextEntry::make('description')
                            ->html()
                            ->columnSpanFull(),
                        TextEntry::make('type')
                            ->badge()->columnSpanFull(),
                        TextEntry::make('price')
                            ->money()
                            ->placeholder('-')->columnSpanFull(),

                        TextEntry::make('availability'),
                    ])->columns(2),
                ])->columnSpan(2),

                Group::make([
                    Section::make([
                        TextEntry::make('created_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->dateTime()
                            ->placeholder('-'),
                    ])

                ])->columnSpan(1),

            ])->columns(3);
    }
}
