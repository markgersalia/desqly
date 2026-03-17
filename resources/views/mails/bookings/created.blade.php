@component('mail::message')
# Booking Confirmation

Hello {{ $data['customer']['name'] ?? 'Customer' }},

Your booking has been successfully created. Here are the details:

**Booking Number:** {{ $data['booking_number'] }}   
**Service:** {{ $data['listing']['title'] ?? 'N/A' }}   
**Therapist:** {{ $data['therapist']['name'] ?? 'N/A' }}  
**Date & Time:** {{ \Carbon\Carbon::parse($data['start_time'])->format('M d, Y h:i A') }} - {{ \Carbon\Carbon::parse($data['end_time'])->format('h:i A') }}  
**Status:** {{ ucfirst($data['status']) }}  
**Price:** {{ isset($data['price']) ? 'PHP' . number_format($data['price'], 2) : 'N/A' }}  

@if(!empty($data['notes']))
**Booking Note:**  
{{ $data['notes'] }}
@endif




**Please note:** Your booking is currently **pending confirmation**.  
Our team is reviewing your booking and will confirm it shortly.  


{{-- @component('mail::button', ['url' => $data['booking_url'] ?? '#'])
View Booking
@endcomponent --}}

Thank you for booking with us!  

Best regards,  
**{{ config('app.name') }}**
@endcomponent
