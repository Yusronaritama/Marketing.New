@extends('layouts.admin.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        {{-- ===================== NOTIFIKASI ===================== --}}
        @if (session('success'))
            <div class="mb-4 p-4 rounded-lg flex items-center gap-3"
                 style="background:rgba(45,107,82,.12);border:1px solid rgba(45,107,82,.35);border-left:3px solid #4A8C74;color:#2D6B52;">
                <span style="font-size:16px;">✅</span>
                <span style="font-size:13px;font-weight:500;">{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-4 rounded-lg flex items-center gap-3"
                 style="background:rgba(140,80,30,.12);border:1px solid rgba(140,80,30,.35);border-left:3px solid #8C6D3F;color:#5C3D18;">
                <span style="font-size:16px;">❌</span>
                <span style="font-size:13px;font-weight:500;">{{ session('error') }}</span>
            </div>
        @endif

        {{-- ===================== HEADER STATISTIK ===================== --}}
        <div class="dashboard-card mb-6">
            <div class="p-6" style="border-bottom:1px solid #D4C9A8;">
                <h3 style="font-size:18px;font-weight:700;color:#1E4A38;margin-bottom:2px;">
                    Selamat datang, {{ Auth::user()->name }}!
                </h3>
                <p style="font-size:12px;color:#7A9E8E;margin-bottom:20px;">
                    Kelola pendaftaran Profesi Apoteker.
                </p>

                <div class="stat-grid">
                    <div class="stat-card stat-teal">
                        <div class="stat-label">Total Pendaftar</div>
                        <div class="stat-number">{{ $totalPendaftar }}<span class="stat-unit">Akun</span></div>
                    </div>
                    <div class="stat-card stat-green">
                        <div class="stat-label">Lunas (Paid)</div>
                        <div class="stat-number">{{ $totalLunas }}<span class="stat-unit">Akun</span></div>
                    </div>
                    <div class="stat-card stat-amber">
                        <div class="stat-label">Menunggu Pembayaran</div>
                        <div class="stat-number">{{ $totalPending }}<span class="stat-unit">Akun</span></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===================== TABEL PENDAFTAR ===================== --}}
        <div class="dashboard-card">

            {{-- ---- Toolbar ---- --}}
            <div class="toolbar">
                <div>
                    <div class="toolbar-title">Daftar Pendaftar Apoteker</div>
                    <div class="toolbar-sub">
                        Menampilkan {{ $pendaftarApoteker->firstItem() ?? 0 }}–{{ $pendaftarApoteker->lastItem() ?? 0 }}
                        dari {{ $pendaftarApoteker->total() }} pendaftar
                    </div>
                </div>
                <div class="toolbar-right">

                    {{-- Form Filter Terpadu (Alumni & Dropdown Gelombang dari DB) --}}
                    <form action="{{ route('admin.profesi.dashboard') }}" method="GET" id="searchFilterForm" class="flex items-center gap-2 m-0">
                        <input type="hidden" name="per_page" value="{{ $perPage }}">
                        
                        {{-- Filter Alumni --}}
                        <select name="filter_alumni" class="dash-select" onchange="document.getElementById('searchFilterForm').submit()">
                            <option value="">Semua Status</option>
                            <option value="alumni"     {{ request('filter_alumni') == 'alumni'     ? 'selected' : '' }}>Alumni</option>
                            <option value="non_alumni" {{ request('filter_alumni') == 'non_alumni' ? 'selected' : '' }}>Umum</option>
                        </select>

                        {{-- PERBAIKAN FILTER: Dropdown Filter Gelombang Dinamis --}}
                        <select name="filter_gelombang" class="dash-select" onchange="document.getElementById('searchFilterForm').submit()">
                            <option value="">Semua Gelombang</option>
                            @foreach($listGelombang as $gel)
                                <option value="{{ $gel->id }}" {{ request('filter_gelombang') == $gel->id ? 'selected' : '' }}>
                                    {{ $gel->nama_gelombang }}
                                </option>
                            @endforeach
                        </select>
                    </form>

                    {{-- Per-page selector --}}
                    <form action="{{ route('admin.profesi.dashboard') }}" method="GET" id="perPageForm">
                        <input type="hidden" name="filter_alumni" value="{{ request('filter_alumni') }}">
                        <input type="hidden" name="filter_gelombang" value="{{ request('filter_gelombang') }}">
                        <select name="per_page" class="dash-select" onchange="document.getElementById('perPageForm').submit()">
                            <option value="10"  {{ $perPage == 10    ? 'selected' : '' }}>10 / halaman</option>
                            <option value="25"  {{ $perPage == 25    ? 'selected' : '' }}>25 / halaman</option>
                            <option value="50"  {{ $perPage == 50    ? 'selected' : '' }}>50 / halaman</option>
                            <option value="100" {{ $perPage == 100   ? 'selected' : '' }}>100 / halaman</option>
                            <option value="all" {{ $perPage == 'all' ? 'selected' : '' }}>Semua data</option>
                        </select>
                    </form>

                    {{-- Export --}}
                    <a href="{{ route('admin.profesi.export', ['filter_alumni' => request('filter_alumni'), 'filter_gelombang' => request('filter_gelombang')]) }}"
                       class="btn-export">
                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Export XLSX
                    </a>
                    <a href="{{ route('admin.profesi.download.semua', ['filter_alumni' => request('filter_alumni'), 'filter_gelombang' => request('filter_gelombang')]) }}" 
                       class="inline-flex items-center px-4 py-2 bg-sky-700 text-white rounded-lg font-bold text-sm hover:bg-sky-800 transition shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        Download Semua
                    </a>
                </div>
            </div>

            {{-- ---- Tabel ---- --}}
            <div style="overflow-x:auto;">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th style="width:44px;">No</th>
                            <th>Nama</th>
                            <th>Gelombang</th>
                            <th>Kontak</th>
                            <th>Status</th>
                            <th>Berkas</th>
                            <th>Verifikasi</th>
                            <th style="text-align:center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pendaftarApoteker as $index => $user)

                        @php
                            $berkas    = $semuaBerkas->get($user->id);
                            $adaBerkas = $berkas && (
                                $berkas->file_akreditasi  || $berkas->file_ijazah     ||
                                $berkas->file_transkrip   || $berkas->file_pasfoto    ||
                                $berkas->file_ktp         || $berkas->file_kk         ||
                                $berkas->file_asuransi    || $berkas->file_surat_sehat
                            );
                            $statusVerif = $berkas->status_verifikasi ?? 'belum';

                            // PERBAIKAN SINKRONISASI DATA: Ambil ID Gelombang dari masterUser, lalu cari nama gelombangnya
                            $userGelombangId = $user->masterUser->gelombang ?? null;
                            $namaGelombangTerdaftar = 'Gelombang 1'; // default aman
                            
                            if($userGelombangId) {
                                $findGel = $listGelombang->firstWhere('id', $userGelombangId);
                                if($findGel) {
                                    $namaGelombangTerdaftar = $findGel->nama_gelombang;
                                }
                            }
                        @endphp

                        {{-- Baris utama --}}
                        <tr class="dash-row">
                            {{-- No --}}
                            <td style="color:#7A9E8E;font-size:13px;">
                                {{ $pendaftarApoteker->firstItem() + $index }}
                            </td>

                            {{-- Nama --}}
                            <td>
                                <p style="font-size:13px;font-weight:600;color:#1E4A38;margin:0;">{{ $user->name }}</p>
                                @if($user->nim)
                                    <p style="font-size:11px;color:#4A8C74;margin:2px 0 0;">NIM: {{ $user->nim }}</p>
                                @endif
                            </td>

                            {{-- PERBAIKAN TAMPILAN: Menampilkan nama gelombang yang dinamis berdasarkan DB --}}
                            <td class="px-6 py-4 text-sm">
                                <span class="px-2 py-1 bg-emerald-100 text-emerald-800 border border-emerald-300 rounded-full text-xs font-bold shadow-sm">
                                    {{ $namaGelombangTerdaftar }}
                                </span>
                            </td>

                            {{-- Kontak --}}
                            <td>
                                <p style="font-size:11px;color:#3D5E50;margin:0;">{{ $user->email }}</p>
                                @if($user->phone)
                                    <p style="font-size:11px;color:#7A9E8E;margin:2px 0 0;">{{ $user->phone }}</p>
                                @endif
                            </td>

                            {{-- Status alumni --}}
                            <td>
                                @if($user->nim)
                                    <span class="badge badge-alumni">🎓 Alumni</span>
                                @else
                                    <span class="badge badge-umum">👤 Umum</span>
                                @endif
                            </td>

                            {{-- Berkas toggle --}}
                            <td>
                                @if($adaBerkas)
                                    <button type="button"
                                            class="berkas-toggle"
                                            data-user-id="{{ $user->id }}"
                                            onclick="toggleBerkasDropdown(event, {{ $user->id }})">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        Berkas
                                        <svg class="chevron-icon" width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                             style="transition:transform .2s;">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                @else
                                    <span style="font-size:11px;color:#7A9E8E;font-style:italic;">Belum ada</span>
                                @endif
                            </td>

                            <td>
                                @if(!$adaBerkas)
                                    <span style="font-size:12px;color:#7A9E8E;">—</span>
                                @elseif($statusVerif === 'lulus')
                                    <div style="display:flex;flex-direction:column;gap:6px;">
                                        <span class="badge badge-verified">✅ Terverifikasi</span>
                                        <form action="{{ route('admin.profesi.berkas.batal', $user->id) }}" method="POST"
                                             onsubmit="return confirm('Yakin ingin membatalkan verifikasi {{ addslashes($user->name) }}?')">
                                            @csrf @method('PATCH')
                                            <button type="submit" style="font-size:10px; color:#a32626; text-decoration:underline; border:none; background:none; cursor:pointer;">
                                                Batalkan Verifikasi
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <div style="display:flex;flex-direction:column;gap:6px;">
                                        <span class="badge badge-pending">⏳ Belum</span>
                                        <form action="{{ route('admin.profesi.berkas.verifikasi', $user->id) }}" method="POST"
                                             onsubmit="return confirm('Verifikasi berkas {{ addslashes($user->name) }}?')">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn-verify">
                                                <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                Verifikasi
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </td>

                            {{-- Hapus --}}
                            <td style="text-align:center;">
                                <form action="{{ route('admin.profesi.users.destroy', $user->id) }}" method="POST"
                                      onsubmit="return confirm('Hapus akun {{ addslashes($user->name) }} beserta semua datanya?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-delete">
                                        <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>

                        {{-- ---- Baris dropdown berkas ---- --}}
                        @if($adaBerkas)
                        <tr class="berkas-dropdown-row" id="berkas-dropdown-{{ $user->id }}" style="display:none;">
                            <td colspan="8" style="padding:0;border:none;">
                                <div class="berkas-dropdown-inner">
                                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
                                        <p style="font-size:10px;color:#7A9E8E;font-weight:700;text-transform:uppercase;letter-spacing:.1em;margin:0;">
                                            📁 Dokumen Pendaftaran — {{ $user->name }}
                                        </p>
                                    
                                        @if($statusVerif === 'lulus')
                                            <span style="font-size:11px; color:#4A8C74; font-weight:700; display:flex; align-items:center; gap:4px;">
                                                <svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                                Berkas Terverifikasi (Lulus)
                                            </span>
                                        @else
                                            <form action="{{ route('admin.profesi.berkas.verifikasi', $user->id) }}" method="POST"
                                                  onsubmit="return confirm('Verifikasi berkas {{ addslashes($user->name) }}?')">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="btn-verify">
                                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    Verifikasi Semua Berkas
                                                </button>
                                            </form>
                                        @endif
                                    </div>

                                    <div class="berkas-grid">
                                        @php
                                            $daftarBerkas = [
                                                'file_akreditasi'  => ['label' => 'Akreditasi',    'icon' => '🏛️'],
                                                'file_ijazah'      => ['label' => 'Ijazah',         'icon' => '🎓'],
                                                'file_transkrip'   => ['label' => 'Transkrip',      'icon' => '📋'],
                                                'file_pasfoto'     => ['label' => 'Pas Foto',       'icon' => '🖼️'],
                                                'file_ktp'         => ['label' => 'KTP',            'icon' => '🪪'],
                                                'file_kk'          => ['label' => 'Kartu Keluarga', 'icon' => '👨‍👩‍👧'],
                                                'file_asuransi'    => ['label' => 'Asuransi',       'icon' => '🛡️'],
                                                'file_surat_sehat' => ['label' => 'Surat Sehat',    'icon' => '🏥'],
                                            ];
                                        @endphp

                                        @foreach($daftarBerkas as $col => $info)
                                            @php
                                                $filePath = $berkas->$col ?? null;
                                                $ext      = $filePath ? strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) : null;
                                                $isImage  = in_array($ext, ['jpg','jpeg','png','webp']);
                                                $fileUrl  = $filePath ? asset('storage/' . $filePath) : null;
                                            @endphp

                                            @if($filePath)
                                            <div class="berkas-card"
                                                 onclick="openModal('{{ $fileUrl }}', '{{ $info['label'] }}', '{{ $ext }}')">
                                                <div class="berkas-thumb">
                                                    @if($isImage)
                                                        <img src="{{ $fileUrl }}" alt="{{ $info['label'] }}" loading="lazy">
                                                    @else
                                                        <div class="pdf-thumb-wrap">
                                                            <iframe src="{{ $fileUrl }}#toolbar=0&navpanes=0&scrollbar=0&view=FitH"
                                                                    scrolling="no" loading="lazy" title="{{ $info['label'] }}"></iframe>
                                                        </div>
                                                    @endif
                                                    <div class="berkas-overlay"><span>Lihat Dokumen</span></div>
                                                </div>
                                                <div class="berkas-info">
                                                    <span class="berkas-icon">{{ $info['icon'] }}</span>
                                                    <div>
                                                        <p class="berkas-label">{{ $info['label'] }}</p>
                                                        <p class="berkas-type">{{ strtoupper($ext) }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            @else
                                            <div class="berkas-card berkas-empty">
                                                <div class="berkas-thumb berkas-thumb-empty">
                                                    <span class="empty-icon">—</span>
                                                </div>
                                                <div class="berkas-info" style="opacity:.45;">
                                                    <span class="berkas-icon">{{ $info['icon'] }}</span>
                                                    <div>
                                                        <p class="berkas-label">{{ $info['label'] }}</p>
                                                        <p class="berkas-type" style="color:#A09070;">Belum upload</p>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endif

                        @empty
                        <tr>
                            <td colspan="8" style="text-align:center;padding:60px 16px;">
                                <p style="font-size:36px;margin-bottom:10px;">📭</p>
                                <p style="font-size:13px;color:#7A9E8E;">Tidak ada data pendaftar</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ---- Pagination ---- --}}
            @if($perPage !== 'all')
            <div class="pager">
                <p style="font-size:11px;color:#7A9E8E;margin:0;">
                    Halaman <strong style="color:#1E4A38;">{{ $pendaftarApoteker->currentPage() }}</strong>
                    dari <strong style="color:#1E4A38;">{{ $pendaftarApoteker->lastPage() }}</strong>
                    &nbsp;·&nbsp;
                    Total <strong style="color:#1E4A38;">{{ $pendaftarApoteker->total() }}</strong> pendaftar
                </p>
                <div class="pagination-wrap">
                    {{ $pendaftarApoteker->appends(['filter_alumni' => request('filter_alumni'), 'filter_gelombang' => request('filter_gelombang'), 'per_page' => $perPage])->links() }}
                </div>
            </div>
            @else
            <div class="pager" style="justify-content:center;">
                <p style="font-size:11px;color:#7A9E8E;margin:0;">
                    Menampilkan semua <strong style="color:#1E4A38;">{{ $pendaftarApoteker->total() }}</strong> pendaftar
                </p>
            </div>
            @endif

        </div>
    </div>
</div>

{{-- ===================== MODAL PREVIEW DOKUMEN ===================== --}}
<div id="docModal" class="doc-modal" onclick="handleModalClick(event)">
    <div class="doc-modal-inner" onclick="event.stopPropagation()">
        <div class="doc-modal-header">
            <div style="display:flex;align-items:center;gap:12px;">
                <span id="modalIcon" style="font-size:22px;line-height:1;"></span>
                <div>
                    <h2 id="modalTitle" style="font-size:15px;font-weight:700;color:#1E4A38;margin:0;line-height:1.3;">Preview</h2>
                    <p  id="modalType"  style="font-size:10px;color:#7A9E8E;margin:2px 0 0;"></p>
                </div>
            </div>
            <div style="display:flex;gap:8px;align-items:center;">
                <a id="modalDownload" href="#" download target="_blank" class="btn-modal-dl">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Download
                </a>
                <button onclick="closeModal()" class="btn-modal-close">✕</button>
            </div>
        </div>
        <div class="doc-modal-body">
            <iframe id="modalIframe" src="" title="Preview"></iframe>
            <img    id="modalImg"    src="" alt="Preview">
        </div>
    </div>
</div>

{{-- ===================== CSS ===================== --}}
<style>
/*
 * PALET WARNA:
 * Farmasi  : #2D6B52 (deep), #4A8C74 (mid), #89B4A8 (tosca), #C8DDD4 (mint), #B7CCB8 (sage)
 * Pasir    : #F2EBD5 (cream), #D4B483 (sand), #8C6D3F (amber garam)
 * Telur Asin: #89B4A8 (tosca shell), #B7CCB8 (hijau shell)
 */

:root {
    --pharma-deep:   #2D6B52;
    --pharma-mid:    #4A8C74;
    --pharma-light:  #89B4A8;
    --pharma-mint:   #C8DDD4;
    --pharma-sage:   #B7CCB8;

    --sand-cream:    #F2EBD5;
    --sand-beach:    #D4B483;
    --sand-amber:    #8C6D3F;
    --sand-dark:     #4A3015;

    --bg-page:       #EEE8D8;
    --bg-card:       #F9F5EE;
    --bg-toolbar:    #2D6B52;
    --border:        #C9BFA0;
    --border-light:  #DDD3B8;
    --text-primary:  #1E4A38;
    --text-sec:      #4A6B5A;
    --text-muted:    #7A9E8E;
}

/* ===== Layout card ===== */
.dashboard-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(45,107,82,.08);
}

/* ===== Stat cards ===== */
.stat-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}
@media (max-width: 640px) { .stat-grid { grid-template-columns: 1fr; } }

.stat-card  { padding: 16px; border-radius: 10px; border: 1px solid; }
.stat-teal  { background: linear-gradient(135deg, #2D6B52, #1E4A38); border-color: #4A8C74; }
.stat-green { background: linear-gradient(135deg, #4A8C74, #2D6B52); border-color: #89B4A8; }
.stat-amber { background: linear-gradient(135deg, #8C6D3F, #5C3D18); border-color: #D4B483; }

.stat-label  { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; margin-bottom: 6px; }
.stat-teal  .stat-label  { color: #C8DDD4; }
.stat-green .stat-label  { color: #C8DDD4; }
.stat-amber .stat-label  { color: #F2EBD5; }

.stat-number { font-size: 28px; font-weight: 800; color: #ffffff; line-height: 1; }
.stat-unit   { font-size: 11px; font-weight: 400; margin-left: 4px; }
.stat-teal  .stat-unit   { color: #89B4A8; }
.stat-green .stat-unit   { color: #B7CCB8; }
.stat-amber .stat-unit   { color: #D4B483; }

/* ===== Toolbar ===== */
.toolbar {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 12px;
    padding: 14px 20px;
    background: var(--bg-toolbar);
    border-bottom: 1px solid #1E4A38;
}
.toolbar-title { font-size: 15px; font-weight: 700; color: #F2EBD5; }
.toolbar-sub   { font-size: 10px; color: #89B4A8; margin-top: 2px; }
.toolbar-right { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }

.dash-select {
    background: #1E4A38;
    border: 1px solid #4A8C74;
    border-radius: 7px;
    color: #C8DDD4;
    font-family: inherit;
    font-size: 11px;
    padding: 6px 10px;
    outline: none;
    cursor: pointer;
}
.dash-select:focus { border-color: #D4B483; }

.btn-export {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: #8C6D3F;
    color: #F2EBD5;
    padding: 6px 12px;
    border-radius: 7px;
    font-size: 11px;
    font-weight: 700;
    text-decoration: none;
    border: 1px solid #D4B483;
    transition: background .15s;
}
.btn-export:hover { background: #A07840; color: #fff; }

/* ===== Tabel ===== */
.dash-table { width: 100%; border-collapse: collapse; }
.dash-table thead { background: #EDE7D5; }
.dash-table th {
    padding: 10px 14px;
    text-align: left;
    font-size: 10px;
    font-weight: 700;
    color: #4A6B5A;
    text-transform: uppercase;
    letter-spacing: .07em;
    white-space: nowrap;
    border-bottom: 2px solid #C9BFA0;
}
.dash-table tbody tr { border-top: 1px solid var(--border-light); }
.dash-row:hover { background: rgba(137,180,168,.12); }
.dash-table td { padding: 12px 14px; vertical-align: middle; }

/* ===== Badge ===== */
.badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 2px 8px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 700;
    white-space: nowrap;
}
.badge-alumni   { background: rgba(45,107,82,.15);  color: #1E4A38; border: 1px solid rgba(74,140,116,.4); }
.badge-umum     { background: rgba(212,180,131,.2); color: #5C3D18; border: 1px solid #D4B483; }
.badge-verified { background: rgba(74,140,116,.2);  color: #2D6B52; border: 1px solid rgba(74,140,116,.5); }
.badge-pending  { background: rgba(140,109,63,.2);  color: #5C3D18; border: 1px solid rgba(140,109,63,.5); }

/* ===== Tombol aksi ===== */
.berkas-toggle {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: #4A8C74;
    color: #fff;
    border: none;
    padding: 5px 10px;
    border-radius: 7px;
    font-family: inherit;
    font-size: 10px;
    font-weight: 700;
    cursor: pointer;
    transition: background .15s;
}
.berkas-toggle:hover { background: #2D6B52; }
.berkas-toggle.active .chevron-icon { transform: rotate(180deg); }

.btn-verify {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: rgba(45,107,82,.12);
    border: 1px solid #4A8C74;
    color: #2D6B52;
    padding: 5px 10px;
    border-radius: 7px;
    font-family: inherit;
    font-size: 10px;
    font-weight: 700;
    cursor: pointer;
    transition: background .15s, color .15s;
    white-space: nowrap;
}
.btn-verify:hover { background: #2D6B52; color: #F2EBD5; border-color: #2D6B52; }

.btn-delete {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: transparent;
    border: 1px solid rgba(140,109,63,.4);
    color: #8C6D3F;
    padding: 5px 10px;
    border-radius: 7px;
    font-family: inherit;
    font-size: 10px;
    font-weight: 700;
    cursor: pointer;
    transition: background .15s, color .15s, border-color .15s;
}
.btn-delete:hover { background: #8C6D3F; color: #F2EBD5; border-color: #8C6D3F; }

/* ===== Dropdown berkas ===== */
.berkas-dropdown-row td { animation: slideDown .22s ease; }
@keyframes slideDown {
    from { opacity: 0; transform: translateY(-5px); }
    to   { opacity: 1; transform: translateY(0); }
}

.berkas-dropdown-inner {
    background: #EDE7D5;
    border-top: 1px solid #C9BFA0;
    padding: 18px 20px;
}

/* ===== Berkas grid ===== */
.berkas-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
    gap: 10px;
}

.berkas-card {
    background: #F9F5EE;
    border: 1px solid #C9BFA0;
    border-radius: 10px;
    overflow: hidden;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    transition: border-color .2s, box-shadow .2s, transform .15s;
}
.berkas-card:hover {
    border-color: #4A8C74;
    box-shadow: 0 0 0 1px rgba(74,140,116,.25), 0 4px 16px rgba(45,107,82,.12);
    transform: translateY(-2px);
}
.berkas-empty         { cursor: default; }
.berkas-empty:hover   { border-color: #C9BFA0; box-shadow: none; transform: none; }

.berkas-thumb {
    position: relative;
    width: 100%;
    aspect-ratio: 3/4;
    background: #DDD5C0;
    overflow: hidden;
    flex-shrink: 0;
}
.berkas-thumb img {
    width: 100%; height: 100%;
    object-fit: cover;
    object-position: top;
    display: block;
    transition: transform .3s;
}
.berkas-card:hover .berkas-thumb img { transform: scale(1.05); }

.pdf-thumb-wrap {
    position: absolute;
    inset: 0;
    overflow: hidden;
    background: #fff;
}
.pdf-thumb-wrap iframe {
    position: absolute;
    top: 0; left: 0;
    width: 200%; height: 200%;
    transform: scale(0.5);
    transform-origin: top left;
    pointer-events: none;
    border: none;
}

.berkas-thumb-empty {
    display: flex;
    align-items: center;
    justify-content: center;
}
.empty-icon { color: #A09070; font-size: 24px; }

.berkas-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(45,107,82,.85) 0%, transparent 55%);
    display: flex;
    align-items: flex-end;
    justify-content: center;
    padding-bottom: 8px;
    opacity: 0;
    transition: opacity .2s;
}
.berkas-overlay span {
    color: #F2EBD5;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
}
.berkas-card:hover .berkas-overlay { opacity: 1; }

.berkas-info {
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 7px 9px;
    border-top: 1px solid #C9BFA0;
    background: #EDE7D5;
    min-height: 48px;
}
.berkas-icon  { font-size: 15px; flex-shrink: 0; line-height: 1; }
.berkas-label { color: #1E4A38; font-size: 10px; font-weight: 700; line-height: 1.3; margin: 0; }
.berkas-type  { color: #7A9E8E; font-size: 9px; font-family: monospace; margin: 0; }

/* ===== Pagination ===== */
.pager {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    padding: 12px 20px;
    background: #EDE7D5;
    border-top: 1px solid #C9BFA0;
}
.pagination-wrap nav { display: flex; }
.pagination-wrap nav > div:first-child { display: none; }
.pagination-wrap [aria-label="Pagination Navigation"] span,
.pagination-wrap [aria-label="Pagination Navigation"] a {
    background: #F9F5EE !important;
    border: 1px solid #C9BFA0 !important;
    color: #4A6B5A !important;
    font-size: 11px !important;
    border-radius: 6px !important;
    padding: 3px 9px !important;
    font-family: inherit !important;
}
.pagination-wrap [aria-label="Pagination Navigation"] span[aria-current="page"] {
    background: #2D6B52 !important;
    border-color: #4A8C74 !important;
    color: #F2EBD5 !important;
}

/* ===== Modal ===== */
.doc-modal {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 999999;
    background: rgba(30,74,56,.88);
    align-items: center;
    justify-content: center;
    padding: 16px;
    backdrop-filter: blur(4px);
}
.doc-modal.open {
    display: flex;
    animation: fadeIn .15s ease;
}
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

.doc-modal-inner {
    background: #F9F5EE;
    border: 1px solid #C9BFA0;
    border-radius: 14px;
    width: 100%;
    max-width: 960px;
    height: 90vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 25px 60px rgba(30,74,56,.4);
}
.doc-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 18px;
    border-bottom: 1px solid #C9BFA0;
    background: #EDE7D5;
    flex-shrink: 0;
}
.doc-modal-body {
    flex: 1;
    position: relative;
    background: #DDD5C0;
    overflow: hidden;
}
.doc-modal-body iframe,
.doc-modal-body img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
}
.doc-modal-body iframe { border: none; }
.doc-modal-body img    { object-fit: contain; background: #EDE7D5; }

.btn-modal-dl {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: #4A8C74;
    color: #F2EBD5;
    border: none;
    padding: 5px 10px;
    border-radius: 7px;
    font-family: inherit;
    font-size: 10px;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
    transition: background .15s;
}
.btn-modal-dl:hover { background: #2D6B52; color: #fff; }

.btn-modal-close {
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(140,109,63,.15);
    color: #8C6D3F;
    border: 1px solid rgba(140,109,63,.35);
    border-radius: 6px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    transition: background .15s, color .15s;
}
.btn-modal-close:hover { background: #8C6D3F; color: #F2EBD5; }
</style>

{{-- ===================== JAVASCRIPT ===================== --}}
<script>
/* ---- Toggle dropdown berkas ---- */
function toggleBerkasDropdown(event, userId) {
    event.preventDefault();
    event.stopPropagation();

    const dropdown = document.getElementById('berkas-dropdown-' + userId);
    const btn      = event.currentTarget;
    const isOpen   = dropdown.style.display === 'table-row';

    document.querySelectorAll('.berkas-dropdown-row').forEach(r => r.style.display = 'none');
    document.querySelectorAll('.berkas-toggle').forEach(b => b.classList.remove('active'));

    if (!isOpen) {
        dropdown.style.display = 'table-row';
        btn.classList.add('active');
        setTimeout(() => dropdown.scrollIntoView({ behavior: 'smooth', block: 'nearest' }), 50);
    }
}

document.addEventListener('click', function (e) {
    if (!e.target.closest('.berkas-toggle') && !e.target.closest('.berkas-dropdown-row')) {
        document.querySelectorAll('.berkas-dropdown-row').forEach(r => r.style.display = 'none');
        document.querySelectorAll('.berkas-toggle').forEach(b => b.classList.remove('active'));
    }
});

/* ---- Modal preview ---- */
const docIconMap = {
    'akreditasi': '🏛️', 'ijazah': '🎓', 'transkrip': '📋',
    'pas foto': '🖼️', 'ktp': '🪪', 'kartu keluarga': '👨‍👩‍👧',
    'asuransi': '🛡️', 'surat sehat': '🏥'
};

function openModal(url, label, ext) {
    const modal   = document.getElementById('docModal');
    const iframe  = document.getElementById('modalIframe');
    const img     = document.getElementById('modalImg');
    const isImage = ['jpg', 'jpeg', 'png', 'webp'].includes(ext);

    document.getElementById('modalTitle').textContent    = label;
    document.getElementById('modalType').textContent     = ext.toUpperCase() + ' · Klik di luar atau tekan Esc untuk tutup';
    document.getElementById('modalIcon').textContent     = docIconMap[label.toLowerCase()] || '📄';
    document.getElementById('modalDownload').href        = url;

    if (isImage) {
        iframe.style.display = 'none';
        img.src = url;
        img.style.display = 'block';
    } else {
        img.style.display = 'none';
        iframe.src = url;
        iframe.style.display = 'block';
    }

    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    const modal  = document.getElementById('docModal');
    const iframe = document.getElementById('modalIframe');
    const img    = document.getElementById('modalImg');
    modal.classList.remove('open');
    iframe.src = '';
    img.src    = '';
    document.body.style.overflow = '';
}

function handleModalClick(e) {
    if (e.target === document.getElementById('docModal')) closeModal();
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
</script>

@endsection