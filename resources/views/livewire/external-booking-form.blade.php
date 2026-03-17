<div class="p-8">
    <form wire:submit="create">
        {{ $this->form }}
        
        <button type="submit" class="bg-primary-600 px-4 py-2 text-white rounded">
            Submit
        </button>
    </form>

    <x-filament-actions::modals />
</div>