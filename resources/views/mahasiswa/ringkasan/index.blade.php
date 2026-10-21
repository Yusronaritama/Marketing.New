@extends('layouts.mahasiswa.app')

@section('title', 'Admission Manifest - PMB UBP Karawang')

@section('content')

@php
    // --- 1. SETUP VARIABEL DARI CONTROLLER ---
    $biodata   = $biodata ?? null;
    $billing   = $billing ?? null;
    $dataFinal = $dataFinal ?? null;
    $ereg      = $ereg ?? null;
    $user      = $user ?? Auth::user();
    
    // --- 2. LOGIKA STATUS KELULUSAN ---
    $statusText  = $statusUjian ?? 'Belum Ujian';
    $statusUpper = strtoupper($statusText);
    $isLulus     = $statusUpper === 'LULUS';
    $isGagal     = in_array($statusUpper, ['TIDAK LULUS', 'GAGAL', 'REJECTED']);
    
    // --- 3. STATUS KEUANGAN ---
    $lunas = (isset($slikBillingStatus) && $slikBillingStatus == 'paid');

    // --- 4. FORMAT FOTO ---
    $fotoUrl = null;
    if ($user && $user->profile_photo_path) {
        $fotoUrl = Str::startsWith($user->profile_photo_path, 'http') 
            ? $user->profile_photo_path 
            : asset('storage/' . $user->profile_photo_path);
    }

    // --- 5. FORMAT TANGGAL LAHIR ---
    $tanggalLahirFormatted = '-';
    if (isset($dataFinal->tanggal_lahir)) {
        try {
            $tanggalLahirFormatted = $dataFinal->tanggal_lahir instanceof \Carbon\Carbon 
                ? $dataFinal->tanggal_lahir->format('d F Y') 
                : \Carbon\Carbon::parse($dataFinal->tanggal_lahir)->format('d F Y');
        } catch (\Exception $e) {}
    } elseif (isset($biodata->tanggal_lahir)) {
        try {
            $tanggalLahirFormatted = \Carbon\Carbon::parse($biodata->tanggal_lahir)->format('d F Y');
        } catch (\Exception $e) {}
    }

    // --- 6. SERIALISASI DATA KE JSON UNTUK VUE MODULAR APP ---
    $manifestPayload = [
        'user' => [
            'id'    => $user->id ?? null,
            'name'  => $user->name ?? '-',
            'email' => $user->email ?? '-',
            'photo' => $fotoUrl,
        ],
        'ereg' => [
            'trx_id'            => $ereg->trx_id ?? 'PENDING',
            'phone'             => $ereg->phone ?? '-',
            'ukuran_almamater'  => $ereg->ukuran_almamater ?? '',
            'jurusan1_nama'     => $ereg->jurusan1_nama ?? '-',
            'jurusan2_nama'     => $ereg->jurusan2_nama ?? '-',
            'jalur_pendaftaran' => $ereg->jalur_pendaftaran ?? 'Reguler',
            'gelombang'         => $ereg->masterUser->gelombang ?? ($ereg->gelombang ?? 'Gelombang 1'),
        ],
        'biodata' => [
            'isCompleted'   => ($biodata && $biodata->status_bio == 'completed'),
            'nama_lengkap'  => $biodata->nama_lengkap ?? '',
            'nik'           => $biodata->nik ?? '-',
            'tempat_lahir'  => $biodata->tempat_lahir ?? '-',
            'tanggal_lahir' => $tanggalLahirFormatted,
            'alamat'        => $biodata->alamat ?? '-',
            'nama_ayah'     => $biodata->nama_ayah ?? '-',
            'nama_ibu'      => $biodata->nama_ibu ?? '-',
            'asal_sekolah'  => $biodata->asal_sekolah ?? '-',
            'tahun_lulus'   => $biodata->tahun_lulus ?? '-',
            'hp_ortu'       => $biodata->dataFinal->handphone ?? ($biodata->no_hp_ortu ?? '-'),
        ],
        'dataFinal' => [
            'isReady'        => !empty($dataFinal),
            'nama_mahasiswa' => $dataFinal->nama_mahasiswa ?? null,
            'nik'            => $dataFinal->nik ?? null,
            'tempat_lahir'   => $dataFinal->tempat_lahir ?? null,
            'tanggal_lahir'  => $tanggalLahirFormatted,
            'jalan'          => $dataFinal->jalan ?? '',
            'rt'             => $dataFinal->rt ?? '',
            'rw'             => $dataFinal->rw ?? '',
            'kelurahan'      => $dataFinal->kelurahan ?? '',
            'handphone'      => $dataFinal->handphone ?? null,
        ],
        'billing' => [
            'virtual_account' => $billing->virtual_account ?? null,
            'amount'          => $billing->amount ?? 0,
            'isLunas'         => $lunas,
        ],
        'status' => [
            'statusText'      => $statusText,
            'isLulus'         => $isLulus,
            'isGagal'         => $isGagal,
            'jurusanDiterima' => $jurusanDiterima ?? '-',
        ],
        'routes' => [
            'dashboard'     => route('mahasiswa.dashboard'),
            'suratDownload' => route('mahasiswa.surat.download'),
            'print'         => route('mahasiswa.print'),
            'simpanAlmet'   => route('mahasiswa.ringkasan.simpan_almet'),
            'csrfToken'     => csrf_token(),
        ],
        'assets' => [
            'logoUrl' => asset('images/logo_ubp.png'),
        ]
    ];
@endphp

{{-- PAYLOAD DATA UNTUK VUE 3 COMPONENT --}}
<script id="ringkasan-data" type="application/json">
    {!! json_encode($manifestPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
</script>

{{-- MOUNT POINT UNTUK VUE COMPONENT --}}
<div id="vue-ringkasan-app">
    {{-- Skeleton Loading Placeholder Sebelum Vue Ter-mount --}}
    <div class="max-w-6xl mx-auto py-12 px-4 animate-pulse">
        <div class="h-20 bg-slate-200/60 rounded-2xl mb-6"></div>
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="lg:col-span-4 h-96 bg-slate-200/60 rounded-2xl"></div>
            <div class="lg:col-span-8 space-y-6">
                <div class="h-28 bg-slate-200/60 rounded-2xl"></div>
                <div class="h-64 bg-slate-200/60 rounded-2xl"></div>
            </div>
        </div>
    </div>
</div>

{{-- NOTIFIKASI TOAST SWEETALERT SETELAH SIMPAN UKURAN --}}
@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (window.Swal) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                timer: 3500,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        }
    });
</script>
@endif

@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (window.Swal) {
            Swal.fire({
                icon: 'error',
                title: 'Peringatan!',
                text: "{{ $errors->first() }}",
                confirmButtonColor: '#4f46e5'
            });
        }
    });
</script>
@endif

@endsection