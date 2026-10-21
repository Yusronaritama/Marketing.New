@extends('layouts.mahasiswa.profesi')

@section('content')
<div class="min-h-screen bg-slate-100 py-10 px-4 sm:px-6 lg:px-8">
<div class="max-w-5xl mx-auto space-y-6">

    {{-- ── HEADER ── --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Ringkasan Data Anda</h2>
            <p class="text-sm text-slate-500 mt-0.5">Rekapitulasi seluruh data dan status pendaftaran profesi.</p>
        </div>
        <a href="{{ route('profesi.dashboard') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-50 shadow-sm transition-all self-start">
            <i class="fas fa-arrow-left text-xs"></i> Kembali ke Dashboard
        </a>
    </div>

    {{-- ── PROGRESS TIMELINE ── --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="h-1 bg-gradient-to-r from-amber-400 via-yellow-300 to-amber-400"></div>
        <div class="p-5">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Tahapan Seleksi</p>
            <div class="flex items-center gap-0">
                @php
                    $steps = [
                        ['label' => 'Berkas',     'icon' => 'fa-folder-open',
                         'done' => $berkas && $berkas->status_verifikasi == 'lulus',
                         'reject' => $berkas && $berkas->status_verifikasi == 'tolak',
                         'active' => $berkas],
                        ['label' => 'Ujian CBT',  'icon' => 'fa-laptop-code',
                         'done' => $isCbtSelesai && $statusKelulusan === 'LULUS',
                         'reject' => $isCbtSelesai && $statusKelulusan === 'TIDAK_LULUS',
                         'active' => $isCbtSelesai],
                       ['label' => 'Wawancara', 'icon' => 'fa-user-tie',
                         'done'   => $isWawancaraSelesai && in_array(strtolower($statusWawancara ?? ''), ['lulus','lolos']),
                         'reject' => $isWawancaraSelesai && !empty($statusWawancara) && !in_array(strtolower($statusWawancara ?? ''), ['lulus','lolos']),
                         'active' => $isWawancaraSelesai && empty($statusWawancara)],
                        ['label' => 'Pembayaran', 'icon' => 'fa-file-invoice-dollar',
                         'done' => isset($isBayarLunas) && $isBayarLunas,
                         'reject' => false,
                         'active' => isset($isBayarLunas)],
                        ['label' => 'Selesai',    'icon' => 'fa-graduation-cap',
                         'done' => false, 'reject' => false, 'active' => false],
                    ];
                @endphp
                @foreach($steps as $step)
                    <div class="flex flex-col items-center gap-1.5 flex-shrink-0">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 transition-all
                            {{ $step['done']   ? 'bg-emerald-500 border-emerald-500 text-white' : '' }}
                            {{ $step['reject'] ? 'bg-red-400 border-red-400 text-white' : '' }}
                            {{ $step['active'] && !$step['done'] && !$step['reject'] ? 'bg-amber-400 border-amber-400 text-white' : '' }}
                            {{ !$step['active'] && !$step['done'] && !$step['reject'] ? 'bg-slate-100 border-slate-200 text-slate-400' : '' }}">
                            <i class="fas {{ $step['done'] ? 'fa-check' : $step['icon'] }} text-xs"></i>
                        </div>
                        <span class="text-[10px] font-semibold text-slate-500 whitespace-nowrap">{{ $step['label'] }}</span>
                    </div>
                    @if(!$loop->last)
                        <div class="flex-1 h-0.5 mb-5 mx-1 {{ $step['done'] ? 'bg-emerald-300' : 'bg-slate-200' }}"></div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── GRID ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- ══ KOLOM KIRI ══ --}}
        <div class="lg:col-span-1 space-y-4">

            {{-- Akun --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="h-1 bg-gradient-to-r from-amber-400 via-yellow-300 to-amber-400"></div>
                <div class="p-5">
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-9 h-9 bg-amber-50 border border-amber-200 rounded-xl flex items-center justify-center">
                            <i class="fas fa-user-circle text-amber-600"></i>
                        </div>
                        <h3 class="font-bold text-slate-800 text-sm">Akun Pengguna</h3>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Nama</p>
                            <p class="text-sm font-semibold text-slate-700">{{ $user->name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Email</p>
                            <p class="text-sm font-semibold text-slate-700">{{ $user->email ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Status Berkas --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="h-1 bg-gradient-to-r from-amber-400 via-yellow-300 to-amber-400"></div>
                <div class="p-5">
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-9 h-9 bg-amber-50 border border-amber-200 rounded-xl flex items-center justify-center">
                            <i class="fas fa-folder-open text-amber-600"></i>
                        </div>
                        <h3 class="font-bold text-slate-800 text-sm">Status Berkas</h3>
                    </div>
                    @if($berkas)
                        <div class="space-y-3">
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Verifikasi</p>
                                @if($berkas->status_verifikasi == 'lulus')
                                    <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 px-3 py-1 rounded-full text-xs font-bold border border-emerald-200">
                                        <i class="fas fa-check-circle"></i> Diterima
                                    </span>
                                @elseif($berkas->status_verifikasi == 'tolak')
                                    <span class="inline-flex items-center gap-1.5 bg-red-50 text-red-600 px-3 py-1 rounded-full text-xs font-bold border border-red-200">
                                        <i class="fas fa-times-circle"></i> Ditolak
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 bg-amber-50 text-amber-700 px-3 py-1 rounded-full text-xs font-bold border border-amber-200">
                                        <i class="fas fa-spinner fa-spin"></i> Diproses
                                    </span>
                                @endif
                            </div>
                            @if($berkas->catatan_verifikasi)
                                <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl">
                                    <p class="text-[10px] font-bold text-slate-400 mb-1">Catatan Admin</p>
                                    <p class="text-xs text-slate-600 italic">"{{ $berkas->catatan_verifikasi }}"</p>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-file-excel text-slate-200 text-2xl mb-1.5"></i>
                            <p class="text-xs font-semibold text-slate-400">Belum ada berkas diunggah.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Status CBT --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="h-1 bg-gradient-to-r from-amber-400 via-yellow-300 to-amber-400"></div>
                <div class="p-5">
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-9 h-9 bg-amber-50 border border-amber-200 rounded-xl flex items-center justify-center">
                            <i class="fas fa-laptop-code text-amber-600"></i>
                        </div>
                        <h3 class="font-bold text-slate-800 text-sm">Ujian CBT</h3>
                    </div>
                    @if($isCbtSelesai)
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Hasil</p>
                            @if($statusKelulusan === 'LULUS')
                                <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 px-3 py-1 rounded-full text-xs font-bold border border-emerald-200">
                                    <i class="fas fa-check-circle"></i> Lulus
                                </span>
                            @elseif($statusKelulusan === 'TIDAK_LULUS')
                                <span class="inline-flex items-center gap-1.5 bg-red-50 text-red-600 px-3 py-1 rounded-full text-xs font-bold border border-red-200">
                                    <i class="fas fa-times-circle"></i> Tidak Lulus
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 bg-amber-50 text-amber-700 px-3 py-1 rounded-full text-xs font-bold border border-amber-200">
                                    <i class="fas fa-spinner fa-spin"></i> Sedang Dinilai
                                </span>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-keyboard text-slate-200 text-2xl mb-1.5"></i>
                            <p class="text-xs font-semibold text-slate-400">Belum mengikuti Ujian CBT.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Status Wawancara --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="h-1 bg-gradient-to-r from-amber-400 via-yellow-300 to-amber-400"></div>
                <div class="p-5">
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-9 h-9 bg-amber-50 border border-amber-200 rounded-xl flex items-center justify-center">
                            <i class="fas fa-user-tie text-amber-600"></i>
                        </div>
                        <h3 class="font-bold text-slate-800 text-sm">Wawancara</h3>
                    </div>
                    @if($isWawancaraSelesai)
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Hasil</p>
                            @if(in_array(strtolower($statusWawancara), ['lulus', 'lolos']))
                                <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 px-3 py-1 rounded-full text-xs font-bold border border-emerald-200">
                                    <i class="fas fa-check-circle"></i> Lulus
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 bg-red-50 text-red-600 px-3 py-1 rounded-full text-xs font-bold border border-red-200">
                                    <i class="fas fa-times-circle"></i> Tidak Lulus
                                </span>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-user-clock text-slate-200 text-2xl mb-1.5"></i>
                            <p class="text-xs font-semibold text-slate-400">Belum mengikuti Wawancara.</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- ══ KOLOM KANAN ══ --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Biodata --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="h-1 bg-gradient-to-r from-amber-400 via-yellow-300 to-amber-400"></div>
                <div class="p-6 sm:p-8">
                    <div class="flex items-center gap-2.5 mb-6">
                        <div class="w-9 h-9 bg-amber-50 border border-amber-200 rounded-xl flex items-center justify-center">
                            <i class="fas fa-address-card text-amber-600"></i>
                        </div>
                        <h3 class="font-bold text-slate-800">Biodata & Riwayat Pendidikan</h3>
                    </div>
                    @if($biodata)
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Tempat, Tanggal Lahir</p>
                                <p class="text-sm font-semibold text-slate-700">{{ $biodata->tempat_lahir }}, {{ \Carbon\Carbon::parse($biodata->tanggal_lahir)->translatedFormat('d F Y') }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Nomor WhatsApp</p>
                                <p class="text-sm font-semibold text-slate-700">{{ $biodata->no_wa }}</p>
                            </div>
                            <div class="sm:col-span-2">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Alamat Lengkap</p>
                                <p class="text-sm font-semibold text-slate-700">{{ $biodata->alamat }}</p>
                            </div>
                            <div class="sm:col-span-2 border-t border-dashed border-slate-200"></div>
                            <div class="sm:col-span-2">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Asal Perguruan Tinggi (S1)</p>
                                <p class="text-sm font-semibold text-slate-700">{{ $biodata->asal_s1 }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">NIM S1</p>
                                <p class="text-sm font-semibold text-slate-700">{{ $biodata->nim ?: 'Pendaftar Non-Alumni' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Tahun Lulus S1</p>
                                <p class="text-sm font-semibold text-slate-700">{{ $biodata->tahun_lulus }}</p>
                            </div>
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-12 text-center">
                            <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center mb-3">
                                <i class="fas fa-clipboard-list text-slate-300 text-2xl"></i>
                            </div>
                            <h4 class="font-bold text-slate-700 mb-1">Biodata Belum Dilengkapi</h4>
                            <p class="text-sm text-slate-400 mb-4">Anda belum mengisi form biodata profesi.</p>
                            <a href="{{ route('profesi.biodata.create') }}"
                               class="flex items-center gap-2 px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-xl transition-all shadow-sm active:scale-95">
                                Lengkapi Sekarang <i class="fas fa-arrow-right text-xs"></i>
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Detail Wawancara --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="h-1 bg-gradient-to-r from-amber-400 via-yellow-300 to-amber-400"></div>
                <div class="p-6">
                    <div class="flex items-center gap-2.5 mb-5">
                        <div class="w-9 h-9 bg-amber-50 border border-amber-200 rounded-xl flex items-center justify-center">
                            <i class="fas fa-comments text-amber-600"></i>
                        </div>
                        <h3 class="font-bold text-slate-800">Detail Hasil Wawancara</h3>
                    </div>
                    @if($isWawancaraSelesai)
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 bg-slate-50 border border-slate-100 rounded-xl">
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Status Kelulusan</p>
                                @if(in_array(strtolower($statusWawancara), ['lulus', 'lolos']))
                                    <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 px-3 py-1 rounded-full text-xs font-bold border border-emerald-200">
                                        <i class="fas fa-check-circle"></i> Direkomendasikan
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 bg-red-50 text-red-600 px-3 py-1 rounded-full text-xs font-bold border border-red-200">
                                        <i class="fas fa-times-circle"></i> Tidak Direkomendasikan
                                    </span>
                                @endif
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Keterangan Sistem</p>
                                <p class="text-sm font-semibold text-slate-700 capitalize">{{ $statusWawancara }}</p>
                            </div>
                        </div>
                    @else
                        <div class="flex items-center gap-3 p-4 bg-slate-50 border border-dashed border-slate-200 rounded-xl">
                            <i class="fas fa-info-circle text-slate-300 text-xl"></i>
                            <div>
                                <h4 class="font-semibold text-slate-600 text-sm">Belum Ada Penilaian</h4>
                                <p class="text-xs text-slate-400 mt-0.5">Admin belum memberikan nilai wawancara Anda.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Pembayaran UKT --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="h-1 bg-gradient-to-r from-amber-400 via-yellow-300 to-amber-400"></div>
                <div class="p-6">
                    <div class="flex items-center gap-2.5 mb-5">
                        <div class="w-9 h-9 bg-amber-50 border border-amber-200 rounded-xl flex items-center justify-center">
                            <i class="fas fa-file-invoice-dollar text-amber-600"></i>
                        </div>
                        <h3 class="font-bold text-slate-800">Status Pembayaran UKT</h3>
                    </div>
                    @if(isset($isBayarLunas) && $isBayarLunas)
                        <div class="flex items-center gap-4 p-4 bg-emerald-50 border border-emerald-100 rounded-xl relative overflow-hidden">
                            <div class="absolute -right-3 -top-3 opacity-10">
                                <i class="fas fa-stamp text-6xl text-emerald-600"></i>
                            </div>
                            <div class="w-11 h-11 bg-white rounded-full flex items-center justify-center shadow-sm flex-shrink-0">
                                <i class="fas fa-check text-emerald-500 text-lg"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-emerald-800 text-sm">Tagihan Lunas</h4>
                                <p class="text-xs text-emerald-600 mt-0.5">Pembayaran UKT Profesi Anda telah berhasil diverifikasi.</p>
                            </div>
                        </div>
                    @else
                        <div class="flex items-center gap-4 p-4 bg-amber-50 border border-amber-100 rounded-xl">
                            <div class="w-11 h-11 bg-white rounded-full flex items-center justify-center shadow-sm flex-shrink-0">
                                <i class="fas fa-clock text-amber-500 text-lg"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-amber-800 text-sm">Belum Lunas / Pending</h4>
                                <p class="text-xs text-amber-600 mt-0.5">Silakan selesaikan pembayaran di menu Dashboard Utama.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>{{-- end kolom kanan --}}
    </div>{{-- end grid --}}

</div>
</div>
@endsection