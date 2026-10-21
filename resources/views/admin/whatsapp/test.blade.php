<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test WhatsApp - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">
                            <i class="fab fa-whatsapp me-2"></i>Test Kirim WhatsApp
                        </h4>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <!-- Form Send Single Message -->
                        <h5 class="mb-3">Kirim Pesan Tunggal</h5>
                        <form action="{{ route('admin.whatsapp.send') }}" method="POST" class="mb-4">
                            @csrf
                            <div class="mb-3">
                                <label for="phone" class="form-label">Nomor WhatsApp</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-phone"></i>
                                    </span>
                                    <input type="text" class="form-control" id="phone" name="phone" 
                                           placeholder="6281234567890" required>
                                </div>
                                <small class="text-muted">Contoh: 6281234567890 (tanpa +)</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="message" class="form-label">Pesan</label>
                                <textarea class="form-control" id="message" name="message" 
                                          rows="4" required placeholder="Tulis pesan Anda di sini..."></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="template" class="form-label">Template (Opsional)</label>
                                <select class="form-select" id="template" name="template">
                                    <option value="">-- Pilih Template --</option>
                                    @foreach(config('whatsapp.templates') as $key => $template)
                                        <option value="{{ $key }}">{{ $template['subject'] }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Jika memilih template, pesan di atas akan diabaikan</small>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="use_queue" name="use_queue" value="1" checked>
                                <label class="form-check-label" for="use_queue">Gunakan Queue</label>
                                <small class="text-muted d-block">Jika diaktifkan, pesan akan dikirim melalui background process</small>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i>Kirim Pesan
                                </button>
                            </div>
                        </form>
                        
                        <hr class="my-4">
                        
                        <!-- Form Broadcast -->
                        <h5 class="mb-3">Broadcast ke Banyak Nomor</h5>
                        <form action="{{ route('admin.whatsapp.broadcast') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="broadcast_phones" class="form-label">Nomor WhatsApp (pisahkan dengan koma)</label>
                                <textarea class="form-control" id="broadcast_phones" name="phones" 
                                          rows="3" required placeholder="6281234567890, 6289876543210, 6281122334455"></textarea>
                                <small class="text-muted">Pisahkan setiap nomor dengan koma</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="broadcast_message" class="form-label">Pesan Broadcast</label>
                                <textarea class="form-control" id="broadcast_message" name="message" 
                                          rows="4" required placeholder="Tulis pesan broadcast di sini..."></textarea>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-bullhorn me-2"></i>Kirim Broadcast
                                </button>
                            </div>
                        </form>
                        
                        <hr class="my-4">
                        
                        <!-- Test Connection -->
                        <div class="text-center">
                            <a href="{{ route('admin.whatsapp.status') }}" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-arrow-left me-1"></i>Kembali ke Status
                            </a>
                            <button onclick="testConnection()" class="btn btn-outline-info">
                                <i class="fas fa-wifi me-1"></i>Test Koneksi
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Predefined Test Numbers -->
                <div class="card mt-4 shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>Nomor Test (Dari Database)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @php
                                // Contoh nomor dari database
                                $testNumbers = [
                                    '6285795950577',
                                    '62811998855337',
                                    '6285893715593',
                                    '62899001155243',
                                    '6281316712924'
                                ];
                            @endphp
                            @foreach($testNumbers as $number)
                            <div class="col-md-6 col-lg-4 mb-2">
                                <button type="button" class="btn btn-outline-dark w-100 text-start" onclick="setPhone('{{ $number }}')">
                                    <i class="fas fa-mobile-alt me-2"></i>{{ $number }}
                                </button>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function setPhone(phone) {
            document.getElementById('phone').value = phone;
        }
        
        function testConnection() {
            fetch('{{ route("admin.whatsapp.test-connection") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.is_ready) {
                        alert('✅ WhatsApp terhubung dengan baik!');
                    } else {
                        alert('❌ WhatsApp tidak terhubung. Silakan cek status.');
                    }
                });
        }
        
        // Auto-fill test message
        document.getElementById('message').value = "Halo, ini adalah pesan test dari sistem {{ config('app.name') }}.\n\nDikirim pada: " + new Date().toLocaleString() + "\n\nTerima kasih.";
        
        document.getElementById('broadcast_message').value = "Halo, ini adalah broadcast test dari sistem {{ config('app.name') }}.\n\nWaktu: " + new Date().toLocaleString() + "\n\nTerima kasih.";
    </script>
</body>
</html>