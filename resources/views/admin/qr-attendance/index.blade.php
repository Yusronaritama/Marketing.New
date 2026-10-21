@extends('layouts.admin.app')
@section('title', 'Manajemen QR Absensi')

@section('content')
<div class="cyberpunk-download-container">

    {{-- Breadcrumb --}}
    <nav class="cyberpunk-breadcrumb">
        <ol class="cyberpunk-breadcrumb-list">
            <li><a href="{{ route('admin.dashboard.index') }}" class="cyberpunk-breadcrumb-link">Dashboard</a></li>
            <li class="cyberpunk-breadcrumb-separator">/</li>
            <li class="cyberpunk-breadcrumb-active">QR Absensi</li>
        </ol>
    </nav>

    {{-- Flash Messages --}}
    @foreach(['success' => 'green', 'error' => 'red', 'info' => 'blue'] as $type => $color)
        @if(session($type))
            <div class="mb-4 p-4 rounded-lg bg-{{ $color }}-500/20 border border-{{ $color }}-500 text-{{ $color }}-200">
                {{ session($type) }}
            </div>
        @endif
    @endforeach

    {{-- Header --}}
    <div class="cyberpunk-header">
        <div class="cyberpunk-header-content">
            <h1 class="cyberpunk-title">QR Code Absensi</h1>
            <p class="cyberpunk-subtitle">Generate & kelola QR Code daftar hadir mahasiswa (single-use)</p>
        </div>
    </div>

    {{-- ── Bulk Generate & Sesi Selector ── --}}
    <div class="smart-export-card" style="margin-bottom: 24px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; align-items: end;">

            {{-- Form Bulk Generate --}}
            <form action="{{ route('admin.qr-attendance.generate-bulk') }}" method="POST">
                @csrf
                <h3 style="font-weight:700; font-size:1rem; margin-bottom:12px; color:#1f2937;">
                    ⚡ Generate Massal Semua Mahasiswa
                </h3>
                <div style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:10px;">
                    <input type="text" name="sesi" value="{{ $sesi }}"
                           placeholder="Nama sesi (misal: Orientasi 2025)"
                           required
                           style="flex:1; padding:9px 14px; border-radius:8px; border:1px solid #d1d5db; font-size:0.9rem; min-width:180px;">
                    <input type="datetime-local" name="expired_at"
                           style="flex:1; padding:9px 14px; border-radius:8px; border:1px solid #d1d5db; font-size:0.9rem; min-width:180px;"
                           title="Kosongkan jika QR tidak ada batas waktu (expired hanya setelah scan)">
                </div>
                <button type="submit" class="btn-generate" style="width:auto; padding: 10px 24px;"
                        onclick="return confirm('Generate QR massal untuk sesi ini? QR aktif sebelumnya akan di-expire.')">
                    Generate Semua QR
                </button>
            </form>

            {{-- Filter Sesi --}}
            <div>
                <h3 style="font-weight:700; font-size:1rem; margin-bottom:12px; color:#1f2937;">
                    📋 Lihat Sesi
                </h3>
                <form method="GET" action="{{ route('admin.qr-attendance.index') }}" style="display:flex; gap:10px;">
                    <select name="sesi" class="custom-select" style="flex:1;">
                        <option value="{{ $sesi }}" selected>{{ $sesi }}</option>
                        @foreach($daftarSesi as $s)
                            @if($s !== $sesi)
                                <option value="{{ $s }}">{{ $s }}</option>
                            @endif
                        @endforeach
                    </select>
                    <button type="submit" style="padding:9px 18px; background:#4f46e5; color:#fff; border:none; border-radius:8px; cursor:pointer; font-weight:600;">
                        Tampilkan
                    </button>
                </form>

                {{-- Statistik --}}
                <div style="display:flex; gap:12px; margin-top:14px; flex-wrap:wrap;">
                    @foreach([['label'=>'Total QR','val'=>$stats['total'],'color'=>'#6366f1'],['label'=>'Hadir','val'=>$stats['scanned'],'color'=>'#10b981'],['label'=>'Aktif','val'=>$stats['active'],'color'=>'#f59e0b'],['label'=>'Expired','val'=>$stats['expired'],'color'=>'#ef4444']] as $s)
                    <div style="background:{{ $s['color'] }}18; border:1px solid {{ $s['color'] }}44; border-radius:10px; padding:8px 16px; text-align:center; min-width:72px;">
                        <div style="font-size:1.4rem; font-weight:800; color:{{ $s['color'] }};">{{ $s['val'] }}</div>
                        <div style="font-size:0.72rem; color:#6b7280;">{{ $s['label'] }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Lihat Daftar Hadir --}}
    <div style="margin-bottom:20px;">
        <a href="{{ route('admin.qr-attendance.list', ['sesi' => $sesi]) }}"
           style="display:inline-flex; align-items:center; gap:8px; padding:10px 20px; background:#10b981; color:#fff; border-radius:8px; font-weight:600; text-decoration:none;">
            📋 Lihat Daftar Hadir Sesi "{{ $sesi }}"
        </a>
    </div>

    {{-- Search --}}
    <form method="GET" action="{{ route('admin.qr-attendance.index') }}"
          style="display:flex; gap:10px; margin-bottom:20px;">
        <input type="hidden" name="sesi" value="{{ $sesi }}">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Cari nama / email mahasiswa..."
               class="custom-select" style="max-width:380px;">
        <button type="submit" style="padding:9px 18px; background:#4f46e5; color:#fff; border:none; border-radius:8px; cursor:pointer; font-weight:600;">
            Cari
        </button>
    </form>

    {{-- Tabel Mahasiswa --}}
    <div style="background:#fff; border-radius:14px; box-shadow:0 2px 12px rgba(0,0,0,0.06); overflow:hidden; border:1px solid #f0f0f5;">
        <table style="width:100%; border-collapse:collapse; font-size:0.88rem;">
            <thead>
                <tr style="background:#4f46e5; color:#fff;">
                    <th style="padding:12px 16px; text-align:left;">No</th>
                    <th style="padding:12px 16px; text-align:left;">Nama Mahasiswa</th>
                    <th style="padding:12px 16px; text-align:left;">Email</th>
                    <th style="padding:12px 16px; text-align:center;">Status QR ({{ $sesi }})</th>
                    <th style="padding:12px 16px; text-align:center;">Waktu Scan</th>
                    <th style="padding:12px 16px; text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $i => $user)
                    @php
                        $qr      = $user->qrAttendances->first();
                        $nama    = $user->dataMahasiswaFinal->nama_mahasiswa ?? $user->name;
                        $statusColor = match($qr?->status) {
                            'active'  => ['bg'=>'#fef3c7','text'=>'#92400e','label'=>'Aktif'],
                            'scanned' => ['bg'=>'#d1fae5','text'=>'#065f46','label'=>'Hadir ✓'],
                            'expired' => ['bg'=>'#fee2e2','text'=>'#991b1b','label'=>'Expired'],
                            default   => ['bg'=>'#f3f4f6','text'=>'#6b7280','label'=>'Belum Ada QR'],
                        };
                    @endphp
                    <tr style="border-bottom:1px solid #f3f4f6; {{ $loop->even ? 'background:#fafafa;' : '' }}">
                        <td style="padding:12px 16px; color:#9ca3af;">{{ $users->firstItem() + $loop->index }}</td>
                        <td style="padding:12px 16px; font-weight:600; color:#1f2937;">{{ $nama }}</td>
                        <td style="padding:12px 16px; color:#6b7280;">{{ $user->email }}</td>
                        <td style="padding:12px 16px; text-align:center;">
                            <span style="background:{{ $statusColor['bg'] }}; color:{{ $statusColor['text'] }}; padding:3px 12px; border-radius:20px; font-size:0.78rem; font-weight:600;">
                                {{ $statusColor['label'] }}
                            </span>
                        </td>
                        <td style="padding:12px 16px; text-align:center; color:#6b7280; font-size:0.8rem;">
                            {{ $qr?->scanned_at?->format('d/m/Y H:i') ?? '-' }}
                        </td>
                        <td style="padding:12px 16px; text-align:center;">
                            <div style="display:flex; gap:6px; justify-content:center; flex-wrap:wrap;">
                                {{-- Generate / Regenerate QR --}}
                                <form action="{{ route('admin.qr-attendance.generate') }}" method="POST"
                                      onsubmit="return confirm('Generate QR baru untuk mahasiswa ini?')">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                                    <input type="hidden" name="sesi" value="{{ $sesi }}">
                                    <button type="submit"
                                            style="padding:5px 12px; background:#6366f1; color:#fff; border:none; border-radius:6px; cursor:pointer; font-size:0.78rem; font-weight:600;">
                                        {{ $qr ? 'Regenerate' : 'Generate' }} QR
                                    </button>
                                </form>

                                {{-- Lihat QR --}}
                                @if($qr && $qr->status === 'active')
                                    <a href="{{ route('qr.show', $user->id) }}" target="_blank"
                                       style="padding:5px 12px; background:#10b981; color:#fff; border-radius:6px; font-size:0.78rem; font-weight:600; text-decoration:none;">
                                        Lihat QR
                                    </a>
                                @endif

                                {{-- Expire Manual --}}
                                @if($qr && $qr->status === 'active')
                                    <form action="{{ route('admin.qr-attendance.expire', $qr->id) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                                style="padding:5px 12px; background:#ef4444; color:#fff; border:none; border-radius:6px; cursor:pointer; font-size:0.78rem; font-weight:600;">
                                            Expire
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding:40px; text-align:center; color:#9ca3af;">
                            Tidak ada data mahasiswa yang ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div style="margin-top:20px;">
        {{ $users->links() }}
    </div>

</div>
@endsection