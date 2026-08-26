<div x-data="waveformPlayer()" x-init="init()" wire:ignore class="space-y-3">

    <input type="file" x-ref="input" @change="loadFile" accept="audio/*">

    <div x-ref="waveform"></div>

    <button type="button" @click="exportImage">
        Export PNG
    </button>

    <img x-show="image" :src="image" />

</div>

<script type="module">
    import WaveSurfer from "https://cdn.jsdelivr.net/npm/wavesurfer.js@7/dist/wavesurfer.esm.js";

    window.waveformPlayer = () => ({
        wavesurfer: null,
        image: null,

        init() {},

        loadFile() {
            const file = this.$refs.input.files[0]
            if (!file) return

            const url = URL.createObjectURL(file)

            if (this.wavesurfer) {
                this.wavesurfer.destroy()
            }

            this.wavesurfer = WaveSurfer.create({
                container: this.$refs.waveform,
                waveColor: '#4F4A85',
                progressColor: '#ff5500',
                height: 150,
            })

            this.wavesurfer.load(url)
        },

        exportImage() {
            if (!this.wavesurfer) return

            const canvas = this.$refs.waveform.querySelector('canvas')
            if (!canvas) return

            this.image = canvas.toDataURL('image/png')
        }
    })
</script>
