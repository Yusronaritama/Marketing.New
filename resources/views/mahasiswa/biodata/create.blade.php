@extends('layouts.mahasiswa.app')

@section('title', 'Formulir Biodata')

@section('content')

{{-- Toast Notification (Menggunakan Custom Event) --}}
<div x-data="{ show: false, message: '' }" 
     x-on:notify.window="show = true; message = $event.detail; setTimeout(() => show = false, 4000)"
     class="fixed top-24 right-4 z-50 flex flex-col gap-2 pointer-events-none">
    <div x-show="show" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-x-10"
         x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-x-0"
         x-transition:leave-end="opacity-0 translate-x-10"
         class="pointer-events-auto bg-blue-600/90 backdrop-blur-md border border-white/20 text-white px-6 py-4 rounded-xl shadow-2xl flex items-center gap-4 min-w-[300px]">
        <div class="bg-white/20 p-2 rounded-full">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
        </div>
        <div>
            <h4 class="font-bold text-sm">Mode Edit Aktif</h4>
            <p class="text-xs text-blue-100" x-text="message"></p>
        </div>
        <button @click="show = false" class="ml-auto text-white/50 hover:text-white">&times;</button>
    </div>
</div>

<div class="px-4 sm:px-6 lg:px-8 py-6 pb-20">
    
    {{-- LOGIC UTAMA: isEditing --}}
    <div class="max-w-5xl mx-auto relative z-10" 
         x-data="{ 
            animate: false,
            isSubmitting: false,
            // Jika data sudah ada ($biodata), maka isEditing FALSE (Terkunci). Jika baru, TRUE.
            isEditing: {{ (isset($biodata) && $biodata) ? 'false' : 'true' }},
            sameAsKtp: {{ (isset($biodata) && $biodata->alamat == $biodata->tempat_tinggal) ? 'true' : 'false' }},
            
            // Helper Function untuk Toggle Edit
            activateEdit() {
                this.isEditing = true;
                window.dispatchEvent(new CustomEvent('notify', { 
                    detail: 'Silakan ubah data Anda, lalu klik Simpan.' 
                }));
            }
        }" 
         x-init="setTimeout(() => animate = true, 200)">
        
        {{-- Header --}}
        <div class="text-center mb-10">
            <h1 class="text-3xl font-extrabold tracking-tight text-white drop-shadow-lg">
                @if(isset($biodata) && $biodata)
                    @if($biodata->status_bio === 'completed')
                        Data Biodata Anda 📄
                    @else
                        Draft Biodata Anda 📝
                    @endif
                @else
                    Formulir Biodata Pendaftaran 📝
                @endif
            </h1>
            <p class="mt-4 text-lg text-blue-100 font-light">
                <span x-show="!isEditing">Data terkunci. Klik tombol <strong>Edit</strong> di bawah untuk mengubah.</span>
                <span x-show="isEditing" class="text-yellow-300 font-medium animate-pulse">Mode Edit Aktif: Silakan perbarui data Anda.</span>
            </p>
            
            {{-- Status Badge --}}
            @if(isset($biodata) && $biodata)
                <div class="mt-2 inline-block px-3 py-1 rounded-full text-sm font-medium 
                     @if($biodata->status_bio === 'completed') bg-green-500/20 text-green-300 border border-green-400/30
                     @else
                         bg-yellow-500/20 text-yellow-300 border border-yellow-400/30
                     @endif">
                    @if($biodata->status_bio === 'completed') ✅ Status: Selesai
                    @else
                        📝 Status: Draft
                    @endif
                </div>
            @endif
        </div>

        <form action="{{ route('mahasiswa.biodata.store') }}" method="POST" class="space-y-8"
              @submit="if(isSubmitting) { $event.preventDefault(); return false; } isSubmitting = true;"
              x-show="animate"
              x-transition:enter="transition ease-out duration-700"
              x-transition:enter-start="opacity-0 translate-y-10"
              x-transition:enter-end="opacity-100 translate-y-0">
            @csrf

            {{-- SECTION 1: Data Pribadi --}}
            <div class="relative overflow-hidden transition-all duration-500"
                 :class="isEditing ? 'bg-white shadow-2xl border border-gray-200' : 'form-card-glass'">
                <div class="absolute top-0 left-0 w-1 h-full" 
                     :class="isEditing ? 'bg-blue-600' : 'bg-blue-400'"></div>
                
                <h2 class="text-xl font-bold mb-6 flex items-center justify-between px-6 pt-6"
                    :class="isEditing ? 'text-gray-800' : 'text-white'">
                    <span class="flex items-center">
                        <span :class="isEditing ? 'bg-blue-100 text-blue-600' : 'bg-blue-500/20 text-white border border-blue-400/30'" 
                              class="p-2 rounded-lg mr-3">👤</span> 
                        Data Pribadi
                    </span>
                    <span x-show="!isEditing" class="text-xs bg-red-500/20 text-red-200 px-2 py-1 rounded border border-red-400/30">🔒 Terkunci</span>
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
                    
                    {{-- NIK --}}
                    <div class="md:col-span-2">
                        <label for="nik" class="form-label" :class="isEditing ? 'text-gray-700' : 'text-blue-200'">NIK</label>
                        <div class="relative">
                            <div class="form-icon-wrapper">
                                <i :class="isEditing ? 'text-blue-500' : 'text-blue-300'" class="fas fa-id-card"></i>
                            </div>
                            <input type="text" name="nik" id="nik" maxlength="16" 
                                   class="form-input pl-10 transition-all duration-300"
                                   :class="isEditing ? 'form-input-edit' : 'form-input-view'"
                                   :readonly="!isEditing"
                                   value="{{ old('nik', $biodata->nik ?? '') }}"
                                   placeholder="Masukkan 16 digit NIK">
                            @error('nik')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Nama Lengkap --}}
                    <div class="md:col-span-2">
                        <label for="nama_lengkap" class="form-label" :class="isEditing ? 'text-gray-700' : 'text-blue-200'">Nama Lengkap</label>
                        <div class="relative">
                            <div class="form-icon-wrapper">
                                <i :class="isEditing ? 'text-blue-500' : 'text-blue-300'" class="fas fa-user"></i>
                            </div>
                            <input type="text" name="nama_lengkap" id="nama_lengkap" 
                                   class="form-input pl-10 transition-all duration-300"
                                   :class="isEditing ? 'form-input-edit' : 'form-input-view'"
                                   :readonly="!isEditing"
                                   value="{{ old('nama_lengkap', $biodata->nama_lengkap ?? '') }}"
                                   placeholder="Masukkan nama lengkap sesuai KTP">
                            @error('nama_lengkap')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Jenis Kelamin (SELECT) --}}
                    <div class="md:col-span-1">
                        <label for="jenis_kelamin" class="form-label" :class="isEditing ? 'text-gray-700' : 'text-blue-200'">Jenis Kelamin</label>
                        <div class="relative">
                            <div class="form-icon-wrapper">
                                <i :class="isEditing ? 'text-blue-500' : 'text-blue-300'" class="fas fa-venus-mars"></i>
                            </div>
                            <select name="jenis_kelamin" id="jenis_kelamin" 
                                    class="form-input pl-10 transition-all duration-300"
                                    :class="isEditing ? 'form-input-edit' : 'form-input-view pointer-events-none'"
                                    :disabled="!isEditing">
                                <option value="" class="text-gray-500">Pilih Jenis Kelamin...</option>
                                <option value="Laki-laki" class="text-gray-800" {{ old('jenis_kelamin', $biodata->jenis_kelamin ?? '') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="Perempuan" class="text-gray-800" {{ old('jenis_kelamin', $biodata->jenis_kelamin ?? '') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                            @error('jenis_kelamin')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Agama (SELECT) --}}
                    <div class="md:col-span-1">
                        <label for="agama" class="form-label" :class="isEditing ? 'text-gray-700' : 'text-blue-200'">Agama</label>
                        <div class="relative">
                            <div class="form-icon-wrapper">
                                <i :class="isEditing ? 'text-blue-500' : 'text-blue-300'" class="fas fa-praying-hands"></i>
                            </div>
                            <select name="agama" id="agama" 
                                    class="form-input pl-10 transition-all duration-300"
                                    :class="isEditing ? 'form-input-edit' : 'form-input-view pointer-events-none'"
                                    :disabled="!isEditing">
                                <option value="" class="text-gray-500">Pilih Agama...</option>
                                @foreach(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'] as $agm)
                                    <option value="{{ $agm }}" class="text-gray-800" {{ old('agama', $biodata->agama ?? '') == $agm ? 'selected' : '' }}>{{ $agm }}</option>
                                @endforeach
                            </select>
                            @error('agama')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Tempat & Tanggal Lahir --}}
                    <div>
                        <label for="tempat_lahir" class="form-label" :class="isEditing ? 'text-gray-700' : 'text-blue-200'">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" id="tempat_lahir" 
                               class="form-input transition-all duration-300"
                               :class="isEditing ? 'form-input-edit' : 'form-input-view'"
                               :readonly="!isEditing"
                               value="{{ old('tempat_lahir', $biodata->tempat_lahir ?? '') }}"
                               placeholder="Kota tempat lahir">
                        @error('tempat_lahir')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="tanggal_lahir" class="form-label" :class="isEditing ? 'text-gray-700' : 'text-blue-200'">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" id="tanggal_lahir" 
                               class="form-input transition-all duration-300"
                               :class="isEditing ? 'form-input-edit' : 'form-input-view'"
                               :readonly="!isEditing"
                               value="{{ old('tanggal_lahir', (isset($biodata) && $biodata->tanggal_lahir) ? \Carbon\Carbon::parse($biodata->tanggal_lahir)->format('Y-m-d') : '') }}"
                               max="{{ date('Y-m-d') }}">
                        @error('tanggal_lahir')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Nama Orang Tua --}}
                    <div>
                        <label for="nama_ayah" class="form-label" :class="isEditing ? 'text-gray-700' : 'text-blue-200'">Nama Ayah</label>
                        <input type="text" name="nama_ayah" id="nama_ayah" 
                               class="form-input transition-all duration-300"
                               :class="isEditing ? 'form-input-edit' : 'form-input-view'"
                               :readonly="!isEditing"
                               value="{{ old('nama_ayah', $biodata->nama_ayah ?? '') }}"
                               placeholder="Nama lengkap ayah kandung">
                        @error('nama_ayah')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="nama_ibu" class="form-label" :class="isEditing ? 'text-gray-700' : 'text-blue-200'">Nama Ibu</label>
                        <input type="text" name="nama_ibu" id="nama_ibu" 
                               class="form-input transition-all duration-300"
                               :class="isEditing ? 'form-input-edit' : 'form-input-view'"
                               :readonly="!isEditing"
                               value="{{ old('nama_ibu', $biodata->nama_ibu ?? '') }}"
                               placeholder="Nama lengkap ibu kandung">
                        @error('nama_ibu')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Alamat KTP --}}
                    <div class="md:col-span-2">
                        <label for="alamat" class="form-label" :class="isEditing ? 'text-gray-700' : 'text-blue-200'">Alamat KTP</label>
                        <textarea name="alamat" id="alamat" rows="3" 
                                  class="form-input transition-all duration-300"
                                  :class="isEditing ? 'form-input-edit' : 'form-input-view'"
                                  :readonly="!isEditing"
                                  placeholder="Alamat lengkap sesuai KTP">{{ old('alamat', $biodata->alamat ?? '') }}</textarea>
                        @error('alamat')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tempat Tinggal --}}
                    <div class="md:col-span-2">
                        <div class="flex justify-between mb-2">
                            <label for="tempat_tinggal" class="form-label" :class="isEditing ? 'text-gray-700' : 'text-blue-200'">Tempat Tinggal Saat Ini</label>
                            <div class="flex items-center" x-show="isEditing" x-transition>
                                <input type="checkbox" id="same_address" x-model="sameAsKtp"
                                       @change="if(sameAsKtp) { document.getElementById('tempat_tinggal').value = document.getElementById('alamat').value } else { document.getElementById('tempat_tinggal').value = '' }"
                                       class="rounded bg-white border-gray-300 text-blue-500 focus:ring-blue-500">
                                <label for="same_address" class="ml-2 text-sm" :class="isEditing ? 'text-gray-700' : 'text-blue-200'">Sama dengan KTP</label>
                            </div>
                        </div>
                        <textarea name="tempat_tinggal" id="tempat_tinggal" rows="3" 
                                  class="form-input transition-all duration-300"
                                  :class="(!isEditing || sameAsKtp) ? 'form-input-view cursor-not-allowed' : 'form-input-edit'"
                                  :readonly="!isEditing || sameAsKtp"
                                  placeholder="Alamat tempat tinggal saat ini (jika berbeda dengan KTP)">{{ old('tempat_tinggal', $biodata->tempat_tinggal ?? '') }}</textarea>
                        @error('tempat_tinggal')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                </div>
            </div>

            {{-- SECTION 2: Data Pendidikan --}}
            <div class="relative overflow-hidden transition-all duration-500"
                 :class="isEditing ? 'bg-white shadow-2xl border border-gray-200' : 'form-card-glass'">
                <div class="absolute top-0 left-0 w-1 h-full" 
                     :class="isEditing ? 'bg-green-600' : 'bg-green-400'"></div>

                <h2 class="text-xl font-bold mb-6 flex items-center justify-between px-6 pt-6"
                    :class="isEditing ? 'text-gray-800' : 'text-white'">
                    <span class="flex items-center">
                        <span :class="isEditing ? 'bg-green-100 text-green-600' : 'bg-green-500/20 text-white border border-green-400/30'" 
                              class="p-2 rounded-lg mr-3">🎓</span> 
                        Data Pendidikan
                    </span>
                    <span x-show="!isEditing" class="text-xs bg-red-500/20 text-red-200 px-2 py-1 rounded border border-red-400/30">🔒 Terkunci</span>
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
                    {{-- Jenjang --}}
                    <div class="md:col-span-2">
                        <label for="jenjang_pendidikan" class="form-label" :class="isEditing ? 'text-gray-700' : 'text-blue-200'">Jenjang Pendidikan Terakhir</label>
                        <select name="jenjang_pendidikan" id="jenjang_pendidikan" onchange="togglePendidikanField()"
                                class="form-input transition-all duration-300"
                                :class="isEditing ? 'form-input-edit' : 'form-input-view pointer-events-none'"
                                :disabled="!isEditing">
                            <option value="" class="text-gray-500">Pilih Jenjang Pendidikan...</option>
                            <option value="smk" class="text-gray-800" {{ old('jenjang_pendidikan', $biodata->jenjang_pendidikan ?? '') == 's1' ? 'selected' : '' }}>SMK</option>
                            <option value="pkbm" class="text-gray-800" {{ old('jenjang_pendidikan', $biodata->jenjang_pendidikan ?? '') == 's1' ? 'selected' : '' }}>PKBM</option>
                            <option value="sma" class="text-gray-800" {{ old('jenjang_pendidikan', $biodata->jenjang_pendidikan ?? '') == 'sma' ? 'selected' : '' }}>SMA/Sederajat</option>
                            <option value="d3" class="text-gray-800" {{ old('jenjang_pendidikan', $biodata->jenjang_pendidikan ?? '') == 'd3' ? 'selected' : '' }}>Diploma 3 (D3)</option>
                            <option value="s1" class="text-gray-800" {{ old('jenjang_pendidikan', $biodata->jenjang_pendidikan ?? '') == 's1' ? 'selected' : '' }}>Sarjana (S1)</option>
                                                    </select>
                        @error('jenjang_pendidikan')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    
                    {{-- Asal Sekolah --}}
                    <div class="md:col-span-2">
                        <label for="asal_sekolah" class="form-label" :class="isEditing ? 'text-gray-700' : 'text-blue-200'"><span id="label_asal_sekolah">Asal Sekolah</span></label>
                        <input type="text" name="asal_sekolah" id="asal_sekolah" 
                               class="form-input transition-all duration-300"
                               :class="isEditing ? 'form-input-edit' : 'form-input-view'"
                               :readonly="!isEditing"
                               value="{{ old('asal_sekolah', $biodata->asal_sekolah ?? '') }}"
                               placeholder="Nama sekolah/universitas asal">
                        @error('asal_sekolah')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Jurusan Asal Sekolah --}}
					@php
					    // Daftar jurusan template untuk mengecek apakah data user saat ini custom atau tidak
					    $templateJurusans = [
					        'SMA - IPA', 'SMA - IPS', 'SMA - Bahasa', 'SMA - Agama', 'SMA - Kurikulum Merdeka',
					        'SMK - Farmasi', 'SMK - Analis Kesehatan/Kimia', 'SMK - Keperawatan/Dental',
					        'SMK - Teknik Komputer & Informatika', 'SMK - Akuntansi & Bisnis', 'SMK - Teknik Mesin/Elektro/Sipil',
					        'SMK - Pariwisata/Tata Boga', 'SMK - Pertanian/Kehutanan'
					    ];
					    
					    // Ambil data lama (dari database atau dari error validasi)
					    $currentJurusan = old('jurusan_sekolah', $biodata->jurusan_sekolah ?? '');
					    
					    // Cek apakah data lama adalah inputan manual (tidak ada di template)
					    $isCustom = $currentJurusan && !in_array($currentJurusan, $templateJurusans);
					    
					    // Setup nilai awal untuk Alpine.js
					    $initSelect = $isCustom ? 'Lainnya' : $currentJurusan;
					    $initManual = $isCustom ? $currentJurusan : '';
					@endphp
					
					<div class="md:col-span-2" x-data="{ 
					    pilihan: '{{ $initSelect }}', 
					    manual: '{{ $initManual }}' 
					}">
					    <label class="form-label" :class="isEditing ? 'text-gray-700' : 'text-blue-200'">
					        <span id="label_jurusan_sekolah">Jurusan Sekolah</span>
					    </label>
					    
					    {{-- HIDDEN INPUT: Ini yang akan dikirim ke Controller Laravel --}}
					    <input type="hidden" name="jurusan_sekolah" :value="pilihan === 'Lainnya' ? manual : pilihan">
					    
					    {{-- DROPDOWN TEMPLATE (Tidak pakai atribut 'name' agar tidak dikirim ke form) --}}
					    <select x-model="pilihan" 
					            class="form-input transition-all duration-300"
					            :class="isEditing ? 'form-input-edit' : 'form-input-view'"
					            :disabled="!isEditing">
					        
					        <option value="">-- Pilih Jurusan Asal Sekolah --</option>
					        
					        <optgroup label="SMA / MA">
					            <option value="SMA - IPA">SMA/MA - IPA / Sains</option>
					            <option value="SMA - IPS">SMA/MA - IPS / Sosial</option>
					            <option value="SMA - Bahasa">SMA/MA - Bahasa</option>
					            <option value="SMA - Agama">SMA/MA - Agama</option>
					            <option value="SMA - Kurikulum Merdeka">SMA/MA - Kurikulum Merdeka (Umum)</option>
					        </optgroup>
					
					        <optgroup label="SMK (Rumpun Kesehatan & Sains)">
					            <option value="SMK - Farmasi">SMK - Farmasi (Klinis/Industri)</option>
					            <option value="SMK - Analis Kesehatan/Kimia">SMK - Analis Kesehatan / Kimia Analisis</option>
					            <option value="SMK - Keperawatan/Dental">SMK - Keperawatan / Asisten Dental</option>
					        </optgroup>
					
					        <optgroup label="SMK (Rumpun Umum)">
					            <option value="SMK - Teknik Komputer & Informatika">SMK - Teknik Komputer & Informatika (RPL/TKJ)</option>
					            <option value="SMK - Akuntansi & Bisnis">SMK - Akuntansi & Bisnis Manajemen</option>
					            <option value="SMK - Teknik Mesin/Elektro/Sipil">SMK - Teknik Mesin / Elektro / Sipil</option>
					            <option value="SMK - Pariwisata/Tata Boga">SMK - Pariwisata / Tata Boga / Tata Busana</option>
					            <option value="SMK - Pertanian/Kehutanan">SMK - Agribisnis Pertanian / Kehutanan</option>
					        </optgroup>
					
					        <option value="Lainnya" class="font-bold text-blue-600">Lainnya / Ketik Manual...</option>
					    </select>
					
					    {{-- INPUT MANUAL (Hanya muncul jika dropdown diatur ke 'Lainnya') --}}
					    <div x-show="pilihan === 'Lainnya'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="mt-3">
					        <input type="text" x-model="manual" 
					               class="form-input transition-all duration-300"
					               :class="isEditing ? 'form-input-edit' : 'form-input-view'"
					               :readonly="!isEditing"
					               placeholder="Ketik nama jurusan secara spesifik di sini...">
					        <p class="text-[10px] text-slate-400 mt-1 ml-1"><i class="fas fa-info-circle"></i> Pastikan ejaan sudah benar.</p>
					    </div>
					
					    @error('jurusan_sekolah')
					        <p class="form-error mt-1">{{ $message }}</p>
					    @enderror
					</div>
                    {{-- TAMBAHAN: Tahun Lulus --}}
                    <div class="md:col-span-1">
                        <label for="tahun_lulus" class="form-label" :class="isEditing ? 'text-gray-700' : 'text-blue-200'">Tahun Lulus</label>
                        <div class="relative">
                            <div class="form-icon-wrapper">
                                <i :class="isEditing ? 'text-blue-500' : 'text-blue-300'" class="fas fa-calendar-alt"></i>
                            </div>
                            <input type="number" name="tahun_lulus" id="tahun_lulus" min="1900" placeholder="Contoh: 2024"
                                   class="form-input pl-10 transition-all duration-300"
                                   :class="isEditing ? 'form-input-edit' : 'form-input-view'"
                                   :readonly="!isEditing"
                                   value="{{ old('tahun_lulus', $biodata->tahun_lulus ?? '') }}">
                            @error('tahun_lulus')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- TAMBAHAN: Asal Daerah --}}
                    <div class="md:col-span-1">
                        <label for="daerah_asal" class="form-label" :class="isEditing ? 'text-gray-700' : 'text-blue-200'">Asal Daerah</label>
                        <div class="relative">
                            <div class="form-icon-wrapper">
                                <i :class="isEditing ? 'text-blue-500' : 'text-blue-300'" class="fas fa-map-marker-alt"></i>
                            </div>
                            
                            <select name="daerah_asal" id="daerah_asal" 
                                    class="form-input pl-10 transition-all duration-300"
                                    :class="isEditing ? 'form-input-edit' : 'form-input-view pointer-events-none'"
                                    :disabled="!isEditing">
                                
                                <option value="" class="text-gray-500">Pilih Daerah...</option>
                                
                                <option value="dalam_karawang" class="text-gray-800" {{ old('daerah_asal', $biodata->daerah_asal ?? '') == 'dalam_karawang' ? 'selected' : '' }}>
                                    Dalam Karawang
                                </option>
                                
                                <option value="luar_karawang" class="text-gray-800" {{ old('daerah_asal', $biodata->daerah_asal ?? '') == 'luar_karawang' ? 'selected' : '' }}>
                                    Luar Karawang
                                </option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6 mt-2 pt-4 border-t border-gray-200/50"
                         x-data="wilayahHandler()" 
                         x-init="initWilayah()">
                        
                        {{-- 1. PILIH PROVINSI --}}
                        <div>
                            <label class="form-label" :class="isEditing ? 'text-gray-700' : 'text-blue-200'">
                                Provinsi Asal Sekolah
                            </label>
                            <div class="relative">
                                <div class="form-icon-wrapper">
                                    <i :class="isEditing ? 'text-blue-500' : 'text-blue-300'" class="fas fa-map"></i>
                                </div>
                                
                                {{-- Hidden Input agar data terkirim ke Controller --}}
                                <input type="hidden" name="provinsi_asal" :value="selectedProvinsiName">

                                <select x-model="selectedProvinsiName" 
                                        @change="loadKota()"
                                        class="form-input pl-10 transition-all duration-300"
                                        :class="isEditing ? 'form-input-edit' : 'form-input-view pointer-events-none'"
                                        :disabled="!isEditing">
                                    
                                    <option value="">Pilih Provinsi...</option>
                                    <template x-for="prov in listProvinsi" :key="prov.id">
                                        <option :value="prov.name" x-text="prov.name"></option>
                                    </template>
                                </select>

                                {{-- Loading Spinner --}}
                                <div x-show="isLoadingProv" class="absolute right-3 top-3 text-blue-500" style="display: none;">
                                    <i class="fas fa-circle-notch fa-spin"></i>
                                </div>
                            </div>
                        </div>

                        {{-- 2. PILIH KOTA/KABUPATEN --}}
                        <div>
                            <label class="form-label" :class="isEditing ? 'text-gray-700' : 'text-blue-200'">
                                Kota/Kabupaten Asal
                            </label>
                            <div class="relative">
                                <div class="form-icon-wrapper">
                                    <i :class="isEditing ? 'text-blue-500' : 'text-blue-300'" class="fas fa-map-marker-alt"></i>
                                </div>

                                {{-- Hidden Input agar data terkirim ke Controller --}}
                                <input type="hidden" name="kota_kabupaten_asal" :value="selectedKotaName">

                                <select x-model="selectedKotaName"
                                        class="form-input pl-10 transition-all duration-300"
                                        :class="isEditing ? 'form-input-edit' : 'form-input-view pointer-events-none'"
                                        :disabled="!isEditing || !selectedProvinsiName">
                                    
                                    <option value="">Pilih Kota/Kabupaten...</option>
                                    <template x-for="kota in listKota" :key="kota.id">
                                        <option :value="kota.name" x-text="kota.name"></option>
                                    </template>
                                </select>

                                {{-- Loading Spinner --}}
                                <div x-show="isLoadingKota" class="absolute right-3 top-3 text-blue-500" style="display: none;">
                                    <i class="fas fa-circle-notch fa-spin"></i>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>

            {{-- Action Buttons (Sticky Bottom Glass) --}}
            <div class="sticky bottom-4 z-40">
                <div class="p-4 rounded-2xl shadow-2xl border flex justify-between items-center transition-all duration-500"
                     :class="isEditing ? 'bg-white border-gray-200' : 'bg-blue-900/90 backdrop-blur-md border-white/20'">
                    
                    @if(isset($biodata) && $biodata)
                        {{-- Tombol Edit (Hanya muncul jika isEditing FALSE) --}}
                        <button type="button" 
                                x-show="!isEditing"
                                @click="activateEdit()" 
                                class="w-full md:w-auto text-yellow-300 hover:text-yellow-100 font-bold py-3 px-6 rounded-xl border border-yellow-300/50 hover:bg-yellow-300/10 transition flex items-center justify-center gap-2">
                             <i class="fas fa-edit"></i> Edit Biodata
                        </button>

                        {{-- Tombol Simpan (Hanya muncul jika isEditing TRUE) --}}
                        <div x-show="isEditing" class="flex w-full gap-4 justify-end" style="display: none;">
                            <button type="button" @click="isEditing = false" 
                                    class="text-gray-500 hover:text-gray-700 font-medium py-2 px-4 transition flex items-center gap-2">
                                <i class="fas fa-times"></i> Batal
                            </button>
                            <button type="submit" 
                                    class="bg-green-500 hover:bg-green-400 text-white font-bold py-3 px-8 rounded-xl shadow-lg shadow-green-500/30 transition flex items-center gap-2">
                                <i class="fas fa-check-circle"></i> Simpan & Selesaikan
                            </button>
                        </div>
                    @else
                        {{-- Jika Data Baru (Selalu aktif) --}}
                        <button type="submit" formaction="{{ route('mahasiswa.biodata.draft') }}" 
                                class="text-gray-300 hover:text-white font-medium py-2 px-4 transition flex items-center gap-2">
                            <i class="fas fa-save"></i> Simpan Draft
                        </button>
                        <button type="submit" 
                                class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold py-3 px-8 rounded-xl shadow-lg shadow-blue-500/30 transition transform hover:scale-105 flex items-center gap-2">
                            <i class="fas fa-paper-plane"></i> Kirim & Selesaikan Biodata
                        </button>
                    @endif
                </div>
            </div>

        </form>
    </div>
</div>

{{-- CSS GLASSMORPHISM --}}
@push('styles')
<style>
    /* Kartu Form Glass (View Mode) */
    .form-card-glass {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 1rem;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }

    /* Base Form Styles */
    .form-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        transition: color 0.3s ease;
    }

    .form-input {
        width: 100%;
        border-radius: 0.75rem;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
        font-size: 0.875rem;
    }

    /* Input View Mode (Readonly) */
    .form-input-view {
        background-color: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: rgba(255, 255, 255, 0.8);
        cursor: not-allowed;
    }

    /* Input Edit Mode */
    .form-input-edit {
        background-color: white;
        border: 1px solid #d1d5db;
        color: #374151;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .form-input-edit:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        outline: none;
    }

    /* Form Icons */
    .form-icon-wrapper {
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        display: flex;
        align-items: center;
        padding-left: 1rem;
        pointer-events: none;
        z-index: 10;
        transition: color 0.3s ease;
    }

    /* Select Styles */
    select.form-input-view {
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23bfdbfe' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.5rem center;
        background-repeat: no-repeat;
        background-size: 1.5em 1.5em;
        padding-right: 2.5rem;
    }

    select.form-input-edit {
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.5rem center;
        background-repeat: no-repeat;
        background-size: 1.5em 1.5em;
        padding-right: 2.5rem;
    }

    /* Textarea Styles */
    textarea.form-input {
        resize: vertical;
        min-height: 80px;
    }

    .form-error {
        margin-top: 0.25rem;
        font-size: 0.75rem;
        color: #fca5a5;
        font-weight: 500;
    }
</style>
@endpush

<script>
// Helper untuk label pendidikan
function togglePendidikanField() {
    const jenjang = document.getElementById('jenjang_pendidikan').value;
    const lblSekolah = document.getElementById('label_asal_sekolah');
    const lblJurusan = document.getElementById('label_jurusan_sekolah');
    
    if(jenjang === 's1' || jenjang === 'd3') {
        lblSekolah.textContent = 'Asal Universitas / Perguruan Tinggi';
        lblJurusan.textContent = 'Program Studi';
    } else {
        lblSekolah.textContent = 'Asal Sekolah (SMA/SMK/MA)';
        lblJurusan.textContent = 'Jurusan Sekolah';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    togglePendidikanField();
    
    // Add real-time validation for NIK
    const nikInput = document.getElementById('nik');
    if (nikInput) {
        nikInput.addEventListener('input', function(e) {
            // Only allow numbers
            e.target.value = e.target.value.replace(/\D/g, '');
            // Limit to 16 digits
            if (e.target.value.length > 16) {
                e.target.value = e.target.value.slice(0, 16);
            }
        });
    }
});

function wilayahHandler() {
    return {
        listProvinsi: [],
        listKota: [],
        isLoadingProv: false,
        isLoadingKota: false,

        // Mengambil data lama dari database (jika user edit profil)
        selectedProvinsiName: '{{ $biodata->provinsi_asal ?? "" }}',
        selectedKotaName: '{{ $biodata->kota_kabupaten_asal ?? "" }}',

        async initWilayah() {
            await this.loadProvinsi();
            if(this.selectedProvinsiName) {
                await this.loadKota(true);
            }
        },

        async loadProvinsi() {
            this.isLoadingProv = true;
            try {
                let res = await fetch('https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json');
                this.listProvinsi = await res.json();
            } catch(e) { console.error('Gagal load provinsi'); }
            this.isLoadingProv = false;
        },

        async loadKota(isInit = false) {
            let provData = this.listProvinsi.find(p => p.name == this.selectedProvinsiName);
            if (provData) {
                this.isLoadingKota = true;
                try {
                    let res = await fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${provData.id}.json`);
                    this.listKota = await res.json();
                    if(!isInit) this.selectedKotaName = ''; 
                } catch(e) { console.error('Gagal load kota'); }
                this.isLoadingKota = false;
            } else {
                this.listKota = [];
            }
        }
    }
}
</script>

@endsection