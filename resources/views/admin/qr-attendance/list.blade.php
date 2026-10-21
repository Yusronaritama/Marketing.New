@extends('layouts.admin.app')
@section('title', 'Daftar Hadir - ' . $sesi)

@section('content')
<div class="cyberpunk-download-container">

    <nav class="cyberpunk-breadcrumb">
        <ol class="cyberpunk-breadcrumb-list">
            <li><a href="{{ route('admin.dashboard.index') }}" class="cyberpunk-breadcrumb-link">Dashboard</a></li>
            <li class="cyberpunk-breadcrumb-separator">/</li>
            <li><a href="{{ route('admin.qr-attendance.index') }}" class="cyberpunk-breadcrumb-link">QR Absensi</a></li>
            <li class="cyberpunk-breadcrumb-separator">/</li>
            <li class="cyberpunk-breadcrumb-active">Daftar Hadir</li>
        </ol>
    </nav>

    <div class="cyberpunk-header">
        <div class="cyberpunk-header-content">
            <h1 class="cyberpunk-title">Daftar Hadir</h1>
            <p class="cyberpunk-subtitle">Sesi: <strong>{{ $sesi }}</strong> — Total hadir: <strong>{{ $list->total() }}</strong> mahasiswa</p>
        </div>
    </div>

    {{-- Filter Sesi --}}
    <form method="GET" style="display:flex; gap:10px; margin-bottom:20px; align-items:center;">
        <select name="sesi" class="custom-select" style="max-width:280px;">
            @foreach($daftarSesi as $s)
                <option value="{{ $s }}" {{ $s === $sesi ? 'selected' : '' }}>{{ $s }}</option>
            @endforeach
        </select>
        <button type="submit" style="padding:9px 18px; background:#4f46e5; color:#fff; border:none; border-radius:8px; cursor:pointer; font-weight:600;">
            Tampilkan
        </button>
    </form>

    <div style="background:#fff; border-radius:14px; box-shadow:0 2px 12px rgba(0,0,0,0.06); overflow:hidden; border:1px solid #f0f0f5;">
        <table style="width:100%; border-collapse:collapse; font-size:0.88rem;">
            <thead>
                <tr style="background:#10b981; color:#fff;">
                    <th style="padding:12px 16px; text-align:left;">No</th>
                    <th style="padding:12px 16px; text-align:left;">Nama Mahasiswa</th>
                    <th style="padding:12px 16px; text-align:left;">Email</th>
                    <th style="padding:12px 16px; text-align:left;">Prodi Pilihan 1</th>
                    <th style="padding:12px 16px; text-align:center;">Waktu Scan</th>
                    <th style="padding:12px 16px; text-align:center;">IP Scanner</th>
                </tr>
            </thead>
            <tbody>
                @forelse($list as $i => $qr)
                    @php
                        $final = $qr->user?->dataMahasiswaFinal;
                        $ereg  = $qr->user?->masterEreg;
                        $nama  = $final?->nama_mahasiswa ?? $qr->user?->name ?? '-';
                    @endphp
                    <tr style="border-bottom:1px solid #f3f4f6; {{ $loop->even ? 'background:#f9fafb;' : '' }}">
                        <td style="padding:12px 16px; color:#9ca3af;">{{ $list->firstItem() + $loop->index }}</td>
                        <td style="padding:12px 16px; font-weight:600; color:#1f2937;">{{ $nama }}</td>
                        <td style="padding:12px 16px; color:#6b7280;">{{ $qr->user?->email ?? '-' }}</td>
                        <td style="padding:12px 16px; color:#374151;">{{ $ereg?->jurusan1 ?? '-' }}</td>
                        <td style="padding:12px 16px; text-align:center; color:#065f46; font-weight:600;">
                            {{ $qr->scanned_at?->format('d/m/Y H:i:s') ?? '-' }}
                        </td>
                        <td style="padding:12px 16px; text-align:center; color:#9ca3af; font-size:0.8rem;">
                            {{ $qr->scanned_by ?? '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding:40px; text-align:center; color:#9ca3af;">
                            Belum ada mahasiswa yang melakukan scan untuk sesi ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:20px;">
        {{ $list->links() }}
    </div>

</div>
@endsection