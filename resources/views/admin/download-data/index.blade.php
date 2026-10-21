@extends('layouts.admin.app')

@section('title', 'Download Data Mahasiswa')

@section('content')
<style>
    /* Container Style */
.smart-export-card {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    padding: 24px;
    border: 1px solid #f0f0f5;
    transition: transform 0.3s ease;
}

/* Header Section */
.export-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 24px;
}

.export-icon-wrapper {
    background: #eef2ff;
    color: #4f46e5;
    padding: 10px;
    border-radius: 12px;
}

.export-badge {
    background: #eef2ff;
    color: #4f46e5;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 4px 12px;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.export-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

/* Form Controls */
.filter-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: #4b5563;
    margin-bottom: 8px;
}

.custom-select {
    width: 100%;
    padding: 10px 14px;
    border-radius: 10px;
    border: 1px solid #d1d5db;
    background-color: #f9fafb;
    font-size: 0.95rem;
    color: #374151;
    transition: all 0.2s;
    outline: none;
}

.custom-select:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    background-color: #fff;
}

/* Checklist Grid */
.checklist-container {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 12px;
    margin-bottom: 24px;
}

.checkbox-item {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    font-size: 0.9rem;
    color: #4b5563;
    padding: 6px;
    border-radius: 6px;
    transition: background 0.2s;
}

.checkbox-item:hover {
    background: #f1f5f9;
}

.checkbox-item input[type="checkbox"] {
    width: 18px;
    height: 18px;
    border-radius: 4px;
    accent-color: #4f46e5;
    cursor: pointer;
}

/* Button Style */
.btn-generate {
    background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
    color: white;
    font-weight: 600;
    padding: 14px;
    border-radius: 10px;
    width: 100%;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
    transition: all 0.3s;
}

.btn-generate:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(79, 70, 229, 0.4);
    filter: brightness(1.1);
}

.btn-generate:active {
    transform: translateY(0);
}
</style>
<div class="cyberpunk-download-container">
    <nav class="cyberpunk-breadcrumb">
        <ol class="cyberpunk-breadcrumb-list">
            <li><a href="{{ route('admin.dashboard.index') }}" class="cyberpunk-breadcrumb-link">Dashboard</a></li>
            <li class="cyberpunk-breadcrumb-separator">/</li>
            <li class="cyberpunk-breadcrumb-active">Download Data</li>
        </ol>
    </nav>
    @if(session('error'))
    <div class="mb-4 p-4 rounded-lg bg-red-500/20 border border-red-500 text-red-200">
        <strong>Peringatan!</strong> {{ session('error') }}
    </div>
@endif

@if(session('info'))
    <div class="mb-4 p-4 rounded-lg bg-blue-500/20 border border-blue-500 text-blue-200">
        <strong>Informasi:</strong> {{ session('info') }}
    </div>
@endif
    <div class="cyberpunk-header">
        <div class="cyberpunk-header-content">
            <h1 class="cyberpunk-title">Download Data Mahasiswa</h1>
            <p class="cyberpunk-subtitle">Download data mahasiswa dalam berbagai format dan kategori</p>
        </div>
        <div class="cyberpunk-header-glow"></div>
    </div>
    <div class="smart-export-card">
        <div class="export-header">
            <div class="export-icon-wrapper">
                <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
            </div>
            <div>
                <span class="export-badge">Export Sesuai Kebutuhan</span>
                <h3 class="export-title">Download Berdasarkan Kriteria</h3>
            </div>
        </div>
        
        <form action="{{ route('admin.download-data.custom-mhs') }}" method="POST">
            @csrf
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; margin-bottom: 24px;">
                {{-- Filter Status Isian --}}
                <div>
                <label class="filter-label">Filter Status Isian</label>
                <select name="status_isian" class="custom-select">
                    <option value="">-- Semua Status Isian --</option>
                    <option value="empty">Belum Isi Apapun (Kosong)</option>
                    <option value="only_biodata">Baru Isi Biodata (Belum E-Reg)</option>
                    <option value="already_ereg">Sudah Registrasi Ulang (E-Reg)</option>
                    <option value="paid_slik">Sudah Bayar Tahap 1 (Lunas SLIK)</option>
                    <option value="paid_300k">Sudah Bayar Pendaftaran (300K)</option>
                </select>
            </div>
    
                {{-- Filter Spesifik Prodi --}}
                <div>
                    <label class="filter-label">Pilih Program Studi</label>
                    <select name="prodi_id" class="custom-select">
                        <option value="">-- Semua Program Studi --</option>
                        @foreach($programStudi ?? [] as $prodi)
                            <option value="{{ $prodi->kode_prodi }}">
                                {{ $prodi->nama_prodi }} ({{ $prodi->jenjang }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
    
          <label class="filter-label">Kriteria Kolom Excel</label>
            <div class="checklist-container">
                <label class="checkbox-item"><input type="checkbox" name="columns[]" value="nama" checked> Nama Lengkap</label>
                <label class="checkbox-item"><input type="checkbox" name="columns[]" value="email" checked> Email</label>
                <label class="checkbox-item"><input type="checkbox" name="columns[]" value="phone" checked> Nomor Telepon</label>
                <label class="checkbox-item"><input type="checkbox" name="columns[]" value="gelombang"> Gelombang</label>
                <label class="checkbox-item"><input type="checkbox" name="columns[]" value="jalur"> Jalur Daftar</label>
                <label class="checkbox-item"><input type="checkbox" name="columns[]" value="prodi_1"> Pilihan Prodi 1</label>
                <label class="checkbox-item"><input type="checkbox" name="columns[]" value="prodi_2"> Pilihan Prodi 2</label>
                <label class="checkbox-item"><input type="checkbox" name="columns[]" value="va"> Username</label>
                <label class="checkbox-item"><input type="checkbox" name="columns[]" value="asal_sekolah"> Asal Sekolah</label>
                <label class="checkbox-item"><input type="checkbox" name="columns[]" value="status_bayar"> Status Bayar Daftar</label>
                <label class="checkbox-item"><input type="checkbox" name="columns[]" value="ujian"> Ujian</label>
                <label class="checkbox-item"><input type="checkbox" name="columns[]" value="bayar_slik"> Bayar Tahap 1</label>
                <label class="checkbox-item"><input type="checkbox" name="columns[]" value="diterima"> Diterima</label> 
                <label class="checkbox-item"><input type="checkbox" name="columns[]" value="ukuran_almamater"> Ukuran Almamater</label>
                <label class="checkbox-item"><input type="checkbox" name="columns[]" value="data_final"> Mahasiswa Data final</label>
                <label class="checkbox-item"><input type="checkbox" name="columns[]" value="quiz"> Status Quiz Moodle</label>
                <label class="checkbox-item"><input type="checkbox" name="columns[]" value="id_provinsi"> Provinsi</label>
                <label class="checkbox-item"><input type="checkbox" name="columns[]" value="id_kabupaten">  Kabupaten</label>
                <label class="checkbox-item"><input type="checkbox" name="columns[]" value="id_kecamatan"> Kecamatan</label>
                <label class="checkbox-item"><input type="checkbox" name="columns[]" value="nim"> NIM</label>
                <label class="checkbox-item"><input type="checkbox" name="columns[]" value="email_mhs"> Email Kampus (@mhs)</label>
                <label class="checkbox-item"><input type="checkbox" name="columns[]" value="beasiswa"> Status Beasiswa</label>
            </div>
                
            </div>
    
            <button type="submit" class="btn-generate">
                <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                GENERATE EXCEL TERPILIH
            </button>
        </form>
    </div>


          @if(!in_array(auth()->user()->role, ['admin_akademik', 'admin_farmasi']))
    <div class="cyberpunk-cards-grid">
        
        <div class="cyberpunk-card cyberpunk-card-success">
    <div class="cyberpunk-card-header">
        <div class="cyberpunk-card-icon">
            <svg class="cyberpunk-card-icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <span class="cyberpunk-card-badge cyberpunk-badge-success">Excel</span>
    </div>
    
    <h3 class="cyberpunk-card-title">Data Kelulusan</h3>
    <p class="cyberpunk-card-description">
        Download data mahasiswa yang sudah lulus (S1 & S2) beserta informasi prodi dan nilai quiz.
    </p>
    
    <div class="cyberpunk-card-features">
        <div class="cyberpunk-feature">
            <svg class="cyberpunk-feature-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span>Semua Jenjang (S1 & S2)</span>
        </div>
    </div>
    
    <div class="cyberpunk-btn-group">
        {{-- ROUTE INI HARUS SESUAI DENGAN YANG DI CONTROLLER --}}
        <a href="{{ route('admin.download.moodle-lulus') }}" class="cyberpunk-download-btn cyberpunk-btn-success">
            <svg class="cyberpunk-download-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Download Semua Lulusan
        </a>
         <a href="{{ route('admin.download.s2-excel') }}" class="cyberpunk-download-btn cyberpunk-btn-outline-success mt-2">
                    <svg class="cyberpunk-download-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Khusus Data S2
                </a>
    </div>
</div>
        <div class="cyberpunk-card cyberpunk-card-primary">
            <div class="cyberpunk-card-header">
                <div class="cyberpunk-card-icon">
                    <svg class="cyberpunk-card-icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <span class="cyberpunk-card-badge cyberpunk-badge-primary">Excel</span>
            </div>
            
            <h3 class="cyberpunk-card-title">Data Pendaftaran</h3>
            <p class="cyberpunk-card-description">
                Download data pendaftaran mahasiswa dengan filter status.
            </p>

            <div class="cyberpunk-filter-section">
                <label class="cyberpunk-filter-label">Filter Status:</label>
                <div class="cyberpunk-filter-buttons">
                    <a href="{{ route('admin.download.master-ereg', ['status' => 'all']) }}"
                       class="cyberpunk-filter-btn {{ request('status', 'all') == 'all' ? 'cyberpunk-filter-active' : 'cyberpunk-filter-inactive' }}">
                        Semua
                    </a>
                    <a href="{{ route('admin.download.master-ereg', ['status' => 'completed']) }}"
                       class="cyberpunk-filter-btn {{ request('status') == 'completed' ? 'cyberpunk-filter-active cyberpunk-filter-success' : 'cyberpunk-filter-inactive' }}">
                        Completed
                    </a>
                    <a href="{{ route('admin.download.master-ereg', ['status' => 'in_progress']) }}"
                       class="cyberpunk-filter-btn {{ request('status') == 'in_progress' ? 'cyberpunk-filter-active cyberpunk-filter-warning' : 'cyberpunk-filter-inactive' }}">
                        Progress
                    </a>
                    <a href="{{ route('admin.download.master-ereg', ['status' => 'rejected']) }}"
                       class="cyberpunk-filter-btn {{ request('status') == 'rejected' ? 'cyberpunk-filter-active cyberpunk-filter-error' : 'cyberpunk-filter-inactive' }}">
                        Rejected
                    </a>
                </div>
            </div>
            
            @php
            $currentStatus = request('status', 'all');
            $downloadUrl = route('admin.download.master-ereg', ['status' => $currentStatus]);
            $buttonText = 'Download ' . ($currentStatus == 'all' ? 'Semua' : ucfirst(str_replace('_', ' ', $currentStatus)));
            @endphp

            <a href="{{ $downloadUrl }}" class="cyberpunk-download-btn cyberpunk-btn-primary mt-auto">
                <svg class="cyberpunk-download-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                {{ $buttonText }}
            </a>
        </div>

      <div class="cyberpunk-card cyberpunk-card-info">
    <div class="cyberpunk-card-header">
        <div class="cyberpunk-card-icon">
            <svg class="cyberpunk-card-icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
        </div>
        <span class="cyberpunk-card-badge cyberpunk-badge-info">Excel</span>
    </div>

    <h3 class="cyberpunk-card-title">Data Mahasiswa Finals</h3>
    <p class="cyberpunk-card-description">
        Download data lengkap mahasiswa dari tabel data_mahasiswa_finals dengan informasi biodata, orangtua, wali, dan kontak.
    </p>

    <div class="cyberpunk-card-features">
        <div class="cyberpunk-feature">
            <svg class="cyberpunk-feature-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span>Data Biodata Lengkap</span>
        </div>
        <div class="cyberpunk-feature">
            <svg class="cyberpunk-feature-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span>Informasi Orangtua & Wali</span>
        </div>
    </div>

    {{-- Filter Section --}}
    <div class="cyberpunk-filter-section" style="margin-top: 16px;">

        {{-- Filter Jenjang --}}
        <label class="cyberpunk-filter-label">Filter Jenjang:</label>
        <div class="cyberpunk-filter-buttons" style="margin-bottom: 12px;">
            <a href="{{ route('admin.download.data-mahasiswa-finals', array_merge(request()->query(), ['jenjang' => 'all', 'prodi' => request('prodi', 'all')])) }}"
               class="cyberpunk-filter-btn {{ request('jenjang', 'all') == 'all' ? 'cyberpunk-filter-active' : 'cyberpunk-filter-inactive' }}">
                Semua
            </a>
            <a href="{{ route('admin.download.data-mahasiswa-finals', array_merge(request()->query(), ['jenjang' => 'S1', 'prodi' => request('prodi', 'all')])) }}"
               class="cyberpunk-filter-btn {{ request('jenjang') == 'S1' ? 'cyberpunk-filter-active cyberpunk-filter-success' : 'cyberpunk-filter-inactive' }}">
                S1
            </a>
            <a href="{{ route('admin.download.data-mahasiswa-finals', array_merge(request()->query(), ['jenjang' => 'S2', 'prodi' => request('prodi', 'all')])) }}"
               class="cyberpunk-filter-btn {{ request('jenjang') == 'S2' ? 'cyberpunk-filter-active cyberpunk-filter-warning' : 'cyberpunk-filter-inactive' }}">
                S2
            </a>
        </div>

        {{-- Filter Prodi --}}
        <label class="cyberpunk-filter-label" style="margin-top: 4px;">Filter Program Studi:</label>
        <select
            id="finals-prodi-select"
            onchange="updateFinalsProdiFilter(this.value)"
            style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid rgba(99,102,241,0.3); background: rgba(15,15,35,0.6); color: #c7c7d4; font-size: 0.85rem; margin-top: 6px; outline: none; cursor: pointer;">
            <option value="all" {{ request('prodi', 'all') == 'all' ? 'selected' : '' }}>-- Semua Program Studi --</option>
            @foreach($programStudi ?? [] as $prodi)
                @if(request('jenjang', 'all') == 'all' || request('jenjang') == $prodi->jenjang)
                    <option value="{{ $prodi->kode_prodi }}"
                        {{ request('prodi') == $prodi->kode_prodi ? 'selected' : '' }}>
                        {{ $prodi->nama_prodi }} ({{ $prodi->jenjang }})
                    </option>
                @endif
            @endforeach
        </select>
    </div>

    {{-- Tombol Download --}}
    @php
        $finalsJenjang = request('jenjang', 'all');
        $finalsProdi   = request('prodi', 'all');

        $finalsParams = [];
        if ($finalsJenjang !== 'all') $finalsParams['jenjang'] = $finalsJenjang;
        if ($finalsProdi   !== 'all') $finalsParams['prodi']   = $finalsProdi;

        $finalsDownloadUrl = route('admin.download.data-mahasiswa-finals', $finalsParams);

        $finalsBtnLabel = 'Download';
        if ($finalsJenjang !== 'all') $finalsBtnLabel .= ' ' . $finalsJenjang;
        if ($finalsProdi   !== 'all') {
            $prodiObj = ($programStudi ?? collect())->firstWhere('kode_prodi', $finalsProdi);
            $finalsBtnLabel .= ' - ' . ($prodiObj ? $prodiObj->nama_prodi : $finalsProdi);
        }
        if ($finalsJenjang === 'all' && $finalsProdi === 'all') $finalsBtnLabel .= ' Semua Data';
    @endphp

    <a href="{{ $finalsDownloadUrl }}" class="cyberpunk-download-btn cyberpunk-btn-info mt-auto" style="margin-top: 16px;">
        <svg class="cyberpunk-download-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
        </svg>
        {{ $finalsBtnLabel }}
    </a>
</div>

<script>
function updateFinalsProdiFilter(prodi) {
    const url = new URL(window.location.href);
    if (prodi === 'all') {
        url.searchParams.delete('prodi');
    } else {
        url.searchParams.set('prodi', prodi);
    }
    window.location.href = url.toString();
}
</script>

@endif
       

          @if(!in_array(auth()->user()->role, ['admin_akademik', 'admin_farmasi']))
        <div class="cyberpunk-card cyberpunk-card-warning">
            <div class="cyberpunk-card-header">
                <div class="cyberpunk-card-icon">
                    <svg class="cyberpunk-card-icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                    </svg>
                </div>
                <span class="cyberpunk-card-badge cyberpunk-badge-warning">ZIP</span>
            </div>
            
            <h3 class="cyberpunk-card-title">Arsip Data Excel</h3>
            <p class="cyberpunk-card-description">
                Paket ZIP berisi file Excel Kelulusan dan Pendaftaran.
            </p>
            
            <div class="cyberpunk-card-features">
                <div class="cyberpunk-feature">
                    <svg class="cyberpunk-feature-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Kelulusan + Pendaftaran</span>
                </div>
            </div>
            
            <a href="{{ route('admin.download.all') }}" class="cyberpunk-download-btn cyberpunk-btn-warning mt-auto">
                <svg class="cyberpunk-download-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Download ZIP Excel
            </a>
        </div>
@endif
        <div class="cyberpunk-card cyberpunk-card-danger" style="grid-column: 1 / -1;">
            <div class="cyberpunk-card-header">
                <div class="cyberpunk-card-icon">
                    <svg class="cyberpunk-card-icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <span class="cyberpunk-card-badge cyberpunk-badge-danger">FULL ARCHIVE</span>
            </div>
            
            <div style="display: flex; flex-direction: column; align-items: center; text-align: center;">
                <h3 class="cyberpunk-card-title">Master Archive (Data + Berkas PDF)</h3>
                <p class="cyberpunk-card-description" style="max-width: 600px;">
                    Download database lengkap termasuk Excel dan seluruh file berkas fisik (KK, Ijazah, KTP) yang diupload mahasiswa. 
                    <span style="color: #ff4d4d; font-weight: bold; display: block; margin-top: 5px;">Warning: Ukuran file mungkin sangat besar.</span>
                </p>
                
                <a href="{{ route('admin.download.with-pdf') }}" class="cyberpunk-download-btn cyberpunk-btn-danger" style="width: auto; padding-left: 3rem; padding-right: 3rem; margin-top: 1rem;">
                    <svg class="cyberpunk-download-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    GENERATE & DOWNLOAD FULL ARCHIVE
                </a>
            </div>
        </div>
    </div>

    <div class="cyberpunk-info-section">
        <div class="cyberpunk-info-card">
            <div class="cyberpunk-info-icon">
                <svg class="cyberpunk-info-icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="cyberpunk-info-content">
                <h3 class="cyberpunk-info-title">System Status</h3>
                <ul class="cyberpunk-info-list">
                    <li class="cyberpunk-info-item">Storage Status: <span style="color: #00ff9d">Safe</span></li>
                    <li class="cyberpunk-info-item">Auto-Delete: <span style="color: #00ff9d">Enabled (After Download)</span></li>
                    <li class="cyberpunk-info-item">Pastikan koneksi internet stabil untuk download Full Archive.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<style>
/* Cyberpunk Theme Variables */
:root {
    --cyberpunk-primary: #465fff;
    --cyberpunk-secondary: #7a5af8;
    --cyberpunk-accent: #ee46bc;
    --cyberpunk-success: #12b76a;
    --cyberpunk-warning: #f79009;
    --cyberpunk-error: #f04438;
    --cyberpunk-purple: #7a5af8;
    --cyberpunk-gray: #667085;
    --cyberpunk-dark: #101828;
    --cyberpunk-light: #f9fafb;
    --cyberpunk-border: rgba(70, 95, 255, 0.2);
    --cyberpunk-glow: 0 0 20px rgba(70, 95, 255, 0.3);
    --cyberpunk-info: #0ba5ec;
}

/* Main Container */
.cyberpunk-download-container {
    position: relative;
    padding: 1.5rem;
    background: linear-gradient(135deg, rgba(70, 95, 255, 0.05) 0%, rgba(122, 90, 248, 0.05) 100%);
    border-radius: 0.75rem;
    border: 1px solid var(--cyberpunk-border);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.cyberpunk-download-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg, var(--cyberpunk-primary), var(--cyberpunk-secondary), var(--cyberpunk-accent));
    animation: cyberpunk-scanline 3s linear infinite;
}

@keyframes cyberpunk-scanline {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

/* Breadcrumb */
.cyberpunk-breadcrumb {
    margin-bottom: 1.5rem;
}

.cyberpunk-breadcrumb-list {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: var(--cyberpunk-gray);
}

.cyberpunk-breadcrumb-link {
    color: var(--cyberpunk-gray);
    transition: color 0.3s ease;
}

.cyberpunk-breadcrumb-link:hover {
    color: var(--cyberpunk-primary);
}

.cyberpunk-breadcrumb-separator {
    color: var(--cyberpunk-gray);
}

.cyberpunk-breadcrumb-active {
    color: var(--cyberpunk-primary);
    font-weight: 500;
}

/* Header */
.cyberpunk-header {
    margin-bottom: 2rem;
    position: relative;
}

.cyberpunk-header-content {
    margin-bottom: 1rem;
}

.cyberpunk-title {
    font-size: 1.875rem;
    font-weight: 700;
    background: linear-gradient(90deg, var(--cyberpunk-primary), var(--cyberpunk-secondary));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    text-shadow: 0 0 10px rgba(70, 95, 255, 0.3);
    margin-bottom: 0.5rem;
}

.cyberpunk-subtitle {
    color: var(--cyberpunk-gray);
    font-size: 1rem;
}

.cyberpunk-header-glow {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, var(--cyberpunk-accent), transparent);
    animation: cyberpunk-glow-pulse 2s ease-in-out infinite;
}

@keyframes cyberpunk-glow-pulse {
    0%, 100% { opacity: 0.5; }
    50% { opacity: 1; }
}

/* Cards Grid */
.cyberpunk-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

/* Card Styles */
.cyberpunk-card {
    background: rgba(255, 255, 255, 0.7);
    border: 1px solid var(--cyberpunk-border);
    border-radius: 0.75rem;
    padding: 1.5rem;
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.cyberpunk-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(70, 95, 255, 0.1), transparent);
    transition: left 0.5s ease;
}

.cyberpunk-card:hover::before {
    left: 100%;
}

.cyberpunk-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--cyberpunk-glow);
}

/* Card Variants */
.cyberpunk-card-success {
    border-color: rgba(18, 183, 106, 0.3);
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.8), rgba(18, 183, 106, 0.05));
}

.cyberpunk-card-primary {
    border-color: rgba(70, 95, 255, 0.3);
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.8), rgba(70, 95, 255, 0.05));
}

.cyberpunk-card-info {
    border-color: rgba(11, 165, 236, 0.3);
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.8), rgba(11, 165, 236, 0.05));
}

.cyberpunk-card-purple {
    border-color: rgba(122, 90, 248, 0.3);
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.8), rgba(122, 90, 248, 0.05));
}

.cyberpunk-card-warning {
    border-color: rgba(247, 144, 9, 0.3);
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.8), rgba(247, 144, 9, 0.05));
}

.cyberpunk-card-danger {
    border-color: rgba(240, 68, 56, 0.3);
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.8), rgba(240, 68, 56, 0.05));
}

/* Card Header */
.cyberpunk-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
}

.cyberpunk-card-icon {
    width: 3rem;
    height: 3rem;
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(70, 95, 255, 0.1);
    color: var(--cyberpunk-primary);
}

.cyberpunk-card-success .cyberpunk-card-icon {
    background: rgba(18, 183, 106, 0.1);
    color: var(--cyberpunk-success);
}

.cyberpunk-card-primary .cyberpunk-card-icon {
    background: rgba(70, 95, 255, 0.1);
    color: var(--cyberpunk-primary);
}

.cyberpunk-card-info .cyberpunk-card-icon {
    background: rgba(11, 165, 236, 0.1);
    color: var(--cyberpunk-info);
}

.cyberpunk-card-purple .cyberpunk-card-icon {
    background: rgba(122, 90, 248, 0.1);
    color: var(--cyberpunk-purple);
}

.cyberpunk-card-warning .cyberpunk-card-icon {
    background: rgba(247, 144, 9, 0.1);
    color: var(--cyberpunk-warning);
}

.cyberpunk-card-danger .cyberpunk-card-icon {
    background: rgba(240, 68, 56, 0.1);
    color: var(--cyberpunk-error);
}

.cyberpunk-card-icon-svg {
    width: 1.5rem;
    height: 1.5rem;
}

/* Card Badge */
.cyberpunk-card-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.cyberpunk-badge-success {
    background: rgba(18, 183, 106, 0.1);
    color: var(--cyberpunk-success);
    border: 1px solid rgba(18, 183, 106, 0.3);
}

.cyberpunk-badge-primary {
    background: rgba(70, 95, 255, 0.1);
    color: var(--cyberpunk-primary);
    border: 1px solid rgba(70, 95, 255, 0.3);
}

.cyberpunk-badge-info {
    background: rgba(11, 165, 236, 0.1);
    color: var(--cyberpunk-info);
    border: 1px solid rgba(11, 165, 236, 0.3);
}

.cyberpunk-badge-purple {
    background: rgba(122, 90, 248, 0.1);
    color: var(--cyberpunk-purple);
    border: 1px solid rgba(122, 90, 248, 0.3);
}

.cyberpunk-badge-warning {
    background: rgba(247, 144, 9, 0.1);
    color: var(--cyberpunk-warning);
    border: 1px solid rgba(247, 144, 9, 0.3);
}

.cyberpunk-badge-danger {
    background: rgba(240, 68, 56, 0.1);
    color: var(--cyberpunk-error);
    border: 1px solid rgba(240, 68, 56, 0.3);
}

/* Card Content */
.cyberpunk-card-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--cyberpunk-dark);
    margin-bottom: 0.5rem;
}

.cyberpunk-card-description {
    color: var(--cyberpunk-gray);
    font-size: 0.875rem;
    line-height: 1.5;
    margin-bottom: 1rem;
}

/* Filter Section */
.cyberpunk-filter-section {
    margin-bottom: 1rem;
}

.cyberpunk-filter-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--cyberpunk-dark);
    margin-bottom: 0.5rem;
}

.cyberpunk-filter-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.cyberpunk-filter-btn {
    padding: 0.5rem 0.75rem;
    border-radius: 0.5rem;
    font-size: 0.75rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.3s ease;
    border: 1px solid transparent;
}

.cyberpunk-filter-inactive {
    background: rgba(102, 112, 133, 0.1);
    color: var(--cyberpunk-gray);
    border-color: rgba(102, 112, 133, 0.2);
}

.cyberpunk-filter-inactive:hover {
    background: rgba(102, 112, 133, 0.2);
    color: var(--cyberpunk-dark);
}

.cyberpunk-filter-active {
    color: white;
    border-color: transparent;
}

.cyberpunk-filter-active {
    background: var(--cyberpunk-primary);
}

.cyberpunk-filter-success {
    background: var(--cyberpunk-success);
}

.cyberpunk-filter-warning {
    background: var(--cyberpunk-warning);
}

.cyberpunk-filter-error {
    background: var(--cyberpunk-error);
}

/* Card Features */
.cyberpunk-card-features {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
}

.cyberpunk-feature {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: var(--cyberpunk-gray);
}

.cyberpunk-feature-icon {
    width: 1rem;
    height: 1rem;
    flex-shrink: 0;
}

/* Download Button */
.cyberpunk-download-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    width: 100%;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.cyberpunk-download-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s ease;
}

.cyberpunk-download-btn:hover::before {
    left: 100%;
}

.cyberpunk-download-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.cyberpunk-btn-success {
    background: linear-gradient(135deg, var(--cyberpunk-success), #039855);
    color: white;
}

.cyberpunk-btn-success:hover {
    background: linear-gradient(135deg, #0da366, #027a48);
}

.cyberpunk-btn-primary {
    background: linear-gradient(135deg, var(--cyberpunk-primary), var(--cyberpunk-secondary));
    color: white;
}

.cyberpunk-btn-primary:hover {
    background: linear-gradient(135deg, #3641f5, #6a55f7);
}

.cyberpunk-btn-info {
    background: linear-gradient(135deg, var(--cyberpunk-info), #0891b2);
    color: white;
}

.cyberpunk-btn-info:hover {
    background: linear-gradient(135deg, #0e7490, #0369a1);
}

.cyberpunk-btn-warning {
    background: linear-gradient(135deg, var(--cyberpunk-warning), #dc6803);
    color: white;
}

.cyberpunk-btn-warning:hover {
    background: linear-gradient(135deg, #e58a0e, #b54708);
}

.cyberpunk-btn-danger {
    background: linear-gradient(135deg, var(--cyberpunk-error), #d92d20);
    color: white;
}

.cyberpunk-btn-danger:hover {
    background: linear-gradient(135deg, #f97066, #b42318);
}

.cyberpunk-download-icon {
    width: 1rem;
    height: 1rem;
}

/* Information Section */
.cyberpunk-info-section {
    margin-top: 2rem;
}

.cyberpunk-info-card {
    background: rgba(11, 165, 236, 0.05);
    border: 1px solid rgba(11, 165, 236, 0.3);
    border-radius: 0.75rem;
    padding: 1.5rem;
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    backdrop-filter: blur(10px);
    position: relative;
    overflow: hidden;
}

.cyberpunk-info-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, var(--cyberpunk-primary), transparent);
}

.cyberpunk-info-icon {
    width: 1.25rem;
    height: 1.25rem;
    color: #0ba5ec;
    flex-shrink: 0;
    margin-top: 0.125rem;
}

.cyberpunk-info-icon-svg {
    width: 100%;
    height: 100%;
}

.cyberpunk-info-content {
    flex: 1;
}

.cyberpunk-info-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #0ba5ec;
    margin-bottom: 0.75rem;
}

.cyberpunk-info-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.cyberpunk-info-item {
    font-size: 0.875rem;
    color: #0ba5ec;
    margin-bottom: 0.25rem;
    line-height: 1.5;
}

/* Dark Mode Support */
.dark .cyberpunk-download-container {
    background: linear-gradient(135deg, rgba(70, 95, 255, 0.1) 0%, rgba(122, 90, 248, 0.1) 100%);
    border: 1px solid rgba(70, 95, 255, 0.3);
}

.dark .cyberpunk-card {
    background: rgba(16, 24, 40, 0.5);
    color: #f9fafb;
}

.dark .cyberpunk-card-title {
    color: #f9fafb;
}

.dark .cyberpunk-card-description {
    color: #d1d5db;
}

.dark .cyberpunk-feature {
    color: #d1d5db;
}

.dark .cyberpunk-filter-label {
    color: #f9fafb;
}

.dark .cyberpunk-filter-inactive {
    background: rgba(156, 163, 175, 0.1);
    color: #d1d5db;
    border-color: rgba(156, 163, 175, 0.2);
}

.dark .cyberpunk-filter-inactive:hover {
    background: rgba(156, 163, 175, 0.2);
    color: #f9fafb;
}

/* Responsive Design */
@media (max-width: 768px) {
    .cyberpunk-cards-grid {
        grid-template-columns: 1fr;
    }
    
    .cyberpunk-download-container {
        padding: 1rem;
    }
    
    .cyberpunk-filter-buttons {
        flex-direction: column;
    }
    
    .cyberpunk-filter-btn {
        text-align: center;
    }
}

/* Animation for cards */
@keyframes cyberpunk-card-enter {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.cyberpunk-card {
    animation: cyberpunk-card-enter 0.5s ease-out;
}

.cyberpunk-card:nth-child(1) { animation-delay: 0.1s; }
.cyberpunk-card:nth-child(2) { animation-delay: 0.2s; }
.cyberpunk-card:nth-child(3) { animation-delay: 0.3s; }
.cyberpunk-card:nth-child(4) { animation-delay: 0.4s; }
.cyberpunk-card:nth-child(5) { animation-delay: 0.5s; }

.mt-auto {
    margin-top: auto;
}

.mt-2 {
    margin-top: 0.5rem;
}
</style>
@endsection