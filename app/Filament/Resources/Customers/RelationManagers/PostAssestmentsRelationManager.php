<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use App\Filament\Resources\Bookings\Schemas\BookingForm;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PostAssestmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'post_assestments';
    protected static ?string $title = 'Assesment';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components(function($record){
                if(!$record){
                   return Wizard::make(BookingForm::postAssessmentWizard())->columnSpanFull();
                }
            })
            ;
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('primary_concern')
                    ->placeholder('-'),
                TextEntry::make('booking.title')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('listing_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('start_time')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('end_time')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('bp')
                    ->placeholder('-'),
                TextEntry::make('pr')
                    ->placeholder('-'),
                TextEntry::make('o2')
                    ->placeholder('-'),
                TextEntry::make('therapist.name')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('therapist_rating')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('post_pain_rating')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('client_remarks')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('post_assesment')
            ->columns([
                TextColumn::make('primary_concern'),
                TextColumn::make('listing.title')
                    ->numeric(),
                TextColumn::make('start_time')
                    ->dateTime(),
                TextColumn::make('end_time')
                    ->dateTime(),
                TextColumn::make('bp'),
                TextColumn::make('pr'),
                TextColumn::make('o2'),
                    
                TextColumn::make('therapist.name')
                    ->numeric(),
                TextColumn::make('therapist_rating')
                    ->numeric(),
                TextColumn::make('post_pain_rating')
                    ->numeric(),
                TextColumn::make('client_remarks') ,
                TextColumn::make('next_session_date')
                    ->dateTime() ,
                TextColumn::make('created_at')
                    ->dateTime() 
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime() 
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                // AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
