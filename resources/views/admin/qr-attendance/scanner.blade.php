<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Scanner QR Absensi</title>
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: #0f172a;
            color: #f1f5f9;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Top Bar ── */
        .topbar {
            background: #1e293b;
            border-bottom: 1px solid #334155;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
        }
        .topbar-left { display: flex; align-items: center; gap: 10px; }
        .topbar-dot {
            width: 10px; height: 10px; border-radius: 50%;
            background: #10b981;
            animation: blink 1.4s infinite;
        }
        .topbar-dot.offline { background: #ef4444; animation: none; }
        @keyframes blink { 0%,100%{opacity:1} 50%{opacity:.3} }
        .topbar-title { font-size: 15px; font-weight: 600; color: #f1f5f9; }
        .topbar-sesi  { font-size: 12px; color: #94a3b8; }

        .counter-pill {
            background: #1d4ed8;
            color: #bfdbfe;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 14px;
            border-radius: 20px;
        }

        /* ── Main Area ── */
        .main { flex: 1; display: flex; flex-direction: column; align-items: center; padding: 20px 16px; gap: 20px; }

        /* ── Camera Box ── */
        .camera-wrap {
            position: relative;
            width: 100%;
            max-width: 440px;
            aspect-ratio: 1;
            border-radius: 20px;
            overflow: hidden;
            background: #020617;
            border: 2px solid #334155;
        }
        #video {
            width: 100%; height: 100%;
            object-fit: cover;
            display: block;
        }

        /* Scanning frame overlay */
        .scan-frame {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
        }
        .frame-box {
            width: 62%;
            aspect-ratio: 1;
            position: relative;
        }
        .frame-box::before, .frame-box::after,
        .frame-box .c::before, .frame-box .c::after {
            content: '';
            position: absolute;
            width: 24px; height: 24px;
            border-color: #6366f1;
            border-style: solid;
        }
        .frame-box::before  { top:0; left:0;  border-width: 3px 0 0 3px; border-radius: 4px 0 0 0; }
        .frame-box::after   { top:0; right:0; border-width: 3px 3px 0 0; border-radius: 0 4px 0 0; }
        .frame-box .c::before { bottom:0; left:0;  border-width: 0 0 3px 3px; border-radius: 0 0 0 4px; }
        .frame-box .c::after  { bottom:0; right:0; border-width: 0 3px 3px 0; border-radius: 0 0 4px 0; }

        /* Laser scan line */
        .laser {
            position: absolute;
            left: 6%; right: 6%;
            height: 2px;
            background: linear-gradient(90deg, transparent, #6366f1, #818cf8, #6366f1, transparent);
            top: 20%;
            animation: sweep 2s ease-in-out infinite;
        }
        @keyframes sweep {
            0%   { top: 18%; opacity: 1; }
            48%  { top: 78%; opacity: 1; }
            50%  { opacity: 0; }
            52%  { top: 18%; opacity: 0; }
            54%  { opacity: 1; }
            100% { top: 18%; }
        }

        /* Camera placeholder */
        .cam-placeholder {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            background: #020617;
            color: #475569;
        }
        .cam-icon { width: 48px; height: 48px; opacity: .5; }
        .cam-placeholder p { font-size: 13px; text-align: center; line-height: 1.5; max-width: 220px; }

        /* Result Flash Overlay */
        .result-flash {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            opacity: 0;
            transition: opacity .2s;
            pointer-events: none;
            border-radius: 18px;
        }
        .result-flash.show { opacity: 1; }
        .result-flash.success { background: rgba(16,185,129,.88); }
        .result-flash.warning { background: rgba(245,158,11,.88); }
        .result-flash.error   { background: rgba(239,68,68,.88);  }
        .flash-icon { font-size: 52px; line-height: 1; }
        .flash-text { font-size: 15px; font-weight: 700; color: #fff; text-align: center; padding: 0 20px; }

        /* ── Controls ── */
        .controls { display: flex; gap: 10px; width: 100%; max-width: 440px; }
        .btn {
            flex: 1;
            padding: 12px;
            border-radius: 12px;
            border: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity .2s, transform .1s;
        }
        .btn:active { transform: scale(.97); }
        .btn-start  { background: #4f46e5; color: #fff; }
        .btn-stop   { background: #334155; color: #94a3b8; }
        .btn-flip   { background: #1e293b; color: #94a3b8; border: 1px solid #334155; flex: 0 0 48px; }

        /* ── Result Card ── */
        .result-card {
            width: 100%;
            max-width: 440px;
            background: #1e293b;
            border-radius: 16px;
            border: 1px solid #334155;
            overflow: hidden;
        }
        .result-header {
            padding: 14px 18px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid #334155;
        }
        .result-header.success { background: #064e3b; }
        .result-header.warning { background: #451a03; }
        .result-header.error   { background: #450a0a; }
        .result-header.idle    { background: #1e293b; }
        .result-status-dot { width: 9px; height: 9px; border-radius: 50%; }
        .result-header.success .result-status-dot { background: #10b981; }
        .result-header.warning .result-status-dot { background: #f59e0b; }
        .result-header.error   .result-status-dot { background: #ef4444; }
        .result-header.idle    .result-status-dot { background: #475569; }
        .result-header-text { flex: 1; }
        .result-label { font-size: 11px; color: #94a3b8; text-transform: uppercase; letter-spacing: .06em; }
        .result-title { font-size: 14px; font-weight: 700; margin-top: 1px; }
        .result-header.success .result-title { color: #6ee7b7; }
        .result-header.warning .result-title { color: #fcd34d; }
        .result-header.error   .result-title { color: #fca5a5; }
        .result-header.idle    .result-title { color: #475569; }
        .result-time { font-size: 11px; color: #64748b; }

        .result-body { padding: 16px 18px; display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .result-field {}
        .field-lbl { font-size: 11px; color: #64748b; margin-bottom: 3px; text-transform: uppercase; letter-spacing:.04em; }
        .field-val { font-size: 13px; font-weight: 600; color: #e2e8f0; word-break: break-word; }
        .field-full { grid-column: 1 / -1; }

        .badge-lulus {
            display: inline-block;
            font-size: 11px; font-weight: 700;
            padding: 2px 10px; border-radius: 20px;
        }
        .badge-lulus.lulus { background: #064e3b; color: #6ee7b7; }
        .badge-lulus.belum { background: #450a0a; color: #fca5a5; }

        /* ── Log ── */
        .log-wrap { width: 100%; max-width: 440px; }
        .log-title { font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing:.06em; margin-bottom: 8px; }
        .log-list { display: flex; flex-direction: column; gap: 6px; max-height: 180px; overflow-y: auto; }
        .log-item {
            display: flex; align-items: center; gap: 10px;
            background: #1e293b;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 12px;
            border-left: 3px solid transparent;
        }
        .log-item.success { border-color: #10b981; }
        .log-item.warning { border-color: #f59e0b; }
        .log-item.error   { border-color: #ef4444; }
        .log-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
        .log-item.success .log-dot { background: #10b981; }
        .log-item.warning .log-dot { background: #f59e0b; }
        .log-item.error   .log-dot { background: #ef4444; }
        .log-name { flex: 1; color: #cbd5e1; font-weight: 600; }
        .log-time { color: #64748b; flex-shrink: 0; }

        /* ── Sesi input bar ── */
        .sesi-bar {
            width: 100%; max-width: 440px;
            background: #1e293b;
            border-radius: 12px;
            border: 1px solid #334155;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sesi-bar label { font-size: 12px; color: #64748b; white-space: nowrap; }
        .sesi-bar input {
            flex: 1;
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 8px;
            color: #e2e8f0;
            font-size: 13px;
            padding: 7px 12px;
            outline: none;
        }
        .sesi-bar input:focus { border-color: #6366f1; }
    </style>
</head>
<body>

<h2 class="sr-only" style="position:absolute;width:1px;height:1px;overflow:hidden;">Scanner QR Code Absensi Mahasiswa</h2>

<div class="topbar">
    <div class="topbar-left">
        <div class="topbar-dot" id="cam-dot"></div>
        <div>
            <div class="topbar-title">Scanner QR Absensi</div>
            <div class="topbar-sesi" id="topbar-sesi-label">Sesi: —</div>
        </div>
    </div>
    <div class="counter-pill" id="counter-pill">0 hadir</div>
</div>

<div class="main">

    {{-- Sesi selector --}}
    <div class="sesi-bar">
        <label for="sesi-input">Sesi</label>
        <input type="text" id="sesi-input" placeholder="Nama sesi (misal: Orientasi 2025)" value="{{ request('sesi', 'Daftar Hadir') }}">
    </div>

    {{-- Camera --}}
    <div class="camera-wrap" id="camera-wrap">
        <video id="video" autoplay muted playsinline></video>
        <canvas id="canvas" style="display:none;"></canvas>

        <div class="cam-placeholder" id="cam-placeholder">
            <svg class="cam-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
            </svg>
            <p>Tekan Mulai Scan untuk mengaktifkan kamera</p>
        </div>

        <div class="scan-frame" id="scan-frame" style="display:none;">
            <div class="frame-box">
                <div class="c"></div>
                <div class="laser"></div>
            </div>
        </div>

        <div class="result-flash" id="result-flash">
            <div class="flash-icon" id="flash-icon"></div>
            <div class="flash-text" id="flash-text"></div>
        </div>
    </div>

    {{-- Controls --}}
    <div class="controls">
        <button class="btn btn-start" id="btn-start" onclick="startScan()">Mulai Scan</button>
        <button class="btn btn-stop"  id="btn-stop"  onclick="stopScan()" style="display:none;">Hentikan</button>
        <button class="btn btn-flip"  id="btn-flip"  onclick="flipCamera()" title="Ganti kamera" style="display:none;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 4v6h6M23 20v-6h-6"/>
                <path d="M20.49 9A9 9 0 005.64 5.64L1 10M23 14l-4.64 4.36A9 9 0 013.51 15"/>
            </svg>
        </button>
    </div>

    {{-- Last result --}}
    <div class="result-card">
        <div class="result-header idle" id="result-header">
            <div class="result-status-dot"></div>
            <div class="result-header-text">
                <div class="result-label">Hasil Scan Terakhir</div>
                <div class="result-title" id="result-title">Menunggu scan...</div>
            </div>
            <div class="result-time" id="result-time"></div>
        </div>
        <div class="result-body" id="result-body" style="display:none;"></div>
    </div>

    {{-- Log --}}
    <div class="log-wrap">
        <div class="log-title">Riwayat scan sesi ini</div>
        <div class="log-list" id="log-list">
            <div style="padding:20px; text-align:center; color:#475569; font-size:12px;">Belum ada scan</div>
        </div>
    </div>

</div>

{{-- jsQR for QR decoding --}}
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>

<script>
const SCAN_ENDPOINT = "{{ route('qr.scan', ['token' => '__TOKEN__']) }}";
const CSRF          = "{{ csrf_token() }}";

let stream       = null;
let scanning     = false;
let lastToken    = null;
let cooldown     = false;
let facingMode   = 'environment';
let scanCount    = 0;
let animFrame    = null;

const video        = document.getElementById('video');
const canvas       = document.getElementById('canvas');
const ctx          = canvas.getContext('2d', { willReadFrequently: true });
const placeholder  = document.getElementById('cam-placeholder');
const scanFrame    = document.getElementById('scan-frame');
const resultFlash  = document.getElementById('result-flash');
const flashIcon    = document.getElementById('flash-icon');
const flashText    = document.getElementById('flash-text');
const resultHeader = document.getElementById('result-header');
const resultTitle  = document.getElementById('result-title');
const resultBody   = document.getElementById('result-body');
const resultTime   = document.getElementById('result-time');
const logList      = document.getElementById('log-list');
const counterPill  = document.getElementById('counter-pill');
const camDot       = document.getElementById('cam-dot');
const sesiInput    = document.getElementById('sesi-input');

sesiInput.addEventListener('input', () => {
    document.getElementById('topbar-sesi-label').textContent = 'Sesi: ' + (sesiInput.value || '—');
});
document.getElementById('topbar-sesi-label').textContent = 'Sesi: ' + (sesiInput.value || '—');

async function startScan() {
    try {
        stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode, width: { ideal: 1280 }, height: { ideal: 1280 } }
        });
        video.srcObject = stream;
        await video.play();

        placeholder.style.display = 'none';
        scanFrame.style.display   = 'flex';
        document.getElementById('btn-start').style.display = 'none';
        document.getElementById('btn-stop').style.display  = 'flex';
        document.getElementById('btn-flip').style.display  = 'flex';
        camDot.classList.remove('offline');
        scanning = true;

        tick();
    } catch (e) {
        alert('Tidak dapat mengakses kamera. Pastikan izin kamera sudah diberikan.');
        camDot.classList.add('offline');
    }
}

function stopScan() {
    scanning = false;
    if (animFrame) cancelAnimationFrame(animFrame);
    if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; }

    placeholder.style.display = 'flex';
    scanFrame.style.display   = 'none';
    document.getElementById('btn-start').style.display = 'flex';
    document.getElementById('btn-stop').style.display  = 'none';
    document.getElementById('btn-flip').style.display  = 'none';
    camDot.classList.add('offline');
}

async function flipCamera() {
    facingMode = (facingMode === 'environment') ? 'user' : 'environment';
    stopScan();
    await startScan();
}

function tick() {
    if (!scanning) return;
    if (video.readyState === video.HAVE_ENOUGH_DATA) {
        canvas.width  = video.videoWidth;
        canvas.height = video.videoHeight;
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const code      = jsQR(imageData.data, imageData.width, imageData.height, { inversionAttempts: 'dontInvert' });

        if (code && !cooldown) {
            const url = code.data;
            const tokenMatch = url.match(/\/qr\/scan\/([A-Za-z0-9]+)/);
            if (tokenMatch) {
                const token = tokenMatch[1];
                if (token !== lastToken) {
                    lastToken = token;
                    processScan(token);
                }
            }
        }
    }
    animFrame = requestAnimationFrame(tick);
}

async function processScan(token) {
    cooldown = true;
    const sesi = sesiInput.value.trim() || 'Daftar Hadir';
    const url  = SCAN_ENDPOINT.replace('__TOKEN__', token) + '?sesi=' + encodeURIComponent(sesi);

    try {
        const res  = await fetch(url, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        handleResult(data);
    } catch (e) {
        handleResult({ status: 'error', message: 'Gagal terhubung ke server.' });
    }

    setTimeout(() => { cooldown = false; lastToken = null; }, 3500);
}

function handleResult(data) {
    const now    = new Date().toLocaleTimeString('id-ID');
    const status = data.status;

    const cfg = {
        success:        { cls:'success', icon:'✓', title:'Kehadiran tercatat!',   sound: 440 },
        already_scanned:{ cls:'warning', icon:'!', title:'QR sudah digunakan',    sound: 220 },
        expired:        { cls:'error',   icon:'✗', title:'QR kadaluarsa',         sound: 180 },
        error:          { cls:'error',   icon:'✗', title:'QR tidak valid',        sound: 180 },
        invalid:        { cls:'error',   icon:'✗', title:'QR tidak dikenal',      sound: 180 },
    }[status] || { cls:'error', icon:'✗', title:'Error', sound: 180 };

    playBeep(cfg.sound, cfg.cls);
    showFlash(cfg.cls, cfg.icon, data.message || cfg.title);
    updateResultCard(cfg.cls, cfg.title, data, now);

    const nama = data.data?.nama_mahasiswa || data.data?.user_name || '—';
    addLog(cfg.cls, nama, now);

    if (status === 'success') {
        scanCount++;
        counterPill.textContent = scanCount + ' hadir';
    }
}

function showFlash(cls, icon, text) {
    resultFlash.className = 'result-flash show ' + cls;
    flashIcon.textContent = icon;
    flashText.textContent = text;
    setTimeout(() => { resultFlash.className = 'result-flash'; }, 2000);
}

function updateResultCard(cls, title, data, now) {
    resultHeader.className = 'result-header ' + cls;
    resultTitle.textContent = title;
    resultTime.textContent  = now;

    const d = data.data;
    if (!d) {
        resultBody.style.display = 'none';
        return;
    }

    const lulus = d.status_kelulusan?.toLowerCase() === 'lulus';
    resultBody.style.display = 'grid';
    resultBody.innerHTML = `
        <div class="result-field field-full">
            <div class="field-lbl">Nama mahasiswa</div>
            <div class="field-val">${esc(d.nama_mahasiswa || d.user_name || '—')}</div>
        </div>
        <div class="result-field">
            <div class="field-lbl">Email</div>
            <div class="field-val">${esc(d.email || '—')}</div>
        </div>
        <div class="result-field">
            <div class="field-lbl">Handphone</div>
            <div class="field-val">${esc(d.handphone || d.phone || '—')}</div>
        </div>
        <div class="result-field">
            <div class="field-lbl">Prodi pilihan 1</div>
            <div class="field-val">${esc(d.nama_prodi_1 || '—')} ${d.jenjang_prodi_1 ? '('+esc(d.jenjang_prodi_1)+')' : ''}</div>
        </div>
        <div class="result-field">
            <div class="field-lbl">Prodi pilihan 2</div>
            <div class="field-val">${esc(d.nama_prodi_2 || '—')}</div>
        </div>
        <div class="result-field">
            <div class="field-lbl">Gelombang</div>
            <div class="field-val">${esc(d.gelombang || '—')}</div>
        </div>
        <div class="result-field">
            <div class="field-lbl">Jalur daftar</div>
            <div class="field-val">${esc(d.jalur_pendaftaran || '—')}</div>
        </div>
        <div class="result-field">
            <div class="field-lbl">Prodi diterima</div>
            <div class="field-val">${esc(d.nama_prodi_lulus || '—')}</div>
        </div>
        <div class="result-field">
            <div class="field-lbl">Status kelulusan</div>
            <div class="field-val">
                ${d.status_kelulusan
                    ? `<span class="badge-lulus ${lulus ? 'lulus' : 'belum'}">${esc(d.status_kelulusan)}</span>`
                    : '—'}
            </div>
        </div>
        <div class="result-field">
            <div class="field-lbl">Nilai quiz</div>
            <div class="field-val">${esc(d.nilai_quiz || '—')}</div>
        </div>
        <div class="result-field">
            <div class="field-lbl">NIK</div>
            <div class="field-val">${esc(d.nik || '—')}</div>
        </div>
        <div class="result-field">
            <div class="field-lbl">Tempat, tgl lahir</div>
            <div class="field-val">${esc(d.tempat_lahir || '—')}, ${d.tanggal_lahir ? new Date(d.tanggal_lahir).toLocaleDateString('id-ID') : '—'}</div>
        </div>
    `;
}

function addLog(cls, nama, time) {
    const existing = logList.querySelector('[data-empty]');
    if (existing) existing.remove();

    const item = document.createElement('div');
    item.className = 'log-item ' + cls;
    item.innerHTML = `<div class="log-dot"></div><div class="log-name">${esc(nama)}</div><div class="log-time">${time}</div>`;
    logList.prepend(item);

    if (logList.children.length > 20) logList.lastElementChild.remove();
}

function playBeep(freq, type) {
    try {
        const ctx2  = new (window.AudioContext || window.webkitAudioContext)();
        const osc   = ctx2.createOscillator();
        const gain  = ctx2.createGain();
        osc.connect(gain);
        gain.connect(ctx2.destination);
        osc.frequency.value = freq;
        osc.type = 'sine';
        gain.gain.setValueAtTime(.4, ctx2.currentTime);
        gain.gain.exponentialRampToValueAtTime(.0001, ctx2.currentTime + (type === 'success' ? .35 : .2));
        osc.start();
        osc.stop(ctx2.currentTime + .4);
    } catch(e) {}
}

function esc(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

document.addEventListener('visibilitychange', () => {
    if (document.hidden && scanning) stopScan();
});
</script>

</body>
</html>