<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setting Info Konversi SKS - Admin Panel</title>
    {{-- Menggunakan CDN Tailwind agar langsung rapi --}}
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- Font Awesome untuk Ikon --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background-color: #f3f4f6; }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-6">

    <div class="w-full max-w-4xl grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        {{-- BAGIAN KIRI: Form Input --}}
        <div class="glass-card rounded-2xl shadow-xl p-8 animate-fade-in-up">
            
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Atur Info Konversi</h1>
                    <p class="text-sm text-slate-500">Jalur Pindahan, Ekstensi, & RPL</p>
                </div>
                <div class="h-10 w-10 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600">
                    <i class="fas fa-file-signature"></i>
                </div>
            </div>

            {{-- Alert Sukses --}}
            @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-r">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-500"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
            @endif

            {{-- Sesuaikan action dengan route konversi kamu --}}
            <form action="{{ route('admin.konversi.store') }}" method="POST">
                @csrf
                
                {{-- Input Tanggal --}}
                <div class="mb-5">
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Batas Waktu Penyerahan <span class="text-xs font-normal text-gray-400 italic ml-1">(Opsional)</span>
                    </label>
                    <input type="date" name="tanggal_batas" 
                           class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition outline-none">
                </div>

                {{-- Input Waktu --}}
                <div class="mb-5">
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Waktu Pelayanan <span class="text-xs font-normal text-gray-400 italic ml-1">(Opsional)</span>
                    </label>
                    <input type="time" name="waktu_pelayanan" 
                           class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition outline-none">
                </div>

                {{-- Input Deskripsi --}}
                <div class="mb-6">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Lokasi & Instruksi <span class="text-red-500">*</span></label>
                    <textarea name="deskripsi" rows="4" required placeholder="Contoh: Serahkan fotokopi transkrip nilai legalisir ke BAAK Gedung Rektorat Lt. 2."
                              class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition outline-none resize-none"></textarea>
                </div>

                <div class="flex items-center gap-3">
                    {{-- Tombol Kembali ke Dashboard --}}
                    <a href="{{ url('/admin/dashboard') }}" class="px-6 py-3 rounded-xl bg-slate-200 text-slate-700 font-bold hover:bg-slate-300 transition">
                        Batal
                    </a>
                    
                    {{-- Tombol Simpan --}}
                    <button type="submit" class="flex-1 px-6 py-3 rounded-xl bg-indigo-600 text-white font-bold hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 transition transform hover:-translate-y-1">
                        <i class="fas fa-save mr-2"></i> Simpan Info
                    </button>
                </div>
            </form>
        </div>

        {{-- BAGIAN KANAN: Preview / Info --}}
        <div class="hidden lg:flex flex-col justify-center space-y-6">
            
            {{-- Kartu Status Aktif --}}
            <div class="bg-white rounded-2xl shadow-lg p-6 border border-slate-100 relative overflow-hidden">
                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-4">Informasi Aktif Saat Ini</h3>
                
                {{-- Gunakan variabel $infoAktif (bisa dikirim dari controller) --}}
                @if(isset($infoAktif) && $infoAktif)
                    {{-- TOMBOL HAPUS --}}
                    <form action="{{ route('admin.konversi.destroy') }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus informasi ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="absolute top-0 right-0 m-4 text-red-400 hover:text-red-600 transition" title="Hapus Informasi">
                            <i class="fas fa-trash-alt text-lg"></i>
                        </button>
                    </form>

                    <div class="text-center py-4">
                        
                        <div class="text-4xl font-black text-slate-800 mb-1">
                            @if($infoAktif->tanggal_batas)
                                {{ \Carbon\Carbon::parse($infoAktif->tanggal_batas)->format('d') }}
                            @else
                                <i class="fas fa-file-invoice text-3xl text-slate-300"></i>
                            @endif
                        </div>
                        
                        <div class="text-xl font-medium text-slate-500 uppercase mb-4">
                            @if($infoAktif->tanggal_batas)
                                {{ \Carbon\Carbon::parse($infoAktif->tanggal_batas)->translatedFormat('F Y') }}
                            @else
                                <span class="text-sm text-slate-400">Batas Waktu Fleksibel</span>
                            @endif
                        </div>
                        
                        <div class="inline-flex items-center px-4 py-2 bg-indigo-50 text-indigo-700 rounded-lg text-sm font-bold mb-4">
                            @if($infoAktif->waktu_pelayanan)
                                <i class="far fa-clock mr-2"></i> {{ \Carbon\Carbon::parse($infoAktif->waktu_pelayanan)->format('H:i') }} WIB
                            @else
                                <i class="fas fa-building mr-2"></i> Jam Kerja Operasional
                            @endif
                        </div>

                        <p class="text-slate-600 text-sm px-4">
                            {{ $infoAktif->deskripsi }}
                        </p>
                    </div>
                @else
                    {{-- TAMPILAN JIKA KOSONG --}}
                    <div class="flex flex-col items-center justify-center py-10 text-slate-400">
                        <i class="fas fa-clipboard-check text-4xl mb-3 opacity-50"></i>
                        <p class="text-sm font-medium">Belum ada informasi instruksi konversi.</p>
                        <p class="text-[10px] mt-1">Silakan input data baru di formulir sebelah kiri.</p>
                    </div>
                @endif
            </div>

            <div class="bg-indigo-600 rounded-2xl shadow-lg p-6 text-white">
                <h4 class="font-bold text-lg mb-2"><i class="fas fa-info-circle mr-2"></i> Informasi Sistem</h4>
                <p class="text-indigo-100 text-sm leading-relaxed">
                    Pengumuman yang Anda input di sini akan <strong>otomatis tampil</strong> di dashboard mahasiswa jika:
                </p>
                <ul class="list-disc list-inside mt-3 text-sm text-indigo-50 space-y-1">
                    <li>Jalur pendaftaran adalah <strong>Pindahan, Ekstensi, atau RPL</strong>.</li>
                    <li>Status verifikasi akademik mahasiswa tersebut masih <strong>Menunggu</strong>.</li>
                </ul>
            </div>

        </div>
    </div>

</body>
</html>