<?php

namespace App\Services;

use App\Models\User;
use App\Models\Booking;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class BookingNotificationService
{
    public function sendNotification(Booking $booking, string $type = 'created'): void
    {
        $recipients = User::getAdminUsers();
        
        $booking->load(['customer', 'listing', 'therapist']);
        
        $title = $this->getTitle($type);
        $body = $this->getBody($booking, $type);
        
        foreach ($recipients as $recipient) {
            $notification = Notification::make()
                ->title($title)
                ->body($body);

            $notification = match($type) {
                'created', 'pending' => $notification->warning(),
                'confirmed', 'updated' => $notification->info(),
                'canceled', 'expired' => $notification->danger(),
                'completed' => $notification->success(),
                default => $notification->success(),
            };

            $notification
                ->actions([
                    Action::make('view')
                        ->label('View Booking')
                        ->url(route('filament.admin.resources.bookings.edit', ['record' => $booking->id]))
                        ->markAsRead(),
                ])
                ->sendToDatabase($recipient);
        }

        $this->sendSmsNotification($booking, $type);
    }

    private function sendSmsNotification(Booking $booking, string $type): void
    {
        $customerPhone = $booking->customer?->phone;

        if (empty($customerPhone)) {
            return;
        }

        $smsMessage = $this->getSmsMessage($booking, $type);
        
        if (empty($smsMessage)) {
            return;
        }

        $sms = new SmsManager();
        $sms->send($customerPhone, $smsMessage);
    }

    private function getSmsMessage(Booking $booking, string $type): ?string
    {
        $serviceName = $booking->listing?->title ?? 'Service';
        $bookingDate = Carbon::parse($booking->start_time)->format('M d, Y');
        $startTime = Carbon::parse($booking->start_time)->format('g:i A');
        $bookingNumber = $booking->booking_number;
        $price = '₱' . number_format($booking->price, 2);

        return match ($type) {
            'created' => "Your booking #{$bookingNumber} for {$serviceName} on {$bookingDate} at {$startTime} is pending. Total: {$price}. We'll confirm once payment is received.",
            'confirmed' => "Your booking #{$bookingNumber} for {$serviceName} on {$bookingDate} at {$startTime} is CONFIRMED. See you soon!",
            'canceled' => "Your booking #{$bookingNumber} for {$serviceName} on {$bookingDate} has been CANCELED. Please contact us for rescheduling.",
            'completed' => "Thank you! Your booking #{$bookingNumber} for {$serviceName} on {$bookingDate} is COMPLETED. We hope you enjoyed your experience!",
            default => null,
        };
    }

    private function getTitle(string $type): string
    {
        return match($type) {
            'created' => 'New Booking Created',
            'confirmed' => 'Booking Confirmed',
            'canceled' => 'Booking Canceled',
            'completed' => 'Booking Completed',
            'updated' => 'Booking Updated',
            'pending' => 'Booking Pending Payment',
            'expired' => 'Booking Expired',
            default => 'Booking Notification',
        };
    }

    private function getBody(Booking $booking, string $type): string
    {
        $customerName = $booking->customer?->name ?? 'N/A';
        $serviceName = $booking->listing?->title ?? 'N/A';
        $serviceType = $booking->listing?->type ?? 'N/A';
        $bookingDate = Carbon::parse($booking->start_time)->format('M d, Y');
        $startTime = Carbon::parse($booking->start_time)->format('g:i A');
        $price = '₱' . number_format($booking->price, 2);
        $status = ucfirst($booking->status);
        $bookingNumber = $booking->booking_number;

        return match($type) {
            'created' => "Appointment {$bookingNumber} has been created for {$customerName}. Service: {$serviceName} ({$serviceType}) scheduled for {$bookingDate} at {$startTime}. Total: {$price}. Status: {$status}.",
            'confirmed' => "Appointment {$bookingNumber} for {$customerName} has been confirmed. Service: {$serviceName} ({$serviceType}) on {$bookingDate} at {$startTime}. Total: {$price}.",
            'canceled' => "Appointment {$bookingNumber} for {$customerName} has been canceled. Service: {$serviceName} on {$bookingDate}.",
            'completed' => "Appointment {$bookingNumber} for {$customerName} has been completed. Service: {$serviceName} on {$bookingDate}.",
            'updated' => "Appointment {$bookingNumber} for {$customerName} has been updated. Service: {$serviceName} on {$bookingDate}. Status: {$status}.",
            'pending' => "Appointment {$bookingNumber} for {$customerName} is pending payment. Service: {$serviceName} ({$serviceType}) on {$bookingDate} at {$startTime}. Total: {$price}.",
            'expired' => "Appointment {$bookingNumber} for {$customerName} has expired. Service: {$serviceName} on {$bookingDate} was not completed.",
            default => "Booking {$bookingNumber} notification for {$customerName}.",
        };
    }
}
