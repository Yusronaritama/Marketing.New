<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Status - {{ config('app.name') }}</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }
        .status-card {
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .status-card:hover {
            transform: translateY(-2px);
        }
        .whatsapp-green {
            color: #25D366;
        }
        .whatsapp-bg-green {
            background-color: #25D366;
        }
        .qr-container {
            max-width: 300px;
            margin: 0 auto;
            border: 2px dashed #25D366;
            border-radius: 10px;
            padding: 20px;
            background-color: white;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h3">
                        <i class="fab fa-whatsapp whatsapp-green me-2"></i>
                        WhatsApp Status
                    </h1>
                    <a href="{{ route('admin.dashboard.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>
                </div>
                <p class="text-muted">Monitor koneksi WhatsApp dan kirim pesan</p>
            </div>
        </div>

        <!-- Status Cards -->
        <div class="row mb-4">
            <!-- Server Status -->
            <div class="col-md-4 mb-3">
                <div class="card status-card border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle p-3 me-3" style="background-color: #e8f5e9;">
                                <i class="fas fa-server fa-2x whatsapp-green"></i>
                            </div>
                            <div>
                                <h5 class="card-title mb-0">Server Status</h5>
                                <p class="text-muted mb-0">{{ config('whatsapp.server.url') }}</p>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            @if($status['success'])
                                <span class="badge bg-success rounded-pill">
                                    <i class="fas fa-check-circle me-1"></i> Online
                                </span>
                                <small class="text-muted">Terakhir: {{ $status['timestamp']->diffForHumans() }}</small>
                            @else
                                <span class="badge bg-danger rounded-pill">
                                    <i class="fas fa-times-circle me-1"></i> Offline
                                </span>
                                <small class="text-danger">Error: {{ $status['error'] ?? 'Unknown' }}</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- WhatsApp Status -->
            <div class="col-md-4 mb-3">
                <div class="card status-card border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle p-3 me-3" style="background-color: #e8f5e9;">
                                <i class="fab fa-whatsapp fa-2x whatsapp-green"></i>
                            </div>
                            <div>
                                <h5 class="card-title mb-0">WhatsApp Status</h5>
                                <p class="text-muted mb-0">Koneksi WhatsApp</p>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            @if($isReady)
                                <span class="badge bg-success rounded-pill">
                                    <i class="fas fa-check-circle me-1"></i> Connected
                                </span>
                                @if(isset($status['status']['data']['myNumber']))
                                <small class="text-muted">{{ $status['status']['data']['myNumber'] }}</small>
                                @endif
                            @else
                                <span class="badge bg-warning rounded-pill">
                                    <i class="fas fa-exclamation-triangle me-1"></i> Not Connected
                                </span>
                                <small class="text-muted">Scan QR untuk koneksi</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="col-md-4 mb-3">
                <div class="card status-card border-0">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="fas fa-cogs me-2"></i>Actions
                        </h5>
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.whatsapp.test') }}" class="btn btn-outline-primary">
                                <i class="fas fa-paper-plane me-1"></i> Test Kirim Pesan
                            </a>
                            <div class="btn-group" role="group">
                                <form action="{{ route('admin.whatsapp.restart') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-warning" onclick="return confirm('Restart WhatsApp client?')">
                                        <i class="fas fa-redo me-1"></i> Restart
                                    </button>
                                </form>
                                <form action="{{ route('admin.whatsapp.delete-session') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Hapus session WhatsApp? Anda perlu scan QR lagi.')">
                                        <i class="fas fa-trash me-1"></i> Delete Session
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- QR Code Section -->
        @if(!$isReady && isset($qrCode['dataUrl']))
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="fas fa-qrcode me-2"></i>Scan QR Code
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="qr-container mb-3">
                            <img src="{{ $qrCode['dataUrl'] }}" alt="WhatsApp QR Code" class="img-fluid">
                        </div>
                        <p class="text-muted">
                            Buka WhatsApp di smartphone Anda, ketuk <strong>Menu</strong> → <strong>Linked Devices</strong> → <strong>Link a Device</strong> → Scan QR code ini
                        </p>
                        <button class="btn btn-success" onclick="refreshQR()">
                            <i class="fas fa-sync-alt me-1"></i> Refresh QR
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Detailed Status -->
        @if($status['success'] && isset($status['status']['data']))
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>Detailed Status
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <tbody>
                                    @foreach($status['status']['data'] as $key => $value)
                                        @if(!is_array($value) && $key !== 'lastQr')
                                        <tr>
                                            <th width="30%">{{ ucwords(str_replace('_', ' ', $key)) }}</th>
                                            <td>
                                                @if(is_bool($value))
                                                    @if($value)
                                                        <span class="badge bg-success">Yes</span>
                                                    @else
                                                        <span class="badge bg-danger">No</span>
                                                    @endif
                                                @else
                                                    {{ $value }}
                                                @endif
                                            </td>
                                        </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function refreshQR() {
            fetch('{{ route("admin.whatsapp.qr") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data.dataUrl) {
                        document.querySelector('.qr-container img').src = data.data.dataUrl;
                        alert('QR code berhasil diperbarui!');
                    }
                });
        }

        // Auto-refresh status setiap 30 detik
        setInterval(() => {
            fetch('{{ route("admin.whatsapp.test-connection") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.is_ready) {
                        location.reload();
                    }
                });
        }, 30000);
    </script>
</body>
</html>