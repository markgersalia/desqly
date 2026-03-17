<?php

namespace App\Filament\Resources\Bookings\Schemas;

use Dom\Text;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class BookingInfolist
{

    public static function configure(Schema $schema): Schema
    {

        return $schema->components([]);
        // return $schema
        //     ->components([
        //         Group::make([
        //             Section::make('Booking Details')
        //                 ->columns(2)
        //                 ->schema([

        //                     TextEntry::make('booking_number'),

        //                     ImageEntry::make('listing.images')
        //                         ->label('Preview')
        //                         ->square()->columnSpanFull(),

        //                     TextEntry::make('listing.title')
        //                         ->label('Listing')
        //                         ->columnSpanFull()
        //                         ->icon('heroicon-m-home'),


        //                     TextEntry::make('price') 
        //                         ->columnSpanFull()
        //                         ->icon(Heroicon::OutlinedCurrencyDollar),


        //                     TextEntry::make('start_time')
        //                         ->label('Start Time')
        //                         ->dateTime()
        //                         ->icon('heroicon-m-calendar'),


        //                     TextEntry::make('end_time')
        //                         ->label('End Time')
        //                         ->dateTime()
        //                         ->icon('heroicon-m-calendar-days'),

        //                     TextEntry::make('notes')
        //                         ->placeholder('-')
        //                         ->label('Notes')
        //                         ->columnSpanFull(),


        //                 ]),

        //             Section::make('Customer Info')->schema([

        //                 ImageEntry::make('customer.image')
        //                     ->label('')
        //                     ->circular(),
        //                 TextEntry::make('customer.name')
        //                     ->label('Customer')
        //                     ->icon('heroicon-m-user-circle'),

        //                 TextEntry::make('customer.email')
        //                     ->label('Email')
        //                     ->icon('heroicon-m-envelope'),

        //                 TextEntry::make('customer.phone')
        //                     ->label('Phone')
        //                     ->icon('heroicon-m-phone'),


        //                 TextEntry::make('customer.address')
        //                     ->label('Phone')
        //                     ->icon('heroicon-m-map'),


        //             ]),
        //         ])->columnSpan(2),
        //         Group::make([
        //             Section::make('Additional Info')
        //                 ->columns(1)
        //                 ->schema([


        //                     TextEntry::make('status')
        //                         ->badge()
        //                         ->color(fn($state) => match ($state) {
        //                             'pending' => 'warning',
        //                             'approved' => 'success',
        //                             'cancelled' => 'danger',
        //                             'confirmed' => 'success',
        //                             default => 'gray',
        //                         }),

        //                     TextEntry::make('user.name')
        //                         ->label('Processed By')
        //                         ->icon('heroicon-m-user'),

        //                     TextEntry::make('created_at')
        //                         ->label('Created At')
        //                         ->dateTime()
        //                         ->placeholder('-')
        //                         ->icon('heroicon-m-clock'),

        //                     TextEntry::make('updated_at')
        //                         ->label('Last Updated')
        //                         ->dateTime()
        //                         ->placeholder('-')
        //                         ->icon('heroicon-m-clock'),
        //                 ]),
                        
        //             Section::make('Therapist Info')->schema([

        //                 ImageEntry::make('therapist.image') 
        //                     ->circular(),
        //                 TextEntry::make('therapist.name'),

        //                 TextEntry::make('therapist.email')
        //                     ->label('Email'),

        //                 TextEntry::make('therapist.phone')
        //                     ->label('Phone'),
 
        //             ]),
        //         ])->columnSpan(1)



        //     ])->columns(3);
    }
}
