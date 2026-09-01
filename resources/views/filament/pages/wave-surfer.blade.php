<x-filament-panels::page>

    <div class="min-h-screen relative overflow-hidden">

        {{-- BACKGROUND GLOW --}}
        <div class="absolute inset-0 bg-gradient-to-br from-black via-gray-900 to-black"></div>

        <div class="absolute -top-40 -left-40 w-[500px] h-[500px] bg-purple-600/30 blur-[120px] rounded-full"></div>
        <div class="absolute bottom-0 right-0 w-[500px] h-[500px] bg-blue-500/20 blur-[140px] rounded-full"></div>

        <div class="relative max-w-6xl mx-auto px-6 py-10 space-y-10 text-white">

            {{-- HEADER --}}
            <div class="flex items-center justify-between">

                <div class="flex items-center gap-4">

                    <img src="{{ $music->release?->art_cover ? Storage::url($music->release->art_cover) : 'https://via.placeholder.com/80' }}"
                        class="w-16 h-16 rounded-2xl object-cover shadow-lg border border-white/10" />

                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">
                            {{ $music->title }}
                        </h1>

                        <p class="text-sm text-white/60">
                            {{ $music->release?->title ?? 'No Release' }}
                        </p>
                    </div>

                </div>

                <div class="text-xs text-white/50">
                    {{ $music->created_at?->diffForHumans() }}
                </div>

            </div>

            {{-- MAIN GLASS PANEL --}}
            <div class="backdrop-blur-xl bg-white/5 border border-white/10 rounded-3xl shadow-2xl overflow-hidden">

                <div class="p-8">

                    <audio id="audio" src="{{ Storage::url($music->file_src) }}"></audio>

                    {{-- WAVEFORM --}}
                    <div wire:ignore>
                        <div id="waveform" class="h-44 rounded-2xl bg-white/5 border border-white/10"></div>
                    </div>

                </div>

                {{-- ACTION BAR --}}
                <div class="flex items-center justify-between px-8 py-6 border-t border-white/10 bg-white/5">

                    <p class="text-sm font-medium text-amber-400 flex items-center gap-2 animate-pulse">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Click Save to generate and save the waveform image. This may take a few seconds.
                    </p>

                    <form id="waveformForm" method="POST" action="{{ route('waveform.save', $music) }}"
                        class="flex items-center gap-4">

                        @csrf
                        <input type="hidden" name="waveform" id="waveformInput">
                        <input type="checkbox" name="is_published" id="publishCheckbox" checked
                            class="accent-indigo-600 h-5 w-5">

                        <button id="saveBtn" type="submit"
                            class="px-6 py-2 rounded-xl bg-black text-white font-semibold hover:bg-gray-200 transition shadow-lg">
                            Save & Return
                        </button>

                    </form>

                </div>

            </div>

            {{-- DETAILS GLASS CARDS --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- RELEASE --}}
                <div class="backdrop-blur-xl bg-white/5 border border-white/10 rounded-3xl p-5">
                    <p class="text-xs text-white/50 mb-4">Release</p>
                    <div class="flex items-center gap-4">
                        <img src="{{ $music->release?->art_cover ? Storage::url($music->release->art_cover) : 'https://via.placeholder.com/80' }}"
                            class="w-14 h-14 rounded-xl object-cover border border-white/10" />
                        <div>
                            <div class="font-semibold">
                                {{ $music->release?->title ?? 'No Release' }}
                            </div>

                            <div class="text-xs text-white/40">
                                Music Collection
                            </div>
                        </div>
                    </div>
                </div>

                {{-- INFO --}}
                <div class="lg:col-span-2 backdrop-blur-xl bg-white/5 border border-white/10 rounded-3xl p-6">
                    <div class="grid grid-cols-2 lg:grid-cols-3 gap-6 text-sm">
                        <div>
                            <p class="text-white/40 text-xs">Duration</p>
                            <p class="font-semibold">{{ $music->duration ?? 'Unknown' }}</p>
                        </div>

                        <div>
                            <p class="text-white/40 text-xs">Size</p>
                            <p class="font-semibold">{{ $music->size }} MB</p>
                        </div>

                        <div>
                            <p class="text-white/40 text-xs">File</p>
                            <p class="text-xs break-all text-white/60">
                                {{ $music->file_name }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- JS stays EXACTLY the same --}}
    @push('scripts')
        <script src="{{ asset('js/wavesurfer.min.js') }}"></script>

        <script>
            document.addEventListener('DOMContentLoaded', () => {

                const audio = document.getElementById('audio');
                const waveformEl = document.getElementById('waveform');
                const form = document.getElementById('waveformForm');
                const input = document.getElementById('waveformInput');
                const btn = document.getElementById('saveBtn');
                const publishCheckbox = document.getElementById('publishCheckbox');

                const wavesurfer = WaveSurfer.create({
                    container: waveformEl,
                    waveColor: '#4F4A85',
                    progressColor: '#bbbbbb',
                    height: 150,
                    backend: 'WebAudio',
                });

                wavesurfer.load(audio.src);

                function canvasToBlob(canvas) {
                    return new Promise(resolve => {
                        canvas.toBlob(blob => resolve(blob), 'image/png');
                    });
                }

                form.addEventListener('submit', async (e) => {
                    e.preventDefault();

                    btn.disabled = true;
                    btn.innerText = 'Generating...';

                    try {
                        await new Promise(r => requestAnimationFrame(r));
                        await new Promise(r => requestAnimationFrame(r));

                        const canvas = document.querySelector('#waveform canvas');
                        if (!canvas) throw new Error('Canvas not ready');
                        const blob = await canvasToBlob(canvas);
                        const formData = new FormData(form);
                        formData.append('waveform_file', blob, 'waveform.png');

                        const response = await fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                            }
                        });

                        if (!response.ok) throw new Error('Upload failed');
                        window.location.href = response.url;

                    } catch (err) {
                        console.error(err);
                        alert(err.message);

                        btn.disabled = false;
                        btn.innerText = 'Save & Return';
                    }
                });

            });
        </script>
    @endpush

</x-filament-panels::page>
