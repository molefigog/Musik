<template>
    <q-page>
        <MusicFilters v-model:filters="filters" :genres="genres" :releases="releases" />

        <!-- STORE HEADER -->
        <div class="store-header">
            <div class="brand-block">
                <div class="brand-icon">
                    <q-icon name="headphones" size="18px" color="white" />
                </div>
                <div>
                    <div class="brand-name">GW ENT Store</div>
                    <div class="brand-sub">Beats Marketplace</div>
                </div>
            </div>
            <div class="header-actions">
                <!-- <q-btn flat dense no-caps class="header-btn" icon="favorite_border" label="Saved" /> -->
                <!-- <q-btn flat dense no-caps class="header-btn cart-btn" icon="shopping_cart" label="Cart" /> -->

                <!-- <q-btn flat dense no-caps class="header-btn cart-btn" icon="shopping_cart" label="Cart"
                    :to="{ name: 'cart-checkout' }" :size="$q.screen.gt.sm ? 'md' : 'sm'">
                    <q-badge v-if="cart.count > 0" color="red" floating>{{ cart.count }}</q-badge>
                </q-btn> -->
            </div>
        </div>


        <!-- TRACK LIST HEADER -->
        <div class="track-list-header">
            <span class="col-num">#</span>
            <span></span>
            <span class="col-title">Track</span>
            <span></span>
            <span></span>
        </div>

        <!-- TRACKS -->
        <div v-if="filteredMusic.length" class="track-list relative z-10">
            <div v-for="(track, index) in filteredMusic" :key="track.id" class="track-row"
                :class="{ 'track-playing': currentId === track.id }">
                <!-- INDEX / PLAYING INDICATOR -->
                <span class="col-num">
                    <q-icon v-if="currentId === track.id && isPlaying" name="graphic_eq" size="14px" color="purple-4" />
                    <span v-else class="track-num-text">{{ index + 1 }}</span>
                </span>

                <!-- COVER ART with PLAY ICON OVERLAY -->
                <div class="cover-play" @click="selectTrack(track)">
                    <img v-if="getCoverArt(track)" :src="getCoverArt(track)"
                        style="width: 100%; height: 100%; object-fit: cover;" class="cover-img" />
                    <div v-else class="cover-fallback">
                        <q-icon name="music_note" size="18px" color="grey-6" />
                    </div>
                    <div class="cover-play-overlay">
                        <q-icon :name="currentId === track.id && isPlaying ? 'pause' : 'play_arrow'" size="20px"
                            color="white" />
                    </div>
                </div>

                <!-- WAVEFORM HERO: title / release / genre / price overlaid on top of waveform -->
                <div class="waveform-hero" @click="(e) => seekTrack(track, e)">
                    <!-- Base layer: dim/grey waveform -->
                    <img v-if="track.waveform" :src="track.waveform" class="waveform-bg" />

                    <!-- Progress layer: bright/purple waveform clipped to progress percentage -->
                    <div v-if="track.waveform && currentId === track.id" class="waveform-progress-container" :style="{
                        width: Math.max(0, Math.min(100, progress * 100)) + '%'
                    }">
                        <img :src="track.waveform" class="waveform-progress" />
                    </div>

                    <div class="waveform-scrim" />

                    <div class="hero-content">
                        <div class="hero-top">
                            <div class="hero-title ellipsis">{{ track.title }}</div>
                            <div class="hero-release ellipsis">{{ track.release?.title || 'No Release' }}</div>
                        </div>
                        <div class="hero-bottom">
                            <span class="genre-tag">{{ track.genre?.title || '—' }}</span>
                            <span class="price-tag">
                                {{ isFreeTrack(track) ? 'Free' : `R${Number(track.price || 0).toFixed(2)}` }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- DESKTOP ACTIONS -->
                <div class="col-action desktop-actions">
                    <q-btn round flat dense icon="favorite_border" class="action-btn" />
                </div>

                <div class="col-action desktop-actions">
                    <q-btn v-if="isFreeTrack(track)" round flat dense icon="download" class="action-btn"
                        :disable="isDownloading(track.id)" @click="downloadTrack(track)" />
                    <q-btn v-else round flat dense icon="add_shopping_cart" class="action-btn cart-action"
                        @click="addTrackToCart(track)" />
                </div>

                <!-- MOBILE ACTIONS -->
                <div class="mobile-actions lt-md">
                    <q-btn round flat dense icon="more_horiz" class="action-btn" @click="openTrackActions(track)" />

                    <q-btn v-if="isFreeTrack(track)" round flat dense
                        :icon="isDownloading(track.id) ? 'downloading' : 'download'" class="action-btn"
                        :disable="isDownloading(track.id)" @click="downloadTrack(track)" />

                    <q-btn v-else round flat dense icon="add_shopping_cart" class="action-btn cart-action"
                        @click="addTrackToCart(track)" />
                </div>

                <!-- DOWNLOAD PROGRESS (spans full row width) -->
                <div v-if="hasDownloadState(track.id)" class="row-download-progress">
                    <q-linear-progress :value="downloadProgressValue(track.id)"
                        :indeterminate="isDownloadIndeterminate(track.id)" color="positive"
                        track-color="rgba(255, 255, 255, 0.12)" rounded size="4px" />
                    <div class="download-progress-label">{{ downloadStatusLabel(track.id) }}</div>
                </div>
            </div>
        </div>

        <q-dialog v-model="mobileActionsOpen" position="bottom" transition-show="slide-up" transition-hide="slide-down">
            <q-card class="track-actions-sheet">
                <div class="sheet-handle"></div>
                <div class="sheet-title">{{ selectedTrack?.title || 'Track options' }}</div>
                <div class="sheet-meta">
                    <span class="sheet-pill">{{ selectedTrack?.genre?.title || 'Genre' }}</span>
                    <span class="sheet-pill">R{{ selectedTrack && !isNaN(Number(selectedTrack?.price)) ?
                        Number(selectedTrack.price).toFixed(2) : '—' }}</span>
                </div>
                <div class="sheet-actions">
                    <q-btn flat class="sheet-action-btn" icon="favorite_border" label="Save"
                        @click="closeTrackActions" />
                    <q-btn flat class="sheet-action-btn" icon="info" label="Details" @click="goToTrackDetails" />
                    <q-btn v-if="selectedTrack && isFreeTrack(selectedTrack)" flat class="sheet-action-btn"
                        :icon="selectedTrack && isDownloading(selectedTrack.id) ? 'downloading' : 'download'"
                        :label="selectedTrack && isDownloading(selectedTrack.id) ? 'Downloading...' : 'Download'"
                        :disable="selectedTrack && isDownloading(selectedTrack.id)" @click="handleSheetDownload" />
                    <q-btn v-if="selectedTrack && !isFreeTrack(selectedTrack)" flat class="sheet-action-btn"
                        icon="add_shopping_cart" label="Add to cart" @click="handleSheetAddToCart" />
                </div>
                <div v-if="selectedTrack && hasDownloadState(selectedTrack.id)" class="sheet-download-progress">
                    <q-linear-progress :value="downloadProgressValue(selectedTrack.id)"
                        :indeterminate="isDownloadIndeterminate(selectedTrack.id)" color="positive"
                        track-color="rgba(255, 255, 255, 0.12)" rounded size="6px" />
                    <div class="download-progress-label">{{ downloadStatusLabel(selectedTrack.id) }}</div>
                </div>
            </q-card>
        </q-dialog>

    </q-page>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useQuasar } from 'quasar'
import { Capacitor } from '@capacitor/core'
import { Directory, Filesystem } from '@capacitor/filesystem'
import { ApiService } from 'src/services/api'
import {
    sharedPlayAudio,
    sharedSeekTo,
    sharedProgress,
    registerTracks,
    sharedCurrentId,
    sharedIsPlaying,
} from 'src/services/audio-player-state'
import MusicFilters from 'src/components/MusicFilters.vue'
import { useMusicFilters } from 'src/composables/useMusicFilters'
import { useCartStore } from 'src/stores/cart'
import { useNotificationsStore } from 'src/stores/notifications'

const $q = useQuasar()
const router = useRouter()
const api = ApiService
const isPlaying = sharedIsPlaying
const currentId = sharedCurrentId
const music = ref([])
const progress = sharedProgress
const cart = useCartStore()
const notifications = useNotificationsStore()
const mobileActionsOpen = ref(false)
const selectedTrack = ref(null)
const downloadStateByTrack = ref({})

const ANDROID_DOWNLOAD_DIR = 'Download/Gw Music'

const isFileMissingError = (error) => {
    const message = String(error?.message || '').toLowerCase()
    const code = String(error?.code || '').toLowerCase()

    return message.includes('not exist')
        || message.includes('no such file')
        || message.includes('notfound')
        || code.includes('not_found')
        || code.includes('enoent')
}

const isFileAlreadyExistsError = (error) => {
    const message = String(error?.message || '').toLowerCase()
    const code = String(error?.code || '').toLowerCase()

    return message.includes('exists')
        || message.includes('already')
        || code.includes('exists')
        || code.includes('eexist')
}

const isFreeTrack = (track) => Number(track?.price || 0) <= 0

const getCoverArt = (track) => track?.release?.cover_art || track?.cover_art || ''

const getDownloadState = (trackId) => downloadStateByTrack.value[String(trackId)] || null

const hasDownloadState = (trackId) => Boolean(getDownloadState(trackId))

const isDownloading = (trackId) => getDownloadState(trackId)?.status === 'downloading'

const isDownloadIndeterminate = (trackId) => {
    const state = getDownloadState(trackId)
    return Boolean(state?.status === 'downloading' && state?.totalBytes === 0)
}

const downloadProgressValue = (trackId) => {
    const percent = Number(getDownloadState(trackId)?.progress || 0)
    return Math.max(0, Math.min(1, percent / 100))
}
const goToTrackDetails = () => {
    const trackId = selectedTrack.value?.id
    if (!trackId) return

    mobileActionsOpen.value = false
    selectedTrack.value = null
    router.push({ name: 'Track', params: { id: trackId } })
}
const downloadStatusLabel = (trackId) => {
    const state = getDownloadState(trackId)

    if (!state) return ''
    if (state.status === 'downloading') {
        return state.totalBytes > 0
            ? `Downloading ${Math.round(state.progress)}%`
            : `Downloading ${(state.loadedBytes / (1024 * 1024)).toFixed(2)} MB`
    }
    if (state.status === 'done') return 'Download complete'
    if (state.status === 'error') return 'Download failed'

    return ''
}

const setDownloadState = (trackId, next) => {
    const key = String(trackId)
    downloadStateByTrack.value = {
        ...downloadStateByTrack.value,
        [key]: {
            ...(downloadStateByTrack.value[key] || {}),
            ...next
        }
    }
}

const getTrackDownloadUrl = (track) => {
    const src = String(track?.file_src || '')

    if (!src) return ''
    if (src.startsWith('http://') || src.startsWith('https://')) return src

    const base = String(import.meta.env.VITE_API_BASE_URL || '').replace(/\/$/, '')
    const path = src.startsWith('/') ? src : `/${src}`

    return `${base}${path}`
}

const getTrackDownloadName = (track) => {
    if (track?.file_name) return String(track.file_name)

    const extension = String(track?.extension || '').replace(/^\./, '')
    const safeTitle = String(track?.title || 'track')
        .replace(/[^a-zA-Z0-9-_ ]/g, '')
        .trim()
        .replace(/\s+/g, '_')

    return extension ? `${safeTitle || 'track'}.${extension}` : `${safeTitle || 'track'}.mp3`
}

const splitFilename = (filename) => {
    const name = String(filename || 'track.mp3').trim() || 'track.mp3'
    const dotIndex = name.lastIndexOf('.')

    if (dotIndex <= 0 || dotIndex === name.length - 1) {
        return { stem: name, ext: '' }
    }

    return {
        stem: name.slice(0, dotIndex),
        ext: name.slice(dotIndex)
    }
}

const blobToBase64 = (blob) => new Promise((resolve, reject) => {
    const reader = new FileReader()

    reader.onloadend = () => {
        const result = String(reader.result || '')
        const [, base64 = ''] = result.split(',')
        resolve(base64)
    }

    reader.onerror = () => {
        reject(reader.error || new Error('Failed to read blob'))
    }

    reader.readAsDataURL(blob)
})

const isNativeAndroid = () => {
    return Capacitor.isNativePlatform() && Capacitor.getPlatform() === 'android'
}

const getUniqueAndroidFilename = async (filename) => {
    const { stem, ext } = splitFilename(filename)

    for (let i = 0; i < 1000; i += 1) {
        const candidate = i === 0 ? `${stem}${ext}` : `${stem} (${i})${ext}`
        const candidatePath = `${ANDROID_DOWNLOAD_DIR}/${candidate}`

        try {
            await Filesystem.stat({
                directory: Directory.ExternalStorage,
                path: candidatePath
            })
        } catch (error) {
            if (isFileMissingError(error)) {
                return candidate
            }

            throw error
        }
    }

    return `${stem}_${Date.now()}${ext}`
}

const saveBlobToDevice = async (blob, filename) => {
    if (isNativeAndroid()) {
        await Filesystem.requestPermissions()

        await Filesystem.mkdir({
            directory: Directory.ExternalStorage,
            path: ANDROID_DOWNLOAD_DIR,
            recursive: true
        })

        const base64 = await blobToBase64(blob)

        for (let attempt = 0; attempt < 5; attempt += 1) {
            const uniqueName = await getUniqueAndroidFilename(filename)
            const filePath = `${ANDROID_DOWNLOAD_DIR}/${uniqueName}`

            try {
                await Filesystem.writeFile({
                    directory: Directory.ExternalStorage,
                    path: filePath,
                    data: base64,
                    recursive: true
                })

                return filePath
            } catch (error) {
                if (isFileAlreadyExistsError(error) && attempt < 4) {
                    continue
                }

                throw error
            }
        }

        throw new Error('Unable to resolve unique Android filename')
    }

    const objectUrl = URL.createObjectURL(blob)
    const anchor = document.createElement('a')
    anchor.href = objectUrl
    anchor.download = filename
    anchor.rel = 'noopener'
    document.body.appendChild(anchor)
    anchor.click()
    anchor.remove()

    window.setTimeout(() => {
        URL.revokeObjectURL(objectUrl)
    }, 2500)

    return filename
}

const downloadTrack = async (track) => {
    if (!track?.id || !isFreeTrack(track)) return
    if (isDownloading(track.id)) return

    const url = getTrackDownloadUrl(track)
    if (!url) {
        $q.notify({ type: 'negative', message: 'No file available for download' })
        return
    }

    try {
        setDownloadState(track.id, {
            status: 'downloading',
            progress: 0,
            totalBytes: 0,
            loadedBytes: 0
        })

        const response = await fetch(url)

        if (!response.ok) {
            throw new Error(`Download failed with status ${response.status}`)
        }

        const totalBytes = Number(response.headers.get('content-length') || 0)

        if (!response.body) {
            const blob = await response.blob()
            const savedAs = await saveBlobToDevice(blob, getTrackDownloadName(track))
            setDownloadState(track.id, {
                status: 'done',
                progress: 100,
                totalBytes,
                loadedBytes: totalBytes || blob.size
            })
            $q.notify({
                type: 'positive',
                message: isNativeAndroid()
                    ? `${track.title} saved to ${savedAs}`
                    : `${track.title} download complete`
            })

            notifications.addHistoryEvent({
                eventType: 'download.completed',
                entityId: `${track.id}-${Date.now()}`,
                title: 'Audio download completed',
                body: track.title || 'Track download finished',
                raw: {
                    musicId: track.id,
                    filePath: savedAs,
                },
            })
            return
        }

        const reader = response.body.getReader()
        const chunks = []
        let loadedBytes = 0

        while (true) {
            const { done, value } = await reader.read()
            if (done) break

            chunks.push(value)
            loadedBytes += value.length

            const progressPercent = totalBytes > 0
                ? (loadedBytes / totalBytes) * 100
                : 0

            setDownloadState(track.id, {
                status: 'downloading',
                progress: progressPercent,
                totalBytes,
                loadedBytes
            })
        }

        const blob = new Blob(chunks)
        const savedAs = await saveBlobToDevice(blob, getTrackDownloadName(track))

        setDownloadState(track.id, {
            status: 'done',
            progress: 100,
            totalBytes,
            loadedBytes
        })
        $q.notify({
            type: 'positive',
            message: isNativeAndroid()
                ? `${track.title} saved to ${savedAs}`
                : `${track.title} download complete`
        })

        notifications.addHistoryEvent({
            eventType: 'download.completed',
            entityId: `${track.id}-${Date.now()}`,
            title: 'Audio download completed',
            body: track.title || 'Track download finished',
            raw: {
                musicId: track.id,
                filePath: savedAs,
            },
        })
    } catch (error) {
        console.error(error)
        setDownloadState(track.id, { status: 'error' })
        $q.notify({ type: 'negative', message: `Failed to download ${track.title}` })
    }
}

const selectTrack = (track) => {
    sharedPlayAudio.value?.(track)
}
const addTrackToCart = (track) => {
    if (!track) return

    const added = cart.addItem({
        id: track.id,
        name: track.title,
        price: Number(track.price) || 0,
        type: 'music'
    }, 'music')

    if (added) {
        $q.notify({ type: 'positive', message: `${track.title} added to cart` })
    } else {
        $q.notify({ type: 'warning', message: 'Your cart already contains a different item type' })
    }
}

const openTrackActions = (track) => {
    selectedTrack.value = track
    mobileActionsOpen.value = true
}

const closeTrackActions = () => {
    mobileActionsOpen.value = false
    selectedTrack.value = null
}

const handleSheetAddToCart = () => {
    addTrackToCart(selectedTrack.value)
    closeTrackActions()
}

const handleSheetDownload = () => {
    if (!selectedTrack.value) return
    downloadTrack(selectedTrack.value)
}

const filters = ref({
    search: '',
    genre: null,
    release: null,
    price: null,
    letter: null
})


const genres = computed(() => {
    const map = new Map()

    music.value.forEach(track => {
        if (track.genre) {
            map.set(track.genre.id, track.genre)
        }
    })

    return [...map.values()]
})
const { filteredMusic } = useMusicFilters(music, filters)
const releases = computed(() => {
    const map = new Map()

    music.value.forEach(track => {
        if (track.release) {
            map.set(track.release.id, track.release)
        }
    })

    return [...map.values()]
})
const fetchMusic = async () => {
    try {
        const res = await api.get('/music')
        music.value = res.data.data
        registerTracks(music.value)

    } catch (err) {
        console.error(err)
        $q.notify({
            type: 'negative',
            message: 'Failed to load music'
        })
    }
}
const seekTrack = (track, e) => {
    const el = e.currentTarget
    const rect = el.getBoundingClientRect()
    const percent = (e.clientX - rect.left) / rect.width
    if (sharedSeekTo.value && currentId.value === track.id) {
        sharedSeekTo.value(percent)
    }
}

onMounted(async () => {
    await fetchMusic()
})
</script>
<style scoped>
.store-page {
    background: #0a0a0f;
    min-height: 100vh;
    padding-bottom: 6rem;
    font-family: var(--q-font-sans, system-ui, sans-serif);
}

.store-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    padding: 0.875rem 1rem;
    border-bottom: 0.5px solid rgba(255, 255, 255, 0.08);
    background: rgba(10, 10, 15, 0.98);
    position: sticky;
    top: 0;
    z-index: 50;
}

.brand-block {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    min-width: 0;
}

.brand-icon {
    width: 2rem;
    height: 2rem;
    border-radius: 0.5rem;
    background: #3a5bed;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.brand-name {
    font-size: 0.9rem;
    font-weight: 600;
    letter-spacing: 0.02em;
    color: #f3f4f6;
}

.brand-sub {
    font-size: 0.65rem;
    color: #4b5563;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

.header-actions {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-end;
    gap: 0.5rem;
}

.header-btn {
    background: rgba(255, 255, 255, 0.05) !important;
    border: 0.5px solid rgba(255, 255, 255, 0.1) !important;
    color: #9ca3af !important;
    border-radius: 0.5rem !important;
    font-size: 0.75rem !important;
    padding: 0.375rem 0.8rem !important;
}

.header-btn:hover {
    background: rgba(255, 255, 255, 0.09) !important;
    color: #fff !important;
}

.header-btn.cart-btn {
    background: rgba(124, 58, 237, 0.14) !important;
    border-color: rgba(124, 58, 237, 0.3) !important;
    color: #c4b5fd !important;
}

.filter-bar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    border-bottom: 0.5px solid rgba(255, 255, 255, 0.06);
    background: rgba(12, 12, 18, 0.95);
}

.filter-search-wrap {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: rgba(255, 255, 255, 0.04);
    border: 0.5px solid rgba(255, 255, 255, 0.09);
    border-radius: 0.5rem;
    padding: 0.45rem 0.8rem;
    min-width: min(100%, 14rem);
    flex: 1 1 14rem;
    color: #4b5563;
}

.filter-placeholder {
    font-size: 0.75rem;
    color: #4b5563;
}

.filter-chip {
    background: rgba(255, 255, 255, 0.05) !important;
    border: 0.5px solid rgba(255, 255, 255, 0.09) !important;
    color: #6b7280 !important;
    border-radius: 0.5rem !important;
    font-size: 0.7rem !important;
}

.track-list-header,
.track-row {
    display: grid;
    grid-template-columns: 2rem 3.25rem minmax(0, 1fr) 2.25rem 2.25rem;
    align-items: center;
    gap: 0.75rem;
    padding: 0 1rem;
}

.track-list-header {
    padding: 0.5rem 1rem 0.7rem;
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #374151;
    border-bottom: 0.5px solid rgba(255, 255, 255, 0.05);
}

.track-list {
    padding: 0.75rem 0.25rem 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.track-row {
    padding: 0.7rem 1rem;
    background: rgba(255, 255, 255, 0.025);
    border: 0.5px solid rgba(255, 255, 255, 0.06);
    border-radius: 0.7rem;
    transition: all 0.18s;
    cursor: default;
    margin: 0 0.25rem;
    overflow: hidden;
}

.track-row:hover {
    background: rgba(124, 58, 237, 0.07);
    border-color: rgba(124, 58, 237, 0.2);
}

.track-row.track-playing {
    background: rgba(124, 58, 237, 0.1);
    border-color: rgba(124, 58, 237, 0.35);
}

.col-num {
    font-size: 0.7rem;
    color: #4b5563;
    text-align: center;
}

.track-num-text {
    font-variant-numeric: tabular-nums;
}

/* COVER ART + PLAY OVERLAY */
.cover-play {
    position: relative;
    width: 3.25rem;
    height: 3.25rem;
    border-radius: 0.6rem;
    overflow: hidden;
    background: rgba(255, 255, 255, 0.05);
    border: 0.5px solid rgba(255, 255, 255, 0.09);
    cursor: pointer;
    flex-shrink: 0;
}

.cover-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.cover-fallback {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.cover-play-overlay {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.4);
    opacity: 0;
    transition: opacity 0.15s ease;
}

.cover-play:hover .cover-play-overlay,
.track-playing .cover-play-overlay {
    opacity: 1;
}

/* WAVEFORM HERO: background waveform image with title/release/genre/price overlaid */
.waveform-hero {
    position: relative;
    min-width: 0;
    height: 3.5rem;
    border-radius: 0.6rem;
    overflow: hidden;
    background: rgba(255, 255, 255, 0.04);
    border: 0.5px solid rgba(255, 255, 255, 0.07);
    cursor: pointer;
}

.waveform-bg {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    /* opacity: 0.35; */
    filter: grayscale(1) brightness(0.75);
}

.waveform-progress-container {
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    overflow: hidden;
    z-index: 3;
    transition: width 0.05s linear;
}

.waveform-progress {
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 1;
    filter: hue-rotate(250deg) saturate(2) brightness(1.4) contrast(1.3);
}

.waveform-scrim {
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, rgba(10, 10, 15, 0.9) 0%, rgba(10, 10, 15, 0.58) 55%, rgba(10, 10, 15, 0.88) 100%);
    z-index: 2;
    pointer-events: none;
}

.hero-content {
    position: relative;
    z-index: 4;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.6rem;
    padding: 0 0.7rem;
}

.hero-top {
    min-width: 0;
    display: flex;
    flex-direction: column;
}

.hero-title {
    font-size: 0.8rem;
    font-weight: 600;
    color: #f3f4f6;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.track-playing .hero-title {
    color: #c4b5fd;
}

.hero-release {
    font-size: 0.68rem;
    color: rgba(229, 231, 235, 0.6);
    margin-top: 0.1rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.hero-bottom {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    flex-shrink: 0;
}

.row-download-progress {
    grid-column: 1 / -1;
    margin-top: 0.4rem;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.download-progress-label {
    font-size: 0.64rem;
    color: #9ca3af;
}

.genre-tag {
    font-size: 0.65rem;
    color: #d1d5db;
    background: rgba(255, 255, 255, 0.1);
    border: 0.5px solid rgba(255, 255, 255, 0.12);
    border-radius: 0.35rem;
    padding: 0.2rem 0.45rem;
    white-space: nowrap;
}

.price-tag {
    font-size: 0.78rem;
    font-weight: 600;
    color: #34d399;
    white-space: nowrap;
}

.action-btn {
    color: #4b5563 !important;
    background: transparent !important;
    border: 0.5px solid rgba(255, 255, 255, 0.07) !important;
    border-radius: 0.45rem !important;
}

.mobile-actions {
    display: none;
}

@media (max-width: 900px) {
    .track-list-header {
        display: none;
    }

    .track-row {
        grid-template-columns: 1.6rem 2.6rem minmax(0, 1fr) auto;
        gap: 0.55rem;
        padding: 0.65rem 0.7rem;
    }

    .desktop-actions {
        display: none;
    }

    .cover-play {
        width: 2.6rem;
        height: 2.6rem;
    }

    .waveform-hero {
        height: 3.1rem;
    }

    .mobile-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.3rem;
        min-width: 0;
    }
}

@media (max-width: 600px) {
    .store-header {
        flex-wrap: wrap;
        padding: 0.75rem;
    }

    .header-actions {
        width: 100%;
        justify-content: space-between;
    }

    .filter-bar {
        padding: 0.7rem 0.75rem;
    }

    .track-row {
        grid-template-columns: 1.6rem 2.6rem minmax(0, 1fr) auto;
        padding: 0.65rem 0.6rem;
    }

    .hero-release {
        display: none;
    }

    .track-actions-sheet {
        border-radius: 1rem 1rem 0 0;
        padding: 0.5rem 0.9rem 1rem;
        background: #111827;
        color: #f9fafb;
    }

    .sheet-handle {
        width: 3rem;
        height: 0.25rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.22);
        margin: 0.2rem auto 0.7rem;
    }

    .sheet-title {
        font-size: 0.95rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .sheet-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        margin-bottom: 0.75rem;
    }

    .sheet-pill {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 0.25rem 0.6rem;
        background: rgba(255, 255, 255, 0.08);
        color: #d1d5db;
        font-size: 0.75rem;
    }

    .sheet-actions {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }

    .sheet-action-btn {
        justify-content: flex-start;
        border-radius: 0.75rem;
        background: rgba(255, 255, 255, 0.04);
        color: #f9fafb;
    }

    .sheet-download-progress {
        margin-top: 0.6rem;
    }
}
</style>