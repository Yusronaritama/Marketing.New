@extends('layouts.admin.app')

@section('title', 'WhatsApp QR Code')

@section('content')
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">
                <i class="fas fa-qrcode me-2"></i>Scan QR WhatsApp
                <span class="float-end">
                    <a href="{{ route('admin.whatsapp.qr') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </a>
                </span>
            </h4>
        </div>

        <div class="card-body">
            {{-- Notifikasi Status --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Notifikasi Error dari Controller --}}
            @if($error)
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i> {{ $error }}
                </div>
            @endif

            @if($message && !$success)
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle me-2"></i> {{ $message }}
                </div>
            @endif

            {{-- Status Informasi --}}
            @if($status && is_array($status))
                <div class="alert alert-info mb-3">
                    <h6><i class="fas fa-info-circle me-2"></i>Status WhatsApp:</h6>
                    
                    {{-- Tampilkan status summary jika ada --}}
                    @if(isset($status['statusSummary']) && is_array($status['statusSummary']))
                        @if(isset($status['statusSummary']['message']))
                            <div class="alert alert-{{ $status['statusSummary']['color'] ?? 'info' }} mb-2">
                                @if(isset($status['statusSummary']['icon']))
                                    <strong>{{ $status['statusSummary']['icon'] }} {{ $status['statusSummary']['status'] ?? '' }}</strong><br>
                                @else
                                    <strong>{{ $status['statusSummary']['status'] ?? '' }}</strong><br>
                                @endif
                                {{ $status['statusSummary']['message'] ?? '' }}
                            </div>
                        @endif
                    @endif
                    
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="mb-0">
                                {{-- Connected --}}
                                <li><strong>Connected:</strong>
                                    @if(isset($status['connected']) && $status['connected'])
                                        <span class="badge bg-success">✅ Terkoneksi</span>
                                    @elseif(isset($status['connected']) && !$status['connected'])
                                        <span class="badge bg-danger">❌ Tidak Terkoneksi</span>
                                    @else
                                        <span class="badge bg-secondary">❓ Tidak diketahui</span>
                                    @endif
                                </li>
                                
                                {{-- Authenticated --}}
                                <li><strong>Authenticated:</strong>
                                    @if(isset($status['authenticated']) && $status['authenticated'])
                                        <span class="badge bg-success">✅ Ya</span>
                                    @elseif(isset($status['authenticated']) && !$status['authenticated'])
                                        <span class="badge bg-warning">❌ Belum</span>
                                    @else
                                        <span class="badge bg-secondary">❓ Tidak diketahui</span>
                                    @endif
                                </li>
                                
                                {{-- Ready --}}
                                <li><strong>Ready:</strong>
                                    @if(isset($status['ready']) && $status['ready'])
                                        <span class="badge bg-success">✅ Ya</span>
                                    @elseif(isset($status['ready']) && !$status['ready'])
                                        <span class="badge bg-danger">❌ Tidak</span>
                                    @else
                                        <span class="badge bg-secondary">❓ Tidak diketahui</span>
                                    @endif
                                </li>
                                
                                {{-- My Number --}}
                                @if(isset($status['myNumber']) && $status['myNumber'])
                                    <li><strong>Nomor:</strong> {{ $status['myNumber'] }}</li>
                                @endif
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="mb-0">
                                {{-- Has QR --}}
                                <li><strong>Has QR:</strong>
                                    @if(isset($status['hasQr']) && $status['hasQr'])
                                        <span class="badge bg-success">✅ Ya</span>
                                    @elseif(isset($status['hasQr']) && !$status['hasQr'])
                                        <span class="badge bg-secondary">❌ Tidak</span>
                                    @else
                                        <span class="badge bg-secondary">❓ Tidak diketahui</span>
                                    @endif
                                </li>
                                
                                {{-- Session Exists --}}
                                <li><strong>Session Exists:</strong>
                                    @if(isset($status['sessionExists']) && $status['sessionExists'])
                                        <span class="badge bg-success">✅ Ya</span>
                                    @elseif(isset($status['sessionExists']) && !$status['sessionExists'])
                                        <span class="badge bg-secondary">❌ Tidak</span>
                                    @else
                                        <span class="badge bg-secondary">❓ Tidak diketahui</span>
                                    @endif
                                </li>
                                
                                {{-- Last Error --}}
                                @if(isset($status['lastError']) && $status['lastError'])
                                    <li><strong>Error:</strong> <span class="text-danger">{{ $status['lastError'] }}</span></li>
                                @endif
                                
                                {{-- Uptime --}}
                                @if(isset($status['uptimeSeconds']) && $status['uptimeSeconds'])
                                    <li><strong>Uptime:</strong> {{ $status['uptimeSeconds'] }} detik</li>
                                @endif
                                
                                {{-- Platform --}}
                                @if(isset($status['platform']) && $status['platform'])
                                    <li><strong>Platform:</strong> {{ $status['platform'] }}</li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            @elseif($status && !is_array($status))
                <div class="alert alert-warning mb-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Status tidak dalam format yang diharapkan. Data: {{ json_encode($status) }}
                </div>
            @else
                <div class="alert alert-warning mb-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Status WhatsApp tidak tersedia atau belum diambil.
                </div>
            @endif

            {{-- QR Code Display --}}
            <div class="text-center">
                @if($qr && $success)
                    <p class="mb-3">
                        <i class="fas fa-mobile-alt me-2"></i>
                        Silakan scan QR code berikut melalui WhatsApp di ponsel Anda:
                    </p>
                    
                    <div class="mb-3">
                        <img src="{{ $qr }}" 
                             alt="WhatsApp QR Code" 
                             id="qrImage"
                             class="img-fluid shadow-sm"
                             style="max-width: 300px; border: 10px solid #f8f9fa; border-radius: 10px;">
                    </div>
                    
                    <div class="text-muted small mb-4">
                        <i class="fas fa-clock me-1"></i>
                        QR code akan berubah setiap 60 detik. Jika QR tidak terbaca, refresh halaman ini.
                    </div>
                    
                    <div class="mb-3">
                        <a href="whatsapp://scan?code={{ $qr }}" 
                           class="btn btn-success btn-lg"
                           onclick="alert('Buka WhatsApp di ponsel Anda, lalu pilih Linked Devices → Scan QR Code')">
                            <i class="fab fa-whatsapp me-2"></i> Buka WhatsApp untuk Scan
                        </a>
                    </div>
                @else
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>Tidak ada QR code tersedia.</strong>
                        <p class="mb-0 mt-2">
                            @if($status && is_array($status) && isset($status['connected']) && $status['connected'] && isset($status['authenticated']) && $status['authenticated'])
                                WhatsApp sudah terkoneksi dengan nomor {{ $status['myNumber'] ?? 'tidak diketahui' }}.
                                Tidak perlu scan QR lagi.
                            @else
                                Mungkin WhatsApp sedang dalam proses inisialisasi atau sudah terautentikasi.
                                Cek status di atas atau tunggu beberapa saat lalu refresh halaman.
                            @endif
                        </p>
                    </div>
                @endif
            </div>
            
            {{-- Action Buttons --}}
            <div class="row mt-4 pt-3 border-top">
                <div class="col-md-12">
                    <h6><i class="fas fa-cogs me-2"></i>Aksi:</h6>
                    <div class="btn-group">
                        <a href="{{ route('admin.whatsapp.qr') }}" class="btn btn-primary">
                            <i class="fas fa-sync-alt me-2"></i> Refresh Status
                        </a>
                        
                        <form action="{{ route('admin.whatsapp.restart') }}" method="POST" class="d-inline" onsubmit="return confirm('Restart WhatsApp client?')">
                            @csrf
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-redo me-2"></i> Restart Client
                            </button>
                        </form>
                        
                        <form action="{{ route('admin.whatsapp.delete-session') }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus session WhatsApp? Ini akan memerlukan scan QR lagi.')">
                            @csrf
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash me-2"></i> Hapus Session
                            </button>
                        </form>
                        
                        <a href="{{ route('admin.whatsapp.status') }}" class="btn btn-info">
                            <i class="fas fa-chart-bar me-2"></i> Status Detail
                        </a>
                        
                        <a href="{{ route('admin.whatsapp.test') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-vial me-2"></i> Test Kirim Pesan
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card-footer text-center">
            <small class="text-muted">
                <i class="fas fa-clock me-1"></i>
                Terakhir diperbarui: {{ now()->format('d-m-Y H:i:s') }}
            </small>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Auto-refresh QR setiap 30 detik jika ada QR
@if($qr && $success && isset($status) && is_array($status) && isset($status['authenticated']) && !$status['authenticated'])
setTimeout(function() {
    window.location.reload();
}, 30000);
@endif

// Fungsi untuk test koneksi
function testConnection() {
    fetch('{{ route('admin.whatsapp.test-connection') }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ Koneksi berhasil!\nReady: ' + (data.is_ready ? 'Ya' : 'Tidak'));
            } else {
                alert('❌ Koneksi gagal: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            alert('❌ Test koneksi gagal: ' + error.message);
        });
}
</script>
@endpush
@endsection