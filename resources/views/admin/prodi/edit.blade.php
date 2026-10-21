@extends('layouts.admin.app')

@section('title', 'Edit Program Studi')

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
    
    .cyber-btn {
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .cyber-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(79, 70, 229, 0.2);
    }
    
    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        font-size: 0.875rem;
        color: #374151;
    }
    
    .dark .form-label {
        color: #d1d5db;
    }
    
    .form-input {
        display: block;
        width: 100%;
        padding: 0.625rem;
        font-size: 0.875rem;
        line-height: 1.25rem;
        color: #374151;
        background-color: #ffffff;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    
    .dark .form-input {
        color: #d1d5db;
        background-color: #1f2937;
        border-color: #4b5563;
    }
    
    .form-input:focus {
        outline: none;
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }
    
    .dark .form-input:focus {
        border-color: #818cf8;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
    }
    
    .form-select {
        display: block;
        width: 100%;
        padding: 0.625rem;
        font-size: 0.875rem;
        line-height: 1.25rem;
        color: #374151;
        background-color: #ffffff;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.5rem center;
        background-repeat: no-repeat;
        background-size: 1.5em 1.5em;
    }
    
    .dark .form-select {
        color: #d1d5db;
        background-color: #1f2937;
        border-color: #4b5563;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%239ca3af' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    }
    
    .form-select:focus {
        outline: none;
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }
    
    .dark .form-select:focus {
        border-color: #818cf8;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
    }
    
    .form-textarea {
        display: block;
        width: 100%;
        padding: 0.625rem;
        font-size: 0.875rem;
        line-height: 1.25rem;
        color: #374151;
        background-color: #ffffff;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        resize: vertical;
        min-height: 6rem;
    }
    
    .dark .form-textarea {
        color: #d1d5db;
        background-color: #1f2937;
        border-color: #4b5563;
    }
    
    .form-textarea:focus {
        outline: none;
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }
    
    .dark .form-textarea:focus {
        border-color: #818cf8;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
    }
    
    .cyber-data {
        margin-bottom: 1rem;
    }
    
    .cyber-text {
        position: relative;
        display: inline-block;
    }
    
    .cyber-text::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 0;
        height: 2px;
        background: linear-gradient(90deg, #4f46e5, #818cf8);
        transition: width 0.3s ease;
    }
    
    .cyber-text:hover::after {
        width: 100%;
    }
    
    .border-red-500 {
        border-color: #ef4444 !important;
    }
    
    .dark .border-red-500 {
        border-color: #dc2626 !important;
    }
    
    .text-red-500 {
        color: #ef4444;
    }
    
    .dark .text-red-500 {
        color: #f87171;
    }
    
    .text-red-600 {
        color: #dc2626;
    }
    
    .dark .text-red-600 {
        color: #fca5a5;
    }
    
    .text-red-400 {
        color: #f87171;
    }
    
    .dark .text-red-400 {
        color: #fca5a5;
    }
    
    .cyber-border {
        position: relative;
        border-radius: 0.75rem;
        overflow: hidden;
    }
    
    .cyber-border::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, #ef4444, #dc2626, #f87171);
        opacity: 0.8;
    }
    
    .cyber-grid {
        background-image: 
            linear-gradient(rgba(79, 70, 229, 0.05) 1px, transparent 1px),
            linear-gradient(90deg, rgba(79, 70, 229, 0.05) 1px, transparent 1px);
        background-size: 20px 20px;
    }
    
    .dark .cyber-grid {
        background-image: 
            linear-gradient(rgba(99, 102, 241, 0.1) 1px, transparent 1px),
            linear-gradient(90deg, rgba(99, 102, 241, 0.1) 1px, transparent 1px);
    }
    
    .cyber-scan {
        position: relative;
        overflow: hidden;
    }
    
    .cyber-scan::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, #4f46e5, transparent);
        animation: scan 2s linear infinite;
        opacity: 0.5;
    }
    
    @keyframes scan {
        0% {
            top: 0;
        }
        100% {
            top: 100%;
        }
    }
    
    .cyber-input {
        transition: all 0.3s ease;
    }
    
    .cyber-input:hover {
        border-color: #4f46e5;
        transform: translateY(-1px);
    }
    
    .dark .cyber-input:hover {
        border-color: #818cf8;
    }
    
    /* Animasi untuk section headers */
    h4.text-md {
        position: relative;
        padding-left: 1rem;
    }
    
    h4.text-md::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 4px;
        height: 80%;
        background: linear-gradient(180deg, #4f46e5, #818cf8);
        border-radius: 2px;
    }
    
    /* Icon colors */
    .fa-info-circle {
        color: #3b82f6;
    }
    
    .fa-chart-line {
        color: #10b981;
    }
    
    .fa-money-bill-wave {
        color: #f59e0b;
    }
    
    .fa-cog {
        color: #8b5cf6;
    }
    
    .dark .fa-info-circle {
        color: #60a5fa;
    }
    
    .dark .fa-chart-line {
        color: #34d399;
    }
    
    .dark .fa-money-bill-wave {
        color: #fbbf24;
    }
    
    .dark .fa-cog {
        color: #a78bfa;
    }
    
    /* Style untuk input harga besar */
    .harga-input {
        font-family: 'Courier New', monospace;
        font-weight: bold;
    }
    
    .harga-preview {
        font-size: 0.75rem;
        margin-top: 0.25rem;
        padding: 0.25rem 0.5rem;
        background: #f3f4f6;
        border-radius: 0.375rem;
        border: 1px solid #e5e7eb;
        display: inline-block;
    }
    
    .dark .harga-preview {
        background: #374151;
        border-color: #4b5563;
        color: #d1d5db;
    }
    
    .currency-symbol {
        font-weight: bold;
        color: #059669;
    }
    
    .dark .currency-symbol {
        color: #34d399;
    }
</style>

<div class="grid grid-cols-12 gap-4 md:gap-6">
    <div class="col-span-12">
        <!-- Header dengan efek cyber -->
        <div class="cyber-card cyber-glow-side cyber-grid rounded-xl px-4 pb-3 pt-4 sm:px-6 mb-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white cyber-text">
                        Edit Program Studi
                    </h3>
                    <p class="mt-1 text-gray-600 dark:text-gray-400 text-theme-sm">
                        Edit data program studi lengkap
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.prodi.index') }}" 
                       class="cyber-btn inline-flex items-center gap-2 rounded-lg bg-gray-600 px-4 py-2.5 text-theme-sm font-medium text-white hover:bg-gray-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Kembali
                    </a>
                </div>
            </div>
        </div>

        <!-- Form dengan efek cyber -->
        <div class="cyber-card cyber-glow-side cyber-grid cyber-scan rounded-xl px-4 pb-3 pt-4 sm:px-6">
            <form action="{{ route('admin.prodi.update', $prodi->id) }}" method="POST" id="prodiForm">
                @csrf
                @method('PUT')
                
                <!-- Alert Error dengan efek cyber -->
                @if($errors->any())
                <div class="cyber-border mb-6 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4">
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

                <!-- Informasi Dasar -->
                <div class="mb-8">
                    <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                        <i class="fas fa-info-circle mr-2 text-blue-500"></i> Informasi Dasar
                    </h4>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Kode Prodi -->
                        <div class="cyber-data">
                            <label for="kode_prodi" class="form-label">
                                Kode Program Studi <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="kode_prodi" 
                                   name="kode_prodi" 
                                   value="{{ old('kode_prodi', $prodi->kode_prodi) }}"
                                   class="form-input cyber-input @error('kode_prodi') border-red-500 dark:border-red-500 @enderror"
                                   placeholder="Contoh: 21201"
                                   required>
                            @error('kode_prodi')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Nama Prodi -->
                        <div class="cyber-data">
                            <label for="nama_prodi" class="form-label">
                                Nama Program Studi <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="nama_prodi" 
                                   name="nama_prodi" 
                                   value="{{ old('nama_prodi', $prodi->nama_prodi) }}"
                                   class="form-input cyber-input @error('nama_prodi') border-red-500 dark:border-red-500 @enderror"
                                   placeholder="Contoh: Teknik Mesin"
                                   required>
                            @error('nama_prodi')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Jenjang -->
                        <div class="cyber-data">
                            <label for="jenjang" class="form-label">
                                Jenjang Pendidikan <span class="text-red-500">*</span>
                            </label>
                            <select id="jenjang" 
                                    name="jenjang" 
                                    class="form-select cyber-input @error('jenjang') border-red-500 dark:border-red-500 @enderror"
                                    required>
                                <option value="">Pilih Jenjang</option>
                                <option value="D3" {{ old('jenjang', $prodi->jenjang) == 'D3' ? 'selected' : '' }}>Diploma 3 (D3)</option>
                                <option value="S1" {{ old('jenjang', $prodi->jenjang) == 'S1' ? 'selected' : '' }}>Strata 1 (S1)</option>
                                <option value="S2" {{ old('jenjang', $prodi->jenjang) == 'S2' ? 'selected' : '' }}>Strata 2 (S2)</option>
                                <option value="S3" {{ old('jenjang', $prodi->jenjang) == 'S3' ? 'selected' : '' }}>Strata 3 (S3)</option>
                            </select>
                            @error('jenjang')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Fakultas -->
                        <div class="cyber-data">
                            <label for="fakultas" class="form-label">
                                Fakultas
                            </label>
                            <input type="text" 
                                   id="fakultas" 
                                   name="fakultas" 
                                   value="{{ old('fakultas', $prodi->fakultas) }}"
                                   class="form-input cyber-input @error('fakultas') border-red-500 dark:border-red-500 @enderror"
                                   placeholder="Contoh: Fakultas Teknik">
                            @error('fakultas')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Akreditasi -->
                        <div class="cyber-data">
                            <label for="akreditasi" class="form-label">
                                Akreditasi
                            </label>
                            <input type="text" 
                                   id="akreditasi" 
                                   name="akreditasi" 
                                   value="{{ old('akreditasi', $prodi->akreditasi) }}"
                                   class="form-input cyber-input @error('akreditasi') border-red-500 dark:border-red-500 @enderror"
                                   placeholder="Contoh: B">
                            @error('akreditasi')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Urutan Tampil -->
                        <div class="cyber-data">
                            <label for="urutan_tampil" class="form-label">
                                Urutan Tampil
                            </label>
                            <input type="number" 
                                   id="urutan_tampil" 
                                   name="urutan_tampil" 
                                   value="{{ old('urutan_tampil', $prodi->urutan_tampil) }}"
                                   class="form-input cyber-input @error('urutan_tampil') border-red-500 dark:border-red-500 @enderror"
                                   placeholder="0"
                                   min="0">
                            @error('urutan_tampil')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Urutan tampil di menu pendaftaran (0 = tidak ditampilkan)
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Passing Grade dan Kuota -->
                <div class="mb-8">
                    <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                        <i class="fas fa-chart-line mr-2 text-green-500"></i> Kriteria Seleksi
                    </h4>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Passing Grade -->
                        <div class="cyber-data">
                            <label for="passing_grade" class="form-label">
                                Passing Grade <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="number" 
                                       id="passing_grade" 
                                       name="passing_grade" 
                                       value="{{ old('passing_grade', $prodi->passing_grade) }}"
                                       class="form-input cyber-input pr-12 @error('passing_grade') border-red-500 dark:border-red-500 @enderror"
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
                                Nilai minimum untuk diterima (0.00 - 100.00)
                            </p>
                        </div>

                        <!-- Kuota -->
                        <div class="cyber-data">
                            <label for="kuota" class="form-label">
                                Kuota <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                   id="kuota" 
                                   name="kuota" 
                                   value="{{ old('kuota', $prodi->kuota) }}"
                                   class="form-input cyber-input @error('kuota') border-red-500 dark:border-red-500 @enderror"
                                   placeholder="Contoh: 200"
                                   min="0"
                                   required>
                            @error('kuota')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Jumlah maksimal mahasiswa yang diterima
                            </p>
                        </div>

                        <!-- Daya Tampung -->
                        <div class="cyber-data">
                            <label for="daya_tampung" class="form-label">
                                Daya Tampung
                            </label>
                            <input type="number" 
                                   id="daya_tampung" 
                                   name="daya_tampung" 
                                   value="{{ old('daya_tampung', $prodi->daya_tampung) }}"
                                   class="form-input cyber-input @error('daya_tampung') border-red-500 dark:border-red-500 @enderror"
                                   placeholder="Contoh: 38"
                                   min="0">
                            @error('daya_tampung')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Biaya Pendidikan -->
                <div class="mb-8">
                    <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                        <i class="fas fa-money-bill-wave mr-2 text-yellow-500"></i> Biaya Pendidikan (Dapat mencapai 1 Milyar)
                    </h4>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                        <!-- Harga Cicilan -->
                        <div class="cyber-data">
                            <label for="harga_cicilan" class="form-label">
                                Harga Cicilan <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <span class="text-gray-500 text-theme-sm">Rp</span>
                                </div>
                                <input type="number" 
                                       id="harga_cicilan" 
                                       name="harga_cicilan" 
                                       value="{{ old('harga_cicilan', $prodi->harga_cicilan) }}"
                                       class="form-input cyber-input harga-input pl-10 @error('harga_cicilan') border-red-500 dark:border-red-500 @enderror"
                                       placeholder="0"
                                       min="0"
                                       max="1000000000"
                                       step="1"
                                       required>
                            </div>
                            @error('harga_cicilan')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <div id="preview_harga_cicilan" class="harga-preview">
                                Rp 0
                            </div>
                        </div>

                        <!-- Harga Semester -->
                        <div class="cyber-data">
                            <label for="harga_semester" class="form-label">
                                Harga per Semester <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <span class="text-gray-500 text-theme-sm">Rp</span>
                                </div>
                                <input type="number" 
                                       id="harga_semester" 
                                       name="harga_semester" 
                                       value="{{ old('harga_semester', $prodi->harga_semester) }}"
                                       class="form-input cyber-input harga-input pl-10 @error('harga_semester') border-red-500 dark:border-red-500 @enderror"
                                       placeholder="0"
                                       min="0"
                                       max="1000000000"
                                       step="1"
                                       required>
                            </div>
                            @error('harga_semester')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <div id="preview_harga_semester" class="harga-preview">
                                Rp 0
                            </div>
                        </div>

                        <!-- Harga Tahun -->
                        <div class="cyber-data">
                            <label for="harga_tahun" class="form-label">
                                Harga per Tahun <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <span class="text-gray-500 text-theme-sm">Rp</span>
                                </div>
                                <input type="number" 
                                       id="harga_tahun" 
                                       name="harga_tahun" 
                                       value="{{ old('harga_tahun', $prodi->harga_tahun) }}"
                                       class="form-input cyber-input harga-input pl-10 @error('harga_tahun') border-red-500 dark:border-red-500 @enderror"
                                       placeholder="0"
                                       min="0"
                                       max="1000000000"
                                       step="1"
                                       required>
                            </div>
                            @error('harga_tahun')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <div id="preview_harga_tahun" class="harga-preview">
                                Rp 0
                            </div>
                        </div>

                        <!-- Beasiswa Keluarga UBP -->
                        <div class="cyber-data">
                            <label for="beasiswa_kel_ubp" class="form-label">
                                Beasiswa Keluarga UBP
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <span class="text-gray-500 text-theme-sm">Rp</span>
                                </div>
                                <input type="number" 
                                       id="beasiswa_kel_ubp" 
                                       name="beasiswa_kel_ubp" 
                                       value="{{ old('beasiswa_kel_ubp', $prodi->beasiswa_kel_ubp) }}"
                                       class="form-input cyber-input harga-input pl-10 @error('beasiswa_kel_ubp') border-red-500 dark:border-red-500 @enderror"
                                       placeholder="0"
                                       min="0"
                                       max="1000000000"
                                       step="1">
                            </div>
                            @error('beasiswa_kel_ubp')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <div id="preview_beasiswa_kel_ubp" class="harga-preview">
                                Rp 0
                            </div>
                        </div>
                    </div>
                    
                    <!-- Info Max Harga -->
                    <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-sm text-blue-800 dark:text-blue-300">
                                <strong>Maksimal 1 Milyar (Rp 1.000.000.000)</strong> - Gunakan titik (.) sebagai pemisah ribuan
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Deskripsi dan Status -->
                <div class="mb-8">
                    <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                        <i class="fas fa-cog mr-2 text-purple-500"></i> Pengaturan Lainnya
                    </h4>
                    <div class="grid grid-cols-1 gap-6">
                        <!-- Deskripsi -->
                        <div class="cyber-data">
                            <label for="deskripsi" class="form-label">
                                Deskripsi Program Studi
                            </label>
                            <textarea id="deskripsi" 
                                      name="deskripsi" 
                                      rows="4"
                                      class="form-textarea cyber-input @error('deskripsi') border-red-500 dark:border-red-500 @enderror"
                                      placeholder="Tulis deskripsi program studi di sini...">{{ old('deskripsi', $prodi->deskripsi) }}</textarea>
                            @error('deskripsi')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Status Prodi -->
                        <div class="cyber-data">
                            <label for="status_prodi" class="form-label">
                                Status Program Studi <span class="text-red-500">*</span>
                            </label>
                            <select id="status_prodi" 
                                    name="status_prodi" 
                                    class="form-select cyber-input @error('status_prodi') border-red-500 dark:border-red-500 @enderror"
                                    required>
                                <option value="">Pilih Status</option>
                                <option value="1" {{ old('status_prodi', $prodi->status_prodi) == '1' ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ old('status_prodi', $prodi->status_prodi) == '0' ? 'selected' : '' }}>Non-Aktif</option>
                            </select>
                            @error('status_prodi')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Status program studi untuk penerimaan mahasiswa baru
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons dengan efek cyber -->
                <div class="flex items-center justify-end gap-3 mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('admin.prodi.index') }}" 
                       class="cyber-btn inline-flex items-center gap-2 rounded-lg bg-gray-600 px-6 py-2.5 text-theme-sm font-medium text-white hover:bg-gray-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Batal
                    </a>
                    <button type="submit" 
                            class="cyber-btn inline-flex items-center gap-2 rounded-lg bg-blue-600 px-6 py-2.5 text-theme-sm font-medium text-white hover:bg-blue-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        <!-- Info Card dengan efek cyber -->
        <div class="cyber-card cyber-glow-side cyber-grid rounded-xl px-4 pb-3 pt-4 sm:px-6 mt-6">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-white cyber-text">
                        Informasi Lengkap Program Studi
                    </h4>
                    <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        <ul class="list-disc list-inside space-y-1">
                            <li><strong>Kode Program Studi</strong> harus unik dan tidak boleh duplikat</li>
                            <li><strong>Passing Grade</strong> adalah nilai minimum untuk diterima (0-100%)</li>
                            <li><strong>Kuota</strong> adalah jumlah maksimal mahasiswa yang dapat diterima</li>
                            <li><strong>Daya Tampung</strong> kapasitas fisik ruang kelas (opsional)</li>
                            <li><strong>Biaya Pendidikan</strong> dapat mencapai Rp 1.000.000.000 (1 Milyar)</li>
                            <li><strong>Beasiswa Keluarga UBP</strong> adalah diskon untuk keluarga UBP</li>
                            <li>Program dengan status <strong>Non-Aktif</strong> tidak akan tampil di pendaftaran</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
// Format angka dengan separator ribuan
function formatRupiah(angka) {
    return 'Rp ' + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// Update preview harga
function updateHargaPreview(inputId, previewId) {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    
    if (input && preview) {
        // Format nilai awal saat halaman dimuat
        let initialValue = input.value.replace(/[^\d]/g, '');
        if (initialValue) {
            preview.textContent = formatRupiah(parseInt(initialValue));
            // Tampilkan dengan format di input untuk user
            input.value = parseInt(initialValue).toLocaleString('id-ID');
        } else {
            preview.textContent = 'Rp 0';
        }
        
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^\d]/g, '');
            
            // Batasi maksimal 1 milyar
            if (parseInt(value) > 1000000000) {
                value = '1000000000';
                e.target.value = parseInt(value).toLocaleString('id-ID');
            }
            
            // Format dengan separator ribuan
            if (value) {
                const formatted = formatRupiah(parseInt(value));
                preview.textContent = formatted;
                
                // Tampilkan dengan format di input
                e.target.value = parseInt(value).toLocaleString('id-ID');
            } else {
                preview.textContent = 'Rp 0';
                e.target.value = '';
            }
        });
        
        input.addEventListener('blur', function(e) {
            let value = e.target.value.replace(/[^\d]/g, '');
            if (value) {
                // Tampilkan dengan format ribuan
                e.target.value = parseInt(value).toLocaleString('id-ID');
            }
        });
        
        input.addEventListener('focus', function(e) {
            let value = e.target.value.replace(/[^\d]/g, '');
            if (value) {
                // Tampilkan tanpa format saat fokus
                e.target.value = parseInt(value);
            }
        });
    }
}

// Inisialisasi preview untuk semua field harga
updateHargaPreview('harga_cicilan', 'preview_harga_cicilan');
updateHargaPreview('harga_semester', 'preview_harga_semester');
updateHargaPreview('harga_tahun', 'preview_harga_tahun');
updateHargaPreview('beasiswa_kel_ubp', 'preview_beasiswa_kel_ubp');

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

// Format daya tampung input
document.getElementById('daya_tampung').addEventListener('blur', function(e) {
    let value = parseInt(e.target.value);
    if (!isNaN(value)) {
        if (value < 0) value = 0;
        e.target.value = value;
    }
});

// Format urutan tampil input
document.getElementById('urutan_tampil').addEventListener('blur', function(e) {
    let value = parseInt(e.target.value);
    if (!isNaN(value)) {
        if (value < 0) value = 0;
        e.target.value = value;
    }
});

// Validasi form sebelum submit
document.getElementById('prodiForm').addEventListener('submit', function(e) {
    // Hapus semua separator sebelum submit untuk semua field harga
    const hargaFields = ['harga_cicilan', 'harga_semester', 'harga_tahun', 'beasiswa_kel_ubp'];
    let hasError = false;
    
    hargaFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            // Hapus semua karakter non-digit (titik, koma, dll)
            let value = field.value.replace(/[^\d]/g, '');
            
            // Jika kosong, set ke 0 untuk required fields
            if (!value && ['harga_cicilan', 'harga_semester', 'harga_tahun'].includes(fieldId)) {
                value = '0';
            }
            
            // Update nilai field dengan angka murni
            field.value = value;
            
            // Validasi maksimal 1 milyar
            if (value && parseInt(value) > 1000000000) {
                alert(`Harga ${field.previousElementSibling?.textContent?.trim()} melebihi batas maksimal 1 Milyar (Rp 1.000.000.000)`);
                hasError = true;
                field.focus();
                return false;
            }
        }
    });
    
    if (hasError) {
        e.preventDefault();
        return;
    }

    // Validasi required fields
    const requiredFields = [
        { id: 'kode_prodi', name: 'Kode Program Studi' },
        { id: 'nama_prodi', name: 'Nama Program Studi' },
        { id: 'jenjang', name: 'Jenjang Pendidikan' },
        { id: 'passing_grade', name: 'Passing Grade' },
        { id: 'kuota', name: 'Kuota' },
        { id: 'harga_cicilan', name: 'Harga Cicilan' },
        { id: 'harga_semester', name: 'Harga per Semester' },
        { id: 'harga_tahun', name: 'Harga per Tahun' },
        { id: 'status_prodi', name: 'Status Program Studi' }
    ];

    let isValid = true;
    let errorMessage = '';

    for (const field of requiredFields) {
        const element = document.getElementById(field.id);
        let value = element ? element.value.trim() : '';
        
        // Untuk field harga, kita sudah mengkonversi ke angka murni
        if (['harga_cicilan', 'harga_semester', 'harga_tahun'].includes(field.id)) {
            value = value.replace(/[^\d]/g, '');
        }
        
        if (!value) {
            isValid = false;
            errorMessage = `${field.name} harus diisi`;
            break;
        }

        // Validasi khusus untuk angka
        if (['passing_grade', 'kuota', 'harga_cicilan', 'harga_semester', 'harga_tahun'].includes(field.id)) {
            const numValue = parseFloat(value);
            if (isNaN(numValue) || numValue < 0) {
                isValid = false;
                errorMessage = `${field.name} harus berupa angka positif`;
                break;
            }

            if (field.id === 'passing_grade' && (numValue < 0 || numValue > 100)) {
                isValid = false;
                errorMessage = `${field.name} harus antara 0.00 - 100.00`;
                break;
            }
            
            if (['harga_cicilan', 'harga_semester', 'harga_tahun'].includes(field.id) && numValue > 1000000000) {
                isValid = false;
                errorMessage = `${field.name} maksimal 1 Milyar (Rp 1.000.000.000)`;
                break;
            }
        }
    }

    if (!isValid) {
        e.preventDefault();
        alert('Error: ' + errorMessage);
    }
});

// Tambahkan efek cyberpunk pada hover
document.querySelectorAll('.cyber-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-2px)';
        this.style.boxShadow = '0 10px 25px rgba(79, 70, 229, 0.15)';
    });
    
    card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
        this.style.boxShadow = '0 1px 3px rgba(0, 0, 0, 0.1)';
    });
});

// Auto-format harga saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    const hargaFields = ['harga_cicilan', 'harga_semester', 'harga_tahun', 'beasiswa_kel_ubp'];
    
    hargaFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field && field.value) {
            let value = field.value.replace(/[^\d]/g, '');
            if (value) {
                // Tampilkan dengan format ribuan untuk user
                field.value = parseInt(value).toLocaleString('id-ID');
            }
        }
    });
});
</script>

@endsection