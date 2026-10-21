@extends('layouts.admin.app')

@section('title', 'Dashboard')

@section('content')

<style>
  /* Simplified Styles with Cyberpunk Side Glow */
  .simple-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease;
    position: relative;
    /* overflow: hidden; */ /* HAPUS ATAU KOMENTARI BARIS INI */
  }
  
  .dark .simple-card {
    background: #1f2937;
    border: 1px solid #374151;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
    /* overflow: hidden; */ /* JIKA ADA DI DARK MODE, HAPUS JUGA */
  }
  
  .simple-card:hover {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  }
  
  .dark .simple-card:hover {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
  }

  /* Cyberpunk Side Glow Effect - Continuous Up-Down Animation */
  .cyber-glow-side {
    position: relative;
  }

  .cyber-glow-side::before {
    content: '';
    position: absolute;
    top: 0;
    left: -2px;
    width: 3px;
    height: 100%;
    background: linear-gradient(180deg, 
      #4f46e5 0%, 
      #818cf8 25%, 
      #a5b4fc 50%, 
      #818cf8 75%, 
      #4f46e5 100%);
    border-radius: 2px;
    opacity: 1;
    box-shadow: 0 0 10px rgba(79, 70, 229, 0.5);
    animation: cyberGlowScan 3s ease-in-out infinite;
  }

  .dark .cyber-glow-side::before {
    background: linear-gradient(180deg, 
      #6366f1 0%, 
      #818cf8 25%, 
      #a5b4fc 50%, 
      #818cf8 75%, 
      #6366f1 100%);
    box-shadow: 0 0 15px rgba(99, 102, 241, 0.7);
    animation: cyberGlowScanDark 3s ease-in-out infinite;
  }

  /* Enhanced glow for main charts with different animation delays */
  .cyber-glow-main::before {
    width: 4px;
    left: -3px;
    box-shadow: 0 0 15px rgba(79, 70, 229, 0.6);
    animation: cyberGlowScanMain 3s ease-in-out infinite;
  }

  .dark .cyber-glow-main::before {
    box-shadow: 0 0 20px rgba(99, 102, 241, 0.8);
    animation: cyberGlowScanMainDark 3s ease-in-out infinite;
  }

  /* Different animation delays for each card type */
  .simple-card:nth-child(1).cyber-glow-side::before {
    animation-delay: 0s;
  }

  .simple-card:nth-child(2).cyber-glow-side::before {
    animation-delay: 0.5s;
  }

  .simple-card:nth-child(3).cyber-glow-side::before {
    animation-delay: 1s;
  }

  .simple-card:nth-child(4).cyber-glow-side::before {
    animation-delay: 1.5s;
  }

  /* Specific delays for main charts */
  .cyber-glow-main:nth-of-type(1)::before {
    animation-delay: 0.2s;
  }

  .cyber-glow-main:nth-of-type(2)::before {
    animation-delay: 0.8s;
  }

  .cyber-glow-main:nth-of-type(3)::before {
    animation-delay: 1.4s;
  }

  /* Animation Keyframes - Up-Down Scan Effect */
  @keyframes cyberGlowScan {
    0% {
      transform: translateY(-100%);
      opacity: 0;
    }
    10% {
      opacity: 1;
    }
    90% {
      opacity: 1;
    }
    100% {
      transform: translateY(100%);
      opacity: 0;
    }
  }

  @keyframes cyberGlowScanDark {
    0% {
      transform: translateY(-100%);
      opacity: 0;
    }
    15% {
      opacity: 1;
    }
    85% {
      opacity: 1;
    }
    100% {
      transform: translateY(100%);
      opacity: 0;
    }
  }

  @keyframes cyberGlowScanMain {
    0% {
      transform: translateY(-120%);
      opacity: 0;
    }
    20% {
      opacity: 1;
    }
    80% {
      opacity: 1;
    }
    100% {
      transform: translateY(120%);
      opacity: 0;
    }
  }

  @keyframes cyberGlowScanMainDark {
    0% {
      transform: translateY(-120%);
      opacity: 0;
    }
    25% {
      opacity: 1;
    }
    75% {
      opacity: 1;
    }
    100% {
      transform: translateY(120%);
      opacity: 0;
    }
  }

  /* Alternative animation for variety - Diagonal Scan */
  .cyber-glow-side.alternate::before {
    animation: cyberGlowDiagonal 4s ease-in-out infinite;
  }

  @keyframes cyberGlowDiagonal {
    0% {
      transform: translateY(-100%) translateX(-10px);
      opacity: 0;
    }
    15% {
      opacity: 1;
    }
    85% {
      opacity: 1;
    }
    100% {
      transform: translateY(100%) translateX(10px);
      opacity: 0;
    }
  }

  .stat-icon {
    background: #4f46e5;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.5rem;
    position: relative;
    overflow: hidden;
  }

  .stat-icon::after {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 50%;
    height: 100%;
    background: linear-gradient(90deg, 
      transparent, 
      rgba(255, 255, 255, 0.3), 
      transparent);
    transition: left 0.5s ease;
  }

  .stat-icon:hover::after {
    left: 100%;
  }
  
  .stat-badge {
    display: inline-flex;
    align-items: center;
    border-radius: 9999px;
    font-size: 0.875rem;
    font-weight: 500;
    padding: 0.25rem 0.75rem;
    position: relative;
    overflow: hidden;
  }
  
  .badge-success {
    background-color: #dcfce7;
    color: #166534;
  }
  
  .dark .badge-success {
    background-color: rgba(21, 128, 61, 0.2);
    color: #4ade80;
  }
  
  .badge-danger {
    background-color: #fef2f2;
    color: #991b1b;
  }
  
  .dark .badge-danger {
    background-color: rgba(185, 28, 28, 0.2);
    color: #f87171;
  }

  /* Subtle cyberpunk text effect */
  .cyber-text {
    background: linear-gradient(135deg, #4f46e5 0%, #818cf8 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .dark .cyber-text {
    background: linear-gradient(135deg, #818cf8 0%, #a5b4fc 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }
</style>

<div class="grid grid-cols-12 gap-4 md:gap-6">
  <div class="col-span-12 space-y-6 xl:col-span-7">
    <!-- Metric Group One -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:gap-6">
      <!-- Metric Item Start -->
      <div class="simple-card cyber-glow-side rounded-xl p-5 md:p-6">
        <div class="flex h-12 w-12 items-center justify-center rounded-lg stat-icon">
          <svg class="fill-white" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
            <circle cx="9" cy="7" r="4"></circle>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
          </svg>
        </div>

        <div class="mt-5 flex items-end justify-between">
          <div>
            <span class="text-sm text-gray-600 dark:text-gray-400">Mahasiswa yang daftar saja</span>
            <h4 class="mt-2 text-title-sm font-bold text-gray-900 dark:text-white cyber-text">
              {{ number_format($usersThisYear) }}
            </h4>
          </div>
          <span class="stat-badge badge-success">
            <svg class="fill-current mr-1" width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path fill-rule="evenodd" clip-rule="evenodd" d="M5.56462 1.62393C5.70193 1.47072 5.90135 1.37432 6.12329 1.37432C6.1236 1.37432 6.12391 1.37432 6.12422 1.37432C6.31631 1.37415 6.50845 1.44731 6.65505 1.59381L9.65514 4.5918C9.94814 4.88459 9.94831 5.35947 9.65552 5.65246C9.36273 5.94546 8.88785 5.94562 8.59486 5.65283L6.87329 3.93247L6.87329 10.125C6.87329 10.5392 6.53751 10.875 6.12329 10.875C5.70908 10.875 5.37329 10.5392 5.37329 10.125L5.37329 3.93578L3.65516 5.65282C3.36218 5.94562 2.8873 5.94547 2.5945 5.65248C2.3017 5.35949 2.30185 4.88462 2.59484 4.59182L5.56462 1.62393Z" fill=""/>
            </svg>
            {{ number_format($userPercentage, 2) }}%
          </span>
        </div>
      </div>
      <!-- Metric Item End -->
<div class="simple-card cyber-glow-side rounded-xl p-5 md:p-6">
  <div class="flex h-12 w-12 items-center justify-center rounded-lg stat-icon">
    <svg class="fill-white" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path>
    </svg>
  </div>

  <div class="mt-5 flex items-end justify-between">
    <div>
      <span class="text-sm text-gray-600 dark:text-gray-400">Lunas UKT (Slik Billings)</span>
      <h4 class="mt-2 text-title-sm font-bold text-gray-900 dark:text-white cyber-text">
        {{ number_format($paidSlik) }}
      </h4>
    </div>
    <div class="flex flex-col items-end">
        <span class="text-[10px] text-gray-500 mb-1 font-medium italic">Total Revenue:</span>
        <span class="stat-badge badge-success text-[10px]">
          Rp {{ number_format($totalRevenue, 0, ',', '.') }}
        </span>
    </div>
  </div>
</div>

      <!-- Metric Item Start -->
<div class="simple-card cyber-glow-side rounded-xl p-5 md:p-6" x-data="{ openFilter: false }">
    <div class="flex items-center justify-between">
        <div class="flex h-12 w-12 items-center justify-center rounded-lg stat-icon">
            <svg class="fill-white" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                <line x1="8" y1="21" x2="16" y2="21"></line>
                <line x1="12" y1="17" x2="12" y2="21"></line>
            </svg>
        </div>

        <div class="relative">
            <button @click="openFilter = !openFilter" class="flex items-center gap-2 px-3 py-1 text-xs font-medium text-gray-500 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors">
                <span>{{ $selectedGelombang ? 'Gel. ' . $selectedGelombang : 'Semua Gel' }}</span>
                <svg class="w-3 h-3 transition-transform" :class="openFilter ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <div x-show="openFilter" 
     x-cloak 
     @click.outside="openFilter = false" 
     class="absolute right-0 z-10 mt-2 w-32 bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
    
    <a href="{{ route('admin.dashboard.index') }}" class="block px-4 py-2 text-xs text-gray-700 hover:bg-indigo-50 sticky top-0 bg-white border-b border-gray-100">Semua Gel</a>
    
    @foreach($listGelombang as $gel)
        <a href="{{ route('admin.dashboard.index', ['gelombang' => $gel]) }}" 
           class="block px-4 py-2 text-xs {{ $selectedGelombang == $gel ? 'bg-indigo-50 text-indigo-600 font-bold' : 'text-gray-700 hover:bg-indigo-50' }}">
            Gelombang {{ $gel }}
        </a>
    @endforeach
</div>
        </div>
    </div>

    <div class="mt-5 flex items-end justify-between">
        <div>
            <span class="text-sm text-gray-600 dark:text-gray-400">
                Lunas Registrasi {{ $selectedGelombang ? '(Gel '.$selectedGelombang.')' : '(Semua Gel)' }}
            </span>
            <h4 class="mt-2 text-title-sm font-bold text-gray-900 dark:text-white cyber-text">
                {{ number_format($paidUsers) }} Orang
            </h4>
            <p class="text-[11px] font-medium text-success-600 mt-1">
                Rp {{ number_format($totalRevenueRegister, 0, ',', '.') }}
            </p>
        </div>

        <div class="flex flex-col items-end gap-2 text-right">
            <span class="stat-badge badge-danger">
                <svg class="fill-current mr-1" width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M5.31462 10.3761C5.45194 10.5293 5.65136 10.6257 5.87329 10.6257C5.8736 10.6257 5.8739 10.6257 5.87421 10.6257C6.0663 10.6259 6.25845 10.5527 6.40505 10.4062L9.40514 7.4082C9.69814 7.11541 9.69831 6.64054 9.40552 6.34754C9.11273 6.05454 8.63785 6.05438 8.34486 6.34717L6.62329 8.06753L6.62329 1.875C6.62329 1.46079 6.28751 1.125 5.87329 1.125C5.45908 1.125 5.12329 1.46079 5.12329 1.875L5.12329 8.06422L3.40516 6.34719C3.11218 6.05439 2.6373 6.05454 2.3445 6.34752C2.0517 6.64051 2.05185 7.11538 2.34484 7.40818L5.31462 10.3761Z" fill=""/>
                </svg>
                {{ number_format($unpaidPercentage, 1) }}%
            </span>
            <span class="text-[10px] text-gray-400 italic">Pending Payment</span>
        </div>
    </div>
</div>
</div>
    <!-- Metric Group One -->
<div class="justify-center flex flex-wrap items-center">
 <div class="col-span-12">
  <div class="simple-card cyber-glow-side rounded-xl px-5 py-5 sm:px-6" id="serverStatusCard">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center gap-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-lg stat-icon flex-shrink-0">
          <svg class="fill-white" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="2" y="3" width="20" height="8" rx="2"></rect>
            <rect x="2" y="13" width="20" height="8" rx="2"></rect>
            <line x1="6" y1="7" x2="6.01" y2="7" stroke="white" stroke-width="2" stroke-linecap="round"></line>
            <line x1="6" y1="17" x2="6.01" y2="17" stroke="white" stroke-width="2" stroke-linecap="round"></line>
          </svg>
        </div>
        <div>
          <h3 class="text-base font-semibold text-gray-900 dark:text-white cyber-text">Status Server E-Learning Dan Scan Berkas</h3>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Monitoring otomatis setiap 30 detik</p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <span id="serverLastChecked" class="text-xs text-gray-400 dark:text-gray-500 hidden sm:block"></span>
        <button onclick="fetchServerStatus()" id="refreshStatusBtn"
          class="p-2 text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-lg transition-all duration-200"
          title="Refresh status">
          <svg id="refreshIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
          </svg>
        </button>
      </div>
    </div>

    {{-- Status Banner --}}
    <div id="statusBanner"
      class="rounded-xl p-4 mb-4 flex items-center gap-3 transition-all duration-500 bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700">
      <div id="statusIconWrap"
        class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center bg-gray-100 dark:bg-gray-700">
        <svg id="statusIcon" class="w-5 h-5 animate-spin" fill="none" stroke="#9ca3af" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
      </div>
      <div class="flex-1 min-w-0">
        <p id="statusTitle" class="font-semibold text-sm text-gray-500 dark:text-gray-400">Menghubungkan ke server...</p>
        <p id="statusDesc" class="text-xs mt-0.5 opacity-80 text-gray-400 dark:text-gray-500"></p>
      </div>
      <div id="statusPulse" class="flex-shrink-0 w-3 h-3 rounded-full hidden"></div>
    </div>

    {{-- Metric Chips --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">

      {{-- Auto Grades --}}
      <div class="rounded-xl p-3 bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700/50">
        <div class="flex items-center gap-2 mb-1.5">
          <svg class="w-3.5 h-3.5 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
          </svg>
          <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Auto Grades</span>
        </div>
        <div class="flex items-center gap-1.5">
          <div id="chip-grade" class="w-2 h-2 rounded-full bg-gray-300 flex-shrink-0"></div>
          <span id="chip-grade-text" class="text-xs font-semibold text-gray-400">—</span>
        </div>
        <p id="chip-grade-time" class="text-[10px] text-gray-400 mt-1 leading-tight">—</p>
      </div>

      {{-- Auto Username --}}
      <div class="rounded-xl p-3 bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700/50">
        <div class="flex items-center gap-2 mb-1.5">
          <svg class="w-3.5 h-3.5 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
          <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Auto Username</span>
        </div>
        <div class="flex items-center gap-1.5">
          <div id="chip-moodle" class="w-2 h-2 rounded-full bg-gray-300 flex-shrink-0"></div>
          <span id="chip-moodle-text" class="text-xs font-semibold text-gray-400">—</span>
        </div>
        <p id="chip-moodle-time" class="text-[10px] text-gray-400 mt-1 leading-tight">—</p>
      </div>

      {{-- Uptime --}}
      <div class="rounded-xl p-3 bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700/50">
        <div class="flex items-center gap-2 mb-1.5">
          <svg class="w-3.5 h-3.5 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Uptime Server</span>
        </div>
        <span id="chip-uptime" class="text-sm font-bold text-gray-400">—</span>
        <p id="chip-logs" class="text-[10px] text-gray-400 mt-1">— log tersimpan</p>
      </div>

    </div>
  </div>
</div>

<script>
(function () {
  const PROXY_URL = '{{ route("admin.server.status") }}';
  const INTERVAL  = 30000;

  function fmtUptime(sec) {
    if (sec == null) return '—';
    const h = Math.floor(sec / 3600);
    const m = Math.floor((sec % 3600) / 60);
    const s = sec % 60;
    return h > 0 ? h + 'j ' + m + 'm' : m > 0 ? m + 'm ' + s + 'd' : s + 'd';
  }

  function fmtRelative(iso) {
    if (!iso) return '—';
    try {
      const diff = Math.floor((Date.now() - new Date(iso)) / 60000);
      if (diff < 1)  return 'Baru saja';
      if (diff < 60) return diff + ' menit lalu';
      const h = Math.floor(diff / 60);
      return h < 24 ? h + ' jam lalu' : new Date(iso).toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });
    } catch { return '—'; }
  }

  /**
   * Status banner ditentukan dari kondisi chip aktual,
   * bukan dari system_status Node.js (yang bisa menyesatkan
   * karena warning log dihitung sebagai error di sana).
   *
   * healthy  → grade_sync_status & moodle_sync_status keduanya 'ok'
   * degraded → salah satu tidak 'ok'
   * offline  → server tidak bisa dijangkau / ada error koneksi
   */
  function resolveStatus(data) {
    if (data.system_status === 'offline' || !!data.error) return 'offline';

    const gradeOk  = data.grade_sync_status  === 'ok';
    const moodleOk = data.moodle_sync_status === 'ok';

    if (gradeOk && moodleOk) return 'healthy';
    return 'degraded';
  }

  function setChip(dotId, textId, timeId, status, lastRun) {
    const dot  = document.getElementById(dotId);
    const text = document.getElementById(textId);
    const time = document.getElementById(timeId);

    if (status === 'ok') {
      dot.className    = 'w-2 h-2 rounded-full bg-emerald-500 flex-shrink-0';
      text.textContent = 'Aktif';
      text.className   = 'text-xs font-semibold text-emerald-600 dark:text-emerald-400';
    } else {
      dot.className    = 'w-2 h-2 rounded-full bg-amber-400 flex-shrink-0 animate-pulse';
      text.textContent = 'Idle';
      text.className   = 'text-xs font-semibold text-amber-600 dark:text-amber-400';
    }
    if (time) time.textContent = lastRun ? 'Terakhir: ' + fmtRelative(lastRun) : '—';
  }

  function setOfflineChip(dotId, textId, timeId) {
    document.getElementById(dotId).className = 'w-2 h-2 rounded-full bg-rose-400 flex-shrink-0';
    const text = document.getElementById(textId);
    text.textContent = 'Offline';
    text.className   = 'text-xs font-semibold text-rose-500';
    if (timeId) document.getElementById(timeId).textContent = '—';
  }

  function applyBanner(status) {
    const banner   = document.getElementById('statusBanner');
    const iconWrap = document.getElementById('statusIconWrap');
    const icon     = document.getElementById('statusIcon');
    const title    = document.getElementById('statusTitle');
    const desc     = document.getElementById('statusDesc');
    const pulse    = document.getElementById('statusPulse');

    icon.classList.remove('animate-spin');

    const configs = {
      healthy: {
        banner:   'rounded-xl p-4 mb-4 flex items-center gap-3 transition-all duration-500 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800/50',
        wrap:     'flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center bg-emerald-100 dark:bg-emerald-900/40',
        stroke:   '#059669',
        iconPath: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        title:    'Server pengambilan username dan auto grades lulus, Maupun Scanner Berkas telah Aktif.',
        titleCls: 'font-semibold text-sm text-emerald-800 dark:text-emerald-300',
        desc:     'Semua layanan berjalan normal. Sinkronisasi nilai & akun Moodle berjalan otomatis.',
        descCls:  'text-xs mt-0.5 opacity-80 text-emerald-700 dark:text-emerald-400',
        pulse:    'flex-shrink-0 w-3 h-3 rounded-full bg-emerald-500 animate-pulse',
      },
      degraded: {
        banner:   'rounded-xl p-4 mb-4 flex items-center gap-3 transition-all duration-500 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/50',
        wrap:     'flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center bg-amber-100 dark:bg-amber-900/40',
        stroke:   '#d97706',
        iconPath: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>',
        title:    'Server aktif namun salah satu layanan belum berjalan.',
        titleCls: 'font-semibold text-sm text-amber-800 dark:text-amber-300',
        desc:     'Pantau log untuk detail lebih lanjut.',
        descCls:  'text-xs mt-0.5 opacity-80 text-amber-700 dark:text-amber-400',
        pulse:    'flex-shrink-0 w-3 h-3 rounded-full bg-amber-400 animate-pulse',
      },
      offline: {
        banner:   'rounded-xl p-4 mb-4 flex items-center gap-3 transition-all duration-500 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800/50',
        wrap:     'flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center bg-rose-100 dark:bg-rose-900/40',
        stroke:   '#e11d48',
        iconPath: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728M15.536 8.464a5 5 0 010 7.072M3 3l18 18"/>',
        title:    'Server tidak dapat dijangkau — silahkan nyalakan kembali servernya.',
        titleCls: 'font-semibold text-sm text-rose-800 dark:text-rose-300',
        desc:     'Tidak ada respons dari server Node.js. Pastikan server sudah berjalan.',
        descCls:  'text-xs mt-0.5 opacity-80 text-rose-700 dark:text-rose-400',
        pulse:    'flex-shrink-0 w-3 h-3 rounded-full bg-rose-300',
      },
    };

    const c = configs[status] || configs.offline;

    banner.className   = c.banner;
    iconWrap.className = c.wrap;
    icon.setAttribute('stroke', c.stroke);
    icon.innerHTML     = c.iconPath;
    title.textContent  = c.title;
    title.className    = c.titleCls;
    desc.textContent   = c.desc;
    desc.className     = c.descCls;
    pulse.className    = c.pulse;
    pulse.classList.remove('hidden');
  }

  function applyData(data) {
    const status  = resolveStatus(data);
    const offline = status === 'offline';

    applyBanner(status);

    if (offline) {
      setOfflineChip('chip-grade',  'chip-grade-text',  'chip-grade-time');
      setOfflineChip('chip-moodle', 'chip-moodle-text', 'chip-moodle-time');
      document.getElementById('chip-uptime').textContent = '—';
      document.getElementById('chip-logs').textContent   = '— log tersimpan';
    } else {
      setChip('chip-grade',  'chip-grade-text',  'chip-grade-time',  data.grade_sync_status,  data.grade_last_run);
      setChip('chip-moodle', 'chip-moodle-text', 'chip-moodle-time', data.moodle_sync_status, data.moodle_last_run);

      document.getElementById('chip-uptime').textContent = fmtUptime(data.uptime_sec);
      document.getElementById('chip-uptime').className   = 'text-sm font-bold text-gray-700 dark:text-gray-300';
      document.getElementById('chip-logs').textContent   = (data.total_logs_stored || 0) + ' log tersimpan';
    }

    const lastChk = document.getElementById('serverLastChecked');
    if (lastChk) lastChk.textContent = 'Diperbarui ' + new Date().toLocaleTimeString('id-ID');

    document.getElementById('refreshIcon').classList.remove('animate-spin');
  }

  window.fetchServerStatus = function () {
    document.getElementById('refreshIcon').classList.add('animate-spin');

    fetch(PROXY_URL, {
      method:      'GET',
      credentials: 'same-origin',
      headers:     { 'X-Requested-With': 'XMLHttpRequest' }
    })
      .then(function (res) { return res.json(); })
      .then(function (data) { applyData(data); })
      .catch(function () {
        applyData({ system_status: 'offline', error: true });
      });
  };

  document.addEventListener('DOMContentLoaded', function () {
    fetchServerStatus();
    setInterval(fetchServerStatus, INTERVAL);
  });

  if (document.readyState !== 'loading') {
    fetchServerStatus();
    setInterval(fetchServerStatus, INTERVAL);
  }
})();
</script>

</div>
    <!-- ====== Chart One Start -->
    <div class="simple-card cyber-glow-side cyber-glow-main rounded-xl px-5 pt-5 sm:px-6 sm:pt-6">
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white cyber-text">
          Grafik Pendaftar Per Bulan
        </h3>

        <div x-data="{openDropDown: false}" class="relative h-fit">
          <button @click="openDropDown = !openDropDown" :class="openDropDown ? 'text-gray-700 dark:text-white' : 'text-gray-400 hover:text-gray-700 dark:hover:text-white'">
            <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path fill-rule="evenodd" clip-rule="evenodd" d="M10.2441 6C10.2441 5.0335 11.0276 4.25 11.9941 4.25H12.0041C12.9706 4.25 13.7541 5.0335 13.7541 6C13.7541 6.9665 12.9706 7.75 12.0041 7.75H11.9941C11.0276 7.75 10.2441 6.9665 10.2441 6ZM10.2441 18C10.2441 17.0335 11.0276 16.25 11.9941 16.25H12.0041C12.9706 16.25 13.7541 17.0335 13.7541 18C13.7541 18.9665 12.9706 19.75 12.0041 19.75H11.9941C11.0276 19.75 10.2441 18.9665 10.2441 18ZM11.9941 10.25C11.0276 10.25 10.2441 11.0335 10.2441 12C10.2441 12.9665 11.0276 13.75 11.9941 13.75H12.0041C12.9706 13.75 13.7541 12.9665 13.7541 12C13.7541 11.0335 12.9706 10.25 12.0041 10.25H11.9941Z" fill=""/>
            </svg>
          </button>
          <div x-show="openDropDown" @click.outside="openDropDown = false" class="absolute right-0 z-40 w-40 p-2 space-y-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 top-full rounded-lg shadow-lg">
            <form action="{{ route('admin.dashboard.target') }}" method="GET">
              @csrf
              <button class="flex w-full px-3 py-2 font-medium text-left text-gray-600 dark:text-gray-400 rounded-lg text-theme-xs hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-300 transition-all">
                Ubah Target
              </button>
            </form>
          </div>
        </div>
      </div>

      <div class="max-w-full overflow-x-auto custom-scrollbar">
        <div class="ml-5 min-w-[650px] pl-2 xl:min-w-full">
          <h3 class="text-lg font-semibold mb-4 ml-5 text-gray-900 dark:text-white">
            Mahasiswa Baru ({{ implode(', ', $availableYears) }})
          </h3>
          <div id="chartAllYears" data-chart='@json($chartData)'></div>
        </div>
      </div>
    </div>
    <!-- ====== Chart One End -->
  </div>

  <div class="col-span-12 xl:col-span-5">
    <!-- ====== Chart Two Start -->
    <div class="simple-card cyber-glow-side cyber-glow-main rounded-xl px-5 pb-11 pt-5 sm:px-6 sm:pt-6">
      <div class="flex justify-between">
        <div>
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white cyber-text">
            Target Pendaftar
          </h3>
          <p class="mt-1 text-theme-sm text-gray-600 dark:text-gray-400">
            Target daftar akun, hingga validasi data.
          </p>
        </div>
        <div x-data="{openDropDown: false}" class="relative h-fit">
          <button @click="openDropDown = !openDropDown" :class="openDropDown ? 'text-gray-700 dark:text-white' : 'text-gray-400 hover:text-gray-700 dark:hover:text-white'">
            <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path fill-rule="evenodd" clip-rule="evenodd" d="M10.2441 6C10.2441 5.0335 11.0276 4.25 11.9941 4.25H12.0041C12.9706 4.25 13.7541 5.0335 13.7541 6C13.7541 6.9665 12.9706 7.75 12.0041 7.75H11.9941C11.0276 7.75 10.2441 6.9665 10.2441 6ZM10.2441 18C10.2441 17.0335 11.0276 16.25 11.9941 16.25H12.0041C12.9706 16.25 13.7541 17.0335 13.7541 18C13.7541 18.9665 12.9706 19.75 12.0041 19.75H11.9941C11.0276 19.75 10.2441 18.9665 10.2441 18ZM11.9941 10.25C11.0276 10.25 10.2441 11.0335 10.2441 12C10.2441 12.9665 11.0276 13.75 11.9941 13.75H12.0041C12.9706 13.75 13.7541 12.9665 13.7541 12C13.7541 11.0335 12.9706 10.25 12.0041 10.25H11.9941Z" fill=""/>
            </svg>
          </button>
          <div x-show="openDropDown" @click.outside="openDropDown = false" class="absolute right-0 top-full z-40 w-40 space-y-1 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-2 shadow-lg">
            <form action="{{ route('admin.dashboard.target') }}" method="GET">
              @csrf
              <button class="flex w-full px-3 py-2 font-medium text-left text-gray-600 dark:text-gray-400 rounded-lg text-theme-xs hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-300 transition-all">
                Ubah Target
              </button>
            </form>
          </div>
        </div>
      </div>
      <div class="relative max-h-[195px]">
        <div id="chartTwoo" data-percentage="{{ round($userPercentage, 2) }}" class="h-full"></div>
        <span class="absolute left-1/2 top-[85%] -translate-x-1/2 -translate-y-[85%] rounded-full bg-green-100 dark:bg-green-900/30 px-3 py-1 text-xs font-medium text-green-800 dark:text-green-400">
          +{{ number_format($userPercentage, 2) }}%
        </span>
      </div>
    </div>

    <div class="flex items-center justify-center gap-5 px-6 py-3.5 sm:gap-8 sm:py-5 bg-gray-50 dark:bg-gray-800 rounded-b-xl border-t border-gray-200 dark:border-gray-700">
      <div>
        <p class="mb-1 text-center text-theme-xs text-gray-600 dark:text-gray-400 sm:text-sm">
          Pendaftar
        </p>
        <p class="flex items-center justify-center gap-1 text-base font-semibold text-gray-900 dark:text-white sm:text-lg cyber-text">
          {{ $paidUsers }}
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M7.26816 13.6632C7.4056 13.8192 7.60686 13.9176 7.8311 13.9176C7.83148 13.9176 7.83187 13.9176 7.83226 13.9176C8.02445 13.9178 8.21671 13.8447 8.36339 13.6981L12.3635 9.70076C12.6565 9.40797 12.6567 8.9331 12.3639 8.6401C12.0711 8.34711 11.5962 8.34694 11.3032 8.63973L8.5811 11.36L8.5811 2.5C8.5811 2.08579 8.24531 1.75 7.8311 1.75C7.41688 1.75 7.0811 2.08579 7.0811 2.5L7.0811 11.3556L4.36354 8.63975C4.07055 8.34695 3.59568 8.3471 3.30288 8.64009C3.01008 8.93307 3.01023 9.40794 3.30321 9.70075L7.26816 13.6632Z" fill="#D92D20"/>
          </svg>
        </p>
      </div>

      <div class="h-7 w-px bg-gray-300 dark:bg-gray-600"></div>

      <div>
        <p class="mb-1 text-center text-theme-xs text-gray-600 dark:text-gray-400 sm:text-sm">
          Validasi Biodata
        </p>
        <p class="flex items-center justify-center gap-1 text-base font-semibold text-gray-900 dark:text-white sm:text-lg cyber-text">
          {{ $totalUsersWithBiodata }}
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M7.60141 2.33683C7.73885 2.18084 7.9401 2.08243 8.16435 2.08243C8.16475 2.08243 8.16516 2.08243 8.16556 2.08243C8.35773 2.08219 8.54998 2.15535 8.69664 2.30191L12.6968 6.29924C12.9898 6.59203 12.9899 7.0669 12.6971 7.3599C12.4044 7.6529 11.9295 7.65306 11.6365 7.36027L8.91435 4.64004L8.91435 13.5C8.91435 13.9142 8.57856 14.25 8.16435 14.25C7.75013 14.25 7.41435 13.9142 7.41435 13.5L7.41435 4.64442L4.69679 7.36025C4.4038 7.65305 3.92893 7.6529 3.63613 7.35992C3.34333 7.06693 3.34348 6.59206 3.63646 6.29926L7.60141 2.33683Z" fill="#039855"/>
          </svg>
        </p>
      </div>

      <div class="h-7 w-px bg-gray-300 dark:bg-gray-600"></div>

      <div>
        <p class="mb-1 text-center text-theme-xs text-gray-600 dark:text-gray-400 sm:text-sm">
          Presentase
        </p>
        <p class="flex items-center justify-center gap-1 text-base font-semibold text-gray-900 dark:text-white sm:text-lg cyber-text">
          {{ $completionPercentage }}%
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M7.60141 2.33683C7.73885 2.18084 7.9401 2.08243 8.16435 2.08243C8.16475 2.08243 8.16516 2.08243 8.16556 2.08243C8.35773 2.08219 8.54998 2.15535 8.69664 2.30191L12.6968 6.29924C12.9898 6.59203 12.9899 7.0669 12.6971 7.3599C12.4044 7.6529 11.9295 7.65306 11.6365 7.36027L8.91435 4.64004L8.91435 13.5C8.91435 13.9142 8.57856 14.25 8.16435 14.25C7.75013 14.25 7.41435 13.9142 7.41435 13.5L7.41435 4.64442L4.69679 7.36025C4.4038 7.65305 3.92893 7.6529 3.63613 7.35992C3.34333 7.06693 3.34348 6.59206 3.63646 6.29926L7.60141 2.33683Z" fill="#039855"/>
          </svg>
        </p>
      </div>
    </div>
    <!-- ====== Chart Two End -->
  </div>

  <div class="col-span-12">
    <!-- ====== Chart Three Start -->
    <div class="simple-card cyber-glow-side cyber-glow-main rounded-xl px-5 pb-5 pt-5 sm:px-6 sm:pt-6">
      <div class="flex flex-col gap-5 mb-6 sm:flex-row sm:justify-between">
        <div class="w-full">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white cyber-text">
            Statistik
          </h3>
          <p class="mt-1 text-gray-600 dark:text-gray-400 text-theme-sm">
            Statistik pendaftar 2 tahun terakhir
          </p>
        </div>
      </div>
      
      <div class="max-w-full overflow-x-auto custom-scrollbar">
        <div class="flex justify-between items-center mb-3">
          <button id="refreshChartBtn" class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm px-4 py-2 rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-all font-medium cyber-glow-side">
            🔄 Refresh
          </button>
        </div>

        <div id="chartThreee" data-chart='{!! json_encode($chartData) !!}' data-current-year="{{ $currentYear }}"></div>
      </div>
    </div>
    <!-- ====== Chart Three End -->
  </div>
  <div class="col-span-12 xl:col-span-7">
    <!-- ====== Table Start ====== -->
    <div class="simple-card cyber-glow-side rounded-xl px-4 pb-3 pt-4 sm:px-6">
      <div class="flex flex-col gap-2 mb-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white cyber-text">
            History Pendaftaran
          </h3>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Daftar pendaftar terbaru sistem
          </p>
        </div>

        <div class="flex items-center gap-3">
          <button id="openFilterBtn" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 cyber-glow-side">
            <svg class="stroke-current" width="18" height="18" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M2.5 5.83333H17.5" stroke-width="1.5" stroke-linecap="round"/>
              <path d="M5 9.16667H15" stroke-width="1.5" stroke-linecap="round"/>
              <path d="M8.33333 12.5H11.6667" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            Filter
          </button>
        </div>
      </div>

      <!-- Table -->
      <div class="max-w-full overflow-x-auto custom-scrollbar">
        <table class="w-full table-auto">
          <thead>
            <tr class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 text-left">
              <th class="min-w-[140px] px-4 py-4 font-semibold text-gray-900 dark:text-white text-sm xl:pl-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2">
                  <span>Nama</span>
                  <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                  </svg>
                </div>
              </th>
              <th class="min-w-[160px] px-4 py-4 font-semibold text-gray-900 dark:text-white text-sm border-b border-gray-200 dark:border-gray-700">
                Email
              </th>
              <th class="min-w-[120px] px-4 py-4 font-semibold text-gray-900 dark:text-white text-sm border-b border-gray-200 dark:border-gray-700">
                Status
              </th>
              <th class="min-w-[120px] px-4 py-4 font-semibold text-gray-900 dark:text-white text-sm border-b border-gray-200 dark:border-gray-700">
                Tanggal
              </th>
              <th class="min-w-[100px] px-4 py-4 font-semibold text-gray-900 dark:text-white text-sm border-b border-gray-200 dark:border-gray-700 xl:pr-6">
                Aksi
              </th>
            </tr>
          </thead>
          <tbody id="tableBody" class="divide-y divide-gray-200 dark:divide-gray-700">
            @php
              $recentRegistrations = $recentRegistrations ?? collect([]);
            @endphp
            
            @forelse($recentRegistrations as $registration)
            <tr class="group hover:bg-gradient-to-r hover:from-blue-50/50 hover:to-indigo-50/50 dark:hover:from-gray-800/50 dark:hover:to-gray-700/50 transition-all duration-200">
              <td class="px-4 py-4 xl:pl-6">
                <div class="flex items-center gap-3">
                  <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 text-white text-xs font-medium">
                    {{ substr($registration->name ?? 'N/A', 0, 1) }}
                  </div>
                  <div>
                    <p class="font-medium text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                      {{ $registration->name ?? 'N/A' }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                      ID: {{ $registration->id ?? 'N/A' }}
                    </p>
                  </div>
                </div>
              </td>
              <td class="px-4 py-4">
                <p class="text-gray-700 dark:text-gray-300">{{ $registration->email ?? 'N/A' }}</p>
              </td>
              <td class="px-4 py-4">
                @if(($registration->payment_status ?? '') == 'paid')
                <span class="inline-flex items-center gap-1 rounded-full bg-green-100 dark:bg-green-900/30 px-3 py-1.5 text-xs font-medium text-green-800 dark:text-green-300 border border-green-200 dark:border-green-800">
                  <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                  </svg>
                  Lunas
                </span>
                @else
                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 dark:bg-amber-900/30 px-3 py-1.5 text-xs font-medium text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                  <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                  </svg>
                  Pending
                </span>
                @endif
              </td>
              <td class="px-4 py-4">
                <div class="flex flex-col">
                  <p class="text-gray-700 dark:text-gray-300 text-sm font-medium">
                    {{ $registration->created_at->format('d M Y') ?? 'N/A' }}
                  </p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ $registration->created_at->format('H:i') ?? '' }}
                  </p>
                </div>
              </td>
              <td class="px-4 py-4 xl:pr-6">
                <div class="flex items-center gap-2">
                  <button class="p-2 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-all duration-200 group/btn" title="Detail">
                    <svg class="w-4 h-4 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                  </button>
                  <button class="p-2 text-gray-400 hover:text-green-600 dark:hover:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-lg transition-all duration-200 group/btn" title="Edit">
                    <svg class="w-4 h-4 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="5" class="px-4 py-12 text-center">
                <div class="flex flex-col items-center justify-center text-gray-400 dark:text-gray-500">
                  <svg class="w-16 h-16 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                  </svg>
                  <p class="text-lg font-medium text-gray-500 dark:text-gray-400 mb-2">Tidak ada data pendaftaran</p>
                  <p class="text-sm text-gray-400 dark:text-gray-500">Data akan muncul ketika ada pendaftar baru</p>
                </div>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <!-- Pagination & Info -->
      <div class="flex flex-col sm:flex-row items-center justify-between mt-6 pt-4 border-t border-gray-200 dark:border-gray-700 gap-4">
        <p class="text-sm text-gray-600 dark:text-gray-400">
          @php
            $total = $recentRegistrations->count();
            $firstItem = $total > 0 ? 1 : 0;
            $lastItem = $total;
          @endphp
          Menampilkan <span class="font-medium text-gray-900 dark:text-white">{{ $firstItem }}</span> - 
          <span class="font-medium text-gray-900 dark:text-white">{{ $lastItem }}</span> dari 
          <span class="font-medium text-gray-900 dark:text-white">{{ $total }}</span> data
        </p>
        
        <div class="flex items-center gap-2">
          <button class="px-3 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
          </button>
          <span class="px-3 py-2 text-sm font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 rounded-lg">1</span>
          <button class="px-3 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-all duration-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </button>
        </div>
      </div>
    </div>
    <!-- ====== Table End ====== -->
  </div>

  <div class="col-span-12 xl:col-span-5">
    <!-- ====== Activity Timeline Start ====== -->
    <div class="simple-card cyber-glow-side rounded-xl px-5 pb-5 pt-5 sm:px-6 sm:pt-6">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white cyber-text">
            Aktivitas Terkini
          </h3>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Update terbaru dari sistem
          </p>
        </div>
        <button class="p-2 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-all duration-200">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
          </svg>
        </button>
      </div>

      <div class="space-y-4">
        @php
          $recentActivities = $recentActivities ?? collect([]);
        @endphp
        
        @forelse($recentActivities as $activity)
        <div class="flex gap-4 group hover:bg-gray-50 dark:hover:bg-gray-800/50 p-3 rounded-xl transition-all duration-200">
          <div class="flex flex-col items-center flex-shrink-0">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-r from-purple-500 to-indigo-600 text-white shadow-lg group-hover:scale-110 transition-transform duration-200">
              @if(($activity->type ?? '') == 'registration')
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
              </svg>
              @elseif(($activity->type ?? '') == 'payment')
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
              </svg>
              @else
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
              </svg>
              @endif
            </div>
            @if(!$loop->last)
            <div class="h-full w-0.5 bg-gradient-to-b from-purple-200 to-indigo-200 dark:from-purple-800 dark:to-indigo-800 my-2 flex-grow"></div>
            @endif
          </div>

          <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-2">
              <p class="font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors line-clamp-1">
                {{ $activity->title ?? 'Aktivitas Sistem' }}
              </p>
              <span class="flex-shrink-0 text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded-full whitespace-nowrap">
                {{ $activity->created_at->diffForHumans() ?? 'Baru saja' }}
              </span>
            </div>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
              {{ $activity->description ?? 'Deskripsi aktivitas sistem' }}
            </p>
            @if($activity->user_name ?? false)
            <div class="mt-2 flex items-center gap-2">
              <div class="w-2 h-2 rounded-full bg-green-500"></div>
              <span class="text-xs text-gray-500 dark:text-gray-400">Oleh: {{ $activity->user_name }}</span>
            </div>
            @endif
          </div>
        </div>
        @empty
        <div class="text-center py-8">
          <div class="flex flex-col items-center justify-center text-gray-400 dark:text-gray-500">
            <svg class="w-16 h-16 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-lg font-medium text-gray-500 dark:text-gray-400 mb-2">Belum ada aktivitas</p>
            <p class="text-sm text-gray-400 dark:text-gray-500">Aktivitas akan muncul di sini</p>
          </div>
        </div>
        @endforelse
      </div>

    
    </div>
    <!-- ====== Activity Timeline End ====== -->
  </div>
</div>

<!-- Include ApexCharts Library -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing charts...');

    // Add random animation delays to create more variety
    const cyberGlowCards = document.querySelectorAll('.cyber-glow-side');
    cyberGlowCards.forEach((card, index) => {
      // Add random delay between 0 and 2 seconds
      const randomDelay = Math.random() * 2;
      card.style.animationDelay = `${randomDelay}s`;
    });

    // Chart Two - Radial Bar
    const chartElement = document.querySelector("#chartTwoo");
    if (chartElement) {
      console.log('Initializing Chart Two...');
      const percentage = parseFloat(chartElement.dataset.percentage) || 0;

      const chartTwoOptions = {
        series: [percentage],
        colors: ["#4f46e5"],
        chart: {
          fontFamily: "Outfit, sans-serif",
          type: "radialBar",
          height: 200,
          sparkline: { enabled: false }
        },
        plotOptions: {
          radialBar: {
            startAngle: -90,
            endAngle: 90,
            hollow: { size: "70%" },
            track: {
              background: "#e5e7eb",
              strokeWidth: "100%",
              margin: 5
            },
            dataLabels: {
              name: { show: false },
              value: {
                fontSize: "24px",
                fontWeight: "600",
                offsetY: -10,
                color: "#1D2939",
                formatter: function(val) {
                  return parseFloat(val).toFixed(2) + "%";
                }
              }
            }
          }
        },
        fill: {
          type: "solid",
        },
        stroke: { lineCap: "round" },
        labels: ["Progress"]
      };

      try {
        const chart = new ApexCharts(chartElement, chartTwoOptions);
        chart.render();
        console.log('Chart Two rendered successfully');
      } catch (error) {
        console.error('Error rendering Chart Two:', error);
      }
    }

    // Chart All Years - Bar Chart
    const chartAllYearsEl = document.querySelector("#chartAllYears");
    if (chartAllYearsEl) {
      console.log('Initializing Chart All Years...');
      try {
        const chartData = JSON.parse(chartAllYearsEl.dataset.chart || "[]");
        console.log('Chart data:', chartData);
        
        const series = chartData.map(item => ({
          name: item.year.toString(),
          data: item.data || new Array(12).fill(0)
        }));

        const monthNames = ["Gelombang 1", "Gelombang 2", "Gelombang 3", "Gelombang 4", "Gelombang 5", "Gelombang 6", "Gelombang 7", "Gelombang 8", "Gelombang 9", "Gelombang 10"];

        const options = {
          series: series,
          colors: ["#4f46e5", "#6366f1", "#818cf8", "#a5b4fc"],
          chart: {
            type: "bar",
            height: 300,
            toolbar: { show: true },
            fontFamily: "Outfit, sans-serif"
          },
          plotOptions: {
            bar: {
              horizontal: false,
              columnWidth: "55%",
              borderRadius: 5,
              borderRadiusApplication: "end"
            }
          },
          dataLabels: { enabled: false },
          stroke: {
            show: true,
            width: 2,
            colors: ["transparent"]
          },
          xaxis: {
            categories: monthNames,
            axisBorder: { show: false },
            axisTicks: { show: false }
          },
          legend: {
            show: true,
            position: "top",
            horizontalAlign: "center",
            fontFamily: "Outfit"
          },
          yaxis: { 
            title: { text: "Jumlah Pendaftar" },
            labels: {
              formatter: function(val) {
                return Math.round(val);
              }
            }
          },
          grid: { 
            borderColor: "#e5e7eb",
            strokeDashArray: 4
          },
          fill: { opacity: 1 },
          tooltip: {
            y: { 
              formatter: function(val) {
                return val + " pendaftar";
              }
            }
          }
        };

        const chart = new ApexCharts(chartAllYearsEl, options);
        chart.render();
        console.log('Chart All Years rendered successfully');
      } catch (error) {
        console.error('Error rendering Chart All Years:', error);
        chartAllYearsEl.innerHTML = '<p class="text-red-500 text-center p-4">Error loading chart data</p>';
      }
    }

    // Chart Three - Area Chart
    const chartThreeEl = document.getElementById('chartThreee');
    if (chartThreeEl) {
      console.log('Initializing Chart Three...');
      try {
        const chartDataAttr = chartThreeEl.dataset.chart;
        const currentYear = chartThreeEl.dataset.currentYear;
        const allYears = JSON.parse(chartDataAttr || "[]");
        console.log('Chart Three data:', allYears);

        const currentYearData = allYears.find(d => d.year == currentYear)?.data || new Array(12).fill(0);
        const previousYear = currentYear - 1;
        const previousYearData = allYears.find(d => d.year == previousYear)?.data || new Array(12).fill(0);

        const monthNames = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];

        const chartThreeOptions = {
          series: [
            { name: `Pendaftar ${currentYear}`, data: currentYearData },
            { name: `Pendaftar ${previousYear}`, data: previousYearData }
          ],
          chart: {
            type: "area",
            height: 350,
            toolbar: { show: true },
            fontFamily: "Outfit, sans-serif",
            animations: { enabled: true }
          },
          colors: ["#4f46e5", "#818cf8"],
          legend: {
            show: true,
            position: "top",
            horizontalAlign: "center"
          },
          fill: {
            type: "gradient",
            gradient: {
              enabled: true,
              opacityFrom: 0.55,
              opacityTo: 0
            }
          },
          stroke: { curve: "smooth", width: 3 },
          grid: {
            borderColor: "#e5e7eb",
            xaxis: { lines: { show: false } },
            yaxis: { lines: { show: true } }
          },
          dataLabels: { enabled: false },
          xaxis: {
            categories: monthNames,
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: { style: { fontSize: "12px" } }
          },
          yaxis: {
            title: { text: "Jumlah Pendaftar", style: { fontSize: "12px" } },
            labels: {
              formatter: function(val) {
                return Math.round(val);
              }
            }
          },
          tooltip: {
            theme: "light",
            y: { formatter: function(val) { return val + " pendaftar"; } }
          }
        };

        const chart = new ApexCharts(chartThreeEl, chartThreeOptions);
        chart.render();
        console.log('Chart Three rendered successfully');
      } catch (error) {
        console.error('Error rendering Chart Three:', error);
        chartThreeEl.innerHTML = '<p class="text-red-500 text-center p-4">Error loading chart data</p>';
      }
    }

    // Refresh Button Functionality
    const refreshBtn = document.getElementById("refreshChartBtn");
    if (refreshBtn) {
      refreshBtn.addEventListener("click", function() {
        location.reload();
      });
    }

    console.log('All charts initialized');
  });
</script>

@endsection