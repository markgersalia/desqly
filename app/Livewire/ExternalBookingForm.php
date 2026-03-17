<?php

namespace App\Livewire;

use App\Models\Booking;
use Livewire\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Schema;

class ExternalBookingForm extends Component implements HasForms
{
   
    
    
    use InteractsWithSchemas;
    
    public ?array $data = [];
    
    public function mount(): void
    {
        $this->form->fill();
    }
    protected static string $layout = 'components.layouts.app';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                MarkdownEditor::make('content'),
                MarkdownEditor::make('content'),
                MarkdownEditor::make('content'),
                MarkdownEditor::make('content'),
                // ...
            ])
            ->statePath('data');
    }
    
    public function create(): void
    {
        dd($this->form->getState());
    }
    
    public function render()
    {
        return view('livewire.home.external-booking-form');
    }
}

