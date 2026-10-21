@extends('layouts.admin.app')

@section('title', 'Tren PMB Multi-Tahun')

@section('content')
<div class="rounded-lg bg-white p-4 sm:p-6 shadow-theme-sm dark:bg-gray-dark w-full overflow-hidden" x-init="page = 'tren-multi-tahun'">
    
    <div class="mb-8 text-center">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Tren PMB per Gelombang</h1>
        <p class="text-gray-500">Visualisasi Data Line Chart Multi-Tahun</p>
    </div>

    {{-- GRAFIK KATEGORI HISTORI TAHUNAN --}}
    <div class="mb-10 rounded-xl bg-gray-50 p-4 sm:p-6 border border-gray-200 dark:bg-gray-800/50 dark:border-gray-700 overflow-hidden min-w-0">
        <div class="mb-4">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-chart-bar text-brand-500"></i> Rekapitulasi & Komparasi Tren Multi Tahun (Unified Chart)
            </h2>
            <p class="text-sm text-gray-500 mt-1">Perbandingan komprehensif status pendaftaran, ujian, dan registrasi ulang antar gelombang periode 2025 vs 2026.</p>
        </div>
        <div class="w-full min-w-0 p-4 bg-white rounded-xl shadow-sm border border-gray-100 dark:bg-gray-900 dark:border-gray-800 overflow-x-auto">
            <div id="unifiedChartPerbandingan" class="w-full min-h-[450px] min-w-[700px] lg:min-w-0"></div>
        </div>
    </div>

    {{-- CSS Kustom AI --}}
    <style>
        .ai-card {
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.08) 0%, rgba(59, 130, 246, 0.03) 100%);
            border: 1px solid rgba(79, 70, 229, 0.25);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 30px;
        }
        .dark .ai-card {
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.15) 0%, rgba(59, 130, 246, 0.05) 100%);
            border: 1px solid rgba(79, 70, 229, 0.35);
        }
        .ai-btn {
            background-color: #4f46e5 !important;
            color: #ffffff !important;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);
        }
        .ai-btn:hover {
            background-color: #4338ca !important;
            transform: translateY(-1px);
        }
        .ai-btn:disabled {
            background-color: #9ca3af !important;
            cursor: not-allowed;
        }

        /* Styling kustom untuk memperindah keluaran AI Markdown */
        #aiResultContent {
            font-size: 14px;
            line-height: 1.6;
            color: #374151;
        }
        .dark #aiResultContent {
            color: #d1d5db;
        }
        #aiResultContent h1, #aiResultContent h2, #aiResultContent h3, #aiResultContent h4 {
            font-weight: 700;
            color: #111827;
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
        }
        .dark #aiResultContent h1, .dark #aiResultContent h2, .dark #aiResultContent h3, .dark #aiResultContent h4 {
            color: #ffffff;
        }
        #aiResultContent h1 { font-size: 20px; border-bottom: 2px solid #e5e7eb; padding-bottom: 6px; }
        .dark #aiResultContent h1 { border-bottom-color: #374151; }
        #aiResultContent h2 { font-size: 18px; }
        #aiResultContent h3 { font-size: 16px; }
        #aiResultContent p {
            margin-top: 8px;
            margin-bottom: 12px;
        }
        #aiResultContent ul {
            list-style-type: disc !important;
            padding-left: 24px !important;
            margin-top: 8px !important;
            margin-bottom: 12px !important;
        }
        #aiResultContent ol {
            list-style-type: decimal !important;
            padding-left: 24px !important;
            margin-top: 8px !important;
            margin-bottom: 12px !important;
        }
        #aiResultContent li {
            margin-bottom: 6px !important;
            display: list-item !important;
        }
        #aiResultContent strong {
            font-weight: 700;
            color: #111827;
        }
        .dark #aiResultContent strong {
            color: #ffffff;
        }
        #aiResultContent table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
            margin-bottom: 16px;
        }
        #aiResultContent th, #aiResultContent td {
            border: 1px solid #e5e7eb;
            padding: 8px 12px;
            text-align: left;
        }
        .dark #aiResultContent th, .dark #aiResultContent td {
            border-color: #374151;
        }
        #aiResultContent th {
            background-color: #f9fafb;
            font-weight: 600;
        }
        .dark #aiResultContent th {
            background-color: #1f2937;
        }
        #aiResultContent hr {
            border: 0;
            border-top: 1px solid #e5e7eb;
            margin: 20px 0;
        }
        .dark #aiResultContent hr {
            border-top-color: #374151;
        }
    </style>

    {{-- CARD ANALISIS AI OLLAMA --}}
    <div class="ai-card">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-indigo-500 text-white text-xs animate-pulse">✨</span>
                    Analisis Tren PMB berbasis AI ({{ $ollamaDomain }})
                </h2>
                <p class="text-sm text-gray-500 mt-1">Gunakan kecerdasan buatan (Gemma4 di <strong>{{ $ollamaDomain }}</strong>) untuk mengulas performa, perbandingan tren pendaftaran, dan rekomendasi marketing secara otomatis.</p>
            </div>
            <button type="button" id="btnAnalyzeAI" class="ai-btn">
                <i class="fas fa-brain"></i> Mulai Analisis AI
            </button>
        </div>
        
        {{-- Loading State --}}
        <div id="aiLoadingState" class="hidden py-6 space-y-4">
            <div class="flex items-center gap-3">
                <div class="h-4 w-4 animate-spin rounded-full border-2 border-indigo-500 border-t-transparent"></div>
                <span class="text-sm font-medium text-indigo-600 dark:text-indigo-400 animate-pulse">Sedang menganalisis laporan multi-tahun menggunakan Gemma4...</span>
            </div>
            <div class="space-y-2.5 animate-pulse">
                <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-full"></div>
                <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-5/6"></div>
                <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-4/5"></div>
            </div>
        </div>

        {{-- Result Container --}}
        <div id="aiResultContainer" class="hidden p-5 bg-white rounded-xl shadow-inner border border-gray-100 dark:bg-gray-900 dark:border-gray-800">
            <div class="prose prose-sm max-w-none dark:prose-invert text-gray-700 dark:text-gray-300" id="aiResultContent"></div>
            <div class="mt-4 flex justify-end gap-3 border-t border-gray-100 pt-3 dark:border-gray-800">
                <button type="button" onclick="copyAIResult()" class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-500 hover:text-indigo-600 transition-colors">
                    <i class="far fa-copy"></i> Salin Laporan
                </button>
            </div>
        </div>

        {{-- Collapsible Prompt Container --}}
        <div class="mt-4 border-t border-gray-100 pt-3 dark:border-gray-800">
            <button type="button" onclick="togglePromptInspector()" class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-500 hover:text-indigo-600 transition-colors">
                <i class="fas fa-code"></i> <span id="promptToggleText">Tampilkan Data & Prompt yang Dikirim</span>
            </button>
            <div id="promptInspector" class="hidden mt-3 p-4 bg-gray-50 rounded-lg border border-gray-200 dark:bg-gray-900 dark:border-gray-800">
                <h4 class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Prompt & Data Payload (JSON)</h4>
                <pre class="text-xs text-gray-600 dark:text-gray-400 overflow-x-auto whitespace-pre-wrap font-mono max-h-[250px] p-3 bg-white border border-gray-100 rounded-md dark:bg-gray-950 dark:border-gray-900" id="promptPayloadContent"></pre>
            </div>
        </div>
    </div>

    {{-- KONTANER 3 GRAFIK BERDAMPINGAN --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- GRAFIK 1: PENDAFTAR --}}
        <div class="border border-gray-200 rounded-xl p-4 shadow-sm bg-gray-50/50 dark:bg-gray-800 dark:border-gray-700 overflow-hidden min-w-0">
            <h3 class="text-center font-bold text-gray-800 dark:text-white mb-2">1. Pendaftar</h3>
            <div id="chartPendaftar" class="min-h-[350px]"></div>
        </div>

        {{-- GRAFIK 2: PESERTA UJIAN --}}
        <div class="border border-gray-200 rounded-xl p-4 shadow-sm bg-gray-50/50 dark:bg-gray-800 dark:border-gray-700 overflow-hidden min-w-0">
            <h3 class="text-center font-bold text-gray-800 dark:text-white mb-2">2. Peserta Ujian</h3>
            <div id="chartUjian" class="min-h-[350px]"></div>
        </div>

        {{-- GRAFIK 3: REGISTRASI --}}
        <div class="border border-gray-200 rounded-xl p-4 shadow-sm bg-gray-50/50 dark:bg-gray-800 dark:border-gray-700 overflow-hidden min-w-0">
            <h3 class="text-center font-bold text-gray-800 dark:text-white mb-2">3. Registrasi</h3>
            <div id="chartRegistrasi" class="min-h-[350px]"></div>
        </div>

    </div>

    <div class="mt-6 text-center text-xs text-gray-400">
        Catatan: Data ditarik secara langsung dari arsip database pmb2021 hingga sistem saat ini.
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Render Unified Chart
        const unifiedSeriesData = @json($unifiedChartData);
        const gelombangCategories = ['Gelombang 1', 'Gelombang 2', 'Gelombang 3', 'Gelombang 4', 'Gelombang 5', 'Gelombang 6', 'Gelombang 7', 'Gelombang 8', 'Gelombang 9', 'Gelombang 10'];

        const optionsUnified = {
            series: unifiedSeriesData,
            chart: {
                type: 'bar',
                height: 480,
                fontFamily: 'Inter, sans-serif',
                toolbar: { show: true }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '80%', 
                    borderRadius: 2,
                    dataLabels: { position: 'top' }
                }
            },
            dataLabels: {
                enabled: true,
                offsetY: -20,
                style: {
                    fontSize: '9px',
                    colors: ["#333"]
                }
            },
            colors: [
                '#3B82F6', // 2025 - DAFTAR (Biru)
                '#EF4444', // 2025 - UJIAN (Merah)
                '#D97706', // 2025 - REGISTRASI (Emas)
                '#10B981', // 2026 - DAFTAR (Hijau)
                '#8B5CF6', // 2026 - UJIAN (Ungu)
                '#06B6D4'  // 2026 - REGISTRASI (Cyan)
            ],
            stroke: {
                show: true,
                width: 1,
                colors: ['transparent']
            },
            xaxis: {
                categories: gelombangCategories,
                labels: { style: { fontSize: '11px', fontWeight: 600 } }
            },
            yaxis: {
                title: { text: 'Jumlah Mahasiswa (Orang)', style: { fontWeight: 600 } }
            },
            fill: { opacity: 1 },
            legend: { 
                position: 'bottom', 
                horizontalAlign: 'center',
                itemMargin: { horizontal: 10, vertical: 5 }
            }
        };

        if (document.querySelector("#unifiedChartPerbandingan")) {
            new ApexCharts(document.querySelector("#unifiedChartPerbandingan"), optionsUnified).render();
        }

        // Render Line Charts
        const rawData = @json($chartData);
        const categories = ['Gel 1', 'Gel 2', 'Gel 3', 'Gel 4', 'Gel 5', 'Gel 6', 'Gel 7', 'Gel 8', 'Gel 9', 'Gel 10'];
        
        // Palet warna yang mirip dengan gambar (Abu, Oranye, Hijau, Biru Muda, Biru Tua)
        const chartColors = ['#9CA3AF', '#F59E0B', '#10B981', '#60A5FA', '#1E3A8A'];

        const commonOptions = {
            chart: {
                type: 'line',
                height: 350,
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif'
            },
            stroke: {
                width: 3,
                curve: 'straight' // Menggunakan garis lurus seperti di gambar, bukan 'smooth'
            },
            markers: {
                size: 5,
                hover: { sizeOffset: 2 }
            },
            xaxis: {
                categories: categories,
                labels: { style: { colors: '#6B7280', fontSize: '11px' } }
            },
            yaxis: {
                title: { text: 'Jumlah', style: { fontSize: '11px', fontWeight: 500 } },
                labels: { style: { colors: '#6B7280' } }
            },
            dataLabels: {
                enabled: true,
                enabledOnSeries: [3, 4], // Hanya tampilkan angka pada seri terbaru (opsional)
                background: { enabled: true, foreColor: '#fff', borderRadius: 2, padding: 4 },
                offsetY: -10
            },
            colors: chartColors,
            legend: {
                show: true,
                position: 'top',
                horizontalAlign: 'center',
                markers: { radius: 12 }
            },
            grid: {
                borderColor: '#E5E7EB',
                strokeDashArray: 4,
                xaxis: { lines: { show: true } }
            }
        };

        // Render Chart 1: Pendaftar
        let optionsPendaftar = { ...commonOptions, series: rawData.pendaftar };
        new ApexCharts(document.querySelector("#chartPendaftar"), optionsPendaftar).render();

        // Render Chart 2: Ujian
        let optionsUjian = { ...commonOptions, series: rawData.ujian };
        new ApexCharts(document.querySelector("#chartUjian"), optionsUjian).render();

        // Render Chart 3: Registrasi
        let optionsRegistrasi = { ...commonOptions, series: rawData.registrasi };
        new ApexCharts(document.querySelector("#chartRegistrasi"), optionsRegistrasi).render();

        // --- AI Analysis Ollama (Direct Client Fetch to bypass MAMP timeouts) ---
        function buildMarkdownTable(seriesArray) {
            let md = "| Tahun | G1 | G2 | G3 | G4 | G5 | G6 | G7 | G8 | G9 | G10 |\n";
            md += "|-------|-----|-----|-----|-----|-----|-----|-----|-----|-----|-----|\n";
            seriesArray.forEach(series => {
                const year = series.name;
                const formattedRow = series.data.map((val, idx) => {
                    if (year === '2026' && idx >= 7) {
                        return '–';
                    }
                    return val;
                });
                md += `| ${year} | ${formattedRow.join(' | ')} |\n`;
            });
            return md;
        }

        function constructPrompt() {
            const tablePendaftar = buildMarkdownTable(rawData.pendaftar);
            const tableUjian = buildMarkdownTable(rawData.ujian);
            const tableRegistrasi = buildMarkdownTable(rawData.registrasi);

            return `# PERAN
Anda adalah Analis Data Senior untuk tim PMB (Penerimaan Mahasiswa Baru) Universitas Buana Perjuangan (UBP) Karawang. Output Anda akan dibaca langsung oleh Rektorat dan Kepala Biro PMB sebagai bahan pengambilan keputusan.

# KONTEKS PENTING (WAJIB DIPATUHI)
1. Periode data: 2021, 2022, 2024, 2025, dan 2026 (tahun berjalan).
   **Data 2023 tidak tersedia** — jangan mengarang angka 2023, dan sebutkan ketiadaannya sebagai keterbatasan analisis.
2. **Tahun 2026 BELUM SELESAI.** Gelombang 8, 9, dan 10 bernilai 0 karena BELUM DIBUKA, bukan karena gagal menjaring pendaftar. Gelombang 7 masih berjalan sehingga angkanya belum final.
3. Karena itu, **DILARANG membandingkan total tahunan 2026 dengan total penuh tahun lain secara langsung.** Semua perbandingan year-over-year harus apple-to-apple: bandingkan **Gelombang 1–7 saja** (2026 vs Gel 1–7 tahun sebelumnya), lalu nyatakan basis perbandingannya secara eksplisit.
4. Konteks bisnis: Gelombang 6 secara historis adalah puncak pendaftaran karena bertepatan dengan periode pasca-pengumuman SNBP/SNBT, saat calon mahasiswa yang tidak lolos PTN beralih ke PTS.

# DATA

## Tabel 1 — Pendaftar
${tablePendaftar}

## Tabel 2 — Peserta Ujian
${tableUjian}

## Tabel 3 — Registrasi (Mahasiswa Terdaftar)
${tableRegistrasi}

# METRIK YANG HARUS DIHITUNG
Hitung dan tampilkan metrik berikut (bulatkan persentase ke 1 desimal):
- **Show-up Rate** = Peserta Ujian ÷ Pendaftar × 100%
- **Yield / Konversi Akhir** = Registrasi ÷ Pendaftar × 100%
- **Konversi Ujian→Registrasi** = Registrasi ÷ Peserta Ujian × 100%
- **Kontribusi gelombang** = pendaftar per gelombang ÷ total tahun × 100%
- **Pertumbuhan YoY per gelombang** (2026 vs 2025, dan 2026 vs rata-rata 2021/2022/2024/2025)

# ATURAN ANALISIS
- **Akurasi angka adalah prioritas utama.** Verifikasi ulang setiap penjumlahan dan persentase sebelum ditampilkan. Jangan menyebut angka yang tidak dapat diturunkan langsung dari tabel di atas.
- Jika Anda menemukan **anomali atau kejanggalan pola pada data** (misalnya rasio yang terlalu seragam antar tahun, lonjakan ekstrem, atau nilai yang tidak konsisten), **laporkan secara eksplisit** sebagai catatan kualitas data — jangan diabaikan atau dimuluskan.
- Bedakan dengan tegas antara **fakta dari data** dan **hipotesis/dugaan Anda**. Beri label jelas pada setiap dugaan, dan sebutkan data tambahan apa yang diperlukan untuk membuktikannya.
- Boleh membuat proyeksi akhir tahun 2026, tetapi **wajib menyebutkan asumsi dan metode** yang digunakan, serta sajikan dalam bentuk rentang (skenario konservatif–optimis), bukan angka tunggal.

# STRUKTUR OUTPUT
Susun jawaban dengan urutan berikut:

1. **Ringkasan Eksekutif** — maksimal 5 poin, berisi temuan paling penting dan angka kunci. Ditulis agar bisa dipahami dalam 60 detik.
2. **Tabel Kinerja Agregat** — total pendaftar, peserta ujian, registrasi, dan ketiga metrik konversi per tahun. Sertakan baris khusus basis Gel 1–7.
3. **Analisis Tren Pendaftaran** — pola musiman antar gelombang, pergerakan antar tahun, dan pergeseran struktural perilaku pendaftar.
4. **Komparasi Tahun 2026** — perbandingan per gelombang (apple-to-apple), identifikasi gelombang unggul dan gelombang bermasalah, serta analisis kualitas pendaftar (bukan hanya kuantitas).
5. **Proyeksi Akhir Tahun 2026** — rentang skenario beserta asumsinya.
6. **Rekomendasi Strategis** — 4–6 rekomendasi, diurutkan berdasarkan prioritas. Setiap rekomendasi wajib memuat: **(a)** aksi konkret, **(b)** dasar data yang mendukungnya, **(c)** penanggung jawab yang disarankan, **(d)** target terukur (KPI).
7. **Keterbatasan Analisis & Data yang Dibutuhkan** — apa yang belum bisa dijawab dari data ini, dan data tambahan apa yang perlu dikumpulkan.

# GAYA & FORMAT
- Bahasa Indonesia profesional, ringkas, dan berorientasi solusi. Optimis namun jujur — **jangan menyembunyikan atau memperhalus temuan negatif.**
- Gunakan Markdown rapi: subjudul, tabel, poin bernomor, dan **cetak tebal** untuk angka serta istilah kunci.
- Hindari jargon statistik tanpa penjelasan. Setiap angka yang disajikan harus disertai kalimat "artinya apa bagi UBP".
- Panjang wajar: cukup detail untuk mendukung keputusan, tanpa pengulangan.`;
        }

        const btnAnalyzeAI = document.getElementById('btnAnalyzeAI');
        const aiLoadingState = document.getElementById('aiLoadingState');
        const aiResultContainer = document.getElementById('aiResultContainer');
        const aiResultContent = document.getElementById('aiResultContent');

        if (btnAnalyzeAI) {
            btnAnalyzeAI.addEventListener('click', function () {
                aiLoadingState.classList.remove('hidden');
                aiResultContainer.classList.add('hidden');
                btnAnalyzeAI.disabled = true;
                btnAnalyzeAI.innerHTML = '<i class="fas fa-spinner animate-spin mr-1"></i> Memproses...';

                const prompt = constructPrompt();
                const ollamaUrl = "{{ env('OLLAMA_URL', 'https://ollama.adirp.id') }}".replace(/\/$/, '') + '/api/generate';

                fetch(ollamaUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        model: 'gemma4:26b',
                        prompt: prompt,
                        stream: true, // Aktifkan streaming untuk realtime rendering
                        options: {
                            num_ctx: 8192,      // Perbesar context window agar muat prompt tabel yang besar
                            num_predict: -1     // Hilangkan batas token output (generate sampai selesai)
                        }
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error('Http Error Status ' + response.status + ': ' + text.substring(0, 300));
                        });
                    }

                    // Tampilkan container hasil analisis segera setelah koneksi sukses terbuka
                    aiResultContainer.classList.remove('hidden');
                    aiResultContent.innerHTML = `
                        <div class="flex flex-col gap-2 p-3 bg-gray-50 rounded border border-gray-200 dark:bg-gray-900 dark:border-gray-800">
                            <span class="text-xs text-indigo-500 font-medium italic animate-pulse">Menghubungkan ke AI dan mulai memproses analisis...</span>
                            <span class="text-[10px] text-gray-400 font-mono" id="aiDebugLog">Status: Koneksi terbuka, inisialisasi stream...</span>
                        </div>
                    `;

                    const debugLog = (msg) => {
                        console.log("[AI Debug]", msg);
                        const dbgEl = document.getElementById('aiDebugLog');
                        if (dbgEl) {
                            dbgEl.innerText = "Status: " + msg;
                        }
                    };

                    const reader = response.body.getReader();
                    const decoder = new TextDecoder('utf-8');
                    let rawBuffer = '';
                    let fullText = '';
                    let firstChunkReceived = false;
                    let chunkCount = 0;

                    function readChunk() {
                        debugLog("Menunggu potongan data stream berikutnya...");
                        return reader.read().then(({ done, value }) => {
                            if (done) {
                                debugLog("Stream selesai. Total potongan data: " + chunkCount);
                                aiLoadingState.classList.add('hidden');
                                btnAnalyzeAI.disabled = false;
                                btnAnalyzeAI.innerHTML = '<i class="fas fa-brain mr-1"></i> Analisis Ulang';
                                
                                // Jika selesai tapi tidak ada data yang masuk sama sekali
                                if (fullText.trim() === '') {
                                    aiResultContent.innerHTML = '<p class="text-yellow-600 font-semibold">Stream selesai tetapi server AI tidak mengembalikan teks jawaban.</p>';
                                }
                                return;
                            }

                            chunkCount++;
                            debugLog("Menerima potongan data #" + chunkCount + " (" + value.length + " bytes)");

                            // Dekode potongan byte stream menjadi teks
                            const chunkStr = decoder.decode(value, { stream: true });
                            rawBuffer += chunkStr;

                            // Pisahkan data buffer berdasarkan baris baru karena Ollama mengembalikan JSON Lines
                            const lines = rawBuffer.split('\n');
                            // Simpan baris terakhir yang belum lengkap kembali ke buffer
                            rawBuffer = lines.pop();

                            for (const line of lines) {
                                if (line.trim() === '') continue;
                                try {
                                    const parsed = JSON.parse(line);
                                    if (parsed.response) {
                                        if (!firstChunkReceived) {
                                            aiResultContent.innerHTML = '';
                                            firstChunkReceived = true;
                                        }
                                        fullText += parsed.response;
                                        
                                        // Update output Markdown secara real-time
                                        if (typeof marked !== 'undefined') {
                                            aiResultContent.innerHTML = marked.parse(fullText);
                                        } else {
                                            aiResultContent.innerHTML = '<pre class="whitespace-pre-wrap font-sans">' + fullText + '</pre>';
                                        }
                                    }
                                    
                                    // Jika server mengirimkan informasi error tengah jalan
                                    if (parsed.error) {
                                        throw new Error(parsed.error);
                                    }
                                } catch (e) {
                                    console.warn("Gagal parse baris stream:", line, e);
                                    debugLog("Peringatan parse: " + e.message);
                                }
                            }

                            // Baca potongan stream berikutnya secara rekursif
                            return readChunk();
                        }).catch(err => {
                            debugLog("Error saat membaca chunk: " + err.message);
                            throw err; // Lempar ke handler catch luar
                        });
                    }

                    return readChunk();
                })
                .catch(error => {
                    aiLoadingState.classList.add('hidden');
                    btnAnalyzeAI.disabled = false;
                    btnAnalyzeAI.innerHTML = '<i class="fas fa-brain mr-1"></i> Analisis Ulang';
                    
                    aiResultContent.innerHTML = `
                        <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm dark:bg-red-950/20 dark:border-red-900/50 dark:text-red-400">
                            <h4 class="font-bold mb-1">Gagal Memproses Laporan</h4>
                            <p class="text-xs mb-2">Terjadi kesalahan koneksi atau runtime javascript.</p>
                            <pre class="text-[10px] font-mono p-2 bg-red-100 dark:bg-red-900/20 rounded">${error.stack || error.message}</pre>
                        </div>
                    `;
                });
            });
        }

        // Inisialisasi tampilan prompt payload pada editor debug
        const promptPayloadContent = document.getElementById('promptPayloadContent');
        if (promptPayloadContent) {
            promptPayloadContent.innerText = constructPrompt();
        }
    });

    // Fungsi buka/tutup inspector prompt
    function togglePromptInspector() {
        const inspector = document.getElementById('promptInspector');
        const toggleText = document.getElementById('promptToggleText');
        if (inspector.classList.contains('hidden')) {
            inspector.classList.remove('hidden');
            toggleText.innerText = 'Sembunyikan Data & Prompt yang Dikirim';
        } else {
            inspector.classList.add('hidden');
            toggleText.innerText = 'Tampilkan Data & Prompt yang Dikirim';
        }
    }

    // Fungsi salin hasil analisis
    function copyAIResult() {
        const text = document.getElementById('aiResultContent').innerText;
        navigator.clipboard.writeText(text).then(() => {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Laporan analisis berhasil disalin!',
                timer: 2000,
                showConfirmButton: false
            });
        }).catch(err => {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Gagal menyalin teks: ' + err
            });
        });
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
@endsection