{{-- FILE: resources/views/mahasiswa/register/steps/step3.blade.php --}}

<style>
    /* === DESKTOP: HORIZONTAL === */
    .radio-group-container {
        background-color: #ffffff;
        padding: 8px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        width: 100%;
        display: flex;
        flex-direction: row; /* Default: Menyamping */
        gap: 8px;
        margin-bottom: 20px;
    }

    /* Label Wrapper */
    .radio-label {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center; /* Desktop: Rata Tengah */
        cursor: pointer;
        position: relative;
        user-select: none;
        padding: 12px;
        border-radius: 8px;
        transition: all 0.2s ease;
        border: 1px solid transparent; 
    }

    /* Hover Effect */
    .radio-label:hover {
        background-color: #f8fafc;
    }

    /* Lingkaran Custom */
    .radio-custom {
        width: 18px;
        height: 18px;
        background-color: #fff;
        border: 2px solid #cbd5e1;
        border-radius: 50%;
        margin-right: 10px;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: all 0.3s ease;
    }

    /* Titik Tengah */
    .radio-custom::before {
        content: "";
        position: absolute;
        width: 8px;
        height: 8px;
        background: #94a3b8;
        border-radius: 50%;
        transform: scale(0);
        transition: all 0.2s ease;
    }

    /* Cincin Orbit */
    .radio-custom::after {
        content: "";
        position: absolute;
        width: 28px;
        height: 28px;
        border: 2px solid transparent;
        border-radius: 50%;
        opacity: 0;
        transform: scale(0.8);
        transition: all 0.4s ease;
    }

    /* Teks Label */
    .radio-text {
        font-size: 0.9rem;
        font-weight: 500;
        color: #64748b;
        white-space: nowrap;
    }

    /* Sembunyikan Input Asli */
    .radio-input { display: none; }

    /* --- STATE CHECKED --- */
    .radio-input:checked + .radio-custom {
        border-color: currentColor; /* Ikut warna parent */
        transform: scale(1);
    }
    .radio-input:checked + .radio-custom::before {
        background-color: currentColor;
        transform: scale(1);
    }
    .radio-input:checked + .radio-custom::after {
        opacity: 1;
        transform: scale(1.1);
        animation: spinOrbit 3s linear infinite;
        border-top-color: currentColor;
    }
    .radio-input:checked ~ .radio-text {
        color: #0f172a;
        font-weight: 700;
    }

    /* Warna Spesifik (Menggunakan Variable CSS agar ringkas) */
    .radio-label:nth-of-type(1) { color: #3b82f6; } /* Biru */
    .radio-label:nth-of-type(2) { color: #d946ef; } /* Magenta */
    .radio-label:nth-of-type(3) { color: #14b8a6; } /* Hijau */

    /* Active Background Style */
    .radio-label:has(.radio-input:checked) {
        background-color: #f1f5f9;
        border-color: #e2e8f0;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }

    @keyframes spinOrbit {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    /* === MOBILE RESPONSIVE (Tampilan Rapi Vertikal) === */
    @media (max-width: 640px) {
        .radio-group-container {
            flex-direction: column; /* Ubah jadi vertikal */
            padding: 10px;
            gap: 10px;
        }

        .radio-label {
            width: 100%;
            justify-content: flex-start; /* PENTING: Rata Kiri agar lurus */
            padding: 12px 15px; /* Padding lebih besar biar enak ditekan */
            background-color: #fff; /* Reset background */
            border: 1px solid #f1f5f9; /* Border tipis pemisah */
        }

        /* Saat dipilih di mobile, warnanya lebih jelas */
        .radio-label:has(.radio-input:checked) {
            background-color: #f8fafc;
            border-color: currentColor; /* Border ikut warna (Biru/Ungu/Hijau) */
        }
        
        /* Agar teks tidak terlalu mepet ke kanan */
        .radio-text {
            font-size: 0.95rem; /* Sedikit lebih besar di HP */
        }
    }
</style>

@php
    use App\Models\ProgramStudi;
    use Illuminate\Support\Str;

    $jalur = $registration->jalur_pendaftaran;
    
    // Ambil Nama Jurusan
    $namaJurusan1 = $registration->programStudi1->nama_prodi ?? $registration->jurusan1 ?? '-';
    $namaJurusan2 = $registration->programStudi2->nama_prodi ?? $registration->jurusan2 ?? '-';

    // Cek Farmasi
    $isFarmasi = Str::contains(strtolower($namaJurusan1), 'farmasi') || Str::contains(strtolower($namaJurusan2), 'farmasi');
@endphp

<div x-data="registrationApp()" x-show="currentStep === 3" class="animate-fade-in-up relative" style="display: none;">
    <!-- Decorative background glow for the card -->
    <div class="absolute -inset-1 bg-gradient-to-r from-blue-100 to-purple-100 rounded-[2.5rem] blur-xl opacity-50 pointer-events-none"></div>

    <div class="relative bg-white/80 backdrop-blur-xl rounded-[2rem] shadow-xl border border-white p-6 sm:p-10">

    {{-- A. HEADER SECTION --}}
    <div class="flex items-center gap-4 mb-8 pb-6 border-b border-indigo-100/50 relative">
        <!-- Animated Icon Container -->
        <div class="relative h-14 w-14 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-[0_8px_16px_-6px_rgba(79,70,229,0.5)] overflow-hidden group-hover:scale-105 transition-transform">
            <div class="absolute inset-0 bg-white/20 blur-sm translate-y-full group-hover:translate-y-0 transition-transform duration-500"></div>
            <i class="fas fa-cloud-upload-alt text-xl relative z-10"></i>
        </div>
        <div>
            <h2 class="text-2xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-slate-800 to-indigo-800 tracking-tight">Upload Dokumen</h2>
            <p class="text-sm font-medium text-slate-500 mt-0.5">Lengkapi berkas persyaratan di bawah ini.</p>
        </div>
    </div>

    {{-- A.1. NOTICE / TIPS (BARU DITAMBAHKAN) --}}
    <div class="mb-8 bg-amber-50 border border-amber-200 rounded-2xl p-5 shadow-sm relative overflow-hidden">
        {{-- Hiasan Background --}}
        <div class="absolute top-0 right-0 -mt-2 -mr-2 w-16 h-16 bg-amber-100 rounded-full opacity-50 blur-xl"></div>
        
        <div class="flex items-start gap-4 relative z-10">
            <div class="flex-shrink-0">
                <div class="h-10 w-10 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-lightbulb text-lg"></i>
                </div>
            </div>
            <div>
                <h3 class="font-bold text-amber-800 text-sm mb-1">Tips Agar Lolos Validasi AI:</h3>
                <ul class="text-xs text-amber-700 space-y-1.5 list-disc list-inside">
                    <li>
                        Gunakan aplikasi scan HP (contoh: <strong>CamScanner, Adobe Scan, atau Office Lens</strong>) agar dokumen terlihat <strong>tegak, jernih, dan tidak gelap</strong>.
                    </li>
                    {{-- TAMBAHAN BARU DI SINI --}}
                    <li>
                        Posisi gambar/dokumen harus <strong>lurus sempurna (tegak)</strong>. Tidak boleh <strong>miring, terpotong, atau terbalik</strong> agar AI dapat membacanya.
                    </li>
                    <li>
                        Wajib format <strong>PDF</strong>.
                    </li>
                    <li>
                        Pastikan ukuran file <strong>di bawah 2MB</strong> agar upload tidak gagal.
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- B. AI SCANNER RESULTS --}}
    <div x-show="scanStatus.scan_status !== 'not_scanned'" 
         class="mb-8 overflow-hidden rounded-2xl border border-blue-100 bg-white shadow-lg">
        <div class="flex items-center justify-between p-4 bg-blue-50 border-b border-blue-100">
            <div class="flex items-center gap-3">
                <div class="h-8 w-8 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                    <i class="fas fa-robot"></i>
                </div>
                <h3 class="font-bold text-slate-800 text-sm">Hasil Analisis AI</h3>
            </div>
            <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide border"
                  :class="{
                      'bg-green-100 text-green-700 border-green-200': scanStatus.scan_status === 'completed',
                      'bg-amber-100 text-amber-700 border-amber-200': scanStatus.scan_status === 'partial_failed',
                      'bg-blue-100 text-blue-700 border-blue-200': scanStatus.scan_status === 'scanning'
                  }">
                <span x-text="getStatusText(scanStatus.scan_status)"></span>
            </span>
        </div>
        <div class="p-4 space-y-2">
            <template x-for="(result, fileType) in scanStatus.scan_results">
                <div class="flex items-center justify-between p-3 rounded-lg border text-sm"
                     :class="result.is_valid ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'">
                    <div class="flex items-center gap-3">
                        <i class="fas" :class="result.is_valid ? 'fa-check text-green-600' : 'fa-times text-red-600'"></i>
                        <span class="font-bold text-slate-700" x-text="getFileName(fileType)"></span>
                    </div>
                    <span class="text-xs font-bold" :class="result.is_valid ? 'text-green-700' : 'text-red-700'" x-text="result.is_valid ? 'VALID' : 'INVALID'"></span>
                </div>
            </template>
        </div>
    </div>

    {{-- D. FORM UPLOAD --}}
    <form id="step3Form" x-on:submit.prevent="submitStep3()" enctype="multipart/form-data">
        @csrf
        
        {{-- D.1 BERKAS IDENTITAS (WAJIB SEMUA JALUR) --}}
        <div class="mb-10">
            <div class="flex items-center gap-2 mb-6">
                <span class="h-8 w-1.5 bg-blue-600 rounded-full"></span>
                <h3 class="text-lg font-bold text-slate-800 tracking-tight">Berkas Identitas</h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- 1. KTP --}}
                <div class="group relative">
                    <label class="block text-sm font-bold text-slate-700 mb-2">KTP Asli <span class="text-red-500">*</span></label>
                    <div class="relative w-full h-40 rounded-2xl border-2 border-dashed flex flex-col items-center justify-center gap-2 overflow-hidden bg-slate-50 border-slate-300 hover:border-blue-400 transition-all cursor-pointer"
                         :class="hasFile('file_ktp') ? '!border-green-500 !bg-green-50' : ''"
                         onclick="document.getElementById('file_ktp').click()">
                        
                        <input type="file" name="file_ktp" id="file_ktp" class="hidden" accept=".pdf,.jpg,.jpeg,.png" required x-on:change="handleFileChange('file_ktp')">
                        
                        <div x-show="!hasFile('file_ktp')" class="text-center pointer-events-none">
                            <i class="fas fa-id-card text-2xl text-slate-400 mb-2"></i>
                            <p class="text-sm font-bold text-slate-600">Upload KTP</p>
                        </div>
                        <div x-show="hasFile('file_ktp')" class="text-center pointer-events-none w-full px-4" style="display:none;">
                            <div class="h-10 w-10 mx-auto rounded-full bg-green-500 text-white flex items-center justify-center mb-2 shadow-lg">
                                <i class="fas fa-check"></i>
                            </div>
                            <div id="file_ktp_name" class="w-full text-xs font-bold text-green-700 truncate"></div>
                        </div>
                    </div>
                </div>

                {{-- 2. KK --}}
                <div class="group relative">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Kartu Keluarga <span class="text-red-500">*</span></label>
                    <div class="relative w-full h-40 rounded-2xl border-2 border-dashed flex flex-col items-center justify-center gap-2 overflow-hidden bg-slate-50 border-slate-300 hover:border-blue-400 transition-all cursor-pointer"
                         :class="hasFile('file_kk') ? '!border-green-500 !bg-green-50' : ''"
                         onclick="document.getElementById('file_kk').click()">
                        
                        <input type="file" name="file_kk" id="file_kk" class="hidden" accept=".pdf,.jpg,.jpeg,.png" required x-on:change="handleFileChange('file_kk')">
                        
                        <div x-show="!hasFile('file_kk')" class="text-center pointer-events-none">
                            <i class="fas fa-users text-2xl text-slate-400 mb-2"></i>
                            <p class="text-sm font-bold text-slate-600">Upload KK</p>
                        </div>
                        <div x-show="hasFile('file_kk')" class="text-center pointer-events-none w-full px-4" style="display:none;">
                            <div class="h-10 w-10 mx-auto rounded-full bg-green-500 text-white flex items-center justify-center mb-2 shadow-lg">
                                <i class="fas fa-check"></i>
                            </div>
                            <div id="file_kk_name" class="w-full text-xs font-bold text-green-700 truncate"></div>
                        </div>
                    </div>
                </div>
                
                {{-- 3. surat ket sehat --}}
                <div class="group relative">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Surat Keterangan Sehat <span class="text-red-500">*</span></label>
                    <div class="relative w-full h-40 rounded-2xl border-2 border-dashed flex flex-col items-center justify-center gap-2 overflow-hidden bg-slate-50 border-slate-300 hover:border-blue-400 transition-all cursor-pointer"
                         :class="hasFile('file_surat_keterangan_sehat') ? '!border-green-500 !bg-green-50' : ''"
                         onclick="document.getElementById('file_surat_keterangan_sehat').click()">
                        <input type="file" name="file_surat_keterangan_sehat" id="file_surat_keterangan_sehat" class="hidden" accept=".pdf,.jpg,.jpeg,.png" required x-on:change="handleFileChange('file_surat_keterangan_sehat')">
                        <div x-show="!hasFile('file_surat_keterangan_sehat')" class="text-center pointer-events-none">
                            <i class="fas fa-file-medical text-2xl text-slate-400 mb-2"></i>
                            <p class="text-sm font-bold text-slate-600">Upload Surat Sehat</p>
                        </div>
                        <div x-show="hasFile('file_surat_keterangan_sehat')" class="text-center pointer-events-none w-full px-4" style="display:none;">
                            <div class="h-10 w-10 mx-auto rounded-full bg-green-500 text-white flex items-center justify-center mb-2 shadow-lg">
                                <i class="fas fa-check"></i>
                            </div>
                            <div id="file_surat_keterangan_sehat_name" class="w-full text-xs font-bold text-green-700 truncate"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- D.2 BERKAS AKADEMIK (DINAMIS SESUAI JALUR) --}}
        <div class="mb-10">
            <div class="flex items-center gap-2 mb-6">
                <span class="h-8 w-1.5 bg-indigo-600 rounded-full"></span>
                <h3 class="text-lg font-bold text-slate-800 tracking-tight">Berkas Akademik ({{ $jalur }})</h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                {{-- === 1. JALUR REGULER === --}}
                @if($jalur == 'Jalur Reguler')
                    <div class="group relative col-span-1 md:col-span-2">
                        <label class="block text-sm font-bold text-slate-700 mb-3">Pilih Dokumen Akademik <span class="text-red-500">*</span></label>
                        
                        {{-- Penanda untuk Backend agar tahu jenis dokumen apa yang dikirim --}}
                        <input type="hidden" name="jenis_dokumen_reguler" id="jenis_dokumen_reguler" value="file_ijazah">
                
                        {{-- Opsi Radio Button --}}
                        <div class="radio-group-container">
                            {{-- Opsi 1: Ijazah --}}
                            <label class="radio-label">
                                <input type="radio" 
                                       name="doc_choice" 
                                       value="file_ijazah" 
                                       class="radio-input" 
                                       checked
                                       onclick="switchReguler('file_ijazah')"
                                       x-on:change="$nextTick(() => { scanStatus.scan_status = 'not_scanned'; })">
                                <span class="radio-custom"></span>
                                <span class="radio-text">Ijazah</span>
                            </label>
                        
                            {{-- Opsi 2: SKL --}}
                            <label class="radio-label">
                                <input type="radio" 
                                       name="doc_choice" 
                                       value="file_skl" 
                                       class="radio-input"
                                       onclick="switchReguler('file_skl')"
                                       x-on:change="$nextTick(() => { scanStatus.scan_status = 'not_scanned'; })">
                                <span class="radio-custom"></span>
                                <span class="radio-text">SKL</span>
                            </label>
                        
                            {{-- Opsi 3: Rapor --}}
                            <label class="radio-label">
                                <input type="radio" 
                                       name="doc_choice" 
                                       value="file_rapor" 
                                       class="radio-input"
                                       onclick="switchReguler('file_rapor')"
                                       x-on:change="$nextTick(() => { scanStatus.scan_status = 'not_scanned'; })">
                                <span class="radio-custom"></span>
                                <span class="radio-text">Rapor</span>
                            </label>
                        </div>
                        
                        {{-- Input File (Terpisah dari Radio Button) --}}
                        {{-- 1. Ijazah (Default: Required & Aktif) --}}
                        <input type="file" name="file_ijazah" id="file_ijazah" class="hidden" accept=".pdf,.jpg,.jpeg,.png" 
                               required 
                               x-on:change="handleFileChange('file_ijazah')"
                               onchange="if(this.files.length > 0) window.updateRegulerUI(this.files[0].name)">
                
                        {{-- 2. SKL (Default: Disabled) --}}
                        <input type="file" name="file_skl" id="file_skl" class="hidden" accept=".pdf,.jpg,.jpeg,.png" 
                               disabled 
                               x-on:change="handleFileChange('file_skl')"
                               onchange="if(this.files.length > 0) window.updateRegulerUI(this.files[0].name)">
                
                        {{-- 3. Rapor (Default: Disabled) --}}
                        <input type="file" name="file_rapor" id="file_rapor" class="hidden" accept=".pdf,.jpg,.jpeg,.png" 
                               disabled 
                               x-on:change="handleFileChange('file_rapor')"
                               onchange="if(this.files.length > 0) window.updateRegulerUI(this.files[0].name)">
                        
                        {{-- Dropzone Area --}}
                        <div id="dropzone_reguler" class="relative w-full h-40 rounded-2xl border-2 border-dashed bg-slate-50 border-slate-300 hover:border-blue-400 transition-all flex flex-col items-center justify-center cursor-pointer"
                             onclick="triggerRegulerUpload()">
                            
                            {{-- State Kosong --}}
                            <div id="reguler_placeholder" class="text-center">
                                <i class="fas fa-certificate text-2xl text-slate-400 mb-2"></i>
                                <p class="text-sm font-bold text-slate-600">Upload Dokumen</p>
                                <p class="text-xs text-slate-400 mt-1" id="label_dokumen_aktif">Ijazah</p>
                            </div>
                
                            {{-- State Sudah Upload --}}
                            <div id="reguler_success" class="text-center hidden">
                                <i class="fas fa-check-circle text-2xl text-green-500 mb-2"></i>
                                <div id="reguler_filename_display" class="w-full text-xs font-bold text-green-700 truncate px-4"></div>
                                <p class="text-[10px] text-slate-400 mt-1">Klik untuk ganti</p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- === 2. JALUR EKSTENSI === --}}
                @if($jalur == 'Jalur Ekstensi')
                    <div class="group relative">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Ijazah D3/Sederajat <span class="text-red-500">*</span></label>
                        <div class="relative w-full h-40 rounded-2xl border-2 border-dashed bg-slate-50 border-slate-300 hover:border-blue-400 transition-all flex flex-col items-center justify-center cursor-pointer"
                             :class="hasFile('file_ijazah') ? '!border-green-500 !bg-green-50' : ''" onclick="document.getElementById('file_ijazah').click()">
                            <input type="file" name="file_ijazah" id="file_ijazah" class="hidden" accept=".pdf,.jpg,.jpeg,.png" required x-on:change="handleFileChange('file_ijazah')">
                            <div x-show="!hasFile('file_ijazah')" class="text-center pointer-events-none">
                                <i class="fas fa-certificate text-2xl text-slate-400 mb-2"></i>
                                <p class="text-sm font-bold text-slate-600">Upload Ijazah D3</p>
                            </div>
                            <div x-show="hasFile('file_ijazah')" class="text-center w-full px-4" style="display:none;">
                                <i class="fas fa-check-circle text-2xl text-green-500 mb-2"></i>
                                <div id="file_ijazah_name" class="w-full text-xs font-bold text-green-700 truncate"></div>
                            </div>
                        </div>
                    </div>
                    <div class="group relative">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Transkrip Nilai (Legalisir) <span class="text-red-500">*</span></label>
                        <div class="relative w-full h-40 rounded-2xl border-2 border-dashed bg-slate-50 border-slate-300 hover:border-blue-400 transition-all flex flex-col items-center justify-center cursor-pointer"
                             :class="hasFile('file_transkrip_s1') ? '!border-green-500 !bg-green-50' : ''" onclick="document.getElementById('file_transkrip_s1').click()">
                            <input type="file" name="file_transkrip_s1" id="file_transkrip_s1" class="hidden" accept=".pdf,.jpg,.jpeg,.png" required x-on:change="handleFileChange('file_transkrip_s1')">
                            <div x-show="!hasFile('file_transkrip_s1')" class="text-center pointer-events-none">
                                <i class="fas fa-file-contract text-2xl text-slate-400 mb-2"></i>
                                <p class="text-sm font-bold text-slate-600">Upload Transkrip</p>
                            </div>
                            <div x-show="hasFile('file_transkrip_s1')" class="text-center w-full px-4" style="display:none;">
                                <i class="fas fa-check-circle text-2xl text-green-500 mb-2"></i>
                                <div id="file_transkrip_s1_name" class="w-full text-xs font-bold text-green-700 truncate"></div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- === 3. PINDAHAN === --}}
                @if($jalur == 'Pindahan')
                    <div class="group relative">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Surat Pindah <span class="text-red-500">*</span></label>
                        <div class="relative w-full h-40 rounded-2xl border-2 border-dashed bg-slate-50 border-slate-300 hover:border-blue-400 transition-all flex flex-col items-center justify-center cursor-pointer"
                             :class="hasFile('file_surat_pindah') ? '!border-green-500 !bg-green-50' : ''" onclick="document.getElementById('file_surat_pindah').click()">
                            <input type="file" name="file_surat_pindah" id="file_surat_pindah" class="hidden" accept=".pdf,.jpg,.jpeg,.png" required x-on:change="handleFileChange('file_surat_pindah')">
                            <i class="fas fa-envelope-open-text text-2xl text-slate-400 mb-2" x-show="!hasFile('file_surat_pindah')"></i>
                            <i class="fas fa-check-circle text-2xl text-green-500 mb-2" x-show="hasFile('file_surat_pindah')" style="display:none"></i>
                            <p class="text-sm font-bold text-slate-600">Upload Surat Pindah</p>
                            <div id="file_surat_pindah_name" class="text-xs text-green-700 font-bold truncate px-4" x-show="hasFile('file_surat_pindah')"></div>
                        </div>
                    </div>
                    <div class="group relative">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Transkrip Nilai <span class="text-red-500">*</span></label>
                        <div class="relative w-full h-40 rounded-2xl border-2 border-dashed bg-slate-50 border-slate-300 hover:border-blue-400 transition-all flex flex-col items-center justify-center cursor-pointer"
                             :class="hasFile('file_transkrip_s1') ? '!border-green-500 !bg-green-50' : ''" onclick="document.getElementById('file_transkrip_s1').click()">
                            <input type="file" name="file_transkrip_s1" id="file_transkrip_s1" class="hidden" accept=".pdf,.jpg,.jpeg,.png" required x-on:change="handleFileChange('file_transkrip_s1')">
                            <i class="fas fa-list-ol text-2xl text-slate-400 mb-2" x-show="!hasFile('file_transkrip_s1')"></i>
                            <i class="fas fa-check-circle text-2xl text-green-500 mb-2" x-show="hasFile('file_transkrip_s1')" style="display:none"></i>
                            <p class="text-sm font-bold text-slate-600">Upload Transkrip</p>
                            <div id="file_transkrip_s1_name" class="text-xs text-green-700 font-bold truncate px-4" x-show="hasFile('file_transkrip_s1')"></div>
                        </div>
                    </div>
                    <div class="group relative">
                        <label class="block text-sm font-bold text-slate-700 mb-2">KTM Asal <span class="text-red-500">*</span></label>
                        <div class="relative w-full h-40 rounded-2xl border-2 border-dashed bg-slate-50 border-slate-300 hover:border-blue-400 transition-all flex flex-col items-center justify-center cursor-pointer"
                             :class="hasFile('file_ktm') ? '!border-green-500 !bg-green-50' : ''" onclick="document.getElementById('file_ktm').click()">
                            <input type="file" name="file_ktm" id="file_ktm" class="hidden" accept=".pdf,.jpg,.jpeg,.png" required x-on:change="handleFileChange('file_ktm')">
                            <i class="fas fa-id-badge text-2xl text-slate-400 mb-2" x-show="!hasFile('file_ktm')"></i>
                            <i class="fas fa-check-circle text-2xl text-green-500 mb-2" x-show="hasFile('file_ktm')" style="display:none"></i>
                            <p class="text-sm font-bold text-slate-600">Upload KTM</p>
                            <div id="file_ktm_name" class="text-xs text-green-700 font-bold truncate px-4" x-show="hasFile('file_ktm')"></div>
                        </div>
                    </div>
                    <div class="group relative">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Ijazah SMA/SMK <span class="text-red-500">*</span></label>
                        <div class="relative w-full h-40 rounded-2xl border-2 border-dashed bg-slate-50 border-slate-300 hover:border-blue-400 transition-all flex flex-col items-center justify-center cursor-pointer"
                             :class="hasFile('file_ijazah') ? '!border-green-500 !bg-green-50' : ''" onclick="document.getElementById('file_ijazah').click()">
                            <input type="file" name="file_ijazah" id="file_ijazah" class="hidden" accept=".pdf,.jpg,.jpeg,.png" required x-on:change="handleFileChange('file_ijazah')">
                            <i class="fas fa-certificate text-2xl text-slate-400 mb-2" x-show="!hasFile('file_ijazah')"></i>
                            <i class="fas fa-check-circle text-2xl text-green-500 mb-2" x-show="hasFile('file_ijazah')" style="display:none"></i>
                            <p class="text-sm font-bold text-slate-600">Upload Ijazah SMA</p>
                            <div id="file_ijazah_name" class="text-xs text-green-700 font-bold truncate px-4" x-show="hasFile('file_ijazah')"></div>
                        </div>
                    </div>
                @endif

                {{-- === 4. JALUR S2 MANAJEMEN === --}}
                @if($jalur == 'Jalur S2 Manajemen')
                    <div class="group relative">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Ijazah S1 <span class="text-red-500">*</span></label>
                        <div class="relative w-full h-40 rounded-2xl border-2 border-dashed bg-slate-50 border-slate-300 hover:border-blue-400 transition-all flex flex-col items-center justify-center cursor-pointer"
                             :class="hasFile('file_ijazah_s1') ? '!border-green-500 !bg-green-50' : ''" onclick="document.getElementById('file_ijazah_s1').click()">
                            <input type="file" name="file_ijazah_s1" id="file_ijazah_s1" class="hidden" accept=".pdf,.jpg,.jpeg,.png" required x-on:change="handleFileChange('file_ijazah_s1')">
                            <i class="fas fa-graduation-cap text-2xl text-slate-400 mb-2" x-show="!hasFile('file_ijazah_s1')"></i>
                            <p class="text-sm font-bold text-slate-600" x-show="!hasFile('file_ijazah_s1')">Upload Ijazah S1</p>
                            <i class="fas fa-check-circle text-2xl text-green-500 mb-2" x-show="hasFile('file_ijazah_s1')" style="display:none"></i>
                            <div id="file_ijazah_s1_name" class="text-xs text-green-700 font-bold truncate px-4" x-show="hasFile('file_ijazah_s1')"></div>
                        </div>
                    </div>
                    <div class="group relative">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Transkrip S1 <span class="text-red-500">*</span></label>
                        <div class="relative w-full h-40 rounded-2xl border-2 border-dashed bg-slate-50 border-slate-300 hover:border-blue-400 transition-all flex flex-col items-center justify-center cursor-pointer"
                             :class="hasFile('file_transkrip_s1') ? '!border-green-500 !bg-green-50' : ''" onclick="document.getElementById('file_transkrip_s1').click()">
                            <input type="file" name="file_transkrip_s1" id="file_transkrip_s1" class="hidden" accept=".pdf,.jpg,.jpeg,.png" required x-on:change="handleFileChange('file_transkrip_s1')">
                            <i class="fas fa-file-alt text-2xl text-slate-400 mb-2" x-show="!hasFile('file_transkrip_s1')"></i>
                            <p class="text-sm font-bold text-slate-600" x-show="!hasFile('file_transkrip_s1')">Upload Transkrip S1</p>
                            <i class="fas fa-check-circle text-2xl text-green-500 mb-2" x-show="hasFile('file_transkrip_s1')" style="display:none"></i>
                            <div id="file_transkrip_s1_name" class="text-xs text-green-700 font-bold truncate px-4" x-show="hasFile('file_transkrip_s1')"></div>
                        </div>
                    </div>
                @endif

                {{-- === 5. JALUR RPL === --}}
                @if($jalur == 'Jalur RPL')
                    {{-- 5.1 Surat Tugas --}}
                    <div class="group relative">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Surat Tugas Kerja <span class="text-red-500">*</span></label>
                        <div class="relative w-full h-40 rounded-2xl border-2 border-dashed bg-slate-50 border-slate-300 hover:border-blue-400 transition-all flex flex-col items-center justify-center cursor-pointer"
                             :class="hasFile('file_surat_tugas_kerja') ? '!border-green-500 !bg-green-50' : ''" onclick="document.getElementById('file_surat_tugas_kerja').click()">
                            <input type="file" name="file_surat_tugas_kerja" id="file_surat_tugas_kerja" class="hidden" accept=".pdf,.jpg,.jpeg,.png" required x-on:change="handleFileChange('file_surat_tugas_kerja')">
                            <i class="fas fa-briefcase text-2xl text-slate-400 mb-2" x-show="!hasFile('file_surat_tugas_kerja')"></i>
                            <p class="text-sm font-bold text-slate-600" x-show="!hasFile('file_surat_tugas_kerja')">Upload Surat Tugas</p>
                            <i class="fas fa-check-circle text-2xl text-green-500 mb-2" x-show="hasFile('file_surat_tugas_kerja')" style="display:none"></i>
                            <div id="file_surat_tugas_kerja_name" class="text-xs text-green-700 font-bold truncate px-4" x-show="hasFile('file_surat_tugas_kerja')"></div>
                        </div>
                    </div>
                    {{-- 5.2 Transkrip RPL --}}
                    <div class="group relative">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Transkrip Nilai RPL <span class="text-red-500">*</span></label>
                        <div class="relative w-full h-40 rounded-2xl border-2 border-dashed bg-slate-50 border-slate-300 hover:border-blue-400 transition-all flex flex-col items-center justify-center cursor-pointer"
                             :class="hasFile('file_transkrip_nilai_rpl') ? '!border-green-500 !bg-green-50' : ''" onclick="document.getElementById('file_transkrip_nilai_rpl').click()">
                            <input type="file" name="file_transkrip_nilai_rpl" id="file_transkrip_nilai_rpl" class="hidden" accept=".pdf,.jpg,.jpeg,.png" required x-on:change="handleFileChange('file_transkrip_nilai_rpl')">
                            <i class="fas fa-file-invoice text-2xl text-slate-400 mb-2" x-show="!hasFile('file_transkrip_nilai_rpl')"></i>
                            <p class="text-sm font-bold text-slate-600" x-show="!hasFile('file_transkrip_nilai_rpl')">Upload Transkrip RPL</p>
                            <i class="fas fa-check-circle text-2xl text-green-500 mb-2" x-show="hasFile('file_transkrip_nilai_rpl')" style="display:none"></i>
                            <div id="file_transkrip_nilai_rpl_name" class="text-xs text-green-700 font-bold truncate px-4" x-show="hasFile('file_transkrip_nilai_rpl')"></div>
                        </div>
                    </div>
                    {{-- 5.3 Surat Akademik RPL (INI YANG TADI HILANG) --}}
                    <!--<div class="group relative">-->
                    <!--    <label class="block text-sm font-bold text-slate-700 mb-2">Surat Akademik RPL <span class="text-red-500">*</span></label>-->
                    <!--    <div class="relative w-full h-40 rounded-2xl border-2 border-dashed bg-slate-50 border-slate-300 hover:border-blue-400 transition-all flex flex-col items-center justify-center cursor-pointer"-->
                    <!--         :class="hasFile('file_surat_akademik_rpl') ? '!border-green-500 !bg-green-50' : ''" onclick="document.getElementById('file_surat_akademik_rpl').click()">-->
                    <!--        <input type="file" name="file_surat_akademik_rpl" id="file_surat_akademik_rpl" class="hidden" accept=".pdf,.jpg,.jpeg,.png" required x-on:change="handleFileChange('file_surat_akademik_rpl')">-->
                    <!--        <i class="fas fa-file-signature text-2xl text-slate-400 mb-2" x-show="!hasFile('file_surat_akademik_rpl')"></i>-->
                    <!--        <p class="text-sm font-bold text-slate-600" x-show="!hasFile('file_surat_akademik_rpl')">Upload Surat Akademik</p>-->
                    <!--        <i class="fas fa-check-circle text-2xl text-green-500 mb-2" x-show="hasFile('file_surat_akademik_rpl')" style="display:none"></i>-->
                    <!--        <div id="file_surat_akademik_rpl_name" class="text-xs text-green-700 font-bold truncate px-4" x-show="hasFile('file_surat_akademik_rpl')"></div>-->
                    <!--    </div>-->
                    <!--</div>-->
                    {{-- 5.4 Ijazah --}}
                    <div class="group relative">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Ijazah <span class="text-red-500">*</span></label>
                        <div class="relative w-full h-40 rounded-2xl border-2 border-dashed bg-slate-50 border-slate-300 hover:border-blue-400 transition-all flex flex-col items-center justify-center cursor-pointer"
                             :class="hasFile('file_ijazah') ? '!border-green-500 !bg-green-50' : ''" onclick="document.getElementById('file_ijazah').click()">
                            <input type="file" name="file_ijazah" id="file_ijazah" class="hidden" accept=".pdf,.jpg,.jpeg,.png" required x-on:change="handleFileChange('file_ijazah')">
                            <i class="fas fa-certificate text-2xl text-slate-400 mb-2" x-show="!hasFile('file_ijazah')"></i>
                            <p class="text-sm font-bold text-slate-600" x-show="!hasFile('file_ijazah')">Upload Ijazah</p>
                            <i class="fas fa-check-circle text-2xl text-green-500 mb-2" x-show="hasFile('file_ijazah')" style="display:none"></i>
                            <div id="file_ijazah_name" class="text-xs text-green-700 font-bold truncate px-4" x-show="hasFile('file_ijazah')"></div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- D.3 KHUSUS FARMASI --}}
        @if($isFarmasi)
            <div class="mb-10 pt-6 border-t border-gray-200">
                <div class="flex items-center mb-6 gap-3">
                    <span class="bg-blue-100 text-blue-800 text-[10px] font-bold px-3 py-1 rounded-full border border-blue-200 uppercase tracking-wide">Wajib</span>
                    <h4 class="text-lg font-bold text-slate-700 tracking-tight">Persyaratan Khusus Farmasi</h4>
                </div>
                <div class="group relative max-w-md">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Surat Ket. Buta Warna <span class="text-red-500">*</span></label>
                    <div class="relative w-full h-40 rounded-2xl border-2 border-dashed flex flex-col items-center justify-center gap-2 overflow-hidden bg-slate-50 border-slate-300 hover:border-blue-400 transition-all cursor-pointer"
                         :class="hasFile('file_surat_buta_warna') ? '!border-green-500 !bg-green-50' : ''"
                         onclick="document.getElementById('file_surat_buta_warna').click()">
                        <input type="file" name="file_surat_buta_warna" id="file_surat_buta_warna" class="hidden" accept=".pdf,.jpg,.jpeg,.png" required x-on:change="handleFileChange('file_surat_buta_warna')">
                        <i class="fas fa-eye text-2xl text-slate-400 mb-2" x-show="!hasFile('file_surat_buta_warna')"></i>
                        <p class="text-sm font-bold text-slate-600" x-show="!hasFile('file_surat_buta_warna')">Upload Surat</p>
                        <i class="fas fa-check-circle text-2xl text-green-500 mb-2" x-show="hasFile('file_surat_buta_warna')" style="display:none"></i>
                        <div id="file_surat_buta_warna_name" class="w-full text-xs font-bold text-green-700 truncate px-4" x-show="hasFile('file_surat_buta_warna')"></div>
                    </div>
                </div>
            </div>
        @endif

    </form>

    {{-- E. TOMBOL AKSI --}}
    <div class="flex flex-col-reverse md:flex-row md:justify-between items-center gap-4 mt-12 pt-8 border-t border-indigo-100/50">
        <button type="button" x-on:click="previousStep()" class="group w-full md:w-auto px-6 py-3.5 rounded-xl border border-white bg-white/50 text-slate-500 font-bold uppercase tracking-widest hover:bg-white hover:text-indigo-600 hover:shadow-md transition-all duration-300 flex justify-center items-center gap-2">
            <i class="fas fa-arrow-left transition-transform group-hover:-translate-x-1"></i> Sebelumnya
        </button>

        <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
            <button type="button" x-on:click="scanAndValidate()" class="group relative w-full md:w-auto px-6 py-3.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 text-white font-bold uppercase tracking-widest hover:shadow-[0_0_20px_rgba(16,185,129,0.4)] transition-all duration-300 flex justify-center items-center gap-2 overflow-hidden" :disabled="scanning">
                <div class="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/20 to-transparent group-hover:animate-shimmer pointer-events-none"></div>
                <span x-show="!scanning" class="flex items-center gap-2 relative z-10"><i class="fas fa-search-plus"></i> Scan & Validasi</span>
                <span x-show="scanning" class="flex items-center gap-2 relative z-10"><i class="fas fa-circle-notch fa-spin"></i> Memproses...</span>
            </button>

            <button type="button" x-on:click="submitStep3()" class="group relative w-full md:w-auto px-9 py-4 rounded-xl bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 text-white font-extrabold uppercase tracking-widest hover:shadow-[0_0_25px_rgba(79,70,229,0.5)] transition-all duration-500 flex justify-center items-center gap-3 overflow-hidden" :disabled="loading || !canProceedToNextStep()">
                <div class="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/20 to-transparent group-hover:animate-shimmer pointer-events-none"></div>
                <span x-text="loading ? 'Menyimpan...' : 'Simpan & Lanjutkan'" class="relative z-10"></span>
                <span x-show="!loading" class="relative z-10 bg-white/20 p-1.5 rounded-lg group-hover:bg-white/30 transition-colors">
                    <i class="fas fa-arrow-right transition-transform group-hover:translate-x-1"></i>
                </span>
            </button>
        </div>
    </div>
    @include('mahasiswa.register.steps.partials.truck-loader')
    
    </div> <!-- End of glass container -->
</div>

    {{-- SCRIPT KHUSUS UNTUK GENERATE HTML BERKAS DINAMIS (Manual String) --}}
    <script>
    // Fungsi ini menggantikan component blade untuk JS injection
    function createModernUploadBox(id, label, icon) {
        return `
        <div class="group relative" x-data>
            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-2 ml-1">
                ${label} <span class="text-red-500">*</span>
            </label>
            <div class="relative w-full h-40 rounded-2xl border-2 border-dashed border-slate-300 bg-white/50 hover:bg-blue-50/50 hover:border-blue-400 transition-all duration-300 cursor-pointer flex flex-col items-center justify-center gap-2 group-hover:shadow-md"
                 onclick="document.getElementById('${id}').click()"
                 :class="{'!border-green-500 !bg-green-50/40 ring-4 ring-green-50': hasFile('${id}')}">
                 
                <input type="file" name="${id}" id="${id}" class="hidden" accept=".pdf,.jpg,.jpeg,.png" required 
                       x-on:change="handleFileChange('${id}')">
                
                <div x-show="!hasFile('${id}')" class="text-center pointer-events-none transition-opacity duration-300">
                    <div class="h-12 w-12 mx-auto rounded-2xl bg-white shadow-sm border border-slate-100 flex items-center justify-center mb-3 text-slate-400 group-hover:text-blue-500 group-hover:scale-110 transition-all duration-300">
                        <i class="fas ${icon} text-xl"></i>
                    </div>
                    <p class="text-xs font-bold text-slate-600 group-hover:text-blue-600 transition-colors">Klik untuk Upload</p>
                    <p class="text-[10px] text-slate-400 mt-0.5">PDF/JPG (Max 2MB)</p>
                </div>

                <div x-show="hasFile('${id}')" class="text-center pointer-events-none transition-all duration-300 transform scale-100" style="display:none;" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100">
                    <div class="h-12 w-12 mx-auto rounded-full bg-green-500 text-white flex items-center justify-center mb-2 shadow-lg shadow-green-500/30 animate-bounce-short">
                        <i class="fas fa-check text-lg"></i>
                    </div>
                    <p class="text-xs font-bold text-green-700 px-4 line-clamp-1 break-all" id="${id}_name"></p>
                    <span class="inline-block mt-2 px-2 py-0.5 rounded text-[9px] font-bold bg-green-100 text-green-700 uppercase tracking-wide">Siap Scan</span>
                </div>
            </div>
        </div>`;
    }
    </script>

<!-- <button type="button" 
        x-on:click="simpleScan()" 
        class="bg-green-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 transition-colors duration-300">
    <i class="fas fa-search mr-2"></i>
    Test Scan
</button> -->
    
<script>
function registrationApp() {
    // Langsung return object, jangan ada fungsi di sini
    return {
        // --- PROPERTIES ---
        jurusan1: '{{ $registration->jurusan1 }}',
        jurusan2: '{{ $registration->jurusan2 }}',
        
        currentStep: {{ $registration->step_completed + 1 }},
        loading: false,
        scanning: false,
        scanStatus: null,
        
        // --- INIT ---
        init() {
            console.log('Registration app initialized, current step:', this.currentStep);
            this.scanStatus = {
                scan_status: 'not_scanned',
                scan_results: {},
                jalur_pendaftaran: '{{ $registration->jalur_pendaftaran }}'
            };
            
            this.checkScanStatus();
            // this.loadSpecificFiles();
            this.attachFileChangeListeners();
        },

        // --- HELPER (PINDAHKAN KE SINI) ---
        hasFile(inputId) {
            if (!inputId) return false;
            const input = document.getElementById(inputId);
            return input && input.files && input.files.length > 0;
        },

        // --- FUNCTIONS LAINNYA ---
        async simpleScan() {
            console.log('Simple scan clicked');
            try {
                const response = await fetch('/mahasiswa/scanner/test-connection');
                const data = await response.json();
                console.log('Test connection:', data);
                alert('Connection: ' + (data.success ? 'Success' : 'Failed'));
            } catch (error) {
                console.error('Simple scan error:', error);
                alert('Error: ' + error.message);
            }
        },

        async testConnection() {
            try {
                const response = await fetch('/mahasiswa/scanner/test-connection', {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Koneksi Berhasil!',
                        text: 'Terhubung ke server scanner OCR dengan baik.',
                        timer: 3000,
                        showConfirmButton: false
                    });
                } else {
                    throw new Error(data.message || 'Koneksi gagal');
                }
            } catch (error) {
                console.error('Test connection error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Koneksi Gagal',
                    text: 'Tidak dapat terhubung ke server scanner: ' + error.message
                });
            }
        },
        
        async scanSingleFile(fileType) {
            const fileInput = document.getElementById(fileType);
            
            if (!fileInput.files || !fileInput.files[0]) {
                Swal.fire({
                    icon: 'warning',
                    title: 'File Belum Diupload',
                    text: 'Silakan upload file ' + this.getFileName(fileType) + ' terlebih dahulu.'
                });
                return;
            }
            
            this.scanning = true;
            
            try {
                const formData = new FormData();
                formData.append(fileType, fileInput.files[0]);
                formData.append('_token', '{{ csrf_token() }}');
                
                const response = await fetch(`/mahasiswa/scanner/single/${fileType}`, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    if (!this.scanStatus.scan_results) {
                        this.scanStatus.scan_results = {};
                    }
                    this.scanStatus.scan_results[fileType] = data.scan_result;
                    
                    this.updateOverallScanStatus();
                    
                    Swal.fire({
                        icon: data.scan_result.is_valid ? 'success' : 'warning',
                        title: data.scan_result.is_valid ? 'Valid!' : 'Perlu Perbaikan',
                        html: `
                            <div class="text-left">
                                <p><strong>${this.getFileName(fileType)}</strong></p>
                                <p class="mt-2">${data.scan_result.message}</p>
                                ${data.scan_result.confidence ? 
                                    `<p class="mt-1">Confidence: ${Math.round(data.scan_result.confidence * 100)}%</p>` : ''}
                            </div>
                        `,
                        confirmButtonText: 'Mengerti'
                    });
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                console.error('Single file scan error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Scan Gagal',
                    text: error.message
                });
            } finally {
                this.scanning = false;
            }
        },
        
        updateOverallScanStatus() {
            if (!this.scanStatus.scan_results) return;
            
            const results = Object.values(this.scanStatus.scan_results);
            if (results.length === 0) return;
            
            const allValid = results.every(result => result.is_valid);
            const allScanned = results.length === this.getTotalRequiredFiles();
            
            if (allScanned && allValid) {
                this.scanStatus.scan_status = 'completed';
            } else if (allScanned && !allValid) {
                this.scanStatus.scan_status = 'partial_failed';
            } else {
                this.scanStatus.scan_status = 'scanning';
            }
        },
        
        getTotalRequiredFiles() {
            const jalur = '{{ $registration->jalur_pendaftaran }}';
            const baseFiles = 2; // KTP + KK
            let total = baseFiles;
            
            const jalurSpecificCount = {
                'Jalur Reguler': 1,
                'Jalur Ekstensi': 2,
                'Jalur S2 Manajemen': 2,
                'Pindahan': 3,
                'Jalur RPL': 3
            };
            
            total += (jalurSpecificCount[jalur] || 0);

            const isFarmasi = (this.jurusan1 && this.jurusan1.toLowerCase().includes('farmasi')) || 
                              (this.jurusan2 && this.jurusan2.toLowerCase().includes('farmasi'));

            if (isFarmasi) {
                total += 1;
            }
            
            return total;
        },
        
        async scanAndValidate() {
            if (this.scanning) return;
            
            this.scanning = true;
            
            try {
                console.log('Scanning files for validation...');
                
                if (!this.hasUploadedFiles()) {
                    throw new Error('Silakan upload semua berkas yang diperlukan terlebih dahulu');
                }
                
                const scanSuccess = await this.scanAllFiles();
                
                if (scanSuccess) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Validasi Berhasil!',
                        text: 'Semua berkas telah divalidasi. Silakan lanjutkan upload jika semua berkas valid.',
                        timer: 3000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Perhatian!',
                        html: `
                            <p>Beberapa berkas perlu perbaikan:</p>
                            <div class="text-left mt-2 max-h-40 overflow-y-auto">
                                ${Object.entries(this.scanStatus.scan_results)
                                    .filter(([_, result]) => !result.is_valid)
                                    .map(([fileType, result]) => 
                                        `<div class="flex justify-between py-1 border-b">
                                            <span>${this.getFileName(fileType)}</span>
                                            <span class="text-red-600">${result.message}</span>
                                        </div>`
                                    ).join('')}
                            </div>
                        `,
                        confirmButtonText: 'Mengerti'
                    });
                }
                
            } catch (error) {
                console.error('Scan and validate error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    text: error.message
                });
            } finally {
                this.scanning = false;
            }
        },
        
        async scanAllFiles() {
            try {
                console.log('Scanning all uploaded files...');
                
                const form = document.getElementById('step3Form');
                const formData = new FormData(form);
                
                const response = await fetch('/mahasiswa/scanner/scan-temp', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: formData
                });
                
                const data = await response.json();
                console.log('Scan response:', data);
                
                if (data.success) {
                    this.scanStatus = {
                        scan_status: data.scan_status,
                        scan_results: data.scan_results,
                        jalur_pendaftaran: data.jalur_pendaftaran,
                        scan_timestamp: data.scan_timestamp
                    };
                    
                    return data.scan_status === 'completed';
                } else {
                    throw new Error(data.message || 'Gagal memproses scan');
                }
                
            } catch (error) {
                console.error('Scan all files error:', error);
                throw error;
            }
        },
        
        handleFileChange(fileType) {
            const input = document.getElementById(fileType);
            const displayElement = document.getElementById(fileType + '_name');
            
            if (input.files && input.files[0]) {
                const fileName = input.files[0].name;
                if (displayElement) {
                    displayElement.textContent = fileName;
                }
                if (['file_ijazah', 'file_skl', 'file_rapor'].includes(fileType)) {
                    window.updateRegulerUI(fileName);
                }
                
                console.log('File changed:', fileType, fileName);
                
                this.scanStatus = {
                    scan_status: 'not_scanned',
                    scan_results: {},
                    jalur_pendaftaran: '{{ $registration->jalur_pendaftaran }}'
                };

                this.$nextTick(() => {}); 
            }
        },
        
        async checkScanStatus() {
            try {
                console.log('Checking scan status...');
                
                const response = await fetch('/mahasiswa/scanner/status', {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                });
                
                const data = await response.json();
                console.log('Scan status response:', data);
                
                if (data.success) {
                    this.scanStatus = data;
                } else {
                    console.error('Failed to get scan status:', data.message);
                    this.scanStatus = {
                        scan_status: 'not_scanned',
                        scan_results: {},
                        jalur_pendaftaran: '{{ $registration->jalur_pendaftaran }}'
                    };
                }
            } catch (error) {
                console.error('Error checking scan status:', error);
                this.scanStatus = {
                    scan_status: 'not_scanned',
                    scan_results: {},
                    jalur_pendaftaran: '{{ $registration->jalur_pendaftaran }}'
                };
            }
        },
        
        hasUploadedFiles() {
            const form = document.getElementById('step3Form');
            if (!form) return false;
            
            const fileInputs = form.querySelectorAll('input[type="file"][required]');
            
            let allFilesUploaded = true;
            
            fileInputs.forEach(input => {
                if (!input.files || input.files.length === 0) {
                    allFilesUploaded = false;
                }
            });
            
            return allFilesUploaded;
        },
        
        getStatusText(status) {
            const statusMap = {
                'not_scanned': 'Belum divalidasi',
                'scanning': 'Sedang memvalidasi...',
                'completed': 'Semua berkas valid',
                'partial_failed': 'Beberapa berkas perlu perbaikan',
                'failed': 'Validasi gagal',
                'error': 'Error'
            };
            return statusMap[status] || status;
        },
        
        formatDate(dateString) {
            if (!dateString) return '';
            try {
                const date = new Date(dateString);
                return date.toLocaleString('id-ID');
            } catch (e) {
                return dateString;
            }
        },
        
        getFileName(fileType) {
            const fileNames = {
                'file_ktp': 'KTP',
                'file_kk': 'Kartu Keluarga',
                'file_ijazah': 'Ijazah / SKL / Raport Sem. Akhir',
                'file_transkrip_nilai': 'Transkrip Nilai',
                'file_ijazah_s1': 'Ijazah S1',
                'file_transkrip_s1': 'Transkrip S1',
                'file_surat_alih_jenjang': 'Surat Alih Jenjang',
                'file_transkrip_nilai_rpl': 'Transkrip Nilai RPL',
                'file_surat_akademik_ekstensi': 'Transkrip Nilai (Ekstensi)',
                'file_surat_pindah': 'Surat Pindah',
                'file_surat_akademik_pindahan': 'Surat Akademik Pindahan',
                'file_ktm': 'Kartu Tanda Mahasiswa (KTM)',
                'file_surat_buta_warna': 'Surat Ket. Buta Warna',
                'file_surat_tugas_kerja': 'Surat Tugas Kerja'
            };
            return fileNames[fileType] || fileType.replace('file_', '').replace(/_/g, ' ');
        },
        
        loadSpecificFiles() {
            // ... (Paste kembali kode loadSpecificFiles lengkap yang saya berikan sebelumnya di sini) ...
            // Agar tidak error, saya tulis ulang singkatnya di sini:
            const jalur = '{{ $registration->jalur_pendaftaran }}';
            const section = document.getElementById('specificFilesSection');
            
            if (!section) return;
            if (!jalur) {
                section.innerHTML = '<p>Pilih jalur dulu.</p>';
                return;
            }
            
            // === TEMPELKAN TEMPLATE HTML PANJANG DISINI ===
            const templates = {
                'Jalur Reguler': `
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Berkas Khusus Jalur Reguler</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="file-upload-container">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Ijazah / SKL / Raport Sem. Akhir *</label>
                            <div class="file-upload-area border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-blue-500 transition-colors"
                                onclick="document.getElementById('file_ijazah').click()">
                                <input type="file" name="file_ijazah" id="file_ijazah" class="hidden" accept=".pdf,.jpg,.jpeg,.png" required
                                    x-on:change="handleFileChange('file_ijazah')">
                                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                <p class="text-gray-600">Klik untuk upload Ijazah/SKL/Raport</p>
                                <p class="text-sm text-gray-500 mt-1">Format: PDF, JPG, PNG, Maks: 2MB</p>
                                <span id="file_ijazah_name" class="text-blue-600 text-sm mt-2 block"></span>
                            </div>
                        </div>
                    </div>
                `,
                'Jalur Ekstensi': `
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Berkas Khusus Jalur Ekstensi</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <div class="file-upload-container">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Ijazah (D3/Sederajat) *</label>
                            <div class="file-upload-area border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-blue-500 transition-colors"
                                onclick="document.getElementById('file_ijazah').click()"
                                :class="{'border-green-500 bg-green-50': hasFile('file_ijazah')}">
                                
                                <input type="file" name="file_ijazah" id="file_ijazah" class="hidden" accept=".pdf,.jpg,.jpeg,.png" required
                                    x-on:change="handleFileChange('file_ijazah')">
                                    
                                <i class="fas fa-certificate text-3xl text-gray-400 mb-2" :class="{'text-green-500': hasFile('file_ijazah')}"></i>
                                <p class="text-gray-600">Klik untuk upload Ijazah</p>
                                <p class="text-sm text-gray-500 mt-1">Format: PDF, JPG, PNG, Maks: 2MB</p>
                                <span id="file_ijazah_name" class="text-blue-600 text-sm mt-2 block font-medium"></span>
                            </div>
                        </div>

                        
                        <div class="file-upload-container">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Transkrip Nilai Legalisir *</label>
                            <div class="file-upload-area border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-blue-500 transition-colors"
                                onclick="document.getElementById('file_transkrip_s1').click()"
                                :class="{'border-green-500 bg-green-50': hasFile('file_transkrip_s1')}">
                                
                                <input type="file" name="file_transkrip_s1" id="file_transkrip_s1" class="hidden" accept=".pdf,.jpg,.jpeg,.png" required
                                    x-on:change="handleFileChange('file_transkrip_s1')">
                                    
                                <i class="fas fa-file-alt text-3xl text-gray-400 mb-2" :class="{'text-green-500': hasFile('file_transkrip_s1')}"></i>
                                <p class="text-gray-600">Klik untuk upload Transkrip Legalisir</p>
                                <p class="text-sm text-gray-500 mt-1">Format: PDF, JPG, PNG, Maks: 2MB</p>
                                <span id="file_transkrip_s1_name" class="text-blue-600 text-sm mt-2 block font-medium"></span>
                            </div>
                        </div>
                        
                    </div>
                `,
                'Jalur S2 Manajemen': `
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Berkas Khusus Jalur S2 Manajemen</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="file-upload-container">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Ijazah S1 *</label>
                            <div class="file-upload-area border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-blue-500 transition-colors"
                                 onclick="document.getElementById('file_ijazah_s1').click()">
                                <input type="file" name="file_ijazah_s1" id="file_ijazah_s1" class="hidden" accept=".pdf,.jpg,.jpeg,.png" required
                                       x-on:change="handleFileChange('file_ijazah_s1')">
                                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                <p class="text-gray-600">Klik untuk upload Ijazah S1</p>
                                <p class="text-sm text-gray-500 mt-1">Format: PDF, JPG, PNG, Maks: 2MB</p>
                                <span id="file_ijazah_s1_name" class="text-blue-600 text-sm mt-2 block"></span>
                            </div>
                        </div>
                        <div class="file-upload-container">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Transkrip S1 *</label>
                            <div class="file-upload-area border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-blue-500 transition-colors"
                                 onclick="document.getElementById('file_transkrip_s1').click()">
                                <input type="file" name="file_transkrip_s1" id="file_transkrip_s1" class="hidden" accept=".pdf,.jpg,.jpeg,.png" required
                                       x-on:change="handleFileChange('file_transkrip_s1')">
                                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                <p class="text-gray-600">Klik untuk upload Transkrip S1</p>
                                <p class="text-sm text-gray-500 mt-1">Format: PDF, JPG, PNG, Maks: 2MB</p>
                                <span id="file_transkrip_s1_name" class="text-blue-600 text-sm mt-2 block"></span>
                            </div>
                        </div>
                    </div>
                `,
                'Pindahan': `
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Berkas Khusus Jalur Pindahan</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <div class="file-upload-container">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Surat Pindahan *</label>
                            <div class="file-upload-area border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-blue-500 transition-colors"
                                onclick="document.getElementById('file_surat_pindah').click()"
                                :class="{'border-green-500 bg-green-50': hasFile('file_surat_pindah')}">
                                
                                <input type="file" name="file_surat_pindah" id="file_surat_pindah" class="hidden" accept=".pdf,.jpg,.jpeg,.png" required
                                    x-on:change="handleFileChange('file_surat_pindah')">
                                    
                                <i class="fas fa-envelope-open-text text-3xl text-gray-400 mb-2" :class="{'text-green-500': hasFile('file_surat_pindah')}"></i>
                                <p class="text-gray-600">Klik untuk upload Surat Pindahan</p>
                                <p class="text-sm text-gray-500 mt-1">Format: PDF, JPG, PNG, Maks: 2MB</p>
                                <span id="file_surat_pindah_name" class="text-blue-600 text-sm mt-2 block font-medium"></span>
                            </div>
                        </div>

                        <div class="file-upload-container">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Transkrip Nilai *</label>
                            <div class="file-upload-area border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-blue-500 transition-colors"
                                onclick="document.getElementById('file_transkrip_nilai').click()"
                                :class="{'border-green-500 bg-green-50': hasFile('file_transkrip_nilai')}">
                                
                                <input type="file" name="file_transkrip_nilai" id="file_transkrip_nilai" class="hidden" accept=".pdf,.jpg,.jpeg,.png" required
                                    x-on:change="handleFileChange('file_transkrip_nilai')">
                                    
                                <i class="fas fa-list-ol text-3xl text-gray-400 mb-2" :class="{'text-green-500': hasFile('file_transkrip_nilai')}"></i>
                                <p class="text-gray-600">Klik untuk upload Transkrip Nilai</p>
                                <p class="text-sm text-gray-500 mt-1">Format: PDF, JPG, PNG, Maks: 2MB</p>
                                <span id="file_transkrip_nilai_name" class="text-blue-600 text-sm mt-2 block font-medium"></span>
                            </div>
                        </div>

                        <div class="file-upload-container">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kartu Tanda Mahasiswa (KTM) *</label>
                            <div class="file-upload-area border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-blue-500 transition-colors"
                                onclick="document.getElementById('file_ktm').click()"
                                :class="{'border-green-500 bg-green-50': hasFile('file_ktm')}">
                                
                                <input type="file" name="file_ktm" id="file_ktm" class="hidden" accept=".pdf,.jpg,.jpeg,.png" required
                                    x-on:change="handleFileChange('file_ktm')">
                                    
                                <i class="fas fa-id-card text-3xl text-gray-400 mb-2" :class="{'text-green-500': hasFile('file_ktm')}"></i>
                                <p class="text-gray-600">Klik untuk upload KTM Asal</p>
                                <p class="text-sm text-gray-500 mt-1">Format: PDF, JPG, PNG, Maks: 2MB</p>
                                <span id="file_ktm_name" class="text-blue-600 text-sm mt-2 block font-medium"></span>
                            </div>
                        </div>
                        
                        {{-- File 4: IJAZAH SMA/SMK (BARU DITAMBAHKAN) --}}
                        <div class="group relative">
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-2 ml-1">Ijazah SMA/SMK <span class="text-red-500">*</span></label>
                            <div class="relative w-full h-40 rounded-2xl border-2 border-dashed border-slate-300 bg-white/50 hover:bg-blue-50/50 hover:border-blue-400 transition-all duration-300 cursor-pointer flex flex-col items-center justify-center gap-2 group-hover:shadow-md"
                                 onclick="document.getElementById('file_ijazah').click()"
                                 :class="{'!border-green-500 !bg-green-50/40 ring-4 ring-green-50': hasFile('file_ijazah')}">
                                
                                <input type="file" name="file_ijazah" id="file_ijazah" class="hidden" accept=".pdf,.jpg,.jpeg,.png" required x-on:change="handleFileChange('file_ijazah')">
                                
                                <div x-show="!hasFile('file_ijazah')" class="text-center pointer-events-none transition-opacity duration-300">
                                    <div class="h-12 w-12 mx-auto rounded-2xl bg-white shadow-sm border border-slate-100 flex items-center justify-center mb-3 text-slate-400 group-hover:text-blue-500 group-hover:scale-110 transition-all duration-300">
                                        <i class="fas fa-certificate text-xl"></i>
                                    </div>
                                    <p class="text-xs font-bold text-slate-600 group-hover:text-blue-600 transition-colors">Klik untuk Upload</p>
                                </div>
                                
                                <div x-show="hasFile('file_ijazah')" class="text-center pointer-events-none" style="display:none;">
                                    <div class="h-12 w-12 mx-auto rounded-full bg-green-500 text-white flex items-center justify-center mb-2 shadow-lg">
                                        <i class="fas fa-check text-lg"></i>
                                    </div>
                                    <p class="text-xs font-bold text-green-700 px-4 line-clamp-1 break-all" id="file_ijazah_name"></p>
                                </div>
                            </div>
                        </div>

                    </div>
                `,
                'Jalur RPL': `
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Berkas Khusus Jalur RPL</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="file-upload-container">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Surat Tugas Kerja *</label>
                            <div class="file-upload-area border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-blue-500 transition-colors"
                                 onclick="document.getElementById('file_surat_tugas_kerja').click()">
                                <input type="file" name="file_surat_tugas_kerja" id="file_surat_tugas_kerja" class="hidden" accept=".pdf,.jpg,.jpeg,.png" required
                                       x-on:change="handleFileChange('file_surat_tugas_kerja')">
                                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                <p class="text-gray-600">Klik untuk upload Surat Tugas Kerja</p>
                                <p class="text-sm text-gray-500 mt-1">Format: PDF, JPG, PNG, Maks: 2MB</p>
                                <span id="file_surat_tugas_kerja_name" class="text-blue-600 text-sm mt-2 block"></span>
                            </div>
                        </div>
                        <div class="file-upload-container">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Transkrip Nilai RPL *</label>
                            <div class="file-upload-area border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-blue-500 transition-colors"
                                 onclick="document.getElementById('file_transkrip_nilai_rpl').click()">
                                <input type="file" name="file_transkrip_nilai_rpl" id="file_transkrip_nilai_rpl" class="hidden" accept=".pdf,.jpg,.jpeg,.png" required
                                       x-on:change="handleFileChange('file_transkrip_nilai_rpl')">
                                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                <p class="text-gray-600">Klik untuk upload Transkrip Nilai RPL</p>
                                <p class="text-sm text-gray-500 mt-1">Format: PDF, JPG, PNG, Maks: 2MB</p>
                                <span id="file_transkrip_nilai_rpl_name" class="text-blue-600 text-sm mt-2 block"></span>
                            </div>
                        </div>
                        <div class="file-upload-container">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Ijazah *</label>
                            <div class="file-upload-area border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-blue-500 transition-colors"
                                 onclick="document.getElementById('file_ijazah').click()">
                                <input type="file" name="file_ijazah" id="file_ijazah" class="hidden" accept=".pdf,.jpg,.jpeg,.png" required
                                       x-on:change="handleFileChange('file_ijazah')">
                                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                <p class="text-gray-600">Klik untuk upload Ijazah</p>
                                <p class="text-sm text-gray-500 mt-1">Format: PDF, JPG, PNG, Maks: 2MB</p>
                                <span id="file_ijazah_name" class="text-blue-600 text-sm mt-2 block"></span>
                            </div>
                        </div>
                    </div>
                `
            };
       
            section.innerHTML = templates[jalur] || '<p>Template tidak ditemukan.</p>';
            const kodeFarmasi = ['48201']; 
            const isFarmasi = kodeFarmasi.includes(this.jurusan1) || kodeFarmasi.includes(this.jurusan2);

            if (isFarmasi) {
                const gridContainer = section.querySelector('.grid');
                if (gridContainer) {
                    const farmasiHtml = `
                        <div class="file-upload-container mt-4 pt-4 border-t-2 border-dashed border-gray-100 col-span-1 md:col-span-2">
                            <div class="flex items-center mb-3">
                                <label class="block text-sm font-bold text-gray-800 mr-2">
                                    <i class="fas fa-eye text-blue-600 mr-1"></i> Surat Keterangan Buta Warna *
                                </label>
                                <span class="bg-blue-100 text-blue-800 text-xs font-bold px-2 py-0.5 rounded-full border border-blue-200">Wajib Farmasi</span>
                            </div>
                            <div class="file-upload-area border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-all duration-300"
                                 onclick="document.getElementById('file_surat_buta_warna').click()"
                                 :class="{'border-green-500 bg-green-50': hasFile('file_surat_buta_warna')}">
                                <input type="file" name="file_surat_buta_warna" id="file_surat_buta_warna" class="hidden" accept=".pdf,.jpg,.jpeg,.png" required x-on:change="handleFileChange('file_surat_buta_warna')">
                                <div x-show="!hasFile('file_surat_buta_warna')">
                                    <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                    <p class="text-gray-600 font-medium">Klik untuk upload Surat</p>
                                </div>
                                <div x-show="hasFile('file_surat_buta_warna')">
                                    <i class="fas fa-check-circle text-4xl text-green-500 mb-2"></i>
                                    <span id="file_surat_buta_warna_name" class="text-green-700 text-sm mt-2 block font-bold break-all"></span>
                                    <p class="text-xs text-green-600 mt-1">File terpilih</p>
                                </div>
                            </div>
                        </div>`;
                    gridContainer.insertAdjacentHTML('beforeend', farmasiHtml);
                }
            }
            
            this.attachFileChangeListeners();
        },
        
        attachFileChangeListeners() {
            // Alpine handles this usually, but keep empty function to avoid errors
        },
        
        canProceedToNextStep() {
            if (this.loading || this.scanning) return false;
            if (!this.hasUploadedFiles()) return false;
            // if (this.scanStatus.scan_status !== 'completed') return false;
            return true;
        },
        
        async submitStep3() {
            // Cek pra-kondisi dulu
            if (!this.canProceedToNextStep()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Belum Lengkap',
                    text: 'Harap upload semua berkas wajib.'
                });
                return;
            }
            
            this.loading = true;
            const form = document.getElementById('step3Form');
            const formData = new FormData(form);

            // <<< PERBAIKAN KRITIS DISINI >>>
            // 1. Lampirkan Hasil Scan (JSON string) dan Status
           
            formData.append('scan_results', JSON.stringify(this.scanStatus.scan_results));
            formData.append('scan_status', this.scanStatus.scan_status);
            // <<< AKHIR PERBAIKAN >>>

            try {
                const response = await fetch('{{ route("mahasiswa.registration.step3") }}', {
                    method: 'POST',
                    headers: { 
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        // Catatan: Jangan set Content-Type: application/json karena kita kirim FormData
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    Swal.fire({
                        icon: 'success', title: 'Berhasil!', text: data.message, 
                        timer: 2000, showConfirmButton: false
                    }).then(() => window.location.reload());
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                Swal.fire({ icon: 'error', title: 'Gagal', text: error.message });
            } finally {
                this.loading = false;
            }
        }
    };
}

    let currentActiveInput = 'file_ijazah';

    // Fungsi Switch Dokumen
    window.switchReguler = function(targetId) {
        currentActiveInput = targetId;
        
        // Update hidden input untuk backend
        document.getElementById('jenis_dokumen_reguler').value = targetId;

        // Daftar semua kemungkinan input
        const inputs = ['file_ijazah', 'file_skl', 'file_rapor'];
        
        // Reset Label di placeholder
        const labels = {
            'file_ijazah': 'Ijazah',
            'file_skl': 'SKL',
            'file_rapor': 'Rapor'
        };
        const labelEl = document.getElementById('label_dokumen_aktif');
        if(labelEl) labelEl.textContent = labels[targetId];

        inputs.forEach(id => {
            const el = document.getElementById(id);
            if(el) {
                if(id === targetId) {
                    // === JIKA INI INPUT YANG DIPILIH ===
                    el.setAttribute('required', 'required');
                    el.disabled = false;
                    
                    // Cek jika sudah ada file, update tampilan UI
                    if (el.files && el.files.length > 0) {
                        window.updateRegulerUI(el.files[0].name);
                    } else {
                        window.resetRegulerUI();
                    }
                } else {
                    el.removeAttribute('required');
                    el.disabled = true; 
                    el.value = ''; 
                }
            }
        });
    };

    // Fungsi Klik Dropzone
    window.triggerRegulerUpload = function() {
        const el = document.getElementById(currentActiveInput);
        if(el) el.click();
    };

    // Fungsi Update Tampilan Jika Ada File
    window.updateRegulerUI = function(fileName) {
        const placeholder = document.getElementById('reguler_placeholder');
        const success = document.getElementById('reguler_success');
        const dropzone = document.getElementById('dropzone_reguler');
        const nameDisplay = document.getElementById('reguler_filename_display');

        if (placeholder && success && dropzone && nameDisplay) {
            placeholder.classList.add('hidden');
            success.classList.remove('hidden');
            nameDisplay.textContent = fileName;
            dropzone.classList.add('!border-green-500', '!bg-green-50');
        }
    };

    // Fungsi Reset Tampilan Jika Kosong
    window.resetRegulerUI = function() {
        const placeholder = document.getElementById('reguler_placeholder');
        const success = document.getElementById('reguler_success');
        const dropzone = document.getElementById('dropzone_reguler');

        if (placeholder && success && dropzone) {
            placeholder.classList.remove('hidden');
            success.classList.add('hidden');
            dropzone.classList.remove('!border-green-500', '!bg-green-50');
        }
    };
</script>