{{-- FILE: resources/views/mahasiswa/pembayaran/index.blade.php --}}

@extends('layouts.mahasiswa.pembayaran-layout')
@section('title', 'Buat Pembayaran')

@section('content')

@php
    // Inisialisasi Agent untuk deteksi device
    $agent = new \Jenssegers\Agent\Agent();
@endphp

{{-- Jika user menggunakan Mobile (HP atau Tablet) --}}
@if($agent->isMobile() || $agent->isTablet())
    @include('mahasiswa.pembayaran.generate-payment.partials.mobile')

{{-- Jika user menggunakan Desktop (Laptop/PC) --}}
@else
    @include('mahasiswa.pembayaran.generate-payment.partials.desktop')
@endif

@endsection

