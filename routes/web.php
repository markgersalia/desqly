<?php

use App\Livewire\BookingForm;
use App\Livewire\ExternalBookingForm;
use App\Livewire\RegistrationForm;
use App\Livewire\ReservationForm;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('home');
});

Route::get('/home2', function () {
    return view('home2');
});

 
Route::get('/book', BookingForm::class)->name('book');

