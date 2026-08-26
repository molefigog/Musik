<x-filament-panels::page>
    <audio src="{{ Storage::url($music->file_src) }}" id="audio"></audio>

    <div x-data="wavePlayer()" x-init="init()">
        <div x-ref="waveform"></div>

        <button @click="exportImage">Export</button>

        <img :src="image">
    </div>
</x-filament-panels::page>
