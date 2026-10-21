@extends('layouts.admin.app')

@section('title', 'Profile Lengkap Mahasiswa Baru')

@section('content')
<div class="cyberpunk-container">
<div class="cyberpunk-stat-text-box" style="margin-bottom: 20px; padding: 15px; background: rgba(0,240,255,0.05); border-left: 4px solid #00f0ff; color: #ff003c; font-family: monospace;">
    <i class="fas fa-info-circle" style="color: #ff003c;"></i> 
    {{ $pageData['stat_text'] }}
</div>
    <div class="cyberpunk-header">
        <div class="cyberpunk-header-content">
            <h1 class="cyberpunk-title">Profile Lengkap Mahasiswa Baru</h1>
            <p class="cyberpunk-subtitle">Daftar lengkap peserta mahasiswa baru dan status verifikasi</p>
        </div>

        <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900"
     x-data="{ showAdvanced: false }">
    <form action="{{ route('admin.profile-lengkap.index') }}" method="GET" class="space-y-4">
        
        {{-- Row 1: Search Bar & Primary Actions --}}
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            {{-- Search Bar --}}
            <div class="relative flex-1">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari nama, email, jurusan, atau no HP..."
                       class="w-full rounded-xl border border-gray-300 bg-gray-50/50 py-2.5 pl-10 pr-4 text-sm text-gray-900 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            </div>

            {{-- Action Buttons --}}
            <div class="flex flex-wrap items-center gap-2">
                <button type="submit"
                        class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2.5 text-xs font-semibold text-white shadow-sm transition-all hover:opacity-90 active:scale-95 cursor-pointer"
                        style="background-color: #2563eb !important; color: #ffffff !important;">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Cari / Filter
                </button>

                {{-- Toggle Advance Filter Button --}}
                <button type="button" @click="showAdvanced = !showAdvanced"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-gray-300 bg-white px-3.5 py-2.5 text-xs font-semibold text-gray-700 shadow-sm transition-all hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 cursor-pointer">
                    <svg class="h-3.5 w-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                    <span>Filter Lanjutan</span>
                    @php
                        $activeCount = count(array_filter(request()->only(['status_slik', 'status_isian', 'kelulusan', 'jenjang', 'verifikasi', 'jalur_pendaftaran', 'kelola_khusus'])));
                    @endphp
                    @if($activeCount > 0)
                        <span class="ml-0.5 rounded-full bg-blue-600 px-1.5 py-0.5 text-[10px] font-bold text-white">{{ $activeCount }}</span>
                    @endif
                    <svg class="h-3.5 w-3.5 transition-transform duration-200" :class="showAdvanced ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>

                @if(request()->hasAny(['search', 'status_slik', 'status_isian', 'kelulusan', 'jenjang', 'verifikasi', 'jalur_pendaftaran', 'kelola_khusus']))
                    <a href="{{ route('admin.profile-lengkap.index') }}"
                       class="inline-flex items-center gap-1.5 rounded-xl border border-gray-300 bg-white px-3.5 py-2.5 text-xs font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                        Reset
                    </a>
                @endif

                <a href="{{ route('admin.profile-lengkap.export-slik', request()->all()) }}"
                   class="inline-flex items-center gap-1.5 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-white shadow-sm transition-all hover:opacity-90 active:scale-95"
                   style="background-color: #059669 !important; color: #ffffff !important;">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Ekspor Pembayaran
                </a>

                <a href="{{ route('admin.download.index') }}"
                   class="inline-flex items-center gap-1.5 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-white shadow-sm transition-all hover:opacity-90 active:scale-95"
                   style="background-color: #059669 !important; color: #ffffff !important;">
                    Export
                </a>

                @if(!in_array(auth()->user()->role, ['admin_akademik', 'admin_farmasi']))
                    <button type="button" onclick="triggerMoodleSync()"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-gray-300 bg-white px-3.5 py-2.5 text-xs font-semibold text-gray-700 shadow-sm transition-all hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 cursor-pointer">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Sync Nilai Moodle
                    </button>
                @endif

                @if(!in_array(auth()->user()->role, ['admin_akademik', 'admin_farmasi']))
                    <div class="inline-flex items-center gap-2" id="bulkActions" style="display: none;">
                        <div class="inline-flex items-center gap-1">
                            <input type="checkbox" id="selectAllCheckbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                            <label for="selectAllCheckbox" class="text-xs font-medium text-gray-700 dark:text-gray-300 cursor-pointer">Pilih Semua</label>
                        </div>
                        <button type="button" class="inline-flex items-center gap-1 rounded-xl px-3 py-2 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 shadow-sm transition-all" id="bulkDeleteBtn">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M19 7L18.1327 19.1425C18.0579 20.1891 17.187 21 16.1378 21H7.86224C6.81296 21 5.94208 20.1891 5.86732 19.1425L5 7M10 11V17M14 11V17M15 7V4C15 3.44772 14.5523 3 14 3H10C9.44772 3 9 3.44772 9 4V7M4 7H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            Hapus Terpilih
                        </button>
                    </div>
                @endif
            </div>
        </div>

        {{-- Row 2: Collapsible Advance Filter Grid --}}
        <div x-show="showAdvanced" x-transition style="display: none;" class="pt-4 border-t border-gray-100 dark:border-gray-800">
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-2.5">
                {{-- 1. Status SLIK --}}
                <div>
                    <label class="block mb-1 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Status SLIK</label>
                    <select name="status_slik" class="w-full rounded-xl border border-gray-300 bg-gray-50/50 px-2.5 py-2 text-xs font-medium text-gray-700 focus:border-blue-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        <option value="">-- Semua SLIK --</option>
                        <option value="paid" {{ request('status_slik') == 'paid' ? 'selected' : '' }}>Paid / Lunas</option>
                        <option value="cancel" {{ request('status_slik') == 'cancel' ? 'selected' : '' }}>Cancel</option>
                        <option value="expired" {{ request('status_slik') == 'expired' ? 'selected' : '' }}>Expired</option>
                    </select>
                </div>

                {{-- 2. Status Isian --}}
                <div>
                    <label class="block mb-1 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Status Isian</label>
                    <select name="status_isian" class="w-full rounded-xl border border-gray-300 bg-gray-50/50 px-2.5 py-2 text-xs font-medium text-gray-700 focus:border-blue-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        <option value="">-- Semua Status --</option>
                        <option value="empty" {{ request('status_isian') == 'empty' ? 'selected' : '' }}>Belum Isi Apapun</option>
                        <option value="only_biodata" {{ request('status_isian') == 'only_biodata' ? 'selected' : '' }}>Hanya Biodata</option>
                        <option value="already_ereg" {{ request('status_isian') == 'already_ereg' ? 'selected' : '' }}>Sudah E-Reg</option>
                        <option value="already_paid" {{ request('status_isian') == 'already_paid' ? 'selected' : '' }}>Sudah Bayar</option>
                        <option value="already_final" {{ request('status_isian') == 'already_final' ? 'selected' : '' }}>Sudah Final PDDIKTI</option>
                    </select>
                </div>

                {{-- 3. Status Kelulusan --}}
                <div>
                    <label class="block mb-1 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Kelulusan</label>
                    <select name="kelulusan" class="w-full rounded-xl border border-gray-300 bg-gray-50/50 px-2.5 py-2 text-xs font-medium text-gray-700 focus:border-blue-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        <option value="">-- Status Kelulusan --</option>
                        <option value="1" {{ request('kelulusan') == '1' ? 'selected' : '' }}>Sudah Lulus</option>
                        <option value="0" {{ request('kelulusan') == '0' ? 'selected' : '' }}>Belum Lulus</option>
                    </select>
                </div>

                {{-- 4. Jenjang --}}
                <div>
                    <label class="block mb-1 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Jenjang</label>
                    <select name="jenjang" class="w-full rounded-xl border border-gray-300 bg-gray-50/50 px-2.5 py-2 text-xs font-medium text-gray-700 focus:border-blue-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        <option value="">-- Semua Jenjang --</option>
                        <option value="S1" {{ request('jenjang') == 'S1' ? 'selected' : '' }}>Sarjana (S1)</option>
                        <option value="S2" {{ request('jenjang') == 'S2' ? 'selected' : '' }}>Pascasarjana (S2)</option>
                    </select>
                </div>

                {{-- 5. Status Verifikasi --}}
                <div>
                    <label class="block mb-1 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Verifikasi</label>
                    <select name="verifikasi" class="w-full rounded-xl border border-gray-300 bg-gray-50/50 px-2.5 py-2 text-xs font-medium text-gray-700 focus:border-blue-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        <option value="">-- Status Verifikasi --</option>
                        <option value="verified" {{ request('verifikasi') == 'verified' ? 'selected' : '' }}>Terverifikasi</option>
                        <option value="unverified" {{ request('verifikasi') == 'unverified' ? 'selected' : '' }}>Belum Diverifikasi</option>
                    </select>
                </div>

                {{-- 6. Jalur Pendaftaran --}}
                <div>
                    <label class="block mb-1 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Jalur</label>
                    <select name="jalur_pendaftaran" class="w-full rounded-xl border border-gray-300 bg-gray-50/50 px-2.5 py-2 text-xs font-medium text-gray-700 focus:border-blue-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        <option value="">-- Semua Jalur --</option>
                        @foreach($jalurPendaftaranList as $jalur)
                            <option value="{{ $jalur }}" {{ request('jalur_pendaftaran') == $jalur ? 'selected' : '' }}>
                                {{ $jalur }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- 7. Kelola Khusus --}}
                <div>
                    <label class="block mb-1 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Kelola Khusus</label>
                    <select name="kelola_khusus" class="w-full rounded-xl border border-gray-300 bg-gray-50/50 px-2.5 py-2 text-xs font-medium text-gray-700 focus:border-blue-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        <option value="">-- Kelola Khusus --</option>
                        <option value="ekstensi" {{ request('kelola_khusus') == 'ekstensi' ? 'selected' : '' }}>Kelola Ekstensi</option>
                        <option value="pindahan" {{ request('kelola_khusus') == 'pindahan' ? 'selected' : '' }}>Kelola Pindahan</option>
                        <option value="rpl" {{ request('kelola_khusus') == 'rpl' ? 'selected' : '' }}>Kelola RPL</option>
                    </select>
                </div>

                {{-- 8. Per Page --}}
                <div>
                    <label class="block mb-1 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Tampilkan</label>
                    <select name="per_page" class="w-full rounded-xl border border-gray-300 bg-gray-50/50 px-2.5 py-2 text-xs font-medium text-gray-700 focus:border-blue-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" onchange="this.form.submit()">
                        <option value="20" {{ request('per_page') == '20' ? 'selected' : '' }}>20 Baris</option>
                        <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50 Baris</option>
                        <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100 Baris</option>
                        <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>Tampilkan Semua</option>
                    </select>
                </div>
            </div>
        </div>
    </form>
</div>

<div id="moodleSyncOverlay" class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-black/80 backdrop-blur-md">
    <div class="bg-gray-900 border-2 border-cyan-500 p-8 rounded-xl shadow-[0_0_20px_rgba(6,182,212,0.5)] max-w-md w-full mx-4 text-center">
        <h3 id="syncStatusTitle" class="text-2xl font-black text-cyan-400 uppercase tracking-widest mb-4">Inisialisasi...</h3>
        <div id="syncStatusDetails" class="text-gray-300 font-mono text-sm">
            </div>
    </div>
</div>
  <script>
    let globalSyncRunning = false;

    function triggerMoodleSync() {
        if (globalSyncRunning) return;
        
        if(!confirm('Sinkronkan ulang seluruh nilai dari Moodle? Halaman akan otomatis terbuka setelah selesai.')) {
            return;
        }

        globalSyncRunning = true;
        showMoodleSyncUI('Koneksi Established', 'Menghubungkan ke API Moodle...');
        runBatchProcess(0);
    }

    function runBatchProcess(offset) {
        const BATCH_LIMIT = 20; 

        fetch('{{ route("admin.moodle.quiz-complete.force-sync") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                offset: offset,
                limit: BATCH_LIMIT
            })
        })
        .then(response => {
            if (!response.ok) throw new Error("Server Error: " + response.status);
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const total = parseInt(data.total_users) || 0;
                const processed = parseInt(data.processed_in_batch) || 0;
                const currentTotal = offset + processed;
                let percentage = data.progress !== undefined ? parseInt(data.progress) : 0;

                if (data.completed) {
                    updateMoodleSyncUI('✅ Sinkronisasi Selesai', `
                        <div class="mt-4">
                            <div class="w-full bg-gray-800 rounded-full h-4 border border-green-500/50 p-0.5 mb-4">
                                <div class="bg-green-500 h-full rounded-full shadow-[0_0_10px_#22c55e]" style="width: 100%"></div>
                            </div>
                            <p class="text-green-400 font-bold text-lg mb-1">DATA TERKIRIM 100%</p>
                            <p class="text-xs text-gray-500">Membuka tab laporan...</p>
                        </div>
                    `);
                    
                    setTimeout(() => {
                        window.open("https://pmb.ubpkarawang.ac.id/admin/moodle/quiz-complete", "_blank");
                        hideMoodleSyncUI();
                        globalSyncRunning = false;
                    }, 1500);
                } else {
                    updateMoodleSyncUI(`Processing (${percentage}%)`, `
                        <div class="mt-4">
                            <div class="w-full bg-gray-800 rounded-full h-4 border border-cyan-500/50 p-0.5">
                                <div class="bg-cyan-500 h-full rounded-full transition-all duration-500 shadow-[0_0_10px_#06b6d4]" style="width: ${percentage}%"></div>
                            </div>
                            <div class="flex justify-between font-mono text-[10px] mt-4 text-cyan-500/70">
                                <span>RECORDS: ${currentTotal} / ${total}</span>
                                <span>STATUS: OK</span>
                            </div>
                        </div>
                    `);
                    runBatchProcess(offset + processed); 
                }
            } else {
                throw new Error(data.message || 'API Failure');
            }
        })
        .catch(err => {
            console.error('Critical Sync Error:', err);
            updateMoodleSyncUI('❌ System Error', `
                <div class="p-4 bg-red-950/50 border border-red-500 rounded text-red-400 text-xs font-mono">
                    <p class="mb-2">ERROR_LOG: ${err.message}</p>
                    <div class="flex gap-2 justify-center mt-4">
                        <button onclick="runBatchProcess(${offset})" class="px-4 py-2 bg-red-600 text-white rounded uppercase text-[10px] font-bold">Retry</button>
                        <button onclick="hideMoodleSyncUI()" class="px-4 py-2 bg-gray-700 text-white rounded uppercase text-[10px] font-bold">Abort</button>
                    </div>
                </div>
            `);
            globalSyncRunning = false;
        });
    }

    function showMoodleSyncUI(title, html) {
        document.getElementById('moodleSyncOverlay').classList.remove('hidden');
        updateMoodleSyncUI(title, html);
    }

    function updateMoodleSyncUI(title, html) {
        document.getElementById('syncStatusTitle').innerText = title;
        document.getElementById('syncStatusDetails').innerHTML = html; 
    }

    function hideMoodleSyncUI() {
        document.getElementById('moodleSyncOverlay').classList.add('hidden');
    }
</script>
@if(!in_array(auth()->user()->role, ['admin_akademik']))
    <div style="display: grid !important; grid-template-columns: repeat(7, minmax(0, 1fr)) !important; gap: 0.75rem !important; margin-bottom: 1.5rem !important;" class="w-full">
        {{-- Card 1: Total --}}
        <div class="rounded-2xl border border-blue-100 bg-white p-4 shadow-sm transition hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 truncate">Total Mahasiswa</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($totalUsers) }}</p>
                </div>
            </div>
        </div>

        {{-- Card 2: Belum Bayar Pendaftaran --}}
        <div class="rounded-2xl border border-pink-100 bg-white p-4 shadow-sm transition hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-pink-50 text-pink-600 dark:bg-pink-900/30 dark:text-pink-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 truncate">Belum Bayar</p>
                    <p class="text-lg font-bold text-pink-600 dark:text-pink-400">{{ number_format($belumbayarregist) }}</p>
                </div>
            </div>
        </div>

        {{-- Card 3: Belum Isi Apapun --}}
        <div class="rounded-2xl border border-rose-100 bg-white p-4 shadow-sm transition hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 truncate">Belum Isi Apapun</p>
                    <p class="text-lg font-bold text-rose-600 dark:text-rose-400">{{ number_format($unfilledCount) }}</p>
                </div>
            </div>
        </div>

        {{-- Card 4: Hanya Isi Biodata --}}
        <div class="rounded-2xl border border-amber-100 bg-white p-4 shadow-sm transition hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 truncate">Hanya Biodata</p>
                    <p class="text-lg font-bold text-amber-600 dark:text-amber-400">{{ number_format($onlyBiodataCount) }}</p>
                </div>
            </div>
        </div>

        {{-- Card 5: Sudah Lulus --}}
        <div class="rounded-2xl border border-purple-100 bg-white p-4 shadow-sm transition hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-purple-50 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 truncate">Sudah Lulus</p>
                    <p class="text-lg font-bold text-purple-600 dark:text-purple-400">{{ number_format($lulusCount) }}</p>
                </div>
            </div>
        </div>

        {{-- Card 6: Belum Verifikasi --}}
        <div class="rounded-2xl border border-orange-100 bg-white p-4 shadow-sm transition hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-orange-50 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 truncate">Belum Verifikasi</p>
                    <p class="text-lg font-bold text-orange-600 dark:text-orange-400">{{ number_format($unverifiedCount) }}</p>
                </div>
            </div>
        </div>

        {{-- Card 7: Terverifikasi --}}
        <div class="rounded-2xl border border-emerald-100 bg-white p-4 shadow-sm transition hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 truncate">Terverifikasi</p>
                    <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($verifiedCount) }}</p>
                </div>
            </div>
        </div>
    </div>
@endif


    <div class="cyberpunk-users-list">
    @forelse($users as $user)
    @php
                        $details = $user->progress_details;
                        // Hitung total %
                        $totalSteps = count($details);
                        $completedSteps = collect($details)->filter(fn($step) => $step['status'])->count();
                        $percent = ($completedSteps / $totalSteps) * 100;
                        
                        // Cari current step label
                        $currentLabel = 'Selesai';
                        foreach($details as $label => $data) {
                            if(!$data['status']) {
                                $currentLabel = $label;
                                break;
                            }
                        }
                    @endphp

<div class="cyberpunk-user-card" id="user-card-{{ $user->id }}">
    <div class="cyberpunk-user-header">
        <div class="cyberpunk-user-checkbox">
            <input type="checkbox"
                class="user-checkbox cyberpunk-checkbox"
                value="{{ $user->id }}"
                data-name="{{ $user->biodata->nama_lengkap ?? $user->name }}"
                data-va="{{ $user->va_number ?? 'N/A' }}"
                id="user_checkbox_{{ $user->id }}">
            <label for="user_checkbox_{{ $user->id }}" class="cyberpunk-checkbox-label"></label>
        </div>

        <div class="cyberpunk-user-main">
            <div class="cyberpunk-user-avatar">
                {{ substr($user->biodata->nama_lengkap ?? $user->name, 0, 1) }}
            </div>
            
            {{-- Container Flex Column agar Text dan Progress Bar tersusun vertikal --}}
            <div class="cyberpunk-user-info" style="display: flex; flex-direction: column; gap: 0.5rem; flex: 1; min-width: 0;">
                
                {{-- 1. Nama & ID --}}
                <div class="cyberpunk-user-top">
                    <h3 class="cyberpunk-user-name">
                        {{ $user->biodata->nama_lengkap ?? $user->name }}
                    </h3>
                    <span class="cyberpunk-user-id">ID: {{ $user->id }}</span>
                </div>
                
                {{-- 2. Meta Info (Email, Role, Phone) --}}
                <div class="cyberpunk-user-meta">
                    <span class="cyberpunk-meta-item">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 8L10.8906 13.2604C11.5624 13.7083 12.4376 13.7083 13.1094 13.2604L21 8M5 19H19C20.1046 19 21 18.1046 21 17V7C21 5.89543 20.1046 5 19 5H5C3.89543 5 3 5.89543 3 7V17C3 18.1046 3.89543 19 5 19Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /></svg>
                        {{ $user->email }}
                    </span>
                    <span class="cyberpunk-meta-item">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M22 16.92V19.92C22 20.47 21.55 20.92 21 20.92H19C18.45 20.92 18 20.47 18 19.92V16.92C18 16.37 18.45 15.92 19 15.92H21C21.55 15.92 22 16.37 22 16.92ZM16 3.92V6.92C16 7.47 15.55 7.92 15 7.92H13C12.45 7.92 12 7.47 12 6.92V3.92C12 3.37 12.45 2.92 13 2.92H15C15.55 2.92 16 3.37 16 3.92Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /><path d="M22 4.92V10.92C22 11.47 21.55 11.92 21 11.92H13C12.45 11.92 12 11.47 12 10.92V4.92C12 4.37 12.45 3.92 13 3.92H21C21.55 3.92 22 4.37 22 4.92Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /><path d="M10 16.92V20.92C10 21.47 9.55 21.92 9 21.92H3C2.45 21.92 2 21.47 2 20.92V16.92C2 16.37 2.45 15.92 3 15.92H9C9.55 15.92 10 16.37 10 16.92Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /><path d="M10 12.92V14.92C10 15.47 9.55 15.92 9 15.92H3C2.45 15.92 2 15.47 2 14.92V12.92C2 12.37 2.45 11.92 3 11.92H9C9.55 11.92 10 12.37 10 12.92Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /></svg>
                        {{ $user->role }}
                    </span>
                    @if($user->phone)
                    <span class="cyberpunk-meta-item">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M22 16.92V19.92C22 20.47 21.55 20.92 21 20.92H19C18.45 20.92 18 20.47 18 19.92V16.92C18 16.37 18.45 15.92 19 15.92H21C21.55 15.92 22 16.37 22 16.92Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /><path d="M16 3.92V6.92C16 7.47 15.55 7.92 15 7.92H13C12.45 7.92 12 7.47 12 6.92V3.92C12 3.37 12.45 2.92 13 2.92H15C15.55 2.92 16 3.37 16 3.92Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /><path d="M22 4.92V10.92C22 11.47 21.55 11.92 21 11.92H13C12.45 11.92 12 11.47 12 10.92V4.92C12 4.37 12.45 3.92 13 3.92H21C21.55 3.92 22 4.37 22 4.92Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /><path d="M10 16.92V20.92C10 21.47 9.55 21.92 9 21.92H3C2.45 21.92 2 21.47 2 20.92V16.92C2 16.37 2.45 15.92 3 15.92H9C9.55 15.92 10 16.37 10 16.92Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /><path d="M10 12.92V14.92C10 15.47 9.55 15.92 9 15.92H3C2.45 15.92 2 15.47 2 14.92V12.92C2 12.37 2.45 11.92 3 11.92H9C9.55 11.92 10 12.37 10 12.92Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /></svg>
                        {{ $user->phone }}
                    </span>
                    @endif
                </div>

                {{-- 3. Progress Bar (Sekarang ada DI BAWAH Nama & Meta, DI DALAM container Info) --}}
                {{-- PERBAIKAN: Segmented Progress Bar (Detail per Tahap) --}}
                @php
                    // Hitung progress details dan variabel terkait
                    $progressDetails = $user->progress_details ?? [
                        'Biodata' => ['status' => false, 'missing' => []],
                        'Registrasi Ulang' => ['status' => false, 'missing' => []],
                        'Ujian' => ['status' => false],
                        'Pembayaran' => ['status' => false],
                        'PDDIKTI' => ['status' => false]
                    ];
                    
                    // Hitung persentase progress
                    $totalSteps = count($progressDetails);
                    $completedSteps = collect($progressDetails)->where('status', true)->count();
                    $percent = $totalSteps > 0 ? ($completedSteps / $totalSteps) * 100 : 0;
                    
                    // Tentukan step berikutnya yang belum selesai
                    $currentLabel = 'Selesai';
                    foreach ($progressDetails as $label => $info) {
                        if (!$info['status']) {
                            $currentLabel = $label;
                            break;
                        }
                    }
                    
                    // Status verifikasi
                    $isVerified = $user->masterUsersVerified && $user->masterUsersVerified->count() > 0;
                    
                    // Status kelulusan
                    $kelulusan = $kelulusanData[$user->masterUser->id ?? 0] ?? null;
                    
                    // Cek apakah prodi Farmasi
                    $isFarmasi = false;
                    if ($user->masterEreg) {
                        $jurusan1 = strtolower($user->masterEreg->jurusan1 ?? '');
                        $jurusan2 = strtolower($user->masterEreg->jurusan2 ?? '');
                        $isFarmasi = str_contains($jurusan1, 'farmasi') || 
                                     str_contains($jurusan2, 'farmasi') ||
                                     str_contains($jurusan1, '48201') ||
                                     str_contains($jurusan2, '48201');
                    }
                    
                    // Verifikasi farmasi
                    $farmasiVerification = $farmasiVerifications[$user->id] ?? null;
                    
                    // Status ereg (PERBAIKAN UTAMA: gunakan dari user object)
                    $eregStatus = $user->ereg_status ?? null;
                @endphp
                
                <div class="cyberpunk-steps-container">
                    <div class="cyberpunk-steps-header">
                        <span class="cyberpunk-steps-percentage">{{ round($percent) }}%</span>
                        <span class="cyberpunk-steps-status">
                            Next: <strong>{{ $currentLabel }}</strong>
                        </span>
                    </div>
                    
                    <div class="cyberpunk-steps-bar">
                        @foreach($progressDetails as $label => $info)
                            @php
                                $tooltip = "$label: " . ($info['status'] ? 'Selesai' : 'Belum Lengkap');
                                // Tambahkan info apa yang kurang di tooltip
                                if (!$info['status'] && !empty($info['missing'] ?? [])) {
                                    $missingList = implode(', ', array_slice($info['missing'], 0, 3)); // Ambil 3 pertama
                                    $tooltip .= " (Kurang: $missingList)";
                                }
                            @endphp
                            
                            <div class="cyberpunk-step-segment {{ $info['status'] ? 'completed' : '' }}" 
                                 title="{{ $tooltip }}">
                            </div>
                        @endforeach
                    </div>
                    
                    {{-- Label Kecil di bawah bar --}}
                    <div class="cyberpunk-steps-labels-row">
                        @foreach(array_keys($progressDetails) as $key)
                            <span class="{{ $progressDetails[$key]['status'] ? 'text-active' : '' }}">{{ substr($key, 0, 3) }}</span>
                        @endforeach
                    </div>

                    {{-- Pesan Error Text Kecil (Opsional: Jika Biodata Kurang) --}}
                    @if(!$progressDetails['Biodata']['status'] && !empty($progressDetails['Biodata']['missing']))
                        <div style="font-size: 0.6rem; color: var(--cyberpunk-error); margin-top: 4px; line-height: 1.2;">
                            *Biodata kurang: {{ implode(', ', array_slice($progressDetails['Biodata']['missing'], 0, 2)) }}...
                        </div>
                    @endif
                </div>
                {{-- END PERBAIKAN --}}
            </div>
        </div>

        <div class="cyberpunk-user-status">
            <div class="cyberpunk-status-badges">
                {{-- PERBAIKAN: Gunakan $eregStatus dari user object --}}
                <span class="cyberpunk-badge {{ $eregStatusColors[$eregStatus] ?? 'cyberpunk-badge-gray' }} cyberpunk-badge-sm">
                    {{ $eregStatusLabels[$eregStatus] ?? 'Unknown' }}
                </span>

                <span class="cyberpunk-badge {{ $isVerified ? 'cyberpunk-badge-success' : 'cyberpunk-badge-warning' }} cyberpunk-badge-sm">
                    {{ $isVerified ? '✓ Terverifikasi' : 'Belum Verifikasi' }}
                </span>

                @if($kelulusan)
                <span class="cyberpunk-badge cyberpunk-badge-accent cyberpunk-badge-sm">
                    ✓ Lulus
                </span>
                @endif

                @if($isFarmasi)
                <span class="cyberpunk-badge cyberpunk-badge-primary cyberpunk-badge-sm">
                    Farmasi
                </span>
                @endif
            </div>

            @if($farmasiVerification)
            <div class="cyberpunk-farmasi-status">
                <span class="cyberpunk-badge {{ $farmasiVerification->status_verifikasi == 'verified' ? 'cyberpunk-badge-success' : ($farmasiVerification->status_verifikasi == 'rejected' ? 'cyberpunk-badge-error' : 'cyberpunk-badge-warning') }} cyberpunk-badge-sm">
                    Farmasi: {{ strtoupper($farmasiVerification->status_verifikasi) }}
                    @if($farmasiVerification->nama_verifikator)
                    <span class="cyberpunk-verifikator">({{ $farmasiVerification->nama_verifikator }})</span>
                    @endif
                </span>
            </div>
            @endif
        </div>

        <div class="cyberpunk-user-actions">

    {{-- Tombol Beasiswa --}}
    <form action="{{ route('admin.profile-lengkap.beasiswa', $user->id) }}"
          method="POST"
          style="display:inline-block;"
          onsubmit="return confirm('Ubah status beasiswa mahasiswa ini?')">
        @csrf

        @if($user->kel_beasiswa == 1)
            <button type="submit"
                    class="cyberpunk-btn cyberpunk-btn-success cyberpunk-btn-sm">
                🎓 Penerima Beasiswa
            </button>
        @else
            <button type="submit"
                    class="cyberpunk-btn cyberpunk-btn-warning cyberpunk-btn-sm">
                🎓 Dapatkan Beasiswa
            </button>
        @endif
    </form>

    {{-- Tombol Detail --}}
    <button type="button"
        onclick="toggleDetails({{ $user->id }})"
        class="cyberpunk-toggle-btn"
        title="Lihat Detail">
        <svg id="icon-{{ $user->id }}" class="cyberpunk-toggle-icon"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

</div>
    </div>

        <div id="details-{{ $user->id }}" class="cyberpunk-user-details-panel">
            <div class="cyberpunk-details-tabs">
                <button class="cyberpunk-tab active" data-tab="personal-{{ $user->id }}">Informasi Pribadi</button>
                <button class="cyberpunk-tab" data-tab="academic-{{ $user->id }}">Informasi Akademik</button>
                <button class="cyberpunk-tab" data-tab="pddikti-{{ $user->id }}">Informasi PDDIKTI</button>
                <button class="cyberpunk-tab" data-tab="files-{{ $user->id }}">Berkas Upload</button>
            </div>

            <div class="cyberpunk-tab-contents">
                <div class="cyberpunk-tab-content active" id="personal-{{ $user->id }}">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <h4 style="color: #00f0ff; margin: 0;">Data Akun Pengguna</h4>
                            <button type="button" onclick="openEditAkunModal({{ $user->id }})" class="cyberpunk-btn cyberpunk-btn-primary cyberpunk-btn-sm">
                                <i class="fas fa-user-edit"></i> Edit Nama & Email
                            </button>
                        </div>
                    <div class="cyberpunk-info-grid">
                        <div class="cyberpunk-info-row">
                            <div class="cyberpunk-info-col">
                                <label class="cyberpunk-info-label">Nama Lengkap</label>
                                <p class="cyberpunk-info-value">{{ $user->biodata->nama_lengkap ?? $user->name }}</p>
                            </div>
                            <div class="cyberpunk-info-col">
                                <label class="cyberpunk-info-label">Email</label>
                                <p class="cyberpunk-info-value">{{ $user->email }}</p>
                            </div>
                        </div>
                        <div class="cyberpunk-info-row">
                            <div class="cyberpunk-info-col">
                                <label class="cyberpunk-info-label">Telepon</label>
                                <p class="cyberpunk-info-value">{{ $user->phone ?? '-' }}</p>
                            </div>
                            <div class="cyberpunk-info-col">
                                <label class="cyberpunk-info-label">Role</label>
                                <p class="cyberpunk-info-value">{{ $user->role }}</p>
                            </div>
                        </div>
                        
                        @if($user->biodata)
                        <div class="cyberpunk-info-row">
                            <div class="cyberpunk-info-col">
                                <label class="cyberpunk-info-label">Agama</label>
                                <p class="cyberpunk-info-value">{{ $user->biodata->agama ?? '-' }}</p>
                            </div>
                            <div class="cyberpunk-info-col">
                                <label class="cyberpunk-info-label">Alamat</label>
                                <p class="cyberpunk-info-value">{{ $user->biodata->alamat ?? '-' }}</p>
                            </div>
                        </div>
                        @endif

                        <div class="cyberpunk-info-section">
                            <h4 class="cyberpunk-info-section-title">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" /><path d="M12 8V12L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" /></svg>
                                Informasi Virtual Account
                            </h4>
                            <div class="cyberpunk-info-grid">
                                <div class="cyberpunk-info-row">
                                    <div class="cyberpunk-info-col">
                                        <label class="cyberpunk-info-label">Nomor VA</label>
                                        @if($user->va_number)
                                        <p class="cyberpunk-info-value cyberpunk-va-number">{{ $user->va_number }}</p>
                                        @else
                                        <p class="cyberpunk-info-muted">-</p>
                                        @endif
                                    </div>
                                    <div class="cyberpunk-info-col">
                                        <label class="cyberpunk-info-label">Status VA</label>
                                        <span class="cyberpunk-badge {{ isset($vaStatusColors[$user->va_status]) ? $vaStatusColors[$user->va_status] : 'cyberpunk-badge-gray' }}">
                                            {{ isset($vaStatusLabels[$user->va_status]) ? $vaStatusLabels[$user->va_status] : 'Tidak Diketahui' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="cyberpunk-tab-content" id="academic-{{ $user->id }}">
                    <div class="cyberpunk-info-grid">
                        <div class="cyberpunk-info-section">
                            <h4 class="cyberpunk-info-section-title">Status Kelulusan</h4>
                            @if($kelulusan)
                            <div class="cyberpunk-success-box">
                                <div class="cyberpunk-success-content">
                                    <h5 class="cyberpunk-success-title">{{ $kelulusan->nama_prodi_lulus ?? 'N/A' }}</h5>
                                    <div class="cyberpunk-success-details">
                                        <span class="cyberpunk-success-code">{{ $kelulusan->prodi_lulus ?? 'N/A' }}</span>
                                        @if($kelulusan->nilai_quiz)
                                        <span class="cyberpunk-success-nilai">Nilai: {{ number_format($kelulusan->nilai_quiz, 2) }}</span>
                                        @endif
                                    </div>
                                    @if($kelulusan->created_at)
                                    <p class="cyberpunk-success-date">
                                        {{ $kelulusan->created_at->format('d F Y H:i') }}
                                    </p>
                                    @endif
                                </div>
                                <span class="cyberpunk-badge cyberpunk-badge-accent">LULUS</span>
                            </div>
                            @else
                            <div class="cyberpunk-muted-box">
                                <p class="cyberpunk-muted-text">Belum ada data kelulusan</p>
                            </div>
                            @endif
                        </div>

                        <div class="cyberpunk-info-section">
                            @if(!in_array(auth()->user()->role, ['admin_akademik', 'admin_farmasi']))
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                                <h4 class="cyberpunk-info-section-title" style="margin-bottom: 0;">Prodi Pilihan</h4>
                                <button type="button" onclick="openEditProdiModal({{ $user->id }})" class="cyberpunk-btn cyberpunk-btn-primary cyberpunk-btn-sm">
                                    <svg class="cyberpunk-btn-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    Ubah Prodi
                                </button>
                            </div>
                            @endif
                            <div class="cyberpunk-prodi-choices">
                                @if($user->masterUser && $user->masterUser->gelombang)
                                <div class="cyberpunk-prodi-choice">
                                    <span class="cyberpunk-prodi-label">Gelombang</span>
                                    <span class="cyberpunk-prodi-value">{{ $user->masterUser->gelombang }}</span>
                                </div>
                                @endif

                                @if($user->masterEreg && $user->masterEreg->jurusan1)
                                <div class="cyberpunk-prodi-choice cyberpunk-prodi-primary">
                                    <span class="cyberpunk-prodi-label">Pilihan 1</span>
                                    <span class="cyberpunk-prodi-value">
                                        {{ $prodiList[$user->masterEreg->jurusan1] ?? $user->masterEreg->jurusan1 }}
                                    </span>
                                </div>
                                @endif

                                @if($user->masterEreg && $user->masterEreg->jurusan2)
                                <div class="cyberpunk-prodi-choice cyberpunk-prodi-secondary">
                                    <span class="cyberpunk-prodi-label">Pilihan 2</span>
                                    <span class="cyberpunk-prodi-value">
                                        {{ $prodiList[$user->masterEreg->jurusan2] ?? $user->masterEreg->jurusan2 }}
                                    </span>
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="cyberpunk-info-section">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                                <h4 class="cyberpunk-info-section-title" style="margin-bottom: 0;">Jalur Pendaftaran</h4>
                    
                            </div>

                            <div class="cyberpunk-prodi-choices">
                                @if($user->masterEreg && $user->masterEreg->jalur_pendaftaran)
                                <div class="cyberpunk-prodi-choice cyberpunk-prodi-primary">
                                    <span class="cyberpunk-prodi-label">Jalur Pendaftaran</span>
                                    <span class="cyberpunk-prodi-value">
                                        {{ $prodiList[$user->masterEreg->jalur_pendaftaran] ?? $user->masterEreg->jalur_pendaftaran }}
                                    </span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="cyberpunk-tab-content" id="pddikti-{{ $user->id }}">
    @if($user->dataMahasiswaFinal)
        <div class="cyberpunk-info-grid">
            
            <div class="cyberpunk-info-section">
                <h4 class="cyberpunk-info-section-title">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    Identitas Pribadi
                </h4>
                <div class="cyberpunk-info-row">
                    <div class="cyberpunk-info-col">
                        <label class="cyberpunk-info-label">Nama Mahasiswa</label>
                        <p class="cyberpunk-info-value">{{ $user->dataMahasiswaFinal->nama_mahasiswa }}</p>
                    </div>
                    <div class="cyberpunk-info-col">
                        <label class="cyberpunk-info-label">Jenis Kelamin</label>
                        <p class="cyberpunk-info-value">{{ $user->dataMahasiswaFinal->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
                    </div>
                    <div class="cyberpunk-info-col">
                        <label class="cyberpunk-info-label">Kewarganegaraan</label>
                        <p class="cyberpunk-info-value">{{ $user->dataMahasiswaFinal->kewarganegaraan ?? 'ID' }}</p>
                    </div>
                </div>
                <div class="cyberpunk-info-row">
                    <div class="cyberpunk-info-col">
                        <label class="cyberpunk-info-label">Tempat Lahir</label>
                        <p class="cyberpunk-info-value">{{ $user->dataMahasiswaFinal->tempat_lahir ?? '-' }}</p>
                    </div>
                    <div class="cyberpunk-info-col">
                        <label class="cyberpunk-info-label">Tanggal Lahir</label>
                        <p class="cyberpunk-info-value">{{ $user->dataMahasiswaFinal->tanggal_lahir ?? '-' }}</p>
                    </div>
                    <div class="cyberpunk-info-col">
                        <label class="cyberpunk-info-label">Agama (ID)</label>
                        <p class="cyberpunk-info-value">{{ $user->dataMahasiswaFinal->id_agama ?? '-' }}</p>
                    </div>
                </div>
                <div class="cyberpunk-info-row">
                    <div class="cyberpunk-info-col">
                        <label class="cyberpunk-info-label">NIK</label>
                        <p class="cyberpunk-info-value">{{ $user->dataMahasiswaFinal->nik ?? '-' }}</p>
                    </div>
                    <div class="cyberpunk-info-col">
                        <label class="cyberpunk-info-label">NISN</label>
                        <p class="cyberpunk-info-value">{{ $user->dataMahasiswaFinal->nisn ?? '-' }}</p>
                    </div>
                    <div class="cyberpunk-info-col">
                        <label class="cyberpunk-info-label">NPWP</label>
                        <p class="cyberpunk-info-value">{{ $user->dataMahasiswaFinal->npwp ?? '-' }}</p>
                    </div>
                </div>
                <div class="cyberpunk-info-row">
                    <div class="cyberpunk-info-col">
                        <label class="cyberpunk-info-label">Penerima KPS</label>
                        <p class="cyberpunk-info-value">
                            @if($user->dataMahasiswaFinal->penerima_kps == '1')
                                <span class="cyberpunk-badge cyberpunk-badge-success">Ya</span>
                            @else
                                Tidak
                            @endif
                        </p>
                    </div>
                    <div class="cyberpunk-info-col">
                        <label class="cyberpunk-info-label">Nomor KPS</label>
                        <p class="cyberpunk-info-value">{{ $user->dataMahasiswaFinal->no_kps ?? '-' }}</p>
                    </div>
                    <div class="cyberpunk-info-col">
                        <label class="cyberpunk-info-label">Alat Transportasi (ID)</label>
                        <p class="cyberpunk-info-value">{{ $user->dataMahasiswaFinal->id_alat_transportasi ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <div class="cyberpunk-info-section">
                <h4 class="cyberpunk-info-section-title">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                    Alamat Domisili
                </h4>
                <div class="cyberpunk-info-row">
                    <div class="cyberpunk-info-col" style="flex:2">
                        <label class="cyberpunk-info-label">Jalan</label>
                        <p class="cyberpunk-info-value">{{ $user->dataMahasiswaFinal->jalan ?? '-' }}</p>
                    </div>
                    <div class="cyberpunk-info-col">
                        <label class="cyberpunk-info-label">RT / RW</label>
                        <p class="cyberpunk-info-value">{{ $user->dataMahasiswaFinal->rt }} / {{ $user->dataMahasiswaFinal->rw }}</p>
                    </div>
                </div>
                <div class="cyberpunk-info-row">
                    <div class="cyberpunk-info-col">
                        <label class="cyberpunk-info-label">Dusun</label>
                        <p class="cyberpunk-info-value">{{ $user->dataMahasiswaFinal->dusun ?? '-' }}</p>
                    </div>
                    <div class="cyberpunk-info-col">
                        <label class="cyberpunk-info-label">Kelurahan</label>
                        <p class="cyberpunk-info-value">{{ $user->dataMahasiswaFinal->kelurahan ?? '-' }}</p>
                    </div>
                    <div class="cyberpunk-info-col">
                        <label class="cyberpunk-info-label">Kode Pos</label>
                        <p class="cyberpunk-info-value">{{ $user->dataMahasiswaFinal->kode_pos ?? '-' }}</p>
                    </div>
                </div>
                <div class="cyberpunk-info-row">
                    <div class="cyberpunk-info-col">
                        <label class="cyberpunk-info-label">Jenis Tinggal (ID)</label>
                        <p class="cyberpunk-info-value">{{ $user->dataMahasiswaFinal->id_jenis_tinggal ?? '-' }}</p>
                    </div>
                    <div class="cyberpunk-info-col">
                        <label class="cyberpunk-info-label">Wilayah (ID)</label>
                        <p class="cyberpunk-info-value">{{ $user->dataMahasiswaFinal->id_wilayah ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <div class="cyberpunk-info-section">
                <h4 class="cyberpunk-info-section-title">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    Data Orang Tua & Wali
                </h4>
                
                <div style="background: rgba(0,0,0,0.02); padding: 10px; border-radius: 8px; margin-bottom: 10px;">
                    <h5 style="font-size: 0.8rem; font-weight: bold; margin-bottom: 8px; color: var(--cyberpunk-primary);">DATA AYAH</h5>
                    <div class="cyberpunk-info-row">
                        <div class="cyberpunk-info-col">
                            <label class="cyberpunk-info-label">Nama Ayah</label>
                            <p class="cyberpunk-info-value">{{ $user->dataMahasiswaFinal->nama_ayah ?? '-' }}</p>
                        </div>
                        <div class="cyberpunk-info-col">
                            <label class="cyberpunk-info-label">NIK Ayah</label>
                            <p class="cyberpunk-info-value">{{ $user->dataMahasiswaFinal->nik_ayah ?? '-' }}</p>
                        </div>
                        <div class="cyberpunk-info-col">
                            <label class="cyberpunk-info-label">Tanggal Lahir</label>
                            <p class="cyberpunk-info-value">{{ $user->dataMahasiswaFinal->tanggal_lahir_ayah ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="cyberpunk-info-row">
                        <div class="cyberpunk-info-col">
                            <label class="cyberpunk-info-label">Pendidikan (ID)</label>
                            <p class="cyberpunk-info-value">{{ $user->dataMahasiswaFinal->id_jenjang_pendidikan_ayah ?? '-' }}</p>
                        </div>
                        <div class="cyberpunk-info-col">
                            <label class="cyberpunk-info-label">Pekerjaan (ID)</label>
                            <p class="cyberpunk-info-value">{{ $user->dataMahasiswaFinal->id_pekerjaan_ayah ?? '-' }}</p>
                        </div>
                        <div class="cyberpunk-info-col">
                            <label class="cyberpunk-info-label">Penghasilan (ID)</label>
                            <p class="cyberpunk-info-value">{{ $user->dataMahasiswaFinal->id_penghasilan_ayah ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <div style="background: rgba(0,0,0,0.02); padding: 10px; border-radius: 8px; margin-bottom: 10px;">
                    <h5 style="font-size: 0.8rem; font-weight: bold; margin-bottom: 8px; color: var(--cyberpunk-primary);">DATA IBU</h5>
                    <div class="cyberpunk-info-row">
                        <div class="cyberpunk-info-col">
                            <label class="cyberpunk-info-label">Nama Ibu Kandung</label>
                            <p class="cyberpunk-info-value">{{ $user->dataMahasiswaFinal->nama_ibu_kandung ?? '-' }}</p>
                        </div>
                        <div class="cyberpunk-info-col">
                            <label class="cyberpunk-info-label">NIK Ibu</label>
                            <p class="cyberpunk-info-value">{{ $user->dataMahasiswaFinal->nik_ibu ?? '-' }}</p>
                        </div>
                        <div class="cyberpunk-info-col">
                            <label class="cyberpunk-info-label">Tanggal Lahir</label>
                            <p class="cyberpunk-info-value">{{ $user->dataMahasiswaFinal->tanggal_lahir_ibu ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="cyberpunk-info-row">
                        <div class="cyberpunk-info-col">
                            <label class="cyberpunk-info-label">Pendidikan (ID)</label>
                            <p class="cyberpunk-info-value">{{ $user->dataMahasiswaFinal->id_jenjang_pendidikan_ibu ?? '-' }}</p>
                        </div>
                        <div class="cyberpunk-info-col">
                            <label class="cyberpunk-info-label">Pekerjaan (ID)</label>
                            <p class="cyberpunk-info-value">{{ $user->dataMahasiswaFinal->id_pekerjaan_ibu ?? '-' }}</p>
                        </div>
                        <div class="cyberpunk-info-col">
                            <label class="cyberpunk-info-label">Penghasilan (ID)</label>
                            <p class="cyberpunk-info-value">{{ $user->dataMahasiswaFinal->id_penghasilan_ibu ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                @if($user->dataMahasiswaFinal->nama_wali)
                <div style="background: rgba(0,0,0,0.02); padding: 10px; border-radius: 8px;">
                    <h5 style="font-size: 0.8rem; font-weight: bold; margin-bottom: 8px; color: var(--cyberpunk-primary);">DATA WALI</h5>
                    <div class="cyberpunk-info-row">
                        <div class="cyberpunk-info-col">
                            <label class="cyberpunk-info-label">Nama Wali</label>
                            <p class="cyberpunk-info-value">{{ $user->dataMahasiswaFinal->nama_wali ?? '-' }}</p>
                        </div>
                        <div class="cyberpunk-info-col">
                            <label class="cyberpunk-info-label">Tanggal Lahir</label>
                            <p class="cyberpunk-info-value">{{ $user->dataMahasiswaFinal->tanggal_lahir_wali ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="cyberpunk-info-row">
                        <div class="cyberpunk-info-col">
                            <label class="cyberpunk-info-label">Pendidikan (ID)</label>
                            <p class="cyberpunk-info-value">{{ $user->dataMahasiswaFinal->id_jenjang_pendidikan_wali ?? '-' }}</p>
                        </div>
                        <div class="cyberpunk-info-col">
                            <label class="cyberpunk-info-label">Pekerjaan (ID)</label>
                            <p class="cyberpunk-info-value">{{ $user->dataMahasiswaFinal->id_pekerjaan_wali ?? '-' }}</p>
                        </div>
                        <div class="cyberpunk-info-col">
                            <label class="cyberpunk-info-label">Penghasilan (ID)</label>
                            <p class="cyberpunk-info-value">{{ $user->dataMahasiswaFinal->id_penghasilan_wali ?? '-' }}</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <div class="cyberpunk-info-section">
                <h4 class="cyberpunk-info-section-title">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                    Kontak
                </h4>
                <div class="cyberpunk-info-row">
                    <div class="cyberpunk-info-col">
                        <label class="cyberpunk-info-label">Telepon Rumah</label>
                        <p class="cyberpunk-info-value">{{ $user->dataMahasiswaFinal->telepon ?? '-' }}</p>
                    </div>
                    <div class="cyberpunk-info-col">
                        <label class="cyberpunk-info-label">Handphone</label>
                        <p class="cyberpunk-info-value">{{ $user->dataMahasiswaFinal->handphone ?? '-' }}</p>
                    </div>
                    <div class="cyberpunk-info-col">
                        <label class="cyberpunk-info-label">Email</label>
                        <p class="cyberpunk-info-value">{{ $user->dataMahasiswaFinal->email ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <div class="cyberpunk-alert cyberpunk-alert-success mt-4">
                <p class="cyberpunk-alert-text">
                    Status Data: <strong>{{ strtoupper($user->dataMahasiswaFinal->status_data) }}</strong> (Terakhir diupdate: {{ $user->dataMahasiswaFinal->updated_at }})
                </p>
            </div>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; border-bottom: 1px solid #333; padding-bottom: 10px;">
    <h3 style="color: #00f0ff; margin: 0;">Data Master PDDIKTI</h3>
    <button type="button" onclick="openEditPddiktiModal({{ $user->id }})" class="cyberpunk-btn cyberpunk-btn-primary cyberpunk-btn-sm">
        <i class="fas fa-edit"></i> Edit Data PDDIKTI
    </button>
</div>
        </div>
    @else
        <div class="cyberpunk-empty-state">
            <svg class="cyberpunk-empty-icon" width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            <p class="cyberpunk-empty-text">User belum melakukan finalisasi data PDDIKTI.</p>
        </div>
    @endif
</div>

               <div class="cyberpunk-tab-content" id="files-{{ $user->id }}">
    @if($user->masterEreg)
        @php
            // Pemetaan Field Database ke Label Manusiawi
            $allFiles = [
                'file_kk' => 'Kartu Keluarga',
                'file_ijazah' => 'Ijazah Terakhir',
                'file_skl' => 'SKL',
                'file_rapor' => 'Rapor',
                'file_ktp' => 'KTP',
                'file_surat_keterangan_sehat' => 'Surat Sehat',
                'file_ijazah_s1' => 'Ijazah S1',
                'file_transkrip_s1' => 'Transkrip S1',
                'file_surat_buta_warna' => 'Surat Buta Warna',
                'file_surat_pindah' => 'Surat Pindah',
                'file_ktm' => 'KTM (Pindahan)',
                'file_surat_akademik_rpl' => 'Berkas RPL'
            ];
            $hasAnyFile = false;
        @endphp

        <div class="cyberpunk-files-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; padding: 10px;">
            @foreach($allFiles as $field => $label)
                @if($user->masterEreg->$field)
                    @php $hasAnyFile = true; @endphp
                    <div class="cyberpunk-file-card cyberpunk-file-preview-card" style="background: #0a0a0a; border: 1px solid #333; padding: 15px; position: relative; overflow: hidden;">
                        <div class="cyberpunk-file-info">
                            {{-- Gunakan $label untuk Judul --}}
                            <span class="cyberpunk-file-name" style="color: #00f0ff; font-weight: bold; display: block; margin-bottom: 10px; font-size: 0.9rem; text-transform: uppercase;">
                                {{ $label }}
                            </span>
                            
                            <div class="cyberpunk-pdf-container" style="background: #1a1a1a; height: 180px; margin-bottom: 15px; border: 1px solid #222; display: flex; align-items: center; justify-content: center;">
                                {{-- Gunakan $field untuk Path --}}
                                <embed 
                                    src="{{ asset('storage/' . $user->masterEreg->$field) }}" 
                                    type="application/pdf" 
                                    width="100%" 
                                    height="100%" 
                                    class="cyberpunk-pdf-embed"
                                />
                            </div>

                            <div class="cyberpunk-file-actions" style="display: flex; gap: 8px;">
                                <a href="{{ asset('storage/' . $user->masterEreg->$field) }}" 
                                   target="_blank" 
                                   class="cyberpunk-btn cyberpunk-btn-sm cyberpunk-btn-outline"
                                   style="flex: 1; text-align: center; font-size: 10px; padding: 8px 5px; text-decoration: none; border: 1px solid #00f0ff; color: #00f0ff;">
                                    <i class="fas fa-expand" style="margin-right: 3px;"></i> VIEW
                                </a>

                                <a href="{{ asset('storage/' . $user->masterEreg->$field) }}" 
                                   download 
                                   style="flex: 1; text-align: center; font-size: 10px; padding: 8px 5px; background: #333; color: #fff; text-decoration: none; border-radius: 2px;">
                                    <i class="fas fa-download"></i> DOWNLOAD
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach

            @if(!$hasAnyFile)
                <div style="grid-column: 1/-1; padding: 40px; text-align: center; color: #666; border: 1px dashed #333; background: rgba(255,255,255,0.02);">
                    <i class="fas fa-folder-open" style="font-size: 2rem; margin-bottom: 10px;"></i>
                    <p>Mahasiswa belum mengunggah berkas fisik pada sistem E-Reg.</p>
                </div>
            @endif
        </div>
    @else
        <div class="cyberpunk-empty-files" style="padding: 60px; text-align: center; background: rgba(255,0,60,0.05); border: 1px dashed #ff003c; border-radius: 4px;">
            <i class="fas fa-exclamation-triangle" style="color: #ff003c; font-size: 2rem; margin-bottom: 15px;"></i>
            <p style="color: #ff003c; font-weight: bold; text-transform: uppercase; letter-spacing: 1px;">
                Data Registrasi Ulang (E-Reg) Belum Ada
            </p>
        </div>
    @endif
</div>
            </div>
            
            <div class="cyberpunk-action-panel" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #333; display: flex; justify-content: flex-end; gap: 10px; flex-wrap: wrap;">
    @php
    // Cek status kelulusan user
    $isLulus = $user->masterUser && $user->masterUser->masterMoodleLulus;
@endphp

<div style="display: inline-block;">
    @if(!$isLulus)
    
          @if(!in_array(auth()->user()->role, ['admin_akademik', 'admin_farmasi']))
        <button type="button"
        
                onclick="openLulusModal({{ $user->id }})"
                style="background: #ff003c; color: white; border: none; padding: 8px 15px; cursor: pointer; font-weight: bold; clip-path: polygon(10% 0, 100% 0, 90% 100%, 0 100%); transition: 0.3s;">
            <i class="fas fa-graduation-cap"></i> Luluskan Manual
        </button>
        @endif

        {{-- Modal Popup --}}
        <div id="lulusModal-{{ $user->id }}" style="display: none; position: fixed; z-index: 99999; inset: 0; font-family: sans-serif;">
            {{-- Overlay --}}
            <div onclick="closeLulusModal({{ $user->id }})" style="background: rgba(0,0,0,0.9); position: absolute; inset: 0; backdrop-blur: 5px;"></div>
            
            {{-- Content --}}
            <div style="position: relative; margin: 10% auto; max-width: 400px; background: #0a0a0a; border: 2px solid #00f0ff; padding: 25px; box-shadow: 0 0 20px rgba(0, 240, 255, 0.3);">
                <div style="border-bottom: 1px solid #333; padding-bottom: 10px; margin-bottom: 20px;">
                    <h3 style="color: #00f0ff; margin: 0; text-transform: uppercase; letter-spacing: 2px;">Input Nilai Kelulusan</h3>
                </div>
                
                <form action="{{ route('admin.profile-lengkap.luluskan', $user->id) }}" method="POST">
                    @csrf
                    <div style="margin-bottom: 20px;">
                        <label style="color: #ccc; display: block; margin-bottom: 10px; font-size: 0.9rem;">Masukkan Nilai (0 - 100):</label>
                        <input type="number" 
                               name="nilai_manual" 
                               min="0" 
                               max="100" 
                               step="0.01" 
                               value="100" 
                               required 
                               style="width: 100%; background: #1a1a1a; border: 1px solid #00f0ff; color: #fff; padding: 12px; box-sizing: border-box; font-size: 1.1rem; outline: none;">
                        <p style="font-size: 0.75rem; color: #ff003c; margin-top: 10px; font-style: italic;">
                            *Mahasiswa akan otomatis tercatat LULUS di sistem.
                        </p>
                    </div>

                    <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 30px;">
                        <button type="button" 
                                onclick="closeLulusModal({{ $user->id }})" 
                                style="background: #333; color: white; border: none; padding: 10px 20px; cursor: pointer; font-weight: bold; text-transform: uppercase;">
                            Batal
                        </button>
                        <button type="submit" 
                                style="background: #00f0ff; color: black; border: none; padding: 10px 20px; cursor: pointer; font-weight: bold; text-transform: uppercase; box-shadow: 0 0 10px rgba(0, 240, 255, 0.5);">
                            Konfirmasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @else
        <div style="background: rgba(0, 240, 255, 0.1); color: #00f0ff; border: 1px solid #00f0ff; padding: 5px 12px; font-size: 0.8rem; font-weight: bold; text-transform: uppercase; display: inline-block;">
            ✓ Sudah Lulus
        </div>
    @endif
</div>
<script>
    function openLulusModal(id) {
        document.getElementById('lulusModal-' + id).style.display = 'block';
        document.body.style.overflow = 'hidden'; // Matikan scroll background
    }

    function closeLulusModal(id) {
        document.getElementById('lulusModal-' + id).style.display = 'none';
        document.body.style.overflow = 'auto'; // Aktifkan kembali scroll
    }
</script>
            @if(!$isVerified)
                <form action="{{ route('admin.profile-lengkap.verify', $user->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="cyberpunk-btn cyberpunk-btn-success">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 5px;"><path d="M20 6L9 17l-5-5"/></svg>
                        Verifikasi User
                    </button>
                </form>
            @else
                <form action="{{ route('admin.profile-lengkap.unverify', $user->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="cyberpunk-btn cyberpunk-btn-error">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 5px;"><path d="M18 6L6 18M6 6l12 12"/></svg>
                        Batalkan Verifikasi
                    </button>
                </form>
            @endif

          @if(!in_array(auth()->user()->role, ['admin_akademik', 'admin_farmasi']))
            @if($user->masterEreg)
                <form action="{{ route('admin.profile-lengkap.delete-ereg-files', $user->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus semua berkas user ini?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="cyberpunk-btn cyberpunk-btn-secondary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 5px;"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        Hapus Berkas
                    </button>
                </form>
            @endif
@endif
            {{-- 3. Tombol Khusus Farmasi --}}
            {{-- 3. Tombol Khusus Farmasi --}}
@if($isFarmasi)
    {{-- 
        KONDISI: 
        1. Jika belum ada data di tabel farmasi_verifications 
        2. ATAU statusnya masih 'pending' 
    --}}
    @if(!$farmasiVerification || $farmasiVerification->status_verifikasi == 'pending')
        <a href="{{ route('admin.profile-lengkap.create-farmasi-verification', $user->id) }}" class="cyberpunk-btn cyberpunk-btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 5px;">
                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Verifikasi Farmasi
        </a>
    @else
        {{-- Jika status sudah 'verified' atau 'rejected', tampilkan tombol batalkan --}}
        <form action="{{ route('admin.profile-lengkap.destroy-farmasi-verification', $user->id) }}" method="POST" onsubmit="return confirm('Batalkan verifikasi khusus farmasi ini?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="cyberpunk-btn cyberpunk-btn-warning">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 5px;">
                    <path d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Batalkan Farmasi
            </button>
        </form>
    @endif
@endif
@if(request()->filled('kelola_khusus'))
                @php
                    // Ambil status verifikasi user ini. Jika tidak ada di array, default 0
                    $statusKelola = $pengelolaanStatus[$user->id] ?? 0;
                @endphp

                <form action="{{ route('admin.profile-lengkap.kirim-pengelolaan', $user->id) }}" method="POST" style="display:inline-block;">
                    @csrf
                    
                    @if($statusKelola == 1)
                        {{-- TAMPILAN JIKA SUDAH DIVERIFIKASI (Tombol Batalkan Merah) --}}
                        <button type="submit" 
                                class="cyberpunk-btn cyberpunk-btn-error" 
                                onclick="return confirm('Anda yakin ingin MEMBATALKAN verifikasi jalur khusus untuk user ini?');">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 5px;">
                                <path d="M18 6L6 18M6 6l12 12"/>
                            </svg>
                            Batalkan Konfirmasi
                        </button>
                    @else
                        {{-- TAMPILAN JIKA BELUM ADA / DIBATALKAN (Tombol Kirim Kuning) --}}
                        <button type="submit" 
                                class="cyberpunk-btn cyberpunk-btn-warning" 
                                style="background: rgba(255, 170, 0, 0.1); color: #ffaa00; border-color: #ffaa00;"
                                onclick="return confirm('Kirim dan verifikasi user ini ke tabel pengelolaan jalur khusus?');">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 5px;">
                                <path d="M5 12h14M12 5l7 7-7 7"/>
                            </svg>
                            Sudah Konfirmasi
                        </button>
                    @endif
                </form>
            @endif
        </div>
        {{-- Modal Edit Akun Utama (Nama & Email) --}}
<div class="cyberpunk-modal" id="editAkunModal-{{ $user->id }}" style="display: none;">
    <div class="cyberpunk-modal-overlay" onclick="closeEditAkunModal({{ $user->id }})"></div>
    <div class="cyberpunk-modal-content" style="max-width: 450px;">
        <div class="cyberpunk-modal-header">
            <h3 class="cyberpunk-modal-title">
                <i class="fas fa-user-cog"></i> Ubah Akun Utama
            </h3>
            <button type="button" class="cyberpunk-modal-close" onclick="closeEditAkunModal({{ $user->id }})">&times;</button>
        </div>
        
        <form action="{{ route('admin.profile-lengkap.update-akun', $user->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="cyberpunk-modal-body">
                <div style="margin-bottom: 1.25rem;">
                    <label class="cyberpunk-info-label" style="margin-bottom: 0.5rem; display:block;">Nama Pengguna (Akun)</label>
                    <input type="text" name="name" value="{{ $user->name }}" class="cyberpunk-search-field" style="width: 100%; padding-left: 10px;" required>
                </div>
                <div style="margin-bottom: 1rem;">
                    <label class="cyberpunk-info-label" style="margin-bottom: 0.5rem; display:block;">Alamat Email Utama</label>
                    <input type="email" name="email" value="{{ $user->email }}" class="cyberpunk-search-field" style="width: 100%; padding-left: 10px;" required>
                </div>
            </div>
            <div class="cyberpunk-modal-footer">
                <button type="button" class="cyberpunk-btn cyberpunk-btn-secondary" onclick="closeEditAkunModal({{ $user->id }})">Batal</button>
                <button type="submit" class="cyberpunk-btn cyberpunk-btn-primary" onclick="return confirm('Simpan perubahan nama & email akun ini?');">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
{{-- Modal Edit PDDIKTI (Lengkap Semua Kolom) --}}
<div class="cyberpunk-modal pddikti-modal-container" id="editPddiktiModal-{{ $user->id }}" style="display: none; position: fixed; inset: 0; z-index: 999999; background: rgba(0,0,0,0.6); overflow-y: auto;">
    
    <div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px;">
        
        <div class="cyberpunk-modal-content" style="position: relative; width: 100%; max-width: 1000px; background: #ffffff; border-radius: 16px; box-shadow: 0 10px 50px rgba(0,0,0,0.5); border: none; overflow: hidden;">
            
            <!-- HEADER -->
            <div style="padding: 1.5rem; border-bottom: 1px solid #E8ECF1; display: flex; justify-content: space-between; align-items: center; background: #ffffff;">
                <h3 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: #1A2332;">
                    Edit Data PDDIKTI: {{ $user->biodata->nama_lengkap ?? $user->name }}
                </h3>
                <button type="button" onclick="closeEditPddiktiModal({{ $user->id }})" style="background: none; border: none; font-size: 1.5rem; color: #8492A6; cursor: pointer;">&times;</button>
            </div>
            
            <!-- FORM -->
            <form action="{{ route('admin.profile-lengkap.update-pddikti', $user->id) }}" method="POST" style="margin: 0;">
                @csrf
                @method('PUT')
                
                <!-- BODY SCROLLABLE -->
                <div style="padding: 20px 30px; max-height: 70vh; overflow-y: auto; background: #F8FAFC;">
                    @php $pddikti = $user->dataMahasiswaFinal; @endphp

                    <!-- 1. IDENTITAS PRIBADI -->
                    <h4 style="color: #00D4AA; border-bottom: 2px dashed #E8ECF1; padding-bottom: 8px; margin-bottom: 15px; font-weight: 600; margin-top: 0;">1. Identitas Pribadi</h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px;">
                        <div>
                            <label style="font-size: 0.75rem; font-weight: 600; color: #8492A6; margin-bottom: 5px; display: block;">NAMA MAHASISWA</label>
                            <input type="text" name="nama_mahasiswa" value="{{ $pddikti->nama_mahasiswa ?? '' }}" class="form-control" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                        </div>
                        <div>
                            <label style="font-size: 0.75rem; font-weight: 600; color: #8492A6; margin-bottom: 5px; display: block;">EMAIL MAHASISWA</label>
                            <input type="email" name="email" value="{{ $pddikti->email ?? $user->email }}" class="form-control" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;" required>
                        </div>
                        <div>
                            <label style="font-size: 0.75rem; font-weight: 600; color: #8492A6; margin-bottom: 5px; display: block;">NIK</label>
                            <input type="text" name="nik" value="{{ $pddikti->nik ?? '' }}" class="form-control" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                        </div>
                        <div>
                            <label style="font-size: 0.75rem; font-weight: 600; color: #8492A6; margin-bottom: 5px; display: block;">NISN</label>
                            <input type="text" name="nisn" value="{{ $pddikti->nisn ?? '' }}" class="form-control" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                        </div>
                        <div>
                            <label style="font-size: 0.75rem; font-weight: 600; color: #8492A6; margin-bottom: 5px; display: block;">JENIS KELAMIN</label>
                            <select name="jenis_kelamin" class="form-control" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                                <option value="L" {{ ($pddikti->jenis_kelamin ?? '') == 'L' ? 'selected' : '' }}>Laki-Laki</option>
                                <option value="P" {{ ($pddikti->jenis_kelamin ?? '') == 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size: 0.75rem; font-weight: 600; color: #8492A6; margin-bottom: 5px; display: block;">TEMPAT LAHIR</label>
                            <input type="text" name="tempat_lahir" value="{{ $pddikti->tempat_lahir ?? '' }}" class="form-control" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                        </div>
                        <div>
                            <label style="font-size: 0.75rem; font-weight: 600; color: #8492A6; margin-bottom: 5px; display: block;">TANGGAL LAHIR</label>
                            <input type="date" name="tanggal_lahir" value="{{ $pddikti->tanggal_lahir ?? '' }}" class="form-control" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                        </div>
                        <div>
                            <label style="font-size: 0.75rem; font-weight: 600; color: #8492A6; margin-bottom: 5px; display: block;">KEWARGANEGARAAN</label>
                            <input type="text" name="kewarganegaraan" value="{{ $pddikti->kewarganegaraan ?? 'ID' }}" class="form-control" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                        </div>
                        <div>
                            <label style="font-size: 0.75rem; font-weight: 600; color: #8492A6; margin-bottom: 5px; display: block;">ID AGAMA</label>
                            <input type="text" name="id_agama" value="{{ $pddikti->id_agama ?? '' }}" class="form-control" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                        </div>
                        <div>
                            <label style="font-size: 0.75rem; font-weight: 600; color: #8492A6; margin-bottom: 5px; display: block;">ID KEBUTUHAN KHUSUS (MHS)</label>
                            <input type="text" name="id_kebutuhan_khusus_mahasiswa" value="{{ $pddikti->id_kebutuhan_khusus_mahasiswa ?? '' }}" class="form-control" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                        </div>
                    </div>

                    <!-- 2. KONTAK & DOMISILI -->
                    <h4 style="color: #00D4AA; border-bottom: 2px dashed #E8ECF1; padding-bottom: 8px; margin-bottom: 15px; font-weight: 600;">2. Kontak & Domisili</h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px;">
                        <div>
                            <label style="font-size: 0.75rem; font-weight: 600; color: #8492A6; margin-bottom: 5px; display: block;">EMAIL</label>
                            <input type="email" name="email" value="{{ $pddikti->email ?? '' }}" class="form-control" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                        </div>
                        <div>
                            <label style="font-size: 0.75rem; font-weight: 600; color: #8492A6; margin-bottom: 5px; display: block;">HANDPHONE</label>
                            <input type="text" name="handphone" value="{{ $pddikti->handphone ?? '' }}" class="form-control" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                        </div>
                        <div>
                            <label style="font-size: 0.75rem; font-weight: 600; color: #8492A6; margin-bottom: 5px; display: block;">TELEPON RUMAH</label>
                            <input type="text" name="telepon" value="{{ $pddikti->telepon ?? '' }}" class="form-control" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                        </div>
                        <div style="grid-column: span 2;">
                            <label style="font-size: 0.75rem; font-weight: 600; color: #8492A6; margin-bottom: 5px; display: block;">JALAN</label>
                            <input type="text" name="jalan" value="{{ $pddikti->jalan ?? '' }}" class="form-control" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                        </div>
                        <div>
                            <label style="font-size: 0.75rem; font-weight: 600; color: #8492A6; margin-bottom: 5px; display: block;">RT / RW</label>
                            <div style="display: flex; gap: 10px;">
                                <input type="text" name="rt" value="{{ $pddikti->rt ?? '' }}" class="form-control" style="width: 50%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;" placeholder="RT">
                                <input type="text" name="rw" value="{{ $pddikti->rw ?? '' }}" class="form-control" style="width: 50%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;" placeholder="RW">
                            </div>
                        </div>
                        <div>
                            <label style="font-size: 0.75rem; font-weight: 600; color: #8492A6; margin-bottom: 5px; display: block;">DUSUN</label>
                            <input type="text" name="dusun" value="{{ $pddikti->dusun ?? '' }}" class="form-control" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                        </div>
                        <div>
                            <label style="font-size: 0.75rem; font-weight: 600; color: #8492A6; margin-bottom: 5px; display: block;">KELURAHAN</label>
                            <input type="text" name="kelurahan" value="{{ $pddikti->kelurahan ?? '' }}" class="form-control" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                        </div>
                        <div>
                            <label style="font-size: 0.75rem; font-weight: 600; color: #8492A6; margin-bottom: 5px; display: block;">ID PROVINSI</label>
                            <input type="text" name="id_provinsi" value="{{ $pddikti->id_provinsi ?? '' }}" class="form-control" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                        </div>
                        <div>
                            <label style="font-size: 0.75rem; font-weight: 600; color: #8492A6; margin-bottom: 5px; display: block;">ID KABUPATEN</label>
                            <input type="text" name="id_kabupaten" value="{{ $pddikti->id_kabupaten ?? '' }}" class="form-control" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                        </div>
                        <div>
                            <label style="font-size: 0.75rem; font-weight: 600; color: #8492A6; margin-bottom: 5px; display: block;">ID KECAMATAN</label>
                            <input type="text" name="id_kecamatan" value="{{ $pddikti->id_kecamatan ?? '' }}" class="form-control" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                        </div>
                        <div>
                            <label style="font-size: 0.75rem; font-weight: 600; color: #8492A6; margin-bottom: 5px; display: block;">KODE POS</label>
                            <input type="text" name="kode_pos" value="{{ $pddikti->kode_pos ?? '' }}" class="form-control" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                        </div>
                        <div>
                            <label style="font-size: 0.75rem; font-weight: 600; color: #8492A6; margin-bottom: 5px; display: block;">ID JENIS TINGGAL</label>
                            <input type="text" name="id_jenis_tinggal" value="{{ $pddikti->id_jenis_tinggal ?? '' }}" class="form-control" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                        </div>
                        <div>
                            <label style="font-size: 0.75rem; font-weight: 600; color: #8492A6; margin-bottom: 5px; display: block;">ID ALAT TRANSPORTASI</label>
                            <input type="text" name="id_alat_transportasi" value="{{ $pddikti->id_alat_transportasi ?? '' }}" class="form-control" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                        </div>
                    </div>

                    <!-- 3. DATA ORANG TUA (AYAH, IBU, WALI) -->
                    <h4 style="color: #00D4AA; border-bottom: 2px dashed #E8ECF1; padding-bottom: 8px; margin-bottom: 15px; font-weight: 600;">3. Data Orang Tua & Wali</h4>
                    <div style="background: #ffffff; border: 1px solid #E8ECF1; border-radius: 12px; padding: 20px; margin-bottom: 25px;">
                        
                        <!-- AYAH -->
                        <div style="margin-bottom: 10px;"><strong style="color: #5B7FFF; font-size: 1rem;">Data Ayah</strong></div>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px dashed #E8ECF1;">
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 600; color: #8492A6; margin-bottom: 5px; display: block;">NAMA AYAH</label>
                                <input type="text" name="nama_ayah" value="{{ $pddikti->nama_ayah ?? '' }}" class="form-control" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                            </div>
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 600; color: #8492A6; margin-bottom: 5px; display: block;">NIK AYAH</label>
                                <input type="text" name="nik_ayah" value="{{ $pddikti->nik_ayah ?? '' }}" class="form-control" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                            </div>
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 600; color: #8492A6; margin-bottom: 5px; display: block;">TANGGAL LAHIR AYAH</label>
                                <input type="date" name="tanggal_lahir_ayah" value="{{ $pddikti->tanggal_lahir_ayah ?? '' }}" class="form-control" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                            </div>
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 600; color: #8492A6; margin-bottom: 5px; display: block;">ID PENDIDIKAN AYAH</label>
                                <input type="text" name="id_jenjang_pendidikan_ayah" value="{{ $pddikti->id_jenjang_pendidikan_ayah ?? '' }}" class="form-control" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                            </div>
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 600; color: #8492A6; margin-bottom: 5px; display: block;">ID PEKERJAAN AYAH</label>
                                <input type="text" name="id_pekerjaan_ayah" value="{{ $pddikti->id_pekerjaan_ayah ?? '' }}" class="form-control" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                            </div>
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 600; color: #8492A6; margin-bottom: 5px; display: block;">ID PENGHASILAN AYAH</label>
                                <input type="text" name="id_penghasilan_ayah" value="{{ $pddikti->id_penghasilan_ayah ?? '' }}" class="form-control" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                            </div>
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 600; color: #8492A6; margin-bottom: 5px; display: block;">ID KEB. KHUSUS AYAH</label>
                                <input type="text" name="id_kebutuhan_khusus_ayah" value="{{ $pddikti->id_kebutuhan_khusus_ayah ?? '' }}" class="form-control" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                            </div>
                        </div>

                        <!-- IBU -->
                        <div style="margin-bottom: 10px;"><strong style="color: #5B7FFF; font-size: 1rem;">Data Ibu Kandung</strong></div>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px dashed #E8ECF1;">
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 600; color: #8492A6; margin-bottom: 5px; display: block;">NAMA IBU KANDUNG</label>
                                <input type="text" name="nama_ibu_kandung" value="{{ $pddikti->nama_ibu_kandung ?? '' }}" class="form-control" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                            </div>
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 600; color: #8492A6; margin-bottom: 5px; display: block;">NIK IBU</label>
                                <input type="text" name="nik_ibu" value="{{ $pddikti->nik_ibu ?? '' }}" class="form-control" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                            </div>
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 600; color: #8492A6; margin-bottom: 5px; display: block;">TANGGAL LAHIR IBU</label>
                                <input type="date" name="tanggal_lahir_ibu" value="{{ $pddikti->tanggal_lahir_ibu ?? '' }}" class="form-control" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                            </div>
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 600; color: #8492A6; margin-bottom: 5px; display: block;">ID PENDIDIKAN IBU</label>
                                <input type="text" name="id_jenjang_pendidikan_ibu" value="{{ $pddikti->id_jenjang_pendidikan_ibu ?? '' }}" class="form-control" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                            </div>
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 600; color: #8492A6; margin-bottom: 5px; display: block;">ID PEKERJAAN IBU</label>
                                <input type="text" name="id_pekerjaan_ibu" value="{{ $pddikti->id_pekerjaan_ibu ?? '' }}" class="form-control" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                            </div>
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 600; color: #8492A6; margin-bottom: 5px; display: block;">ID PENGHASILAN IBU</label>
                                <input type="text" name="id_penghasilan_ibu" value="{{ $pddikti->id_penghasilan_ibu ?? '' }}" class="form-control" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                            </div>
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 600; color: #8492A6; margin-bottom: 5px; display: block;">ID KEB. KHUSUS IBU</label>
                                <input type="text" name="id_kebutuhan_khusus_ibu" value="{{ $pddikti->id_kebutuhan_khusus_ibu ?? '' }}" class="form-control" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                            </div>
                        </div>

                        <!-- WALI -->
                        <div style="margin-bottom: 10px;"><strong style="color: #5B7FFF; font-size: 1rem;">Data Wali (Opsional)</strong></div>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 600; color: #8492A6; margin-bottom: 5px; display: block;">NAMA WALI</label>
                                <input type="text" name="nama_wali" value="{{ $pddikti->nama_wali ?? '' }}" class="form-control" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                            </div>
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 600; color: #8492A6; margin-bottom: 5px; display: block;">NIK WALI</label>
                                <input type="text" name="nik_wali" value="{{ $pddikti->nik_wali ?? '' }}" class="form-control" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                            </div>
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 600; color: #8492A6; margin-bottom: 5px; display: block;">TANGGAL LAHIR WALI</label>
                                <input type="date" name="tanggal_lahir_wali" value="{{ $pddikti->tanggal_lahir_wali ?? '' }}" class="form-control" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                            </div>
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 600; color: #8492A6; margin-bottom: 5px; display: block;">ID PENDIDIKAN WALI</label>
                                <input type="text" name="id_jenjang_pendidikan_wali" value="{{ $pddikti->id_jenjang_pendidikan_wali ?? '' }}" class="form-control" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                            </div>
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 600; color: #8492A6; margin-bottom: 5px; display: block;">ID PEKERJAAN WALI</label>
                                <input type="text" name="id_pekerjaan_wali" value="{{ $pddikti->id_pekerjaan_wali ?? '' }}" class="form-control" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                            </div>
                            <div>
                                <label style="font-size: 0.75rem; font-weight: 600; color: #8492A6; margin-bottom: 5px; display: block;">ID PENGHASILAN WALI</label>
                                <input type="text" name="id_penghasilan_wali" value="{{ $pddikti->id_penghasilan_wali ?? '' }}" class="form-control" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                            </div>
                        </div>
                    </div>

                    <!-- 4. DATA TAMBAHAN -->
                    <h4 style="color: #00D4AA; border-bottom: 2px dashed #E8ECF1; padding-bottom: 8px; margin-bottom: 15px; font-weight: 600;">4. Data Tambahan Lainnya</h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; padding-bottom: 20px;">
                        <div>
                            <label style="font-size: 0.75rem; font-weight: 600; color: #8492A6; margin-bottom: 5px; display: block;">PENERIMA KPS?</label>
                            <select name="penerima_kps" class="form-control" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                                <option value="0" {{ ($pddikti->penerima_kps ?? 0) == 0 ? 'selected' : '' }}>Tidak</option>
                                <option value="1" {{ ($pddikti->penerima_kps ?? 0) == 1 ? 'selected' : '' }}>Ya</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size: 0.75rem; font-weight: 600; color: #8492A6; margin-bottom: 5px; display: block;">NOMOR KPS</label>
                            <input type="text" name="no_kps" value="{{ $pddikti->no_kps ?? '' }}" class="form-control" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                        </div>
                        <div>
                            <label style="font-size: 0.75rem; font-weight: 600; color: #8492A6; margin-bottom: 5px; display: block;">NPWP</label>
                            <input type="text" name="npwp" value="{{ $pddikti->npwp ?? '' }}" class="form-control" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                        </div>
                    </div>
                </div>
                
                <!-- FOOTER -->
                <div style="padding: 1rem 1.5rem; background: #ffffff; border-top: 1px solid #E8ECF1; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="closeEditPddiktiModal({{ $user->id }})" style="padding: 0.6rem 1.5rem; background: white; border: 2px solid #E8ECF1; border-radius: 8px; color: #1A2332; font-weight: 600; cursor: pointer;">Batal</button>
                    <button type="submit" onclick="return confirm('Simpan perubahan data PDDIKTI?');" style="padding: 0.6rem 1.5rem; background: linear-gradient(135deg, #5B7FFF 0%, #667EEA 100%); border: none; border-radius: 8px; color: white; font-weight: 600; cursor: pointer; box-shadow: 0 4px 12px rgba(91, 127, 255, 0.3);">Simpan Data PDDIKTI</button>
                </div>
            </form>
        </div>
    </div>
</div>
            <div class="cyberpunk-modal" id="editProdiModal-{{ $user->id }}" style="display: none;">
                <div class="cyberpunk-modal-overlay"></div>
                <div class="cyberpunk-modal-content">
                    <div class="cyberpunk-modal-header">
                        <h3 class="cyberpunk-modal-title">
                            <svg class="cyberpunk-modal-icon" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            Ubah Program Studi
                        </h3>
                        <button type="button" class="cyberpunk-modal-close" onclick="closeEditProdiModal({{ $user->id }})">&times;</button>
                    </div>
                    
                    <form action="{{ route('admin.profile-lengkap.update-prodi', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="cyberpunk-modal-body">
                            <div class="cyberpunk-alert cyberpunk-alert-warning" style="margin-bottom: 1rem;">
                                <p class="cyberpunk-alert-text">Perubahan prodi akan mengubah data pada <strong>Master Ereg</strong>.</p>
                            </div>
                            <div style="margin-bottom: 1rem;">
                                <label class="cyberpunk-info-label" style="margin-bottom: 0.5rem; display:block;">Pilihan Jurusan 1</label>
                                <select name="jurusan1" class="cyberpunk-search-field" style="width: 100%;" required>
                                    <option value="">-- Pilih Jurusan 1 --</option>
                                    @foreach($programStudi as $prodi)
                                        <option value="{{ $prodi->kode_prodi }}" {{ ($user->masterEreg->jurusan1 ?? '') == $prodi->kode_prodi ? 'selected' : '' }}>
                                            {{ $prodi->kode_prodi }} - {{ $prodi->nama_prodi }} {{ $prodi->jenjang ? '('.$prodi->jenjang.')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div style="margin-bottom: 1rem;">
                                <label class="cyberpunk-info-label" style="margin-bottom: 0.5rem; display:block;">Pilihan Jurusan 2</label>
                                <select name="jurusan2" class="cyberpunk-search-field" style="width: 100%;">
                                    <option value="">-- Pilih Jurusan 2 --</option>
                                    @foreach($programStudi as $prodi)
                                        <option value="{{ $prodi->kode_prodi }}" {{ ($user->masterEreg->jurusan2 ?? '') == $prodi->kode_prodi ? 'selected' : '' }}>
                                            {{ $prodi->kode_prodi }} - {{ $prodi->nama_prodi }} {{ $prodi->jenjang ? '('.$prodi->jenjang.')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="cyberpunk-modal-footer">
                            <button type="button" class="cyberpunk-btn cyberpunk-btn-secondary" onclick="closeEditProdiModal({{ $user->id }})">Batal</button>
                            <button type="submit" class="cyberpunk-btn cyberpunk-btn-primary">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="cyberpunk-empty-state">
        <svg class="cyberpunk-empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
        </svg>
        <p class="cyberpunk-empty-text">Tidak ada data mahasiswa baru</p>
    </div>
    @endforelse
</div>

 {{-- GANTI KODE INI --}}
@if($users->hasPages())
<div class="cyberpunk-pagination">
    {{-- Informasi Halaman dan Total Data --}}
    <div class="cyberpunk-pagination-info">
        Menampilkan 
        <strong>{{ $users->firstItem() ?: 0 }}</strong> 
        sampai 
        <strong>{{ $users->lastItem() ?: 0 }}</strong> 
        dari 
        <strong>{{ $users->total() }}</strong> 
        data
        @if($users->total() > 0)
        (Halaman <strong>{{ $users->currentPage() }}</strong> dari <strong>{{ $users->lastPage() }}</strong>)
        @endif
    </div>
    
    {{-- Navigasi Halaman --}}
    <div class="cyberpunk-pagination-links">
        {{-- Tombol Previous --}}
        @if($users->onFirstPage())
            <span class="cyberpunk-pagination-link disabled">
                &laquo; Prev
            </span>
        @else
            <a href="{{ $users->previousPageUrl() }}" class="cyberpunk-pagination-link">
                &laquo; Prev
            </a>
        @endif

        {{-- Tombol Halaman --}}
        @php
            // Custom pagination logic untuk tampilan yang lebih baik
            $current = $users->currentPage();
            $last = $users->lastPage();
            $start = max(1, $current - 2);
            $end = min($last, $current + 2);
            
            // Jika di awal, tampilkan lebih banyak halaman setelahnya
            if ($current <= 3) {
                $end = min($last, 5);
            }
            
            // Jika di akhir, tampilkan lebih banyak halaman sebelumnya
            if ($current >= $last - 2) {
                $start = max(1, $last - 4);
            }
        @endphp

        {{-- Halaman pertama jika tidak termasuk dalam range --}}
        @if($start > 1)
            <a href="{{ $users->url(1) }}" class="cyberpunk-pagination-link">
                1
            </a>
            @if($start > 2)
                <span class="cyberpunk-pagination-ellipsis">...</span>
            @endif
        @endif

        {{-- Range halaman --}}
        @for($page = $start; $page <= $end; $page++)
            @if($page == $current)
                <span class="cyberpunk-pagination-link active">
                    {{ $page }}
                </span>
            @else
                <a href="{{ $users->url($page) }}" class="cyberpunk-pagination-link">
                    {{ $page }}
                </a>
            @endif
        @endfor

        {{-- Halaman terakhir jika tidak termasuk dalam range --}}
        @if($end < $last)
            @if($end < $last - 1)
                <span class="cyberpunk-pagination-ellipsis">...</span>
            @endif
            <a href="{{ $users->url($last) }}" class="cyberpunk-pagination-link">
                {{ $last }}
            </a>
        @endif

        {{-- Tombol Next --}}
        @if($users->hasMorePages())
            <a href="{{ $users->nextPageUrl() }}" class="cyberpunk-pagination-link">
                Next &raquo;
            </a>
        @else
            <span class="cyberpunk-pagination-link disabled">
                Next &raquo;
            </span>
        @endif
    </div>
    
    {{-- Opsi Jumlah Data per Halaman --}}
    <form method="GET" action="{{ url()->current() }}" class="per-page-form" style="margin-top: 1rem;">
        <input type="hidden" name="page" value="1">
        <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; justify-content: center;">
            <label for="per_page" style="color: var(--cyberpunk-text-secondary); font-size: 0.9rem;">
                Tampilkan:
            </label>
            <select name="per_page" id="per_page" 
                    onchange="this.form.submit()"
                    style="background: rgba(40, 40, 60, 0.6); 
                           border: 1px solid var(--cyberpunk-border); 
                           border-radius: 4px; 
                           color: var(--cyberpunk-text); 
                           padding: 6px 12px;
                           font-size: 0.9rem;">
                <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                <option value="20" {{ request('per_page') == 20 || !request('per_page') ? 'selected' : '' }}>20</option>
                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>Semua</option>
            </select>
            <span style="color: var(--cyberpunk-text-secondary); font-size: 0.85rem;">
                per halaman
            </span>
        </div>
    </form>
</div>
@else
{{-- Jika hanya ada 1 halaman --}}
<div class="cyberpunk-pagination">
    <div class="cyberpunk-pagination-info">
        Total <strong>{{ $users->total() }}</strong> data ditemukan
        @if($users->total() > 0)
        <div style="margin-top: 0.5rem; font-size: 0.85rem; color: var(--cyberpunk-success);">
            Semua data ditampilkan dalam 1 halaman
        </div>
        @endif
    </div>
</div>
@endif
</div>

<div class="cyberpunk-modal" id="deleteModal" style="display: none;">
    <div class="cyberpunk-modal-overlay"></div>
    <div class="cyberpunk-modal-content">
        <div class="cyberpunk-modal-header">
            <h3 class="cyberpunk-modal-title">
                <svg class="cyberpunk-modal-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 9V11M12 15H12.01M5.07183 19H18.9282C20.4678 19 21.4301 17.3333 20.6603 16L13.7321 4C12.9623 2.66667 11.0377 2.66667 10.2679 4L3.33975 16C2.56995 17.3333 3.53223 19 5.07183 19Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                Konfirmasi Hapus User
            </h3>
            <button type="button" class="cyberpunk-modal-close" onclick="closeDeleteModal()">
                &times;
            </button>
        </div>
        <div class="cyberpunk-modal-body">
            <div class="cyberpunk-alert cyberpunk-alert-danger">
                <h4 class="cyberpunk-alert-title">PERHATIAN!</h4>
                <p class="cyberpunk-alert-text">
                    Anda akan menghapus user berikut:
                </p>
                <div class="cyberpunk-alert-details">
                    <p><strong>Nama:</strong> <span id="deleteUserName"></span></p>
                    <p><strong>VA Number:</strong> <span id="deleteUserVA"></span></p>
                </div>
                <div class="cyberpunk-warning-box">
                    <h5>⚠️ Tindakan ini akan:</h5>
                    <ul>
                        <li>Menghapus user dari sistem</li>
                        <li>Nonaktifkan Virtual Account</li>
                        <li>Menghapus data terkait</li>
                        <li><strong>Tidak dapat dibatalkan!</strong></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="cyberpunk-modal-footer">
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <button type="button" class="cyberpunk-btn cyberpunk-btn-secondary" onclick="closeDeleteModal()">
                    Batal
                </button>
                <button type="submit" class="cyberpunk-btn cyberpunk-btn-danger">
                    Hapus User
                </button>
            </form>
        </div>
    </div>
</div>

<script>
	function openEditPddiktiModal(id) {
        const modal = document.getElementById('editPddiktiModal-' + id);
        if (modal) {
            modal.style.display = 'flex'; // Gunakan flex jika kamu pakai model cyberpunk yang flexbox centernya
            document.body.style.overflow = 'hidden';
        }
    }

    function closeEditPddiktiModal(id) {
        const modal = document.getElementById('editPddiktiModal-' + id);
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    }
    function openEditProdiModal(userId) {
        const modal = document.getElementById(`editProdiModal-${userId}`);
        if (modal) {
            modal.style.display = 'block';
        }
    }

    // Fungsi Tutup Modal Edit Prodi
    function closeEditProdiModal(userId) {
        const modal = document.getElementById(`editProdiModal-${userId}`);
        if (modal) {
            modal.style.display = 'none';
        }
    }

    // Close modal on overlay click (Update listener yang sudah ada)
    window.addEventListener('click', function(event) {
        // Cek jika yang diklik adalah overlay dari modal edit prodi
        if (event.target.classList.contains('cyberpunk-modal-overlay')) {
            // Cari parent modalnya
            const modal = event.target.closest('.cyberpunk-modal');
            if (modal) {
                modal.style.display = 'none';
            }
        }
    });

    function toggleDetails(userId) {
        const details = document.getElementById(`details-${userId}`);
        const icon = document.getElementById(`icon-${userId}`);

        if (details.style.display === 'none' || details.style.display === '') {
            details.style.display = 'block';
            icon.classList.add('rotate-180');
        } else {
            details.style.display = 'none';
            icon.classList.remove('rotate-180');
        }
    }

    // Tab functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize all details panels as hidden
        document.querySelectorAll('.cyberpunk-user-details-panel').forEach(panel => {
            panel.style.display = 'none';
        });

        // Tab switching
        document.querySelectorAll('.cyberpunk-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                const userId = this.dataset.tab.split('-')[1];
                const tabId = this.dataset.tab;

                // Remove active class from all tabs
                document.querySelectorAll(`[data-tab^="personal-${userId}"], [data-tab^="academic-${userId}"], [data-tab^="pddikti-${userId}"], [data-tab^="files-${userId}"]`).forEach(t => {
                    t.classList.remove('active');
                });

                // Hide all tab contents
                document.querySelectorAll(`.cyberpunk-tab-content`).forEach(content => {
                    if (content.id.endsWith(`-${userId}`)) {
                        content.classList.remove('active');
                    }
                });

                // Activate clicked tab
                this.classList.add('active');
                document.getElementById(tabId).classList.add('active');
            });
        });

        // Bulk delete functionality
        const userCheckboxes = document.querySelectorAll('.user-checkbox');
        const selectAllCheckbox = document.getElementById('selectAllCheckbox');
        const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
        const bulkActions = document.getElementById('bulkActions');

        if (userCheckboxes.length > 0) {
            bulkActions.style.display = 'flex';
        }

        // Select all
        selectAllCheckbox.addEventListener('change', function() {
            userCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActions();
        });

        // Individual checkbox
        userCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateBulkActions);
        });

        // Bulk delete button
        bulkDeleteBtn.addEventListener('click', function() {
    const selected = document.querySelectorAll('.user-checkbox:checked');
    if (selected.length === 0) {
        alert('Pilih mahasiswa yang ingin dihapus terlebih dahulu!');
        return;
    }

    const ids = Array.from(selected).map(cb => cb.value);
    showBulkDeleteModal(ids);
});

        function updateBulkActions() {
            const selectedCount = document.querySelectorAll('.user-checkbox:checked').length;
            if (selectedCount > 0) {
                bulkDeleteBtn.innerHTML = `
                <svg class="cyberpunk-btn-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M19 7L18.1327 19.1425C18.0579 20.1891 17.187 21 16.1378 21H7.86224C6.81296 21 5.94208 20.1891 5.86732 19.1425L5 7M10 11V17M14 11V17M15 7V4C15 3.44772 14.5523 3 14 3H10C9.44772 3 9 3.44772 9 4V7M4 7H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Hapus ${selectedCount} Terpilih
            `;
            } else {
                bulkDeleteBtn.innerHTML = `
                <svg class="cyberpunk-btn-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M19 7L18.1327 19.1425C18.0579 20.1891 17.187 21 16.1378 21H7.86224C6.81296 21 5.94208 20.1891 5.86732 19.1425L5 7M10 11V17M14 11V17M15 7V4C15 3.44772 14.5523 3 14 3H10C9.44772 3 9 3.44772 9 4V7M4 7H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Hapus Terpilih
            `;
            }

            // Update select all checkbox state
            const allChecked = selectedCount === userCheckboxes.length;
            const someChecked = selectedCount > 0 && !allChecked;

            selectAllCheckbox.checked = allChecked;
            selectAllCheckbox.indeterminate = someChecked;
        }
    });

    function showDeleteModal(name, va, actionUrl) {
        document.getElementById('deleteUserName').textContent = name;
        document.getElementById('deleteUserVA').textContent = va;
        document.getElementById('deleteForm').action = actionUrl;
        document.getElementById('deleteModal').style.display = 'block';
    }
function openEditAkunModal(userId) {
    const modal = document.getElementById(`editAkunModal-${userId}`);
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeEditAkunModal(userId) {
    const modal = document.getElementById(`editAkunModal-${userId}`);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}
    function showBulkDeleteModal(userIds) {
    // Tampilkan informasi di modal
    document.getElementById('deleteUserName').textContent = userIds.length + ' User Terpilih';
    document.getElementById('deleteUserVA').textContent = 'Penghapusan Massal';

    const form = document.getElementById('deleteForm');
    
    // PERBAIKAN: Gunakan URL lengkap ke route bulk-delete
    form.action = "{{ route('admin.profile-lengkap.bulk-destroy') }}"; 

    // Pastikan ada hidden input untuk menampung IDs
    let inputIds = form.querySelector('input[name="user_ids"]');
    if (!inputIds) {
        inputIds = document.createElement('input');
        inputIds.type = 'hidden';
        inputIds.name = 'user_ids';
        form.appendChild(inputIds);
    }
    inputIds.value = JSON.stringify(userIds);

    // Pastikan Method Form adalah POST (karena di route kita pakai POST)
    // Jika modal Anda secara default menggunakan @method('DELETE'), hapus sementara via JS:
    let methodInput = form.querySelector('input[name="_method"]');
    if (methodInput) {
        methodInput.value = 'POST'; 
    }

    document.getElementById('deleteModal').style.display = 'block';
}

    function closeDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
    }

    // Close modal on overlay click
    window.addEventListener('click', function(event) {
        if (event.target === document.querySelector('.cyberpunk-modal-overlay')) {
            closeDeleteModal();
        }
    });
</script>
<style>
    /* =========================================
       1. VARIABLES & RESET
       ========================================= */
    :root {
        --cyberpunk-primary: #465fff;
        --cyberpunk-primary-hover: #3641f5;
        --cyberpunk-secondary: #7a5af8;
        --cyberpunk-accent: #ee46bc;
        --cyberpunk-success: #12b76a;
        --cyberpunk-warning: #f79009;
        --cyberpunk-error: #f04438;
        --cyberpunk-gray: #667085;
        --cyberpunk-dark: #101828;
        --cyberpunk-light: #f9fafb;
        --cyberpunk-border: #e5e7eb;
        --cyberpunk-border-focus: rgba(70, 95, 255, 0.4);
        --cyberpunk-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        --cyberpunk-glow: 0 0 0 4px rgba(70, 95, 255, 0.15);
    }

    * {
        box-sizing: border-box;
    }

    /* =========================================
       2. CONTAINER & LAYOUT
       ========================================= */
    .cyberpunk-container {
        padding: 1.5rem;
        background: #ffffff;
        background: linear-gradient(145deg, #ffffff 0%, #f8faff 100%);
        border-radius: 1rem;
        border: 1px solid var(--cyberpunk-border);
        box-shadow: var(--cyberpunk-shadow);
        position: relative;
        overflow: hidden;
    }
/* Tambahan untuk Full Width */
    .cyberpunk-container-fluid {
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 1.5rem !important; /* Padding dalam tetap ada agar konten tidak nempel border */
        box-sizing: border-box;
    }

    /* Pastikan parent dari konten admin juga full width jika diperlukan */
    /* Sesuaikan selector ini dengan class wrapper utama di layout admin Anda jika masih ada jarak */
    .content-wrapper, .main-content {
        padding: 0 !important;
        margin: 0 !important;
        width: 100% !important;
    }
    /* Decorative top border line for cyberpunk feel */
    .cyberpunk-container::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--cyberpunk-primary), var(--cyberpunk-accent));
    }

    /* =========================================
       3. HEADER & TYPOGRAPHY
       ========================================= */
    .cyberpunk-header {
        margin-bottom: 2rem;
    }

    .cyberpunk-header-content {
        margin-bottom: 1.5rem;
    }

    .cyberpunk-title {
        font-size: 1.75rem;
        font-weight: 800;
        letter-spacing: -0.025em;
        color: var(--cyberpunk-dark);
        margin-bottom: 0.5rem;
    }

    .cyberpunk-subtitle {
        color: var(--cyberpunk-gray);
        font-size: 0.875rem;
        line-height: 1.5;
    }

    .cyberpunk-header-actions {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    @media (min-width: 768px) {
        .cyberpunk-header-actions {
            flex-direction: row;
            align-items: flex-end;
            justify-content: space-between;
        }
    }

    /* =========================================
       4. FORMS & SEARCH (Enhanced Select)
       ========================================= */
    .cyberpunk-search-container {
        flex: 1;
        width: 100%;
    }

    .cyberpunk-search-form {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    @media (min-width: 1024px) {
        .cyberpunk-search-form {
            flex-direction: row;
            align-items: center;
        }
    }

    .cyberpunk-search-inputs {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        flex: 1;
    }

    @media (min-width: 768px) {
        .cyberpunk-search-inputs {
            flex-direction: row;
            flex-wrap: wrap;
        }
    }

    .cyberpunk-search-group {
        position: relative;
        flex: 2;
        min-width: 200px;
    }

    .cyberpunk-search-icon {
        position: absolute;
        left: 0.875rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--cyberpunk-gray);
        width: 18px;
        height: 18px;
        transition: color 0.2s;
    }

    .cyberpunk-search-field:focus + .cyberpunk-search-icon {
        color: var(--cyberpunk-primary);
    }

    .cyberpunk-search-field {
        width: 100%;
        padding: 0.625rem 0.875rem 0.625rem 2.5rem;
        background: white;
        border: 1px solid var(--cyberpunk-border);
        border-radius: 0.5rem;
        font-size: 0.875rem;
        transition: all 0.2s ease;
        color: var(--cyberpunk-dark);
    }

    .cyberpunk-search-field:focus {
        outline: none;
        border-color: var(--cyberpunk-primary);
        box-shadow: var(--cyberpunk-glow);
    }

    /* Custom Styling untuk Select Option */
    .cyberpunk-select {
        appearance: none; /* Hilangkan default arrow browser */
        padding: 0.625rem 2.5rem 0.625rem 0.875rem; /* Kanan dilebarkan untuk icon */
        background-color: white;
        border: 1px solid var(--cyberpunk-border);
        border-radius: 0.5rem;
        font-size: 0.875rem;
        color: var(--cyberpunk-dark);
        cursor: pointer;
        min-width: 140px;
        /* Custom Chevron Icon (SVG encoded) */
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.5rem center;
        background-repeat: no-repeat;
        background-size: 1.5em 1.5em;
        transition: all 0.2s;
    }

    .cyberpunk-select:focus {
        outline: none;
        border-color: var(--cyberpunk-primary);
        box-shadow: var(--cyberpunk-glow);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23465fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    }

    .cyberpunk-search-actions {
        display: flex;
        gap: 0.5rem;
        align-items: stretch;
    }

    /* =========================================
       5. BUTTONS
       ========================================= */
    .cyberpunk-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.625rem 1rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid transparent;
        text-decoration: none;
        line-height: 1.25;
    }

    .cyberpunk-btn:active {
        transform: translateY(1px);
    }

    .cyberpunk-btn-sm {
        padding: 0.375rem 0.75rem;
        font-size: 0.75rem;
    }

    .cyberpunk-btn-primary {
        background: var(--cyberpunk-primary);
        color: white;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    .cyberpunk-btn-primary:hover {
        background: var(--cyberpunk-primary-hover);
        box-shadow: 0 4px 12px rgba(70, 95, 255, 0.25);
    }

    .cyberpunk-btn-success {
        background: var(--cyberpunk-success);
        color: white;
    }
    
    .cyberpunk-btn-success:hover {
        filter: brightness(110%);
    }

    .cyberpunk-btn-danger, .cyberpunk-btn-error {
        background: var(--cyberpunk-error);
        color: white;
    }

    .cyberpunk-btn-danger:hover {
        filter: brightness(110%);
    }

    .cyberpunk-btn-secondary {
        background: white;
        border-color: #d1d5db;
        color: #374151;
    }

    .cyberpunk-btn-secondary:hover {
        background: #f9fafb;
        border-color: #9ca3af;
    }

    .cyberpunk-btn-outline {
        background: transparent;
        border-color: #d1d5db;
        color: #4b5563;
    }

    .cyberpunk-btn-outline:hover {
        background: #f3f4f6;
        color: #111827;
    }

    /* Bulk Actions Area */
    .cyberpunk-header-buttons {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-top: 1rem;
    }

    .cyberpunk-bulk-actions {
        display: flex;
        align-items: center;
        gap: 1rem;
        background: #f9fafb;
        padding: 0.25rem 0.5rem 0.25rem 0.75rem;
        border-radius: 0.5rem;
        border: 1px solid #e5e7eb;
    }

    .cyberpunk-select-all {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.75rem;
        font-weight: 500;
        color: var(--cyberpunk-gray);
    }

    /* =========================================
       6. STATISTICS CARDS
       ========================================= */
    .cyberpunk-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2.5rem;
    }

    .cyberpunk-stat-card {
        background: white;
        border: 1px solid var(--cyberpunk-border);
        border-radius: 0.75rem;
        padding: 1.25rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .cyberpunk-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
        border-color: var(--cyberpunk-border-focus);
    }

    .cyberpunk-stat-icon {
        width: 3rem;
        height: 3rem;
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(70, 95, 255, 0.1);
        color: var(--cyberpunk-primary);
    }

    .cyberpunk-stat-success { background: rgba(18, 183, 106, 0.1); color: var(--cyberpunk-success); }
    .cyberpunk-stat-warning { background: rgba(247, 144, 9, 0.1); color: var(--cyberpunk-warning); }
    .cyberpunk-stat-accent  { background: rgba(238, 70, 188, 0.1); color: var(--cyberpunk-accent); }

    .cyberpunk-stat-content {
        flex: 1;
    }

    .cyberpunk-stat-label {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--cyberpunk-gray);
        margin-bottom: 0.25rem;
    }

    .cyberpunk-stat-value {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--cyberpunk-dark);
        line-height: 1;
    }

    /* =========================================
       7. USER CARDS & LIST
       ========================================= */
    .cyberpunk-users-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .cyberpunk-user-card {
        background: white;
        border: 1px solid var(--cyberpunk-border);
        border-radius: 0.75rem;
        overflow: hidden;
        transition: all 0.2s ease;
    }

    .cyberpunk-user-card:hover {
        border-color: var(--cyberpunk-primary);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .cyberpunk-user-header {
        padding: 1.25rem;
        display: flex;
        align-items: flex-start; /* Changed to flex-start for better multi-line handling */
        gap: 1rem;
        position: relative;
    }

    .cyberpunk-user-main {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        flex: 1;
        min-width: 0; /* Prevents flex item from overflowing */
    }

    .cyberpunk-user-avatar {
        width: 3rem;
        height: 3rem;
        border-radius: 0.75rem;
        background: linear-gradient(135deg, var(--cyberpunk-primary), var(--cyberpunk-secondary));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 1.125rem;
        flex-shrink: 0;
        box-shadow: 0 2px 4px rgba(70, 95, 255, 0.2);
    }

    .cyberpunk-user-info {
        flex: 1;
        min-width: 0;
    }

    .cyberpunk-user-top {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 0.35rem;
    }

    .cyberpunk-user-name {
        font-size: 1rem;
        font-weight: 700;
        color: var(--cyberpunk-dark);
        margin: 0;
    }

    .cyberpunk-user-id {
        font-size: 0.6875rem;
        font-family: monospace;
        color: var(--cyberpunk-gray);
        background: #f3f4f6;
        padding: 0.125rem 0.375rem;
        border-radius: 0.25rem;
        border: 1px solid #e5e7eb;
    }

    .cyberpunk-user-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        font-size: 0.8125rem;
        color: var(--cyberpunk-gray);
    }

    .cyberpunk-meta-item {
        display: flex;
        align-items: center;
        gap: 0.375rem;
    }

    /* =========================================
       8. PROGRESS BAR (New Addition)
       ========================================= */
    .cyberpunk-progress-wrapper {
        margin-top: 0.75rem;
        background: #f8fafc;
        border: 1px solid #f1f5f9;
        padding: 0.5rem;
        border-radius: 0.5rem;
        width: 100%;
        max-width: 350px;
    }

    .cyberpunk-progress-label {
        display: flex;
        justify-content: space-between;
        font-size: 0.6875rem;
        margin-bottom: 0.25rem;
        font-weight: 600;
        color: var(--cyberpunk-dark);
    }

    .cyberpunk-progress-track {
        height: 6px;
        background-color: #e2e8f0;
        border-radius: 3px;
        overflow: hidden;
    }

    .cyberpunk-progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #fcee0a, #f79009); /* Default Warning Color */
        border-radius: 3px;
        transition: width 0.6s ease-out;
    }
    
    .cyberpunk-progress-fill.bg-success {
        background: linear-gradient(90deg, var(--cyberpunk-success), #34d399);
    }

    /* =========================================
       9. BADGES & STATUS
       ========================================= */
    .cyberpunk-user-status {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        align-items: flex-end;
    }

    .cyberpunk-status-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.375rem;
        justify-content: flex-end;
    }

    .cyberpunk-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.625rem;
        border-radius: 9999px;
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.025em;
    }

    .cyberpunk-badge-success { background: #dcfce7; color: #166534; }
    .cyberpunk-badge-warning { background: #fef9c3; color: #854d0e; }
    .cyberpunk-badge-error   { background: #fee2e2; color: #991b1b; }
    .cyberpunk-badge-gray    { background: #f3f4f6; color: #4b5563; }
    .cyberpunk-badge-accent  { background: #fce7f3; color: #9d174d; }
    .cyberpunk-badge-primary { background: #e0e7ff; color: #3730a3; }

    .cyberpunk-farmasi-status {
        margin-top: 0.25rem;
    }

    .cyberpunk-verifikator {
        font-size: 0.6rem;
        opacity: 0.8;
        margin-left: 0.25rem;
        font-weight: 400;
    }

    /* Toggle & Actions */
    .cyberpunk-user-actions {
        display: flex;
        align-items: center;
    }

    .cyberpunk-toggle-btn {
        background: white;
        border: 1px solid #e5e7eb;
        width: 2rem;
        height: 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--cyberpunk-gray);
        cursor: pointer;
        border-radius: 0.5rem;
        transition: all 0.2s;
    }

    .cyberpunk-toggle-btn:hover {
        background: #f3f4f6;
        color: var(--cyberpunk-primary);
        border-color: var(--cyberpunk-primary);
    }

    .cyberpunk-toggle-icon {
        width: 1rem;
        height: 1rem;
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .rotate-180 {
        transform: rotate(180deg);
    }

    /* =========================================
       10. DETAILS PANEL & TABS
       ========================================= */
    .cyberpunk-user-details-panel {
        display: none;
        border-top: 1px solid #e5e7eb;
        background: #f8fafc;
        animation: slideDown 0.2s ease-out;
    }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .cyberpunk-details-tabs {
        display: flex;
        gap: 0.25rem;
        padding: 0.75rem 1.25rem 0;
        border-bottom: 1px solid #e5e7eb;
        overflow-x: auto;
    }

    .cyberpunk-tab {
        padding: 0.75rem 1rem;
        background: transparent;
        border: none;
        border-bottom: 2px solid transparent;
        color: var(--cyberpunk-gray);
        font-size: 0.8125rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        white-space: nowrap;
    }

    .cyberpunk-tab:hover {
        color: var(--cyberpunk-primary);
    }

    .cyberpunk-tab.active {
        color: var(--cyberpunk-primary);
        border-bottom-color: var(--cyberpunk-primary);
    }

    .cyberpunk-tab-contents {
        padding: 1.5rem;
    }

    .cyberpunk-tab-content {
        display: none;
    }
    
    .cyberpunk-tab-content.active {
        display: block;
        animation: fadeIn 0.3s ease;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    /* =========================================
       11. INFO GRID & SECTIONS
       ========================================= */
    .cyberpunk-info-grid {
        display: grid;
        gap: 1.5rem;
    }

    .cyberpunk-info-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
    }

    .cyberpunk-info-label {
        display: block;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--cyberpunk-gray);
        margin-bottom: 0.35rem;
    }

    .cyberpunk-info-value {
        font-size: 0.9375rem;
        color: var(--cyberpunk-dark);
        font-weight: 500;
        line-height: 1.4;
        word-break: break-word;
    }
    
    .cyberpunk-va-number {
        font-family: monospace;
        background: #f3f4f6;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        display: inline-block;
    }

    .cyberpunk-info-section-title {
        font-size: 0.875rem;
        font-weight: 700;
        color: var(--cyberpunk-dark);
        border-bottom: 2px solid #e5e7eb;
        padding-bottom: 0.5rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* Prodi Choices Visual */
    .cyberpunk-prodi-choice {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 1rem;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        border-left: 4px solid var(--cyberpunk-gray);
    }

    .cyberpunk-prodi-primary {
        border-left-color: var(--cyberpunk-primary);
        background: linear-gradient(to right, rgba(70, 95, 255, 0.05), transparent);
    }

    .cyberpunk-prodi-secondary {
        border-left-color: var(--cyberpunk-warning);
    }

    /* =========================================
       12. FILES & PDF PREVIEW
       ========================================= */
    .cyberpunk-files-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1rem;
    }

    .cyberpunk-file-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        padding: 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        transition: transform 0.2s;
    }

    .cyberpunk-file-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--cyberpunk-shadow);
    }
    
    .cyberpunk-pdf-container {
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        overflow: hidden;
        background: #f1f5f9;
        height: 200px; /* Fixed height for preview */
    }
    
    .cyberpunk-pdf-embed {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .cyberpunk-action-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        padding: 1.25rem;
        background: white;
        border-top: 1px solid #e5e7eb;
    }

    /* =========================================
       13. MODALS (With Animation)
       ========================================= */
    .cyberpunk-modal {
        position: fixed;
        z-index: 100;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .cyberpunk-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px); /* Efek Blur Keren */
        z-index: -1;
        animation: fadeIn 0.3s ease;
    }

    .cyberpunk-modal-content {
        background-color: #fefefe;
        margin: auto;
        padding: 0;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        width: 100%;
        max-width: 500px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        position: relative;
        animation: modalPop 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    @keyframes modalPop {
        from { opacity: 0; transform: scale(0.95) translateY(10px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }

    .cyberpunk-modal-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f8fafc;
        border-radius: 1rem 1rem 0 0;
    }

    .cyberpunk-modal-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--cyberpunk-dark);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .cyberpunk-modal-body {
        padding: 1.5rem;
    }

    .cyberpunk-modal-footer {
        padding: 1.25rem 1.5rem;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        background: #f8fafc;
        border-radius: 0 0 1rem 1rem;
    }

    /* =========================================
       14. RESPONSIVE UTILS
       ========================================= */
    .inline { display: inline-block; }

    @media (max-width: 640px) {
        .cyberpunk-header-actions, 
        .cyberpunk-search-form,
        .cyberpunk-user-header {
            flex-direction: column;
            align-items: stretch;
        }

        .cyberpunk-user-status {
            align-items: flex-start;
            margin-top: 0.5rem;
        }

        .cyberpunk-status-badges {
            justify-content: flex-start;
        }

        .cyberpunk-user-actions {
            position: absolute;
            top: 1rem;
            right: 1rem;
        }
        
        .cyberpunk-progress-wrapper {
            max-width: 100%;
        }
    }
    /* =========================================
       16. NEW SEGMENTED PROGRESS BAR
       ========================================= */
    .cyberpunk-steps-container {
        width: 100%;
        max-width: 350px;
        margin-top: 0.75rem;
        background: rgba(243, 244, 246, 0.5); /* Sedikit transparan */
        padding: 0.75rem;
        border-radius: 0.5rem;
        border: 1px solid #e5e7eb;
    }

    .cyberpunk-steps-header {
        display: flex;
        justify-content: space-between;
        font-size: 0.75rem;
        margin-bottom: 0.5rem;
        color: var(--cyberpunk-gray);
    }

    .cyberpunk-steps-percentage {
        font-weight: 800;
        color: var(--cyberpunk-primary);
    }

    .cyberpunk-steps-status strong {
        color: var(--cyberpunk-warning);
    }

    /* Container baris kotak-kotak */
    .cyberpunk-steps-bar {
        display: flex;
        gap: 4px; /* Jarak antar kotak */
        height: 8px;
        width: 100%;
    }

    /* Kotak per tahap */
    .cyberpunk-step-segment {
        flex: 1; /* Lebar rata */
        background-color: #e5e7eb; /* Warna abu-abu (belum selesai) */
        border-radius: 2px;
        cursor: help; /* Kursor tanda tanya saat hover */
        transition: all 0.3s ease;
        position: relative;
    }

    /* Warna jika selesai */
    .cyberpunk-step-segment.completed {
        background: linear-gradient(90deg, var(--cyberpunk-success), #34d399);
        box-shadow: 0 0 5px rgba(18, 183, 106, 0.4);
    }

    /* Efek hover pada kotak */
    .cyberpunk-step-segment:hover {
        transform: scaleY(1.5); /* Membesar sedikit vertikal */
    }

    /* Label kecil di bawah (Bio, Reg, dll) */
    .cyberpunk-steps-labels-row {
        display: flex;
        justify-content: space-between;
        margin-top: 0.35rem;
        font-size: 0.6rem;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .cyberpunk-steps-labels-row span {
        flex: 1;
        text-align: center;
    }

    .cyberpunk-steps-labels-row span.text-active {
        color: var(--cyberpunk-dark);
        font-weight: 700;
    }
    /* SEGMENTED PROGRESS BAR */
    .cyberpunk-steps-container {
        width: 100%;
        max-width: 350px;
        margin-top: 0.75rem;
        background: rgba(243, 244, 246, 0.7);
        padding: 0.75rem;
        border-radius: 0.5rem;
        border: 1px solid #e5e7eb;
    }
    .cyberpunk-steps-header { display: flex; justify-content: space-between; font-size: 0.7rem; margin-bottom: 0.5rem; color: var(--cyberpunk-gray); }
    .cyberpunk-steps-percentage { font-weight: 800; color: var(--cyberpunk-primary); }
    .cyberpunk-steps-status strong { color: var(--cyberpunk-warning); }
    
    .cyberpunk-steps-bar { display: flex; gap: 4px; height: 8px; width: 100%; }
    .cyberpunk-step-segment { flex: 1; background-color: #e5e7eb; border-radius: 2px; cursor: help; transition: all 0.2s; }
    .cyberpunk-step-segment.completed { background: linear-gradient(90deg, var(--cyberpunk-success), #34d399); }
    .cyberpunk-step-segment:hover { transform: scaleY(1.4); }

    .cyberpunk-steps-labels-row { display: flex; justify-content: space-between; margin-top: 0.4rem; font-size: 0.6rem; color: #9ca3af; text-transform: uppercase; }
    .cyberpunk-steps-labels-row span { flex: 1; text-align: center; }
    .cyberpunk-steps-labels-row span.text-active { color: var(--cyberpunk-dark); font-weight: 700; }
    .cyberpunk-pagination {
    margin-top: 2rem;
    padding: 1rem;
    /*background: rgba(30, 30, 40, 0.8);*/
    border: 1px solid var(--cyberpunk-border);
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    align-items: center;
}

.cyberpunk-pagination-info {
    font-size: 0.9rem;
    color: var(--cyberpunk-text-secondary);
    text-align: center;
}

.cyberpunk-pagination-info strong {
    color: var(--cyberpunk-accent);
}

.cyberpunk-pagination-links {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    justify-content: center;
}

.cyberpunk-pagination-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
    height: 40px;
    padding: 0 12px;
    background: rgba(40, 40, 60, 0.6);
    border: 1px solid var(--cyberpunk-border);
    border-radius: 6px;
    color: var(--cyberpunk-text);
    text-decoration: none;
    font-size: 0.9rem;
    transition: all 0.2s ease;
}

.cyberpunk-pagination-link:hover {
    background: rgba(60, 60, 80, 0.8);
    border-color: var(--cyberpunk-accent);
    color: var(--cyberpunk-accent);
    transform: translateY(-2px);
}

.cyberpunk-pagination-link.active {
    background: var(--cyberpunk-accent);
    border-color: var(--cyberpunk-accent);
    color: var(--cyberpunk-bg);
    font-weight: bold;
}

.cyberpunk-pagination-link.disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
}

.cyberpunk-pagination-ellipsis {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
    height: 40px;
    color: var(--cyberpunk-text-secondary);
}

/* Responsive */
@media (max-width: 768px) {
    .cyberpunk-pagination-links {
        gap: 0.25rem;
    }
    
    .cyberpunk-pagination-link {
        min-width: 36px;
        height: 36px;
        padding: 0 8px;
        font-size: 0.85rem;
    }
}
</style>
@endsection