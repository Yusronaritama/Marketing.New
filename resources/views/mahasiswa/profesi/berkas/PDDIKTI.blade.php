@extends('layouts.mahasiswa.profesi')

@section('title', 'Data Final PDDIKTI')

@section('content')
@php
    $isMaintenance = false;
    // Buka akses khusus ID 215 buat testing kalo lagi maintenance
    if (auth()->check() && auth()->id() == 215) {
        $isMaintenance = false;
    }
    
    $targetSlide = 1; // Default slide awal

    if($errors->any()) {
        // Cek Error di Slide 1 (Identitas)
        if($errors->hasAny([
            'nama_mahasiswa', 'nik', 'nisn', 'tempat_lahir', 'tanggal_lahir', 
            'jenis_kelamin', 'id_agama', 'kewarganegaraan', 'id_kk'
        ])) {
            $targetSlide = 1;
        }
        // Cek Error di Slide 2 (Domisili & Kontak)
        elseif($errors->hasAny([
            'handphone', 'telepon', 'email', 'jalan', 'dusun', 'rt', 'rw', 
            'kelurahan', 'id_wilayah', 'kode_pos', 'id_jenis_tinggal', 
            'id_alat_transportasi', 'id_provinsi', 'id_kabupaten', 'id_kecamatan'
        ])) {
            $targetSlide = 2;
        }
        // Cek Error di Slide 3 (Orang Tua)
        elseif($errors->hasAny([
            'nama_ayah', 'nik_ayah', 'tanggal_lahir_ayah', 'id_jenjang_pendidikan_ayah', 
            'id_pekerjaan_ayah', 'id_penghasilan_ayah', 'nama_ibu_kandung', 'nik_ibu', 
            'tanggal_lahir_ibu', 'id_jenjang_pendidikan_ibu', 'id_pekerjaan_ibu', 
            'id_penghasilan_ibu'
        ])) {
            $targetSlide = 3;
        }
        // Cek Error di Slide 4 (Wali & Finalisasi)
        elseif($errors->hasAny([
            'penerima_kps', 'no_kps', 'npwp', 'nama_wali', 'tanggal_lahir_wali', 
            'id_jenjang_pendidikan_wali', 'id_pekerjaan_wali', 'id_penghasilan_wali'
        ])) {
            $targetSlide = 4;
        }
    }
@endphp

@if($isMaintenance)
    {{-- TAMPILAN SAAT MAINTENANCE --}}
    <div class="max-w-3xl mx-auto mt-12 pb-24 px-4 text-center">
        <div class="bg-white/95 backdrop-blur-md border border-gray-200 rounded-3xl shadow-2xl p-10">
            <div class="w-20 h-20 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-sm">
                <i class="fas fa-tools text-4xl animate-bounce"></i>
            </div>
            
            {{-- Warna digelapin ke gray-900, ditebelin ke font-black, tambah drop-shadow tipis --}}
            <h2 class="text-3xl sm:text-4xl font-black text-gray-900 mb-4 drop-shadow-sm">
                Pembaruan Sistem
            </h2>
            
            {{-- Warna digelapin ke gray-800, ukuran jadi text-lg, dan dikasih font-medium --}}
            <p class="text-gray-800 text-lg mb-8 leading-relaxed font-medium">
                Mohon maaf, pengisian <b class="text-gray-900 bg-gray-100 px-2 py-0.5 rounded">Data Final PDDIKTI</b> sedang dikunci sementara untuk pemeliharaan sistem. Silakan kembali lagi nanti.
            </p>
            
            {{-- Tombol dibuat lebih pekat dan nambah efek hover naik ke atas --}}
            <a href="{{ route('profesi.dashboard') }}" class="inline-flex items-center justify-center bg-gray-900 hover:bg-black text-white font-bold py-3.5 px-8 rounded-xl shadow-xl transition-all transform hover:-translate-y-1">
                <i class="fas fa-arrow-left mr-2"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>
@else
    {{-- FORM ASLI ANDA MULAI DARI SINI --}}
    <div class="max-w-5xl mx-auto pb-24" 
         x-data="{ 
            slide: {{ $targetSlide }},
            showErrorModal: {{ $errors->any() ? 'true' : 'false' }} 
         }">

        {{-- =============================================================== --}}
        {{-- CUSTOM ERROR MODAL POPUP (TAILWIND STYLE)                       --}}
        {{-- =============================================================== --}}
        <template x-if="showErrorModal">
            <div class="fixed inset-0 z-[9999] flex items-center justify-center px-4" x-cloak>
        
                {{-- Backdrop --}}
                <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm"
                     x-show="showErrorModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"></div>
        
                {{-- Modal --}}
                <div class="relative bg-white rounded-2xl border border-gray-100 w-full max-w-md overflow-hidden"
                     x-show="showErrorModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 scale-95">
        
                    {{-- Header --}}
                    <div class="flex items-start gap-3.5 px-6 pt-6 pb-4">
                        <div class="w-10 h-10 rounded-full bg-red-50 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-triangle-exclamation text-red-700 text-base"></i>
                        </div>
                        <div>
                            <p class="text-base font-medium text-gray-900 leading-snug">Data belum valid</p>
                            <p class="text-xs text-gray-500 mt-0.5">Perbaiki kesalahan berikut sebelum melanjutkan.</p>
                        </div>
                    </div>
        
                    <div class="mx-6 border-t border-gray-100"></div>
        
                    {{-- Error List --}}
                    <div class="px-6 py-4 flex flex-col gap-2 max-h-60 overflow-y-auto">
                        @if($errors->any())
                            @foreach ($errors->all() as $error)
                                <div class="flex items-start gap-2.5 px-3 py-2.5 rounded-lg bg-red-50 border border-red-200">
                                    <i class="fas fa-times text-red-700 text-xs mt-1 flex-shrink-0"></i>
                                    <span class="text-xs text-red-900">{{ $error }}</span>
                                </div>
                            @endforeach
                        @endif
                    </div>
        
                    {{-- Footer --}}
                    <div class="flex items-center justify-between gap-3 px-6 pb-6 pt-2">
                        <span class="text-xs text-gray-400">{{ $errors->count() }} kesalahan ditemukan</span>
                        <button @click="
                            showErrorModal = false;
                            setTimeout(() => {
                                const firstError = document.querySelector('.input-error');
                                if (firstError) {
                                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                    firstError.focus();
                                }
                            }, 300)"
                            class="flex items-center gap-1.5 px-4 py-2 bg-red-700 hover:bg-red-800 active:scale-95 text-red-100 text-xs font-medium rounded-lg transition-all">
                            <i class="fas fa-wrench text-xs"></i>
                            Siap, saya perbaiki
                        </button>
                    </div>
        
                </div>
            </div>
        </template>

        {{-- HEADER --}}
        <div class="mb-6 pt-8">
            <div class="flex items-center gap-3 mb-1">
                <div class="h-px flex-1 bg-slate-200"></div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">PDDIKTI · Profesi</span>
                <div class="h-px flex-1 bg-slate-200"></div>
            </div>
            <h1 class="text-2xl font-bold text-slate-200 text-center mt-3">Data Final Mahasiswa</h1>
            <p class="text-sm text-slate-100 text-center mt-1">Lengkapi seluruh data pelaporan dengan benar sesuai dokumen resmi</p>
        </div>

        {{-- STATIC WARNING BANNER --}}
        <div class="flex items-start gap-3.5 bg-amber-50 border border-amber-200 rounded-xl p-4 mb-8">
            <div class="w-9 h-9 rounded-full bg-amber-200 flex items-center justify-center flex-shrink-0 mt-0.5">
                <i class="fas fa-shield-halved text-amber-900 text-sm"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-amber-900 mb-1">Data bersifat final & permanen</p>
                <p class="text-sm text-amber-800 leading-relaxed">
                    Data akan langsung disinkronisasi ke server PDDIKTI dan 
                    <strong class="font-medium text-amber-900">tidak dapat diubah</strong> 
                    setelah disimpan. Isi sesuai dokumen resmi (KTP / Ijazah / KK) dan periksa 
                    kembali ejaan nama, NIK, serta tanggal lahir.
                </p>
            </div>
        </div>

        {{-- INDICATOR SLIDE (RESPONSIVE) --}}
        <div class="mb-8 px-2">

            {{-- STEPPER --}}
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
        
                {{-- Step Icons --}}
                <div class="flex items-start gap-0 mb-4">
        
                    {{-- Step 1 --}}
                    <button type="button" @click="slide = 1" class="flex flex-col items-center gap-1.5 flex-1 focus:outline-none">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center transition-all duration-200"
                             :class="slide > 1 ? 'bg-blue-100 text-blue-600' : (slide === 1 ? 'bg-blue-400 text-white' : 'bg-white text-gray-300 border border-gray-200')">
                            <i class="fas fa-user text-sm"></i>
                        </div>
                        <span class="text-[10px] uppercase tracking-wider transition-colors duration-200"
                              :class="slide >= 1 ? 'text-blue-600 font-medium' : 'text-gray-400'">Identitas</span>
                    </button>
        
                    <div class="h-[1.5px] flex-1 rounded-full mt-[18px] transition-colors duration-300"
                         :class="slide > 1 ? 'bg-blue-400' : 'bg-gray-200'"></div>
        
                    {{-- Step 2 --}}
                    <button type="button" @click="slide = 2" class="flex flex-col items-center gap-1.5 flex-1 focus:outline-none">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center transition-all duration-200"
                             :class="slide > 2 ? 'bg-blue-100 text-blue-600' : (slide === 2 ? 'bg-blue-400 text-white' : 'bg-white text-gray-300 border border-gray-200')">
                            <i class="fas fa-map-pin text-sm"></i>
                        </div>
                        <span class="text-[10px] uppercase tracking-wider transition-colors duration-200"
                              :class="slide >= 2 ? 'text-blue-600 font-medium' : 'text-gray-400'">Domisili</span>
                    </button>
        
                    <div class="h-[1.5px] flex-1 rounded-full mt-[18px] transition-colors duration-300"
                         :class="slide > 2 ? 'bg-blue-400' : 'bg-gray-200'"></div>
        
                    {{-- Step 3 --}}
                    <button type="button" @click="slide = 3" class="flex flex-col items-center gap-1.5 flex-1 focus:outline-none">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center transition-all duration-200"
                             :class="slide > 3 ? 'bg-blue-100 text-blue-600' : (slide === 3 ? 'bg-blue-400 text-white' : 'bg-white text-gray-300 border border-gray-200')">
                            <i class="fas fa-users text-sm"></i>
                        </div>
                        <span class="text-[10px] uppercase tracking-wider transition-colors duration-200"
                              :class="slide >= 3 ? 'text-blue-600 font-medium' : 'text-gray-400'">Orang Tua</span>
                    </button>
        
                    <div class="h-[1.5px] flex-1 rounded-full mt-[18px] transition-colors duration-300"
                         :class="slide > 3 ? 'bg-blue-400' : 'bg-gray-200'"></div>
        
                    {{-- Step 4 --}}
                    <button type="button" @click="slide = 4" class="flex flex-col items-center gap-1.5 flex-1 focus:outline-none">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center transition-all duration-200"
                             :class="slide === 4 ? 'bg-blue-400 text-white' : 'bg-white text-gray-300 border border-gray-200'">
                            <i class="fas fa-circle-check text-sm"></i>
                        </div>
                        <span class="text-[10px] uppercase tracking-wider transition-colors duration-200"
                              :class="slide >= 4 ? 'text-blue-600 font-medium' : 'text-gray-400'">Finalisasi</span>
                    </button>
        
                </div>
        
                {{-- Step Title --}}
                <div class="border-t border-gray-100 pt-3 text-center">
                    <p class="text-[10px] uppercase tracking-widest text-gray-400 mb-0.5">
                        Langkah <span x-text="slide"></span> dari 4
                    </p>
                    <h2 class="text-base font-medium text-gray-800"
                        x-text="['Identitas Diri', 'Domisili & Kontak', 'Data Orang Tua', 'Finalisasi Data'][slide - 1]">
                    </h2>
                </div>
        
            </div>
        </div>

        {{-- FORM START (MENGARAH KE CONTROLLER PROFESI) --}}
        <form id="form-final" action="{{ route('profesi.data-final.store') }}" method="POST">
            @csrf
        
            {{-- INPUT HIDDEN DEFAULT --}}
            <input type="hidden" name="id_kebutuhan_khusus_mahasiswa" value="0">
            <input type="hidden" name="id_kebutuhan_khusus_ayah" value="0">
            <input type="hidden" name="id_kebutuhan_khusus_ibu" value="0">
        
            {{-- ==================================================== --}}
            {{-- SLIDE 1: IDENTITAS DIRI                              --}}
            {{-- ==================================================== --}}
            <div x-show="slide === 1" 
                 x-transition:enter="transition ease-out duration-300 transform" 
                 x-transition:enter-start="opacity-0 translate-x-10" 
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 translate-x-0"
                 x-transition:leave-end="opacity-0 -translate-x-10"
                 style="display: none;">
            
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden relative">
            
                    {{-- Top accent strip --}}
                    <div class="h-1 bg-gradient-to-r from-amber-400 via-yellow-300 to-amber-400"></div>
            
                    <div class="p-6 md:p-8">
            
                        {{-- Header --}}
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-11 h-11 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-id-card text-amber-700"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800">Identitas Pribadi</h3>
                                <p class="text-xs text-slate-400">Isi sesuai dokumen resmi (KTP / Ijazah)</p>
                            </div>
                        </div>
            
                        {{-- Alert --}}
                        <div class="flex items-start gap-3.5 bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
                            <div class="w-9 h-9 rounded-full bg-amber-200 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <i class="fas fa-shield-halved text-amber-900 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-amber-900 mb-1">Data bersifat final & permanen</p>
                                <p class="text-sm text-amber-800 leading-relaxed">
                                    Data akan langsung disinkronisasi ke server PDDIKTI dan 
                                    <strong class="font-medium text-amber-900">tidak dapat diubah</strong> 
                                    setelah disimpan. Isi sesuai dokumen resmi (KTP / Ijazah / KK) dan periksa 
                                    kembali ejaan nama, NIK, serta tanggal lahir.
                                </p>
                            </div>
                        </div>
            
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            
                            {{-- Nama Lengkap --}}
                            <div class="md:col-span-2">
                                <label class="label-text">Nama Lengkap <span class="text-slate-400 font-normal text-xs">(Sesuai Ijazah)</span> <span class="text-red-500">*</span></label>
                                <input type="text" 
                                       name="nama_mahasiswa" 
                                       pattern="^[a-zA-Z\s.,']+$"
                                       title="Hanya boleh Huruf, Spasi, Titik, Koma, dan Tanda Petik"
                                       value="{{ old('nama_mahasiswa', $data->nama_mahasiswa ?? $biodata->nama_lengkap ?? Auth::user()->name ?? '') }}" 
                                       class="input-glass @error('nama_mahasiswa') input-error @enderror" 
                                       required maxlength="100">
                            </div>
            
                            {{-- NIK --}}
                            <div>
                                <label class="label-text">NIK <span class="text-slate-400 font-normal text-xs">(KTP)</span> <span class="text-red-500">*</span></label>
                                <input type="text" 
                                       name="nik" 
                                       pattern="\d{16}" 
                                       inputmode="numeric" 
                                       maxlength="16"
                                       oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                       title="NIK harus berisi 16 digit angka"
                                       value="{{ old('nik', $data->nik ?? $biodata->nik ?? '') }}" 
                                       class="input-glass @error('nik') input-error @enderror" 
                                       required placeholder="16 Digit">
                            </div>
            
                            {{-- NISN --}}
                            <div>
                                <label class="label-text">NISN <span class="text-red-500 font-bold">*</span></label>
                                <input type="text" 
                                       name="nisn" 
                                       pattern="\d{10}" 
                                       inputmode="numeric" 
                                       maxlength="10"
                                       oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                       title="NISN harus berisi persis 10 digit angka"
                                       value="{{ old('nisn', $data->nisn ?? $biodata->nisn ?? '') }}" 
                                       class="input-glass @error('nisn') input-error @enderror" 
                                       placeholder="10 Digit" required>
            
                                {{-- Info NISN --}}
                                <div class="mt-4 bg-slate-50 border border-slate-200 rounded-xl overflow-hidden" x-data="{ showIframe: false }">
                                    <div class="flex items-center justify-between gap-3 p-3.5 flex-wrap">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-lg bg-amber-400 flex items-center justify-center flex-shrink-0">
                                                <i class="fas fa-search text-white text-sm"></i>
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold text-slate-700">Cek / Cari NISN</p>
                                                <p class="text-[11px] text-slate-400">Validasi mandiri via Pusdatin Kemendikbud</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button type="button" @click="showIframe = !showIframe"
                                                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[11px] font-bold uppercase tracking-wide transition-all active:scale-95"
                                                    :class="showIframe ? 'bg-slate-200 text-slate-600' : 'bg-amber-400 text-white shadow shadow-amber-200'">
                                                <i class="fas" :class="showIframe ? 'fa-times' : 'fa-window-maximize'"></i>
                                                <span x-text="showIframe ? 'Tutup' : 'Buka Panel'"></span>
                                            </button>
                                            <a href="https://nisn.data.kemdikbud.go.id/" target="_blank" 
                                               class="w-8 h-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:text-amber-600 hover:border-amber-300 transition-all text-xs">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        </div>
                                    </div>
            
                                    <div x-show="showIframe"
                                         x-transition:enter="transition ease-out duration-300"
                                         x-transition:enter-start="opacity-0 -translate-y-2"
                                         x-transition:enter-end="opacity-100 translate-y-0"
                                         x-transition:leave="transition ease-in duration-200"
                                         x-transition:leave-start="opacity-100"
                                         x-transition:leave-end="opacity-0"
                                         class="border-t border-slate-200">
                                        <div class="bg-slate-100 border-b border-slate-200 px-3 py-2 flex items-center gap-3">
                                            <div class="flex gap-1.5">
                                                <span class="w-2.5 h-2.5 rounded-full bg-slate-300"></span>
                                                <span class="w-2.5 h-2.5 rounded-full bg-slate-300"></span>
                                                <span class="w-2.5 h-2.5 rounded-full bg-slate-300"></span>
                                            </div>
                                            <div class="flex-1 bg-white border border-slate-200 rounded px-2.5 py-1 flex items-center justify-between">
                                                <span class="text-[10px] font-mono text-slate-400">https://nisn.data.kemdikbud.go.id</span>
                                                <i class="fas fa-lock text-[9px] text-emerald-500"></i>
                                            </div>
                                        </div>
                                        <div class="relative bg-slate-100 h-[500px] md:h-[600px]">
                                            <div class="absolute inset-0 flex flex-col items-center justify-center z-0">
                                                <i class="fas fa-circle-notch fa-spin text-amber-400 text-2xl mb-2"></i>
                                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Menghubungkan ke Kemendikbud...</span>
                                            </div>
                                            <iframe src="https://nisn.data.kemdikbud.go.id/" 
                                                    class="w-full h-full border-none relative z-10" 
                                                    title="Website Cek NISN"></iframe>
                                        </div>
                                        <div class="bg-amber-50 border-t border-amber-200 px-4 py-2">
                                            <p class="text-[10px] text-amber-700 italic">* Gunakan nama ibu kandung dan tempat lahir untuk pencarian data.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
            
                            {{-- Tempat Lahir --}}
                            <div>
                                <label class="label-text">Tempat Lahir <span class="text-red-500">*</span></label>
                                <input type="text" 
                                       name="tempat_lahir" 
                                       pattern="^[a-zA-Z\s.-]+$"
                                       title="Hanya boleh huruf, spasi, titik, dan strip"
                                       value="{{ old('tempat_lahir', $data->tempat_lahir ?? $biodata->tempat_lahir ?? '') }}" 
                                       class="input-glass @error('tempat_lahir') input-error @enderror" 
                                       required maxlength="32">
                            </div>
            
                            {{-- Tanggal Lahir --}}
                            <div>
                                <label class="label-text">Tanggal Lahir <span class="text-red-500">*</span></label>
                                <input type="date" 
                                       name="tanggal_lahir" 
                                       class="input-glass @error('tanggal_lahir') input-error @enderror" 
                                       required
                                       value="{{ old('tanggal_lahir', ($data->tanggal_lahir && $data->tanggal_lahir->format('Y-m-d') !== '2000-01-01' ? $data->tanggal_lahir->format('Y-m-d') : '')) }}">
                            </div>
            
                            {{-- Jenis Kelamin --}}
                            <div>
                                <label class="label-text">Jenis Kelamin <span class="text-red-500">*</span></label>
                                @php $jk = old('jenis_kelamin', $data->jenis_kelamin ?? $biodata->jenis_kelamin ?? ''); @endphp
                                <select name="jenis_kelamin" class="input-glass @error('jenis_kelamin') input-error @enderror" required>
                                    <option value="">Pilih...</option>
                                    <option value="L" {{ ($jk == 'L' || $jk == 'Laki-laki') ? 'selected' : '' }}>Laki-laki (L)</option>
                                    <option value="P" {{ ($jk == 'P' || $jk == 'Perempuan') ? 'selected' : '' }}>Perempuan (P)</option>
                                </select>
                            </div>
            
                            {{-- Agama --}}
                            <div>
                                <label class="label-text">Agama <span class="text-red-500">*</span></label>
                                @php $agm = old('id_agama', $data->id_agama ?? $biodata->agama ?? ''); @endphp
                                <select name="id_agama" class="input-glass @error('id_agama') input-error @enderror" required>
                                    <option value="">Pilih Agama</option>
                                    <option value="1"  {{ $agm == 1  ? 'selected' : '' }}>Islam</option>
                                    <option value="2"  {{ $agm == 2  ? 'selected' : '' }}>Kristen</option>
                                    <option value="3"  {{ $agm == 3  ? 'selected' : '' }}>Katholik</option>
                                    <option value="4"  {{ $agm == 4  ? 'selected' : '' }}>Hindu</option>
                                    <option value="5"  {{ $agm == 5  ? 'selected' : '' }}>Budha</option>
                                    <option value="6"  {{ $agm == 6  ? 'selected' : '' }}>Konghucu</option>
                                    <option value="99" {{ $agm == 99 ? 'selected' : '' }}>Lainnya</option>
                                </select>
                            </div>
            
                            {{-- Kewarganegaraan --}}
                            <div class="md:col-span-2">
                                <label class="label-text">Kewarganegaraan <span class="text-red-500">*</span></label>
                                <select name="kewarganegaraan" class="input-glass @error('kewarganegaraan') input-error @enderror" required>
                                    <option value="ID" {{ (old('kewarganegaraan', $data->kewarganegaraan) == 'ID' || old('kewarganegaraan', $data->kewarganegaraan) == 'WNI') ? 'selected' : '' }}>Indonesia (WNI)</option>
                                    <option value="AS" {{ (old('kewarganegaraan', $data->kewarganegaraan) == 'AS' || old('kewarganegaraan', $data->kewarganegaraan) == 'WNA') ? 'selected' : '' }}>Asing (WNA)</option>
                                </select>
                            </div>
            
                        </div>
                    </div>
            
                    {{-- Footer --}}
                    <div class="px-6 md:px-8 py-4 border-t border-slate-100 flex justify-end">
                        <button type="button" @click="validateAndNext(1, 2, $data)" 
                                class="flex items-center gap-2 px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-xl transition-all shadow-sm active:scale-95">
                            Lanjut Domisili <i class="fas fa-arrow-right text-xs"></i>
                        </button>
                    </div>
            
                </div>
            </div>
        
            {{-- ==================================================== --}}
            {{-- SLIDE 2: DOMISILI & KONTAK                           --}}
            {{-- ==================================================== --}}
            <div x-show="slide === 2" 
                 x-transition:enter="transition ease-out duration-300 transform" 
                 x-transition:enter-start="opacity-0 translate-x-10" 
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 translate-x-0"
                 x-transition:leave-end="opacity-0 -translate-x-10"
                 style="display: none;">
            
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            
                    <div class="h-1 bg-gradient-to-r from-amber-400 via-yellow-300 to-amber-400"></div>
            
                    <div class="p-6 md:p-8">
            
                        {{-- Header --}}
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-11 h-11 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-map-marked-alt text-amber-700"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800">Domisili & Kontak</h3>
                                <p class="text-xs text-slate-400">Isi alamat sesuai domisili saat ini</p>
                            </div>
                        </div>
            
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            
                            {{-- Kontak --}}
                            <div class="md:col-span-2 bg-amber-50 border border-amber-100 rounded-xl p-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="label-text">No. Handphone</label>
                                    <input type="text" 
                                           name="handphone" 
                                           pattern="^08\d{8,11}$" 
                                           inputmode="numeric" 
                                           maxlength="13"
                                           oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                           title="Nomor HP harus diawali 08 dan 10-13 digit"
                                           value="{{ old('handphone', $data->handphone ?? Auth::user()->no_hp ?? '') }}" 
                                           class="input-glass @error('handphone') input-error @enderror" 
                                           placeholder="08...">
                                </div>
                                <div>
                                    <label class="label-text">Telepon Rumah</label>
                                    <input type="text" 
                                           name="telepon" 
                                           pattern="\d+" 
                                           inputmode="numeric"
                                           oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                           value="{{ old('telepon', $data->telepon ?? '') }}" 
                                           class="input-glass @error('telepon') input-error @enderror" 
                                           placeholder="Kode Area + Nomor" maxlength="20">
                                </div>
                                <div>
                                    <label class="label-text">Email</label>
                                    <input type="email" 
                                           name="email" 
                                           value="{{ old('email', $data->email ?? Auth::user()->email ?? '') }}" 
                                           class="input-glass bg-gray-100 @error('email') input-error @enderror" 
                                           maxlength="60">
                                </div>
                            </div>
            
                            {{-- Jalan --}}
                            <div class="md:col-span-2">
                                <label class="label-text">Jalan</label>
                                <input type="text" 
                                       name="jalan" 
                                       value="{{ old('jalan', $data->jalan ?? $biodata->alamat_jalan ?? '') }}" 
                                       class="input-glass @error('jalan') input-error @enderror" 
                                       placeholder="Nama Jalan" maxlength="80">
                            </div>
            
                            {{-- Dusun --}}
                            <div class="md:col-span-2">
                                <label class="label-text">Dusun</label>
                                <input type="text" 
                                       name="dusun" 
                                       value="{{ old('dusun', $data->dusun ?? '') }}" 
                                       class="input-glass @error('dusun') input-error @enderror" 
                                       placeholder="Nama Dusun" maxlength="60">
                            </div>
            
                            {{-- RT / RW --}}
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="label-text">RT</label>
                                    <input type="text" 
                                           name="rt" 
                                           pattern="\d{1,3}" 
                                           inputmode="numeric" maxlength="3"
                                           oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                           value="{{ old('rt', $data->rt ?? $biodata->rt ?? '') }}" 
                                           class="input-glass @error('rt') input-error @enderror">
                                </div>
                                <div>
                                    <label class="label-text">RW</label>
                                    <input type="text" 
                                           name="rw" 
                                           pattern="\d{1,3}" 
                                           inputmode="numeric" maxlength="3"
                                           oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                           value="{{ old('rw', $data->rw ?? $biodata->rw ?? '') }}" 
                                           class="input-glass @error('rw') input-error @enderror">
                                </div>
                            </div>
            
                            {{-- Kelurahan --}}
                            <div>
                                <label class="label-text">Kelurahan / Desa <span class="text-red-500">*</span></label>
                                <input type="text" 
                                       name="kelurahan" 
                                       pattern="^[a-zA-Z0-9\s.-]+$"
                                       value="{{ old('kelurahan', $data->kelurahan ?? $biodata->kelurahan ?? '') }}" 
                                       class="input-glass @error('kelurahan') input-error @enderror" 
                                       required maxlength="60">
                            </div>
            
                            {{-- Provinsi / Kab / Kecamatan --}}
                            <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-4" 
                                 x-data="{ 
                                     provinces: [], cities: [], districts: [], 
                                     selectedProv: '{{ old('id_provinsi', $data->id_provinsi ?? '') }}', 
                                     selectedCity: '{{ old('id_kabupaten', $data->id_kabupaten ?? '') }}', 
                                     selectedDist: '{{ old('id_kecamatan', $data->id_kecamatan ?? '') }}',
                                     init() {
                                         fetch('/api/wilayah/provinces').then(r=>r.json()).then(d=>this.provinces=d);
                                         if(this.selectedProv) fetch(`/api/wilayah/cities/${this.selectedProv}`).then(r=>r.json()).then(d=>this.cities=d);
                                         if(this.selectedCity) fetch(`/api/wilayah/districts/${this.selectedCity}`).then(r=>r.json()).then(d=>this.districts=d);
                                     },
                                     fetchCities() {
                                         this.selectedCity=''; this.selectedDist=''; this.cities=[]; this.districts=[];
                                         if(this.selectedProv) fetch(`/api/wilayah/cities/${this.selectedProv}`).then(r=>r.json()).then(d=>this.cities=d);
                                     },
                                     fetchDistricts() {
                                         this.selectedDist=''; this.districts=[];
                                         if(this.selectedCity) fetch(`/api/wilayah/districts/${this.selectedCity}`).then(r=>r.json()).then(d=>this.districts=d);
                                     }
                                 }">
                                <div>
                                    <label class="label-text">Provinsi <span class="text-red-500">*</span></label>
                                    <select name="id_provinsi" x-model="selectedProv" @change="fetchCities()" class="input-glass @error('id_provinsi') input-error @enderror" required>
                                        <option value="">Pilih Provinsi...</option>
                                        <template x-for="prov in provinces" :key="prov.id">
                                            <option :value="prov.id" x-text="prov.name" :selected="prov.id == selectedProv"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label class="label-text">Kabupaten/Kota <span class="text-red-500">*</span></label>
                                    <select name="id_kabupaten" x-model="selectedCity" @change="fetchDistricts()" class="input-glass @error('id_kabupaten') input-error @enderror" :disabled="!selectedProv" required>
                                        <option value="">Pilih Kab/Kota...</option>
                                        <template x-for="city in cities" :key="city.id">
                                            <option :value="city.id" x-text="city.name" :selected="city.id == selectedCity"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label class="label-text">Kecamatan <span class="text-red-500">*</span></label>
                                    <select name="id_kecamatan" x-model="selectedDist" class="input-glass @error('id_kecamatan') input-error @enderror" :disabled="!selectedCity" required>
                                        <option value="">Pilih Kecamatan...</option>
                                        <template x-for="dist in districts" :key="dist.id">
                                            <option :value="dist.id" x-text="dist.name" :selected="dist.id == selectedDist"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>
            
                            {{-- Kode Pos --}}
                            <div>
                                <label class="label-text">Kode Pos</label>
                                <input type="text" 
                                       name="kode_pos" 
                                       pattern="\d{5}" 
                                       inputmode="numeric" maxlength="5"
                                       oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                       value="{{ old('kode_pos', $data->kode_pos ?? $biodata->kode_pos ?? '') }}" 
                                       class="input-glass @error('kode_pos') input-error @enderror">
                            </div>
            
                            {{-- Jenis Tinggal --}}
                            <div>
                                <label class="label-text">Jenis Tinggal</label>
                                <select name="id_jenis_tinggal" class="input-glass @error('id_jenis_tinggal') input-error @enderror" required>
                                    <option value="">Pilih...</option>
                                    @foreach([1=>'Bersama orang tua', 2=>'Wali', 3=>'Kos', 4=>'Asrama', 5=>'Panti Asuhan', 99=>'Lainnya'] as $k => $v)
                                        <option value="{{ $k }}" {{ old('id_jenis_tinggal', $data->id_jenis_tinggal ?? '') == $k ? 'selected' : '' }}>{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
            
                            {{-- Alat Transportasi --}}
                            <div class="md:col-span-2">
                                <label class="label-text">Alat Transportasi</label>
                                <select name="id_alat_transportasi" class="input-glass @error('id_alat_transportasi') input-error @enderror" required>
                                    <option value="">Pilih...</option>
                                    @foreach([1=>'Jalan kaki', 2=>'Angkutan umum/bus/pete-pete', 3=>'Mobil jemputan', 4=>'Kereta api', 5=>'Ojek', 6=>'Andong/Bendi/Sado/Becak', 7=>'Perahu penyeberangan/Rakit/Getek', 8=>'Kuda', 9=>'Sepeda', 10=>'Sepeda motor', 11=>'Mobil pribadi', 99=>'Lainnya'] as $k => $v)
                                        <option value="{{ $k }}" {{ old('id_alat_transportasi', $data->id_alat_transportasi ?? '') == $k ? 'selected' : '' }}>{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
            
                        </div>
                    </div>
            
                    {{-- Footer --}}
                    <div class="px-6 md:px-8 py-4 border-t border-slate-100 flex justify-between items-center">
                        <button type="button" @click="slide = 1; window.scrollTo(0,0)" 
                                class="flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-semibold rounded-xl transition-all active:scale-95">
                            <i class="fas fa-arrow-left text-xs"></i> Kembali
                        </button>
                        <button type="button" @click="validateAndNext(2, 3, $data)" 
                                class="flex items-center gap-2 px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-xl transition-all shadow-sm active:scale-95">
                            Lanjut Data Ortu <i class="fas fa-arrow-right text-xs"></i>
                        </button>
                    </div>
            
                </div>
            </div>
        
            {{-- ==================================================== --}}
            {{-- SLIDE 3: DATA ORANG TUA                              --}}
            {{-- ==================================================== --}}
            <div x-show="slide === 3" 
                 x-transition:enter="transition ease-out duration-300 transform" 
                 x-transition:enter-start="opacity-0 translate-x-10" 
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 translate-x-0"
                 x-transition:leave-end="opacity-0 -translate-x-10"
                 style="display: none;">
            
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            
                    <div class="h-1 bg-gradient-to-r from-amber-400 via-yellow-300 to-amber-400"></div>
            
                    <div class="p-6 md:p-8">
            
                        {{-- Header --}}
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-11 h-11 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-user-friends text-amber-700"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800">Data Orang Tua</h3>
                                <p class="text-xs text-slate-400">Isi data ayah dan ibu kandung</p>
                            </div>
                        </div>
            
                        <div class="space-y-5">
            
                            {{-- DATA AYAH --}}
                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-5">
                                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-lg bg-slate-200 flex items-center justify-center">
                                        <i class="fas fa-male text-slate-500 text-xs"></i>
                                    </span>
                                    Data Ayah
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="label-text">Nama Ayah</label>
                                        <input type="text" 
                                               name="nama_ayah" 
                                               pattern="^[a-zA-Z\s.,']+$"
                                               value="{{ old('nama_ayah', $data->nama_ayah ?? $biodata->nama_ayah ?? '') }}" 
                                               class="input-glass @error('nama_ayah') input-error @enderror" 
                                               maxlength="100">
                                    </div>
                                    <div>
                                        <label class="label-text">NIK Ayah</label>
                                        <input type="text" 
                                               name="nik_ayah" 
                                               pattern="\d{16}" 
                                               inputmode="numeric" maxlength="16"
                                               oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                               value="{{ old('nik_ayah', $data->nik_ayah ?? $biodata->nik_ayah ?? '') }}" 
                                               class="input-glass @error('nik_ayah') input-error @enderror">
                                    </div>
                                    <div>
                                        <label class="label-text">Tanggal Lahir Ayah</label>
                                        <input type="date" 
                                               name="tanggal_lahir_ayah" 
                                               class="input-glass @error('tanggal_lahir_ayah') input-error @enderror"
                                               value="{{ old('tanggal_lahir_ayah', ($data->tanggal_lahir_ayah && $data->tanggal_lahir_ayah->format('Y-m-d') !== '2000-01-01' ? $data->tanggal_lahir_ayah->format('Y-m-d') : '')) }}">
                                    </div>
                                    <div>
                                        <label class="label-text">Pendidikan</label>
                                        <select name="id_jenjang_pendidikan_ayah" class="input-glass @error('id_jenjang_pendidikan_ayah') input-error @enderror" required>
                                            <option value="">Pilih...</option>
                                            @foreach([0=>'Tidak sekolah', 1=>'PAUD', 2=>'TK / sederajat', 3=>'Putus SD', 4=>'SD / sederajat', 5=>'SMP / sederajat', 6=>'SMA / sederajat', 20=>'D1', 21=>'D2', 22=>'D3', 23=>'D4', 30=>'S1', 35=>'S2', 40=>'S3', 99=>'Non formal'] as $k => $v)
                                                <option value="{{ $k }}" {{ old('id_jenjang_pendidikan_ayah', $data->id_jenjang_pendidikan_ayah ?? '') == $k ? 'selected' : '' }}>{{ $v }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="label-text">Pekerjaan</label>
                                        <select name="id_pekerjaan_ayah" class="input-glass @error('id_pekerjaan_ayah') input-error @enderror" required>
                                            <option value="">Pilih...</option>
                                            @foreach([1=>'Tidak bekerja', 2=>'Nelayan', 3=>'Petani', 4=>'Peternak', 5=>'PNS/TNI/Polri', 6=>'Karyawan Swasta', 7=>'Pedagang Kecil', 8=>'Pedagang Besar', 9=>'Wiraswasta', 10=>'Wirausaha', 11=>'Buruh', 12=>'Pensiunan', 13=>'Sudah Meninggal', 99=>'Lain-lain'] as $k => $v)
                                                <option value="{{ $k }}" {{ old('id_pekerjaan_ayah', $data->id_pekerjaan_ayah ?? '') == $k ? 'selected' : '' }}>{{ $v }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="label-text">Penghasilan</label>
                                        <select name="id_penghasilan_ayah" class="input-glass @error('id_penghasilan_ayah') input-error @enderror" required>
                                            <option value="">Pilih...</option>
                                            @foreach([0=>'Tidak Berpenghasilan', 11=>'Kurang dari Rp. 500.000', 12=>'Rp. 500.000 - Rp. 999.999', 13=>'Rp. 1.000.000 - Rp. 1.999.999', 14=>'Rp. 2.000.000 - Rp. 4.999.999', 15=>'Rp. 5.000.000 - Rp. 20.000.000', 16=>'Lebih dari Rp. 20.000.000'] as $k => $v)
                                                <option value="{{ $k }}" {{ old('id_penghasilan_ayah', $data->id_penghasilan_ayah ?? '') == $k ? 'selected' : '' }}>{{ $v }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
            
                            {{-- DATA IBU --}}
                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-5">
                                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-lg bg-slate-200 flex items-center justify-center">
                                        <i class="fas fa-female text-slate-500 text-xs"></i>
                                    </span>
                                    Data Ibu
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="label-text">Nama Ibu Kandung <span class="text-red-500">*</span></label>
                                        <input type="text" 
                                               name="nama_ibu_kandung" 
                                               pattern="^[a-zA-Z\s.,']+$"
                                               value="{{ old('nama_ibu_kandung', $data->nama_ibu_kandung ?? $biodata->nama_ibu ?? '') }}" 
                                               class="input-glass @error('nama_ibu_kandung') input-error @enderror" 
                                               required maxlength="100">
                                    </div>
                                    <div>
                                        <label class="label-text">NIK Ibu</label>
                                        <input type="text" 
                                               name="nik_ibu" 
                                               pattern="\d{16}" 
                                               inputmode="numeric" maxlength="16"
                                               oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                               value="{{ old('nik_ibu', $data->nik_ibu ?? $biodata->nik_ibu ?? '') }}" 
                                               class="input-glass @error('nik_ibu') input-error @enderror">
                                    </div>
                                    <div>
                                        <label class="label-text">Tanggal Lahir Ibu</label>
                                        <input type="date" 
                                               name="tanggal_lahir_ibu" 
                                               class="input-glass @error('tanggal_lahir_ibu') input-error @enderror"
                                               value="{{ old('tanggal_lahir_ibu', ($data->tanggal_lahir_ibu && $data->tanggal_lahir_ibu->format('Y-m-d') !== '2000-01-01' ? $data->tanggal_lahir_ibu->format('Y-m-d') : '')) }}">
                                    </div>
                                    <div>
                                        <label class="label-text">Pendidikan</label>
                                        <select name="id_jenjang_pendidikan_ibu" class="input-glass @error('id_jenjang_pendidikan_ibu') input-error @enderror" required>
                                            <option value="">Pilih...</option>
                                            @foreach([0=>'Tidak sekolah', 1=>'PAUD', 2=>'TK / sederajat', 3=>'Putus SD', 4=>'SD / sederajat', 5=>'SMP / sederajat', 6=>'SMA / sederajat', 20=>'D1', 21=>'D2', 22=>'D3', 23=>'D4', 30=>'S1', 35=>'S2', 40=>'S3', 99=>'Non formal'] as $k => $v)
                                                <option value="{{ $k }}" {{ old('id_jenjang_pendidikan_ibu', $data->id_jenjang_pendidikan_ibu ?? '') == $k ? 'selected' : '' }}>{{ $v }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="label-text">Pekerjaan</label>
                                        <select name="id_pekerjaan_ibu" class="input-glass @error('id_pekerjaan_ibu') input-error @enderror" required>
                                            <option value="">Pilih...</option>
                                            @foreach([1=>'Tidak bekerja', 2=>'Nelayan', 3=>'Petani', 4=>'Peternak', 5=>'PNS/TNI/Polri', 6=>'Karyawan Swasta', 7=>'Pedagang Kecil', 8=>'Pedagang Besar', 9=>'Wiraswasta', 10=>'Wirausaha', 11=>'Buruh', 12=>'Pensiunan', 13=>'Sudah Meninggal', 99=>'Lain-lain'] as $k => $v)
                                                <option value="{{ $k }}" {{ old('id_pekerjaan_ibu', $data->id_pekerjaan_ibu ?? '') == $k ? 'selected' : '' }}>{{ $v }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="label-text">Penghasilan</label>
                                        <select name="id_penghasilan_ibu" class="input-glass @error('id_penghasilan_ibu') input-error @enderror" required>
                                            <option value="">Pilih...</option>
                                            @foreach([0=>'Tidak Berpenghasilan', 11=>'Kurang dari Rp. 500.000', 12=>'Rp. 500.000 - Rp. 999.999', 13=>'Rp. 1.000.000 - Rp. 1.999.999', 14=>'Rp. 2.000.000 - Rp. 4.999.999', 15=>'Rp. 5.000.000 - Rp. 20.000.000', 16=>'Lebih dari Rp. 20.000.000'] as $k => $v)
                                                <option value="{{ $k }}" {{ old('id_penghasilan_ibu', $data->id_penghasilan_ibu ?? '') == $k ? 'selected' : '' }}>{{ $v }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
            
                        </div>
                    </div>
            
                    {{-- Footer --}}
                    <div class="px-6 md:px-8 py-4 border-t border-slate-100 flex justify-between items-center">
                        <button type="button" @click="slide = 2; window.scrollTo(0,0)" 
                                class="flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-semibold rounded-xl transition-all active:scale-95">
                            <i class="fas fa-arrow-left text-xs"></i> Kembali
                        </button>
                        <button type="button" @click="validateAndNext(3, 4, $data)" 
                                class="flex items-center gap-2 px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-xl transition-all shadow-sm active:scale-95">
                            Lanjut Finalisasi <i class="fas fa-arrow-right text-xs"></i>
                        </button>
                    </div>
            
                </div>
            </div>
        
            {{-- ==================================================== --}}
            {{-- SLIDE 4: WALI & FINALISASI                           --}}
            {{-- ==================================================== --}}
            <div x-show="slide === 4" 
                 x-transition:enter="transition ease-out duration-300 transform" 
                 x-transition:enter-start="opacity-0 translate-x-10" 
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 translate-x-0"
                 x-transition:leave-end="opacity-0 -translate-x-10"
                 style="display: none;">
            
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            
                    <div class="h-1 bg-gradient-to-r from-amber-400 via-yellow-300 to-amber-400"></div>
            
                    <div class="p-6 md:p-8">
            
                        {{-- Header --}}
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-11 h-11 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-clipboard-check text-amber-700"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800">Data Tambahan & Wali</h3>
                                <p class="text-xs text-slate-400">Langkah terakhir sebelum penyimpanan</p>
                            </div>
                        </div>
            
                        {{-- DATA KPS/NPWP --}}
                        <div class="mb-5 p-5 bg-slate-50 border border-slate-200 rounded-xl">
                            <h4 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4">Data Kesejahteraan Keluarga</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4"
                                 x-data="{ isKps: '{{ old('penerima_kps', $data->penerima_kps ?? '0') }}' }">
            
                                <div>
                                    <label class="label-text">Penerima KPS? <span class="text-red-500">*</span></label>
                                    <select name="penerima_kps"
                                            class="input-glass w-full @error('penerima_kps') input-error @enderror"
                                            required x-model="isKps">
                                        <option value="0">0: Bukan Penerima KPS</option>
                                        <option value="1">1: Penerima KPS</option>
                                    </select>
                                </div>
            
                                <div>
                                    <label class="label-text">Nomor KPS (Jika Ada)</label>
                                    <input type="text"
                                           name="no_kps"
                                           value="{{ old('no_kps', $data->no_kps ?? '') }}"
                                           class="input-glass w-full @error('no_kps') input-error @enderror"
                                           placeholder="Nomor KPS" maxlength="80"
                                           pattern="^[a-zA-Z0-9.-]+$"
                                           :disabled="isKps != '1'"
                                           :class="isKps != '1' ? 'bg-slate-200 text-slate-400 cursor-not-allowed opacity-60' : ''"
                                           :required="isKps == '1'">
                                </div>
            
                                <div class="md:col-span-2">
                                    <label class="label-text">NPWP (Pribadi/Ortu)</label>
                                    <input type="text"
                                           name="npwp"
                                           value="{{ old('npwp', $data->npwp ?? '') }}"
                                           class="input-glass @error('npwp') input-error @enderror"
                                           placeholder="Nomor Pokok Wajib Pajak" maxlength="20"
                                           pattern="^[0-9.-]+$"
                                           oninput="this.value = this.value.replace(/[^0-9.-]/g, '')">
                                </div>
            
                            </div>
                        </div>
            
                        {{-- DATA WALI --}}
                        <div x-data="{ hasWali: false }" class="mb-2">
            
                            <div class="flex items-center gap-2.5 p-3.5 bg-slate-50 border border-slate-200 rounded-xl cursor-pointer hover:bg-amber-50 hover:border-amber-200 transition-all"
                                 @click="hasWali = !hasWali">
                                <input type="checkbox" id="checkWali" x-model="hasWali"
                                       class="w-4 h-4 accent-amber-500 rounded cursor-pointer">
                                <label class="text-sm font-semibold text-slate-700 cursor-pointer select-none">
                                    Saya tinggal bersama Wali <span class="text-xs font-normal text-slate-400">(isi jika ada)</span>
                                </label>
                            </div>
            
                            <div x-show="hasWali" x-transition class="mt-3 p-5 bg-slate-50 border border-slate-200 rounded-xl">
                                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-lg bg-slate-200 flex items-center justify-center">
                                        <i class="fas fa-user-shield text-slate-500 text-xs"></i>
                                    </span>
                                    Data Wali
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="label-text">Nama Wali</label>
                                        <input type="text"
                                               name="nama_wali"
                                               pattern="^[a-zA-Z\s.,']+$"
                                               value="{{ old('nama_wali', $data->nama_wali ?? '') }}"
                                               class="input-glass @error('nama_wali') input-error @enderror"
                                               maxlength="100">
                                    </div>
                                    <div>
                                        <label class="label-text">NIK Wali <span class="text-[10px] text-slate-400 font-normal">(16 Digit)</span></label>
                                        <input type="text"
                                               name="nik_wali"
                                               value="{{ old('nik_wali', $data->nik_wali ?? '') }}"
                                               class="input-glass @error('nik_wali') input-error @enderror"
                                               maxlength="16" pattern="[0-9]{16}"
                                               oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                               placeholder="Contoh: 3215012345678901">
                                    </div>
                                    <div>
                                        <label class="label-text">Tanggal Lahir Wali</label>
                                        <input type="date"
                                               name="tanggal_lahir_wali"
                                               value="{{ old('tanggal_lahir_wali', isset($data->tanggal_lahir_wali) ? $data->tanggal_lahir_wali->format('Y-m-d') : '') }}"
                                               class="input-glass @error('tanggal_lahir_wali') input-error @enderror">
                                    </div>
                                    <div>
                                        <label class="label-text">Pendidikan Wali</label>
                                        <select name="id_jenjang_pendidikan_wali" class="input-glass @error('id_jenjang_pendidikan_wali') input-error @enderror">
                                            <option value="">Pilih...</option>
                                            @foreach([0=>'Tidak sekolah', 1=>'PAUD', 2=>'TK / sederajat', 3=>'Putus SD', 4=>'SD / sederajat', 5=>'SMP / sederajat', 6=>'SMA / sederajat', 20=>'D1', 21=>'D2', 22=>'D3', 23=>'D4', 30=>'S1', 35=>'S2', 40=>'S3', 99=>'Non formal'] as $k => $v)
                                                <option value="{{ $k }}" {{ old('id_jenjang_pendidikan_wali', $data->id_jenjang_pendidikan_wali ?? '') == $k ? 'selected' : '' }}>{{ $v }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="label-text">Pekerjaan Wali</label>
                                        <select name="id_pekerjaan_wali" class="input-glass @error('id_pekerjaan_wali') input-error @enderror">
                                            <option value="">Pilih...</option>
                                            @foreach([1=>'Tidak bekerja', 2=>'Nelayan', 3=>'Petani', 4=>'Peternak', 5=>'PNS/TNI/Polri', 6=>'Karyawan Swasta', 7=>'Pedagang Kecil', 8=>'Pedagang Besar', 9=>'Wiraswasta', 10=>'Wirausaha', 11=>'Buruh', 12=>'Pensiunan', 13=>'Sudah Meninggal', 99=>'Lain-lain'] as $k => $v)
                                                <option value="{{ $k }}" {{ old('id_pekerjaan_wali', $data->id_pekerjaan_wali ?? '') == $k ? 'selected' : '' }}>{{ $v }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="label-text">Penghasilan Wali</label>
                                        <select name="id_penghasilan_wali" class="input-glass @error('id_penghasilan_wali') input-error @enderror">
                                            <option value="">Pilih...</option>
                                            @foreach([0=>'Tidak Berpenghasilan', 11=>'Kurang dari Rp. 500.000', 12=>'Rp. 500.000 - Rp. 999.999', 13=>'Rp. 1.000.000 - Rp. 1.999.999', 14=>'Rp. 2.000.000 - Rp. 4.999.999', 15=>'Rp. 5.000.000 - Rp. 20.000.000', 16=>'Lebih dari Rp. 20.000.000'] as $k => $v)
                                                <option value="{{ $k }}" {{ old('id_penghasilan_wali', $data->id_penghasilan_wali ?? '') == $k ? 'selected' : '' }}>{{ $v }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
            
                    </div>
            
                    {{-- Footer --}}
                    <div class="px-6 md:px-8 py-4 border-t border-slate-100 flex justify-between items-center">
                        <button type="button" @click="slide = 3; window.scrollTo(0,0)"
                                class="flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-semibold rounded-xl transition-all active:scale-95">
                            <i class="fas fa-arrow-left text-xs"></i> Kembali
                        </button>
                        <button type="button" onclick="confirmSubmit()"
                                class="flex items-center gap-2 px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-bold rounded-xl transition-all shadow-sm shadow-amber-200 active:scale-95">
                            <i class="fas fa-check-double"></i> Simpan Permanen
                        </button>
                    </div>
            
                </div>
            </div>

    {{-- SCRIPT --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // 1. POPUP WARNING SAAT HALAMAN DIMUAT (AUTO RUN)
        document.addEventListener("DOMContentLoaded", function () {
            Swal.fire({
                html: `
                    <div style="display:flex; flex-direction:column; align-items:center; gap:12px; padding: 0.5rem 0 0;">
                        <div style="width:48px; height:48px; border-radius:50%; background:#FAEEDA; display:flex; align-items:center; justify-content:center;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#854F0B" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a12 12 0 0 0 8.5 3A12 12 0 0 1 12 21 12 12 0 0 1 3.5 6 12 12 0 0 0 12 3"/><path d="M12 8v4m0 4h.01"/></svg>
                        </div>
                        <div style="text-align:center;">
                            <p style="font-size:16px; font-weight:500; color:#111827; margin:0 0 4px;">Perhatian: data bersifat final & permanen</p>
                            <p style="font-size:13px; color:#6B7280; margin:0;">Anda akan mengisi formulir data final untuk pelaporan <strong style="color:#111827; font-weight:500;">PDDIKTI</strong>.</p>
                        </div>
                        <div style="width:100%; border-top:0.5px solid #E5E7EB; margin: 4px 0;"></div>
                        <div style="width:100%; display:flex; flex-direction:column; gap:8px;">
                            <div style="display:flex; align-items:flex-start; gap:10px; padding:10px 12px; border-radius:8px; border:0.5px solid #FAC775; background:#FAEEDA;">
                                <svg style="flex-shrink:0; margin-top:1px;" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#854F0B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                <span style="font-size:13px; color:#633806;">Data <strong style="font-weight:500;">tidak dapat diubah</strong> setelah disimpan.</span>
                            </div>
                            <div style="display:flex; align-items:flex-start; gap:10px; padding:10px 12px; border-radius:8px; border:0.5px solid #FAC775; background:#FAEEDA;">
                                <svg style="flex-shrink:0; margin-top:1px;" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#854F0B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                <span style="font-size:13px; color:#633806;">Isi dengan teliti — periksa ejaan nama, NIK, dan tanggal lahir.</span>
                            </div>
                            <div style="display:flex; align-items:flex-start; gap:10px; padding:10px 12px; border-radius:8px; border:0.5px solid #FAC775; background:#FAEEDA;">
                                <svg style="flex-shrink:0; margin-top:1px;" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#854F0B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/></svg>
                                <span style="font-size:13px; color:#633806;">Data ini sangat penting untuk keperluan akademik resmi.</span>
                            </div>
                        </div>
                        <div style="width:100%; border-top:0.5px solid #E5E7EB; margin: 4px 0;"></div>
                        <p style="font-size:12px; color:#9CA3AF; text-align:center; margin:0; font-style:italic;">"Saya bertanggung jawab penuh atas kebenaran data yang saya isikan."</p>
                    </div>
                `,
                showConfirmButton: true,
                confirmButtonText: 'Saya mengerti & akan mengisi dengan benar',
                confirmButtonColor: '#854F0B',
                allowOutsideClick: false,
                allowEscapeKey: false,
                width: '520px',
                customClass: {
                    confirmButton: 'swal-btn-amber',
                    popup: 'swal-popup-clean'
                }
            });
        });

        // 2. CONFIRM SUBMIT
        function confirmSubmit() {
            Swal.fire({
                title: 'Yakin Data Sudah Benar?',
                html: "Data akan dikunci dan dikirim ke PDDIKTI.<br><b>Anda tidak bisa mengubahnya lagi!</b>",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981', 
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Simpan Permanen!',
                cancelButtonText: 'Periksa Lagi'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Menyimpan...',
                        text: 'Mohon jangan tutup halaman ini.',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });
                    document.getElementById('form-final').submit();
                }
            })
        }
        
        function validateAndNext(currentSlide, nextSlide, alpineData) {
            
            // 1. Cari Container Slide
            const container = document.querySelector(`div[x-show="slide === ${currentSlide}"]`);
            if (!container) return;

            // 2. Ambil Input & Select
            const inputs = container.querySelectorAll('input, select');
            
            let isValid = true;
            let firstError = null;

            // DAFTAR DATA DUMMY YANG HARUS DITOLAK
            const dummyValues = [
                '0000000000000000', // NIK Dummy
                '-',                // String Dummy
                '2000-01-01',       // Tanggal Dummy
                '000000'            // Wilayah Dummy
            ];

            // 3. Cek Satu per Satu
            inputs.forEach(input => {
                // Bersihkan error lama
                input.classList.remove('input-error');

                const val = input.value.trim();
                
                // CEK A: Validasi HTML5 Standar
                const isHtmlInvalid = !input.checkValidity();

                // CEK B: Validasi Data Dummy
                const isDummyData = input.hasAttribute('required') && dummyValues.includes(val);

                // CEK C: Validasi Select
                const isInvalidSelect = input.tagName === 'SELECT' && input.hasAttribute('required') && val === "";

                // --- KEPUTUSAN FINAL ---
                if (isHtmlInvalid || isDummyData || isInvalidSelect) {
                    isValid = false;
                    
                    input.classList.add('input-error'); 
                    
                    if (!firstError) firstError = input;
                }
            });

            // 4. Aksi Lanjut / Stop
            if (isValid) {
                alpineData.slide = nextSlide;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Data Belum Lengkap / Masih Default',
                    html: 'Mohon isi data dengan benar.<br>Pastikan tidak ada data yang masih <b>strip (-)</b>, <b>0000</b>, atau <b>kosong</b>.',
                    confirmButtonText: 'Perbaiki',
                    confirmButtonColor: '#d33',
                });

                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
            }
        }
    </script>

    {{-- CUSTOM CSS --}}
    <style>
        .glass-white { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.5); }
        .glass-form { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.6); border-radius: 1rem; padding: 1.5rem; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .section-title { font-size: 1.125rem; font-weight: 800; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem; border-bottom: 1px solid #f3f4f6; padding-bottom: 1rem; }
        .input-glass { width: 100%; margin-top: 0.25rem; background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 0.6rem 1rem; color: #1f2937; font-size: 0.875rem; transition: all 0.2s; }
        .input-glass:focus { outline: none; border-color: #3b82f6; background-color: white; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
        .label-text { font-size: 0.75rem; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-left: 0.25rem; }
        .step-btn { padding: 0.5rem 1rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 700; color: #4b5563; background: transparent; transition: all 0.3s; display: flex; align-items: center; }
        .step-btn.active-step { color: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .btn-next { color: white; font-weight: 700; padding: 0.75rem 1.5rem; border-radius: 0.75rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 0.5rem; transition: transform 0.2s; }
        .btn-next:hover { transform: translateY(-2px); }
        .btn-back { color: #6b7280; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; transition: color 0.2s; }
        .btn-back:hover { color: #1f2937; }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-4px); }
            20%, 40%, 60%, 80% { transform: translateX(4px); }
        }
        .input-error {
            border-color: #ef4444 !important; /* Merah */
            background-color: #fef2f2 !important; /* Pink tipis */
            animation: shake 0.4s ease-in-out; /* Efek getar */
        }
        .swal-popup-clean {
            border-radius: 16px !important;
            padding: 1.5rem !important;
        }
        .swal-btn-amber {
            border-radius: 8px !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            padding: 10px 20px !important;
            width: 100% !important;
        }
    </style>
@endif
@endsection

{{-- Jika file komponen ini ada di S1, berarti di sini juga bisa dipakai selama path-nya bener --}}
@include('mahasiswa.components.modal-tutorial-nisn')