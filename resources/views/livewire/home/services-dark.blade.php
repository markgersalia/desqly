@extends('livewire.home.services')

@section('styles')
<style>
#services .bg-white {
    background-color: #242924 !important;
}

#services .hover\:bg-white:hover {
    background-color: #2a302a !important;
}

#services .text-white {
    color: var(--color-dark) !important;
}

#services .hover\:bg-white:hover.text-primary {
    color: var(--color-primary) !important;
}

#services .hover\:bg-white:hover.text-dark {
    color: var(--color-dark) !important;
}
</style>
@endsection