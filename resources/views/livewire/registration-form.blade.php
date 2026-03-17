<div class="max-w-2xl mx-auto py-12"> <form wire:submit="create">
        <x-filament::section>
            <x-slot name="heading">
                Booking Information
            </x-slot>

            <div class="grid grid-cols-1 gap-y-6"> {{ $this->form }}
            </div>

            <x-slot name="footer">
                <x-filament::button type="submit" size="lg">
                    Submit Booking
                </x-filament::button>
            </x-slot>
        </x-filament::section>
    </form>

    <x-filament-actions::modals />
</div>