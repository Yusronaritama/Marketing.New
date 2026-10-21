{{-- FILE: resources/views/mahasiswa/register/steps/step4.blade.php --}}

@php
    use App\Models\ProgramStudi;
    
    // Ambil Nama Jurusan
    $namaJurusan1 = $registration->programStudi1->nama_prodi ?? $registration->jurusan1 ?? '-';
    $namaJurusan2 = $registration->programStudi2->nama_prodi ?? $registration->jurusan2 ?? '-';

    // Daftar Mapping Label & Icon untuk Semua Kemungkinan File
    $fileLabels = [
        'file_ktp' => ['label' => 'KTP Asli', 'icon' => 'fa-id-card'],
        'file_kk' => ['label' => 'Kartu Keluarga', 'icon' => 'fa-users'],
        'file_surat_keterangan_sehat' => ['label' => 'Surat Ket. Sehat', 'icon' => 'fa-file-medical'],
        
        // Akademik Reguler
        'file_ijazah' => ['label' => 'Ijazah', 'icon' => 'fa-certificate'],
        'file_skl'    => ['label' => 'Surat Ket. Lulus (SKL)', 'icon' => 'fa-graduation-cap'],
        'file_rapor'  => ['label' => 'Rapor Semester Akhir', 'icon' => 'fa-book'],

        // Akademik Lainnya
        'file_transkrip_nilai' => ['label' => 'Transkrip Nilai', 'icon' => 'fa-list-ol'],
        'file_ijazah_s1' => ['label' => 'Ijazah S1', 'icon' => 'fa-graduation-cap'],
        'file_transkrip_s1' => ['label' => 'Transkrip S1', 'icon' => 'fa-file-alt'],
        'file_surat_pindah' => ['label' => 'Surat Pindah', 'icon' => 'fa-envelope-open-text'],
        'file_ktm' => ['label' => 'KTM Asal', 'icon' => 'fa-id-badge'],
        'file_surat_tugas_kerja' => ['label' => 'Surat Tugas', 'icon' => 'fa-briefcase'],
        'file_transkrip_nilai_rpl' => ['label' => 'Transkrip RPL', 'icon' => 'fa-file-invoice'],
        'file_surat_akademik_rpl' => ['label' => 'Surat Akademik RPL', 'icon' => 'fa-file-signature'],
        'file_surat_buta_warna' => ['label' => 'Surat Buta Warna', 'icon' => 'fa-eye'],
    ];

    // Filter hanya file yang diisi (tidak null) di database
    $uploadedFiles = [];
    foreach ($registration->getAttributes() as $key => $value) {
        // Cek jika key berawalan file_ DAN isinya tidak kosong
        if (str_starts_with($key, 'file_') && !empty($value)) {
            $uploadedFiles[$key] = $value;
        }
    }
@endphp

<div x-show="currentStep === 4" class="animate-fade-in-up relative" style="display: none;">
    <!-- Decorative background glow for the card -->
    <div class="absolute -inset-1 bg-gradient-to-r from-blue-100 to-purple-100 rounded-[2.5rem] blur-xl opacity-50 pointer-events-none"></div>

    <div class="relative bg-white/80 backdrop-blur-xl rounded-[2rem] shadow-xl border border-white p-6 sm:p-10">

    {{-- A. HEADER SECTION --}}
    <div class="flex items-center gap-4 mb-8 pb-6 border-b border-indigo-100/50 relative">
        <!-- Animated Icon Container -->
        <div class="relative h-14 w-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white shadow-[0_8px_16px_-6px_rgba(16,185,129,0.5)] overflow-hidden group-hover:scale-105 transition-transform">
            <div class="absolute inset-0 bg-white/20 blur-sm translate-y-full group-hover:translate-y-0 transition-transform duration-500"></div>
            <i class="fas fa-clipboard-check text-xl relative z-10"></i>
        </div>
        <div>
            <h2 class="text-2xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-slate-800 to-indigo-800 tracking-tight">Validasi Data</h2>
            <p class="text-sm font-medium text-slate-500 mt-0.5">Cek kembali data Anda sebelum melakukan finalisasi.</p>
        </div>
    </div>

    {{-- B. SUCCESS BANNER --}}
    <div class="relative overflow-hidden rounded-2xl bg-emerald-50/60 border border-emerald-100 p-5 mb-8 shadow-sm backdrop-blur-sm">
        <div class="flex items-start gap-4 relative z-10">
            <div class="shrink-0 mt-1">
                <div class="h-8 w-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 shadow-sm">
                    <i class="fas fa-thumbs-up"></i>
                </div>
            </div>
            <div>
                <h3 class="text-sm font-bold text-emerald-800 uppercase tracking-wide mb-1">Siap untuk Finalisasi!</h3>
                <p class="text-xs text-emerald-700/80 leading-relaxed font-medium">
                    Pastikan seluruh data benar. <span class="font-bold underline">Data akan dikunci permanen</span> setelah Anda menekan tombol "Selesaikan Pendaftaran".
                </p>
            </div>
        </div>
        <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 bg-emerald-200/30 rounded-full blur-2xl"></div>
    </div>

    {{-- C. RINGKASAN DATA --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        
        {{-- Card 1: Data Pribadi --}}
        <div class="rounded-2xl bg-white/50 border border-white/80 shadow-[inset_0_2px_4px_rgba(0,0,0,0.02)] hover:shadow-md transition-shadow p-6 backdrop-blur-sm">
            <div class="flex items-center gap-2 mb-4 border-b border-slate-200/50 pb-3">
                <i class="fas fa-user-circle text-blue-500"></i>
                <h4 class="font-bold text-slate-700 text-xs uppercase">Identitas Diri</h4>
            </div>
            <ul class="space-y-3 text-sm">
                <li>
                    <span class="block text-[10px] text-slate-400 font-bold uppercase">Nama</span>
                    <span class="font-bold text-slate-800">{{ $registration->nama }}</span>
                </li>
                <li>
                    <span class="block text-[10px] text-slate-400 font-bold uppercase">Email</span>
                    <span class="font-medium text-slate-700 truncate">{{ $registration->gmail }}</span>
                </li>
                <li>
                    <span class="block text-[10px] text-slate-400 font-bold uppercase">Telepon</span>
                    <span class="font-medium text-slate-700">{{ $registration->phone }}</span>
                </li>
            </ul>
        </div>

        {{-- Card 2: Pilihan Jurusan --}}
        <div class="rounded-2xl bg-white/50 border border-white/80 shadow-[inset_0_2px_4px_rgba(0,0,0,0.02)] hover:shadow-md transition-shadow p-6 backdrop-blur-sm">
            <div class="flex items-center gap-2 mb-4 border-b border-slate-200/50 pb-3">
                <i class="fas fa-graduation-cap text-indigo-500"></i>
                <h4 class="font-bold text-slate-700 text-xs uppercase">Program Studi</h4>
            </div>
            <ul class="space-y-3 text-sm">
                <li>
                    <span class="block text-[10px] text-slate-400 font-bold uppercase">Jalur</span>
                    <span class="inline-block px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 text-xs font-bold border border-indigo-100">
                        {{ $registration->jalur_pendaftaran }}
                    </span>
                </li>
                <li>
                    <span class="block text-[10px] text-slate-400 font-bold uppercase">Pilihan 1</span>
                    <span class="font-bold text-slate-800">{{ $namaJurusan1 }}</span>
                </li>
                <li>
                    <span class="block text-[10px] text-slate-400 font-bold uppercase">Pilihan 2</span>
                    <span class="font-medium text-slate-600">
                        {{ $registration->jurusan2 ? $namaJurusan2 : '- Tidak Memilih -' }}
                    </span>
                </li>
            </ul>
        </div>
    </div>

    {{-- D. STATUS BERKAS (OTOMATIS & LENGKAP) --}}
    <div class="mb-10">
        <div class="flex items-center justify-between mb-4 pl-1">
            <h4 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Status Dokumen</h4>
            <span class="text-[10px] bg-emerald-100 text-emerald-700 px-2 py-1 rounded-full font-bold border border-emerald-200">
                <i class="fas fa-check mr-1"></i> {{ count($uploadedFiles) }} Berkas Lengkap
            </span>
        </div>
        
        {{-- Grid Otomatis: Menyesuaikan jumlah file --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            
            @foreach($uploadedFiles as $key => $path)
                @php
                    // Default fallback jika file tidak ada di mapping
                    $info = $fileLabels[$key] ?? ['label' => ucwords(str_replace('_', ' ', str_replace('file_', '', $key))), 'icon' => 'fa-file'];
                @endphp

                <div class="flex items-center justify-between p-3 bg-white/50 backdrop-blur-sm border border-emerald-200/50 rounded-xl shadow-[inset_0_2px_4px_rgba(0,0,0,0.02)] hover:shadow-md transition-all group">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <div class="h-10 w-10 shrink-0 rounded-full bg-green-50 flex items-center justify-center text-green-600 border border-green-100 group-hover:bg-green-100 transition-colors">
                            <i class="fas {{ $info['icon'] }} text-sm"></i>
                        </div>
                        <div class="flex flex-col overflow-hidden">
                            <span class="text-xs font-bold text-slate-700 truncate" title="{{ $info['label'] }}">
                                {{ $info['label'] }}
                            </span>
                            {{-- INDIKATOR STATUS BERHASIL --}}
                            <span class="text-[10px] font-bold text-green-600 flex items-center gap-1">
                                Tersimpan <i class="fas fa-check text-[9px]"></i>
                            </span>
                        </div>
                    </div>
                    <div class="shrink-0 pl-2">
                        <div class="h-6 w-6 rounded-full bg-green-500 flex items-center justify-center text-white shadow-sm shadow-green-200">
                            <i class="fas fa-check text-[10px]"></i>
                        </div>
                    </div>
                </div>
            @endforeach

        </div>
        
        @if(empty($uploadedFiles))
            <div class="p-4 rounded-xl bg-red-50 border border-red-100 text-center text-xs text-red-600 font-medium">
                <i class="fas fa-exclamation-circle mr-1"></i> Belum ada dokumen yang diunggah. Silakan kembali ke tahap sebelumnya.
            </div>
        @endif
    </div>

    {{-- E. TOMBOL AKSI --}}
    <div class="flex flex-col-reverse md:flex-row md:justify-between items-center gap-4 mt-12 pt-8 border-t border-indigo-100/50">
        <button type="button" x-on:click="previousStep()" class="group w-full md:w-auto px-6 py-3.5 rounded-xl border border-white bg-white/50 text-slate-500 font-bold uppercase tracking-widest hover:bg-white hover:text-indigo-600 hover:shadow-md transition-all duration-300 flex justify-center items-center gap-2">
            <i class="fas fa-arrow-left transition-transform group-hover:-translate-x-1"></i> Kembali
        </button>

        <button type="button" x-on:click="completeRegistration()" 
                class="group relative w-full md:w-auto px-10 py-4 rounded-xl font-extrabold text-white uppercase tracking-widest transition-all duration-500 shadow-[0_0_20px_rgba(16,185,129,0.4)] bg-gradient-to-r from-emerald-500 to-teal-500 hover:shadow-[0_0_25px_rgba(16,185,129,0.6)] focus:ring-4 focus:ring-emerald-500/20 flex justify-center items-center gap-3 overflow-hidden disabled:opacity-70 disabled:cursor-not-allowed"
                :disabled="loading">
            <!-- Shimmer effect -->
            <div class="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/20 to-transparent group-hover:animate-shimmer pointer-events-none"></div>

            <span x-show="!loading" class="relative z-10 flex items-center gap-2">Selesaikan Pendaftaran 
                <span class="bg-white/20 p-1.5 rounded-lg group-hover:bg-white/30 transition-colors">
                    <i class="fas fa-check-circle transition-transform group-hover:scale-110"></i>
                </span>
            </span>
            <span x-show="loading" class="relative z-10 flex items-center gap-2"><i class="fas fa-circle-notch fa-spin"></i> Memproses...</span>
        </button>
    </div>

    </div> <!-- End of glass container -->
</div>