@extends('layouts.admin.app')

@section('title', 'Edit Biodata - ' . $biodata->nama_lengkap)

@section('content')
<div class="grid grid-cols-1 gap-6">
    <!-- Header Section -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-sm font-bold text-gray-900 dark:text-white">
                Edit Biodata Mahasiswa
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                Perbarui informasi biodata calon mahasiswa
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
                <a href="{{ route('admin.biodata.show', $biodata->id) }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-400 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Lihat Detail
                </a>
                
                <a href="{{ route('admin.biodata.index') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-400 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Main Form -->
    <form action="{{ route('admin.biodata.update', $biodata->id) }}" method="POST">
        @csrf
        @method('PUT')
        
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
                        <!-- Nama Lengkap -->
                        <div>
                            <label for="nama_lengkap" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Nama Lengkap <span class="text-error-500">*</span>
                            </label>
                            <input type="text" 
                                   id="nama_lengkap" 
                                   name="nama_lengkap" 
                                   value="{{ old('nama_lengkap', $biodata->nama_lengkap) }}"
                                   class="w-full px-4 py-2.5 rounded-lg border border-gray-300 bg-white text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-3 focus:ring-brand-500/10 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:placeholder-gray-500 dark:focus:border-brand-500 transition-colors"
                                   placeholder="Masukkan nama lengkap"
                                   required>
                            @error('nama_lengkap')
                                <p class="mt-1 text-sm text-error-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Grid for Tempat Lahir & Tanggal Lahir -->
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
    <!-- Tempat Lahir -->
    <div>
        <label for="tempat_lahir" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Tempat Lahir <span class="text-error-500">*</span>
        </label>
        <input type="text" 
               id="tempat_lahir" 
               name="tempat_lahir" 
               value="{{ old('tempat_lahir', $biodata->tempat_lahir) }}"
               class="w-full px-4 py-2.5 rounded-lg border border-gray-300 bg-white text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-3 focus:ring-brand-500/10 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:placeholder-gray-500 dark:focus:border-brand-500 transition-colors"
               placeholder="Kota tempat lahir"
               required>
        @error('tempat_lahir')
            <p class="mt-1 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>

    <!-- Tanggal Lahir -->
    <div>
        <label for="tanggal_lahir" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Tanggal Lahir <span class="text-error-500">*</span>
        </label>
        <input type="date" 
               id="tanggal_lahir" 
               name="tanggal_lahir" 
               value="{{ old('tanggal_lahir', \Carbon\Carbon::parse($biodata->tanggal_lahir)->format('Y-m-d')) }}"
               class="w-full px-4 py-2.5 rounded-lg border border-gray-300 bg-white text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-3 focus:ring-brand-500/10 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:placeholder-gray-500 dark:focus:border-brand-500 transition-colors input-date-icon"
               required>
        @error('tanggal_lahir')
            <p class="mt-1 text-sm text-error-500">{{ $message }}</p>
        @enderror
    </div>
</div>

                        <!-- Grid for Nama Ibu & Ayah -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                            <!-- Nama Ibu -->
                            <div>
                                <label for="nama_ibu" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Nama Ibu <span class="text-error-500">*</span>
                                </label>
                                <input type="text" 
                                       id="nama_ibu" 
                                       name="nama_ibu" 
                                       value="{{ old('nama_ibu', $biodata->nama_ibu) }}"
                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 bg-white text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-3 focus:ring-brand-500/10 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:placeholder-gray-500 dark:focus:border-brand-500 transition-colors"
                                       placeholder="Nama lengkap ibu"
                                       required>
                                @error('nama_ibu')
                                    <p class="mt-1 text-sm text-error-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Nama Ayah -->
                            <div>
                                <label for="nama_ayah" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Nama Ayah <span class="text-error-500">*</span>
                                </label>
                                <input type="text" 
                                       id="nama_ayah" 
                                       name="nama_ayah" 
                                       value="{{ old('nama_ayah', $biodata->nama_ayah) }}"
                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 bg-white text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-3 focus:ring-brand-500/10 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:placeholder-gray-500 dark:focus:border-brand-500 transition-colors"
                                       placeholder="Nama lengkap ayah"
                                       required>
                                @error('nama_ayah')
                                    <p class="mt-1 text-sm text-error-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Alamat -->
                        <div>
                            <label for="alamat" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Alamat Lengkap <span class="text-error-500">*</span>
                            </label>
                            <textarea id="alamat" 
                                      name="alamat" 
                                      rows="4"
                                      class="w-full px-4 py-2.5 rounded-lg border border-gray-300 bg-white text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-3 focus:ring-brand-500/10 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:placeholder-gray-500 dark:focus:border-brand-500 transition-colors resize-none"
                                      placeholder="Masukkan alamat lengkap"
                                      required>{{ old('alamat', $biodata->alamat) }}</textarea>
                            @error('alamat')
                                <p class="mt-1 text-sm text-error-500">{{ $message }}</p>
                            @enderror
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
                    <div class="p-6 space-y-6">
                        <!-- Jenjang Pendidikan -->
                        <div>
                            <label for="jenjang_pendidikan" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Jenjang Pendidikan <span class="text-error-500">*</span>
                            </label>
                            <select id="jenjang_pendidikan" 
                                    name="jenjang_pendidikan"
                                    class="w-full px-4 py-2.5 rounded-lg border border-gray-300 bg-white text-sm text-gray-900 focus:border-brand-500 focus:ring-3 focus:ring-brand-500/10 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:focus:border-brand-500 transition-colors appearance-none"
                                    required>
                                <option value="">Pilih Jenjang Pendidikan</option>
                                @foreach($jenjangPendidikan as $jenjang)
                                    <option value="{{ $jenjang }}" 
                                            {{ old('jenjang_pendidikan', $biodata->jenjang_pendidikan) == $jenjang ? 'selected' : '' }}>
                                        {{ $jenjang }}
                                    </option>
                                @endforeach
                            </select>
                            @error('jenjang_pendidikan')
                                <p class="mt-1 text-sm text-error-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Asal Sekolah -->
                        <div>
                            <label for="asal_sekolah" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Asal Sekolah <span class="text-error-500">*</span>
                            </label>
                            <input type="text" 
                                   id="asal_sekolah" 
                                   name="asal_sekolah" 
                                   value="{{ old('asal_sekolah', $biodata->asal_sekolah) }}"
                                   class="w-full px-4 py-2.5 rounded-lg border border-gray-300 bg-white text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-3 focus:ring-brand-500/10 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:placeholder-gray-500 dark:focus:border-brand-500 transition-colors"
                                   placeholder="Nama sekolah asal"
                                   required>
                            @error('asal_sekolah')
                                <p class="mt-1 text-sm text-error-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Jurusan Sekolah -->
                        <div>
                            <label for="jurusan_sekolah" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Jurusan Sekolah <span class="text-error-500">*</span>
                            </label>
                            <input type="text" 
                                   id="jurusan_sekolah" 
                                   name="jurusan_sekolah" 
                                   value="{{ old('jurusan_sekolah', $biodata->jurusan_sekolah) }}"
                                   class="w-full px-4 py-2.5 rounded-lg border border-gray-300 bg-white text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-3 focus:ring-brand-500/10 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:placeholder-gray-500 dark:focus:border-brand-500 transition-colors"
                                   placeholder="Jurusan di sekolah"
                                   required>
                            @error('jurusan_sekolah')
                                <p class="mt-1 text-sm text-error-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Additional Info & Actions -->
            <div class="space-y-6">
                <!-- Additional Information Card -->
                <div class="rounded-2xl bg-white shadow-theme-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Informasi Tambahan
                        </h3>
                    </div>
                    <div class="p-6 space-y-6">
                        <!-- Agama -->
                        <div>
                            <label for="agama" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Agama <span class="text-error-500">*</span>
                            </label>
                            <input type="text" 
                                   id="agama" 
                                   name="agama" 
                                   value="{{ old('agama', $biodata->agama) }}"
                                   class="w-full px-4 py-2.5 rounded-lg border border-gray-300 bg-white text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-3 focus:ring-brand-500/10 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:placeholder-gray-500 dark:focus:border-brand-500 transition-colors"
                                   placeholder="Agama"
                                   required>
                            @error('agama')
                                <p class="mt-1 text-sm text-error-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tempat Tinggal -->
                        <div>
                            <label for="tempat_tinggal" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Tempat Tinggal <span class="text-error-500">*</span>
                            </label>
                            <input type="text" 
                                   id="tempat_tinggal" 
                                   name="tempat_tinggal" 
                                   value="{{ old('tempat_tinggal', $biodata->tempat_tinggal) }}"
                                   class="w-full px-4 py-2.5 rounded-lg border border-gray-300 bg-white text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-3 focus:ring-brand-500/10 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:placeholder-gray-500 dark:focus:border-brand-500 transition-colors"
                                   placeholder="Tempat tinggal saat ini"
                                   required>
                            @error('tempat_tinggal')
                                <p class="mt-1 text-sm text-error-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Verification Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Status Verifikasi
                            </label>
                            <div class="flex items-center gap-3">
                                <label class="flex items-center">
                                    <input type="checkbox" 
                                           name="is_verified" 
                                           value="1"
                                           {{ old('is_verified', $biodata->is_verified) ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-brand-500 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-700">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                        Tandai sebagai terverifikasi
                                    </span>
                                </label>
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Centang untuk memverifikasi biodata ini
                            </p>
                        </div>
                    </div>
                </div>

                <!-- User Account Info -->
                <div class="rounded-2xl bg-white shadow-theme-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Informasi Akun
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
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="rounded-2xl bg-white shadow-theme-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Tindakan
                        </h3>
                    </div>
                    <div class="p-6 space-y-3">
                        <button type="submit" 
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg border border-brand-500 bg-brand-500 text-sm font-medium text-white hover:bg-brand-600 hover:border-brand-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Simpan Perubahan
                        </button>
                        
                        <a href="{{ route('admin.biodata.show', $biodata->id) }}" 
                           class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-400 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Batal
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
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
                    Terdapat kesalahan dalam pengisian form. Silakan periksa kembali.
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
<script>
    // Date input formatting
    document.addEventListener('DOMContentLoaded', function() {
        const dateInput = document.getElementById('tanggal_lahir');
        if (dateInput) {
            // Format tanggal untuk display yang lebih baik
            dateInput.addEventListener('focus', function() {
                this.type = 'date';
            });
            
            dateInput.addEventListener('blur', function() {
                if (!this.value) {
                    this.type = 'text';
                }
            });
        }

        // Auto-capitalize for certain fields
        const capitalizeFields = ['nama_lengkap', 'tempat_lahir', 'nama_ibu', 'nama_ayah', 'asal_sekolah', 'jurusan_sekolah', 'agama', 'tempat_tinggal'];
        
        capitalizeFields.forEach(fieldName => {
            const field = document.getElementById(fieldName);
            if (field) {
                field.addEventListener('input', function() {
                    this.value = this.value.charAt(0).toUpperCase() + this.value.slice(1);
                });
            }
        });
    });
</script>
<script src="{{ asset('assets/js/bundle.js') }}"></script>