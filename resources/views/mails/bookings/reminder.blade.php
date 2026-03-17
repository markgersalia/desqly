@component('mail::message')
# Upcoming Booking Reminder

Hello {{ $user_name ?? 'Customer' }},

This is a friendly reminder that you have an upcoming booking:

**Booking Number:** {{ $data['booking_number'] }}   
**Service:** {{ $data['listing']['title'] ?? 'N/A' }}   
**Therapist:** {{ $data['therapist']['name'] ?? 'N/A' }}  
**Date & Time:** {{ \Carbon\Carbon::parse($data['start_time'])->format('M d, Y h:i A') }} - {{ \Carbon\Carbon::parse($data['end_time'])->format('h:i A') }}  
**Status:** {{ ucfirst($data['status']) }}  
**Price:** {{ isset($data['price']) ? 'PHP' . number_format($data['price'], 2) : 'N/A' }}  


@if(!empty($notes))
**Notes:**  
{{ $data['notes'] }}
@endif


If you have any questions or concerns, please feel free to contact us at **{{ config('app.name') }}**.  We are here to assist you.


{{-- @component('mail::button', ['url' => $booking_url ?? '#'])
View Booking
@endcomponent --}}

Thank you for choosing **{{ config('app.name') }}**.

@endcomponent
