@extends('layouts.app')

@section('content')
<div class="container text-center">
    <div class="card col-md-6 mx-auto">
        <div class="card-body">
            <h3>Status Pembayaran</h3>
            <hr>
            <p>Halo, <strong>{{ $user->name }}</strong></p>
            
            <div class="alert {{ $user->va_status == 'PAID' ? 'alert-success' : 'alert-info' }}">
                Status Saat Ini: <strong>{{ $user->va_status ?? 'BELUM DIBAYAR' }}</strong>
            </div>

            <table class="table text-left">
                <tr>
                    <td>Nomor VA</td>
                    <td>: {{ $user->va_number }}</td>
                </tr>
                <tr>
                    <td>Total Tagihan</td>
                    <td>: Rp {{ number_format($user->va_amount, 0, ',', '.') }}</td>
                </tr>
            </table>

            @if($user->va_status == 'PAID')
                <p class="text-success font-weight-bold">Terima kasih! Pembayaran Anda telah kami terima.</p>
                <a href="/dashboard" class="btn btn-primary">Lanjut ke Dashboard</a>
            @else
                <p class="text-danger">Silakan lakukan pembayaran sebelum: <br> 
                   <strong>{{ \Carbon\Carbon::parse($user->va_expired_at)->format('d M Y H:i') }}</strong>
                </p>
            @endif
        </div>
    </div>
</div>
@endsection