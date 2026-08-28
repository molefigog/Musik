<x-filament-panels::page>
    <form wire:submit="send">
        {{ $this->form }}

        <x-filament::button type="submit" class="mt-4">
            Send Command
        </x-filament::button>
    </form>
</x-filament-panels::page>
