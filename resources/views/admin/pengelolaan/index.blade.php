@extends('layouts.admin.app')

@section('title', 'Kelola Jalur Khusus (Ekstensi, Pindahan, RPL)')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
    :root {
        --primary-blue: #5B7FFF;
        --success-green: #00D4AA;
        --danger-red: #FF6B6B;
        --warning-orange: #FFB84D;
        --text-dark: #1A2332;
        --text-muted: #8492A6;
        --bg-light: #F8FAFC;
        --border-light: #E8ECF1;
    }

    body {
        background: var(--bg-light);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }

    /* Stat Cards - Modern White Design with Flex */
    .stat-card {
        background: #FFFFFF;
        padding: 1.5rem;
        border-radius: 16px;
        border: 1px solid var(--border-light);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 140px;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, var(--accent-color) 0%, transparent 100%);
    }

    .stat-card.card-primary::before { --accent-color: var(--primary-blue); }
    .stat-card.card-success::before { --accent-color: var(--success-green); }
    .stat-card.card-danger::before { --accent-color: var(--danger-red); }
    .stat-card.card-warning::before { --accent-color: var(--warning-orange); }

    .stat-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.08);
        border-color: transparent;
    }

    .stat-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1rem;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }

    .stat-icon.icon-primary { background: linear-gradient(135deg, #667EEA 0%, #5B7FFF 100%); color: white; }
    .stat-icon.icon-success { background: linear-gradient(135deg, #0BC5EA 0%, #00D4AA 100%); color: white; }
    .stat-icon.icon-danger { background: linear-gradient(135deg, #FF6B9D 0%, #FF6B6B 100%); color: white; }
    .stat-icon.icon-warning { background: linear-gradient(135deg, #FFA94D 0%, #FFB84D 100%); color: white; }

    .stat-card-body {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .stat-label {
        font-size: 0.813rem;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin: 0;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--text-dark);
        line-height: 1;
        margin: 0;
    }

    /* Header Section */
    .page-header { margin-bottom: 2rem; }
    .page-title {
        font-size: 1.875rem;
        font-weight: 700;
        color: var(--text-dark);
        margin: 0;
    }

    .breadcrumb-custom {
        background: white;
        padding: 0.75rem 1.25rem;
        border-radius: 12px;
        border: 1px solid var(--border-light);
        margin: 0;
    }
    .breadcrumb-custom .breadcrumb-item a { color: var(--text-muted); text-decoration: none; font-weight: 500; }
    .breadcrumb-custom .breadcrumb-item.active { color: var(--primary-blue); font-weight: 600; }

    /* Filter Section */
    .filter-card {
        background: #FFFFFF;
        padding: 2rem;
        border-radius: 20px;
        border: 1px solid var(--border-light);
        margin-bottom: 2rem;
    }
    .filter-label {
        font-weight: 600;
        font-size: 0.875rem;
        color: var(--text-dark);
        margin-bottom: 0.5rem;
        display: block;
    }

    .input-group-modern { position: relative; }
    .input-group-modern .icon-prefix {
        position: absolute; left: 16px; top: 50%; transform: translateY(-50%);
        color: var(--text-muted); z-index: 10;
    }

    .form-control-modern {
        height: 48px; padding: 0.75rem 1rem 0.75rem 3rem;
        border: 2px solid var(--border-light); border-radius: 12px;
        background: #FFFFFF; font-size: 0.938rem; color: var(--text-dark);
        transition: all 0.3s ease; width: 100%; box-sizing: border-box;
    }
    .form-control-modern:focus {
        border-color: var(--primary-blue);
        box-shadow: 0 0 0 4px rgba(91, 127, 255, 0.1); outline: none;
    }

    .btn-primary-modern {
        height: 48px; padding: 0 2rem;
        background: linear-gradient(135deg, #667EEA 0%, #5B7FFF 100%);
        border: none; border-radius: 12px; color: white;
        font-weight: 600; font-size: 0.938rem; transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(91, 127, 255, 0.3);
        cursor: pointer;
    }
    .btn-primary-modern:hover {
        transform: translateY(-2px); box-shadow: 0 8px 20px rgba(91, 127, 255, 0.4);
    }
    .btn-reset-modern {
        height: 48px; padding: 0 2rem; background: white;
        border: 2px solid var(--border-light); border-radius: 12px;
        color: var(--text-dark); font-weight: 600; font-size: 0.938rem;
        transition: all 0.3s ease; text-decoration: none; display: inline-flex;
        align-items: center; justify-content: center; box-sizing: border-box;
    }
    .btn-reset-modern:hover { border-color: var(--text-muted); background: var(--bg-light); color: var(--text-dark); }

    /* Table Card */
    .table-card {
        background: #FFFFFF; border-radius: 20px;
        border: 1px solid var(--border-light); overflow: hidden;
        margin-bottom: 2rem;
    }
    .table-modern { width: 100%; border-collapse: collapse; }
    .table-modern thead th {
        background: #FAFBFC; border-bottom: 2px solid var(--border-light);
        color: var(--text-dark); font-weight: 700; font-size: 0.813rem;
        text-transform: uppercase; letter-spacing: 0.5px; padding: 1.25rem 1.5rem;
        text-align: left;
    }
    .table-modern tbody td {
        padding: 1.5rem; vertical-align: middle;
        border-bottom: 1px solid var(--border-light); color: var(--text-dark); font-size: 0.938rem;
    }
    .table-modern tbody tr:last-child td { border-bottom: none; }
    .table-modern tbody tr:hover { background: var(--bg-light); }

    /* Status Badge */
    .status-badge {
        display: inline-flex; align-items: center; padding: 0.5rem 1rem;
        border-radius: 10px; font-size: 0.813rem; font-weight: 600;
    }
    .badge-pending { background: #FFF3E0; color: #F57C00; }
    .badge-verified { background: #E0F7F4; color: #00897B; }

    /* Small Actions */
    .btn-save-modern {
        height: 40px; padding: 0 1.5rem; background: var(--primary-blue);
        border: none; border-radius: 10px; color: white; font-weight: 600;
        font-size: 0.875rem; transition: all 0.3s ease; cursor: pointer;
    }
    .btn-save-modern:hover {
        background: #4A6FEE; transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(91, 127, 255, 0.3);
    }
    .select-modern {
        height: 48px; padding: 0.75rem 1rem; border: 2px solid var(--border-light);
        border-radius: 12px; background: white; font-size: 0.938rem; color: var(--text-dark);
        font-weight: 500; width: 100%; transition: all 0.3s ease; box-sizing: border-box;
    }
    .select-modern:focus { border-color: var(--primary-blue); outline: none; }

    /* Alerts & Empty */
    .alert-modern { padding: 1.25rem 1.5rem; border-radius: 16px; margin: 1.5rem; font-weight: 500; }
    .alert-success-modern { background: linear-gradient(135deg, #E0F7F4 0%, #B2EBF2 100%); color: #00695C; }
    .empty-state { text-align: center; padding: 4rem 2rem; }
    .empty-icon { font-size: 4rem; color: #E0E0E0; margin-bottom: 1.5rem; }
    .empty-text { font-size: 1.125rem; color: var(--text-muted); font-weight: 500; }

    /* Student Info Text */
    .student-name { font-weight: 600; color: var(--text-dark); font-size: 0.938rem; margin-bottom: 0.25rem; }
    .student-meta { font-size: 0.813rem; color: var(--text-muted); }

    /* Grid layout */
    .stats-row { display: flex; flex-wrap: wrap; margin: 0 -0.75rem 1.5rem; }
    .stats-col { flex: 0 0 33.333%; max-width: 33.333%; padding: 0 0.75rem; margin-bottom: 1.5rem; box-sizing: border-box; }
    @media (max-width: 991px) { .stats-col { flex: 0 0 100%; max-width: 100%; } }

    /* Custom Modern Modal */
    .modal-modern {
        display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(26, 35, 50, 0.6); backdrop-filter: blur(4px); z-index: 1050;
        align-items: center; justify-content: center;
    }
    .modal-modern-content {
        background: #fff; border-radius: 20px; width: 90%; max-width: 500px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1); overflow: hidden;
        animation: modalSlideUp 0.3s ease-out;
    }
    .modal-modern-header {
        padding: 1.5rem; border-bottom: 1px solid var(--border-light);
        display: flex; justify-content: space-between; align-items: center;
    }
    .modal-modern-title { font-size: 1.25rem; font-weight: 700; color: var(--text-dark); margin: 0; }
    .modal-close-btn { background: none; border: none; font-size: 1.5rem; color: var(--text-muted); cursor: pointer; }
    .modal-modern-body { padding: 1.5rem; }
    .modal-modern-footer {
        padding: 1.5rem; border-top: 1px solid var(--border-light); background: var(--bg-light);
        display: flex; justify-content: flex-end; gap: 10px;
    }
    .textarea-modern {
        width: 100%; border: 2px solid var(--border-light); border-radius: 12px;
        padding: 1rem; font-size: 0.938rem; color: var(--text-dark); box-sizing: border-box;
        transition: border-color 0.3s; resize: vertical;
    }
    .textarea-modern:focus { border-color: var(--primary-blue); outline: none; }

    @keyframes modalSlideUp {
        from { transform: translateY(20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
</style>

<div class="container-fluid px-4 py-4">
    <div class="page-header" style="display: flex; align-items: center; justify-content: space-between;">
        <div>
            <h1 class="page-title">Pengelolaan Jalur Khusus</h1>
            <nav aria-label="breadcrumb" style="margin-top: 10px;">
                <ol class="breadcrumb breadcrumb-custom" style="display: flex; list-style: none; gap: 8px; padding: 0.5rem 1rem;">
                    <li class="breadcrumb-item"><a href="#">Admin</a></li>
                    <li style="color: var(--text-muted);">/</li>
                    <li class="breadcrumb-item active">Jalur Khusus</li>
                </ol>
            </nav>
        </div>
		<div class="header-actions" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            
            <a href="{{ route('admin.pengelolaan.export-data', ['jalur' => request('jalur'), 'search' => request('search')]) }}" class="btn-reset-modern" style="color: #00D4AA; border-color: #00D4AA;" title="Export ke Excel/CSV">
                <i class="fas fa-file-excel mr-2"></i> Export Data
            </a>

            <!--<a href="{{ route('admin.pengelolaan.export-berkas', ['jalur' => request('jalur'), 'search' => request('search')]) }}" class="btn-reset-modern" style="color: var(--primary-blue); border-color: var(--primary-blue);" title="Download semua berkas (ZIP)">-->
            <!--    <i class="fas fa-file-archive mr-2"></i> Export Berkas-->
            <!--</a>-->
            
        </div>
        <div class="header-actions" id="bulkActionsContainer" style="display: none;">
            <form id="bulkVerifyForm" action="{{ route('admin.pengelolaan.bulk-verify') }}" method="POST">
                @csrf
                <input type="hidden" name="user_ids" id="bulkUserIds">
                <button type="button" onclick="submitBulkVerify()" class="btn-primary-modern" style="background: linear-gradient(135deg, #0BC5EA 0%, #00D4AA 100%);">
                    <i class="fas fa-check-double mr-2"></i> Verifikasi Terpilih (<span id="selectedCount">0</span>)
                </button>
            </form>
        </div>
        <div style="display: flex; gap: 10px;">
            <button type="button" class="btn-primary-modern" style="background: linear-gradient(135deg, #5B7FFF 0%, #667EEA 100%);" onclick="openGlobalInfoModal()">
                <i class="fas fa-bullhorn mr-2"></i> Set Info Semua
            </button>

            <div class="header-actions" id="bulkActionsContainer" style="display: none;">
                </div>
        </div>
    </div>

    <div class="stats-row">
        <div class="stats-col">
            <div class="stat-card card-primary">
                <div class="stat-card-header">
                    <div class="stat-icon icon-primary"><i class="fas fa-users"></i></div>
                </div>
                <div class="stat-card-body">
                    <div class="stat-label">Total Mahasiswa Terkelola</div>
                    <div class="stat-value">{{ $statTotal }}</div>
                </div>
            </div>
        </div>

        <div class="stats-col">
            <div class="stat-card card-success">
                <div class="stat-card-header">
                    <div class="stat-icon icon-success"><i class="fas fa-check-circle"></i></div>
                </div>
                <div class="stat-card-body">
                    <div class="stat-label">Sudah Diverifikasi</div>
                    <div class="stat-value">{{ $statVerified }}</div>
                </div>
            </div>
        </div>

        <div class="stats-col">
            <div class="stat-card card-warning">
                <div class="stat-card-header">
                    <div class="stat-icon icon-warning"><i class="fas fa-clock"></i></div>
                </div>
                <div class="stat-card-body">
                    <div class="stat-label">Belum Diverifikasi</div>
                    <div class="stat-value">{{ $statUnverified }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="filter-card">
        <form action="{{ route('admin.pengelolaan.index') }}" method="GET">
            <div class="row" style="display: flex; flex-wrap: wrap; margin: 0 -10px; align-items: flex-end;">
                <div style="flex: 1 1 300px; padding: 0 10px; margin-bottom: 15px;">
                    <label class="filter-label">Cari Mahasiswa</label>
                    <div class="input-group-modern">
                        <i class="fas fa-search icon-prefix"></i>
                        <input type="text" name="search" class="form-control-modern" placeholder="Cari nama..." value="{{ request('search') }}">
                    </div>
                </div>
                
                <div style="flex: 1 1 200px; padding: 0 10px; margin-bottom: 15px;">
                    <label class="filter-label">Jalur Pendaftaran</label>
                    <select name="jalur" class="select-modern">
                        <option value="">Semua Jalur</option>
                        <option value="Ekstensi" {{ request('jalur') == 'Ekstensi' ? 'selected' : '' }}>Ekstensi</option>
                        <option value="Pindahan" {{ request('jalur') == 'Pindahan' ? 'selected' : '' }}>Pindahan</option>
                        <option value="RPL" {{ request('jalur') == 'RPL' ? 'selected' : '' }}>RPL</option>
                    </select>
                </div>
                
                <div style="flex: 1 1 auto; padding: 0 10px; margin-bottom: 15px; display: flex; gap: 10px;">
                    <button type="submit" class="btn-primary-modern">
                        <i class="fas fa-filter"></i> Terapkan
                    </button>
                    @if(request()->hasAny(['search', 'jalur']))
                    <a href="{{ route('admin.pengelolaan.index') }}" class="btn-reset-modern">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <div class="table-card">
        @if(session('success'))
            <div class="alert-modern alert-success-modern">
                <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            </div>
        @endif

        <div style="overflow-x: auto;">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">
                            <input type="checkbox" id="selectAllCheckbox" style="transform: scale(1.3); cursor: pointer; accent-color: var(--primary-blue);">
                        </th>
                        <th class="text-center" style="width: 60px; text-align: center;">#</th>
                        <th>Identitas Mahasiswa</th>
                        <th>Jalur Pendaftaran</th>
                        <th>Status Verifikasi</th>
                        <th>Catatan / Info</th>
                        <!--<th style="text-align: center;">Aksi</th>-->
                    </tr>
                </thead>
                <tbody>
                    @forelse($pengelolaan as $item)
                        <tr>
                            <td style="text-align: center;">
                                <input type="checkbox" class="user-checkbox" value="{{ $item->user_id }}" style="transform: scale(1.3); cursor: pointer; accent-color: var(--primary-blue);">
                            </td>
                            <td style="text-align: center;">
                                <span style="background: var(--bg-light); color: var(--text-muted); font-weight: 600; padding: 6px 12px; border-radius: 8px; font-size: 0.875rem;">
                                    {{ $loop->iteration + ($pengelolaan->currentPage() - 1) * $pengelolaan->perPage() }}
                                </span>
                            </td>
                            <td>
                                <div class="student-name">{{ strtoupper($item->nama_lengkap) }}</div>
                                <div class="student-meta">User ID: {{ $item->user_id }}</div>
                            </td>
                            <td>
                                <span style="color: var(--primary-blue); font-weight: 600; background: rgba(91, 127, 255, 0.1); padding: 5px 10px; border-radius: 6px; font-size: 0.813rem;">
                                    {{ $item->jalur_pendaftaran }}
                                </span>
                            </td>
                           <td>
							    <form action="{{ route('admin.pengelolaan.update-status', $item->user_id) }}" method="POST" style="display: flex; align-items: center; gap: 8px; margin: 0;">
							        @csrf
							        <select name="status_verifikasi" class="select-modern" style="height: 38px; padding: 0.25rem 0.75rem; width: auto; font-size: 0.875rem; min-width: 150px; cursor: pointer;">
							            <option value="0" {{ $item->status_verifikasi == 0 ? 'selected' : '' }}>
							                ⏳ Belum Verifikasi
							            </option>
							            <option value="1" {{ $item->status_verifikasi == 1 ? 'selected' : '' }}>
							                ✅ Diverifikasi
							            </option>
							        </select>
							        <button type="submit" class="btn-save-modern" style="height: 38px; padding: 0 1rem; border-radius: 8px;">
							            Simpan
							        </button>
							    </form>
							</td>
                            <td>
                                <div style="color: var(--text-muted); font-size: 0.875rem; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $item->teks_informasi ?: '-' }}
                                </div>
                            </td>
                            <!--<td style="text-align: center;">-->
                            <!--    <button type="button" class="btn-save-modern" onclick="openInfoModal({{ $item->user_id }})">-->
                            <!--        <i class="fas fa-edit"></i> Edit Info-->
                            <!--    </button>-->
                            <!--</td>-->
                        </tr>

                        {{-- Modal untuk setiap user --}}
                        <div class="modal-modern" id="infoModal-{{ $item->user_id }}">
                            <div class="modal-modern-content">
                                <div class="modal-modern-header">
                                    <h3 class="modal-modern-title">Edit Info: {{ $item->nama_lengkap }}</h3>
                                    <button type="button" class="modal-close-btn" onclick="closeInfoModal({{ $item->user_id }})">&times;</button>
                                </div>
                                <form action="{{ route('admin.pengelolaan.update-informasi', $item->user_id) }}" method="POST">
                                    @csrf
                                    <div class="modal-modern-body">
                                        <label class="filter-label">Catatan / Informasi Tambahan</label>
                                        <textarea name="teks_informasi" rows="4" class="textarea-modern" placeholder="Ketik informasi tambahan di sini...">{{ $item->teks_informasi }}</textarea>
                                    </div>
                                    <div class="modal-modern-footer">
                                        <button type="button" class="btn-reset-modern" style="height: 40px; padding: 0 1.5rem;" onclick="closeInfoModal({{ $item->user_id }})">Batal</button>
                                        <button type="submit" class="btn-save-modern">Simpan Data</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="empty-icon"><i class="fas fa-folder-open"></i></div>
                                    <p class="empty-text">Tidak ada data pengelolaan jalur khusus yang ditemukan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination (Styled) --}}
    @if($pengelolaan->hasPages())
    <div style="margin-top: 1rem; display: flex; justify-content: flex-end;">
        {{ $pengelolaan->links() }}
    </div>
    @endif
</div>
{{-- Modal Set Info Global --}}
    <div class="modal-modern" id="globalInfoModal">
        <div class="modal-modern-content">
            <div class="modal-modern-header">
                <h3 class="modal-modern-title">Set Informasi Global</h3>
                <button type="button" class="modal-close-btn" onclick="closeGlobalInfoModal()">&times;</button>
            </div>
            <form action="{{ route('admin.pengelolaan.bulk-info') }}" method="POST">
                @csrf
                <input type="hidden" name="jalur" value="{{ request('jalur') }}">
                <input type="hidden" name="search" value="{{ request('search') }}">
                
                <div class="modal-modern-body">
                    <div style="background: #E0F7F4; color: #00897B; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; font-size: 0.875rem;">
                        <i class="fas fa-info-circle mr-1"></i> Pesan yang diketik di bawah ini akan otomatis dikirim/diterapkan ke <strong>SEMUA MAHASISWA</strong> yang saat ini tampil di tabel sesuai filter pencarian Anda.
                    </div>
                    <label class="filter-label">Pesan Informasi</label>
                    <textarea name="teks_informasi" rows="4" class="form-control-modern" style="height: auto; resize: vertical;" placeholder="Ketik pesan informasi di sini..."></textarea>
                </div>
                <div class="modal-modern-footer">
                    <button type="button" class="btn-reset-modern" style="height: 40px; padding: 0 1.5rem;" onclick="closeGlobalInfoModal()">Batal</button>
                    <button type="submit" class="btn-save-modern" onclick="return confirm('Kirim informasi ini ke SEMUA data terpilih?');">Kirim ke Semua</button>
                </div>
            </form>
        </div>
    </div>
<script>
	function openGlobalInfoModal() {
        document.getElementById('globalInfoModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeGlobalInfoModal() {
        document.getElementById('globalInfoModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    // Pastikan modal ditampilkan sebagai Flex (menengah ke tengah layar)
    function openInfoModal(id) {
        document.getElementById('infoModal-' + id).style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeInfoModal(id) {
        document.getElementById('infoModal-' + id).style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    // Klik overlay (di luar modal box) untuk menutup
    window.onclick = function(event) {
        if (event.target.classList.contains('modal-modern')) {
            event.target.style.display = "none";
            document.body.style.overflow = 'auto';
        }
    }
    function openInfoModal(id) {
        document.getElementById('infoModal-' + id).style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeInfoModal(id) {
        document.getElementById('infoModal-' + id).style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    window.onclick = function(event) {
        if (event.target.classList.contains('modal-modern')) {
            event.target.style.display = "none";
            document.body.style.overflow = 'auto';
        }
    }

    // FUNGSI CHECKBOX DAN VERIFIKASI MASSAL BARU
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('selectAllCheckbox');
        const userCheckboxes = document.querySelectorAll('.user-checkbox');
        const bulkActionsContainer = document.getElementById('bulkActionsContainer');
        const selectedCountSpan = document.getElementById('selectedCount');
        const bulkUserIdsInput = document.getElementById('bulkUserIds');

        // Update UI tombol verifikasi massal
        function updateBulkUI() {
            const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
            const count = checkedBoxes.length;

            if (count > 0) {
                bulkActionsContainer.style.display = 'block'; // Tampilkan tombol
                selectedCountSpan.textContent = count; // Update angka
            } else {
                bulkActionsContainer.style.display = 'none'; // Sembunyikan tombol
            }

            // Update status checkbox "Pilih Semua"
            if(selectAllCheckbox) {
                selectAllCheckbox.checked = count === userCheckboxes.length && count > 0;
                selectAllCheckbox.indeterminate = count > 0 && count < userCheckboxes.length;
            }
        }

        // Event listener untuk "Pilih Semua"
        if(selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                userCheckboxes.forEach(cb => cb.checked = this.checked);
                updateBulkUI();
            });
        }

        // Event listener untuk setiap checkbox individu
        userCheckboxes.forEach(cb => {
            cb.addEventListener('change', updateBulkUI);
        });

        // Event listener untuk tombol Submit
        window.submitBulkVerify = function() {
            const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
            if (checkedBoxes.length === 0) return;

            if (!confirm('Yakin ingin memverifikasi ' + checkedBoxes.length + ' data yang dipilih?')) return;

            // Kumpulkan ID dan masukkan ke hidden input form
            const ids = Array.from(checkedBoxes).map(cb => cb.value);
            bulkUserIdsInput.value = JSON.stringify(ids);
            
            // Eksekusi Form
            document.getElementById('bulkVerifyForm').submit();
        }
    });
</script>
@endsection