@if (session('success'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl mb-4 shadow-sm">
        {{ session('success') }}
    </div>
@endif

@if (session('warning'))
    <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-xl mb-4 shadow-sm">
        {{ session('warning') }}
    </div>
@endif

@if (session('error'))
    <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl mb-4 shadow-sm">
        {{ session('error') }}
    </div>
@endif

@if(session('va_info'))
    <div class="bg-sky-50 border border-sky-200 text-sky-900 px-4 py-3 rounded-xl mb-4 shadow-sm">
        <div class="flex items-start">
            <svg class="h-5 w-5 mr-2 mt-0.5 text-sky-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
            </svg>
            <div>
                <p class="font-bold mb-1">Virtual Account Baru Berhasil Dibuat!</p>
                <p class="mb-2">Berikut adalah informasi Virtual Account baru Anda:</p>
                <div class="bg-white p-3 rounded-lg border border-sky-100">
                    <p class="mb-1"><strong>Nomor VA:</strong> <span class="font-mono">{{ session('va_info.va_number') }}</span></p>
                    <p class="mb-1"><strong>Jumlah:</strong> Rp {{ number_format(session('va_info.amount'), 0, ',', '.') }}</p>
                    <p class="mb-0"><strong>Berlaku hingga:</strong> {{ \Carbon\Carbon::parse(session('va_info.expired_at'))->format('d/m/Y H:i') }} WIB</p>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- ============================================================ --}}
{{-- VARIABEL PENGATURAN REDIRECT OTOMATIS BERDASARKAN PROFESI --}}
{{-- ============================================================ --}}
@php
    $isProfesi = isset($user) && $user->profesi === 'Apoteker Farmasi';
    $targetRoute = $isProfesi ? route('profesi.dashboard') : route('mahasiswa.dashboard');
    $dashboardName = $isProfesi ? 'Dashboard Profesi' : 'Dashboard Mahasiswa';
@endphp

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instruksi Pembayaran</title>

    @if (app()->environment('local', 'development'))
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style type="text/tailwindcss">
        @layer base { html { font-family: 'Plus Jakarta Sans', sans-serif; } }
        @layer utilities {
            .card-shadow { box-shadow: 0 10px 25px -5px rgba(14,165,233,0.1),0 8px 10px -6px rgba(14,165,233,0.05); }
            .gradient-sky-brown { background: linear-gradient(135deg,#0ea5e9 0%,#0c4a6e 50%,#78350f 100%); }
            .gradient-card { background: linear-gradient(135deg,#f0f9ff 0%,#e0f2fe 100%); }
            .hover-lift { transition: transform 0.3s ease,box-shadow 0.3s ease; }
            .hover-lift:hover { transform: translateY(-2px); box-shadow: 0 20px 35px -5px rgba(14,165,233,0.15); }
        }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'sky-custom': { 50:'#f0f9ff',100:'#e0f2fe',200:'#bae6fd',300:'#7dd3fc',400:'#38bdf8',500:'#0ea5e9',600:'#0284c7',700:'#0369a1',800:'#075985',900:'#0c4a6e' },
                        'brown-custom': { 50:'#fdf8f6',100:'#f2e8e5',200:'#eaddd7',300:'#e0cec7',400:'#d2bab0',500:'#bfa094',600:'#a18072',700:'#977669',800:'#846358',900:'#78350f' },
                    }
                }
            }
        }
    </script>

    {{-- ============================================================ --}}
    {{-- AUTO-LOGIN: Jika status paid, langsung redirect dinamis       --}}
    {{-- ============================================================ --}}
    @if(isset($user) && $user && ($user->va_status ?? '') === 'paid')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const overlay = document.getElementById('paid-redirect-overlay');
            if (overlay) overlay.style.display = 'flex';

            // Redirect dinamis ke target yang sesuai
            setTimeout(function () {
                window.location.href = '{{ $targetRoute }}';
            }, 1500);
        });
    </script>
    @endif
</head>

<body class="bg-gradient-to-br from-sky-50 via-blue-50 to-stone-50 text-gray-800 min-h-screen">

    {{-- ============================================================ --}}
    {{-- OVERLAY REDIRECT (hanya muncul jika status paid)             --}}
    {{-- ============================================================ --}}
    @if(isset($user) && $user && ($user->va_status ?? '') === 'paid')
    <div id="paid-redirect-overlay"
         style="display:none; position:fixed; inset:0; z-index:9999;
                background:rgba(16,185,129,0.97);
                flex-direction:column; align-items:center; justify-content:center;
                font-family:'Plus Jakarta Sans',sans-serif;">
        <div style="background:white; border-radius:24px; padding:48px 40px; text-align:center; max-width:400px; width:90%; box-shadow:0 25px 50px rgba(0,0,0,0.15);">
            <div style="width:72px; height:72px; background:#d1fae5; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
                <svg style="width:40px; height:40px; color:#059669;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h2 style="font-size:22px; font-weight:800; color:#065f46; margin-bottom:10px;">Pembayaran Terverifikasi!</h2>
            <p style="font-size:14px; color:#047857; margin-bottom:24px;">
                Halo, <strong>{{ $user->name ?? 'Mahasiswa' }}</strong>!<br>
                Pembayaran Anda telah berhasil dikonfirmasi.<br>
                Anda akan diarahkan ke dashboard...
            </p>
            <div style="width:100%; height:6px; background:#d1fae5; border-radius:99px; overflow:hidden;">
                <div style="height:100%; background:#059669; border-radius:99px;
                            animation: progress-bar 1.5s linear forwards;">
                </div>
            </div>
            <p style="font-size:12px; color:#6ee7b7; margin-top:12px;">Mengalihkan ke {{ $dashboardName }}...</p>
        </div>
        <style>
            @keyframes progress-bar {
                from { width: 0%; }
                to   { width: 100%; }
            }
        </style>
    </div>
    @endif

    <div class="min-h-screen flex flex-col">

        <header class="bg-white/80 backdrop-blur-lg shadow-sm sticky top-0 z-10 border-b border-sky-100">
            <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <div class="flex items-center space-x-2">
                        <img src="https://3.bp.blogspot.com/-9jB6Uhgglco/WlJW0uU9ekI/AAAAAAAANPQ/NPDKUvdd4DgoQdYP75_XlH94IFWRdYxQQCLcBGAs/s1600/logo-bank-mandiri.png" alt="MANDIRI Logo" class="h-6">
                        <img src="{{ asset('images/PENMABAR.gif') }}" alt="PENMABAR Logo" class="h-10">
                    </div>
                    <span class="font-bold text-xl bg-gradient-to-r from-sky-600 to-brown-custom-700 bg-clip-text text-transparent">PENMABAR</span>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-700 font-medium">{{ $user->name ?? '' }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a href="{{ route('logout') }}"
                            onclick="event.preventDefault(); this.closest('form').submit();"
                            class="text-sm font-semibold text-gray-700 hover:text-sky-600 transition-colors px-4 py-2 border border-gray-200 rounded-lg hover:bg-sky-50 hover:border-sky-200">
                            Logout
                        </a>
                    </form>
                </div>
            </div>
        </header>

        <main class="flex-grow py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                @if(isset($error_message))
                <div class="bg-rose-50 border-l-4 border-rose-400 text-rose-800 p-4 rounded-xl shadow-sm mb-6" role="alert">
                    <p class="font-bold">Terjadi Kesalahan</p>
                    <p>{{ $error_message }}</p>
                </div>
                @endif

                @if(!isset($user) || $user === null)
                    {{-- TAMPILAN JIKA VA TIDAK ADA --}}
                    <div class="max-w-3xl mx-auto">
                        <div class="bg-white rounded-3xl card-shadow p-8 hover-lift">
                            <div class="text-center mb-6">
                                <div class="inline-flex items-center justify-center w-20 h-20 bg-amber-100 rounded-full mb-4">
                                    <svg class="h-10 w-10 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.232 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                </div>
                                <h2 class="text-2xl font-bold text-gray-800 mb-2">Virtual Account Tidak Ditemukan</h2>
                                <p class="text-gray-600 mb-6">{{ $error_message ?? 'Gagal membuat Virtual Account saat registrasi.' }}</p>
                            </div>

                            @if(isset($can_regenerate) && $can_regenerate)
                                <div class="border border-amber-200 bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl p-6 mb-6">
                                    <h4 class="font-bold text-amber-900 mb-2 flex items-center">
                                        <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"></path>
                                        </svg>
                                        Buat Virtual Account Baru
                                    </h4>
                                    <p class="text-amber-800 text-sm mb-4">Anda dapat membuat Virtual Account baru untuk melanjutkan pembayaran pendaftaran.</p>
                                    <form action="{{ route('mahasiswa.va.regenerate') }}" method="POST" id="regenerateForm">
                                        @csrf
                                        <div class="mb-4 space-y-3">
                                            <div class="flex items-start">
                                                <input type="checkbox" id="confirmRegenerate" name="confirm" required class="mt-1 mr-3 h-5 w-5 text-sky-600 rounded focus:ring-sky-500">
                                                <label for="confirmRegenerate" class="text-sm text-gray-700">Saya setuju untuk membuat Virtual Account baru. VA sebelumnya (jika ada) akan digantikan dengan yang baru.</label>
                                            </div>
                                            <div class="flex items-start">
                                                <input type="checkbox" id="understandTerms" name="understand_terms" required class="mt-1 mr-3 h-5 w-5 text-sky-600 rounded focus:ring-sky-500">
                                                <label for="understandTerms" class="text-sm text-gray-700">Saya memahami bahwa Virtual Account akan expired dalam 24 jam.</label>
                                            </div>
                                        </div>
                                        <button type="submit" class="w-full bg-gradient-to-r from-sky-500 to-sky-600 text-white py-3 px-4 rounded-xl font-semibold hover:from-sky-600 hover:to-sky-700 focus:outline-none focus:ring-4 focus:ring-sky-300 transition-all duration-300 shadow-lg shadow-sky-200">
                                            Buat Virtual Account Baru
                                        </button>
                                    </form>
                                </div>
                            @endif

                            <div class="bg-gradient-to-br from-sky-50 to-blue-50 p-6 rounded-2xl border border-sky-100">
                                <h4 class="font-bold text-gray-800 mb-3 flex items-center">
                                    <svg class="h-5 w-5 mr-2 text-sky-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"></path>
                                    </svg>
                                    Butuh Bantuan?
                                </h4>
                                <ul class="text-sm text-gray-700 space-y-2">
                                    <li class="flex items-center"><span class="text-lg mr-2">📞</span><span>Hotline: (021) 1234-5678</span></li>
                                    <li class="flex items-center"><span class="text-lg mr-2">📧</span><span>Email: admin@penmabar.ac.id</span></li>
                                    <li class="flex items-center"><span class="text-lg mr-2">🕒</span><span>Jam Operasional: Senin - Jumat, 08:00 - 16:00 WIB</span></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                @else
                {{-- TAMPILAN NORMAL --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="space-y-6">
                        <div class="bg-white rounded-3xl card-shadow p-6 hover-lift">
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="text-xl font-bold text-gray-800 flex items-center">
                                    <span class="bg-gradient-to-r from-sky-500 to-sky-600 text-white p-2 rounded-xl mr-3">
                                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"></path>
                                            <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"></path>
                                        </svg>
                                    </span>
                                    Virtual Account
                                </h2>
                                <span class="px-4 py-2 rounded-full text-white text-sm font-semibold shadow-lg {{ $user->va_status == 'paid' ? 'bg-gradient-to-r from-emerald-500 to-emerald-600' : 'bg-gradient-to-r from-amber-500 to-amber-600' }}">
                                    {{ ucfirst($user->va_status ?? 'Pending') }}
                                </span>
                            </div>

                            @php
                                $now = now();
                                $expiredAt = \Carbon\Carbon::parse($user->va_expired_at);
                                $isPastDue = $now->gt($expiredAt);
                                $isExpiringSoon = $expiredAt->diffInHours($now) <= 2;
                            @endphp

                            @if(isset($can_regenerate) && $can_regenerate && $user->va_status !== 'paid' && !session('va_info') && ($isPastDue || $isExpiringSoon))
                            <div class="bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200 rounded-2xl p-5 mb-6">
                                <div class="flex items-start">
                                    <div class="bg-amber-100 rounded-full p-2 mr-3 flex-shrink-0">
                                        <svg class="h-5 w-5 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-bold text-amber-900 mb-2">Virtual Account perlu diperbarui</p>
                                        <p class="text-sm text-amber-800 mb-4">
                                            @if($user->va_expired_at && now()->gt($user->va_expired_at))
                                                Virtual Account Anda sudah expired pada {{ \Carbon\Carbon::parse($user->va_expired_at)->format('d/m/Y H:i') }}.
                                            @else
                                                Virtual Account Anda akan segera expired.
                                            @endif
                                        </p>
                                        <form action="{{ route('mahasiswa.va.regenerate') }}" method="POST">
                                            @csrf
                                            <div class="flex items-start mb-4">
                                                <input type="checkbox" id="confirmRegenerate{{ $user->id }}" name="confirm" required class="mt-1 mr-3 h-4 w-4 text-sky-600 rounded focus:ring-sky-500">
                                                <label for="confirmRegenerate{{ $user->id }}" class="text-sm text-amber-800">Saya setuju untuk membuat Virtual Account baru. VA sebelumnya akan digantikan.</label>
                                            </div>
                                            <button type="submit"
                                                onclick="return confirm('Apakah Anda yakin ingin membuat Virtual Account baru?')"
                                                class="bg-gradient-to-r from-amber-500 to-amber-600 text-white py-2.5 px-5 rounded-xl font-semibold hover:from-amber-600 hover:to-amber-700 focus:outline-none focus:ring-4 focus:ring-amber-300 transition-all text-sm shadow-lg shadow-amber-200">
                                                Buat Virtual Account Baru
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <div class="gradient-card border border-sky-100 rounded-2xl p-5 mb-5">
                                <p class="text-sm text-gray-600 mb-2 font-medium">Nomor Virtual Account</p>
                                <div class="flex items-center gap-3">
                                    <span id="va-number" class="text-2xl font-bold text-gray-800 tracking-wider flex-1 min-w-0 break-all">{{ $user->va_number }}</span>
                                    <button id="copy-button" onclick="copyVirtualAccount()"
                                        class="flex items-center space-x-2 text-sm font-semibold text-sky-600 hover:text-sky-700 transition-all px-4 py-2.5 rounded-xl hover:bg-white border border-sky-200 hover:border-sky-300 shadow-sm flex-shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                        <span id="copy-text" class="whitespace-nowrap">Salin</span>
                                        <span id="copied-text" class="whitespace-nowrap" style="display:none;">Tersalin!</span>
                                    </button>
                                </div>
                            </div>

                            <div class="flex items-center justify-between bg-gradient-to-br from-orange-50 to-amber-50 p-5 rounded-2xl border border-orange-100">
                                <div>
                                    <p class="text-sm text-gray-600 font-medium">Jumlah Tagihan</p>
                                    <p class="text-3xl font-extrabold bg-gradient-to-r from-brown-custom-600 to-brown-custom-900 bg-clip-text text-transparent tracking-tight">
                                        Rp {{ number_format($user->va_amount, 0, ',', '.') }}
                                    </p>
                                    @if($user->original_amount && $user->original_amount != $user->va_amount)
                                        <p class="text-xs text-gray-500 line-through mt-1">Rp {{ number_format($user->original_amount, 0, ',', '.') }}</p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-600 font-medium">Batas Waktu</p>
                                    <p class="text-sm font-semibold {{ $user->va_expired_at && now()->gt($user->va_expired_at) ? 'text-red-600' : 'text-gray-800' }}">
                                        @if($user->va_status === 'paid')
                                            Virtual Account sudah terbayarkan.
                                        @elseif($user->va_expired_at && now()->gt($user->va_expired_at))
                                            Expired
                                        @else
                                            {{ \Carbon\Carbon::parse($user->va_expired_at)->translatedFormat('d M Y, H:i') }} WIB
                                        @endif
                                    </p>
                                </div>
                            </div>

                            {{-- Penambahan ID btn-main-refresh pada tombol ini --}}
                            <div class="mt-5">
                                <button id="btn-main-refresh" onclick="checkVAStatus()"
                                        class="flex items-center justify-center w-full bg-gradient-to-r from-slate-100 to-slate-200 text-gray-700 py-3 px-4 rounded-xl font-semibold hover:from-slate-200 hover:to-slate-300 focus:outline-none focus:ring-4 focus:ring-slate-300 transition-all shadow-sm">
                                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    Refresh Status Pembayaran
                                </button>
                            </div>
                        </div>

                        <div class="gradient-card p-4 rounded-2xl text-center border border-sky-100">
                            <p class="font-bold text-gray-800">Jika Masih Pending, Silahkan Kirim Bukti Pembayaran. Proses membutuhkan 1x24 jam.</p>
                        </div>

                        @if($user->va_status !== 'paid')
                        <div class="mt-6 p-4 border-2 border-dashed border-gray-200 rounded-2xl">
                            <h4 class="font-bold text-gray-800 mb-2 text-sm">Upload Bukti Pembayaran</h4>
                            <form action="{{ route('mahasiswa.va.uploadProof') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="file" name="payment_proof" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 mb-3" required>
                                <button type="submit" class="w-full bg-sky-600 text-white py-2 rounded-xl text-sm font-bold shadow-sm hover:bg-sky-700 transition">
                                    Kirim Bukti
                                </button>
                            </form>
                        </div>
                        @endif

                        @if($master->payment_proof)
                        <div class="mt-4">
                            <p class="text-xs text-gray-500 mb-1">Bukti Terkirim:</p>
                            <a href="{{ asset('storage/' . $master->payment_proof) }}" target="_blank" class="text-sky-600 text-sm font-medium underline">
                                Lihat Bukti Pembayaran
                            </a>
                        </div>
                        @endif

                        {{-- Petunjuk Pembayaran --}}
                        <div class="bg-white rounded-3xl card-shadow p-6 hover-lift">
                            <h4 class="font-bold text-gray-800 mb-5 text-lg flex items-center">
                                <span class="bg-gradient-to-r from-brown-custom-600 to-brown-custom-700 text-white p-2 rounded-xl mr-3">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                </span>
                                Petunjuk Pembayaran
                            </h4>
                            <div x-data="{ activeTab: 'mobile' }">
                                <div class="mb-5">
                                    <nav class="-mb-px flex space-x-3 overflow-x-auto pb-3
                                               [&::-webkit-scrollbar]:h-2
                                               [&::-webkit-scrollbar-track]:bg-sky-50 [&::-webkit-scrollbar-track]:rounded-full
                                               [&::-webkit-scrollbar-thumb]:bg-sky-400 [&::-webkit-scrollbar-thumb]:rounded-full">
                                        <button @click="activeTab = 'mobile'" :class="{ 'bg-gradient-to-r from-sky-500 to-sky-600 text-white shadow-lg shadow-sky-200': activeTab === 'mobile', 'bg-slate-100 text-gray-600 hover:bg-slate-200': activeTab !== 'mobile' }" class="whitespace-nowrap py-3 px-5 rounded-xl font-semibold text-sm focus:outline-none transition-all">Mobile Banking</button>
                                        <button @click="activeTab = 'atm'" :class="{ 'bg-gradient-to-r from-sky-500 to-sky-600 text-white shadow-lg shadow-sky-200': activeTab === 'atm', 'bg-slate-100 text-gray-600 hover:bg-slate-200': activeTab !== 'atm' }" class="whitespace-nowrap py-3 px-5 rounded-xl font-semibold text-sm focus:outline-none transition-all">ATM MANDIRI</button>
                                        <button @click="activeTab = 'ibanking'" :class="{ 'bg-gradient-to-r from-sky-500 to-sky-600 text-white shadow-lg shadow-sky-200': activeTab === 'ibanking', 'bg-slate-100 text-gray-600 hover:bg-slate-200': activeTab !== 'ibanking' }" class="whitespace-nowrap py-3 px-5 rounded-xl font-semibold text-sm focus:outline-none transition-all">Internet Banking</button>
                                        <button @click="activeTab = 'other'" :class="{ 'bg-gradient-to-r from-sky-500 to-sky-600 text-white shadow-lg shadow-sky-200': activeTab === 'other', 'bg-slate-100 text-gray-600 hover:bg-slate-200': activeTab !== 'other' }" class="whitespace-nowrap py-3 px-5 rounded-xl font-semibold text-sm focus:outline-none transition-all">Bank Lain</button>
                                    </nav>
                                </div>
                                <div class="text-sm text-gray-700 space-y-2 leading-relaxed">
                                    <div x-show="activeTab === 'mobile'" x-transition>
                                        <ol class="list-decimal pl-5 space-y-3">
                                            <li>Buka aplikasi <strong>MANDIRI Mobile Banking</strong> dan login.</li>
                                            <li>Pilih menu <strong>"Transfer"</strong> → <strong>"Virtual Account Billing"</strong>.</li>
                                            <li>Masukkan nomor VA: <strong>{{ $user->va_number }}</strong>.</li>
                                            <li>Tagihan muncul otomatis. Pastikan data sesuai.</li>
                                            <li>Konfirmasi dan masukkan <strong>Password Transaksi</strong>.</li>
                                        </ol>
                                    </div>
                                    <div x-show="activeTab === 'atm'" x-transition style="display:none;">
                                        <ol class="list-decimal pl-5 space-y-3">
                                            <li>Masukkan Kartu ATM dan PIN.</li>
                                            <li>Pilih <strong>"Menu Lain"</strong> → <strong>"Transfer"</strong> → <strong>"Virtual Account Billing"</strong>.</li>
                                            <li>Masukkan nomor VA: <strong>{{ $user->va_number }}</strong>.</li>
                                            <li>Konfirmasi dan selesaikan transaksi.</li>
                                        </ol>
                                    </div>
                                    <div x-show="activeTab === 'ibanking'" x-transition style="display:none;">
                                        <ol class="list-decimal pl-5 space-y-3">
                                            <li>Buka <strong>ibank.mandiri.co.id</strong> dan login.</li>
                                            <li>Pilih <strong>"Transfer"</strong> → <strong>"Virtual Account Billing"</strong>.</li>
                                            <li>Masukkan nomor VA: <strong>{{ $user->va_number }}</strong>.</li>
                                            <li>Masukkan kode token <strong>MANDIRI e-Secure</strong>.</li>
                                        </ol>
                                    </div>
                                    <div x-show="activeTab === 'other'" x-transition style="display:none;">
                                        <ol class="list-decimal pl-5 space-y-3">
                                            <li>Pilih <strong>"Transfer Antar Bank"</strong>, kode bank MANDIRI: <strong>89</strong>.</li>
                                            <li>Masukkan nomor VA sebagai rekening tujuan.</li>
                                            <li>Masukkan jumlah <strong>Rp {{ number_format($user->va_amount, 0, ',', '.') }}</strong> (harus sama persis).</li>
                                        </ol>
                                        <p class="mt-4 text-xs text-amber-800 bg-amber-50 p-3 rounded-xl border border-amber-200">
                                            <strong>Catatan:</strong> Mungkin dikenakan biaya administrasi dari bank pengirim.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="bg-white rounded-3xl card-shadow p-6 hover-lift">
                            <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                                <span class="bg-gradient-to-r from-brown-custom-600 to-brown-custom-700 text-white p-2 rounded-xl mr-3">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                    </svg>
                                </span>
                                Status Pembayaran
                            </h2>

                            <div class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="gradient-card p-4 rounded-2xl border border-sky-100">
                                        <p class="text-sm text-gray-600 font-medium mb-1">Nama</p>
                                        <p class="font-semibold text-gray-800">{{ $user->name ?? '-' }}</p>
                                    </div>
                                    <div class="gradient-card p-4 rounded-2xl border border-sky-100">
                                        <p class="text-sm text-gray-600 font-medium mb-1">Email</p>
                                        <p class="font-semibold text-gray-800">{{ $user->email ?? '-' }}</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="gradient-card p-4 rounded-2xl border border-sky-100">
                                        <p class="text-sm text-gray-600 font-medium mb-1">VA Number</p>
                                        <p class="font-semibold text-gray-800">{{ $user->va_number ?? '-' }}</p>
                                    </div>
                                    <div class="gradient-card p-4 rounded-2xl border border-sky-100">
                                        <p class="text-sm text-gray-600 font-medium mb-1">Gelombang</p>
                                        <p class="font-semibold text-gray-800">{{ $user->gelombang ? 'Gelombang ' . $user->gelombang : '-' }}</p>
                                    </div>
                                </div>

                                @if($user->promo_code_used)
                                <div class="relative overflow-hidden bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-200 rounded-2xl p-5 shadow-sm">
                                    <div class="relative flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                                        <div class="flex items-start">
                                            <div class="bg-white p-2.5 rounded-xl shadow-sm mr-3 text-emerald-600 border border-emerald-100 flex-shrink-0">
                                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-emerald-900 text-base">Promo Digunakan!</h4>
                                                <div class="flex items-center mt-1.5">
                                                    <span class="text-xs text-emerald-600 mr-2">Kode:</span>
                                                    <span class="bg-emerald-200/50 text-emerald-800 text-xs font-mono font-bold px-2 py-1 rounded-md border border-emerald-200 tracking-wide">{{ $user->promo_code_used }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-left sm:text-right mt-1 sm:mt-0 w-full sm:w-auto bg-white/40 sm:bg-transparent p-3 sm:p-0 rounded-xl">
                                            <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider mb-0.5">Anda Hemat</p>
                                            <p class="text-2xl font-extrabold text-emerald-700 leading-none">Rp {{ number_format($user->discount_amount, 0, ',', '.') }}</p>
                                            @if($user->discount_percentage > 0)
                                                <p class="text-xs text-emerald-600 font-medium mt-1"><span class="bg-emerald-100 px-1.5 py-0.5 rounded text-[10px]">Diskon {{ $user->discount_percentage }}%</span></p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endif

                                @if($user->original_amount && $user->original_amount != $user->va_amount)
                                <div class="bg-gradient-to-br from-slate-50 to-gray-50 border border-gray-200 rounded-2xl p-5 shadow-sm">
                                    <p class="text-sm text-gray-700 font-bold mb-4">Rincian Pembayaran</p>
                                    <div class="space-y-3">
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-500">Harga Normal</span>
                                            <span class="text-sm text-gray-400 line-through decoration-red-400">Rp {{ number_format($user->original_amount, 0, ',', '.') }}</span>
                                        </div>
                                        @if($user->discount_amount > 0)
                                        <div class="flex justify-between items-center bg-emerald-50/50 p-2 rounded-lg border border-emerald-100/50 -mx-2">
                                            <span class="text-sm font-medium text-emerald-700">Potongan Promo</span>
                                            <span class="text-sm text-emerald-700 font-bold">- Rp {{ number_format($user->discount_amount, 0, ',', '.') }}</span>
                                        </div>
                                        @endif
                                        <div class="flex justify-between items-center pt-3 border-t border-dashed border-gray-300">
                                            <span class="font-bold text-gray-800 text-base">Total Bayar</span>
                                            <span class="font-extrabold text-xl bg-gradient-to-r from-sky-600 to-blue-800 bg-clip-text text-transparent">Rp {{ number_format($user->va_amount, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <div class="gradient-card p-4 rounded-2xl text-center border border-sky-100">
                                    <p class="font-bold text-gray-800">Username dan Password Untuk Login Penmabar</p>
                                </div>

                                <div class="space-y-4">
                                    <div class="bg-gradient-to-br from-sky-50 to-blue-50 border-2 border-sky-300 rounded-2xl p-8 shadow-md hover-lift">
                                        <p class="text-sm text-sky-600 font-bold uppercase tracking-wider mb-3">Username</p>
                                        <div class="bg-white rounded-xl p-6 border border-sky-200 shadow-sm">
                                            <div class="flex items-center gap-4">
                                                <p class="font-mono text-3xl font-extrabold text-sky-700 break-all flex-1">{{ $user->trx_id ?? '-' }}</p>
                                                <button onclick="copyToClipboard('{{ $user->trx_id ?? '' }}', 'username')" class="flex-shrink-0 bg-sky-100 hover:bg-sky-200 text-sky-700 p-2.5 rounded-lg transition-colors" title="Salin username">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="bg-gradient-to-br from-amber-50 to-yellow-50 border-2 border-amber-300 rounded-2xl p-8 shadow-md hover-lift">
                                        <p class="text-sm text-amber-600 font-bold uppercase tracking-wider mb-3">Password</p>
                                        
                                        <div class="bg-white rounded-xl p-6 border border-amber-200 shadow-sm mb-5">
                                            <p class="text-lg text-amber-800 font-bold mb-4">Password Anda:</p>
                                            
                                            <div class="flex items-center gap-3 mb-6 p-4 bg-amber-50 rounded-lg border border-amber-200">
                                                <div class="flex-1">
                                                    <input 
                                                        type="password" 
                                                        id="password-input" 
                                                        value="{{ $user->plain_password ?? '-' }}" 
                                                        class="w-full font-mono text-2xl font-extrabold text-amber-700 bg-transparent border-none outline-none" 
                                                        readonly 
                                                    />
                                                </div>
                                                <button 
                                                    type="button"
                                                    onclick="togglePasswordVisibility()" 
                                                    class="flex-shrink-0 bg-amber-100 hover:bg-amber-200 text-amber-700 p-2.5 rounded-lg transition-colors" 
                                                    title="Tampilkan/Sembunyikan password"
                                                >
                                                    <svg id="eye-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                </button>
                                                <button 
                                                    type="button"
                                                    onclick="copyToClipboard('{{ $user->plain_password ?? '' }}', 'password')" 
                                                    class="flex-shrink-0 bg-amber-100 hover:bg-amber-200 text-amber-700 p-2.5 rounded-lg transition-colors" 
                                                    title="Salin password"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                            
                                            <div class="bg-amber-50 p-4 rounded-lg border border-amber-200 mb-4">
                                                <p class="text-sm text-amber-800 font-semibold mb-3">Format Password:</p>
                                                <p class="text-amber-900 font-semibold text-sm mb-2">Rumus: <span class="font-mono text-amber-700">PMB + 8 digit terakhir nomor HP</span></p>
                                                <p class="text-amber-900 font-semibold text-sm">Contoh:</p>
                                                <div class="ml-3 mt-2 space-y-1">
                                                    <p class="text-amber-900 text-sm">HP: <span class="font-mono text-amber-700">081234567890</span></p>
                                                    <p class="text-amber-600 text-sm text-center">↓</p>
                                                    <p class="text-amber-900 text-sm">Password: <span class="font-mono text-lg font-bold text-amber-700">PMB34567890</span></p>
                                                </div>
                                            </div>
                                
                                            <div class="bg-red-50 p-4 rounded-lg border border-red-200">
                                                <p class="text-sm text-red-700 font-semibold flex items-start">
                                                    <svg class="w-5 h-5 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    <span>Jangan bagikan password Anda kepada siapapun!</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <script>
                                function togglePasswordVisibility() {
                                    const passwordInput = document.getElementById('password-input');
                                    const eyeIcon = document.getElementById('eye-icon');
                                    
                                    if (passwordInput.type === 'password') {
                                        passwordInput.type = 'text';
                                        eyeIcon.classList.add('text-amber-600');
                                    } else {
                                        passwordInput.type = 'password';
                                        eyeIcon.classList.remove('text-amber-600');
                                    }
                                }
                                
                                function copyToClipboard(text, type) {
                                    if (!text || text === '-') {
                                        showNotification('Tidak ada ' + (type === 'username' ? 'username' : 'password') + ' untuk disalin', 'error');
                                        return;
                                    }
                                
                                    if (navigator.clipboard) {
                                        navigator.clipboard.writeText(text).then(() => {
                                            showNotification((type === 'username' ? 'Username' : 'Password') + ' berhasil disalin ke clipboard', 'success');
                                        }).catch(() => {
                                            fallbackCopy(text, type);
                                        });
                                    } else {
                                        fallbackCopy(text, type);
                                    }
                                }
                                
                                function fallbackCopy(text, type) {
                                    const textarea = document.createElement('textarea');
                                    textarea.value = text;
                                    textarea.style.position = 'fixed';
                                    textarea.style.opacity = '0';
                                    document.body.appendChild(textarea);
                                    textarea.select();
                                    try {
                                        document.execCommand('copy');
                                        showNotification((type === 'username' ? 'Username' : 'Password') + ' berhasil disalin', 'success');
                                    } catch (err) {
                                        showNotification('Gagal menyalin, silakan salin manual', 'error');
                                    }
                                    document.body.removeChild(textarea);
                                }
                                
                                function showNotification(message, type = 'info') {
                                    const n = document.createElement('div');
                                    n.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-2xl shadow-2xl text-white transition-all ${
                                        type === 'success' ? 'bg-gradient-to-r from-emerald-500 to-emerald-600' :
                                        type === 'error'   ? 'bg-gradient-to-r from-rose-500 to-rose-600' :
                                                             'bg-gradient-to-r from-sky-500 to-sky-600'
                                    }`;
                                    n.innerHTML = `<div class="flex items-center"><div class="bg-white/20 rounded-full p-2 mr-3"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div><span class="font-semibold">${message}</span></div>`;
                                    document.body.appendChild(n);
                                    setTimeout(() => { 
                                        n.style.opacity = '0'; 
                                        n.style.transform = 'translateX(100%)'; 
                                        setTimeout(() => n.remove(), 500); 
                                    }, 3000);
                                }
                                </script>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="gradient-card p-4 rounded-2xl border border-sky-100">
                                        <p class="text-sm text-gray-600 font-medium mb-2">Status Pembayaran</p>
                                        <span class="px-4 py-2 rounded-full text-white font-semibold inline-block shadow-lg {{ $user->va_status == 'paid' ? 'bg-gradient-to-r from-emerald-500 to-emerald-600' : 'bg-gradient-to-r from-amber-500 to-amber-600' }}">
                                            {{ ucfirst($user->va_status ?? 'Pending') }}
                                        </span>
                                    </div>
                                    <div class="gradient-card p-4 rounded-2xl border border-sky-100">
                                        <p class="text-sm text-gray-600 font-medium mb-1">Tanggal Pembayaran</p>
                                        <p class="font-semibold text-gray-800">{{ $user->payment_date ?? '-' }}</p>
                                    </div>
                                </div>

                                <div class="bg-gradient-to-br from-orange-50 to-amber-50 p-5 rounded-2xl border border-orange-200">
                                    <p class="text-sm text-gray-600 font-medium mb-2">Nominal Bayar</p>
                                    <p class="font-extrabold text-3xl text-orange-800 tracking-tight">
                                        Rp {{ number_format($user->payment_amount ?? $user->va_amount, 0, ',', '.') }}
                                    </p>
                                </div>

                                {{-- TOMBOL AKSI UTAMA --}}
                                <div class="gradient-card p-5 rounded-2xl text-center border border-sky-100">
                                    @if ($user->va_status === 'paid')
                                        {{-- Paid: tombol aktif, link dinamis sesuai profesi --}}
                                        <a href="{{ $targetRoute }}"
                                           class="w-full block bg-gradient-to-r from-emerald-500 to-emerald-600 text-white py-4 px-6 rounded-2xl font-bold
                                                  hover:from-emerald-600 hover:to-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-300
                                                  transition-all duration-300 ease-in-out text-center text-lg shadow-lg shadow-emerald-200">
                                            🎉 Masuk ke {{ $dashboardName }}
                                        </a>
                                        <p class="text-sm text-gray-600 mt-3">Anda akan diarahkan otomatis dalam beberapa detik...</p>
                                    @else
                                        <button disabled
                                                class="w-full bg-gradient-to-r from-slate-300 to-slate-400 text-white py-4 px-6 rounded-2xl font-bold
                                                       cursor-not-allowed opacity-70 transition-all duration-300 ease-in-out text-lg">
                                            ⏳ Menunggu Pembayaran...
                                        </button>
                                        <p class="text-sm text-gray-600 mt-3">Silakan selesaikan pembayaran untuk mengakses dashboard.</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="gradient-sky-brown text-white rounded-3xl p-6 shadow-xl">
                            <h3 class="font-bold text-lg mb-4 flex items-center">
                                <svg class="h-6 w-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                                Informasi Penting
                            </h3>
                            <ul class="space-y-3 text-sm">
                                <li class="flex items-start"><div class="bg-white/20 rounded-full p-1 mr-3 mt-0.5 flex-shrink-0"><svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg></div><span>User tidak dapat login apabila VA masih dalam status PENDING.</span></li>
                                <li class="flex items-start"><div class="bg-white/20 rounded-full p-1 mr-3 mt-0.5 flex-shrink-0"><svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg></div><span>Pastikan nomor Virtual Account yang dimasukkan sudah benar.</span></li>
                                <li class="flex items-start"><div class="bg-white/20 rounded-full p-1 mr-3 mt-0.5 flex-shrink-0"><svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg></div><span>Transfer sesuai dengan jumlah tagihan yang tertera.</span></li>
                                <li class="flex items-start"><div class="bg-white/20 rounded-full p-1 mr-3 mt-0.5 flex-shrink-0"><svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg></div><span>Simpan bukti pembayaran sebagai arsip pribadi.</span></li>
                            </ul>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </main>

        <footer class="bg-white/80 backdrop-blur-lg mt-auto border-t border-sky-100">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 text-center text-sm text-gray-600">
                <p class="font-medium">© {{ date('Y') }} PENMABAR. Semua hak cipta dilindungi.</p>
            </div>
        </footer>
    </div>

    {{-- ============================================================ --}}
    {{-- POPUP PENGINGAT REFRESH PEMBAYARAN (ALPINE JS)               --}}
    {{-- ============================================================ --}}
    @if(isset($user) && $user && ($user->va_status ?? '') !== 'paid')
    <div x-data="{ showRefreshPrompt: false }" 
         x-init="setTimeout(() => showRefreshPrompt = true, 30000)" 
         x-show="showRefreshPrompt" 
         style="display: none;"
         class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
         
        <div class="bg-white rounded-3xl p-6 md:p-8 shadow-2xl max-w-sm w-full text-center relative"
             x-show="showRefreshPrompt"
             x-transition:enter="transition ease-out duration-300 delay-100"
             x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             @click.away="showRefreshPrompt = false">
            
            <div class="w-20 h-20 bg-sky-50 text-sky-500 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner border border-sky-100">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            </div>
            <h3 class="text-xl font-extrabold text-gray-800 mb-2">Sudah Transfer?</h3>
            <p class="text-gray-600 text-sm mb-6 leading-relaxed">Jika Anda sudah melakukan pembayaran melalui bank, silakan klik tombol di bawah ini agar sistem dapat memperbarui status Anda.</p>
            
            <div class="flex flex-col gap-3">
                <button @click="showRefreshPrompt = false; checkVAStatus();" class="w-full bg-gradient-to-r from-sky-500 to-sky-600 text-white font-bold py-3.5 rounded-xl hover:from-sky-600 hover:to-sky-700 transition-all shadow-lg shadow-sky-200 flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Refresh Status Sekarang
                </button>
                <button @click="showRefreshPrompt = false" class="w-full bg-slate-100 text-gray-600 font-bold py-3 rounded-xl hover:bg-slate-200 transition-all">
                    Nanti Saja
                </button>
            </div>
        </div>
    </div>
    @endif

    <script>
        function copyVirtualAccount() {
            const vaNumber = document.getElementById('va-number').innerText;
            const copyText = document.getElementById('copy-text');
            const copiedText = document.getElementById('copied-text');
            const copyButton = document.getElementById('copy-button');

            const doFallback = (text) => {
                const ta = document.createElement('textarea');
                ta.value = text;
                ta.style.cssText = 'position:fixed;top:0;left:0;opacity:0';
                document.body.appendChild(ta);
                ta.focus(); ta.select();
                try { document.execCommand('copy') ? showCopied() : alert('Salin manual'); }
                catch(e) { alert('Salin manual'); }
                document.body.removeChild(ta);
            };

            if (navigator.clipboard) {
                navigator.clipboard.writeText(vaNumber).then(showCopied).catch(() => doFallback(vaNumber));
            } else {
                doFallback(vaNumber);
            }

            function showCopied() {
                copyText.style.display = 'none';
                copiedText.style.display = 'inline';
                copyButton.classList.add('bg-emerald-50','text-emerald-600','border-emerald-300');
                copyButton.classList.remove('text-sky-600','hover:text-sky-700','border-sky-200');
                setTimeout(() => {
                    copyText.style.display = 'inline';
                    copiedText.style.display = 'none';
                    copyButton.classList.remove('bg-emerald-50','text-emerald-600','border-emerald-300');
                    copyButton.classList.add('text-sky-600','hover:text-sky-700','border-sky-200');
                }, 2000);
            }
        }

        function checkVAStatus() {
            // Kita mengambil target tombol refresh utama (meskipun fungsi ini dipanggil dari Modal AlpineJS)
            const btn = document.getElementById('btn-main-refresh');
            if(!btn) return;
            
            const orig = btn.innerHTML;
            btn.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Memuat...';
            btn.disabled = true;

            fetch('{{ route("mahasiswa.va.check.status") }}')
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'success') {
                        if (data.data.va_status === 'paid') {
                            // =====================================================
                            // AUTO-LOGIN via checkStatus: tampilkan overlay & redirect dinamis
                            // =====================================================
                            const overlay = document.getElementById('paid-redirect-overlay');
                            if (overlay) {
                                overlay.style.display = 'flex';
                            }
                            setTimeout(() => {
                                window.location.href = '{{ $targetRoute }}';
                            }, 1500);
                        } else {
                            showNotification('Status masih pending. Silakan selesaikan pembayaran.', 'info');
                        }
                    }
                })
                .catch(() => showNotification('Gagal memperbarui status.', 'error'))
                .finally(() => {
                    setTimeout(() => {
                        btn.innerHTML = orig;
                        btn.disabled = false;
                    }, 1000);
                });
        }

        // Polling otomatis setiap 15 detik jika status masih pending
        @if(isset($user) && $user && ($user->va_status ?? '') !== 'paid')
        (function startPolling() {
            setInterval(function () {
                fetch('{{ route("mahasiswa.va.check.status") }}')
                    .then(r => r.json())
                    .then(data => {
                        if (data.status === 'success' && data.data.va_status === 'paid') {
                            const overlay = document.getElementById('paid-redirect-overlay');
                            if (overlay) overlay.style.display = 'flex';
                            setTimeout(() => {
                                window.location.href = '{{ $targetRoute }}';
                            }, 1500);
                        }
                    })
                    .catch(() => {}); // silent fail
            }, 15000); // setiap 15 detik
        })();
        @endif
    </script>
</body>
</html>