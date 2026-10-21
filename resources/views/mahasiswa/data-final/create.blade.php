@extends('layouts.mahasiswa.app')

@section('title', 'Data Final LDDIKTI')

@section('content')
@php
    $isMaintenance = false;
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
        'id_alat_transportasi', 'nama_wilayah', 'kecamatan'
    ])) {
        $targetSlide = 2;
    }
    // Cek Error di Slide 3 (Orang Tua)
    elseif($errors->hasAny([
        'nama_ayah', 'nik_ayah', 'tanggal_lahir_ayah', 'jenjang_pendidikan_ayah', 
        'pekerjaan_ayah', 'penghasilan_ayah', 'nama_ibu_kandung', 'nik_ibu', 
        'tanggal_lahir_ibu', 'jenjang_pendidikan_ibu', 'pekerjaan_ibu', 
        'penghasilan_ibu', 'ibu_kandung'
    ])) {
        $targetSlide = 3;
    }
    // Cek Error di Slide 4 (Wali & Finalisasi)
    elseif($errors->hasAny([
        'penerima_kps', 'no_kps', 'npwp', 'nama_wali', 'tanggal_lahir_wali', 
        'jenjang_pendidikan_wali', 'pekerjaan_wali', 'penghasilan_wali',
        'is_final' // Tambahkan ini jika ada checkbox konfirmasi finalisasi
    ])) {
        $targetSlide = 4;
    }
}
@endphp
@if($isMaintenance)
    {{-- TAMPILAN SAAT MAINTENANCE --}}
    <div class="max-w-3xl mx-auto mt-12 pb-24 px-4 text-center">
        <div class="bg-white/95 backdrop-blur-md border border-white/60 rounded-3xl shadow-2xl p-10">
            <div class="w-20 h-20 bg-amber-100 text-amber-500 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-tools text-4xl animate-bounce"></i>
            </div>
            <h2 class="text-3xl font-extrabold text-gray-800 mb-4">Pembaruan Sistem</h2>
            <p class="text-gray-600 mb-8 leading-relaxed">
                Mohon maaf, pengisian <b>Data Final PDDIKTI</b> sedang dikunci sementara untuk pemeliharaan sistem. Silakan kembali lagi nanti.
            </p>
            <a href="{{ route('mahasiswa.dashboard') }}" class="bg-gray-800 hover:bg-gray-900 text-white font-bold py-3 px-8 rounded-xl shadow-lg transition-all">
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

<div class="max-w-5xl mx-auto pb-24" 
     x-data="{ 
        slide: {{ $targetSlide }},
        showErrorModal: {{ $errors->any() ? 'true' : 'false' }} 
     }">

    @include('mahasiswa.data-final.partials.error-modal')


    {{-- HEADER --}}
    <div class="text-center mb-6">
        <h1 class="text-3xl font-bold text-white drop-shadow-md">Data Final Mahasiswa 📋</h1>
        <p class="text-blue-100 mt-2 text-sm opacity-90">
            Lengkapi data pelaporan <b>PDDIKTI</b>.
        </p>
    </div>

    @include('mahasiswa.data-final.partials.warning-banner')


    @include('mahasiswa.data-final.partials.indicator-slide')


    {{-- ERROR ALERT --}}
    @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded mb-6 mx-4 md:mx-0 text-sm">
            <ul class="list-disc ml-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- FORM START --}}
    <form id="form-final" action="{{ route('mahasiswa.data-final.store') }}" method="POST">
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
            <div class="bg-white/70 backdrop-blur-2xl border border-white/60 rounded-[2rem] p-6 md:p-10 relative overflow-hidden shadow-2xl bg-gradient-to-br from-blue-200/50 via-white/40 to-red-200/50">
                <div class="absolute top-0 left-0 w-1.5 h-full bg-blue-500"></div>
                <h3 class="section-title text-blue-700"><i class="fas fa-id-card"></i> Identitas Pribadi</h3>
    
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <label class="label-text">Nama Lengkap (Sesuai Ijazah) <span class="text-red-500">*</span></label>
                        <input type="text" 
                               name="nama_mahasiswa" 
                               pattern="^[a-zA-Z\s.,']+$"
                               title="Hanya boleh Huruf, Spasi, Titik, Koma, dan Tanda Petik"
                               value="{{ old('nama_mahasiswa', $data->nama_mahasiswa ?? $biodata->nama_lengkap ?? Auth::user()->name ?? '') }}" 
                               class="input-glass @error('nama_mahasiswa') input-error @enderror" 
                               required maxlength="100">
                    </div>
                    <div>
                        <label class="label-text">NIK (KTP) <span class="text-red-500">*</span></label>
                        {{-- FIXED: Type Text + Regex Pattern --}}
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
                    <div>
                        <label class="label-text">NISN <span class="text-red-500 font-bold">*</span></label>
                        {{-- FIXED: Type Text + Regex Pattern --}}
                        <input type="text" 
                               name="nisn" 
                               pattern="\d{10}" 
                               inputmode="numeric" 
                               maxlength="10"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                               title="NISN harus berisi persis 10 digit angka"
                               value="{{ old('nisn', $data->nisn ?? $biodata->nisn ?? '') }}" 
                               class="input-glass @error('nisn') input-error @enderror" 
                               placeholder="Nomor Induk Siswa Nasional"
                               required>
                        
                        {{-- [TAMBAHAN BARU] Info Box Lupa NISN & Tombol --}}
                        <div class="mt-6 relative overflow-hidden rounded-2xl border border-blue-200 bg-white shadow-sm" x-data="{ showIframe: false }">
                            
                            {{-- Decorative Background Glow --}}
                            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-blue-50 rounded-full blur-2xl opacity-60"></div>
                        
                            <div class="p-5 relative z-10">
                                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                    <div class="flex gap-4 items-start">
                                        <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-lg shadow-blue-200">
                                            <i class="fas fa-search-location text-lg"></i>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-black text-slate-800 uppercase tracking-tight">Pencarian NISN</h4>
                                            <p class="text-[11px] text-slate-500 leading-relaxed max-w-md">
                                                Kehilangan data NISN? Anda dapat melakukan validasi mandiri melalui sistem Pusdatin Kemendikbud secara langsung di sini.
                                            </p>
                                        </div>
                                    </div>
                        
                                    {{-- Button Group dengan Style Glass --}}
                                    <div class="flex items-center gap-2">
                                        <button 
                                            @click="showIframe = !showIframe" 
                                            class="group flex items-center gap-2 px-4 py-2.5 rounded-xl transition-all duration-300 active:scale-95 border"
                                            :class="showIframe ? 'bg-rose-50 border-rose-200 text-rose-600' : 'bg-blue-600 border-blue-500 text-white shadow-md shadow-blue-100 hover:bg-blue-700'">
                                            <i class="fas" :class="showIframe ? 'fa-times-circle' : 'fa-window-maximize'"></i>
                                            <span class="text-[10px] font-bold uppercase tracking-wider" x-text="showIframe ? 'Tutup Panel' : 'Cek NISN Sekarang'"></span>
                                        </button>
                        
                                        <a href="https://nisn.data.kemdikbud.go.id/" target="_blank" 
                                           class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-all shadow-sm"
                                           title="Buka di Tab Baru">
                                            <i class="fas fa-external-link-alt text-xs"></i>
                                        </a>
                                    </div>
                                </div>
                        
                                {{-- AREA IFRAME (MOCKUP BROWSER) --}}
                                <div 
                                    x-show="showIframe" 
                                    x-transition:enter="transition ease-out duration-500"
                                    x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
                                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                    x-transition:leave="transition ease-in duration-300"
                                    class="mt-6 w-full"
                                >
                                    <div class="rounded-2xl border border-slate-200 overflow-hidden shadow-2xl bg-white">
                                        {{-- Toolbar Ala Browser --}}
                                        <div class="bg-slate-50 border-b border-slate-200 px-4 py-3 flex items-center gap-4">
                                            <div class="flex gap-1.5">
                                                <div class="w-3 h-3 rounded-full bg-slate-300"></div>
                                                <div class="w-3 h-3 rounded-full bg-slate-300"></div>
                                                <div class="w-3 h-3 rounded-full bg-slate-300"></div>
                                            </div>
                                            <div class="flex-1 bg-white border border-slate-200 rounded-lg px-3 py-1 flex items-center justify-between">
                                                <span class="text-[10px] text-slate-400 font-mono truncate">https://nisn.data.kemdikbud.go.id</span>
                                                <i class="fas fa-lock text-[9px] text-emerald-500"></i>
                                            </div>
                                        </div>
                        
                                        {{-- Iframe Container --}}
                                        <div class="relative bg-slate-100 h-[500px] md:h-[600px]">
                                            <iframe 
                                                src="https://nisn.data.kemdikbud.go.id/" 
                                                class="w-full h-full border-none relative z-10" 
                                                title="Website Cek NISN">
                                            </iframe>
                                            
                                            {{-- Loader Sederhana (Akan tertutup saat iframe load) --}}
                                            <div class="absolute inset-0 flex flex-col items-center justify-center z-0">
                                                <i class="fas fa-circle-notch fa-spin text-blue-500 text-2xl mb-2"></i>
                                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Menghubungkan ke Kemendikbud...</span>
                                            </div>
                                        </div>
                        
                                        {{-- Footer Browser --}}
                                        <div class="bg-blue-600 px-4 py-2 flex items-center justify-between">
                                            <p class="text-[9px] text-blue-100 font-medium italic">
                                                *Gunakan nama ibu kandung dan tempat lahir untuk pencarian data.
                                            </p>
                                            <div class="flex gap-2">
                                                 <div class="w-1 h-1 rounded-full bg-blue-300 animate-pulse"></div>
                                                 <div class="w-1 h-1 rounded-full bg-blue-300 animate-pulse" style="animation-delay: 0.2s"></div>
                                                 <div class="w-1 h-1 rounded-full bg-blue-300 animate-pulse" style="animation-delay: 0.4s"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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
                    <div>
                        <label class="label-text">Tanggal Lahir <span class="text-red-500">*</span></label>
                        <input type="date" 
                               name="tanggal_lahir" 
                               class="input-glass @error('tanggal_lahir') input-error @enderror" 
                               required
                               value="{{ old('tanggal_lahir', ($data->tanggal_lahir && $data->tanggal_lahir->format('Y-m-d') !== '2000-01-01' ? $data->tanggal_lahir->format('Y-m-d') : '')) }}">
                    </div>
                    <div>
                        <label class="label-text">Jenis Kelamin <span class="text-red-500">*</span></label>
                        <select name="jenis_kelamin" class="input-glass @error('jenis_kelamin') input-error @enderror" required>
                            @php $jk = old('jenis_kelamin', $data->jenis_kelamin ?? $biodata->jenis_kelamin ?? ''); @endphp
                            <option value="">Pilih...</option>
                            <option value="L" {{ ($jk == 'L' || $jk == 'Laki-laki') ? 'selected' : '' }}>Laki-laki (L)</option>
                            <option value="P" {{ ($jk == 'P' || $jk == 'Perempuan') ? 'selected' : '' }}>Perempuan (P)</option>
                            <option value="*" {{ ($jk == '*') ? 'selected' : '' }}>Belum ada informasi (*)</option>
                        </select>
                    </div>
                    <div>
                        <label class="label-text">Agama <span class="text-red-500">*</span></label>
                        <select name="id_agama" class="input-glass @error('id_agama') input-error @enderror" required>
                            @php $agm = old('id_agama', $data->id_agama ?? $biodata->agama ?? ''); @endphp
                            <option value="">Pilih Agama</option>
                            <option value="1" {{ ($agm == 1) ? 'selected' : '' }}>Islam</option>
                            <option value="2" {{ ($agm == 2) ? 'selected' : '' }}>Kristen</option>
                            <option value="3" {{ ($agm == 3) ? 'selected' : '' }}>Katholik</option>
                            <option value="4" {{ ($agm == 4) ? 'selected' : '' }}>Hindu</option>
                            <option value="5" {{ ($agm == 5) ? 'selected' : '' }}>Budha</option>
                            <option value="6" {{ ($agm == 6) ? 'selected' : '' }}>Konghucu</option>
                            <option value="99" {{ ($agm == 99) ? 'selected' : '' }}>Lainnya</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="label-text">Kewarganegaraan <span class="text-red-500">*</span></label>
                        <select name="kewarganegaraan" class="input-glass @error('kewarganegaraan') input-error @enderror" required>
                            <option value="ID" {{ (old('kewarganegaraan', $data->kewarganegaraan) == 'ID' || old('kewarganegaraan', $data->kewarganegaraan) == 'WNI') ? 'selected' : '' }}>Indonesia (WNI)</option>
                            <option value="AS" {{ (old('kewarganegaraan', $data->kewarganegaraan) == 'AS' || old('kewarganegaraan', $data->kewarganegaraan) == 'WNA') ? 'selected' : '' }}>Asing (WNA)</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end mt-8">
                    <button type="button" @click="validateAndNext(1, 2, $data)" class="btn-next bg-blue-600 hover:bg-blue-700">Lanjut Domisili <i class="fas fa-arrow-right"></i></button>
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
            <div class="bg-white/70 backdrop-blur-2xl border border-white/60 rounded-[2rem] p-6 md:p-10 relative overflow-hidden shadow-2xl bg-gradient-to-br from-blue-200/50 via-white/40 to-red-200/50">
                <div class="absolute top-0 left-0 w-1.5 h-full bg-indigo-500"></div>
                <h3 class="section-title text-indigo-700"><i class="fas fa-map-marked-alt"></i> Domisili & Kontak</h3>
    
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="bg-indigo-50/50 p-4 rounded-xl border border-indigo-100 md:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="label-text">No. Handphone</label>
                            {{-- FIXED: Type Text + Regex Pattern --}}
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
                            {{-- FIXED: Type Text + Regex Pattern --}}
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
                    <div class="md:col-span-2 mt-2">
                        <label class="label-text">Jalan</label>
                        <input type="text" 
                               name="jalan" 
                               value="{{ old('jalan', $data->jalan ?? $biodata->alamat_jalan ?? '') }}" 
                               class="input-glass @error('jalan') input-error @enderror" 
                               placeholder="Nama Jalan" maxlength="80">
                    </div>
                    <div class="md:col-span-2">
                        <label class="label-text">Dusun</label>
                        <input type="text" 
                               name="dusun" 
                               value="{{ old('dusun', $data->dusun ?? '') }}" 
                               class="input-glass @error('dusun') input-error @enderror" 
                               placeholder="Nama Dusun" maxlength="60">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="label-text">RT</label>
                            {{-- FIXED: Type Text + Regex --}}
                            <input type="text" 
                                   name="rt" 
                                   pattern="\d{1,3}" 
                                   inputmode="numeric"
                                   maxlength="3"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                   value="{{ old('rt', $data->rt ?? $biodata->rt ?? '') }}" 
                                   class="input-glass @error('rt') input-error @enderror">
                        </div>
                        <div>
                            <label class="label-text">RW</label>
                            {{-- FIXED: Type Text + Regex --}}
                            <input type="text" 
                                   name="rw" 
                                   pattern="\d{1,3}" 
                                   inputmode="numeric"
                                   maxlength="3"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                   value="{{ old('rw', $data->rw ?? $biodata->rw ?? '') }}" 
                                   class="input-glass @error('rw') input-error @enderror">
                        </div>
                    </div>
                    <div>
                        <label class="label-text">Kelurahan / Desa <span class="text-red-500">*</span></label>
                        <input type="text" 
                               name="kelurahan" 
                               pattern="^[a-zA-Z0-9\s.-]+$"
                               value="{{ old('kelurahan', $data->kelurahan ?? $biodata->kelurahan ?? '') }}" 
                               class="input-glass @error('kelurahan') input-error @enderror" 
                               required maxlength="60">
                    </div>
                    <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-4" 
                         x-data="{ 
                             provinces: [], 
                             cities: [], 
                             districts: [], 
                             // Tangkap data 'old' dari Laravel jika ada error, atau data dari database
                             selectedProv: '{{ old('id_provinsi', $data->id_provinsi ?? '') }}', 
                             selectedCity: '{{ old('id_kabupaten', $data->id_kabupaten ?? '') }}', 
                             selectedDist: '{{ old('id_kecamatan', $data->id_kecamatan ?? '') }}',
                             
                             init() {
                                 // 1. Selalu fetch provinsi di awal
                                 fetch('/api/wilayah/provinces')
                                     .then(res => res.json())
                                     .then(data => this.provinces = data);
                                     
                                 // 2. Jika ada 'old' Provinsi, langsung fetch Kota tanpa nunggu diklik
                                 if (this.selectedProv) {
                                     fetch(`/api/wilayah/cities/${this.selectedProv}`)
                                         .then(res => res.json())
                                         .then(data => this.cities = data);
                                 }
                                 
                                 // 3. Jika ada 'old' Kota, langsung fetch Kecamatan
                                 if (this.selectedCity) {
                                     fetch(`/api/wilayah/districts/${this.selectedCity}`)
                                         .then(res => res.json())
                                         .then(data => this.districts = data);
                                 }
                             },
                             fetchCities() {
                                 this.selectedCity = ''; 
                                 this.selectedDist = ''; 
                                 this.cities = []; 
                                 this.districts = [];
                                 if(this.selectedProv) {
                                     fetch(`/api/wilayah/cities/${this.selectedProv}`)
                                         .then(res => res.json())
                                         .then(data => this.cities = data);
                                 }
                             },
                             fetchDistricts() {
                                 this.selectedDist = ''; 
                                 this.districts = [];
                                 if(this.selectedCity) {
                                     fetch(`/api/wilayah/districts/${this.selectedCity}`)
                                         .then(res => res.json())
                                         .then(data => this.districts = data);
                                 }
                             }
                         }">
                        
                        {{-- 1. Pilih Provinsi --}}
                        <div>
                            <label class="label-text">Provinsi <span class="text-red-500">*</span></label>
                            {{-- TAMBAH ATRIBUT NAME DI SINI --}}
                            <select name="id_provinsi" x-model="selectedProv" @change="fetchCities()" class="input-glass @error('id_provinsi') input-error @enderror" required>
                                <option value="">Pilih Provinsi...</option>
                                <template x-for="prov in provinces" :key="prov.id">
                                    <option :value="prov.id" x-text="prov.name" :selected="prov.id == selectedProv"></option>
                                </template>
                            </select>
                        </div>

                        {{-- 2. Pilih Kabupaten/Kota --}}
                        <div>
                            <label class="label-text">Kabupaten/Kota <span class="text-red-500">*</span></label>
                            {{-- TAMBAH ATRIBUT NAME DI SINI --}}
                            <select name="id_kabupaten" x-model="selectedCity" @change="fetchDistricts()" class="input-glass @error('id_kabupaten') input-error @enderror" :disabled="!selectedProv" required>
                                <option value="">Pilih Kab/Kota...</option>
                                <template x-for="city in cities" :key="city.id">
                                    <option :value="city.id" x-text="city.name" :selected="city.id == selectedCity"></option>
                                </template>
                            </select>
                        </div>

                        {{-- 3. Pilih Kecamatan --}}
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
                    <div>
                        <label class="label-text">Kode Pos</label>
                        {{-- FIXED: Type Text + Regex --}}
                        <input type="text" 
                               name="kode_pos" 
                               pattern="\d{5}" 
                               inputmode="numeric"
                               maxlength="5"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                               value="{{ old('kode_pos', $data->kode_pos ?? $biodata->kode_pos ?? '') }}" 
                               class="input-glass @error('kode_pos') input-error @enderror">
                    </div>
                    <div>
                        <label class="label-text">Jenis Tinggal</label>
                        <select name="id_jenis_tinggal" class="input-glass @error('id_jenis_tinggal') input-error @enderror" required>
                            <option value="">Pilih...</option>
                            @foreach([1=>'Bersama orang tua', 2=>'Wali', 3=>'Kos', 4=>'Asrama', 5=>'Panti Asuhan', 99=>'Lainnya'] as $k => $v)
                                <option value="{{ $k }}" {{ old('id_jenis_tinggal', $data->id_jenis_tinggal ?? '') == $k ? 'selected' : '' }}>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label-text">Alat Transportasi</label>
                        <select name="id_alat_transportasi" class="input-glass @error('id_alat_transportasi') input-error @enderror" required>
                            <option value="">Pilih...</option>
                            @foreach([1=>'Jalan kaki', 2=>'Angkutan umum/bus/pete-pete', 3=>'Mobil jemputan', 4=>'Kereta api', 5=>'Ojek', 6=>'Andong/Bendi/Sado/Becak', 7=>'Perahu penyeberangan/Rakit/Getek', 8=>'Kuda', 9=>'Sepeda', 10=>'Sepeda motor', 11=>'Mobil pribadi', 99=>'Lainnya'] as $k => $v)
                                <option value="{{ $k }}" {{ old('id_alat_transportasi', $data->id_alat_transportasi ?? '') == $k ? 'selected' : '' }}>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex justify-between mt-8 pt-4 border-t border-gray-200">
                    <button type="button" @click="slide = 1; window.scrollTo(0,0)" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali</button>
                    <button type="button" @click="validateAndNext(2, 3, $data)" class="btn-next bg-indigo-600 hover:bg-indigo-700">Lanjut Data Ortu <i class="fas fa-arrow-right"></i></button>
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
            <div class="bg-white/70 backdrop-blur-2xl border border-white/60 rounded-[2rem] p-6 md:p-10 relative overflow-hidden shadow-2xl bg-gradient-to-br from-blue-200/50 via-white/40 to-red-200/50">
                <div class="absolute top-0 left-0 w-1.5 h-full bg-purple-500"></div>
                <h3 class="section-title text-purple-700"><i class="fas fa-user-friends"></i> Data Orang Tua</h3>
    
                <div class="space-y-6">
                    <div class="bg-blue-50/60 p-5 rounded-2xl border border-blue-100">
                        <h4 class="text-sm font-bold text-blue-800 mb-3 flex items-center gap-2">
                            <span class="w-6 h-6 rounded bg-blue-200 flex items-center justify-center text-xs"><i class="fas fa-male"></i></span> DATA AYAH
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
                                {{-- FIXED: Type Text + Regex --}}
                                <input type="text" 
                                       name="nik_ayah" 
                                       pattern="\d{16}" 
                                       inputmode="numeric"
                                       maxlength="16"
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
    
                    <div class="bg-pink-50/60 p-5 rounded-2xl border border-pink-100">
                        <h4 class="text-sm font-bold text-pink-800 mb-3 flex items-center gap-2">
                            <span class="w-6 h-6 rounded bg-pink-200 flex items-center justify-center text-xs"><i class="fas fa-female"></i></span> DATA IBU
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
                                {{-- FIXED: Type Text + Regex --}}
                                <input type="text" 
                                       name="nik_ibu" 
                                       pattern="\d{16}" 
                                       inputmode="numeric"
                                       maxlength="16"
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
                <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-4 mt-4 p-4 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                    <div class="md:col-span-3">
                        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-widest">Informasi Kebutuhan Khusus (Jika Ada)</h4>
                    </div>
                
                    {{-- Definisi List Cukup Sekali Saja --}}
                    @php
                        $list_kebutuhan = [
                            1 => 'Netra', 2 => 'Rungu', 3 => 'Wicara', 4 => 'Daksa', 
                            5 => 'Grahita', 6 => 'Laras', 7 => 'ADHD', 8 => 'Autis', 
                            9 => 'Ganda', 10 => 'Kesulitan Belajar', 11 => 'Berbakat', 
                            12 => 'Indigo', 13 => 'Down Syndrome', 14 => 'Korban Narkoba'
                        ];
                    @endphp
                    
                    {{-- 1. Kebutuhan Khusus Mahasiswa --}}
                    <div>
                        <label class="label-text">Mahasiswa</label>
                        <select name="id_kebutuhan_khusus_mahasiswa" class="input-glass">
                            <option value="0" {{ old('id_kebutuhan_khusus_mahasiswa', $data->id_kebutuhan_khusus_mahasiswa ?? '0') == '0' ? 'selected' : '' }}>Tidak Ada</option>
                            @foreach($list_kebutuhan as $id => $nama)
                                <option value="{{ $id }}" {{ old('id_kebutuhan_khusus_mahasiswa', $data->id_kebutuhan_khusus_mahasiswa ?? '') == $id ? 'selected' : '' }}>{{ $nama }}</option>
                            @endforeach
                        </select>
                    </div>
                
                    {{-- 2. Kebutuhan Khusus Ayah --}}
                    <div>
                        <label class="label-text">Ayah</label>
                        <select name="id_kebutuhan_khusus_ayah" class="input-glass">
                            <option value="0" {{ old('id_kebutuhan_khusus_ayah', $data->id_kebutuhan_khusus_ayah ?? '0') == '0' ? 'selected' : '' }}>Tidak Ada</option>
                            @foreach($list_kebutuhan as $id => $nama)
                                <option value="{{ $id }}" {{ old('id_kebutuhan_khusus_ayah', $data->id_kebutuhan_khusus_ayah ?? '') == $id ? 'selected' : '' }}>{{ $nama }}</option>
                            @endforeach
                        </select>
                    </div>
                
                    {{-- 3. Kebutuhan Khusus Ibu --}}
                    <div>
                        <label class="label-text">Ibu</label>
                        <select name="id_kebutuhan_khusus_ibu" class="input-glass">
                            <option value="0" {{ old('id_kebutuhan_khusus_ibu', $data->id_kebutuhan_khusus_ibu ?? '0') == '0' ? 'selected' : '' }}>Tidak Ada</option>
                            @foreach($list_kebutuhan as $id => $nama)
                                <option value="{{ $id }}" {{ old('id_kebutuhan_khusus_ibu', $data->id_kebutuhan_khusus_ibu ?? '') == $id ? 'selected' : '' }}>{{ $nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex justify-between mt-8 pt-4 border-t border-gray-200">
                    <button type="button" @click="slide = 2; window.scrollTo(0,0)" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali</button>
                    <button type="button" @click="validateAndNext(3, 4, $data)" class="btn-next bg-purple-600 hover:bg-purple-700">Lanjut Finalisasi <i class="fas fa-arrow-right"></i></button>
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
            <div class="bg-white/70 backdrop-blur-2xl border border-white/60 rounded-[2rem] p-6 md:p-10 relative overflow-hidden shadow-2xl bg-gradient-to-br from-blue-200/50 via-white/40 to-red-200/50">
                <div class="absolute top-0 left-0 w-1.5 h-full bg-emerald-500"></div>
                <h3 class="section-title text-emerald-700"><i class="fas fa-clipboard-check"></i> Data Tambahan & Wali</h3>
    
                {{-- DATA KPS/NPWP --}}
                <div class="mb-6 p-4 bg-gray-50 rounded-xl border border-gray-200">
                    <h4 class="text-sm font-bold text-gray-700 mb-3 uppercase tracking-wide">Data Kesejahteraan Keluarga</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4" 
                             x-data="{ isKps: '{{ old('penerima_kps', $data->penerima_kps ?? '0') }}' }">
                            
                            {{-- 1. PILIHAN KPS --}}
                            <div>
                                <label class="label-text">Penerima Kartu Perlindungan Sosial (KPS)? <span class="text-red-500">*</span></label>
                                <select name="penerima_kps" 
                                        class="input-glass w-full @error('penerima_kps') input-error @enderror" 
                                        required 
                                        x-model="isKps"> 
                                    <option value="0">0: Bukan Penerima KPS</option>
                                    <option value="1">1: Penerima KPS</option>
                                </select>
                            </div>
                            
                            {{-- 2. INPUT NOMOR KPS --}}
                            <div>
                                <label class="label-text">Nomor KPS (Jika Ada)</label>
                                <input type="text" 
                                       name="no_kps" 
                                       value="{{ old('no_kps', $data->no_kps ?? '') }}" 
                                       class="input-glass w-full transition-all duration-300 @error('no_kps') input-error @enderror" 
                                       placeholder="Nomor KPS" 
                                       maxlength="80"
                                       pattern="^[a-zA-Z0-9.-]+$"
                                       :disabled="isKps != '1'"
                                       :class="isKps != '1' ? 'bg-slate-200 text-slate-400 cursor-not-allowed opacity-60' : ''"
                                       :required="isKps == '1'">
                            </div>
                            <div>
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
                </div>
    
                {{-- DATA WALI (OPSIONAL) --}}
                <div x-data="{ hasWali: false }" class="mb-8">
                    <div class="flex items-center gap-2 mb-3 bg-emerald-50 p-3 rounded-lg border border-emerald-100 cursor-pointer" @click="hasWali = !hasWali">
                        <input type="checkbox" id="checkWali" x-model="hasWali" class="w-5 h-5 text-emerald-600 rounded focus:ring-emerald-500">
                        <label class="text-sm font-bold text-emerald-800 cursor-pointer select-none">Saya tinggal bersama Wali (Isi jika ada)</label>
                    </div>
                    <div x-show="hasWali" x-transition class="p-5 bg-emerald-50/50 rounded-2xl border border-emerald-100">
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
                                <label class="label-text">NIK Wali <span class="text-[10px] text-slate-400 font-normal normal-case">(16 Digit)</span></label>
                                <input type="text" 
                                       name="nik_wali" 
                                       value="{{ old('nik_wali', $data->nik_wali ?? '') }}" 
                                       class="input-glass @error('nik_wali') input-error @enderror" 
                                       maxlength="16"
                                       pattern="[0-9]{16}"
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
    
                <div class="flex justify-between items-center mt-8 pt-4 border-t border-gray-200">
                    <button type="button" @click="slide = 3; window.scrollTo(0,0)" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali</button>
                    <button type="button" onclick="confirmSubmit()" class="bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg shadow-emerald-500/30 transition transform hover:scale-105 flex items-center gap-2 animate-pulse hover:animate-none">
                        <i class="fas fa-check-double"></i> SIMPAN PERMANEN
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@include('mahasiswa.data-final.partials.scripts')


<script>
    document.addEventListener("DOMContentLoaded", function() {
        
        // Cek apakah ada error validasi dari Controller
        @if(session('popup_error'))
            Swal.fire({
                title: '<span class="text-2xl font-black text-white tracking-tight uppercase"><i class="fas fa-times-circle mr-2"></i> Data Belum Valid!</span>',
                html: `
                    <div class="mt-2 mb-4 text-left text-red-50">
                        <div class="bg-red-900/40 border border-red-400/30 rounded-2xl p-4 shadow-inner">
                            <div class="text-sm text-red-100 leading-relaxed font-medium">
                                {!! session('popup_error') !!}
                            </div>
                        </div>
                    </div>
                `,
                icon: null,
                allowOutsideClick: false,
                confirmButtonText: 'Baik, Saya Akan Perbaiki',
                background: '#dc2626', // bg-red-600
                color: '#ffffff',
                width: '550px',
                buttonsStyling: false,
                customClass: { 
                    popup: 'rounded-3xl font-sans shadow-[0_20px_50px_rgba(220,38,38,0.5)] border border-red-500', 
                    confirmButton: 'bg-white text-red-700 hover:bg-red-50 border border-white rounded-xl px-6 py-3 font-bold text-sm w-full md:w-auto shadow-lg transition-all active:scale-95 focus:ring-4 focus:ring-white/50 focus:outline-none' 
                }
            });
            
        // Jika tidak ada error, tampilkan popup peringatan default (kecuali saat direfresh)
        @else
            let isReload = false;
            // Modern way
            const navEntries = performance.getEntriesByType("navigation");
            if (navEntries.length > 0 && navEntries[0].type === "reload") {
                isReload = true;
            } 
            // Fallback (older browsers)
            else if (window.performance && window.performance.navigation && window.performance.navigation.type === 1) {
                isReload = true;
            }

            if (!isReload) {
                Swal.fire({
                    title: '<span class="text-2xl font-black text-white tracking-tight uppercase"><i class="fas fa-exclamation-triangle mr-2"></i> Peringatan Final</span>',
                    html: `
                        <div class="mt-2 mb-4 text-left text-red-50">
                            <p class="text-sm font-medium mb-4">Anda akan mengisi Formulir Data Final untuk pelaporan <b class="text-white font-bold bg-red-900/50 px-2 py-0.5 rounded">PDDIKTI</b>.</p>
                            
                            <div class="bg-red-900/40 border border-red-400/30 rounded-2xl p-4 mb-4 shadow-inner">
                                <div class="flex items-start gap-3">
                                    <div class="bg-red-500 rounded-full w-10 h-10 flex items-center justify-center mt-0.5 animate-pulse flex-shrink-0 text-white shadow-lg">
                                        <i class="fas fa-lock text-lg"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-bold text-white mb-1 uppercase tracking-wider">Ketentuan Sangat Penting:</h4>
                                        <ul class="text-xs text-red-100 space-y-1.5 list-disc ml-4 leading-relaxed">
                                            <li>Data ini bersifat <b>SANGAT PENTING</b> dan <b>PERMANEN</b>.</li>
                                            <li><b>TIDAK DAPAT DIUBAH</b> setelah disimpan.</li>
                                            <li>Isi dengan teliti, periksa ejaan nama dan angka.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center bg-black/20 border border-white/20 rounded-xl py-3 px-4 shadow-sm">
                                <p class="text-[11px] font-bold text-white uppercase tracking-widest leading-relaxed">
                                    "Saya bertanggung jawab penuh atas kebenaran data yang saya isikan."
                                </p>
                            </div>
                        </div>
                    `,
                    icon: null,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    confirmButtonText: 'Saya Mengerti & Akan Mengisi Dengan Benar',
                    background: '#dc2626', // bg-red-600
                    color: '#ffffff',
                    width: '550px',
                    buttonsStyling: false,
                    customClass: { 
                        popup: 'rounded-3xl font-sans shadow-[0_20px_50px_rgba(220,38,38,0.5)] border border-red-500', 
                        confirmButton: 'bg-white text-red-700 hover:bg-red-50 border border-white rounded-xl px-6 py-3 font-bold text-sm w-full md:w-auto shadow-lg transition-all active:scale-95 focus:ring-4 focus:ring-white/50 focus:outline-none' 
                    }
                });
            }
        @endif

    });


    // 2. CONFIRM SUBMIT
    function confirmSubmit() {
        Swal.fire({
            title: '<span class="text-xl md:text-2xl font-black text-white uppercase tracking-tight"><i class="fas fa-lock mr-2"></i> Kunci Data Permanen?</span>',
            html: `<div class="mt-2 mb-4 text-red-50 text-left">
                       <p class="text-sm font-medium mb-3">Data akan dikunci dan dikirim ke sistem pelaporan PDDIKTI.</p>
                       <div class="bg-red-900/50 border border-red-400/50 rounded-xl p-3 flex gap-3 shadow-inner">
                           <div class="shrink-0 text-white mt-0.5"><i class="fas fa-exclamation-circle text-2xl animate-pulse"></i></div>
                           <div><p class="text-xs font-bold text-white uppercase tracking-wider">Terkunci Permanen:</p><p class="text-xs text-red-100 leading-relaxed">Anda sama sekali tidak bisa mengubahnya lagi setelah ini!</p></div>
                       </div>
                   </div>`,
            icon: null,
            showCancelButton: true,
            background: '#dc2626', // bg-red-600
            color: '#ffffff',
            buttonsStyling: false,
            confirmButtonText: 'Ya, Kunci Permanen!',
            cancelButtonText: 'Periksa Lagi',
            reverseButtons: true,
            customClass: { 
                popup: 'rounded-3xl font-sans shadow-[0_20px_50px_rgba(220,38,38,0.5)] border border-red-500', 
                confirmButton: 'bg-white text-red-700 hover:bg-red-50 rounded-xl px-6 py-3 font-bold shadow-lg transition-all active:scale-95 w-full md:w-auto mb-3 md:mb-0 md:ml-3 focus:ring-4 focus:ring-white/50 focus:outline-none', 
                cancelButton: 'bg-red-900 text-red-100 hover:bg-red-950 border border-red-800 rounded-xl px-6 py-3 font-bold transition-all active:scale-95 w-full md:w-auto focus:ring-4 focus:ring-red-900/50 focus:outline-none' 
            }
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
        // Jika input isinya salah satu dari ini, kita anggap ERROR
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
            
            // CEK A: Validasi HTML5 Standar (Required, Pattern Regex, Type)
            // Ini otomatis ngecek pattern="..." yang baru kita perbaiki tadi
            const isHtmlInvalid = !input.checkValidity();

            // CEK B: Validasi Data Dummy (Deteksi Data Sampah)
            // Jika input required DAN isinya ada di daftar dummy -> ERROR
            const isDummyData = input.hasAttribute('required') && dummyValues.includes(val);

            // CEK C: Validasi Select (Pastiin gak pilih "Pilih...")
            const isInvalidSelect = input.tagName === 'SELECT' && input.hasAttribute('required') && val === "";

            // --- KEPUTUSAN FINAL ---
            // Jika salah satu dari A, B, atau C terjadi, maka ERROR
            if (isHtmlInvalid || isDummyData || isInvalidSelect) {
                isValid = false;
                
                // Tambah efek merah & getar
                input.classList.add('input-error'); 
                
                // Tangkap elemen pertama buat di-scroll
                if (!firstError) firstError = input;
            }
        });

        // 4. Aksi Lanjut / Stop
        if (isValid) {
            alpineData.slide = nextSlide;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } else {
            Swal.fire({
                title: '<span class="text-xl md:text-2xl font-black text-white tracking-tight uppercase"><i class="fas fa-exclamation-circle mr-2"></i> Data Belum Lengkap</span>',
                html: `
                    <div class="mt-2 mb-4 text-left text-red-50">
                        <div class="bg-red-900/40 border border-red-400/30 rounded-2xl p-4 shadow-inner">
                            <p class="text-sm font-medium leading-relaxed">
                                Mohon isi data dengan benar.<br>
                                Pastikan tidak ada data yang masih <b class="text-white bg-red-500/20 px-1 py-0.5 rounded">strip (-)</b>, <b class="text-white bg-red-500/20 px-1 py-0.5 rounded">0000</b>, atau <b class="text-white bg-red-500/20 px-1 py-0.5 rounded">kosong</b>.
                            </p>
                        </div>
                    </div>
                `,
                icon: null,
                confirmButtonText: 'Perbaiki Sekarang',
                background: '#dc2626', // bg-red-600
                color: '#ffffff',
                buttonsStyling: false,
                customClass: { 
                    popup: 'rounded-3xl font-sans shadow-[0_20px_50px_rgba(220,38,38,0.5)] border border-red-500', 
                    confirmButton: 'bg-white text-red-700 hover:bg-red-50 border border-white rounded-xl px-6 py-3 font-bold text-sm w-full md:w-auto shadow-lg transition-all active:scale-95 focus:ring-4 focus:ring-white/50 focus:outline-none' 
                }
            });

            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
        }
    }



    

</script>

@include('mahasiswa.data-final.partials.styles')

@endif
@endsection
@include('mahasiswa.components.modal-tutorial-nisn')