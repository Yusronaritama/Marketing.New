@extends('layouts.admin.app')

@section('title', 'Tambah Program Studi')

@section('content')

<style>
    .cyber-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
    }
    
    .dark .cyber-card {
        background: #1f2937;
        border: 1px solid #374151;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
    }
    
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
        background: linear-gradient(180deg, #4f46e5, #818cf8);
        border-radius: 2px;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .cyber-glow-side:hover::before {
        opacity: 1;
    }
    
    .form-input {
        @apply block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2.5 text-theme-sm text-gray-900 dark:text-white placeholder-gray-500 focus:border-blue-500 dark:focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:focus:ring-blue-500;
    }
    
    .form-label {
        @apply block text-theme-sm font-medium text-gray-700 dark:text-gray-300 mb-2;
    }
    
    .form-select {
        @apply block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2.5 text-theme-sm text-gray-900 dark:text-white focus:border-blue-500 dark:focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:focus:ring-blue-500;
    }
</style>

<div class="grid grid-cols-12 gap-4 md:gap-6">
    <div class="col-span-12">
        <!-- Header -->
        <div class="cyber-card cyber-glow-side rounded-xl px-4 pb-3 pt-4 sm:px-6 mb-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Tambah Program Studi
                    </h3>
                    <p class="mt-1 text-gray-600 dark:text-gray-400 text-theme-sm">
                        Tambah data program studi baru
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.prodi.index') }}" class="inline-flex items-center gap-2 rounded-lg bg-gray-600 px-4 py-2.5 text-theme-sm font-medium text-white hover:bg-gray-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Kembali
                    </a>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="cyber-card cyber-glow-side rounded-xl px-4 pb-3 pt-4 sm:px-6">
            <form action="{{ route('admin.prodi.store') }}" method="POST">
                @csrf
                
                <!-- Alert Error -->
                @if($errors->any())
                <div class="mb-6 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h4 class="text-sm font-medium text-red-800 dark:text-red-400">
                            Terdapat kesalahan dalam pengisian form
                        </h4>
                    </div>
                    <ul class="mt-2 text-sm text-red-700 dark:text-red-300 list-disc list-inside">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <!-- Kode Jurusan -->
                    <div>
                        <label for="kode_jurusan" class="form-label">
                            Kode Jurusan <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="kode_jurusan" 
                               name="kode_jurusan" 
                               value="{{ old('kode_jurusan') }}"
                               class="form-input @error('kode_jurusan') border-red-500 dark:border-red-500 @enderror"
                               placeholder="Contoh: 55201"
                               required>
                        @error('kode_jurusan')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nama Jurusan -->
                    <div>
                        <label for="nama_jurusan" class="form-label">
                            Nama Program Studi <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="nama_jurusan" 
                               name="nama_jurusan" 
                               value="{{ old('nama_jurusan') }}"
                               class="form-input @error('nama_jurusan') border-red-500 dark:border-red-500 @enderror"
                               placeholder="Contoh: Teknik Informatika"
                               required>
                        @error('nama_jurusan')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Passing Grade -->
                    <div>
                        <label for="passing_grade" class="form-label">
                            Passing Grade <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" 
                                   id="passing_grade" 
                                   name="passing_grade" 
                                   value="{{ old('passing_grade') }}"
                                   class="form-input pr-12 @error('passing_grade') border-red-500 dark:border-red-500 @enderror"
                                   placeholder="0.00"
                                   step="0.01"
                                   min="0"
                                   max="100"
                                   required>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <span class="text-gray-500 text-theme-sm">%</span>
                            </div>
                        </div>
                        @error('passing_grade')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Nilai passing grade dalam persentase (0.00 - 100.00)
                        </p>
                    </div>

                    <!-- Kuota -->
                    <div>
                        <label for="kuota" class="form-label">
                            Kuota <span class="text-red-500">*</span>
                        </label>
                        <input type="number" 
                               id="kuota" 
                               name="kuota" 
                               value="{{ old('kuota') }}"
                               class="form-input @error('kuota') border-red-500 dark:border-red-500 @enderror"
                               placeholder="Contoh: 200"
                               min="0"
                               required>
                        @error('kuota')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Jumlah kuota mahasiswa yang diterima
                        </p>
                    </div>

                    <!-- Status -->
                    <div class="md:col-span-2">
                        <label for="status" class="form-label">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select id="status" 
                                name="status" 
                                class="form-select @error('status') border-red-500 dark:border-red-500 @enderror"
                                required>
                            <option value="">Pilih Status</option>
                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                        </select>
                        @error('status')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Status program studi untuk penerimaan mahasiswa baru
                        </p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-3 mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('admin.prodi.index') }}" class="inline-flex items-center gap-2 rounded-lg bg-gray-600 px-6 py-2.5 text-theme-sm font-medium text-white hover:bg-gray-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Batal
                    </a>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-6 py-2.5 text-theme-sm font-medium text-white hover:bg-blue-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Program Studi
                    </button>
                </div>
            </form>
        </div>

        <!-- Info Card -->
        <div class="cyber-card cyber-glow-side rounded-xl px-4 pb-3 pt-4 sm:px-6 mt-6">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                        Informasi Program Studi
                    </h4>
                    <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        <ul class="list-disc list-inside space-y-1">
                            <li><strong>Kode Jurusan</strong> harus unik dan tidak boleh duplikat</li>
                            <li><strong>Passing Grade</strong> adalah nilai minimum yang harus dicapai untuk diterima</li>
                            <li><strong>Kuota</strong> adalah jumlah maksimal mahasiswa yang dapat diterima</li>
                            <li>Program studi dengan status <strong>Non-Aktif</strong> tidak akan muncul di pilihan pendaftaran</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Format passing grade input
document.getElementById('passing_grade').addEventListener('blur', function(e) {
    let value = parseFloat(e.target.value);
    if (!isNaN(value)) {
        // Batasi antara 0-100
        if (value < 0) value = 0;
        if (value > 100) value = 100;
        e.target.value = value.toFixed(2);
    }
});

// Format kuota input
document.getElementById('kuota').addEventListener('blur', function(e) {
    let value = parseInt(e.target.value);
    if (!isNaN(value)) {
        // Pastikan tidak negatif
        if (value < 0) value = 0;
        e.target.value = value;
    }
});

// Validasi form sebelum submit
document.querySelector('form').addEventListener('submit', function(e) {
    const kodeJurusan = document.getElementById('kode_jurusan').value.trim();
    const namaJurusan = document.getElementById('nama_jurusan').value.trim();
    const passingGrade = document.getElementById('passing_grade').value;
    const kuota = document.getElementById('kuota').value;
    const status = document.getElementById('status').value;

    let isValid = true;
    let errorMessage = '';

    if (!kodeJurusan) {
        isValid = false;
        errorMessage = 'Kode jurusan harus diisi';
    } else if (!namaJurusan) {
        isValid = false;
        errorMessage = 'Nama program studi harus diisi';
    } else if (!passingGrade || passingGrade < 0 || passingGrade > 100) {
        isValid = false;
        errorMessage = 'Passing grade harus antara 0.00 - 100.00';
    } else if (!kuota || kuota < 0) {
        isValid = false;
        errorMessage = 'Kuota harus diisi dan tidak boleh negatif';
    } else if (!status) {
        isValid = false;
        errorMessage = 'Status harus dipilih';
    }

    if (!isValid) {
        e.preventDefault();
        alert('Error: ' + errorMessage);
    }
});
</script>

@endsection