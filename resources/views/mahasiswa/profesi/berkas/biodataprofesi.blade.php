@extends('layouts.mahasiswa.profesi')

@section('content')
    <div class="min-h-screen bg-blue-100 py-10 px-4 sm:px-6 lg:px-8 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]">
        <div class="max-w-4xl mx-auto">
            
            {{-- Header Section --}}
            <div class="mb-10 text-center">
                <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 tracking-tight">Lengkapi Biodata Program Profesi</h2>
                <p class="mt-3 text-base text-gray-500 max-w-2xl mx-auto">Silakan lengkapi data diri dan riwayat pendidikan S1 Anda dengan valid untuk keperluan evaluasi administrasi.</p>
            </div>
            
            {{-- Alert Messages --}}
            @if ($errors->any())
                <div class="mb-8 p-5 bg-red-50 border border-red-200 rounded-2xl shadow-sm">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 bg-red-100 text-red-600 rounded-full flex items-center justify-center font-bold">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h3 class="font-bold text-red-800 text-lg">Waduh dawg, ada yang salah pas input:</h3>
                    </div>
                    <ul class="list-disc pl-12 text-sm text-red-600 space-y-1 font-medium">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="mb-8 p-5 bg-green-50 border border-green-200 rounded-2xl shadow-sm flex items-center gap-4">
                    <div class="w-10 h-10 bg-green-100 text-green-600 rounded-full flex items-center justify-center font-bold text-xl shrink-0">
                        <i class="fas fa-check"></i>
                    </div>
                    <p class="font-bold text-green-800">{{ session('success') }}</p>
                </div>
            @endif
    
            {{-- Form Card dengan UI Dashboard (Glassmorphism & Rounded) --}}
            <div class="bg-white/95 backdrop-blur-md shadow-2xl border border-white/20 rounded-3xl overflow-hidden">
                <form id="formBiodata" action="{{ route('profesi.biodata.store') }}" method="POST" enctype="multipart/form-data" class="p-6 sm:p-10 space-y-10">
                    @csrf
                    
                    {{-- SECTION 1: Data Diri Dasar --}}
                    <div class="grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-6">
    
                        {{-- READONLY: Nama Lengkap --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Lengkap</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-slate-400"></i>
                                </div>
                                <input type="text" name="nama" value="{{ old('nama', $biodata->nama) }}" readonly 
                                    class="block w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-500 cursor-not-allowed focus:ring-0">
                                <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-slate-300 text-xs"></i>
                                </div>
                            </div>
                        </div>
                    
                        {{-- READONLY: Email Aktif --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Email Aktif</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                    <i class="fas fa-envelope text-slate-400"></i>
                                </div>
                                <input type="text" name="email_aktif" value="{{ old('email_aktif', $biodata->email_aktif ?? Auth::user()->email ?? '') }}" readonly 
                                    class="block w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-500 cursor-not-allowed focus:ring-0">
                                <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-slate-300 text-xs"></i>
                                </div>
                            </div>
                        </div>
                    
                        {{-- READONLY: Nomor Whatsapp --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nomor Whatsapp</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                    <i class="fab fa-whatsapp text-slate-400 text-lg"></i>
                                </div>
                                <input type="text" name="no_wa" value="{{ old('no_wa', $biodata->no_wa) }}" readonly 
                                    class="block w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-500 cursor-not-allowed focus:ring-0">
                                <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-slate-300 text-xs"></i>
                                </div>
                            </div>
                        </div>
                    
                        {{-- ACTIVE: Tempat Lahir --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tempat Lahir <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                    <i class="fas fa-city text-slate-400 transition-colors peer-focus:text-blue-500"></i>
                                </div>
                                <input type="text" name="tempat_lahir" placeholder="Contoh: Karawang" 
                                    class="peer block w-full pl-10 pr-3 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 placeholder-slate-400 hover:border-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200">
                            </div>
                        </div>
                    
                        {{-- ACTIVE: Tanggal Lahir --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tanggal Lahir <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                    <i class="far fa-calendar-alt text-slate-400 transition-colors peer-focus:text-blue-500"></i>
                                </div>
                                <input type="date" name="tanggal_lahir" 
                                    class="peer block w-full pl-10 pr-3 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 hover:border-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200">
                            </div>
                        </div>
                    
                        {{-- ACTIVE: Alamat Lengkap --}}
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Alamat Lengkap (Sesuai KTP) <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <div class="absolute top-3 left-0 pl-3.5 flex items-start pointer-events-none">
                                    <i class="fas fa-map-marker-alt text-slate-400 transition-colors peer-focus:text-blue-500 mt-0.5"></i>
                                </div>
                                <textarea name="alamat" rows="3" placeholder="Contoh: Jl. HS. Ronggo Waluyo, Sirnabaya, Kec. Telukjambe Timur..." 
                                    class="peer block w-full pl-10 pr-3 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 placeholder-slate-400 hover:border-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200">{{ old('alamat') }}</textarea>
                            </div>
                        </div>
                    
                    
                    </div>
    
                    {{-- SECTION 2: Riwayat Pendidikan S1 --}}
                    <div class="grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-6">
    
                        {{-- ACTIVE: Asal Perguruan Tinggi (S1) --}}
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Asal Perguruan Tinggi (S1) <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                    <i class="fas fa-university text-slate-400 transition-colors peer-focus:text-indigo-500"></i>
                                </div>
                                <input type="text" name="asal_s1" value="{{ old('asal_s1', $biodata->asal_s1 ?? '') }}" placeholder="Cth: Universitas Buana Perjuangan Karawang" 
                                    class="peer block w-full pl-10 pr-3 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 placeholder-slate-400 hover:border-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all duration-200">
                            </div>
                        </div>
                    
                        {{-- ACTIVE: NIM (Khusus UBP) --}}
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-sm font-semibold text-slate-700">NIM / No. Induk Mahasiswa</label>
                                <span class="bg-amber-100 text-amber-700 text-[10px] uppercase tracking-wider font-extrabold px-2 py-0.5 rounded-full border border-amber-200 shadow-sm">Khusus UBP</span>
                            </div>
                        
                            @php
                                // Cek apakah maba punya NIM dari database
                                $currentNim = old('nim', $biodata->nim ?? '');
                            @endphp
                        
                            @if(empty($currentNim))
                                {{-- TAMPILAN JIKA BUKAN ALUMNI (NIM KOSONG) --}}
                                
                                {{-- Form tetap mengirim data kosong tanpa terlihat user --}}
                                <input type="hidden" name="nim" value="">
                                
                                {{-- Kotak informasi pengganti input box --}}
                                <div class="flex items-start gap-3 p-3.5 bg-slate-50 border border-slate-200 rounded-xl">
                                    <div class="mt-0.5 text-slate-400">
                                        <i class="fas fa-id-badge text-xl"></i>
                                    </div>
                                    <div>
                                        <span class="block text-sm font-bold text-slate-700">Pendaftar Non-Alumni</span>
                                        <span class="block text-xs text-slate-500 mt-0.5">Anda tidak memiliki NIM karena bukan lulusan S1 Farmasi UBP. Kolom ini dinonaktifkan.</span>
                                    </div>
                                </div>
                            @else
                                {{-- TAMPILAN JIKA ALUMNI (NIM TERISI) --}}
                                
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                        <i class="fas fa-id-badge text-slate-400"></i>
                                    </div>
                                    <input type="text" name="nim" value="{{ $currentNim }}" readonly 
                                        class="block w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-500 cursor-not-allowed focus:ring-0">
                                    <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none">
                                        <i class="fas fa-lock text-slate-300 text-xs"></i>
                                    </div>
                                </div>
                                <p class="mt-2 text-xs text-slate-500 flex items-start gap-1.5">
                                    <i class="fas fa-info-circle text-blue-500 mt-0.5"></i> 
                                    NIM Anda berhasil ditarik otomatis dari database kampus.
                                </p>
                            @endif
                        </div>
                    
                        {{-- ACTIVE: Tahun Lulus S1 --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tahun Lulus S1 <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                    <i class="fas fa-user-graduate text-slate-400 transition-colors peer-focus:text-indigo-500"></i>
                                </div>
                                <input type="number" name="tahun_lulus" value="{{ old('tahun_lulus', $biodata->tahun_lulus ?? '') }}" placeholder="Cth: 2024" 
                                    class="peer block w-full pl-10 pr-3 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 placeholder-slate-400 hover:border-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all duration-200">
                            </div>
                        </div>
                    
                    </div>
                    </div>
    
                    {{-- Tombol Submit --}}
                    <div class="pt-6 border-t border-gray-100">
                        <div class="flex items-center justify-between sm:justify-end gap-4">
                            <a href="{{ route('profesi.dashboard') }}" class="text-sm font-bold text-gray-500 hover:text-gray-700 transition-colors">
                                <i class="fas fa-arrow-left mr-1"></i> Kembali
                            </a>
                            <button type="submit" class="inline-flex justify-center items-center gap-2 py-3 px-8 border border-transparent shadow-md text-sm font-bold rounded-xl text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all transform hover:-translate-y-0.5">
                                <i class="fas fa-save"></i> Simpan Biodata & Lanjutkan
                            </button>
                        </div>
                    </div>
    
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.getElementById('formBiodata');
            
            if (!form) {
                console.error("Waduh dawg, form dengan ID 'formBiodata' gak ketemu!");
                return;
            }
    
            const storageKey = 'draft_biodata_profesi';
    
            // 1. RESTORE DRAFT
            const rawData = localStorage.getItem(storageKey);
            if (rawData) {
                try {
                    const draftData = JSON.parse(rawData);
                    
                    Object.keys(draftData).forEach(key => {
                        const input = form.querySelector(`[name="${key}"]`);
                        
                        if (input && key !== '_token' && !input.readOnly) {
                            if (input.value.trim() === '') {
                                input.value = draftData[key];
                            }
                        }
                    });
                    console.log("✅ Mantap dawg! Data ketikan sebelumnya berhasil dibangkitkan.");
                } catch (error) {
                    console.error("❌ Gagal membaca memori draft:", error);
                }
            }
    
            // 2. AUTO-SAVE
            const saveToDraft = function() {
                const formData = new FormData(form);
                const dataObj = {};
                
                formData.forEach((value, key) => {
                    if (key !== '_token' && value.trim() !== '') { 
                        dataObj[key] = value;
                    }
                });
                
                localStorage.setItem(storageKey, JSON.stringify(dataObj));
                console.log("💾 Draft tersimpan diam-diam...");
            };
    
            form.addEventListener('input', saveToDraft);
            form.addEventListener('change', saveToDraft);
    
            // 3. BERSIHKAN MEMORI SAAT DI-SUBMIT
            form.addEventListener('submit', function() {
                localStorage.removeItem(storageKey);
                console.log("🧹 Memori draft dibersihkan karena data sudah disubmit!");
            });
        });
    </script>
@endsection