{{-- Menggunakan layout utama admin --}}
@extends('layouts.admin.app')

{{-- Mengatur judul spesifik untuk halaman ini --}}
@section('title', 'Dashboard Utama')

{{-- Ini adalah konten utama yang akan dimasukkan ke dalam @yield('content') --}}
@section('content')

    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
        Selamat Datang di Dashboard!
    </h1>

    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-6 xl:grid-cols-4 2xl:gap-7.5">
        <div class="rounded-sm border border-stroke bg-white py-6 px-7.5 shadow-default dark:border-strokedark dark:bg-boxdark">
            <div class="flex h-11.5 w-11.5 items-center justify-center rounded-full bg-meta-2 dark:bg-meta-4">
                {{-- Icon SVG --}}
            </div>
            <div class="mt-4 flex items-end justify-between">
                <div>
                    <h4 class="text-title-md font-bold text-black dark:text-white">$3.456K</h4>
                    <span class="text-sm font-medium">Total Views</span>
                </div>
            </div>
        </div>
        {{-- Tambahkan card atau komponen lainnya di sini --}}

    </div>

@endsection

{{-- Jika perlu menambahkan script atau style khusus untuk halaman ini --}}
@push('scripts')
    <script>
        // Contoh script khusus untuk halaman dashboard
        console.log('Halaman dashboard berhasil dimuat!');
    </script>
@endpush