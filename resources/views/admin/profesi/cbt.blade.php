@extends('layouts.admin.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        @if (session('success'))
            <div class="mb-4 p-4 rounded-lg flex items-center gap-3" style="background:rgba(45,107,82,.12);border:1px solid rgba(45,107,82,.35);border-left:3px solid var(--pharma-deep);color:var(--pharma-deep);">
                <span style="font-size:13px;font-weight:600;">{{ session('success') }}</span>
            </div>
        @endif

        <div class="mb-6 flex gap-4">
            <a href="{{ route('admin.profesi.dashboard') }}" 
               style="padding:8px 16px; background:var(--sand-cream); border:1px solid var(--border); color:var(--text-primary); border-radius:8px; font-size:12px; font-weight:600; text-decoration:none;">
                ← Kembali ke Dashboard Utama
            </a>
        </div>

        <div class="dashboard-card">
            <div class="toolbar">
                <div>
                    <div class="toolbar-title">Kelola Kelulusan CBT Ujian Profesi Apoteker & Wawancara</div>
                </div>
                <div class="toolbar-right">
                    
                    {{-- FORM FILTER TERPADU (Nama, Gelombang & Per Page) --}}
                    <form action="{{ route('admin.profesi.cbt.index') }}" method="GET" id="cbtFilterForm" class="flex items-center gap-2 m-0 flex-wrap">
                        
                        {{-- INPUT SEARCH NAMA --}}
                        <div class="search-box-wrap">
                            <input type="text" name="search_name" value="{{ request('search_name') }}" placeholder="Cari nama pendaftar..." class="dash-input-search">
                            <button type="submit" class="btn-search-trigger">
                                🔍 Cari
                            </button>
                        </div>

                        {{-- Dropdown Filter Gelombang --}}
                        <select name="filter_gelombang" class="dash-select" onchange="document.getElementById('cbtFilterForm').submit()">
                            <option value="">Semua Gelombang</option>
                            @foreach($listGelombang as $gel)
                                <option value="{{ $gel->id }}" {{ request('filter_gelombang') == $gel->id ? 'selected' : '' }}>
                                    {{ $gel->nama_gelombang }}
                                </option>
                            @endforeach
                        </select>

                        {{-- Per Page Selector --}}
                        <select name="per_page" onchange="document.getElementById('cbtFilterForm').submit()" class="dash-select">
                            <option value="15" {{ $perPage == 15 ? 'selected' : '' }}>15 / halaman</option>
                            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50 / halaman</option>
                            <option value="all" {{ $perPage == 'all' ? 'selected' : '' }}>Semua data</option>
                        </select>
                    </form>

                </div>
                <a href="{{ route('admin.profesi.export.kelulusan', ['filter_gelombang' => request('filter_gelombang'), 'search_name' => request('search_name')]) }}" class="btn-export">
                    <i class="fas fa-file-excel"></i> Export Excel
                </a>
            </div>
            
            <div style="overflow-x:auto;">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th style="width:44px;">No</th>
                            <th>Nama Lengkap</th>
                            <th>Gelombang</th>
                            <th>TRX ID (Username)</th>
                            <th style="text-align:center;">Nilai CBT</th>
                            <th style="text-align:center;">Status</th>
                            <th style="text-align:center;">Aksi Konfirmasi</th>
                            <th style="text-align:center;">Status Wawancara</th>
                            <th style="text-align:center;">Aksi Wawancara</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pendaftarCbt as $index => $user)
                            @php
                                $userGelombangId = $user->masterUser->gelombang ?? null;
                                $namaGelombangTerdaftar = 'Gelombang 1';

                                if($userGelombangId) {
                                    $findGel = $listGelombang->firstWhere('id', $userGelombangId);
                                    if($findGel) {
                                        $namaGelombangTerdaftar = $findGel->nama_gelombang;
                                    }
                                }
                            @endphp

                            <tr class="dash-row">
                                <td style="color:var(--text-muted);">{{ $pendaftarCbt->firstItem() + $index }}</td>
                                <td>
                                    <p style="margin:0; font-weight:700; color:var(--text-primary);">{{ $user->name }}</p>
                                    <p style="margin:0; font-size:10px; color:var(--text-muted);">{{ $user->email }}</p>
                                </td>
                                
                                <td class="text-sm">
                                    <span class="badge" style="background: rgba(74, 140, 116, 0.15); color: var(--pharma-deep); border: 1px solid rgba(74, 140, 116, 0.4); font-weight:700; border-radius:9999px;">
                                        {{ $namaGelombangTerdaftar }}
                                    </span>
                                </td>

                                <td style="font-family:monospace; color:var(--text-sec);">{{ $user->masterUser->trx_id ?? '-' }}</td>
                                
                                <td style="text-align:center; font-weight:800; color:var(--pharma-deep);">
                                    {{ $user->nilai_cbt ?? '-' }}
                                </td>
                                
                                <td style="text-align:center;">
                                    @if($user->status_cbt === 'Lulus')
                                        <span class="badge badge-verified">🎉 LULUS</span>
                                    @elseif($user->status_cbt === 'Tidak Lulus')
                                        <span class="badge badge-pending" style="border-color:#8C3D3D; color:#8C3D3D;">❌ TIDAK LULUS</span>
                                    @else
                                        <span class="badge" style="background:#e0f2fe; color:#0369a1;">{{ $user->status_cbt ?? 'Belum Diuji' }}</span>
                                    @endif
                                </td>
                                
                                {{-- PERBAIKAN KOLOM AKSI: DITAMBAHKAN TOMBOL BATAL UJI --}}
                                <td style="text-align:center;">
                                    <div style="display: flex; flex-direction: column; align-items: center; gap: 4px;">
                                        <form action="{{ route('admin.profesi.cbt.update', $user->id) }}" method="POST" style="display:inline-flex; gap:6px; justify-content:center;">
                                            @csrf
                                            <button type="submit" name="status_cbt" value="Lulus" class="btn-verify" style="background:var(--pharma-mid); color:#fff; border:none;">
                                                Set Lulus
                                            </button>
                                            <button type="submit" name="status_cbt" value="Tidak Lulus" class="btn-delete" style="background:#8C3D3D; color:#fff; border:none;">
                                                Tidak Lulus
                                            </button>
                                        </form>
                                        
                                        {{-- Tombol Batal tampil kondisional jika status_cbt sudah terisi lulus/tidak lulus --}}
                                        @if($user->status_cbt === 'Lulus' || $user->status_cbt === 'Tidak Lulus')
                                            <form action="{{ route('admin.profesi.cbt.batal', $user->id) }}" method="POST" onsubmit="return confirm('Yakin ingin membatalkan kelulusan CBT dan menghapus nilai {{ addslashes($user->name) }}?')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn-batal-uji">
                                                    🔄 Batal Uji & Hapus Nilai
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>

                                <td style="text-align:center;">
                                    @if($user->status_wawancara === 'Lulus')
                                        <span class="badge badge-verified">✅ LULUS</span>
                                    @elseif($user->status_wawancara === 'Tidak Lulus')
                                        <span class="badge badge-pending" style="border-color:#8C3D3D; color:#8C3D3D;">❌ TIDAK LULUS</span>
                                    @else
                                        <span class="badge" style="background:#ddd; color:#666;">Belum Diuji</span>
                                    @endif
                                </td>
                                <td style="text-align:center;">
                                    <form action="{{ route('admin.profesi.wawancara.update', $user->id) }}" method="POST" style="display:inline-flex; gap:6px; justify-content:center;">
                                        @csrf
                                        <button type="submit" name="status_wawancara" value="Lulus" class="btn-verify" style="background:var(--pharma-mid); color:#fff; border:none; padding: 4px 8px; font-size:9px;">
                                            Lulus
                                        </button>
                                        <button type="submit" name="status_wawancara" value="Tidak Lulus" class="btn-delete" style="background:#8C3D3D; color:#fff; border:none; padding: 4px 8px; font-size:9px;">
                                            Gagal
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" style="text-align:center; padding:40px; color:var(--text-muted);">Tidak ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($pendaftarCbt->hasPages())
                <div class="pager">
                    {{ $pendaftarCbt->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

{{-- ===================== CSS ===================== --}}
<style>
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

.search-box-wrap {
    display: inline-flex;
    align-items: center;
    background: #1E4A38;
    border: 1px solid #4A8C74;
    border-radius: 7px;
    padding: 2px;
}
.dash-input-search {
    background: transparent;
    border: none;
    color: #fff;
    font-size: 11px;
    padding: 4px 8px;
    outline: none;
    width: 160px;
}
.dash-input-search::placeholder { color: #89B4A8; }
.btn-search-trigger {
    background: var(--pharma-mid);
    color: #fff;
    border: none;
    font-size: 10px;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 5px;
    cursor: pointer;
    transition: background .15s;
}
.btn-search-trigger:hover { background: #2D6B52; }

/* STYLE BARU UNTUK TOMBOL BATAL UJI */
.btn-batal-uji {
    background: none;
    border: none;
    color: #a32626;
    font-size: 9px;
    font-weight: 700;
    text-decoration: underline;
    cursor: pointer;
    padding: 2px 4px;
    transition: color 0.15s;
}
.btn-batal-uji:hover {
    color: #e03d3d;
}

.dashboard-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(45,107,82,.08);
}

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
.badge-verified { background: rgba(74,140,116,.2);  color: #2D6B52; border: 1px solid rgba(74,140,116,.5); }
.badge-pending  { background: rgba(140,109,63,.2);  color: #5C3D18; border: 1px solid rgba(140,109,63,.5); }

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
</style>
@endsection