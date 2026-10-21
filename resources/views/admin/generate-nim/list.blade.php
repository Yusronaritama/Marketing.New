@extends('layouts.admin.app')

@section('title', 'Daftar NIM Tergenerate')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 text-black">Daftar NIM Tergenerate</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                NIM yang sudah digenerate beserta status pengiriman ke SIPT dan SLIK.
            </p>
        </div>
        <a href="{{ route('admin.generate-nim.index') }}"
           class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
            ← Kembali Generate NIM
        </a>
    </div>

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
    @if(session('info'))
        <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
            <p class="text-sm text-blue-800 dark:text-blue-300">{!! session('info') !!}</p>
        </div>
    @endif

    {{-- Filter & Bulk Send Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
        <form method="GET" class="flex flex-1 flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Cari NIM / nama / email..."
                   class="flex-1 min-w-48 rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 text-black dark:placeholder-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"/>
            
            <select name="jenjang" class="rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 text-black">
                <option value="">Semua Jenjang</option>
                <option value="S1" {{ request('jenjang') === 'S1' ? 'selected' : '' }}>S1</option>
                <option value="S2" {{ request('jenjang') === 'S2' ? 'selected' : '' }}>S2</option>
                <option value="Profesi" {{ request('jenjang') === 'Profesi' ? 'selected' : '' }}>Profesi (Apoteker)</option>
            </select>
            
            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-black hover:bg-blue-700 transition-colors cursor-pointer">Filter</button>
            
            @if(request('search') || request('jenjang'))
                <a href="{{ route('admin.generate-nim.list') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-400">Reset</a>
            @endif
        </form>

        <div class="flex flex-wrap items-center gap-2">
            {{-- Form Kirim SIPT Massal (Tanpa Checkbox) --}}
            <form method="POST" action="{{ route('admin.generate-nim.send-bulk-sipt') }}"
                  onsubmit="return confirm('Kirim seluruh data NIM yang belum terdaftar ke SIPT API{{ request('jenjang') ? ' (Jenjang: ' . request('jenjang') . ')' : '' }}?')">
                @csrf
                <input type="hidden" name="jenjang" value="{{ request('jenjang') }}">
                <button type="submit" id="btnBulkSipt"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-black px-4 py-2 text-sm font-semibold shadow-sm transition-all active:scale-95 cursor-pointer">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    Kirim SIPT Massal {{ request('jenjang') ? '('.request('jenjang').')' : '' }}
                </button>
            </form>

            {{-- Form Kirim SLIK Massal (Dilengkapi Lock withoutOverlapping & Delay) --}}
            <form method="POST" action="{{ route('admin.generate-nim.send-slik-bulk') }}"
                  onsubmit="return confirm('Kirim batch tagihan ke SLIK API (Maks 50 data per proses dengan jeda aman)?')">
                @csrf
                <input type="hidden" name="jenjang" value="{{ request('jenjang') }}">
                <input type="hidden" name="limit" value="50">
                <button type="submit" id="btnBulkSlik"
                        class="inline-flex items-center gap-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-black px-4 py-2 text-sm font-semibold shadow-sm transition-all active:scale-95 cursor-pointer">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    Kirim SLIK Massal
                    @if(isset($pendingSlikCount) && $pendingSlikCount > 0)
                        <span class="rounded-full bg-black text-white text-xs px-2 py-0.5 ml-1">{{ $pendingSlikCount }}</span>
                    @endif
                </button>
            </form>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">NIM</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">Nama Mahasiswa</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300 hidden lg:table-cell">Prodi / Jenjang</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">Status Integrasi</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300 hidden xl:table-cell">Digenerate</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($list as $item)
                        <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-700/40">
                            {{-- NIM --}}
                            <td class="px-4 py-3">
                                <span class="font-mono text-sm font-semibold text-gray-900 text-black">{{ $item->nim }}</span>
                            </td>

                            {{-- Nama Mahasiswa --}}
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900 text-black">
                                    {{ $item->mahasiswa->nama_mahasiswa ?? $item->user->name ?? '-' }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $item->email_mhs }}</p>
                            </td>

                            {{-- Prodi / Jenjang --}}
                            <td class="px-4 py-3 hidden lg:table-cell">
                                <div class="flex flex-col gap-1 text-xs text-gray-600 dark:text-gray-400">
                                    <span>{{ $item->kode_prodi }}</span>
                                    <div>
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold
                                            {{ $item->jenjang === 'S2'
                                                ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400'
                                                : ($item->jenjang === 'Profesi'
                                                    ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                                                    : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400') }}">
                                            {{ $item->jenjang }}
                                        </span>
                                        @if($item->syarat_type === 'payment')
                                            <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-[10px] font-semibold text-green-700 ml-1">Lunas</span>
                                        @elseif($item->syarat_type === 'beasiswa')
                                            <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-700 ml-1">Beasiswa</span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Status Integrasi --}}
                            <td class="px-4 py-3">
                                <div class="flex flex-col gap-1.5">
                                    {{-- Status SIPT --}}
                                    <div>
                                        @if($item->message_sipt === 'Success')
                                            <span class="inline-flex items-center gap-1 rounded-md bg-green-50 px-2 py-1 text-xs font-semibold text-green-700 border border-green-200">
                                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                                SIPT Selesai
                                            </span>
                                        @elseif($item->message_sipt)
                                            <span class="inline-flex items-center gap-1 rounded-md bg-red-50 px-2 py-1 text-xs font-semibold text-red-700 border border-red-200" title="{{ $item->message_sipt }}">
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                SIPT Gagal
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-md bg-gray-50 px-2 py-1 text-xs font-semibold text-gray-700 border border-gray-200">
                                                SIPT Belum
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Status SLIK --}}
                                    @if($item->message_sipt === 'Success')
                                        <div>
                                            @if(strtolower($item->message_slik) === 'success')
                                                <span class="inline-flex items-center gap-1 rounded-md bg-green-50 px-2 py-1 text-xs font-semibold text-green-700 border border-green-200">
                                                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                                    SLIK Selesai
                                                </span>
                                            @elseif($item->message_slik)
                                                <span class="inline-flex items-center gap-1 rounded-md bg-yellow-50 px-2 py-1 text-xs font-semibold text-yellow-700 border border-yellow-200" title="{{ $item->message_slik }}">
                                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                                    SLIK Tertunda
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 rounded-md bg-gray-50 px-2 py-1 text-xs font-semibold text-gray-700 border border-gray-200">
                                                    SLIK Belum
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </td>

                            {{-- Info Digenerate --}}
                            <td class="px-4 py-3 text-xs text-gray-700 dark:text-gray-300 hidden xl:table-cell">
                                {{ $item->generated_at?->format('d/m/Y H:i') ?? '-' }}
                                <br>
                                <span class="text-gray-400">{{ $item->generatedBy->name ?? '-' }}</span>
                            </td>

                            {{-- Aksi (Tombol Kirim) --}}
                            <td class="px-4 py-3 text-center">
                                @if($item->message_sipt !== 'Success')
                                    <form method="POST" action="{{ route('admin.generate-nim.send-sipt', $item->id) }}" onsubmit="return confirm('Kirim NIM {{ $item->nim }} ke SIPT?')">
                                        @csrf
                                        <button type="submit" class="inline-flex w-full justify-center items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-black shadow-sm transition-all hover:bg-blue-700 active:scale-95 cursor-pointer">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                            </svg>
                                            Kirim SIPT
                                        </button>
                                    </form>
                                @elseif(strtolower($item->message_slik) !== 'success')
                                    <form method="POST" action="{{ route('admin.generate-nim.send-slik', $item->id) }}" onsubmit="return confirm('Kirim Tagihan NIM {{ $item->nim }} ke SLIK?')">
                                        @csrf
                                        <button type="submit" class="inline-flex w-full justify-center items-center gap-1.5 rounded-lg bg-yellow-500 px-3 py-2 text-xs font-semibold text-black shadow-sm transition-all hover:bg-yellow-600 active:scale-95 cursor-pointer">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                            </svg>
                                            Kirim SLIK
                                        </button>
                                    </form>
                                @else
                                    <span class="inline-flex w-full justify-center items-center gap-1 rounded-lg bg-gray-100 dark:bg-gray-700 px-3 py-2 text-xs font-semibold text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600">
                                        Selesai
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400 dark:text-gray-600">
                                <p class="font-medium">Belum ada NIM yang digenerate</p>
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