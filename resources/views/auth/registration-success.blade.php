{{-- resources/views/auth/registration-success.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">🎉 Registrasi Berhasil!</h4>
                </div>
                
                <div class="card-body">
                    @if(session('registration_details'))
                        @php $details = session('registration_details'); @endphp
                        
                        <div class="alert alert-success">
                            <h5>Selamat, pendaftaran Anda berhasil!</h5>
                            <p>Informasi login telah dikirim ke WhatsApp Anda.</p>
                        </div>
                        
                        <div class="info-box mb-4">
                            <h5 class="text-primary">📋 Informasi Login</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Username:</strong>
                                    <div class="bg-light p-2 mt-1 rounded">
                                        <code>{{ $details['username'] }}</code>
                                        <button class="btn btn-sm btn-outline-secondary copy-btn ms-2" 
                                                data-text="{{ $details['username'] }}">
                                            Salin
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <strong>Password:</strong>
                                    <div class="bg-light p-2 mt-1 rounded">
                                        <code>{{ $details['password'] }}</code>
                                        <button class="btn btn-sm btn-outline-secondary copy-btn ms-2" 
                                                data-text="{{ $details['password'] }}">
                                            Salin
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-warning mt-3">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Simpan informasi login ini!</strong> Jangan bagikan kepada siapapun.
                            </div>
                        </div>
                        
                        <div class="info-box mb-4">
                            <h5 class="text-primary">💳 Informasi Pembayaran</h5>
                            <ul class="list-group">
                                <li class="list-group-item">
                                    <strong>Virtual Account:</strong> 
                                    <code>{{ $details['va_number'] }}</code>
                                    <button class="btn btn-sm btn-outline-secondary copy-btn ms-2" 
                                            data-text="{{ $details['va_number'] }}">
                                        Salin
                                    </button>
                                </li>
                                <li class="list-group-item">
                                    <strong>Jumlah:</strong> {{ $details['va_amount'] }}
                                </li>
                                <li class="list-group-item">
                                    <strong>Status:</strong> 
                                    <span class="badge bg-{{ $details['va_status'] === 'PAID' ? 'success' : 'warning' }}">
                                        {{ $details['va_status'] === 'PAID' ? 'LUNAS' : 'MENUNGGU' }}
                                    </span>
                                </li>
                                <li class="list-group-item">
                                    <strong>Batas Waktu:</strong> {{ $details['expired_at'] }}
                                </li>
                                <li class="list-group-item">
                                    <strong>Gelombang:</strong> {{ $details['gelombang'] }}
                                </li>
                            </ul>
                        </div>
                        
                        <div class="alert alert-info">
                            <h6><i class="fas fa-whatsapp"></i> Pengiriman WhatsApp</h6>
                            <p>
                                @if($details['whatsapp_sent'])
                                    ✅ Informasi login telah dikirim ke WhatsApp Anda.
                                    <br><small class="text-muted">Cek pesan masuk di WhatsApp Anda.</small>
                                @else
                                    ⚠ Informasi login akan dikirim ke WhatsApp Anda dalam beberapa menit.
                                    <br><small class="text-muted">Jika tidak menerima dalam 5 menit, hubungi admin.</small>
                                @endif
                            </p>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <a href="{{ route('mahasiswa.dashboard') }}" class="btn btn-primary btn-lg">
                                <i class="fas fa-tachometer-alt"></i> Ke Dashboard
                            </a>
                            <a href="{{ route('mahasiswa.va.dashboard') }}" class="btn btn-outline-primary">
                                <i class="fas fa-credit-card"></i> Lihat Detail Pembayaran
                            </a>
                            <button class="btn btn-outline-success" onclick="printRegistrationDetails()">
                                <i class="fas fa-print"></i> Cetak Informasi
                            </button>
                        </div>
                        
                    @else
                        <div class="alert alert-warning">
                            Informasi registrasi tidak ditemukan. Silakan login dengan akun Anda.
                        </div>
                        <a href="{{ route('login') }}" class="btn btn-primary">Ke Halaman Login</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Copy to clipboard functionality
    document.querySelectorAll('.copy-btn').forEach(button => {
        button.addEventListener('click', function() {
            const text = this.getAttribute('data-text');
            navigator.clipboard.writeText(text).then(() => {
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-check"></i> Tersalin';
                this.classList.remove('btn-outline-secondary');
                this.classList.add('btn-success');
                
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.classList.remove('btn-success');
                    this.classList.add('btn-outline-secondary');
                }, 2000);
            });
        });
    });
    
    function printRegistrationDetails() {
        window.print();
    }
</script>
@endpush

@endsection