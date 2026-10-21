@extends('layouts.admin.app')

@section('title', 'Generate NIM Mahasiswa')

@section('content')
<div class="space-y-6" x-data="nimPage('{{ $activeTab }}')">

    {{-- ══ HEADER ══════════════════════════════════════════════════════════════ --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-black">Generate NIM Mahasiswa</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Hanya untuk pendaftar yang <strong class="text-gray-700 dark:text-gray-300">sudah lunas</strong>
                atau <strong class="text-gray-700 dark:text-gray-300">beasiswa pegawai</strong> — generate satu per satu.
            </p>
        </div>
        <a href="{{ route('admin.generate-nim.list') }}"
           class="inline-flex shrink-0 items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            Daftar NIM Tergenerate
        </a>
    </div>

    {{-- ══ STATISTIK ════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
        <div class="cursor-pointer rounded-2xl border-2 p-4 transition-all"
             :class="activeTab==='S1' ? 'border-blue-500 bg-blue-50 dark:border-blue-500 dark:bg-blue-900/20' : 'border-transparent bg-white dark:bg-gray-800'"
             @click="switchTab('S1')">
            <div class="flex items-center justify-between">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-900/40">
                    <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <span class="rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-bold text-blue-700 dark:bg-blue-900/40 dark:text-blue-400">S1</span>
            </div>
            <p class="mt-3 text-2xl font-bold text-gray-900 dark:text-black">{{ $stats['belum_nim_s1'] }}</p>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Antre Generate NIM</p>
        </div>

        <div class="cursor-pointer rounded-2xl border-2 p-4 transition-all"
             :class="activeTab==='S2' ? 'border-purple-500 bg-purple-50 dark:border-purple-500 dark:bg-purple-900/20' : 'border-transparent bg-white dark:bg-gray-800'"
             @click="switchTab('S2')">
            <div class="flex items-center justify-between">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-purple-100 dark:bg-purple-900/40">
                    <svg class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                </div>
                <span class="rounded-full bg-purple-100 px-2 py-0.5 text-[10px] font-bold text-purple-700 dark:bg-purple-900/40 dark:text-purple-400">S2</span>
            </div>
            <p class="mt-3 text-2xl font-bold text-gray-900 dark:text-black">{{ $stats['belum_nim_s2'] }}</p>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Antre Generate NIM</p>
            @if(!in_array(now()->month, [4, 9]))
                <p class="mt-1 text-[10px] font-medium text-amber-600 dark:text-amber-400">⚠ Periode tutup</p>
            @else
                <p class="mt-1 text-[10px] font-medium text-green-600 dark:text-green-400">✓ Periode buka</p>
            @endif
        </div>
        <div class="cursor-pointer rounded-2xl border-2 p-4 transition-all"
             :class="activeTab==='Apoteker' ? 'border-emerald-500 bg-emerald-50 dark:border-emerald-500 dark:bg-emerald-900/20' : 'border-transparent bg-white dark:bg-gray-800'"
             @click="switchTab('Apoteker')">
            <div class="flex items-center justify-between">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 dark:bg-emerald-900/40">
                    <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                </div>
                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400">Profesi</span>
            </div>
            <p class="mt-3 text-2xl font-bold text-gray-900 dark:text-black">{{ $stats['belum_nim_apoteker'] ?? 0 }}</p>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Antre Generate NIM</p>
        </div>

        <div class="rounded-2xl border-2 border-transparent bg-white p-4 dark:bg-gray-800">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-green-100 dark:bg-green-900/40">
                <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="mt-3 text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['sudah_nim'] }}</p>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total NIM Tergenerate</p>
        </div>

        <div class="rounded-2xl border-2 border-transparent bg-white p-4 dark:bg-gray-800">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-sky-100 dark:bg-sky-900/40">
                <svg class="h-5 w-5 text-sky-600 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
            </div>
            <p class="mt-3 text-2xl font-bold text-sky-600 dark:text-sky-400">{{ $stats['terkirim_sipt'] }}</p>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Terkirim ke SIPT</p>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="flex items-start gap-3 rounded-xl border border-green-200 bg-green-50 p-4 dark:border-green-800/50 dark:bg-green-900/20">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <p class="text-sm text-green-800 dark:text-green-300">{!! session('success') !!}</p>
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-800/50 dark:bg-red-900/20">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-500 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
            <p class="text-sm text-red-800 dark:text-red-300">{{ session('error') }}</p>
        </div>
    @endif

    {{-- ══ TAB NAVIGATION ══════════════════════════════════════════════════════ --}}
    <div class="flex gap-1 rounded-2xl border border-gray-200 bg-gray-100 p-1 dark:border-gray-700 dark:bg-gray-900 w-fit">
        <button @click="switchTab('S1')"
                :class="activeTab==='S1' ? 'bg-white shadow text-blue-700 dark:bg-gray-800 dark:text-blue-400' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                class="flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold transition-all">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            Sarjana (S1)
            <span :class="activeTab==='S1' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400' : 'bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-400'"
                  class="rounded-full px-2 py-0.5 text-xs font-bold transition-colors">
                {{ $pendaftarS1->total() }}
            </span>
        </button>
        <button @click="switchTab('S2')"
                :class="activeTab==='S2' ? 'bg-white shadow text-purple-700 dark:bg-gray-800 dark:text-purple-400' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                class="flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold transition-all">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
            </svg>
            Magister (S2)
            <span :class="activeTab==='S2' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-400' : 'bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-400'"
                  class="rounded-full px-2 py-0.5 text-xs font-bold transition-colors">
                {{ $pendaftarS2->total() }}
            </span>
        </button>
        <button @click="switchTab('Apoteker')"
                :class="activeTab==='Apoteker' ? 'bg-white shadow text-emerald-700 dark:bg-gray-800 dark:text-emerald-400' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                class="flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold transition-all">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
            </svg>
            Profesi (Apoteker)
            <span :class="activeTab==='Apoteker' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400' : 'bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-400'"
                  class="rounded-full px-2 py-0.5 text-xs font-bold transition-colors">
                {{ $pendaftarApoteker->total() }}
            </span>
        </button>
    </div>

    {{-- ══════════════════════════════════ PANEL S1 ══════════════════════════════════ --}}
    <div x-show="activeTab==='S1'" x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
         class="space-y-4">

        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-900/30">
                <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div>
                <h2 class="text-base font-bold text-gray-900 dark:text-black">Program Sarjana (S1)</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">Generate NIM tersedia sepanjang tahun untuk semua prodi S1</p>
            </div>
        </div>

        {{-- Filter S1 --}}
        <form method="GET" action="{{ route('admin.generate-nim.index') }}"
              class="rounded-xl border border-blue-100 bg-blue-50/40 p-4 dark:border-blue-900/30 dark:bg-blue-900/10">
            <input type="hidden" name="tab" value="S1">
            <div class="flex flex-wrap gap-3">
                <input type="text" name="search_s1" value="{{ request('search_s1') }}"
                       placeholder="Cari nama / email / HP..."
                       class="flex-1 min-w-44 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-black dark:placeholder-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"/>
                <select name="prodi_s1"
                        class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-black focus:border-blue-500 focus:outline-none">
                    <option value="">Semua Prodi S1</option>
                    @foreach($prodiS1List as $prodi)
                        <option value="{{ $prodi->kode_prodi }}" {{ request('prodi_s1') === $prodi->kode_prodi ? 'selected' : '' }}>
                            {{ $prodi->nama_prodi }}
                        </option>
                    @endforeach
                </select>
                <select name="jalur_s1"
                        class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-black focus:border-blue-500 focus:outline-none">
                    <option value="">Semua Jalur</option>
                    <option value="Jalur Reguler" {{ request('jalur_s1')==='Jalur Reguler' ? 'selected' : '' }}>Jalur Reguler</option>
                    <option value="Jalur Prestasi" {{ request('jalur_s1')==='Jalur Prestasi' ? 'selected' : '' }}>Jalur Prestasi</option>
                    <option value="Jalur Beasiswa" {{ request('jalur_s1')==='Jalur Beasiswa' ? 'selected' : '' }}>Jalur Beasiswa</option>
                </select>
                <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-black hover:bg-blue-700 transition-colors">Filter</button>
                <a href="{{ route('admin.generate-nim.index', ['tab'=>'S1']) }}"
                   class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 transition-colors">Reset</a>
            </div>
        </form>

        @if($pendaftarS1->count() > 0)
        <div class="mt-3 flex justify-end">
            <form method="POST" action="{{ route('admin.generate-nim.bulk-generate') }}"
                  onsubmit="return confirm('Yakin ingin generate NIM secara massal untuk {{ $pendaftarS1->total() }} pendaftar S1?')">
                @csrf
                <input type="hidden" name="tab" value="S1">
                @foreach($pendaftarS1 as $bulkItem)
                    <input type="hidden" name="ids[]" value="{{ $bulkItem->id }}">
                @endforeach
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-semibold shadow transition cursor-pointer" style="background-color: #2563eb !important; color: #ffffff !important;">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Generate Bulk S1 ({{ $pendaftarS1->total() }})
                </button>
            </form>
        </div>
        @endif

        {{-- Tabel S1 --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center gap-2 border-b border-blue-100 bg-blue-50/60 px-5 py-3 dark:border-blue-900/30 dark:bg-blue-900/10">
                <span class="h-2.5 w-2.5 rounded-full bg-blue-500"></span>
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Antre Generate NIM — Sarjana S1</h3>
                <span class="rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-bold text-blue-700 dark:bg-blue-900/40 dark:text-blue-400">
                    {{ $pendaftarS1->total() }} orang
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/40">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">No.</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Nama Mahasiswa</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 hidden md:table-cell">Program Studi</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 hidden lg:table-cell">Jalur</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Syarat</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 hidden xl:table-cell">Mendaftar</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                        @forelse($pendaftarS1 as $item)
                            @php
                                $ereg = $item->masterEreg;
                                $mu   = $item->masterUser;
                                $isPaid     = $mu && $mu->va_status === 'paid';
                                $isBeasiswa = $mu && $mu->jenis_beasiswa === 'pegawai';
                            @endphp
                            <tr class="transition hover:bg-blue-50/30 dark:hover:bg-blue-900/10">
                                <td class="px-4 py-3.5 text-xs text-gray-400">{{ $pendaftarS1->firstItem() + $loop->index }}</td>
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-blue-400 to-blue-600 text-xs font-bold text-black shadow-sm">
                                            {{ strtoupper(substr($item->nama_mahasiswa, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-900 dark:text-black">{{ $item->nama_mahasiswa }}</p>
                                            <p class="text-xs text-gray-400">{{ $item->user->email ?? $item->email ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                @php
                                    $kodeProdiTampil = !empty($ereg?->jurusan_lulusan) && $ereg->jurusan_lulusan !== '00000' && $ereg->jurusan_lulusan !== '-'
                                        ? $ereg->jurusan_lulusan
                                        : $ereg?->jurusan1;
                                    $namaProdiTampil = \App\Models\ProgramStudi::where('kode_prodi', $kodeProdiTampil)->value('nama_prodi') ?? ($kodeProdiTampil ?? '-');
                                    $isBedaProdi = !empty($ereg?->jurusan_lulusan) && !empty($ereg?->jurusan1) && ($ereg->jurusan_lulusan !== $ereg->jurusan1);
                                @endphp
                                <td class="px-4 py-3.5 hidden md:table-cell">
                                    <p class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $namaProdiTampil }}</p>
                                    <p class="text-xs text-gray-400">Kode: {{ $kodeProdiTampil ?? '-' }}
                                        @if($isBedaProdi)
                                            <span class="inline-flex items-center rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-semibold text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 ml-1">Lulusan/Pindahan</span>
                                        @endif
                                    </p>
                                </td>
                                <td class="px-4 py-3.5 hidden lg:table-cell text-xs text-gray-500 dark:text-gray-400">{{ $ereg->jalur_pendaftaran ?? '-' }}</td>
                                <td class="px-4 py-3.5">
                                    @if($isPaid)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                            Lunas
                                        </span>
                                    @elseif($isBeasiswa)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            Beasiswa Pegawai
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 hidden xl:table-cell text-xs text-gray-400">{{ $item->created_at?->format('d M Y') }}</td>
                                <td class="px-4 py-3.5 text-center">
                                    <form method="POST" action="{{ route('admin.generate-nim.generate', $item->id) }}"
                                          onsubmit="return confirm('Generate NIM untuk {{ addslashes($item->nama_mahasiswa) }}?')">
                                        @csrf
                                        <input type="hidden" name="tab" value="S1">
                                        <button type="submit"
                                                class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3.5 py-1.5 text-xs font-semibold text-black shadow-sm hover:bg-blue-700 active:scale-95 transition-all">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            Generate NIM
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-14 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-100 dark:bg-blue-900/30">
                                            <svg class="h-8 w-8 text-blue-300 dark:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                        <p class="font-semibold text-gray-600 dark:text-gray-400">Tidak ada pendaftar S1 yang memenuhi syarat</p>
                                        <p class="text-sm text-gray-400">Semua sudah punya NIM atau belum lunas.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($pendaftarS1->hasPages())
                <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-700">
                    {{ $pendaftarS1->appends(['tab'=>'S1'])->links('pagination::tailwind') }}
                </div>
            @endif
        </div>
    </div>

    {{-- ══════════════════════════════════ PANEL S2 ══════════════════════════════════ --}}
    <div x-show="activeTab==='S2'" x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
         class="space-y-4">

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-100 dark:bg-purple-900/30">
                    <svg class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-black">Program Magister (S2) — Manajemen</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Generate NIM dibuka pada bulan <strong>April</strong> dan <strong>September</strong></p>
                </div>
            </div>
            @if(in_array(now()->month, [4, 9]))
                <div class="inline-flex shrink-0 items-center gap-2 rounded-xl border border-green-200 bg-green-50 px-4 py-2 dark:border-green-800/50 dark:bg-green-900/20">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-green-500"></span>
                    </span>
                    <span class="text-xs font-semibold text-green-700 dark:text-green-400">Periode Buka — {{ now()->isoFormat('MMMM Y') }}</span>
                </div>
            @else
                <div class="inline-flex shrink-0 items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2 dark:border-amber-800/50 dark:bg-amber-900/20">
                    <svg class="h-4 w-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <div>
                        <p class="text-xs font-semibold text-amber-700 dark:text-amber-400">Periode Tutup</p>
                        <p class="text-[10px] text-amber-600 dark:text-amber-500">Buka: April & September</p>
                    </div>
                </div>
            @endif
        </div>

        @if(!in_array(now()->month, [4, 9]))
            <div class="flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-800/50 dark:bg-amber-900/10">
                <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <p class="text-sm font-semibold text-amber-800 dark:text-amber-400">Periode Penerimaan S2 Sedang Tutup</p>
                    <p class="mt-0.5 text-xs text-amber-700 dark:text-amber-500">
                        Generate NIM S2 hanya dapat dilakukan pada bulan <strong>April</strong> (semester genap) dan
                        <strong>September</strong> (semester ganjil). Saat ini bulan <strong>{{ now()->isoFormat('MMMM') }}</strong>.
                        Data pendaftar dapat dilihat, namun tombol generate dinonaktifkan.
                    </p>
                </div>
            </div>
        @endif

        {{-- Filter S2 --}}
        <form method="GET" action="{{ route('admin.generate-nim.index') }}"
              class="rounded-xl border border-purple-100 bg-purple-50/40 p-4 dark:border-purple-900/30 dark:bg-purple-900/10">
            <input type="hidden" name="tab" value="S2">
            <div class="flex flex-wrap gap-3">
                <input type="text" name="search_s2" value="{{ request('search_s2') }}"
                       placeholder="Cari nama / email / HP..."
                       class="flex-1 min-w-44 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-black dark:placeholder-gray-400 focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20"/>
                <select name="jalur_s2"
                        class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-black focus:border-purple-500 focus:outline-none">
                    <option value="">Semua Jalur</option>
                    <option value="Jalur Reguler" {{ request('jalur_s2')==='Jalur Reguler' ? 'selected' : '' }}>Jalur Reguler</option>
                    <option value="Jalur Beasiswa" {{ request('jalur_s2')==='Jalur Beasiswa' ? 'selected' : '' }}>Jalur Beasiswa</option>
                </select>
                <button type="submit" class="rounded-lg bg-purple-600 px-4 py-2 text-sm font-medium text-black hover:bg-purple-700 transition-colors">Filter</button>
                <a href="{{ route('admin.generate-nim.index', ['tab'=>'S2']) }}"
                   class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 transition-colors">Reset</a>
            </div>
        </form>

        @if($pendaftarS2->count() > 0)
        <div class="mt-3 flex justify-end">
            <form method="POST" action="{{ route('admin.generate-nim.bulk-generate') }}"
                  onsubmit="return confirm('Yakin ingin generate NIM secara massal untuk {{ $pendaftarS2->total() }} pendaftar S2?')">
                @csrf
                <input type="hidden" name="tab" value="S2">
                @foreach($pendaftarS2 as $bulkItem)
                    <input type="hidden" name="ids[]" value="{{ $bulkItem->id }}">
                @endforeach
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-semibold shadow transition cursor-pointer" style="background-color: #7c3aed !important; color: #ffffff !important;">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Generate Bulk S2 ({{ $pendaftarS2->total() }})
                </button>
            </form>
        </div>
        @endif

        {{-- Tabel S2 --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center gap-2 border-b border-purple-100 bg-purple-50/60 px-5 py-3 dark:border-purple-900/30 dark:bg-purple-900/10">
                <span class="h-2.5 w-2.5 rounded-full bg-purple-500"></span>
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Antre Generate NIM — S2 Manajemen</h3>
                <span class="rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-bold text-purple-700 dark:bg-purple-900/40 dark:text-purple-400">
                    {{ $pendaftarS2->total() }} orang
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/40">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">No.</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Nama Mahasiswa</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 hidden md:table-cell">Latar Belakang S1</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 hidden lg:table-cell">Jalur</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Syarat</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 hidden lg:table-cell">Status Kerja</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                        @forelse($pendaftarS2 as $item)
                            @php
                                $ereg = $item->masterEreg;
                                $mu   = $item->masterUser;
                                $isPaid       = $mu && $mu->va_status === 'paid';
                                $isBeasiswa   = $mu && $mu->jenis_beasiswa === 'pegawai';
                                $periodeAktif = in_array(now()->month, [4, 9]);
                            @endphp
                            <tr class="transition hover:bg-purple-50/30 dark:hover:bg-purple-900/10">
                                <td class="px-4 py-3.5 text-xs text-gray-400">{{ $pendaftarS2->firstItem() + $loop->index }}</td>
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-purple-400 to-purple-600 text-xs font-bold text-black shadow-sm">
                                            {{ strtoupper(substr($item->nama_mahasiswa, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-900 dark:text-black">{{ $item->nama_mahasiswa }}</p>
                                            <p class="text-xs text-gray-400">{{ $item->user->email ?? $item->email ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 hidden md:table-cell">
                                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $ereg->jurusan_lulusan ?? '-' }}</p>
                                </td>
                                <td class="px-4 py-3.5 hidden lg:table-cell text-xs text-gray-500 dark:text-gray-400">{{ $ereg->jalur_pendaftaran ?? '-' }}</td>
                                <td class="px-4 py-3.5">
                                    @if($isPaid)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                            Lunas
                                        </span>
                                    @elseif($isBeasiswa)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            Beasiswa Pegawai
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 hidden lg:table-cell">
                                    @if($ereg->status_pekerjaan)
                                        <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs text-gray-600 dark:bg-gray-700 dark:text-gray-400">{{ $ereg->status_pekerjaan }}</span>
                                    @else
                                        <span class="text-xs text-gray-300">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    @if($periodeAktif)
                                        <form method="POST" action="{{ route('admin.generate-nim.generate', $item->id) }}"
                                              onsubmit="return confirm('Generate NIM S2 untuk {{ addslashes($item->nama_mahasiswa) }}?\nProgram: S2 Manajemen')">
                                            @csrf
                                            <input type="hidden" name="tab" value="S2">
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1.5 rounded-lg bg-purple-600 px-3.5 py-1.5 text-xs font-semibold text-black shadow-sm hover:bg-purple-700 active:scale-95 transition-all">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                                                </svg>
                                                Generate NIM
                                            </button>
                                        </form>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-medium text-amber-600 dark:border-amber-800/50 dark:bg-amber-900/20 dark:text-amber-400"
                                              title="Generate NIM S2 hanya tersedia di bulan April dan September">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                            </svg>
                                            Periode Tutup
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-14 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-purple-100 dark:bg-purple-900/30">
                                            <svg class="h-8 w-8 text-purple-300 dark:text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                        <p class="font-semibold text-gray-600 dark:text-gray-400">Tidak ada pendaftar S2 yang memenuhi syarat</p>
                                        <p class="text-sm text-gray-400">Semua sudah punya NIM atau belum lunas.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($pendaftarS2->hasPages())
                <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-700">
                    {{ $pendaftarS2->appends(['tab'=>'S2'])->links('pagination::tailwind') }}
                </div>
            @endif
        </div>
    </div>


    {{-- ══════════════════════════════════ PANEL APOTEKER (PROFESI) ══════════════════════════════════ --}}
    <div x-show="activeTab==='Apoteker'" x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
         class="space-y-4">

        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 dark:bg-emerald-900/30">
                <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-base font-bold text-gray-900 dark:text-black">Program Profesi Apoteker</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">Generate NIM pendaftar khusus Program Profesi Apoteker</p>
            </div>
        </div>

        {{-- Filter & Action Bar Apoteker --}}
        <div class="flex flex-col gap-3 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 lg:flex-row lg:items-center lg:justify-between">
            <form method="GET" action="{{ route('admin.generate-nim.index') }}"
                  class="flex flex-wrap items-center gap-2.5">
                <input type="hidden" name="tab" value="Apoteker">

                <div class="relative flex-1 sm:w-64 sm:flex-none">
                    <svg class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="search_apoteker" value="{{ request('search_apoteker') }}"
                           placeholder="Cari nama, HP, email..."
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 py-2 pl-9 pr-3 text-xs text-gray-900 focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-black dark:focus:border-emerald-400">
                </div>

                <select name="jalur_apoteker"
                        class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-700 focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    <option value="">Semua Jalur</option>
                    @foreach(['Jalur Reguler', 'Beasiswa', 'Pindahan'] as $j)
                        <option value="{{ $j }}" {{ request('jalur_apoteker')==$j ? 'selected' : '' }}>{{ $j }}</option>
                    @endforeach
                </select>

                <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-xs font-medium shadow-sm transition" style="background-color: #059669 !important; color: #ffffff !important;">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    Filter
                </button>

                @if(request('search_apoteker') || request('jalur_apoteker'))
                <a href="{{ route('admin.generate-nim.index', ['tab'=>'Apoteker']) }}"
                   class="text-xs text-gray-500 underline hover:text-gray-700 dark:text-gray-400">Reset</a>
                @endif
            </form>

            @if($pendaftarApoteker->count() > 0)
            <form method="POST" action="{{ route('admin.generate-nim.bulk-generate') }}"
                  onsubmit="return confirm('Yakin ingin generate NIM secara massal untuk {{ $pendaftarApoteker->total() }} pendaftar Apoteker?')">
                @csrf
                <input type="hidden" name="tab" value="Apoteker">
                @foreach($pendaftarApoteker as $bulkItem)
                    <input type="hidden" name="ids[]" value="{{ $bulkItem->id }}">
                @endforeach
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-semibold shadow transition cursor-pointer" style="background-color: #059669 !important; color: #ffffff !important;">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Generate Bulk Apoteker ({{ $pendaftarApoteker->total() }})
                </button>
            </form>
            @endif
        </div>

        {{-- Tabel Apoteker --}}
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-100 bg-emerald-50/50 px-5 py-3 dark:border-gray-800 dark:bg-emerald-950/20 flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-300">
                    Antre Generate NIM — Profesi Apoteker &nbsp;·&nbsp; <span class="font-medium text-emerald-700">{{ $pendaftarApoteker->total() }} orang</span>
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/50 text-left dark:border-gray-800 dark:bg-gray-800/50">
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">#</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Nama / Kontak</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 hidden md:table-cell">Program Studi</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 hidden lg:table-cell">Jalur</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Syarat</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($pendaftarApoteker as $item)
                            @php
                                $ereg = $item->masterEreg;
                                $u    = $item->user;
                                $mu   = $item->masterUser;
                            @endphp
                            <tr class="transition hover:bg-gray-50/60 dark:hover:bg-gray-800/40">
                                <td class="px-4 py-3.5 text-xs text-gray-400">{{ $pendaftarApoteker->firstItem() + $loop->index }}</td>
                                <td class="px-4 py-3.5">
                                    <div class="font-semibold text-gray-900 dark:text-black">{{ $item->nama_mahasiswa }}</div>
                                    <div class="text-xs text-gray-400">{{ $u->email ?? '-' }}</div>
                                    <div class="text-xs text-gray-400">{{ $item->handphone }}</div>
                                </td>
                                <td class="px-4 py-3.5 hidden md:table-cell">
                                    <span class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                                        Profesi Apoteker
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 hidden lg:table-cell text-xs text-gray-500 dark:text-gray-400">{{ $ereg->jalur_pendaftaran ?? '-' }}</td>
                                <td class="px-4 py-3.5">
                                    @if(($mu->va_status ?? '') === 'paid')
                                        <span class="inline-flex items-center gap-1 rounded-md bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700 dark:bg-green-900/40 dark:text-green-300">
                                            Lunas
                                        </span>
                                    @elseif(($mu->jenis_beasiswa ?? '') === 'pegawai')
                                        <span class="inline-flex items-center gap-1 rounded-md bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                                            Beasiswa Pegawai
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-md bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                            {{ $mu->va_status ?? '-' }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <form method="POST" action="{{ route('admin.generate-nim.generate', $item->id) }}"
                                          onsubmit="return confirm('Generate NIM untuk {{ addslashes($item->nama_mahasiswa) }}?')">
                                        @csrf
                                        <input type="hidden" name="tab" value="Apoteker">
                                        <button type="submit"
                                                class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-1.5 text-xs font-semibold shadow-sm transition-all hover:opacity-90 active:scale-95"
                                                style="background-color: #059669 !important; color: #ffffff !important;">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            Generate NIM
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-gray-400 dark:text-gray-500">
                                    <p class="text-sm">Tidak ada pendaftar Apoteker yang antre generate NIM.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($pendaftarApoteker->hasPages())
                <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-700">
                    {{ $pendaftarApoteker->appends(['tab'=>'Apoteker'])->links('pagination::tailwind') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function nimPage(initialTab) {
    return {
        activeTab: initialTab || 'S1',
        switchTab(tab) {
            this.activeTab = tab;
            const url = new URL(window.location.href);
            url.searchParams.set('tab', tab);
            window.history.replaceState({}, '', url.toString());
        }
    }
}
</script>
@endpush