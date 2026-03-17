@component('mail::message')
# Booking Completed

Hello {{ $user_name ?? 'Customer' }},

Your booking has been successfully completed. We hope you had a great experience!  

**Booking Number:** {{ $data['booking_number'] }}   
**Service:** {{ $data['listing']['title'] ?? 'N/A' }}   
**Therapist:** {{ $data['therapist']['name'] ?? 'N/A' }}  
**Date & Time:** {{ \Carbon\Carbon::parse($data['start_time'])->format('M d, Y h:i A') }} - {{ \Carbon\Carbon::parse($data['end_time'])->format('h:i A') }}  
**Status:** {{ ucfirst($data['status']) }}  
**Price:** {{ isset($data['price']) ? 'PHP' . number_format($data['price'], 2) : 'N/A' }}  

@if(!empty($notes))
**Notes:**  
{{ $notes }}
@endif

{{-- @component('mail::button', ['url' => $booking_url ?? '#'])
View Booking
@endcomponent --}}

Thank you for choosing **{{ config('app.name') }}**. We hope to see you again soon!  

Best regards,  
**{{ config('app.name') }}**
@endcomponent