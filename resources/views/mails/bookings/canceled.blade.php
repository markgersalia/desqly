@component('mail::message')
# Booking Cancelled

Hello {{ $user_name ?? 'Customer' }},

We regret to inform you that your booking has been canceled.  

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

{{-- @component('mail::button', ['url' => $booking_url ?? '#'])
View Booking
@endcomponent --}}

We look forward to serving you!  

Best regards,  
**{{ config('app.name') }}**
@endcomponent
