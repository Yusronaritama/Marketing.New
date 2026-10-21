@extends('layouts.admin.app')

@section('title', 'Detail Data S2 - ' . $dataS2->nama_mahasiswa)

@section('content')
<div class="space-y-6">
    {{-- Header Section --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.data-s2.index') }}"
               class="rounded-lg border border-gray-300 bg-white p-2 text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $dataS2->nama_mahasiswa }}</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">ID: {{ $dataS2->id }}</p>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.data-s2.edit', $dataS2->id) }}"
               class="inline-flex items-center gap-2 rounded-lg bg-amber-500 px-4 py-2 font-medium text-white transition hover:bg-amber-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit Data
            </a>
            <button onclick="deleteConfirm({{ $dataS2->id }})"
                    class="inline-flex items-center gap-2 rounded-lg bg-red-500 px-4 py-2 font-medium text-white transition hover:bg-red-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Hapus Data
            </button>
        </div>
    </div>

    {{-- Status Cards --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        {{-- Data Status --}}
        <div class="rounded-lg border-l-4 border-blue-500 bg-blue-50 p-4 dark:bg-blue-900/20">
            <p class="text-sm font-medium text-blue-600 dark:text-blue-400">Status Data</p>
            <p class="mt-2 text-2xl font-bold text-blue-900 dark:text-blue-100">
                {{ match($dataS2->status_data) { 'completed' => 'Selesai', 'pending' => 'Pending', 'rejected' => 'Ditolak', default => ucfirst($dataS2->status_data) } }}
            </p>
        </div>

        {{-- Payment Status --}}
        <div class="rounded-lg border-l-4 border-green-500 bg-green-50 p-4 dark:bg-green-900/20">
            <p class="text-sm font-medium text-green-600 dark:text-green-400">Status Pembayaran</p>
            <p class="mt-2 text-2xl font-bold text-green-900 dark:text-green-100">
                {{ $masterUser->va_status ?? 'Belum Ada' }}
            </p>
            @if ($masterUser)
                <p class="mt-1 text-sm text-green-700 dark:text-green-300">
                    Rp {{ number_format($masterUser->payment_amount ?? 0, 0, ',', '.') }}
                </p>
            @endif
        </div>

        {{-- E-Registration Status --}}
        <div class="rounded-lg border-l-4 border-purple-500 bg-purple-50 p-4 dark:bg-purple-900/20">
            <p class="text-sm font-medium text-purple-600 dark:text-purple-400">Status E-Registrasi</p>
            <p class="mt-2 text-2xl font-bold text-purple-900 dark:text-purple-100">
                {{ match($masterEreg->status ?? '') { 'completed' => 'Selesai', 'in_progress' => 'Proses', 'rejected' => 'Ditolak', default => 'Belum Ada' } }}
            </p>
        </div>
    </div>

    {{-- Tabs Navigation --}}
    <div x-data="{ activeTab: 'biodata' }" class="space-y-4">
        <div class="border-b border-gray-200 dark:border-gray-700">
            <div class="flex gap-4 overflow-x-auto">
                <button @click="activeTab = 'biodata'"
                        :class="activeTab === 'biodata' ? 'border-b-2 border-brand-500 text-brand-600 dark:text-brand-400' : 'text-gray-600 dark:text-gray-400'"
                        class="px-4 py-3 font-medium transition hover:text-brand-600 dark:hover:text-brand-400">
                    Biodata Lengkap
                </button>
                <button @click="activeTab = 'orangtua'"
                        :class="activeTab === 'orangtua' ? 'border-b-2 border-brand-500 text-brand-600 dark:text-brand-400' : 'text-gray-600 dark:text-gray-400'"
                        class="px-4 py-3 font-medium transition hover:text-brand-600 dark:hover:text-brand-400">
                    Data Orang Tua
                </button>
                <button @click="activeTab = 'wali'"
                        :class="activeTab === 'wali' ? 'border-b-2 border-brand-500 text-brand-600 dark:text-brand-400' : 'text-gray-600 dark:text-gray-400'"
                        class="px-4 py-3 font-medium transition hover:text-brand-600 dark:hover:text-brand-400">
                    Data Wali
                </button>
                <button @click="activeTab = 'pembayaran'"
                        :class="activeTab === 'pembayaran' ? 'border-b-2 border-brand-500 text-brand-600 dark:text-brand-400' : 'text-gray-600 dark:text-gray-400'"
                        class="px-4 py-3 font-medium transition hover:text-brand-600 dark:hover:text-brand-400">
                    Pembayaran
                </button>
                <button @click="activeTab === 'ereg'"
                        :class="activeTab === 'ereg' ? 'border-b-2 border-brand-500 text-brand-600 dark:text-brand-400' : 'text-gray-600 dark:text-gray-400'"
                        class="px-4 py-3 font-medium transition hover:text-brand-600 dark:hover:text-brand-400">
                    E-Registrasi
                </button>
            </div>
        </div>

        {{-- Tab: Biodata Lengkap --}}
        <div x-show="activeTab === 'biodata'" x-transition class="space-y-6">
            {{-- Section: Identitas Diri --}}
            <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-bold text-gray-900 dark:text-white">Identitas Diri</h2>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Lengkap</label>
                        <p class="mt-2 text-gray-900 dark:text-white">{{ $dataS2->nama_mahasiswa }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jenis Kelamin</label>
                        <p class="mt-2 text-gray-900 dark:text-white">{{ $dataS2->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tempat Lahir</label>
                        <p class="mt-2 text-gray-900 dark:text-white">{{ $dataS2->tempat_lahir }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Lahir</label>
                        <p class="mt-2 text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($dataS2->tanggal_lahir)->format('d M Y') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">NIK (Nomor Identitas)</label>
                        <p class="mt-2 font-mono text-gray-900 dark:text-white">{{ $dataS2->nik ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">NISN</label>
                        <p class="mt-2 font-mono text-gray-900 dark:text-white">{{ $dataS2->nisn ?? '-' }}</p>
                    </div>
                </div>
            </div>

            {{-- Section: Kontak & Komunikasi --}}
            <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-bold text-gray-900 dark:text-white">Kontak & Komunikasi</h2>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                        <a href="mailto:{{ $user->email }}" class="mt-2 text-brand-600 hover:underline dark:text-brand-400">
                            {{ $user->email }}
                        </a>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nomor HP</label>
                        <a href="tel:{{ $dataS2->handphone }}" class="mt-2 text-brand-600 hover:underline dark:text-brand-400">
                            {{ $dataS2->handphone ?? '-' }}
                        </a>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Telepon Rumah</label>
                        <p class="mt-2 text-gray-900 dark:text-white">{{ $dataS2->telepon ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kewarganegaraan</label>
                        <p class="mt-2 text-gray-900 dark:text-white">{{ $dataS2->kewarganegaraan ?? '-' }}</p>
                    </div>
                </div>
            </div>

            {{-- Section: Alamat Lengkap --}}
            <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-bold text-gray-900 dark:text-white">Alamat Lengkap</h2>
                <div class="space-y-4">
                    <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700/50">
                        <p class="whitespace-pre-wrap text-gray-900 dark:text-white">
                            {{ $dataS2->jalan ?? '-' }} No. {{ $dataS2->nomor_rumah ?? '-' }}<br>
                            RT/RW: {{ $dataS2->rt ?? '-' }}/{{ $dataS2->rw ?? '-' }}<br>
                            Dusun: {{ $dataS2->dusun ?? '-' }}<br>
                            Kelurahan: {{ $dataS2->kelurahkan ?? '-' }}<br>
                            Kode Pos: {{ $dataS2->kode_pos ?? '-' }}
                        </p>
                    </div>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Provinsi</label>
                            <p class="mt-2 text-gray-900 dark:text-white">ID: {{ $dataS2->id_provinsi ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kabupaten</label>
                            <p class="mt-2 text-gray-900 dark:text-white">ID: {{ $dataS2->id_kabupaten ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kecamatan</label>
                            <p class="mt-2 text-gray-900 dark:text-white">ID: {{ $dataS2->id_kecamatan ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section: Informasi Tambahan --}}
            <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-bold text-gray-900 dark:text-white">Informasi Tambahan</h2>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Agama</label>
                        <p class="mt-2 text-gray-900 dark:text-white">ID: {{ $dataS2->id_agama ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jenis Tinggal</label>
                        <p class="mt-2 text-gray-900 dark:text-white">ID: {{ $dataS2->id_jenis_tinggal ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Alat Transportasi</label>
                        <p class="mt-2 text-gray-900 dark:text-white">ID: {{ $dataS2->id_alat_transportasi ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">NPWP</label>
                        <p class="mt-2 font-mono text-gray-900 dark:text-white">{{ $dataS2->npwp ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Penerima KPS</label>
                        <p class="mt-2 text-gray-900 dark:text-white">
                            @if ($dataS2->penerima_kps)
                                <span class="inline-block rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-800 dark:bg-green-900/30 dark:text-green-400">Ya</span>
                            @else
                                <span class="inline-block rounded-full bg-red-100 px-3 py-1 text-sm font-semibold text-red-800 dark:bg-red-900/30 dark:text-red-400">Tidak</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">No. KPS</label>
                        <p class="mt-2 font-mono text-gray-900 dark:text-white">{{ $dataS2->no_kps ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab: Data Orang Tua --}}
        <div x-show="activeTab === 'orangtua'" x-transition class="space-y-6">
            {{-- Ayah --}}
            <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-bold text-gray-900 dark:text-white">Data Ayah</h2>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Ayah</label>
                        <p class="mt-2 text-gray-900 dark:text-white">{{ $dataS2->nama_ayah ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Lahir</label>
                        <p class="mt-2 text-gray-900 dark:text-white">
                            {{ $dataS2->tanggal_lahir_ayah ? \Carbon\Carbon::parse($dataS2->tanggal_lahir_ayah)->format('d M Y') : '-' }}
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">NIK Ayah</label>
                        <p class="mt-2 font-mono text-gray-900 dark:text-white">{{ $dataS2->nik_ayah ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jenjang Pendidikan</label>
                        <p class="mt-2 text-gray-900 dark:text-white">ID: {{ $dataS2->id_jenjang_pendidikan_ayah ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pekerjaan</label>
                        <p class="mt-2 text-gray-900 dark:text-white">ID: {{ $dataS2->id_pekerjaan_ayah ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Penghasilan</label>
                        <p class="mt-2 text-gray-900 dark:text-white">ID: {{ $dataS2->id_penghasilan_ayah ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kebutuhan Khusus</label>
                        <p class="mt-2 text-gray-900 dark:text-white">ID: {{ $dataS2->id_kebutuhan_khusus_ayah ?? '-' }}</p>
                    </div>
                </div>
            </div>

            {{-- Ibu Kandung --}}
            <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-bold text-gray-900 dark:text-white">Data Ibu Kandung</h2>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Ibu</label>
                        <p class="mt-2 text-gray-900 dark:text-white">{{ $dataS2->nama_ibu_kandung ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Lahir</label>
                        <p class="mt-2 text-gray-900 dark:text-white">
                            {{ $dataS2->tanggal_lahir_ibu ? \Carbon\Carbon::parse($dataS2->tanggal_lahir_ibu)->format('d M Y') : '-' }}
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">NIK Ibu</label>
                        <p class="mt-2 font-mono text-gray-900 dark:text-white">{{ $dataS2->nik_ibu ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jenjang Pendidikan</label>
                        <p class="mt-2 text-gray-900 dark:text-white">ID: {{ $dataS2->id_jenjang_pendidikan_ibu ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pekerjaan</label>
                        <p class="mt-2 text-gray-900 dark:text-white">ID: {{ $dataS2->id_pekerjaan_ibu ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Penghasilan</label>
                        <p class="mt-2 text-gray-900 dark:text-white">ID: {{ $dataS2->id_penghasilan_ibu ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kebutuhan Khusus</label>
                        <p class="mt-2 text-gray-900 dark:text-white">ID: {{ $dataS2->id_kebutuhan_khusus_ibu ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab: Data Wali --}}
        <div x-show="activeTab === 'wali'" x-transition class="space-y-6">
            @if ($dataS2->nama_wali)
                <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                    <h2 class="mb-4 text-lg font-bold text-gray-900 dark:text-white">Data Wali</h2>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Wali</label>
                            <p class="mt-2 text-gray-900 dark:text-white">{{ $dataS2->nama_wali }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Lahir</label>
                            <p class="mt-2 text-gray-900 dark:text-white">
                                {{ $dataS2->tanggal_lahir_wali ? \Carbon\Carbon::parse($dataS2->tanggal_lahir_wali)->format('d M Y') : '-' }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jenjang Pendidikan</label>
                            <p class="mt-2 text-gray-900 dark:text-white">ID: {{ $dataS2->id_jenjang_pendidikan_wali ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pekerjaan</label>
                            <p class="mt-2 text-gray-900 dark:text-white">ID: {{ $dataS2->id_pekerjaan_wali ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Penghasilan</label>
                            <p class="mt-2 text-gray-900 dark:text-white">ID: {{ $dataS2->id_penghasilan_wali ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kebutuhan Khusus</label>
                            <p class="mt-2 text-gray-900 dark:text-white">ID: {{ $dataS2->id_kebutuhan_khusus_ayah ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            @else
                <div class="rounded-lg border-l-4 border-yellow-400 bg-yellow-50 p-4 dark:bg-yellow-900/20">
                    <p class="text-yellow-800 dark:text-yellow-300">Tidak ada data wali untuk calon mahasiswa ini</p>
                </div>
            @endif
        </div>

        {{-- Tab: Pembayaran --}}
        <div x-show="activeTab === 'pembayaran'" x-transition class="space-y-6">
            @if ($masterUser)
                <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                    <h2 class="mb-4 text-lg font-bold text-gray-900 dark:text-white">Informasi Pembayaran</h2>
                    <div class="space-y-6">
                        {{-- Status & Virtual Account --}}
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700/50">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status Pembayaran</label>
                                <div class="mt-2 flex items-center gap-2">
                                    @php
                                        $vaStatus = $masterUser->va_status;
                                        $statusColor = match($vaStatus) {
                                            'paid' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                                            'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                                            'expired' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                                            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
                                        };
                                    @endphp
                                    <span class="inline-block rounded-full px-3 py-1 text-sm font-bold {{ $statusColor }}">
                                        {{ ucfirst($vaStatus) }}
                                    </span>
                                </div>
                            </div>
                            <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700/50">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nomor Virtual Account</label>
                                <p class="mt-2 font-mono text-lg font-bold text-gray-900 dark:text-white">{{ $masterUser->va_number ?? '-' }}</p>
                            </div>
                        </div>

                        {{-- Nominal --}}
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                            <div class="rounded-lg border-l-4 border-blue-500 bg-blue-50 p-4 dark:bg-blue-900/20">
                                <label class="block text-sm font-medium text-blue-700 dark:text-blue-400">Nominal Awal</label>
                                <p class="mt-2 text-2xl font-bold text-blue-900 dark:text-blue-100">
                                    Rp {{ number_format($masterUser->original_amount ?? 0, 0, ',', '.') }}
                                </p>
                            </div>
                            <div class="rounded-lg border-l-4 border-amber-500 bg-amber-50 p-4 dark:bg-amber-900/20">
                                <label class="block text-sm font-medium text-amber-700 dark:text-amber-400">Diskon</label>
                                <p class="mt-2 text-2xl font-bold text-amber-900 dark:text-amber-100">
                                    Rp {{ number_format($masterUser->discount_amount ?? 0, 0, ',', '.') }}
                                </p>
                                @if ($masterUser->discount_percentage)
                                    <p class="text-sm text-amber-700 dark:text-amber-400">({{ $masterUser->discount_percentage }}%)</p>
                                @endif
                            </div>
                            <div class="rounded-lg border-l-4 border-green-500 bg-green-50 p-4 dark:bg-green-900/20">
                                <label class="block text-sm font-medium text-green-700 dark:text-green-400">Nominal VA</label>
                                <p class="mt-2 text-2xl font-bold text-green-900 dark:text-green-100">
                                    Rp {{ number_format($masterUser->va_amount ?? 0, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>

                        {{-- Tanggal Pembayaran --}}
                        @if ($masterUser->va_status === 'paid')
                            <div class="rounded-lg bg-green-50 p-4 dark:bg-green-900/20">
                                <label class="block text-sm font-medium text-green-700 dark:text-green-400">Tanggal Pembayaran</label>
                                <p class="mt-2 text-lg font-bold text-green-900 dark:text-green-100">
                                    {{ $masterUser->paid_at ? \Carbon\Carbon::parse($masterUser->paid_at)->format('d M Y H:i') : '-' }}
                                </p>
                                <p class="text-sm text-green-700 dark:text-green-400">
                                    Metode: {{ $masterUser->payment_method ?? '-' }}
                                </p>
                            </div>
                        @else
                            <div class="rounded-lg bg-yellow-50 p-4 dark:bg-yellow-900/20">
                                <label class="block text-sm font-medium text-yellow-700 dark:text-yellow-400">VA Expired</label>
                                <p class="mt-2 text-lg font-bold text-yellow-900 dark:text-yellow-100">
                                    {{ $masterUser->va_expired_at ? \Carbon\Carbon::parse($masterUser->va_expired_at)->format('d M Y H:i') : '-' }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="rounded-lg border-l-4 border-gray-400 bg-gray-50 p-4 dark:bg-gray-900/20">
                    <p class="text-gray-800 dark:text-gray-300">Belum ada data pembayaran untuk calon mahasiswa ini</p>
                </div>
            @endif
        </div>

        {{-- Tab: E-Registrasi --}}
        <div x-show="activeTab === 'ereg'" x-transition class="space-y-6">
            @if ($masterEreg)
                <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                    <h2 class="mb-4 text-lg font-bold text-gray-900 dark:text-white">Data E-Registrasi</h2>
                    <div class="space-y-6">
                        {{-- Informasi Dasar --}}
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama</label>
                                <p class="mt-2 text-gray-900 dark:text-white">{{ $masterEreg->nama }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                                <p class="mt-2 text-gray-900 dark:text-white">{{ $masterEreg->gmail }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nomor HP</label>
                                <p class="mt-2 text-gray-900 dark:text-white">{{ $masterEreg->phone }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jalur Pendaftaran</label>
                                <p class="mt-2 text-gray-900 dark:text-white">{{ $masterEreg->jalur_pendaftaran }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status Pekerjaan</label>
                                <p class="mt-2 text-gray-900 dark:text-white">{{ $masterEreg->status_pekerjaan ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jurusan Lulusan</label>
                                <p class="mt-2 text-gray-900 dark:text-white">{{ $masterEreg->jurusan_lulus ?? '-' }}</p>
                            </div>
                        </div>

                        {{-- Pilihan Program Studi --}}
                        <div class="border-t border-gray-200 pt-6 dark:border-gray-700">
                            <h3 class="mb-4 font-bold text-gray-900 dark:text-white">Pilihan Program Studi</h3>
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div class="rounded-lg border-l-4 border-blue-500 bg-blue-50 p-4 dark:border-blue-500 dark:bg-blue-900/20">
                                    <label class="block text-sm font-bold text-blue-700 dark:text-blue-400">⭐ PILIHAN UTAMA (Jurusan 1)</label>
                                    <p class="mt-2 text-lg font-bold text-gray-900 dark:text-white">{{ $masterEreg->jurusan1 ?? '-' }}</p>
                                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ $masterEreg->alasan_jurusan_1 ?? '-' }}</p>
                                </div>
                                <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700/50">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pilihan Alternatif (Jurusan 2)</label>
                                    <p class="mt-2 font-semibold text-gray-900 dark:text-white">{{ $masterEreg->jurusan2 ?? '-' }}</p>
                                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ $masterEreg->alasan_jurusan_2 ?? '-' }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Dokumen & Status --}}
                        <div class="border-t border-gray-200 pt-6 dark:border-gray-700">
                            <h3 class="mb-4 font-bold text-gray-900 dark:text-white">Status E-Registrasi</h3>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700/50">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Step Terakhir</label>
                                    <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $masterEreg->step_completed ?? 0 }}/5</p>
                                </div>
                                <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700/50">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                                    <p class="mt-2 font-semibold text-gray-900 dark:text-white">
                                        @php
                                            $status = match($masterEreg->status) {
                                                'completed' => 'Selesai',
                                                'in_progress' => 'Sedang Diproses',
                                                'rejected' => 'Ditolak',
                                                default => ucfirst($masterEreg->status)
                                            };
                                        @endphp
                                        {{ $status }}
                                    </p>
                                </div>
                                <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700/50">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Scan Status</label>
                                    <p class="mt-2 font-semibold text-gray-900 dark:text-white">
                                        {{ match($masterEreg->scan_status) { 'validated' => 'Validasi', 'need_reupload' => 'Perlu Upload Ulang', default => ucfirst($masterEreg->scan_status ?? '') } }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Dokumen Diupload --}}
                        <div class="border-t border-gray-200 pt-6 dark:border-gray-700">
                            <h3 class="mb-4 font-bold text-gray-900 dark:text-white">Dokumen yang Diupload</h3>
                            <div class="space-y-3">
                                @php
                                    $documents = [
                                        ['name' => 'Kartu Keluarga', 'field' => 'file_kk'],
                                        ['name' => 'Ijazah SMA', 'field' => 'file_ijazah'],
                                        ['name' => 'SKL', 'field' => 'file_skl'],
                                        ['name' => 'Rapor', 'field' => 'file_rapor'],
                                        ['name' => 'KTP', 'field' => 'file_ktp'],
                                        ['name' => 'Surat Keterangan Sehat', 'field' => 'file_surat_kesehatan'],
                                        ['name' => 'Ijazah S1', 'field' => 'file_ijazah_s1'],
                                        ['name' => 'Transkrip Nilai', 'field' => 'file_transkrip'],
                                        ['name' => 'KTM', 'field' => 'file_ktm'],
                                    ];
                                @endphp
                                @foreach ($documents as $doc)
                                    @php
                                        $hasDoc = !empty($masterEreg->{$doc['field']});
                                    @endphp
                                    <div class="flex items-center justify-between rounded-lg border px-4 py-3 {{ $hasDoc ? 'border-green-200 bg-green-50 dark:border-green-900/30 dark:bg-green-900/10' : 'border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-700/50' }}">
                                        <div class="flex items-center gap-3">
                                            @if ($hasDoc)
                                                <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                <span class="font-medium text-green-900 dark:text-green-100">{{ $doc['name'] }}</span>
                                            @else
                                                <svg class="h-5 w-5 text-gray-400 dark:text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                                </svg>
                                                <span class="text-gray-600 dark:text-gray-400">{{ $doc['name'] }}</span>
                                            @endif
                                        </div>
                                        @if ($hasDoc)
                                            <span class="text-xs font-semibold text-green-700 dark:text-green-400">TERUPLOAD</span>
                                        @else
                                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">BELUM</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="rounded-lg border-l-4 border-gray-400 bg-gray-50 p-4 dark:bg-gray-900/20">
                    <p class="text-gray-800 dark:text-gray-300">Belum ada data e-registrasi untuk calon mahasiswa ini</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Timestamps --}}
    <div class="mt-8 rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
        <p>Dibuat: {{ $dataS2->created_at->format('d M Y H:i') }}</p>
        <p>Diperbarui: {{ $dataS2->updated_at->format('d M Y H:i') }}</p>
    </div>
</div>

@push('scripts')
<script>
function deleteConfirm(id) {
    Swal.fire({
        title: 'Hapus Data?',
        text: 'Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/data-s2/${id}`;
            form.innerHTML = `
                @csrf
                @method('DELETE')
            `;
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endpush
@endsection