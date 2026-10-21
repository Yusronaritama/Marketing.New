@extends('layouts.admin.app')

@section('title', 'Generate Virtual Account')

@section('content')
<div class="p-4 sm:p-6">
    <!-- Page Header -->
    <div class="mb-6">
        <h4 class="text-title-md font-semibold text-gray-900 dark:text-white mb-2">
            Generate Virtual Account
        </h4>
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                <li>
                    <a href="{{ route('admin.dashboard.index') }}" class="hover:text-brand-500 dark:hover:text-brand-400 transition-colors">
                        Dashboard
                    </a>
                </li>
                <li>
                    <span class="mx-2">/</span>
                </li>
                <li class="text-gray-900 dark:text-white font-medium">
                    Generate VA
                </li>
            </ol>
        </nav>
    </div>

    <!-- Main Card -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-theme-sm border border-gray-200 dark:border-gray-700">
        <div class="p-4 sm:p-6">
            <!-- Filter Section -->
            <div class="mb-6">
                <form method="GET" action="{{ route('admin.generate-va.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                        <!-- Search Input -->
                        <div class="sm:col-span-7">
                            <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Cari Mahasiswa
                            </label>
                            <input 
                                type="text" 
                                id="search"
                                name="search" 
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-400 focus:border-brand-500 dark:focus:border-brand-500 focus:ring-3 focus:ring-brand-500/10 outline-hidden transition-colors"
                                placeholder="Cari nama, email, atau jurusan..."
                                value="{{ request('search') }}">
                        </div>

                        <!-- Status Filter -->
                        <div class="sm:col-span-3">
                            <label for="kelulusan" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Status Kelulusan
                            </label>
                            <select 
                                name="kelulusan" 
                                id="kelulusan"
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:border-brand-500 dark:focus:border-brand-500 focus:ring-3 focus:ring-brand-500/10 outline-hidden appearance-none">
                                <option value="">Semua Status</option>
                                <option value="1" {{ request('kelulusan') == '1' ? 'selected' : '' }}>Sudah Lulus</option>
                                <option value="0" {{ request('kelulusan') == '0' ? 'selected' : '' }}>Belum Lulus</option>
                            </select>
                        </div>

                        <!-- Verifikasi Filter -->
                        <div class="sm:col-span-2">
                            <label for="verifikasi" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Verifikasi
                            </label>
                            <select 
                                name="verifikasi" 
                                id="verifikasi"
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:border-brand-500 dark:focus:border-brand-500 focus:ring-3 focus:ring-brand-500/10 outline-hidden appearance-none">
                                <option value="">Semua</option>
                                <option value="1" {{ request('verifikasi') == '1' ? 'selected' : '' }}>Terverifikasi</option>
                                <option value="0" {{ request('verifikasi') == '0' ? 'selected' : '' }}>Belum Verifikasi</option>
                            </select>
                        </div>

                        <!-- Search Button -->
                        <div class="sm:col-span-2 flex items-end">
                            <button 
                                type="submit" 
                                class="w-full px-6 py-2.5 bg-brand-500 text-white rounded-lg hover:bg-brand-600 focus:ring-3 focus:ring-brand-500/10 transition-colors font-medium inline-flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <span>Cari</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto -mx-4 sm:-mx-6">
                <div class="inline-block min-w-full align-middle">
                    <div class="overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">#</th>
                                    <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Mahasiswa</th>
                                    <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Prodi Pilihan 1</th>
                                    <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Prodi Pilihan 2</th>
                                    <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Virtual Account</th>
                                    <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Tagihan</th>
                                    <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($masterEregs as $masterEreg)
                                @php
                                    $user = $masterEreg->masterUser->user ?? null;
                                    $userId = $user->id ?? null;
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50 transition-colors">
                                    <!-- Number -->
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                        {{ $loop->iteration + ($masterEregs->currentPage() - 1) * $masterEregs->perPage() }}
                                    </td>

                                    <!-- Student Info -->
                                    <td class="px-4 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $masterEreg->nama ?? ($user->biodata->nama_lengkap ?? $user->name ?? 'N/A') }}
                                            </span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                {{ $masterEreg->email ?? ($user->email ?? 'N/A') }}
                                            </span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                HP: {{ $masterEreg->no_hp ?? ($user->biodata->no_telepon ?? $user->phone ?? '-') }}
                                            </span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                TRX: {{ $masterEreg->master_user_id ?? ($user->masterUser->trx_id ?? '-') }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Prodi Pilihan 1 -->
                                    <td class="px-4 py-4">
                                        @if($masterEreg->jurusan1)
                                            <div class="flex flex-col">
                                                <span class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $prodiMapping[$masterEreg->jurusan1]->nama_prodi ?? $masterEreg->jurusan1 }}
                                                </span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $masterEreg->jurusan1 }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-gray-400 dark:text-gray-500">-</span>
                                        @endif
                                    </td>

                                    <!-- Prodi Pilihan 2 -->
                                    <td class="px-4 py-4">
                                        @if($masterEreg->jurusan2)
                                            <div class="flex flex-col">
                                                <span class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $prodiMapping[$masterEreg->jurusan2]->nama_prodi ?? $masterEreg->jurusan2 }}
                                                </span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $masterEreg->jurusan2 }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-gray-400 dark:text-gray-500">-</span>
                                        @endif
                                    </td>

                                    <!-- Status Kelulusan -->
                                    <td class="px-4 py-4">
                                        @if($user && $user->masterMoodleLulus)
                                            <div class="flex flex-col gap-1">
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500 w-fit">
                                                    LULUS
                                                </span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $user->masterMoodleLulus->prodi_lulus ?? '-' }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-500">
                                                BELUM LULUS
                                            </span>
                                        @endif
                                    </td>

                                    <!-- VA Info -->
                                    <td class="px-4 py-4">
                                        @if($userId && isset($vaData[$userId]))
                                            <div class="flex flex-col gap-1">
                                                <code class="text-sm font-mono text-gray-900 dark:text-white px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded w-fit">
                                                    {{ $vaData[$userId]->virtual_account }}
                                                </code>
                                                <div class="flex items-center gap-2">
                                                    @if($vaData[$userId]->status == 'paid')
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500">
                                                            PAID
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-500">
                                                            PENDING
                                                        </span>
                                                    @endif
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                                        Exp: {{ \Carbon\Carbon::parse($vaData[$userId]->datetime_expired)->format('d M Y') }}
                                                    </span>
                                                </div>
                                            </div>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                                                NO VA
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Amount with Custom Info -->
                                    <td class="px-4 py-4">
                                        @if($userId && isset($vaData[$userId]))
                                            <div class="flex flex-col gap-1">
                                                <!-- Amount Display -->
                                                <div class="flex items-center gap-2">
                                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                                        Rp {{ number_format($vaData[$userId]->amount, 0, ',', '.') }}
                                                    </span>
                                                    
                                                    @if($vaData[$userId]->is_custom_amount)
                                                        <div class="flex items-center gap-1">
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-400">
                                                                Custom
                                                            </span>
                                                            <span class="text-xs text-gray-500 dark:text-gray-400" title="Harga Standar">
                                                                (Std: Rp {{ number_format($vaData[$userId]->standard_amount, 0, ',', '.') }})
                                                            </span>
                                                        </div>
                                                    @endif
                                                </div>

                                                <!-- Tenor Info -->
                                                <div class="text-xs text-gray-500 dark:text-gray-400 capitalize">
                                                    {{ $vaData[$userId]->tenor_pembayaran ?? '-' }}
                                                </div>

                                                <!-- Standard Price Info (if custom) -->
                                                @if(isset($vaData[$userId]->is_custom_amount) && $vaData[$userId]->is_custom_amount && isset($vaData[$userId]->standard_amount))
                                                    <div class="mt-1 p-1.5 bg-blue-50 dark:bg-blue-500/10 rounded">
                                                        <div class="text-xs text-gray-600 dark:text-gray-400">
                                                            <span class="font-medium">Std:</span> 
                                                            <span class="line-through">Rp {{ number_format($vaData[$userId]->standard_amount, 0, ',', '.') }}</span>
                                                        </div>
                                                        @php
                                                            $diff = $vaData[$userId]->amount - $vaData[$userId]->standard_amount;
                                                            $diffClass = $diff >= 0 ? 'text-success-600 dark:text-success-400' : 'text-error-600 dark:text-error-400';
                                                        @endphp
                                                        <div class="text-xs {{ $diffClass }} font-medium">
                                                            {{ $diff >= 0 ? '+' : '' }}Rp {{ number_format(abs($diff), 0, ',', '.') }}
                                                        </div>
                                                    </div>
                                                @endif

                                                <!-- Jurusan Info -->
                                                @if(isset($vaData[$userId]->nama_jurusan))
                                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                        {{ $vaData[$userId]->kode_jurusan ?? '' }} - {{ $vaData[$userId]->nama_jurusan ?? '' }}
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-gray-400 dark:text-gray-500">-</span>
                                        @endif
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-2">
                                            @if($userId)
                                                @if(!isset($vaData[$userId]) || ($vaData[$userId]->status == 'paid'))
                                                    <a 
                                                        href="{{ route('admin.generate-va.create', $masterEreg->id) }}" 
                                                        class="inline-flex items-center px-3 py-2 bg-brand-500 text-white rounded-lg text-sm font-medium hover:bg-brand-600 transition-colors gap-1.5">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                        </svg>
                                                        Generate
                                                    </a>
                                                @else
                                                    <a 
                                                        href="{{ route('admin.generate-va.check-status', $masterEreg->id) }}" 
                                                        class="inline-flex items-center px-3 py-2 bg-blue-light-500 text-white rounded-lg text-sm font-medium hover:bg-blue-light-600 transition-colors gap-1.5">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                        </svg>
                                                        Cek Status
                                                    </a>
                                                    <form 
                                                        action="{{ route('admin.generate-va.destroy', $masterEreg->id) }}" 
                                                        method="POST" 
                                                        class="inline"
                                                        onsubmit="return confirm('Apakah Anda yakin ingin membatalkan Virtual Account ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button 
                                                            type="submit" 
                                                            class="inline-flex items-center px-3 py-2 bg-error-500 text-white rounded-lg text-sm font-medium hover:bg-error-600 transition-colors gap-1.5">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                            </svg>
                                                            Batalkan
                                                        </button>
                                                    </form>
                                                @endif
                                            @else
                                                <span class="text-xs text-gray-400 dark:text-gray-500">No User</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center gap-3">
                                            <svg class="w-16 h-16 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            </svg>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">Tidak ada data</p>
                                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Tidak ada mahasiswa yang memenuhi kriteria pencarian</p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            @if($masterEregs->hasPages())
            <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    Menampilkan <span class="font-medium text-gray-900 dark:text-white">{{ $masterEregs->firstItem() }}</span> - 
                    <span class="font-medium text-gray-900 dark:text-white">{{ $masterEregs->lastItem() }}</span> dari 
                    <span class="font-medium text-gray-900 dark:text-white">{{ $masterEregs->total() }}</span> hasil
                </div>
                <div class="flex gap-1">
                    {{ $masterEregs->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Legend untuk Custom Amount -->
<div class="mt-4 bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/20 rounded-lg p-4">
    <div class="flex items-start gap-3">
        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
        </svg>
        <div>
            <h6 class="text-sm font-semibold text-blue-800 dark:text-blue-300 mb-2">
                Keterangan Icon
            </h6>
            <div class="space-y-1 text-sm text-blue-700 dark:text-blue-400">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-300">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                        </svg>
                    </span>
                    <span>= Menggunakan custom amount (harga manual)</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Auto refresh setiap 30 detik untuk update status VA
    setTimeout(function() {
        window.location.reload();
    }, 30000);
    
    document.addEventListener('DOMContentLoaded', function() {
        const customBadges = document.querySelectorAll('[title]');
        customBadges.forEach(badge => {
            badge.style.cursor = 'help';
        });
    });
</script>
@endsection