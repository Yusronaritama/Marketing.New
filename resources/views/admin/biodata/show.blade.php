@extends('layouts.admin.app')

@section('title', 'Detail Biodata - ' . $biodata->nama_lengkap)

@section('content')
<div class="grid grid-cols-1 gap-6">
    <!-- Header Section -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-sm font-bold text-gray-900 dark:text-white">
                Detail Biodata Mahasiswa
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                Informasi lengkap biodata calon mahasiswa
            </p>
        </div>
        
        <div class="flex items-center gap-3">
            <!-- Status Badge -->
            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium 
                {{ $biodata->is_verified ? 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500' : 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-500' }}">
                <span class="w-1.5 h-1.5 rounded-full 
                    {{ $biodata->is_verified ? 'bg-success-500' : 'bg-warning-500' }}"></span>
                {{ $biodata->is_verified ? 'Terverifikasi' : 'Pending' }}
            </div>
            
            <!-- Action Buttons -->
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.biodata.edit', $biodata->id) }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-400 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit
                </a>
                
                <form action="{{ route('admin.biodata.destroy', $biodata->id) }}" method="POST" 
                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus biodata ini?')" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-error-300 bg-error-50 text-sm font-medium text-error-700 hover:bg-error-100 hover:border-error-400 dark:border-error-700 dark:bg-error-500/15 dark:text-error-500 dark:hover:bg-error-500/25 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Personal Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Personal Information Card -->
            <div class="rounded-2xl bg-white shadow-theme-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Informasi Pribadi
                    </h3>
                </div>
                <div class="p-6 space-y-6">
                    <!-- Grid for personal info -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama Lengkap</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white font-medium">
                                {{ $biodata->nama_lengkap }}
                            </p>
                        </div>
                        
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Tempat, Tanggal Lahir</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $biodata->tempat_lahir }}, {{ \Carbon\Carbon::parse($biodata->tanggal_lahir)->format('d/m/Y') }}
                            </p>
                        </div>
                        
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama Ibu</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $biodata->nama_ibu }}
                            </p>
                        </div>
                        
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama Ayah</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $biodata->nama_ayah }}
                            </p>
                        </div>
                        
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Agama</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $biodata->agama }}
                            </p>
                        </div>
                        
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Tempat Tinggal</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $biodata->tempat_tinggal }}
                            </p>
                        </div>
                    </div>
                    
                    <!-- Alamat -->
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Alamat Lengkap</label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white leading-relaxed">
                            {{ $biodata->alamat }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Education Information Card -->
            <div class="rounded-2xl bg-white shadow-theme-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Informasi Pendidikan
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Jenjang Pendidikan</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white font-medium">
                                {{ $biodata->jenjang_pendidikan }}
                            </p>
                        </div>
                        
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Asal Sekolah</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $biodata->asal_sekolah }}
                            </p>
                        </div>
                        
                        <div class="sm:col-span-2">
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Jurusan Sekolah</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $biodata->jurusan_sekolah }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - User Info & Actions -->
        <div class="space-y-6">
            <!-- User Account Card -->
            <div class="rounded-2xl bg-white shadow-theme-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Akun Pengguna
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white break-all">
                            {{ $biodata->user->email ?? 'N/A' }}
                        </p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Username</label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $biodata->user->name ?? 'N/A' }}
                        </p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Terdaftar Sejak</label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $biodata->user->created_at ? $biodata->user->created_at->format('d M Y') : 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Verification Actions Card -->
            <div class="rounded-2xl bg-white shadow-theme-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Verifikasi Biodata
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Status Verifikasi</span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium 
                            {{ $biodata->is_verified ? 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500' : 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-500' }}">
                            {{ $biodata->is_verified ? 'Terverifikasi' : 'Pending' }}
                        </span>
                    </div>
                    
                    <div class="flex flex-col gap-2">
                        @if($biodata->is_verified)
                            <form action="{{ route('admin.biodata.unverify', $biodata->id) }}" method="POST" class="w-full">
                                @csrf
                                <button type="submit" 
                                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg border border-warning-300 bg-warning-50 text-sm font-medium text-warning-700 hover:bg-warning-100 hover:border-warning-400 dark:border-warning-700 dark:bg-warning-500/15 dark:text-warning-500 dark:hover:bg-warning-500/25 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    Batalkan Verifikasi
                                </button>
                            </form>
                        @else
                            <form action="{{ route('admin.biodata.verify', $biodata->id) }}" method="POST" class="w-full">
                                @csrf
                                <button type="submit" 
                                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg border border-success-300 bg-success-50 text-sm font-medium text-success-700 hover:bg-success-100 hover:border-success-400 dark:border-success-700 dark:bg-success-500/15 dark:text-success-500 dark:hover:bg-success-500/25 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Verifikasi Biodata
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="rounded-2xl bg-white shadow-theme-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Tindakan Cepat
                    </h3>
                </div>
                <div class="p-6 space-y-3">
                    <a href="{{ route('admin.biodata.index') }}" 
                       class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-400 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Kembali ke Daftar
                    </a>
                    
                    <a href="{{ route('admin.biodata.edit', $biodata->id) }}" 
                       class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg border border-brand-500 bg-brand-50 text-sm font-medium text-brand-700 hover:bg-brand-100 hover:border-brand-600 dark:border-brand-500 dark:bg-brand-500/15 dark:text-brand-400 dark:hover:bg-brand-500/25 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit Biodata
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
        <div class="text-sm text-gray-500 dark:text-gray-400">
            Terakhir diperbarui: {{ $biodata->updated_at->format('d M Y H:i') }}
        </div>
        
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.biodata.index') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-400 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali
            </a>
            
            <a href="{{ route('admin.biodata.edit', $biodata->id) }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-brand-500 bg-brand-500 text-sm font-medium text-white hover:bg-brand-600 hover:border-brand-600 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit Biodata
            </a>
        </div>
    </div>
</div>

<!-- Success/Error Messages -->
@if(session('success'))
<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
     class="fixed top-5 right-5 z-50 max-w-sm w-full">
    <div class="rounded-lg bg-success-50 border border-success-200 p-4 shadow-theme-sm dark:bg-success-500/15 dark:border-success-500/30">
        <div class="flex items-center gap-3">
            <div class="flex-shrink-0">
                <svg class="w-5 h-5 text-success-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="flex-1">
                <p class="text-sm font-medium text-success-800 dark:text-success-400">
                    {{ session('success') }}
                </p>
            </div>
            <button @click="show = false" class="flex-shrink-0 ml-auto">
                <svg class="w-4 h-4 text-success-500 hover:text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
</div>
@endif

@if($errors->any())
<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
     class="fixed top-5 right-5 z-50 max-w-sm w-full">
    <div class="rounded-lg bg-error-50 border border-error-200 p-4 shadow-theme-sm dark:bg-error-500/15 dark:border-error-500/30">
        <div class="flex items-center gap-3">
            <div class="flex-shrink-0">
                <svg class="w-5 h-5 text-error-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="flex-1">
                <p class="text-sm font-medium text-error-800 dark:text-error-400">
                    Terjadi kesalahan. Silakan coba lagi.
                </p>
            </div>
            <button @click="show = false" class="flex-shrink-0 ml-auto">
                <svg class="w-4 h-4 text-error-500 hover:text-error-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
</div>
@endif
@endsection
<script src="{{ asset('assets/js/bundle.js') }}"></script>