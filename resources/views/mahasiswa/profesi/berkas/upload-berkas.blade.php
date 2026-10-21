@extends('layouts.mahasiswa.profesi')

@section('title', 'Upload Berkas - PMB Profesi Farmasi')

@section('content')
{{-- Hapus bg-gray-50 biar efek tembus pandangnya (backdrop-blur) kelihatan --}}
<div class="min-h-screen py-8 px-4 sm:px-6">
    <div class="max-w-4xl mx-auto space-y-6 pb-10">

        {{-- Header Navigation & Title --}}
        <div class="flex items-center gap-4 mb-6">
            <a href="{{ route('profesi.dashboard') }}" class="w-10 h-10 bg-white/90 backdrop-blur-md rounded-full flex items-center justify-center shadow-md border border-white/20 text-gray-600 hover:text-blue-600 hover:bg-blue-50 hover:scale-105 transition-all">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-black text-white leading-tight">Upload Berkas Persyaratan</h1>
                <p class="text-sm text-gray-500 mt-1">Program Studi Profesi Apoteker / Farmasi</p>
            </div>
        </div>

        {{-- Info Alert --}}
        <div class="bg-blue-50/80 backdrop-blur-sm border-l-4 border-blue-500 p-4 rounded-r-xl shadow-sm flex items-start gap-4">
            <div class="text-blue-500 mt-0.5">
                <i class="fas fa-info-circle text-xl animate-pulse"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-blue-900">Panduan Upload Berkas</h3>
                <ul class="text-xs text-blue-800 mt-2 space-y-1 list-disc list-inside">
                    <li>Pastikan scan dokumen Asli (Bukan fotokopi leges) dan terbaca dengan jelas.</li>
                    <li>Format file: <strong>PDF</strong> untuk berkas scan, dan <strong>JPG</strong> untuk Pas Photo (Maks 2MB/file).</li>
                    <li>Berkas dengan tanda bintang (<span class="text-red-500 font-bold">*</span>) wajib diisi.</li>
                </ul>
            </div>
        </div>

        {{-- Form Upload --}}
        <form action="{{ route('profesi.berkas.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            {{-- KOTAK UTAMA (Glassmorphism Effect) --}}
            <div class="bg-white/95 backdrop-blur-md p-6 sm:p-8 rounded-2xl shadow-xl border border-white/20 space-y-8">
                
                {{-- ==========================================
                     NOTIFIKASI ERROR (Sistem & Validasi)
                     ========================================== --}}
        
                {{-- Error dari try-catch Sistem --}}
                @if(session('error'))
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-xl shadow-sm mb-6 animate-pulse">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-exclamation-triangle text-red-500 mt-1"></i>
                        <div>
                            <h3 class="text-sm font-bold text-red-800">Upload Gagal!</h3>
                            <p class="text-sm text-red-700 mt-1">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Error dari Validasi File (Misal: > 2MB atau bukan PDF/JPG) --}}
                @if($errors->any())
                <div class="bg-rose-50 border border-rose-200 p-4 rounded-xl shadow-sm mb-6">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-times-circle text-rose-500 mt-0.5 text-lg"></i>
                        <div>
                            <h3 class="text-sm font-bold text-rose-900 mb-1">Ada berkas yang tidak sesuai syarat:</h3>
                            <ul class="text-xs text-rose-700 list-disc list-inside space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endif
                

                {{-- =======================================
                     SECTION 2: DOKUMEN PERSYARATAN (GRID)
                     ======================================= --}}
                <div>
                    <h3 class="text-lg font-bold text-gray-800 border-b border-gray-100 pb-2 mb-4">Dokumen Fisik (Scan)</h3>
                    
                    {{-- Grid 2 Kolom --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        {{-- 1. Akreditasi --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-800 mb-2 flex justify-between">
                                <span>1. Sertifikat Akreditasi Prodi <span class="text-red-500">*</span></span>
                            </label>
                            <div x-data="{ fileName: null }" class="relative border-2 border-dashed border-gray-300 bg-white/50 backdrop-blur-sm hover:bg-blue-50/80 hover:border-blue-500 transition-all duration-300 rounded-xl p-4 text-center group cursor-pointer overflow-hidden h-32 flex flex-col items-center justify-center shadow-sm hover:shadow-md">
                                <input type="file" name="file_akreditasi" required accept=".pdf" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" @change="fileName = $event.target.files[0]?.name">
                                <div x-show="!fileName" class="transition-transform group-hover:-translate-y-1">
                                    <i class="fas fa-award text-2xl text-gray-400 group-hover:text-blue-500 mb-2 transition-colors"></i>
                                    <p class="text-xs font-bold text-gray-700">Tarik / Klik file ke sini</p>
                                </div>
                                <div x-show="fileName" style="display: none;" class="animate-fadeIn w-full px-4">
                                    <i class="fas fa-check-circle text-2xl text-emerald-500 mb-2"></i>
                                    <p class="text-xs font-bold text-emerald-700 truncate w-full" x-text="fileName"></p>
                                    <p class="text-[9px] text-gray-400 font-bold mt-1 uppercase tracking-wider">PDF (Maks 2MB)</p>
                                </div>
                            </div>
                            <p class="text-[10px] text-gray-500 mt-1">Minimal predikat 'Baik Sekali' saat lulus.</p>
                        </div>

                        {{-- 2. Ijazah S1 --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-800 mb-2 flex justify-between">
                                <span>2. Scan Asli Ijazah S1 Farmasi/SKL <span class="text-red-500">*</span></span>
                            </label>
                            <div x-data="{ fileName: null }" class="relative border-2 border-dashed border-gray-300 bg-white/50 backdrop-blur-sm hover:bg-blue-50/80 hover:border-blue-500 transition-all duration-300 rounded-xl p-4 text-center group cursor-pointer overflow-hidden h-32 flex flex-col items-center justify-center shadow-sm hover:shadow-md">
                                <input type="file" name="file_ijazah" required accept=".pdf" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" @change="fileName = $event.target.files[0]?.name">
                                <div x-show="!fileName" class="transition-transform group-hover:-translate-y-1">
                                    <i class="fas fa-graduation-cap text-2xl text-gray-400 group-hover:text-blue-500 mb-2 transition-colors"></i>
                                    <p class="text-xs font-bold text-gray-700">Tarik / Klik file ke sini</p>
                                    <p class="text-[9px] text-gray-400 font-bold mt-1 uppercase tracking-wider">PDF (Maks 2MB)</p>
                                </div>
                                <div x-show="fileName" style="display: none;" class="animate-fadeIn w-full px-4">
                                    <i class="fas fa-check-circle text-2xl text-emerald-500 mb-2"></i>
                                    <p class="text-xs font-bold text-emerald-700 truncate w-full" x-text="fileName"></p>
                                </div>
                            </div>
                        </div>

                        {{-- 3. Transkrip Nilai --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-800 mb-2 flex justify-between">
                                <span>3. Scan Asli Transkrip S1 <span class="text-red-500">*</span></span>
                            </label>
                            <div x-data="{ fileName: null }" class="relative border-2 border-dashed border-gray-300 bg-white/50 backdrop-blur-sm hover:bg-blue-50/80 hover:border-blue-500 transition-all duration-300 rounded-xl p-4 text-center group cursor-pointer overflow-hidden h-32 flex flex-col items-center justify-center shadow-sm hover:shadow-md">
                                <input type="file" name="file_transkrip" required accept=".pdf" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" @change="fileName = $event.target.files[0]?.name">
                                <div x-show="!fileName" class="transition-transform group-hover:-translate-y-1">
                                    <i class="fas fa-file-alt text-2xl text-gray-400 group-hover:text-blue-500 mb-2 transition-colors"></i>
                                    <p class="text-xs font-bold text-gray-700">Tarik / Klik file ke sini</p>
                                    <p class="text-[9px] text-gray-400 font-bold mt-1 uppercase tracking-wider">PDF (Maks 2MB)</p>
                                </div>
                                <div x-show="fileName" style="display: none;" class="animate-fadeIn w-full px-4">
                                    <i class="fas fa-check-circle text-2xl text-emerald-500 mb-2"></i>
                                    <p class="text-xs font-bold text-emerald-700 truncate w-full" x-text="fileName"></p>
                                </div>
                            </div>
                        </div>

                        {{-- 4. Pas Photo --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-800 mb-2 flex justify-between">
                                <span>4. Pas Photo 4x6 Latar Merah <span class="text-red-500">*</span></span>
                            </label>
                            <div x-data="{ fileName: null }" class="relative border-2 border-dashed border-gray-300 bg-white/50 backdrop-blur-sm hover:bg-blue-50/80 hover:border-blue-500 transition-all duration-300 rounded-xl p-4 text-center group cursor-pointer overflow-hidden h-32 flex flex-col items-center justify-center shadow-sm hover:shadow-md">
                                <input type="file" name="file_pasfoto" required accept=".jpg,.jpeg" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" @change="fileName = $event.target.files[0]?.name">
                                <div x-show="!fileName" class="transition-transform group-hover:-translate-y-1">
                                    <i class="fas fa-portrait text-2xl text-gray-400 group-hover:text-blue-500 mb-2 transition-colors"></i>
                                    <p class="text-xs font-bold text-gray-700">Tarik / Klik file ke sini</p>
                                    <p class="text-[9px] text-gray-400 font-bold mt-1 uppercase tracking-wider">JPG (Maks 2MB)</p>
                                </div>
                                <div x-show="fileName" style="display: none;" class="animate-fadeIn w-full px-4">
                                    <i class="fas fa-check-circle text-2xl text-emerald-500 mb-2"></i>
                                    <p class="text-xs font-bold text-emerald-700 truncate w-full" x-text="fileName"></p>
                                </div>
                            </div>
                            <p class="text-[10px] text-gray-500 mt-1">Gunakan kemeja formal/jas.</p>
                        </div>

                        {{-- 5. KTP --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-800 mb-2 flex justify-between">
                                <span>5. Scan Asli KTP <span class="text-red-500">*</span></span>
                            </label>
                            <div x-data="{ fileName: null }" class="relative border-2 border-dashed border-gray-300 bg-white/50 backdrop-blur-sm hover:bg-blue-50/80 hover:border-blue-500 transition-all duration-300 rounded-xl p-4 text-center group cursor-pointer overflow-hidden h-32 flex flex-col items-center justify-center shadow-sm hover:shadow-md">
                                <input type="file" name="file_ktp" required accept=".pdf" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" @change="fileName = $event.target.files[0]?.name">
                                <div x-show="!fileName" class="transition-transform group-hover:-translate-y-1">
                                    <i class="fas fa-id-card text-2xl text-gray-400 group-hover:text-blue-500 mb-2 transition-colors"></i>
                                    <p class="text-xs font-bold text-gray-700">Tarik / Klik file ke sini</p>
                                    <p class="text-[9px] text-gray-400 font-bold mt-1 uppercase tracking-wider">PDF (Maks 2MB)</p>
                                </div>
                                <div x-show="fileName" style="display: none;" class="animate-fadeIn w-full px-4">
                                    <i class="fas fa-check-circle text-2xl text-emerald-500 mb-2"></i>
                                    <p class="text-xs font-bold text-emerald-700 truncate w-full" x-text="fileName"></p>
                                </div>
                            </div>
                        </div>

                        {{-- 6. KK --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-800 mb-2 flex justify-between">
                                <span>6. Scan Asli Kartu Keluarga <span class="text-red-500">*</span></span>
                            </label>
                            <div x-data="{ fileName: null }" class="relative border-2 border-dashed border-gray-300 bg-white/50 backdrop-blur-sm hover:bg-blue-50/80 hover:border-blue-500 transition-all duration-300 rounded-xl p-4 text-center group cursor-pointer overflow-hidden h-32 flex flex-col items-center justify-center shadow-sm hover:shadow-md">
                                <input type="file" name="file_kk" required accept=".pdf" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" @change="fileName = $event.target.files[0]?.name">
                                <div x-show="!fileName" class="transition-transform group-hover:-translate-y-1">
                                    <i class="fas fa-users text-2xl text-gray-400 group-hover:text-blue-500 mb-2 transition-colors"></i>
                                    <p class="text-xs font-bold text-gray-700">Tarik / Klik file ke sini</p>
                                    <p class="text-[9px] text-gray-400 font-bold mt-1 uppercase tracking-wider">PDF (Maks 2MB)</p>
                                </div>
                                <div x-show="fileName" style="display: none;" class="animate-fadeIn w-full px-4">
                                    <i class="fas fa-check-circle text-2xl text-emerald-500 mb-2"></i>
                                    <p class="text-xs font-bold text-emerald-700 truncate w-full" x-text="fileName"></p>
                                </div>
                            </div>
                        </div>

                        {{-- 7. Asuransi / BPJS --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-800 mb-2 flex justify-between">
                                <span>7. Asuransi Kesehatan (Min. BPJS) <span class="text-red-500">*</span></span>
                            </label>
                            <div x-data="{ fileName: null }" class="relative border-2 border-dashed border-gray-300 bg-white/50 backdrop-blur-sm hover:bg-blue-50/80 hover:border-blue-500 transition-all duration-300 rounded-xl p-4 text-center group cursor-pointer overflow-hidden h-32 flex flex-col items-center justify-center shadow-sm hover:shadow-md">
                                <input type="file" name="file_asuransi" required accept=".pdf" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" @change="fileName = $event.target.files[0]?.name">
                                <div x-show="!fileName" class="transition-transform group-hover:-translate-y-1">
                                    <i class="fas fa-heartbeat text-2xl text-gray-400 group-hover:text-blue-500 mb-2 transition-colors"></i>
                                    <p class="text-xs font-bold text-gray-700">Tarik / Klik file ke sini</p>
                                    <p class="text-[9px] text-gray-400 font-bold mt-1 uppercase tracking-wider">PDF (Maks 2MB)</p>
                                </div>
                                <div x-show="fileName" style="display: none;" class="animate-fadeIn w-full px-4">
                                    <i class="fas fa-check-circle text-2xl text-emerald-500 mb-2"></i>
                                    <p class="text-xs font-bold text-emerald-700 truncate w-full" x-text="fileName"></p>
                                </div>
                            </div>
                            <p class="text-[10px] text-gray-500 mt-1">Pastikan status asuransi dalam keadaan Aktif.</p>
                        </div>

                        {{-- 8. Surat Keterangan Sehat --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-800 mb-2 flex justify-between">
                                <span>8. Surat Keterangan Sehat <span class="text-red-500">*</span></span>
                            </label>
                            <div x-data="{ fileName: null }" class="relative border-2 border-dashed border-gray-300 bg-white/50 backdrop-blur-sm hover:bg-blue-50/80 hover:border-blue-500 transition-all duration-300 rounded-xl p-4 text-center group cursor-pointer overflow-hidden h-32 flex flex-col items-center justify-center shadow-sm hover:shadow-md">
                                <input type="file" name="file_surat_sehat" required accept=".pdf" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" @change="fileName = $event.target.files[0]?.name">
                                <div x-show="!fileName" class="transition-transform group-hover:-translate-y-1">
                                    <i class="fas fa-notes-medical text-2xl text-gray-400 group-hover:text-blue-500 mb-2 transition-colors"></i>
                                    <p class="text-xs font-bold text-gray-700">Tarik / Klik file ke sini</p>
                                    <p class="text-[9px] text-gray-400 font-bold mt-1 uppercase tracking-wider">PDF (Maks 2MB)</p>
                                </div>
                                <div x-show="fileName" style="display: none;" class="animate-fadeIn w-full px-4">
                                    <i class="fas fa-check-circle text-2xl text-emerald-500 mb-2"></i>
                                    <p class="text-xs font-bold text-emerald-700 truncate w-full" x-text="fileName"></p>
                                </div>
                            </div>
                            <p class="text-[10px] text-gray-500 mt-1">Termasuk pemeriksaan fisik & tidak buta warna.</p>
                        </div>

                    </div>
                </div>
                
            </div>

            {{-- Sticky Submit Button --}}
            <div class="sticky bottom-4 z-40 bg-white/95 backdrop-blur-md p-4 sm:p-5 rounded-2xl shadow-xl border border-white/20 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm text-gray-600 hidden sm:block">
                    Pastikan ke-8 berkas persyaratan telah dipilih.
                </div>
                <button type="submit" class="w-full sm:w-auto px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-md shadow-blue-600/20 transition transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                    <i class="fas fa-paper-plane"></i> Simpan & Kirim Berkas
                </button>
            </div>

        </form>
    </div>
</div>
@endsection