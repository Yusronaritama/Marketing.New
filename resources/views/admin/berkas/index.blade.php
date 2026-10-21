{{-- resources/views/admin/berkas/index.blade.php --}}
@php
use App\Models\User;
@endphp

@extends('layouts.admin.app')

@section('title', 'Manajemen Berkas')

@section('content')
<div class="px-6 py-8">
    <!-- Header with Cyberpunk Style -->
    <div class="mb-8 relative">
        <!-- Cyber Grid Background -->
        <div class="absolute inset-0 opacity-10 dark:opacity-5 pointer-events-none">
            <div class="absolute inset-0" style="background-image: linear-gradient(rgba(70, 95, 255, 0.3) 1px, transparent 1px), linear-gradient(90deg, rgba(70, 95, 255, 0.3) 1px, transparent 1px); background-size: 20px 20px;"></div>
        </div>
        
        <div class="relative">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-1 h-8 bg-gradient-to-b from-brand-500 to-blue-light-500 rounded-full"></div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">
                    <span class="bg-clip-text text-transparent bg-gradient-to-r from-brand-500 to-blue-light-500">
                        MANAJEMEN BERKAS
                    </span>
                </h1>
            </div>
            <p class="text-gray-600 dark:text-gray-400 ml-4 text-sm font-medium">Kelola semua berkas pendaftaran peserta</p>
        </div>
    </div>

    <!-- Statistik Cards dengan Cyberpunk Design -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <!-- Total Berkas Card -->
        <div class="group relative overflow-hidden bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-brand-500 dark:hover:border-brand-500 transition-all duration-300">
            <!-- Glow Effect -->
            <div class="absolute inset-0 bg-gradient-to-br from-brand-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            
            <!-- Corner Accents -->
            <div class="absolute top-0 left-0 w-8 h-8 border-t-2 border-l-2 border-brand-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="absolute bottom-0 right-0 w-8 h-8 border-b-2 border-r-2 border-brand-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            
            <div class="relative flex items-center gap-4 p-4">
                <div class="relative">
                    <div class="absolute inset-0 bg-brand-500 rounded-lg blur-lg opacity-20 group-hover:opacity-40 transition-opacity"></div>
                    <div class="relative p-3 bg-gradient-to-br from-brand-50 to-blue-light-50 dark:from-brand-900/30 dark:to-blue-light-900/30 rounded-lg ring-1 ring-brand-500/20">
                        <svg class="w-6 h-6 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Total Berkas</p>
                    <h3 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-gray-900 to-gray-700 dark:from-white dark:to-gray-300">{{ $stats['total'] }}</h3>
                </div>
            </div>
        </div>

        <!-- In Progress Card -->
        <div class="group relative overflow-hidden bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-warning-500 dark:hover:border-warning-500 transition-all duration-300">
            <div class="absolute inset-0 bg-gradient-to-br from-warning-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            
            <div class="absolute top-0 left-0 w-8 h-8 border-t-2 border-l-2 border-warning-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="absolute bottom-0 right-0 w-8 h-8 border-b-2 border-r-2 border-warning-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            
            <div class="relative flex items-center gap-4 p-4">
                <div class="relative">
                    <div class="absolute inset-0 bg-warning-500 rounded-lg blur-lg opacity-20 group-hover:opacity-40 transition-opacity"></div>
                    <div class="relative p-3 bg-gradient-to-br from-warning-50 to-orange-50 dark:from-warning-900/30 dark:to-orange-900/30 rounded-lg ring-1 ring-warning-500/20">
                        <svg class="w-6 h-6 text-warning-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">In Progress</p>
                    <h3 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-warning-600 to-orange-500 dark:from-warning-400 dark:to-orange-400">{{ $stats['in_progress'] }}</h3>
                </div>
            </div>
        </div>

        <!-- Rejected Card -->
        <div class="group relative overflow-hidden bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-error-500 dark:hover:border-error-500 transition-all duration-300">
            <div class="absolute inset-0 bg-gradient-to-br from-error-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            
            <div class="absolute top-0 left-0 w-8 h-8 border-t-2 border-l-2 border-error-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="absolute bottom-0 right-0 w-8 h-8 border-b-2 border-r-2 border-error-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            
            <div class="relative flex items-center gap-4 p-4">
                <div class="relative">
                    <div class="absolute inset-0 bg-error-500 rounded-lg blur-lg opacity-20 group-hover:opacity-40 transition-opacity"></div>
                    <div class="relative p-3 bg-gradient-to-br from-error-50 to-red-50 dark:from-error-900/30 dark:to-red-900/30 rounded-lg ring-1 ring-error-500/20">
                        <svg class="w-6 h-6 text-error-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Rejected</p>
                    <h3 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-error-600 to-red-500 dark:from-error-400 dark:to-red-400">{{ $stats['rejected'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Berkas dengan Cyberpunk Style -->
    <div class="relative group">
        <!-- Glow effect around table -->
        <div class="absolute -inset-1 bg-gradient-to-r from-brand-500 via-blue-light-500 to-brand-500 rounded-xl blur opacity-0 group-hover:opacity-20 transition duration-500"></div>
        
        <div class="relative bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <!-- Table Header -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-white dark:from-gray-900 dark:to-gray-800">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-1 h-6 bg-gradient-to-b from-brand-500 to-blue-light-500 rounded-full"></div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Daftar Berkas</h2>
                    </div>
                    
                    <!-- Download Button with Cyber Style -->
                    <a href="{{ route('admin.unduhan.index') }}" 
                       class="group relative inline-flex items-center px-6 py-2.5 text-sm font-semibold text-white overflow-hidden rounded-lg transition-all duration-300">
                        <!-- Animated Background -->
                        <div class="absolute inset-0 bg-gradient-to-r from-brand-500 to-blue-light-500 transition-all duration-300 group-hover:scale-105"></div>
                        
                        <!-- Glow Effect -->
                        <div class="absolute inset-0 bg-gradient-to-r from-brand-400 to-blue-light-400 blur-md opacity-0 group-hover:opacity-50 transition-opacity duration-300"></div>
                        
                        <!-- Corner Highlights -->
                        <div class="absolute top-0 left-0 w-2 h-2 bg-white/50 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="absolute bottom-0 right-0 w-2 h-2 bg-white/50 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        
                        <span class="relative flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Menu Download Berkas
                        </span>
                    </a>
                </div>
            </div>

            <!-- Table Content -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <span class="w-1 h-4 bg-brand-500 rounded-full"></span>
                                    Nama
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <span class="w-1 h-4 bg-brand-500 rounded-full"></span>
                                    Email
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <span class="w-1 h-4 bg-brand-500 rounded-full"></span>
                                    Status
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <span class="w-1 h-4 bg-brand-500 rounded-full"></span>
                                    Tanggal
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <span class="w-1 h-4 bg-brand-500 rounded-full"></span>
                                    Aksi
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($pendaftaran as $item)
                        <tr class="group hover:bg-gradient-to-r hover:from-brand-50/50 hover:to-transparent dark:hover:from-brand-900/10 dark:hover:to-transparent transition-all duration-200">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-0.5 h-8 bg-brand-500 opacity-0 group-hover:opacity-100 transition-opacity rounded-full"></div>
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $item->nama }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-600 dark:text-gray-400 font-medium">{{ $item->user->email ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusConfig = [
                                        'completed' => [
                                            'bg' => 'bg-gradient-to-r from-success-50 to-success-100 dark:from-success-900/30 dark:to-success-800/30',
                                            'text' => 'text-success-700 dark:text-success-300',
                                            'ring' => 'ring-1 ring-success-500/20',
                                            'label' => 'Completed'
                                        ],
                                        'in_progress' => [
                                            'bg' => 'bg-gradient-to-r from-warning-50 to-warning-100 dark:from-warning-900/30 dark:to-warning-800/30',
                                            'text' => 'text-warning-700 dark:text-warning-300',
                                            'ring' => 'ring-1 ring-warning-500/20',
                                            'label' => 'In Progress'
                                        ],
                                        'rejected' => [
                                            'bg' => 'bg-gradient-to-r from-error-50 to-error-100 dark:from-error-900/30 dark:to-error-800/30',
                                            'text' => 'text-error-700 dark:text-error-300',
                                            'ring' => 'ring-1 ring-error-500/20',
                                            'label' => 'Rejected'
                                        ]
                                    ];
                                    $config = $statusConfig[$item->status] ?? [
                                        'bg' => 'bg-gray-100 dark:bg-gray-800',
                                        'text' => 'text-gray-800 dark:text-gray-300',
                                        'ring' => 'ring-1 ring-gray-500/20',
                                        'label' => $item->status
                                    ];
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg {{ $config['bg'] }} {{ $config['text'] }} {{ $config['ring'] }} uppercase tracking-wider">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current animate-pulse"></span>
                                    {{ $config['label'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 font-medium">
                                    <svg class="w-4 h-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    {{ $item->created_at->format('d M Y') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('admin.berkas.show', $item->id) }}" 
                                   class="group/btn relative inline-flex items-center justify-center p-2.5 text-brand-500 hover:text-brand-600 dark:text-brand-400 dark:hover:text-brand-300 rounded-lg transition-all duration-200 overflow-hidden">
                                    <!-- Hover Background -->
                                    <div class="absolute inset-0 bg-gradient-to-r from-brand-50 to-blue-light-50 dark:from-brand-900/20 dark:to-blue-light-900/20 opacity-0 group-hover/btn:opacity-100 transition-opacity rounded-lg"></div>
                                    
                                    <!-- Icon -->
                                    <svg class="relative w-5 h-5 transition-transform group-hover/btn:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center gap-4">
                                    <!-- Empty State Icon with Cyber Style -->
                                    <div class="relative">
                                        <div class="absolute inset-0 bg-gradient-to-r from-brand-500 to-blue-light-500 rounded-full blur-xl opacity-20"></div>
                                        <div class="relative p-6 bg-gradient-to-br from-gray-100 to-gray-50 dark:from-gray-800 dark:to-gray-900 rounded-2xl ring-1 ring-gray-200 dark:ring-gray-700">
                                            <svg class="w-16 h-16 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <p class="text-lg font-bold text-gray-700 dark:text-gray-300">Tidak ada berkas</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-500">Belum ada pendaftaran yang dilakukan.</p>
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
</div>

<!-- Cyber Grid Background Pattern -->
<style>
@keyframes pulse-glow {
    0%, 100% { opacity: 0.1; }
    50% { opacity: 0.2; }
}

.animate-pulse {
    animation: pulse-glow 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
@endsection

<script src="{{ asset('assets/js/bundle.js') }}"></script>