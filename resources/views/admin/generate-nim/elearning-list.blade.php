@extends('layouts.admin.app')

@section('title', 'Daftar Akun E-Learning')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-black">Daftar Akun E-Learning Moodle</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Data mahasiswa yang sudah terintegrasi dan dibuatkan akun E-Learning (PKKMB).
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.generate-nim.list') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                ← Daftar NIM
            </a>
        </div>
    </div>

    {{-- Alert Feedback --}}
    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20">
            <p class="text-sm text-green-800 dark:text-green-300">{!! session('success') !!}</p>
        </div>
    @endif
    @if(session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
            <p class="text-sm text-red-800 dark:text-red-300">{!! session('error') !!}</p>
        </div>
    @endif

    {{-- Kartu Statistik --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total NIM Digenerate</p>
            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-black">{{ number_format($statsElearning['total_nim']) }}</p>
        </div>
        <div class="rounded-xl border border-green-200 bg-green-50/50 p-4 shadow-sm dark:border-green-800 dark:bg-green-900/10">
            <p class="text-xs font-medium uppercase tracking-wider text-green-600 dark:text-green-400">Terdaftar di E-Learning</p>
            <p class="mt-1 text-2xl font-bold text-green-700 dark:text-green-300">{{ number_format($statsElearning['total_success']) }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Belum Terdaftar</p>
            <p class="mt-1 text-2xl font-bold text-gray-700 dark:text-gray-300">{{ number_format($statsElearning['total_pending']) }}</p>
        </div>
        <div class="rounded-xl border border-red-200 bg-red-50/50 p-4 shadow-sm dark:border-red-800 dark:bg-red-900/10">
            <p class="text-xs font-medium uppercase tracking-wider text-red-600 dark:text-red-400">Gagal / Bermasalah</p>
            <p class="mt-1 text-2xl font-bold text-red-700 dark:text-red-300">{{ number_format($statsElearning['total_failed']) }}</p>
        </div>
    </div>

    {{-- Filter & Batch Kirim --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
        <form method="GET" class="flex flex-1 flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Cari NIM / Nama / Email..."
                   class="flex-1 min-w-48 rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-black dark:placeholder-gray-400 focus:border-blue-500 focus:outline-none"/>
            
            <select name="status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-black">
                <option value="success" {{ request('status', 'success') === 'success' ? 'selected' : '' }}>Hanya yang Terdaftar (Success)</option>
                <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>Semua Status</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Belum Dikirim</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Gagal</option>
            </select>

            <select name="jenjang" class="rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-black">
                <option value="">Semua Jenjang</option>
                <option value="S1" {{ request('jenjang') === 'S1' ? 'selected' : '' }}>S1</option>
                <option value="S2" {{ request('jenjang') === 'S2' ? 'selected' : '' }}>S2</option>
                <option value="Profesi" {{ request('jenjang') === 'Profesi' ? 'selected' : '' }}>Profesi</option>
            </select>
            
            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-black hover:bg-blue-700 transition-colors">
                Filter
            </button>
            
            @if(request('search') || request('jenjang') || request('status') !== 'success')
                <a href="{{ route('admin.generate-nim.elearning-list') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-400">
                    Reset
                </a>
            @endif
        </form>

        {{-- Tombol Kirim Massal E-Learning --}}
        <form method="POST" action="{{ route('admin.generate-nim.send-bulk-elearning') }}"
              onsubmit="return confirm('Kirim akun E-Learning massal untuk data yang belum terdaftar?')">
            @csrf
            <input type="hidden" name="jenjang" value="{{ request('jenjang') }}">
            <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-black px-4 py-2 text-sm font-semibold shadow-sm transition-all active:scale-95">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Sync Massal ke E-Learning
            </button>
        </form>
    </div>

    {{-- Tabel List --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">Mahasiswa</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">NIM / Akun Moodle</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300 hidden md:table-cell">Prodi</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">Status E-Learning</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($list as $item)
                        @php
                            $trxId = $item->mahasiswa?->masterUser?->trx_id ?? $item->nim;
                            $cleanTrxId = preg_replace('/[^0-9]/', '', (string)$trxId);
                            $moodleUsername = strtolower('pkkmb-26' . $cleanTrxId);
                        @endphp
                        <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-700/40">
                            {{-- Info Mahasiswa --}}
                            <td class="px-4 py-3">
                                <p class="font-semibold text-gray-900 dark:text-black">
                                    {{ $item->mahasiswa->nama_mahasiswa ?? $item->user->name ?? '-' }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $item->email_mhs }}
                                </p>
                            </td>

                            {{-- Akun Moodle --}}
                            <td class="px-4 py-3">
                                <p class="font-mono text-sm font-semibold text-gray-900 dark:text-black">{{ $item->nim }}</p>
                                <p class="text-xs font-mono text-indigo-600 dark:text-indigo-400">
                                    User: {{ $moodleUsername }}
                                </p>
                            </td>

                            {{-- Prodi --}}
                            <td class="px-4 py-3 hidden md:table-cell">
                                <div class="text-xs text-gray-600 dark:text-gray-400">
                                    <span>{{ $item->kode_prodi }}</span>
                                    <span class="inline-flex rounded-full bg-gray-100 dark:bg-gray-700 px-2 py-0.5 text-[10px] font-bold text-gray-700 dark:text-gray-300 ml-1">
                                        {{ $item->jenjang }}
                                    </span>
                                </div>
                            </td>

                            {{-- Status E-Learning --}}
                            <td class="px-4 py-3">
                                @if($item->message_elearning === 'Success')
                                    <span class="inline-flex items-center gap-1 rounded-md bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700 border border-green-200 dark:bg-green-900/20 dark:border-green-800 dark:text-green-300">
                                        <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        Terdaftar (Course 1595)
                                    </span>
                                @elseif($item->message_elearning)
                                    <span class="inline-flex items-center gap-1 rounded-md bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700 border border-red-200 dark:bg-red-900/20 dark:border-red-800 dark:text-red-300" title="{{ $item->message_elearning }}">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        Gagal
                                    </span>
                                    <p class="text-[10px] text-red-600 dark:text-red-400 mt-1 max-w-xs truncate">{{ $item->message_elearning }}</p>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-md bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700 border border-gray-200 dark:bg-gray-700 dark:text-gray-300">
                                        Belum Dikirim
                                    </span>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="px-4 py-3 text-center">
                                <form method="POST" action="{{ route('admin.generate-nim.send-elearning', $item->id) }}" onsubmit="return confirm('Kirim/Sinkron ulang akun E-Learning NIM {{ $item->nim }}?')">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                        {{ $item->message_elearning === 'Success' ? 'Re-Sync' : 'Kirim' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400 dark:text-gray-600">
                                <p class="font-medium">Tidak ada data mahasiswa E-Learning yang cocok dengan filter.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($list->hasPages())
            <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-700">
                {{ $list->links('pagination::tailwind') }}
            </div>
        @endif
    </div>

</div>
@endsection