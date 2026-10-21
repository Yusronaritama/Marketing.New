{{-- resources/views/admin/berkas/download.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Menu Download Berkas')

@section('content')
<div class="px-6 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Menu Download Berkas</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">Download data dan berkas peserta dalam berbagai format</p>
    </div>

    <!-- Statistik -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <!-- Total Berkas -->
        <div class="flex items-center gap-4 bg-white dark:bg-gray-800 rounded-xl shadow-theme-sm p-4">
            <div class="p-3 bg-blue-light-50 dark:bg-blue-light-900 rounded-lg">
                <svg class="w-6 h-6 text-blue-light-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Berkas</p>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</h3>
            </div>
        </div>

        <!-- Completed -->
        <div class="flex items-center gap-4 bg-white dark:bg-gray-800 rounded-xl shadow-theme-sm p-4">
            <div class="p-3 bg-success-50 dark:bg-success-900 rounded-lg">
                <svg class="w-6 h-6 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Completed</p>
                <h3 class="text-2xl font-bold text-success-600 dark:text-success-400">{{ $stats['completed'] }}</h3>
            </div>
        </div>

        <!-- In Progress -->
        <div class="flex items-center gap-4 bg-white dark:bg-gray-800 rounded-xl shadow-theme-sm p-4">
            <div class="p-3 bg-warning-50 dark:bg-warning-900 rounded-lg">
                <svg class="w-6 h-6 text-warning-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">In Progress</p>
                <h3 class="text-2xl font-bold text-warning-600 dark:text-warning-400">{{ $stats['in_progress'] }}</h3>
            </div>
        </div>

        <!-- Rejected -->
        <div class="flex items-center gap-4 bg-white dark:bg-gray-800 rounded-xl shadow-theme-sm p-4">
            <div class="p-3 bg-error-50 dark:bg-error-900 rounded-lg">
                <svg class="w-6 h-6 text-error-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Rejected</p>
                <h3 class="text-2xl font-bold text-error-600 dark:text-error-400">{{ $stats['rejected'] }}</h3>
            </div>
        </div>
    </div>

    <!-- Menu Download Options -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Download Data Users -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-theme-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Download Data Users</h3>
            <div class="space-y-3">
                <a href="{{ route('admin.berkas.download.all-users') }}" 
                   class="w-full flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-50 dark:bg-blue-900 rounded-lg">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">Semua Users</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Download data semua user terdaftar</p>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>

                <a href="{{ route('admin.berkas.download.users-biodata') }}" 
                   class="w-full flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-green-50 dark:bg-green-900 rounded-lg">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">Users dengan Biodata</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">User yang sudah mengisi biodata</p>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>

                <a href="{{ route('admin.berkas.download.complete-users') }}" 
                   class="w-full flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-purple-50 dark:bg-purple-900 rounded-lg">
                            <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">Users Lengkap</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">User dengan biodata + registrasi ulang</p>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        </div>

        <!-- Download Berkas & Filter -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-theme-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Download Berkas & Filter</h3>
            <div class="space-y-3">
                <!-- Download All Berkas -->
                <a href="{{ route('admin.berkas.download.all') }}" 
                   class="w-full flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-orange-50 dark:bg-orange-900 rounded-lg">
                            <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">Semua Berkas (ZIP)</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Download semua file + data Excel</p>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>

                <!-- Download by Status -->
                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('admin.berkas.download.by-status', 'completed') }}" 
                       class="flex items-center justify-between p-3 border border-success-200 dark:border-success-800 rounded-lg hover:bg-success-50 dark:hover:bg-success-900/20 transition-colors">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 bg-success-500 rounded-full"></div>
                            <span class="text-sm font-medium text-success-700 dark:text-success-300">Completed</span>
                        </div>
                        <svg class="w-4 h-4 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3"></path>
                        </svg>
                    </a>

                    <a href="{{ route('admin.berkas.download.by-status', 'in_progress') }}" 
                       class="flex items-center justify-between p-3 border border-warning-200 dark:border-warning-800 rounded-lg hover:bg-warning-50 dark:hover:bg-warning-900/20 transition-colors">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 bg-warning-500 rounded-full"></div>
                            <span class="text-sm font-medium text-warning-700 dark:text-warning-300">In Progress</span>
                        </div>
                        <svg class="w-4 h-4 text-warning-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3"></path>
                        </svg>
                    </a>

                    <a href="{{ route('admin.berkas.download.by-status', 'rejected') }}" 
                       class="flex items-center justify-between p-3 border border-error-200 dark:border-error-800 rounded-lg hover:bg-error-50 dark:hover:bg-error-900/20 transition-colors">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 bg-error-500 rounded-full"></div>
                            <span class="text-sm font-medium text-error-700 dark:text-error-300">Rejected</span>
                        </div>
                        <svg class="w-4 h-4 text-error-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3"></path>
                        </svg>
                    </a>
                </div>

                <!-- Download by Gelombang -->
                <form action="{{ route('admin.berkas.download.by-gelombang') }}" method="POST" class="pt-2">
                    @csrf
                    <div class="flex gap-2">
                        <select name="gelombang" required
                                class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                            <option value="">Pilih Gelombang</option>
                            @foreach($gelombangList as $gelombang)
                                <option value="{{ $gelombang }}">{{ $gelombang }}</option>
                            @endforeach
                        </select>
                        <button type="submit"
                                class="px-4 py-2 bg-brand-500 text-white rounded-lg hover:bg-brand-600 transition-colors text-sm font-medium flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3"></path>
                            </svg>
                            Download
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-theme-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('admin.berkas.index') }}" 
               class="flex items-center gap-3 p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <div class="p-2 bg-gray-100 dark:bg-gray-600 rounded-lg">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">Kembali ke Berkas</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Lihat daftar berkas</p>
                </div>
            </a>

            <a href="{{ route('admin.dashboard.index') }}" 
               class="flex items-center gap-3 p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <div class="p-2 bg-blue-100 dark:bg-blue-600 rounded-lg">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">Dashboard</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Kembali ke dashboard</p>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection

<script>
    // Tambahkan loading state untuk tombol download
    document.addEventListener('DOMContentLoaded', function() {
        const downloadLinks = document.querySelectorAll('a[href*="download"]');
        downloadLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                // Tambahkan loading indicator
                const originalText = this.innerHTML;
                this.innerHTML = `
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                        <span>Loading...</span>
                    </div>
                `;
                this.classList.add('opacity-50', 'pointer-events-none');
                
                // Reset setelah 3 detik (fallback)
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.classList.remove('opacity-50', 'pointer-events-none');
                }, 3000);
            });
        });
    });
</script>
<script src="{{ asset('assets/js/bundle.js') }}"></script>