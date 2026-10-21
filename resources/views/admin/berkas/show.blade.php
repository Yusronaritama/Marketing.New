<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Berkas - Admin</title>
    @if (app()->environment('local', 'development'))
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <a href="{{ route('admin.berkas.index') }}" class="text-blue-600 hover:text-blue-800 mr-4">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <h1 class="text-2xl font-bold text-gray-900">Detail Berkas Pendaftaran</h1>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <!-- Header Info -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-xl font-bold text-white">{{ $pendaftaran->nama }}</h2>
                        <p class="text-blue-100">{{ $pendaftaran->gmail }}</p>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <!-- Informasi Pribadi -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-user-circle mr-2 text-blue-500"></i>
                        Informasi Pribadi
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Nama Lengkap</label>
                            <p class="text-gray-900">{{ $pendaftaran->nama }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">WhatsApp</label>
                            <p class="text-gray-900">{{ $pendaftaran->whatsapp }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Email</label>
                            <p class="text-gray-900">{{ $pendaftaran->gmail }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Kode Promo</label>
                            <p class="text-gray-900">{{ $pendaftaran->kode_promo ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Data Biodata -->
                @if($pendaftaran->biodata)
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-id-card mr-2 text-green-500"></i>
                        Data Biodata Lengkap
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Nama Lengkap (Biodata)</label>
                            <p class="text-gray-900">{{ $pendaftaran->biodata->nama_lengkap ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Alamat</label>
                            <p class="text-gray-900">{{ $pendaftaran->biodata->alamat ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Tanggal Lahir (Biodata)</label>
                            <p class="text-gray-900">{{ $pendaftaran->biodata->tanggal_lahir ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Nama Ibu</label>
                            <p class="text-gray-900">{{ $pendaftaran->biodata->nama_ibu ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Nama Ayah</label>
                            <p class="text-gray-900">{{ $pendaftaran->biodata->nama_ayah ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Asal Sekolah</label>
                            <p class="text-gray-900">{{ $pendaftaran->biodata->asal_sekolah ?? '-' }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-sm font-medium text-gray-500">Jurusan Sekolah</label>
                            <p class="text-gray-900">{{ $pendaftaran->biodata->jurusan_sekolah ?? '-' }}</p>
                        </div>
                    </div>
                </div>
                @else
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-id-card mr-2 text-gray-400"></i>
                        Data Biodata
                    </h3>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle text-yellow-500 mr-3"></i>
                            <div>
                                <p class="text-yellow-800 font-medium">Data biodata belum lengkap</p>
                                <p class="text-yellow-600 text-sm">Peserta belum melengkapi data biodata</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Program Studi -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-graduation-cap mr-2 text-green-500"></i>
                        Pilihan Program Studi
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <label class="text-sm font-medium text-gray-500">Pilihan Pertama</label>
                            <p class="text-lg font-semibold text-blue-700">{{ $pendaftaran->jurusan1 }}</p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <label class="text-sm font-medium text-gray-500">Pilihan Kedua</label>
                            <p class="text-lg font-semibold text-green-700">{{ $pendaftaran->jurusan2 ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Berkas -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-folder-open mr-2 text-orange-500"></i>
                        Berkas Pendaftaran
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Kartu Keluarga -->
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex justify-between items-center mb-3">
                                <h4 class="font-medium text-gray-900">Kartu Keluarga</h4>
                                @if($pendaftaran->file_kk)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check mr-1"></i> Terunggah
                                </span>
                                @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-times mr-1"></i> Belum Unggah
                                </span>
                                @endif
                            </div>
                            @if($pendaftaran->file_kk)
                            <a href="{{ route('admin.berkas.download', ['id' => $pendaftaran->id, 'type' => 'kk']) }}" 
                               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                                <i class="fas fa-download mr-2"></i> Download KK
                            </a>
                            @else
                            <button disabled class="inline-flex items-center px-4 py-2 bg-gray-400 text-white rounded-lg cursor-not-allowed">
                                <i class="fas fa-download mr-2"></i> File Tidak Tersedia
                            </button>
                            @endif
                        </div>

                        <!-- Ijazah -->
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex justify-between items-center mb-3">
                                <h4 class="font-medium text-gray-900">Ijazah</h4>
                                @if($pendaftaran->file_ijazah)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check mr-1"></i> Terunggah
                                </span>
                                @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-times mr-1"></i> Belum Unggah
                                </span>
                                @endif
                            </div>
                            @if($pendaftaran->file_ijazah)
                            <a href="{{ route('admin.berkas.download', ['id' => $pendaftaran->id, 'type' => 'ijazah']) }}" 
                               class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200">
                                <i class="fas fa-download mr-2"></i> Download Ijazah
                            </a>
                            @else
                            <button disabled class="inline-flex items-center px-4 py-2 bg-gray-400 text-white rounded-lg cursor-not-allowed">
                                <i class="fas fa-download mr-2"></i> File Tidak Tersedia
                            </button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Progress & Timeline -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Progress -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-tasks mr-2 text-purple-500"></i>
                            Progress Pendaftaran
                        </h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="mb-2 flex justify-between">
                                <span class="text-sm font-medium text-gray-700">Langkah Diselesaikan</span>
                                <span class="text-sm font-bold text-blue-600">{{ $pendaftaran->step_completed ?? '0' }}/5</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div class="bg-blue-600 h-2.5 rounded-full" 
                                     style="width: {{ (($pendaftaran->step_completed ?? 0) / 5) * 100 }}%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Timeline -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-clock mr-2 text-orange-500"></i>
                            Timeline
                        </h3>
                        <div class="space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Dibuat</span>
                                <span class="text-gray-900">{{ $pendaftaran->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Diperbarui</span>
                                <span class="text-gray-900">{{ $pendaftaran->updated_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Status Saat Ini</span>
                                <span class="font-medium 
                                    @if($pendaftaran->status == 'completed') text-green-600
                                    @elseif($pendaftaran->status == 'rejected') text-red-600
                                    @else text-yellow-600 @endif">
                                    @if($pendaftaran->status == 'completed') Selesai
                                    @elseif($pendaftaran->status == 'rejected') Ditolak
                                    @else Dalam Proses @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>