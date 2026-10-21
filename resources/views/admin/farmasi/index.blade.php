@extends('layouts.admin.app')

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

    .stat-icon.icon-primary {
        background: linear-gradient(135deg, #667EEA 0%, #5B7FFF 100%);
        color: white;
    }

    .stat-icon.icon-success {
        background: linear-gradient(135deg, #0BC5EA 0%, #00D4AA 100%);
        color: white;
    }

    .stat-icon.icon-danger {
        background: linear-gradient(135deg, #FF6B9D 0%, #FF6B6B 100%);
        color: white;
    }

    .stat-icon.icon-warning {
        background: linear-gradient(135deg, #FFA94D 0%, #FFB84D 100%);
        color: white;
    }

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
    .page-header {
        margin-bottom: 2rem;
    }

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

    .breadcrumb-custom .breadcrumb-item a {
        color: var(--text-muted);
        text-decoration: none;
        font-weight: 500;
    }

    .breadcrumb-custom .breadcrumb-item.active {
        color: var(--primary-blue);
        font-weight: 600;
    }

    /* Filter Section - Pure White */
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

    .input-group-modern {
        position: relative;
    }

    .input-group-modern .icon-prefix {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        z-index: 10;
    }

    .form-control-modern {
        height: 48px;
        padding: 0.75rem 1rem 0.75rem 3rem;
        border: 2px solid var(--border-light);
        border-radius: 12px;
        background: #FFFFFF;
        font-size: 0.938rem;
        color: var(--text-dark);
        transition: all 0.3s ease;
    }

    .form-control-modern:focus {
        border-color: var(--primary-blue);
        box-shadow: 0 0 0 4px rgba(91, 127, 255, 0.1);
        background: #FFFFFF;
        outline: none;
    }

    .form-control-modern::placeholder {
        color: var(--text-muted);
    }

    .btn-primary-modern {
        height: 48px;
        padding: 0 2rem;
        background: linear-gradient(135deg, #667EEA 0%, #5B7FFF 100%);
        border: none;
        border-radius: 12px;
        color: white;
        font-weight: 600;
        font-size: 0.938rem;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(91, 127, 255, 0.3);
    }

    .btn-primary-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(91, 127, 255, 0.4);
        background: linear-gradient(135deg, #5B7FFF 0%, #667EEA 100%);
    }

    .btn-reset-modern {
        height: 48px;
        padding: 0 2rem;
        background: white;
        border: 2px solid var(--border-light);
        border-radius: 12px;
        color: var(--text-dark);
        font-weight: 600;
        font-size: 0.938rem;
        transition: all 0.3s ease;
    }

    .btn-reset-modern:hover {
        border-color: var(--text-muted);
        background: var(--bg-light);
    }

    /* Table Card - Clean White */
    .table-card {
        background: #FFFFFF;
        border-radius: 20px;
        border: 1px solid var(--border-light);
        overflow: hidden;
    }

    .table-modern {
        width: 100%;
        margin-bottom: 0;
    }

    .table-modern thead th {
        background: #FAFBFC;
        border-bottom: 2px solid var(--border-light);
        color: var(--text-dark);
        font-weight: 700;
        font-size: 0.813rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 1.25rem 1.5rem;
    }

    .table-modern tbody td {
        padding: 1.5rem;
        vertical-align: middle;
        border-bottom: 1px solid var(--border-light);
        color: var(--text-dark);
        font-size: 0.938rem;
    }

    .table-modern tbody tr:last-child td {
        border-bottom: none;
    }

    .table-modern tbody tr:hover {
        background: var(--bg-light);
    }

    /* Status Badge */
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 1rem;
        border-radius: 10px;
        font-size: 0.813rem;
        font-weight: 600;
    }

    .status-badge.badge-pending {
        background: #FFF3E0;
        color: #F57C00;
    }

    .status-badge.badge-verified {
        background: #E0F7F4;
        color: #00897B;
    }

    .status-badge.badge-rejected {
        background: #FFEBEE;
        color: #D32F2F;
    }

    .status-badge.badge-unprocessed {
        background: #F5F5F5;
        color: #757575;
    }

    /* Form Controls in Table */
    .select-modern {
        height: 40px;
        padding: 0.5rem 1rem;
        border: 2px solid var(--border-light);
        border-radius: 10px;
        background: white;
        font-size: 0.875rem;
        color: var(--text-dark);
        font-weight: 500;
        min-width: 160px;
        transition: all 0.3s ease;
    }

    .select-modern:focus {
        border-color: var(--primary-blue);
        outline: none;
        box-shadow: 0 0 0 3px rgba(91, 127, 255, 0.1);
    }

    .btn-save-modern {
        height: 40px;
        padding: 0 1.5rem;
        background: var(--primary-blue);
        border: none;
        border-radius: 10px;
        color: white;
        font-weight: 600;
        font-size: 0.875rem;
        transition: all 0.3s ease;
    }

    .btn-save-modern:hover {
        background: #4A6FEE;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(91, 127, 255, 0.3);
    }

    /* Alert */
    .alert-modern {
        padding: 1.25rem 1.5rem;
        border-radius: 16px;
        border: none;
        margin: 1.5rem;
        font-weight: 500;
    }

    .alert-success-modern {
        background: linear-gradient(135deg, #E0F7F4 0%, #B2EBF2 100%);
        color: #00695C;
    }

    /* Student Info */
    .student-name {
        font-weight: 600;
        color: var(--text-dark);
        font-size: 0.938rem;
        margin-bottom: 0.25rem;
    }

    .student-meta {
        font-size: 0.813rem;
        color: var(--text-muted);
    }

    /* Date Info */
    .date-info {
        font-size: 0.875rem;
        color: var(--text-dark);
    }

    .date-meta {
        font-size: 0.813rem;
        color: var(--text-muted);
        margin-top: 0.25rem;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
    }

    .empty-icon {
        font-size: 4rem;
        color: #E0E0E0;
        margin-bottom: 1.5rem;
    }

    .empty-text {
        font-size: 1.125rem;
        color: var(--text-muted);
        font-weight: 500;
    }

    /* Ensure equal height cards */
    .stats-row {
        display: flex;
        flex-wrap: wrap;
        margin: 0 -0.75rem 1.5rem;
    }

    .stats-col {
        flex: 0 0 25%;
        max-width: 25%;
        padding: 0 0.75rem;
        margin-bottom: 1.5rem;
    }

    @media (max-width: 1199px) {
        .stats-col {
            flex: 0 0 50%;
            max-width: 50%;
        }
    }

    @media (max-width: 767px) {
        .stats-col {
            flex: 0 0 100%;
            max-width: 100%;
        }
    }
</style>
<style>
    .btn-success-modern {
        background: #1cc88a;
        color: #fff;
        border: none;
        padding: 0.6rem 1.2rem;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s;
    }
    .btn-success-modern:hover {
        background: #17a673;
        color: #fff;
        transform: translateY(-2px);
    }
</style>
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <h1 class="page-title">Dashboard Verifikasi Farmasi</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-custom">
                <li class="breadcrumb-item"><a href="#">Admin</a></li>
                <li class="breadcrumb-item active" aria-current="page">Farmasi</li>
            </ol>
        </nav>
        <div class="header-actions">
        <a href="{{ route('admin.farmasi.export', request()->all()) }}" class="btn btn-success-modern shadow-sm">
            <i class="fas fa-file-excel mr-2"></i> Export Data Excel
        </a>
    </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-row">
        <div class="stats-col">
            <div class="stat-card card-primary">
                <div class="stat-card-header">
                    <div class="stat-icon icon-primary">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="stat-card-body">
                    <div class="stat-label">Total Mahasiswa</div>
                    <div class="stat-value">{{ $stats['total'] }}</div>
                </div>
            </div>
        </div>

        <div class="stats-col">
            <div class="stat-card card-success">
                <div class="stat-card-header">
                    <div class="stat-icon icon-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="stat-card-body">
                    <div class="stat-label">Verified (Lulus)</div>
                    <div class="stat-value">{{ $stats['verified'] }}</div>
                </div>
            </div>
        </div>

        <div class="stats-col">
            <div class="stat-card card-danger">
                <div class="stat-card-header">
                    <div class="stat-icon icon-danger">
                        <i class="fas fa-times-circle"></i>
                    </div>
                </div>
                <div class="stat-card-body">
                    <div class="stat-label">Rejected</div>
                    <div class="stat-value">{{ $stats['rejected'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-card">
        <form action="{{ route('admin.farmasi.index') }}" method="GET">
            <div class="row align-items-end">
                <div class="col-lg-4 col-md-6 mb-3 mb-lg-0">
                    <label class="filter-label">Cari Mahasiswa</label>
                    <div class="input-group-modern">
                        <i class="fas fa-search icon-prefix"></i>
                        <input type="text" name="search" class="form-control form-control-modern" placeholder="Nama atau Email..." value="{{ $search }}">
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                    <label class="filter-label">Status</label>
                    <select name="status" class="form-control select-modern">
                        <option value="">Semua Status</option>
                        <option value="pending"      {{ $filterStatus == 'pending'      ? 'selected' : '' }}>Menunggu</option>
                        <option value="verified"     {{ $filterStatus == 'verified'     ? 'selected' : '' }}>Lulus</option>
                        <option value="rejected"     {{ $filterStatus == 'rejected'     ? 'selected' : '' }}>Tidak Lulus</option>
                        <option value="unprocessed"  {{ $filterStatus == 'unprocessed'  ? 'selected' : '' }}>Belum Diproses</option>
                    </select>
                </div>
                <div class="col-lg-5 col-md-12 text-lg-right">
                    <button type="submit" class="btn btn-primary-modern">
                        <i class="fas fa-filter mr-2"></i> Terapkan Filter
                    </button>
                    <a href="{{ route('admin.farmasi.index') }}" class="btn btn-reset-modern ml-2">
                        <i class="fas fa-redo mr-2"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Table Section -->
    <div class="table-card">
        @if(session('success'))
            <div class="alert alert-success-modern alert-modern">
                <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            </div>
        @endif

        <div class="table-responsive">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 80px;">#</th>
                        <th>Identitas Mahasiswa</th>
                        <th>Email Siswa</th>
                        <th>Verifikasi Status</th>
                        <th>Update Terakhir</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $row)
                        @php
                            $existing = DB::table('farmasi_verifications')
                                          ->where('email', $row->email)
                                          ->first();
                        @endphp
                    
                        <tr>
                            <td class="text-center">
                                <span class="badge badge-light" style="font-size: 0.875rem; padding: 0.5rem 0.75rem; border-radius: 8px; background: var(--bg-light); color: var(--text-muted); font-weight: 600;">{{ $row->id }}</span>
                            </td>
                            <td>
                                <div class="student-name">{{ strtoupper($row->nama_lengkap) }}</div>
                                <div class="student-meta">Moodle ID: {{ $row->moodle_user_id }}</div>
                            </td>
                            <td>
                                <span style="color: var(--text-dark); font-weight: 500;">{{ $row->email }}</span>
                            </td>
                            <td>
                                <form action="{{ route('admin.farmasi.validate', $row->id) }}" method="POST" class="d-flex align-items-center">
                                    @csrf
                                    <select name="status_verifikasi" class="select-modern mr-2">
                                        <option value="pending" {{ ($existing->status_verifikasi ?? '') == 'pending' ? 'selected' : '' }}>Proses</option>
                                        <option value="verified" {{ ($existing->status_verifikasi ?? '') == 'verified' ? 'selected' : '' }}>Lulus</option>
                                        <option value="rejected" {{ ($existing->status_verifikasi ?? '') == 'rejected' ? 'selected' : '' }}>Tidak Lulus</option>
                                    </select>
                                    <button type="submit" class="btn btn-save-modern">Simpan</button>
                                </form>
                            </td>
                            <td>
                                @if($existing)
                                    <div class="date-info">
                                        <i class="far fa-calendar-alt mr-1"></i> {{ \Carbon\Carbon::parse($existing->updated_at)->translatedFormat('d M Y') }}
                                    </div>
                                    <div class="date-meta">
                                        <i class="far fa-clock mr-1"></i> {{ \Carbon\Carbon::parse($existing->updated_at)->format('H:i') }}
                                    </div>
                                @else
                                    <span class="status-badge badge-unprocessed">BELUM DIPROSES</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        <i class="fas fa-folder-open"></i>
                                    </div>
                                    <p class="empty-text">Tidak ada data mahasiswa yang ditemukan</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection