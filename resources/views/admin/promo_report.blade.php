@extends('layouts.admin.app')

@section('content')
<div class="p-4 md:p-6">
    <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-white/[0.03]">

        {{-- Header --}}
        <div class="flex flex-col gap-4 rounded-t-2xl border-b border-gray-200 px-6 py-5 dark:border-gray-800">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                    🏫 Laporan Penggunaan Kode Promo per Sekolah
                </h3>
                <p class="text-theme-sm text-gray-500 dark:text-gray-400">
                    Ketik kode promo atau nama sekolah untuk menampilkan datanya
                </p>
            </div>

            {{-- Search box: daftar sekolah baru muncul setelah user mengetik --}}
            <div class="relative w-full sm:max-w-[400px]">
                <span class="pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-gray-400">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9.167 15.833a6.667 6.667 0 1 0 0-13.333 6.667 6.667 0 0 0 0 13.333ZM17.5 17.5l-3.625-3.625" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <input
                    type="text"
                    id="promoSearchInput"
                    placeholder="Cari kode promo atau nama sekolah..."
                    autocomplete="off"
                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pr-4 pl-11 text-theme-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                >
            </div>
        </div>

        {{-- Pesan default: tampil selama belum ada pencarian --}}
        <div id="promoSearchHint" class="flex flex-col items-center gap-2 px-6 py-12 text-center">
            <svg class="h-8 w-8 text-gray-300 dark:text-gray-600" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M9.167 15.833a6.667 6.667 0 1 0 0-13.333 6.667 6.667 0 0 0 0 13.333ZM17.5 17.5l-3.625-3.625" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <p class="text-theme-sm text-gray-400 dark:text-gray-500">
                Mulai ketik untuk mencari data sekolah / kode promo.
            </p>
        </div>

        {{-- Pesan saat pencarian tidak ditemukan --}}
        <div id="promoNoResult" class="hidden flex-col items-center gap-2 px-6 py-12 text-center">
            <p class="text-theme-sm text-gray-400 dark:text-gray-500">
                Tidak ada sekolah / kode promo yang cocok dengan pencarian.
            </p>
        </div>

        {{-- Accordion list --}}
        <div id="promoAccordionList" class="promo-accordion hidden divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($promoCodes as $index => $promo)
                @php
                    $slugId = 'promo-' . preg_replace('/[^A-Za-z0-9\-]/', '', $promo->code);
                    $totalUser = $promo->va_users->count();
                    $paidUser = $promo->va_users->where('va_status', 'paid')->count();
                    $searchKey = strtolower($promo->code . ' ' . $promo->description);

                    // Samarkan 4 karakter terakhir kode promo demi keamanan
                    $codeLength = strlen($promo->code);
                    $maskLength = min(4, $codeLength);
                    $visiblePart = substr($promo->code, 0, $codeLength - $maskLength);
                    $maskedCode = $visiblePart . str_repeat('•', $maskLength);
                @endphp

                {{--
                    <details>/<summary> murni HTML+CSS untuk tabel user (display:none default).
                    data-search dipakai JS untuk filter, dan item ini sendiri disembunyikan
                    total (lihat #promoAccordionList) sampai ada pencarian yang cocok.
                --}}
                <details class="promo-item group" id="{{ $slugId }}" data-search="{{ $searchKey }}">
                    <summary class="promo-summary flex w-full cursor-pointer list-none flex-wrap items-center justify-between gap-3 px-6 py-4 transition-colors hover:bg-gray-50 dark:hover:bg-white/[0.03]">
                        <div class="flex min-w-0 flex-wrap items-center gap-2">
                            <span
                                class="promo-code-badge inline-flex shrink-0 items-center gap-1.5 rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-medium whitespace-nowrap text-brand-500 dark:bg-brand-500/15 dark:text-brand-400"
                                data-full="{{ $promo->code }}"
                                data-masked="{{ $maskedCode }}"
                            >
                                <span class="promo-code-text font-mono">{{ $maskedCode }}</span>
                                <button
                                    type="button"
                                    class="promo-code-toggle inline-flex shrink-0 items-center text-brand-400 hover:text-brand-600 text-white dark:text-brand-400 dark:hover:text-white"
                                    title="Tampilkan/sembunyikan kode promo lengkap"
                                >
                                    {{-- Ikon mata (tertutup, default) --}}
                                    <svg class="promo-eye-closed h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M3 3l14 14M8.35 8.35a2 2 0 0 0 2.83 2.83M6.18 6.2C4.32 7.36 2.9 9.14 2.5 10c1.13 2.5 4.06 6 7.5 6 1.44 0 2.76-.42 3.9-1.06M9.9 4.06A6.9 6.9 0 0 1 10 4c3.44 0 6.37 3.5 7.5 6-.32.7-.85 1.55-1.55 2.36" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    {{-- Ikon mata (terbuka, saat kode terlihat) --}}
                                    <svg class="promo-eye-open hidden h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M2.5 10c1.13-2.5 4.06-6 7.5-6s6.37 3.5 7.5 6c-1.13 2.5-4.06 6-7.5 6s-6.37-3.5-7.5-6Z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                        <circle cx="10" cy="10" r="2" stroke="currentColor" stroke-width="1.4"/>
                                    </svg>
                                </button>
                            </span>
                            <span class="font-medium break-words text-gray-800 dark:text-white/90">
                                {{ $promo->description }}
                            </span>
                            <span class="inline-flex shrink-0 items-center rounded-full border border-gray-200 bg-gray-50 px-2.5 py-0.5 text-xs font-medium whitespace-nowrap text-gray-600 dark:border-gray-700 dark:bg-white/[0.03] dark:text-gray-400">
                                Diskon {{ $promo->discount }}%
                            </span>
                        </div>

                        <div class="flex shrink-0 items-center gap-2">
                            <span class="inline-flex items-center rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">
                                {{ $paidUser }} Terverifikasi
                            </span>
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-white/5 dark:text-gray-400">
                                {{ $totalUser }} Total Klaim
                            </span>

                            @if($totalUser > 0)
                                <button
                                    type="button"
                                    class="promo-export-btn inline-flex items-center gap-1.5 rounded-lg border border-gray-300 px-2.5 py-1 text-xs font-medium text-gray-600 transition-colors hover:border-brand-500 hover:text-brand-500 dark:border-gray-700 dark:text-gray-400 dark:hover:border-brand-500 dark:hover:text-brand-400"
                                    data-target="{{ $slugId }}"
                                    data-filename="Peserta_{{ preg_replace('/[^A-Za-z0-9\-]/', '_', $promo->code) }}"
                                    title="Export daftar user kode promo {{ $promo->code }}"
                                >
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M10 3v10m0 0-3.5-3.5M10 13l3.5-3.5M4 15.833h12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <span class="hidden sm:inline">Export</span>
                                </button>
                            @endif

                            <svg class="promo-chevron h-4 w-4 text-gray-400 transition-transform duration-300" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                    </summary>

                    <div class="promo-detail border-t border-gray-100 bg-gray-50 p-4 dark:border-gray-800 dark:bg-white/[0.02]">
                        @if($totalUser > 0)
                            <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                                <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800">
                                    <thead class="bg-gray-50 dark:bg-white/[0.02]">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">Nama Lengkap</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">User ID</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">Gel</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">Trx ID</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">Nominal</th>
                                            <th class="px-4 py-3 text-center text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">Status VA</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">Tanggal Bayar</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        @foreach($promo->va_users as $user)
                                            <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                                <td class="px-4 py-3 text-theme-sm font-medium whitespace-nowrap text-gray-800 dark:text-white/90">
                                                    {{ $user->user_name }}
                                                </td>
                                                <td class="px-4 py-3 text-theme-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                    {{ $user->user_id }}
                                                </td>
                                                <td class="px-4 py-3 text-theme-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                    Gel. {{ $user->gelombang }}
                                                </td>
                                                <td class="px-4 py-3 text-theme-sm font-mono whitespace-nowrap text-gray-700 dark:text-gray-300">
                                                    {{ $user->trx_id ?? '-' }}
                                                </td>
                                                <td class="px-4 py-3 text-right text-theme-sm font-semibold whitespace-nowrap text-gray-800 dark:text-white/90">
                                                    Rp {{ number_format($user->va_amount, 0, ',', '.') }}
                                                </td>
                                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                                    @if($user->va_status == 'paid')
                                                        <span class="inline-flex items-center rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">
                                                            PAID
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center rounded-full bg-warning-50 px-2.5 py-0.5 text-xs font-medium text-warning-600 dark:bg-warning-500/15 dark:text-warning-400">
                                                            {{ strtoupper($user->va_status) }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-theme-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                    {{ $user->paid_at ?? $user->payment_date ?? '-' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="rounded-xl border border-dashed border-gray-200 bg-white p-6 text-center text-theme-sm text-gray-400 dark:border-gray-800 dark:bg-white/[0.02] dark:text-gray-500">
                                📭 Belum ada data pengguna virtual account untuk kode promo ini.
                            </div>
                        @endif
                    </div>
                </details>

            @empty
                {{-- Tidak ada promo sama sekali di database --}}
            @endforelse
        </div>

    </div>
</div>

@if($promoCodes->isEmpty())
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var hint = document.getElementById('promoSearchHint');
            if (hint) {
                hint.querySelector('p').textContent = 'Tidak ada data sekolah atau kode promo yang tersedia.';
            }
        });
    </script>
@endif

<style>
    /* Hilangkan panah bawaan browser pada <summary> */
    .promo-summary::-webkit-details-marker {
        display: none;
    }

    /* Panah custom berputar saat item dibuka */
    details[open] > .promo-summary .promo-chevron {
        transform: rotate(180deg);
    }

    /* Highlight header saat item sedang terbuka */
    details[open] > .promo-summary {
        background-color: var(--color-gray-50);
    }
    .dark details[open] > .promo-summary {
        background-color: color-mix(in oklab, var(--color-white) 3%, transparent);
    }

    /* Animasi halus saat konten muncul */
    .promo-detail {
        animation: promoFadeIn 0.2s ease-in-out;
    }
    @keyframes promoFadeIn {
        from { opacity: 0; transform: translateY(-4px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* Item promo yang tidak cocok dengan pencarian disembunyikan total */
    .promo-item.is-search-hidden {
        display: none;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var input       = document.getElementById('promoSearchInput');
        var list        = document.getElementById('promoAccordionList');
        var hint        = document.getElementById('promoSearchHint');
        var noResult    = document.getElementById('promoNoResult');

        if (!input || !list) return;

        var items = Array.prototype.slice.call(list.querySelectorAll('.promo-item'));

        function runSearch() {
            var query = input.value.trim().toLowerCase();

            // Belum ada pencarian: sembunyikan daftar, tampilkan hint saja
            if (query === '') {
                list.classList.add('hidden');
                noResult.classList.add('hidden');
                noResult.classList.remove('flex');
                hint.classList.remove('hidden');
                items.forEach(function (item) {
                    item.classList.remove('is-search-hidden');
                    item.open = false; // tutup lagi detail yang sempat terbuka
                });
                return;
            }

            hint.classList.add('hidden');

            var matchCount = 0;
            items.forEach(function (item) {
                var haystack = item.getAttribute('data-search') || '';
                var isMatch = haystack.indexOf(query) !== -1;
                item.classList.toggle('is-search-hidden', !isMatch);
                if (!isMatch) {
                    item.open = false;
                }
                if (isMatch) matchCount++;
            });

            if (matchCount === 0) {
                list.classList.add('hidden');
                noResult.classList.remove('hidden');
                noResult.classList.add('flex');
            } else {
                list.classList.remove('hidden');
                noResult.classList.add('hidden');
                noResult.classList.remove('flex');
            }
        }

        input.addEventListener('input', runSearch);

        // ===== Export CSV per sekolah =====
        var exportButtons = document.querySelectorAll('.promo-export-btn');

        exportButtons.forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation(); // jangan sampai toggle buka/tutup accordion

                var targetId = btn.getAttribute('data-target');
                var filename = btn.getAttribute('data-filename') || 'export';
                var detailsEl = document.getElementById(targetId);
                if (!detailsEl) return;

                var table = detailsEl.querySelector('table');
                if (!table) return;

                var rows = [];

                // Header
                var headerCells = table.querySelectorAll('thead th');
                rows.push(Array.prototype.map.call(headerCells, function (th) {
                    return csvEscape(th.textContent.trim());
                }).join(','));

                // Data rows
                var bodyRows = table.querySelectorAll('tbody tr');
                bodyRows.forEach(function (tr) {
                    var cells = tr.querySelectorAll('td');
                    var rowData = Array.prototype.map.call(cells, function (td) {
                        return csvEscape(td.textContent.trim());
                    });
                    rows.push(rowData.join(','));
                });

                var csvContent = '\uFEFF' + rows.join('\r\n'); // BOM biar Excel baca UTF-8 dengan benar
                var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                var url = URL.createObjectURL(blob);

                var today = new Date().toISOString().slice(0, 10);
                var link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', filename + '_' + today + '.csv');
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(url);
            });
        });

        function csvEscape(value) {
            if (value == null) return '';
            var needsQuote = /[",\r\n]/.test(value);
            var escaped = value.replace(/"/g, '""');
            return needsQuote ? '"' + escaped + '"' : escaped;
        }

        // ===== Toggle reveal/sembunyikan kode promo =====
        var codeToggleButtons = document.querySelectorAll('.promo-code-toggle');

        codeToggleButtons.forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation(); // jangan sampai toggle buka/tutup accordion

                var badge = btn.closest('.promo-code-badge');
                if (!badge) return;

                var textEl    = badge.querySelector('.promo-code-text');
                var eyeClosed = badge.querySelector('.promo-eye-closed');
                var eyeOpen   = badge.querySelector('.promo-eye-open');
                var isRevealed = badge.classList.toggle('is-revealed');

                textEl.textContent = isRevealed
                    ? badge.getAttribute('data-full')
                    : badge.getAttribute('data-masked');

                eyeClosed.classList.toggle('hidden', isRevealed);
                eyeOpen.classList.toggle('hidden', !isRevealed);
            });
        });
    });
</script>
@endsection