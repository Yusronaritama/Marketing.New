@extends('layouts.admin.app')

@section('content')
<div class="py-12" style="background:var(--bg-page);">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        
        <div class="mb-6">
            <a href="{{ route('admin.profesi.dashboard') }}" class="px-4 py-2 bg-black text-white rounded-lg text-sm font-bold">← Kembali</a>
        </div>

        <div class="dashboard-card">
            <div class="toolbar">
                <h3 class="toolbar-title">Pengaturan Jadwal Gelombang Pendaftaran</h3>
            </div>
            
            <form action="{{ route('admin.profesi.update.gelombang') }}" method="POST" class="p-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($gelombang as $g)
                    <div class="p-4 border border-gray-200 rounded-lg bg-gray-50">
                        <h4 class="font-bold text-lg text-var(--pharma-deep) mb-4">{{ $g->nama_gelombang }}</h4>
                        
                        <div class="mb-3">
                            <label class="block text-xs font-bold text-gray-600 mb-1">Tanggal Mulai</label>
                            <input type="date" name="gelombang[{{$g->id}}][tanggal_mulai]" value="{{ $g->tanggal_mulai }}" 
                                   class="w-full border border-gray-300 rounded-lg p-2 text-sm">
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Tanggal Selesai</label>
                            <input type="date" name="gelombang[{{$g->id}}][tanggal_selesai]" value="{{ $g->tanggal_selesai }}" 
                                   class="w-full border border-gray-300 rounded-lg p-2 text-sm">
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="mt-8 flex justify-end">
                    <button type="submit" class="px-6 py-2 bg-var(--pharma-deep) text-white font-bold rounded-lg hover:bg-var(--pharma-mid) transition">
                        Simpan Semua Jadwal
                    </button>
                </div>
            </form>
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

@endsection