/**
 * Perekam voice note dengan suara tersamar.
 * Prinsip: suara asli TIDAK pernah meninggalkan perangkat bila mode samaran dipilih, * penyamaran (Tone.js, render offline) dan encoding WAV mono 16 kHz dilakukan di browser sebelum upload.
 */
const MAX_SECONDS = 120;
const SAMPLE_RATE = 16000;

function encodeWav(float32, sampleRate) {
    const buffer = new ArrayBuffer(44 + float32.length * 2);
    const view = new DataView(buffer);
    const writeStr = (o, s) => { for (let i = 0; i < s.length; i++) view.setUint8(o + i, s.charCodeAt(i)); };
    writeStr(0, 'RIFF');
    view.setUint32(4, 36 + float32.length * 2, true);
    writeStr(8, 'WAVE');
    writeStr(12, 'fmt ');
    view.setUint32(16, 16, true);
    view.setUint16(20, 1, true);
    view.setUint16(22, 1, true);
    view.setUint32(24, sampleRate, true);
    view.setUint32(28, sampleRate * 2, true);
    view.setUint16(32, 2, true);
    view.setUint16(34, 16, true);
    writeStr(36, 'data');
    view.setUint32(40, float32.length * 2, true);
    let o = 44;
    for (let i = 0; i < float32.length; i++, o += 2) {
        const s = Math.max(-1, Math.min(1, float32[i]));
        view.setInt16(o, s < 0 ? s * 0x8000 : s * 0x7fff, true);
    }
    return new Blob([view], { type: 'audio/wav' });
}

async function disguise(audioBuffer, mode) {
    const Tone = await import('tone');
    const duration = Math.min(audioBuffer.duration, MAX_SECONDS);
    const rendered = await Tone.Offline(() => {
        const player = new Tone.Player(new Tone.ToneAudioBuffer(audioBuffer));
        let chain = [];
        if (mode === 'deep') chain = [new Tone.PitchShift({ pitch: -5, windowSize: 0.08 })];
        if (mode === 'high') chain = [new Tone.PitchShift({ pitch: 5, windowSize: 0.06 })];
        if (mode === 'robot') chain = [new Tone.PitchShift({ pitch: -3, windowSize: 0.05 }), new Tone.FrequencyShifter({ frequency: -45 }), new Tone.Chebyshev({ order: 3, wet: 0.25 })];
        const gain = new Tone.Gain(0.95);
        if (chain.length) player.chain(...chain, gain, Tone.getDestination());
        else player.chain(gain, Tone.getDestination());
        player.start(0);
    }, duration, 1, SAMPLE_RATE);
    return rendered.getChannelData(0);
}

export default (uploadUrl) => ({
    open: false,
    state: 'idle', // idle | recording | processing | preview | uploading
    seconds: 0,
    mode: 'deep',
    error: '',
    previewUrl: null,
    wav: null,
    duration: 0,
    raw: null,
    _rec: null,
    _timer: null,
    modes: [
        { id: 'deep', label: 'Suara Rendah' },
        { id: 'high', label: 'Suara Tinggi' },
        { id: 'robot', label: 'Robot' },
        { id: 'none', label: 'Tanpa Samaran' },
    ],
    supported() {
        return !!(navigator.mediaDevices?.getUserMedia && window.MediaRecorder);
    },
    async start() {
        this.error = '';
        this.open = true;
        if (!this.supported()) { this.error = 'Browser ini belum mendukung rekam suara. Coba pakai Chrome atau buka lewat HTTPS.'; return; }
        let stream;
        try {
            stream = await navigator.mediaDevices.getUserMedia({ audio: { echoCancellation: true, noiseSuppression: true } });
        } catch (e) {
            this.error = 'Izin mikrofon ditolak. Nggak apa-apa, kamu tetap bisa cerita lewat tulisan. Kalau mau merekam, izinkan mikrofon di pengaturan browser.';
            return;
        }
        const chunks = [];
        this._rec = new MediaRecorder(stream);
        this._rec.ondataavailable = (e) => e.data.size && chunks.push(e.data);
        this._rec.onstop = async () => {
            stream.getTracks().forEach((t) => t.stop());
            clearInterval(this._timer);
            this.raw = new Blob(chunks, { type: this._rec.mimeType });
            await this.process();
        };
        this.seconds = 0;
        this.state = 'recording';
        this._rec.start();
        this._timer = setInterval(() => {
            this.seconds++;
            if (this.seconds >= MAX_SECONDS) this.stop();
        }, 1000);
    },
    stop() {
        if (this._rec && this._rec.state === 'recording') this._rec.stop();
    },
    async process() {
        if (!this.raw) return;
        this.state = 'processing';
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const decoded = await ctx.decodeAudioData(await this.raw.arrayBuffer());
            ctx.close();
            const samples = await disguise(decoded, this.mode);
            this.duration = Math.max(1, Math.round(samples.length / SAMPLE_RATE));
            this.wav = encodeWav(samples, SAMPLE_RATE);
            if (this.previewUrl) URL.revokeObjectURL(this.previewUrl);
            this.previewUrl = URL.createObjectURL(this.wav);
            this.state = 'preview';
        } catch (e) {
            console.error(e);
            this.error = 'Suaranya belum bisa diproses. Coba rekam ulang, ya.';
            this.state = 'idle';
        }
    },
    async changeMode(mode) {
        this.mode = mode;
        if (this.state === 'preview') await this.process();
    },
    async upload() {
        if (!this.wav) return;
        this.state = 'uploading';
        this.error = '';
        const fd = new FormData();
        fd.append('audio', this.wav, 'pesan-suara.wav');
        fd.append('duration', String(this.duration));
        fd.append('disguise_mode', this.mode);
        try {
            const { data } = await window.axios.post(uploadUrl, fd, { params: { after: this.lastId ?? 0 } });
            window.dispatchEvent(new CustomEvent('chat-append', { detail: { ...data, mine: true } }));
            this.reset();
        } catch (e) {
            this.state = 'preview';
            this.error = e.response?.data?.message || (e.response?.status === 429 ? 'Batas 10 pesan suara per 10 menit. Coba lagi sebentar lagi.' : 'Pesan suara belum terkirim. Coba lagi, ya.');
        }
    },
    reset() {
        this.stop();
        clearInterval(this._timer);
        if (this.previewUrl) URL.revokeObjectURL(this.previewUrl);
        Object.assign(this, { open: false, state: 'idle', seconds: 0, previewUrl: null, wav: null, raw: null, error: '' });
    },
    rerecord() {
        if (this.previewUrl) URL.revokeObjectURL(this.previewUrl);
        Object.assign(this, { previewUrl: null, wav: null, raw: null });
        this.start();
    },
    timeLabel(s) {
        return String(Math.floor(s / 60)).padStart(2, '0') + ':' + String(s % 60).padStart(2, '0');
    },
});
