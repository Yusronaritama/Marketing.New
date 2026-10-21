@extends('layouts.admin.app')

@section('title', 'Hasil Analisis')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    /* Absensi Report Scoped Styles */
    .absensi-grid-4 {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    @media (min-width: 640px) {
        .absensi-grid-4 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (min-width: 1024px) {
        .absensi-grid-4 {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }
    .bg-gradient-amber {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        box-shadow: 0 6px 16px rgba(245, 158, 11, 0.3);
    }
    .absensi-grid-3 {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    @media (min-width: 768px) {
        .absensi-grid-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }
    .absensi-stat-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 18px 22px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .absensi-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.07);
    }
    .dark .absensi-stat-card {
        background: #1e293b;
        border-color: #334155;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);
    }
    .absensi-icon-circle {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        color: #ffffff;
        flex-shrink: 0;
    }
    .bg-gradient-emerald {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        box-shadow: 0 6px 16px rgba(16, 185, 129, 0.3);
    }
    .bg-gradient-purple {
        background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
        box-shadow: 0 6px 16px rgba(139, 92, 246, 0.3);
    }
    .bg-gradient-blue {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        box-shadow: 0 6px 16px rgba(59, 130, 246, 0.3);
    }
    .absensi-stat-value {
        font-size: 26px;
        font-weight: 800;
        line-height: 1.1;
        letter-spacing: -0.5px;
    }
    .absensi-stat-unit {
        font-size: 13px;
        font-weight: 600;
        color: #64748b;
        margin-left: 4px;
    }
    .dark .absensi-stat-unit {
        color: #94a3b8;
    }
    .absensi-stat-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #64748b;
        margin-bottom: 3px;
    }
    .dark .absensi-stat-label {
        color: #94a3b8;
    }
    .absensi-stat-desc {
        font-size: 11px;
        color: #94a3b8;
        display: flex;
        align-items: center;
        margin-top: 4px;
    }
    .dark .absensi-stat-desc {
        color: #64748b;
    }
    .absensi-pill-green {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 9999px;
        font-size: 11.5px;
        font-weight: 700;
        background-color: #ecfdf5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }
    .dark .absensi-pill-green {
        background-color: rgba(6, 95, 70, 0.3);
        color: #34d399;
        border-color: rgba(52, 211, 153, 0.2);
    }
    .absensi-pill-blue {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 9999px;
        font-size: 11.5px;
        font-weight: 700;
        background-color: #eff6ff;
        color: #1e40af;
        border: 1px solid #bfdbfe;
    }
    .dark .absensi-pill-blue {
        background-color: rgba(30, 64, 175, 0.3);
        color: #60a5fa;
        border-color: rgba(96, 165, 250, 0.2);
    }
    .absensi-pill-gray {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 11px;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-weight: 600;
        background-color: #f1f5f9;
        color: #334155;
        border: 1px solid #e2e8f0;
    }
    .dark .absensi-pill-gray {
        background-color: #1e293b;
        color: #cbd5e1;
        border-color: #334155;
    }
    .absensi-btn-action {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 14px;
        border-radius: 10px;
        font-size: 11.5px;
        font-weight: 700;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: #ffffff !important;
        border: none;
        cursor: pointer;
        box-shadow: 0 2px 6px rgba(16, 185, 129, 0.3);
        transition: all 0.2s ease;
    }
    .absensi-btn-action:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(16, 185, 129, 0.4);
    }
    .absensi-btn-action:active {
        transform: translateY(0);
    }
    /* WhatsApp Card & Bubble Styles */
    .absensi-wa-card {
        background: #ffffff;
        border: 1px solid #d1fae5;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.08);
        margin-bottom: 24px;
    }
    .dark .absensi-wa-card {
        background: #1e293b;
        border-color: #064e3b;
    }
    .absensi-wa-bubble {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-left: 5px solid #22c55e;
        border-radius: 12px;
        padding: 16px 20px;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 12.5px;
        line-height: 1.65;
        color: #1f2937;
        white-space: pre-wrap;
        user-select: all;
    }
    .dark .absensi-wa-bubble {
        background: #0f172a;
        border-color: #1e293b;
        border-left-color: #22c55e;
        color: #e2e8f0;
    }
    .absensi-btn-wa-copy {
        background: #10b981;
        color: #ffffff !important;
        font-size: 12px;
        font-weight: 700;
        padding: 8px 16px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        border: none;
        cursor: pointer;
        box-shadow: 0 2px 6px rgba(16, 185, 129, 0.3);
        transition: all 0.2s;
    }
    .absensi-btn-wa-copy:hover {
        background: #059669;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
        transform: translateY(-1px);
    }
    .absensi-btn-wa-share {
        background: #25d366;
        color: #ffffff !important;
        font-size: 12px;
        font-weight: 700;
        padding: 8px 16px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        border: none;
        cursor: pointer;
        box-shadow: 0 2px 6px rgba(37, 211, 102, 0.3);
        transition: all 0.2s;
        text-decoration: none;
    }
    .absensi-btn-wa-share:hover {
        background: #20ba5a;
        box-shadow: 0 4px 12px rgba(37, 211, 102, 0.4);
        transform: translateY(-1px);
    }
</style>
@endpush


@section('content')
<div class="rounded-lg bg-white p-6 shadow-theme-sm dark:bg-gray-dark">
    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-title-md font-bold text-gray-900 dark:text-white">
            Hasil Analisis Per Gelombang
        </h2>
        <a href="{{ route('admin.show-all.export') }}" 
           class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-all flex items-center gap-2">
            <i class="fas fa-file-excel"></i> Export Data Excel
        </a>
    </div>
    
    <div class="mb-6 p-5 bg-white rounded-xl shadow-sm border-l-4 border-blue-600">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
            <div>
                <h3 class="text-lg font-bold text-gray-800 mb-1">
                    <i class="fas fa-chart-line mr-2 text-blue-600"></i>Ringkasan Data Pendaftaran
                </h3>
                <p class="text-gray-600 text-sm leading-relaxed">
                    Yang mendaftar ada <strong>{{ number_format($totalMahasiswa) }}</strong> mahasiswa dari registrasi ulang,
                    dan yang sudah upload berkas sekitar <strong>{{ number_format($totalBerkas) }}</strong> Berkas dengan jumlah registrasi ulang <strong>{{ number_format($totalBiodataOrang) }}</strong> orang. <br>
                    Data final untuk DIKTI yang sudah ditarik sebanyak <strong>{{ number_format($totalDikti) }}</strong> mahasiswa.
                </p>
            </div>
            
            <div class="mt-4 md:mt-0 flex flex-wrap gap-4 bg-gray-50 p-3 rounded-lg border border-gray-100">
                <div class="text-xs"><span class="inline-block w-4 h-4 bg-blue-500 text-white text-center rounded-sm font-bold mr-1">D</span> <span class="text-gray-500">= Diterima</span></div>
                <div class="text-xs"><span class="inline-block w-4 h-4 bg-yellow-500 text-white text-center rounded-sm font-bold mr-1">U</span> <span class="text-gray-500">= Ujian</span></div>
                <div class="text-xs"><span class="inline-block w-4 h-4 bg-green-500 text-white text-center rounded-sm font-bold mr-1">R</span> <span class="text-gray-500">= Registrasi</span></div>
                <div class="text-xs"><span class="inline-block w-4 h-4 bg-purple-500 text-white text-center rounded-sm font-bold mr-1">F</span> <span class="text-gray-500">= Final (Dikti)</span></div>
            </div>
        </div>
    </div>

    {{-- TABEL REKAP PER PRODI --}}
    <div class="overflow-x-auto mb-10">
        <table class="min-w-full border-collapse">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-800">
                    <th rowspan="2" class="border border-gray-200 px-4 py-3 text-center text-sm font-semibold text-gray-900 dark:border-gray-700 dark:text-white">Nama Prodi</th>
                    @for ($i = 1; $i <= 10; $i++)
                        <th colspan="4" class="border border-gray-200 px-4 py-3 text-center text-sm font-semibold text-gray-900 dark:border-gray-700 dark:text-white">Gelombang {{ $i }}</th>
                    @endfor
                    <th colspan="4" class="border border-gray-200 bg-brand-50 px-4 py-3 text-center text-sm font-semibold text-brand-600 dark:border-gray-700 dark:bg-brand-500/15 dark:text-brand-400">Total</th>
                    @auth
                    <th rowspan="2" class="border border-gray-200 px-4 py-3 text-center text-sm font-semibold text-gray-900 dark:border-gray-700 dark:text-white">Unduh</th>
                    @endauth
                </tr>
                <tr class="bg-gray-50 dark:bg-gray-800">
                    @for ($i = 1; $i <= 10; $i++)
                        <th class="border border-gray-200 px-3 py-2 text-center text-xs font-medium text-gray-600 dark:border-gray-700 dark:text-gray-400">D</th>
                        <th class="border border-gray-200 px-3 py-2 text-center text-xs font-medium text-gray-600 dark:border-gray-700 dark:text-gray-400">U</th>
                        <th class="border border-gray-200 px-3 py-2 text-center text-xs font-medium text-gray-600 dark:border-gray-700 dark:text-gray-400">R</th>
                        <th class="border border-gray-200 px-3 py-2 text-center text-xs font-medium text-purple-600 dark:border-gray-700">Dikti</th>
                    @endfor
                    <th class="border border-gray-200 bg-brand-50 px-3 py-2 text-center text-xs font-medium text-brand-600 dark:border-gray-700 dark:bg-brand-500/15">D</th>
                    <th class="border border-gray-200 bg-brand-50 px-3 py-2 text-center text-xs font-medium text-brand-600 dark:border-gray-700 dark:bg-brand-500/15">U</th>
                    <th class="border border-gray-200 bg-brand-50 px-3 py-2 text-center text-xs font-medium text-brand-600 dark:border-gray-700 dark:bg-brand-500/15">R</th>
                    <th class="border border-gray-200 bg-brand-50 px-3 py-2 text-center text-xs font-medium text-purple-600 dark:border-gray-700 dark:bg-brand-500/15">Dikti</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalGel = [];
                    for ($i = 1; $i <= 10; $i++) { $totalGel[$i] = ['D' => 0, 'U' => 0, 'R' => 0, 'F' => 0]; }
                    $totalAll = ['D' => 0, 'U' => 0, 'R' => 0, 'F' => 0];
                @endphp
                @foreach ($prodi as $row)
                    @php
                        $totalD = 0; $totalU = 0; $totalR = 0; $totalF = 0;
                        for ($i = 1; $i <= 10; $i++) {
                            $d = $row->{'g'.$i.'_D'} ?? 0; $u = $row->{'g'.$i.'_U'} ?? 0;
                            $r = $row->{'g'.$i.'_R'} ?? 0; $f = $row->{'g'.$i.'_F'} ?? 0;
                            $totalD += $d; $totalU += $u; $totalR += $r; $totalF += $f;
                            $totalGel[$i]['D'] += $d; $totalGel[$i]['U'] += $u; $totalGel[$i]['R'] += $r; $totalGel[$i]['F'] += $f;
                        }
                        $totalAll['D'] += $totalD; $totalAll['U'] += $totalU; $totalAll['R'] += $totalR; $totalAll['F'] += $totalF;
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <td class="border border-gray-200 px-4 py-3 text-sm font-medium text-gray-900 dark:border-gray-700 dark:text-white">{{ $row->nama_prodi }}</td>
                        @for ($i = 1; $i <= 10; $i++)
                            {{-- Kolom Diterima (D) --}}
                            <td class="border border-gray-200 px-3 py-2 text-center text-sm text-gray-700 dark:border-gray-700 dark:text-gray-300">
                                @if(($row->{'g'.$i.'_D'} ?? 0) > 0)
                                    <a href="javascript:void(0)" class="view-users-trigger text-blue-600 hover:underline font-medium" data-title="Mendaftar: Gel {{ $i }} - {{ $row->nama_prodi }}" data-users="{{ $row->{'g'.$i.'_D_users'} }}">{{ $row->{'g'.$i.'_D'} }}</a>
                                @else 0 @endif
                            </td>
                            {{-- Kolom Ujian (U) --}}
                            <td class="border border-gray-200 px-3 py-2 text-center text-sm text-gray-700 dark:border-gray-700 dark:text-gray-300">
                                @if(($row->{'g'.$i.'_U'} ?? 0) > 0)
                                    <a href="javascript:void(0)" class="view-users-trigger text-yellow-600 hover:underline font-medium" data-title="Mengikuti Ujian: Gel {{ $i }} - {{ $row->nama_prodi }}" data-users="{{ $row->{'g'.$i.'_U_users'} }}">{{ $row->{'g'.$i.'_U'} }}</a>
                                @else 0 @endif
                            </td>
                            {{-- Kolom Registrasi (R) --}}
                            <td class="border border-gray-200 px-3 py-2 text-center text-sm text-gray-700 dark:border-gray-700 dark:text-gray-300">
                                @if(($row->{'g'.$i.'_R'} ?? 0) > 0)
                                    <a href="javascript:void(0)" class="view-users-trigger text-green-600 hover:underline font-medium" data-title="Registrasi Ulang Paid: Gel {{ $i }} - {{ $row->nama_prodi }}" data-users="{{ $row->{'g'.$i.'_R_users'} }}">{{ $row->{'g'.$i.'_R'} }}</a>
                                @else 0 @endif
                            </td>
                            {{-- Kolom Final Data (F) --}}
                            <td class="border border-gray-200 px-3 py-2 text-center text-sm text-gray-700 dark:border-gray-700 dark:text-gray-300">
                                @if(($row->{'g'.$i.'_F'} ?? 0) > 0)
                                    <a href="javascript:void(0)" class="view-users-trigger text-purple-600 hover:underline font-medium" data-title="Data Final DIKTI: Gel {{ $i }} - {{ $row->nama_prodi }}" data-users="{{ $row->{'g'.$i.'_F_users'} }}">{{ $row->{'g'.$i.'_F'} }}</a>
                                @else 0 @endif
                            </td>
                        @endfor
                        <td class="border border-gray-200 px-3 py-2 text-center text-sm font-semibold text-brand-600 dark:border-gray-700 dark:bg-brand-500/10 dark:text-brand-400">{{ $totalD }}</td>
                        <td class="border border-gray-200 px-3 py-2 text-center text-sm font-semibold text-brand-600 dark:border-gray-700 dark:bg-brand-500/10 dark:text-brand-400">{{ $totalU }}</td>
                        <td class="border border-gray-200 px-3 py-2 text-center text-sm font-semibold text-brand-600 dark:border-gray-700 dark:bg-brand-500/10 dark:text-brand-400">{{ $totalR }}</td>
                        <td class="border border-gray-200 px-3 py-2 text-center text-sm font-semibold text-brand-600 dark:border-gray-700 dark:bg-brand-500/10 dark:text-brand-400">{{ $totalF }}</td>
                        @auth
                        <td class="border border-gray-200 px-3 py-2 text-center">
    <a href="{{ route('admin.show-all.export-prodi', urlencode($row->nama_prodi)) }}"
       class="inline-flex items-center gap-1 rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-brand-600 transition-all">
        <i class="fas fa-download"></i> Unduh
    </a>
</td>
@endauth
                    </tr>
                @endforeach
                {{-- GRAND TOTAL --}}
                <tr class="bg-brand-100 dark:bg-brand-500/20">
                    <td class="border border-gray-200 px-4 py-3 text-sm font-bold text-gray-900 dark:border-gray-700 dark:text-white">GRAND TOTAL</td>
                    @for ($i = 1; $i <= 10; $i++)
                        <td class="border border-gray-200 px-3 py-2 text-center text-sm font-bold text-gray-900 dark:border-gray-700 dark:text-white">{{ $totalGel[$i]['D'] }}</td>
                        <td class="border border-gray-200 px-3 py-2 text-center text-sm font-bold text-gray-900 dark:border-gray-700 dark:text-white">{{ $totalGel[$i]['U'] }}</td>
                        <td class="border border-gray-200 px-3 py-2 text-center text-sm font-bold text-gray-900 dark:border-gray-700 dark:text-white">{{ $totalGel[$i]['R'] }}</td>
                        <td class="border border-gray-200 px-3 py-2 text-center text-sm font-bold text-gray-900 dark:border-gray-700 dark:text-white">{{ $totalGel[$i]['F'] }}</td>
                    @endfor
                    <td class="border border-gray-200 bg-brand-500 px-3 py-2 text-center text-sm font-bold text-white">{{ $totalAll['D'] }}</td>
                    <td class="border border-gray-200 bg-brand-500 px-3 py-2 text-center text-sm font-bold text-white">{{ $totalAll['U'] }}</td>
                    <td class="border border-gray-200 bg-brand-500 px-3 py-2 text-center text-sm font-bold text-white">{{ $totalAll['R'] }}</td>
                    <td class="border border-gray-200 bg-brand-500 px-3 py-2 text-center text-sm font-bold text-white">{{ $totalAll['F'] }}</td>
                    <td class="border border-gray-200 bg-brand-100 dark:bg-brand-500/20"></td>
                </tr>
            </tbody>
        </table>
    </div>


</div>

{{-- TABEL REKAP BIODATA & JALUR PENDAFTARAN --}}
<div class="mt-6 rounded-lg bg-white p-6 shadow-theme-sm dark:bg-gray-dark" x-data="rekapGrafik()">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-md font-bold text-gray-900 dark:text-white">
                Grafik Analisis Mahasiswa (Saat Ini)
            </h2>
            <p class="text-sm text-gray-500">Distribusi pendaftar per gelombang berdasarkan kategori</p>
        </div>

        <div class="flex items-center gap-3">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Filter:</label>
            <select 
                x-model="selectedCategory" 
                @change="updateChart()"
                class="rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900"
            >
                <template x-for="cat in categories" :key="cat">
                    <option :value="cat" x-text="cat"></option>
                </template>
            </select>
        </div>
    </div>

    <div class="min-h-[400px] w-full" id="chart-rekap"></div>

    <div class="mt-8 overflow-x-auto">
        <h3 class="mb-3 text-sm font-bold uppercase tracking-wider text-gray-500">Data Tabulasi: <span x-text="selectedCategory" class="text-brand-500"></span></h3>
        <table class="min-w-full border-collapse text-xs">
            <thead>
                <tr class="bg-gray-100 dark:bg-gray-800">
                    <th class="border border-gray-200 p-2 text-left dark:border-gray-700">Item</th>
                    @for ($g = 1; $g <= 10; $g++)
                        <th class="border border-gray-200 p-2 text-center dark:border-gray-700">G{{ $g }}</th>
                    @endfor
                    <th class="border border-gray-200 bg-brand-50 p-2 text-center font-bold text-brand-600 dark:border-gray-700">Total</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="row in filteredData" :key="row.nama">
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <td class="border border-gray-200 p-2 font-medium dark:border-gray-700" x-text="row.nama"></td>
                        <template x-for="g in [1,2,3,4,5,6,7,8,9,10]" :key="g">
                            <td class="border border-gray-200 p-2 text-center dark:border-gray-700" x-text="row['g'+g] || 0"></td>
                        </template>
                        <td class="border border-gray-200 bg-brand-50/30 p-2 text-center font-bold dark:border-gray-700" x-text="calculateTotal(row)"></td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>


    // =========================================================
    // 2. SCRIPT UNTUK GRAFIK ANALISIS SAAT INI (KODE ASLI MU)
    // =========================================================
    function rekapGrafik() {
        const rawData = @json($rekap);
        return {
            selectedCategory: 'Asal Wilayah', 
            categories: [...new Set(rawData.map(item => item.category_type))],
            chart: null,
            get filteredData() { return rawData.filter(item => item.category_type === this.selectedCategory); },
            calculateTotal(row) {
                let total = 0;
                for(let i=1; i<=10; i++) total += parseInt(row['g'+i] || 0);
                return total;
            },
            init() { this.$nextTick(() => { this.renderChart(); }); },
            renderChart() {
                const options = {
                    series: this.getSeries(),
                    chart: { type: 'bar', height: 400, stacked: true, toolbar: { show: true }, fontFamily: 'Inter, sans-serif' },
                    plotOptions: { bar: { horizontal: false, columnWidth: '55%', borderRadius: 4 } },
                    dataLabels: { enabled: false },
                    stroke: { show: true, width: 2, colors: ['transparent'] },
                    xaxis: { categories: ['Gel 1', 'Gel 2', 'Gel 3', 'Gel 4', 'Gel 5', 'Gel 6', 'Gel 7', 'Gel 8', 'Gel 9', 'Gel 10'] },
                    yaxis: { title: { text: 'Jumlah Mahasiswa' } },
                    fill: { opacity: 1 },
                    colors: ['#3C50E0', '#80CAEE', '#10B981', '#FF9C55', '#FF5B5B'],
                    legend: { position: 'top', horizontalAlign: 'left' }
                };
                this.chart = new ApexCharts(document.querySelector("#chart-rekap"), options);
                this.chart.render();
            },
            getSeries() {
                return this.filteredData.map(item => {
                    const dataPoints = [];
                    for(let i=1; i<=10; i++) { dataPoints.push(parseInt(item['g'+i] || 0)); }
                    return { name: item.nama, data: dataPoints };
                });
            },
            updateChart() { this.chart.updateSeries(this.getSeries()); }
        }
    }
</script>

{{-- SECTION GRAFIK ANALISIS KATEGORI (CHART.JS) --}}
<div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
    @php
        $groupedRekap = $rekap->groupBy('category_type');
    @endphp

    @foreach($groupedRekap as $type => $items)
    <div class="rounded-lg bg-white p-6 shadow-theme-sm dark:bg-gray-dark">
        <h3 class="mb-4 text-lg font-bold text-gray-900 dark:text-white">Analisis {{ $type }}</h3>
        <div class="h-80">
            <canvas id="chart_{{ Str::slug($type) }}"></canvas>
        </div>
    </div>
    @endforeach
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const colors = [
            'rgba(59, 130, 246, 0.8)', 'rgba(249, 115, 22, 0.8)', 
            'rgba(16, 185, 129, 0.8)', 'rgba(139, 92, 246, 0.8)',
            'rgba(236, 72, 153, 0.8)'
        ];

        @foreach($groupedRekap as $type => $items)
        new Chart(document.getElementById('chart_{{ Str::slug($type) }}').getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Gel 1', 'Gel 2', 'Gel 3', 'Gel 4', 'Gel 5', 'Gel 6', 'Gel 7', 'Gel 8', 'Gel 9', 'Gel 10'],
                datasets: [
                    @foreach($items as $index => $row)
                    {
                        label: '{{ $row->nama }}',
                        data: [
                            {{ $row->g1 }}, {{ $row->g2 }}, {{ $row->g3 }}, {{ $row->g4 }}, 
                            {{ $row->g5 }}, {{ $row->g6 }}, {{ $row->g7 }}, {{ $row->g8 }}, 
                            {{ $row->g9 }}, {{ $row->g10 }}
                        ],
                        backgroundColor: colors[{{ $index }} % colors.length],
                    },
                    @endforeach
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, usePointStyle: true } },
                    tooltip: { mode: 'index', intersect: false }
                },
                scales: {
                    x: { stacked: true },
                    y: { stacked: true, beginAtZero: true }
                }
            }
        });
        @endforeach
    });
</script>

{{-- ========================================================================= --}}
{{-- SECTION REKAP ABSENSI PMB (MASUK & PULANG - GROUP BY HARI & PETUGAS)       --}}
{{-- ========================================================================= --}}
<div class="mt-8 rounded-2xl bg-white p-6 shadow-theme-sm dark:bg-gray-dark border border-gray-200 dark:border-gray-800" 
     x-data="{
        selectedTanggal: '{{ $listTanggalAbsensi->first()->tanggal ?? date('Y-m-d') }}',
        searchAbsen: '',
        tabStatus: 'masuk', // 'masuk' or 'pulang'
        showTable: false,   // DEFAULT HIDE TABLE
        waFormatType: 'lengkap', // 'lengkap', 'masuk', 'pulang'
        datesData: {{ json_encode($listTanggalAbsensi->keyBy('tanggal')) }},
        allStats: {
            total_masuk: {{ $totalMasukSemua }},
            total_pulang: {{ $totalPulangSemua }},
            petugas_masuk: {{ $totalPetugasMasukSemua }},
            petugas_pulang: {{ $totalPetugasPulangSemua }},
            scan_masuk: {{ $totalScanMasukSemua }},
            scan_pulang: {{ $totalScanPulangSemua }}
        },
        get activeStats() {
            if (this.selectedTanggal === 'all') {
                return this.allStats;
            }
            return this.datesData[this.selectedTanggal] || {
                total_masuk: 0,
                total_pulang: 0,
                petugas_masuk: 0,
                petugas_pulang: 0,
                scan_masuk: 0,
                scan_pulang: 0
            };
        },
        get sisaBelumPulang() {
            const masuk = parseInt(this.activeStats.total_masuk || 0);
            const pulang = parseInt(this.activeStats.total_pulang || 0);
            return Math.max(0, masuk - pulang);
        },
        get tanggalLabelFormatted() {
            if (this.selectedTanggal === 'all') return 'Semua Tanggal (Akumulasi)';
            try {
                const p = this.selectedTanggal.split('-');
                if (p.length === 3) {
                    const d = new Date(p[0], p[1] - 1, p[2]);
                    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                    const dayName = days[d.getDay()];
                    const dayNum = String(d.getDate()).padStart(2, '0');
                    const monthName = months[d.getMonth()];
                    const year = d.getFullYear();
                    return `${dayName}, ${dayNum} ${monthName} ${year}`;
                }
            } catch(e) {}
            return this.selectedTanggal;
        },
        get currentReportText() {
            const tgl = this.tanggalLabelFormatted;
            const masuk = (this.activeStats.total_masuk || 0).toLocaleString('id-ID');
            const pulang = (this.activeStats.total_pulang || 0).toLocaleString('id-ID');
            const sisa = this.sisaBelumPulang.toLocaleString('id-ID');
            const petMasuk = (this.activeStats.petugas_masuk || 0).toLocaleString('id-ID');
            const petPulang = (this.activeStats.petugas_pulang || 0).toLocaleString('id-ID');
            const scanMasuk = (this.activeStats.scan_masuk || 0).toLocaleString('id-ID');
            const scanPulang = (this.activeStats.scan_pulang || 0).toLocaleString('id-ID');

            if (this.waFormatType === 'masuk') {
                return `*LAPORAN REKAPITULASI PRESENSI MASUK PKKMB*\n*UNIVERSITAS BUANA PERJUANGAN KARAWANG*\n────────────────────────\n\n📌 *INFORMASI PELAKSANAAN*\n• *Tanggal :* ${tgl}\n• *Kegiatan :* Presensi Masuk Mahasiswa Baru (PKKMB)\n\n📊 *DATA STATISTIK MASUK*\n• *Total Hadir (Masuk) :* *${masuk} orang*\n• *Jumlah Petugas Scanner :* *${petMasuk} petugas*\n• *Total Frekuensi Scan :* *${scanMasuk} kali*\n\n────────────────────────\n_Laporan resmi realtime dari Sistem PMB UBP Karawang_`;
            } else if (this.waFormatType === 'pulang') {
                return `*LAPORAN REKAPITULASI PRESENSI PULANG PKKMB*\n*UNIVERSITAS BUANA PERJUANGAN KARAWANG*\n────────────────────────\n\n📌 *INFORMASI PELAKSANAAN*\n• *Tanggal :* ${tgl}\n• *Kegiatan :* Presensi Pulang Mahasiswa Baru (PKKMB)\n\n📊 *DATA STATISTIK KEPULANGAN*\n• *Total Presensi Pulang :* *${pulang} orang*\n• *Belum Pulang (Di Lokasi) :* *${sisa} orang*\n• *Jumlah Petugas Scanner Pulang :* *${petPulang} petugas*\n• *Total Frekuensi Scan Pulang :* *${scanPulang} kali*\n\n────────────────────────\n_Laporan resmi realtime dari Sistem PMB UBP Karawang_`;
            }

            // Default: Lengkap (Masuk & Pulang)
            return `*LAPORAN REKAPITULASI PRESENSI PKKMB*\n*UNIVERSITAS BUANA PERJUANGAN KARAWANG*\n────────────────────────\n\n📌 *INFORMASI PELAKSANAAN*\n• *Tanggal :* ${tgl}\n• *Kegiatan :* Presensi Masuk & Pulang Mahasiswa Baru (PKKMB)\n\n🟢 *PRESENSI MASUK*\n• *Total Hadir (Masuk) :* *${masuk} orang*\n• *Petugas Scanner :* *${petMasuk} petugas*\n• *Total Scan Masuk :* *${scanMasuk} kali*\n\n🔵 *PRESENSI PULANG*\n• *Total Presensi Pulang :* *${pulang} orang*\n• *Belum Pulang (Di Lokasi) :* *${sisa} orang*\n• *Petugas Scanner Pulang :* *${petPulang} petugas*\n• *Total Scan Pulang :* *${scanPulang} kali*\n\n────────────────────────\n_Laporan resmi realtime dari Sistem PMB UBP Karawang_`;
        },
        shareToWhatsApp() {
            const text = encodeURIComponent(this.currentReportText);
            window.open('https://api.whatsapp.com/send?text=' + text, '_blank');
        },
        filterRow(tgl, email) {
            const matchTgl = (this.selectedTanggal === 'all' || this.selectedTanggal === tgl);
            const matchQuery = (!this.searchAbsen || email.toLowerCase().includes(this.searchAbsen.toLowerCase()) || tgl.includes(this.searchAbsen.toLowerCase()));
            return matchTgl && matchQuery;
        }
     }">

    {{-- Header & Toolbar Filter Tanggal --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-gray-100 dark:border-gray-800 pb-5">
        <div class="flex items-center gap-3">
            <span class="absensi-icon-circle bg-gradient-emerald text-xl">
                <i class="fas fa-clipboard-check"></i>
            </span>
            <div>
                <h2 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">
                    Laporan Rekap Absensi PMB (PKKMB)
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">Rekapitulasi Presensi Masuk & Pulang Peserta PKKMB</p>
            </div>
        </div>

        {{-- Controls Pilihan Tanggal Presensi --}}
        <div class="flex flex-wrap items-center gap-2.5">
            <div class="relative">
                <select 
                    x-model="selectedTanggal" 
                    class="cursor-pointer appearance-none rounded-xl border border-emerald-600/40 bg-emerald-50/60 hover:bg-emerald-50 py-2.5 pl-3.5 pr-9 text-xs font-bold text-emerald-950 shadow-xs focus:border-emerald-600 focus:outline-none dark:border-emerald-700/60 dark:bg-emerald-950/40 dark:text-emerald-200 transition-colors"
                >
                    @foreach($listTanggalAbsensi as $tgl)
                        <option value="{{ $tgl->tanggal }}">
                            {{ \Carbon\Carbon::parse($tgl->tanggal)->translatedFormat('l, d F Y') }} ({{ number_format($tgl->total_masuk) }} Masuk / {{ number_format($tgl->total_pulang) }} Pulang)
                        </option>
                    @endforeach
                    <option value="all">-- Semua Tanggal (Akumulasi) --</option>
                </select>
                <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-xs text-emerald-700 dark:text-emerald-400 pointer-events-none"></i>
            </div>

            <div class="flex items-center gap-1.5">
                <input 
                    type="date" 
                    x-model="selectedTanggal" 
                    class="rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-700 focus:border-emerald-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 shadow-xs"
                >
            </div>
        </div>
    </div>

    {{-- STATISTIK MASUK & PULANG (4 KARTU BERDAMPINGAN) --}}
    <div class="absensi-grid-4">
        {{-- Card 1: Mahasiswa Masuk --}}
        <div class="absensi-stat-card">
            <div class="absensi-icon-circle bg-gradient-emerald">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="flex-1 min-w-0">
                <div class="absensi-stat-label">Mahasiswa Masuk</div>
                <div class="flex items-baseline">
                    <span class="absensi-stat-value text-emerald-600 dark:text-emerald-400" x-text="(activeStats.total_masuk || 0).toLocaleString('id-ID')"></span>
                    <span class="absensi-stat-unit">orang</span>
                </div>
                <div class="absensi-stat-desc">
                    <i class="far fa-calendar-check mr-1.5 text-emerald-500"></i>
                    <span class="truncate" x-text="'Hadir: ' + tanggalLabelFormatted"></span>
                </div>
            </div>
        </div>

        {{-- Card 2: Mahasiswa Pulang --}}
        <div class="absensi-stat-card">
            <div class="absensi-icon-circle bg-gradient-blue">
                <i class="fas fa-walking"></i>
            </div>
            <div class="flex-1 min-w-0">
                <div class="absensi-stat-label">Mahasiswa Pulang</div>
                <div class="flex items-baseline">
                    <span class="absensi-stat-value text-blue-600 dark:text-blue-400" x-text="(activeStats.total_pulang || 0).toLocaleString('id-ID')"></span>
                    <span class="absensi-stat-unit">orang</span>
                </div>
                <div class="absensi-stat-desc">
                    <i class="fas fa-sign-out-alt mr-1.5 text-blue-500"></i>
                    <span>Presensi Pulang Terdata</span>
                </div>
            </div>
        </div>

        {{-- Card 3: Belum Pulang (Di Lokasi) --}}
        <div class="absensi-stat-card">
            <div class="absensi-icon-circle bg-gradient-amber">
                <i class="fas fa-user-clock"></i>
            </div>
            <div class="flex-1 min-w-0">
                <div class="absensi-stat-label">Belum Pulang (Di Kampus)</div>
                <div class="flex items-baseline">
                    <span class="absensi-stat-value text-amber-600 dark:text-amber-400" x-text="sisaBelumPulang.toLocaleString('id-ID')"></span>
                    <span class="absensi-stat-unit">orang</span>
                </div>
                <div class="absensi-stat-desc">
                    <i class="fas fa-map-marker-alt mr-1.5 text-amber-500"></i>
                    <span>Masih di Lingkungan Acara</span>
                </div>
            </div>
        </div>

        {{-- Card 4: Scanner & Frekuensi Scan --}}
        <div class="absensi-stat-card">
            <div class="absensi-icon-circle bg-gradient-purple">
                <i class="fas fa-qrcode"></i>
            </div>
            <div class="flex-1 min-w-0">
                <div class="absensi-stat-label">Scanner & Total Scan</div>
                <div class="flex items-baseline">
                    <span class="absensi-stat-value text-purple-600 dark:text-purple-400" x-text="(activeStats.petugas_masuk || 0) + ' / ' + (activeStats.petugas_pulang || 0)"></span>
                    <span class="absensi-stat-unit">petugas</span>
                </div>
                <div class="absensi-stat-desc">
                    <i class="fas fa-history mr-1.5 text-purple-500"></i>
                    <span x-text="'Scan: ' + (activeStats.scan_masuk || 0) + ' msk / ' + (activeStats.scan_pulang || 0) + ' plg'"></span>
                </div>
            </div>
        </div>
    </div>

    {{-- BOX TEXT RINGKASAN LAPORAN (UNTUK SHARE WA) --}}
    <div class="absensi-wa-card" x-data="{ copied: false }">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3.5">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl text-white text-lg shadow-sm shrink-0" style="background: linear-gradient(135deg, #10b981, #059669);">
                    <i class="fab fa-whatsapp text-xl"></i>
                </span>
                <div>
                    <h4 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        Format Pesan WhatsApp (Siap Kirim)
                        <span class="inline-block px-2 py-0.5 text-[10px] font-bold rounded-full" style="background-color: #d1fae5; color: #065f46;">Rapi & Terstruktur</span>
                    </h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Pesan terformat rapi dengan status masuk & pulang untuk disebarkan ke WhatsApp.</p>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex flex-wrap items-center gap-2">
                <button 
                    type="button" 
                    @click="
                        navigator.clipboard.writeText(currentReportText);
                        copied = true;
                        setTimeout(() => copied = false, 2500);
                    "
                    class="absensi-btn-wa-copy"
                >
                    <i class="fas" :class="copied ? 'fa-check' : 'fa-copy'"></i>
                    <span x-text="copied ? 'Tersalin ke Clipboard!' : 'Salin Teks WA'"></span>
                </button>

                <button 
                    type="button" 
                    @click="shareToWhatsApp()"
                    class="absensi-btn-wa-share"
                    title="Buka langsung di WhatsApp"
                >
                    <i class="fab fa-whatsapp text-sm"></i>
                    <span>Bagikan ke WA</span>
                </button>
            </div>
        </div>

        {{-- Format Selector Tabs --}}
        <div class="mb-3 flex flex-wrap items-center gap-2">
            <button 
                type="button"
                @click="waFormatType = 'lengkap'"
                class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors cursor-pointer"
                :style="waFormatType === 'lengkap' ? 'background-color: #10b981; color: #ffffff;' : 'background-color: #f1f5f9; color: #64748b;'"
            >
                <i class="fab fa-whatsapp mr-1"></i> Masuk & Pulang (Lengkap)
            </button>
            <button 
                type="button"
                @click="waFormatType = 'masuk'"
                class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors cursor-pointer"
                :style="waFormatType === 'masuk' ? 'background-color: #10b981; color: #ffffff;' : 'background-color: #f1f5f9; color: #64748b;'"
            >
                <i class="fas fa-sign-in-alt mr-1"></i> Hanya Presensi Masuk
            </button>
            <button 
                type="button"
                @click="waFormatType = 'pulang'"
                class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors cursor-pointer"
                :style="waFormatType === 'pulang' ? 'background-color: #10b981; color: #ffffff;' : 'background-color: #f1f5f9; color: #64748b;'"
            >
                <i class="fas fa-sign-out-alt mr-1"></i> Hanya Presensi Pulang
            </button>
        </div>

        {{-- Message Bubble Preview --}}
        <div class="relative">
            <pre class="absensi-wa-bubble" x-text="currentReportText"></pre>
        </div>
    </div>

    {{-- TOMBOL TOGGLE HIDE & SHOW TABEL DETAIL (DEFAULT: HIDE) --}}
    <div class="mt-4 mb-2 text-center">
        <button 
            type="button" 
            @click="showTable = !showTable"
            class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-xs font-bold border transition-all cursor-pointer shadow-xs active:scale-95"
            :style="showTable ? 'background-color: #f1f5f9; color: #475569; border-color: #cbd5e1;' : 'background: linear-gradient(135deg, #10b981, #059669); color: #ffffff; border-color: transparent; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);'"
        >
            <i class="fas" :class="showTable ? 'fa-eye-slash' : 'fa-table-list'"></i>
            <span x-text="showTable ? 'Sembunyikan Tabel Rincian Petugas Scanner' : 'Tampilkan Tabel Rincian Petugas Scanner (Masuk & Pulang)'"></span>
            <i class="fas ml-1" :class="showTable ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
        </button>
    </div>

    {{-- AREA TABEL RINCIAN (DAPAT DI-HIDE & SHOW) --}}
    <div x-show="showTable" x-cloak class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 transition-all">
        {{-- Sub-Toolbar: Tab Masuk vs Pulang & Search Bar --}}
        <div class="mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-gray-50/80 dark:bg-gray-800/60 p-3 rounded-xl border border-gray-200 dark:border-gray-700">
            {{-- Tab Switcher Masuk vs Pulang --}}
            <div class="flex items-center gap-2">
                <button 
                    type="button" 
                    @click="tabStatus = 'masuk'"
                    class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer"
                    :style="tabStatus === 'masuk' ? 'background-color: #10b981; color: #ffffff;' : 'background-color: #ffffff; color: #475569; border: 1px solid #cbd5e1;'"
                >
                    <i class="fas fa-sign-in-alt mr-1"></i> Petugas Presensi Masuk ({{ count($rekapAbsensi) }})
                </button>
                <button 
                    type="button" 
                    @click="tabStatus = 'pulang'"
                    class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer"
                    :style="tabStatus === 'pulang' ? 'background-color: #3b82f6; color: #ffffff;' : 'background-color: #ffffff; color: #475569; border: 1px solid #cbd5e1;'"
                >
                    <i class="fas fa-sign-out-alt mr-1"></i> Petugas Presensi Pulang ({{ count($rekapAbsensiPulang) }})
                </button>
            </div>

            {{-- Search Bar --}}
            <div class="relative max-w-sm w-full">
                <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                <input 
                    type="text" 
                    x-model="searchAbsen" 
                    placeholder="Cari email petugas scanner..."
                    class="w-full rounded-lg border border-gray-300 bg-white py-2 pl-9 pr-8 text-xs text-gray-900 focus:border-emerald-500 focus:outline-none dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-2xs"
                >
                <button 
                    type="button" 
                    x-show="searchAbsen" 
                    @click="searchAbsen = ''" 
                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xs"
                >
                    <i class="fas fa-times-circle"></i>
                </button>
            </div>
        </div>

        {{-- 1. TABEL PETUGAS PRESENSI MASUK --}}
        <div x-show="tabStatus === 'masuk'" class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700 max-h-[500px] shadow-2xs">
            <table class="min-w-full border-collapse text-left text-xs">
                <thead class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="p-3 text-center font-bold text-gray-700 dark:text-gray-200 w-12">No.</th>
                        <th class="p-3 font-bold text-gray-700 dark:text-gray-200">Hari / Tanggal</th>
                        <th class="p-3 font-bold text-gray-700 dark:text-gray-200">Petugas Scanner Masuk</th>
                        <th class="p-3 text-center font-bold text-gray-700 dark:text-gray-200">Mahasiswa Hadir</th>
                        <th class="p-3 text-center font-bold text-gray-700 dark:text-gray-200">Total Scan</th>
                        <th class="p-3 text-center font-bold text-gray-700 dark:text-gray-200">Rentang Waktu Scan</th>
                        <th class="p-3 text-center font-bold text-gray-700 dark:text-gray-200">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800 bg-white dark:bg-gray-900">
                    @forelse($rekapAbsensi as $idx => $absen)
                    <tr class="hover:bg-emerald-50/40 dark:hover:bg-emerald-950/20 transition-colors"
                        x-show="filterRow('{{ $absen->tanggal }}', '{{ strtolower($absen->email_masuk) }}')">
                        <td class="p-3 text-center text-gray-400 font-medium">{{ $idx + 1 }}</td>
                        <td class="p-3 font-semibold text-gray-800 dark:text-gray-200 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 dark:bg-gray-800 px-2.5 py-1 text-xs font-semibold text-gray-700 dark:text-gray-300">
                                <i class="far fa-calendar-alt text-gray-400"></i>
                                {{ \Carbon\Carbon::parse($absen->tanggal)->translatedFormat('l, d M Y') }}
                            </span>
                        </td>
                        <td class="p-3">
                            <div class="flex items-center gap-2.5">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 text-xs font-bold shrink-0">
                                    <i class="fas fa-user-tie"></i>
                                </span>
                                <div>
                                    <p class="font-bold text-gray-800 dark:text-gray-100 font-mono text-[11.5px]">{{ $absen->email_masuk }}</p>
                                    <span class="inline-block text-[10px] text-gray-400 uppercase tracking-wider">Petugas Presensi Masuk</span>
                                </div>
                            </div>
                        </td>
                        <td class="p-3 text-center whitespace-nowrap">
                            <span class="absensi-pill-green">
                                <i class="fas fa-user-graduate mr-1"></i> {{ number_format($absen->total_peserta) }} orang
                            </span>
                        </td>
                        <td class="p-3 text-center whitespace-nowrap">
                            <span class="absensi-pill-blue">
                                <i class="fas fa-qrcode mr-1"></i> {{ number_format($absen->total_scan) }}x
                            </span>
                        </td>
                        <td class="p-3 text-center whitespace-nowrap">
                            <span class="absensi-pill-gray">
                                <i class="far fa-clock mr-1 text-gray-400"></i>
                                {{ $absen->scan_pertama ?? '-' }} s/d {{ $absen->scan_terakhir ?? '-' }}
                            </span>
                        </td>
                        <td class="p-3 text-center whitespace-nowrap">
                            <button 
                                type="button" 
                                class="absensi-btn-action view-users-trigger"
                                data-title="Presensi Masuk {{ \Carbon\Carbon::parse($absen->tanggal)->translatedFormat('d M Y') }} — {{ $absen->email_masuk }}"
                                data-users="{{ $absen->daftar_peserta }}"
                            >
                                <i class="fas fa-users text-xs"></i>
                                <span>Lihat Mahasiswa ({{ $absen->total_peserta }})</span>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-gray-400">
                            <i class="fas fa-info-circle text-xl mb-2 text-gray-300"></i>
                            <p class="font-medium">Belum ada rekaman presensi masuk.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- 2. TABEL PETUGAS PRESENSI PULANG --}}
        <div x-show="tabStatus === 'pulang'" class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700 max-h-[500px] shadow-2xs">
            <table class="min-w-full border-collapse text-left text-xs">
                <thead class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="p-3 text-center font-bold text-gray-700 dark:text-gray-200 w-12">No.</th>
                        <th class="p-3 font-bold text-gray-700 dark:text-gray-200">Hari / Tanggal</th>
                        <th class="p-3 font-bold text-gray-700 dark:text-gray-200">Petugas Scanner Pulang</th>
                        <th class="p-3 text-center font-bold text-gray-700 dark:text-gray-200">Mahasiswa Pulang</th>
                        <th class="p-3 text-center font-bold text-gray-700 dark:text-gray-200">Total Scan</th>
                        <th class="p-3 text-center font-bold text-gray-700 dark:text-gray-200">Rentang Waktu Scan</th>
                        <th class="p-3 text-center font-bold text-gray-700 dark:text-gray-200">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800 bg-white dark:bg-gray-900">
                    @forelse($rekapAbsensiPulang as $idx => $absen)
                    <tr class="hover:bg-blue-50/40 dark:hover:bg-blue-950/20 transition-colors"
                        x-show="filterRow('{{ $absen->tanggal }}', '{{ strtolower($absen->email_pulang) }}')">
                        <td class="p-3 text-center text-gray-400 font-medium">{{ $idx + 1 }}</td>
                        <td class="p-3 font-semibold text-gray-800 dark:text-gray-200 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 dark:bg-gray-800 px-2.5 py-1 text-xs font-semibold text-gray-700 dark:text-gray-300">
                                <i class="far fa-calendar-alt text-gray-400"></i>
                                {{ \Carbon\Carbon::parse($absen->tanggal)->translatedFormat('l, d M Y') }}
                            </span>
                        </td>
                        <td class="p-3">
                            <div class="flex items-center gap-2.5">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 text-xs font-bold shrink-0">
                                    <i class="fas fa-user-check"></i>
                                </span>
                                <div>
                                    <p class="font-bold text-gray-800 dark:text-gray-100 font-mono text-[11.5px]">{{ $absen->email_pulang }}</p>
                                    <span class="inline-block text-[10px] text-gray-400 uppercase tracking-wider">Petugas Presensi Pulang</span>
                                </div>
                            </div>
                        </td>
                        <td class="p-3 text-center whitespace-nowrap">
                            <span class="absensi-pill-blue">
                                <i class="fas fa-walking mr-1"></i> {{ number_format($absen->total_peserta) }} orang
                            </span>
                        </td>
                        <td class="p-3 text-center whitespace-nowrap">
                            <span class="absensi-pill-green">
                                <i class="fas fa-qrcode mr-1"></i> {{ number_format($absen->total_scan) }}x
                            </span>
                        </td>
                        <td class="p-3 text-center whitespace-nowrap">
                            <span class="absensi-pill-gray">
                                <i class="far fa-clock mr-1 text-gray-400"></i>
                                {{ $absen->scan_pertama ?? '-' }} s/d {{ $absen->scan_terakhir ?? '-' }}
                            </span>
                        </td>
                        <td class="p-3 text-center whitespace-nowrap">
                            <button 
                                type="button" 
                                class="absensi-btn-action view-users-trigger"
                                style="background: linear-gradient(135deg, #3b82f6, #1d4ed8); box-shadow: 0 2px 6px rgba(59, 130, 246, 0.3);"
                                data-title="Presensi Pulang {{ \Carbon\Carbon::parse($absen->tanggal)->translatedFormat('d M Y') }} — {{ $absen->email_pulang }}"
                                data-users="{{ $absen->daftar_peserta }}"
                            >
                                <i class="fas fa-users text-xs"></i>
                                <span>Lihat Mahasiswa ({{ $absen->total_peserta }})</span>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-gray-400">
                            <i class="fas fa-info-circle text-xl mb-2 text-gray-300"></i>
                            <p class="font-medium">Belum ada rekaman presensi pulang pada data ini.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL POPUP LIHAT MAHASISWA --}}
<div class="modal fade fixed inset-0 z-[99999] hidden w-full h-full outline-none overflow-x-hidden overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4" id="userListModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="relative w-full max-w-lg pointer-events-none">
        <div class="relative flex flex-col w-full pointer-events-auto bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-2xl outline-none overflow-hidden">
            
            {{-- Modal Header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-800 bg-gray-50/60 dark:bg-gray-800/40">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400 text-sm font-bold">
                        <i class="fas fa-users"></i>
                    </span>
                    <div>
                        <h5 class="text-sm font-bold text-gray-900 dark:text-white" id="modalTargetTitle">
                            Detail Mahasiswa
                        </h5>
                        <p class="text-[11px] text-gray-400" id="modalSubtitle">Daftar mahasiswa yang di-scan oleh petugas</p>
                    </div>
                </div>
                <button type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-lg p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors cursor-pointer" onclick="$('#userListModal').addClass('hidden').removeClass('flex')">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            {{-- Modal Search Filter --}}
            <div class="p-3 border-b border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input 
                        type="text" 
                        id="modalSearchUser" 
                        placeholder="Cari nama / nomor pendaftaran / NIM..." 
                        class="w-full rounded-lg border border-gray-200 bg-gray-50 py-1.5 pl-8 pr-3 text-xs text-gray-900 focus:border-emerald-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    >
                </div>
            </div>
            
            {{-- Modal Body List --}}
            <div class="p-4 max-h-[380px] overflow-y-auto">
                <ul id="userListContainer" class="space-y-1 text-xs text-gray-700 dark:text-gray-300"></ul>
            </div>
            
            {{-- Modal Footer --}}
            <div class="flex items-center justify-between px-5 py-3 border-t border-gray-100 dark:border-gray-800 bg-gray-50/70 dark:bg-gray-800/40">
                <span class="text-xs text-gray-500 dark:text-gray-400 font-medium" id="modalUserCount"></span>
                <button type="button" class="px-4 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700 dark:hover:bg-gray-700 transition-colors shadow-2xs cursor-pointer" onclick="$('#userListModal').addClass('hidden').removeClass('flex')">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        let currentUsers = [];

        function renderModalList(filter = '') {
            const container = $('#userListContainer');
            container.empty();
            const q = filter.toLowerCase().trim();
            const filtered = currentUsers.filter(u => !q || u.toLowerCase().includes(q));

            if (filtered.length > 0) {
                filtered.forEach(function(user, idx) {
                    container.append(
                        '<li class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/60 border-b border-gray-100 dark:border-gray-800/50 last:border-0">' +
                        '<span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 text-[10px] font-bold">' + (idx + 1) + '</span>' +
                        '<span class="font-medium text-gray-800 dark:text-gray-200">' + user + '</span>' +
                        '</li>'
                    );
                });
            } else {
                container.append('<li class="text-center py-6 text-gray-400"><i class="fas fa-user-slash text-xl mb-1 block"></i> Tidak ditemukan mahasiswa yang sesuai kata kunci.</li>');
            }

            $('#modalUserCount').text('Menampilkan ' + filtered.length + ' dari ' + currentUsers.length + ' mahasiswa');
        }

        // Event click pemicu popup nama mahasiswa
        $(document).on('click', '.view-users-trigger', function() {
            var title = $(this).data('title');
            var rawUsers = $(this).data('users'); 
            
            $('#modalTargetTitle').text(title);
            $('#modalSearchUser').val('');
            
            currentUsers = [];
            if (rawUsers && String(rawUsers).trim() !== "") {
                currentUsers = String(rawUsers).split('||').map(s => s.trim()).filter(Boolean);
            }
            
            renderModalList('');
            $('#userListModal').removeClass('hidden').addClass('flex');
        });

        $('#modalSearchUser').on('input', function() {
            renderModalList($(this).val());
        });

        // Menutup modal jika admin klik di luar box modal
        $('#userListModal').on('click', function(e) {
            if (e.target === this) {
                $(this).addClass('hidden').removeClass('flex');
            }
        });
    });
</script>

@endsection
