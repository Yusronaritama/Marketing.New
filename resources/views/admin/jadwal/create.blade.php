<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setting Jadwal Ujian Farmasi - Admin Panel</title>
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
                    <h1 class="text-2xl font-bold text-slate-800">Atur Jadwal Ujian</h1>
                    <p class="text-sm text-slate-500">Khusus Program Studi Farmasi</p>
                </div>
                <div class="h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600">
                    <i class="fas fa-calendar-alt"></i>
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

            <form action="{{ route('admin.jadwal.store') }}" method="POST">
                @csrf
                
                {{-- Input Tanggal --}}
                <div class="mb-5">
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Tanggal Pelaksanaan <span class="text-xs font-normal text-gray-400 italic ml-1">(Opsional)</span>
                    </label>
                    {{-- HAPUS REQUIRED DISINI --}}
                    <input type="date" name="tanggal_ujian" 
                           class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition outline-none">
                </div>

                {{-- Input Waktu --}}
                <div class="mb-5">
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Waktu Mulai <span class="text-xs font-normal text-gray-400 italic ml-1">(Opsional)</span>
                    </label>
                    {{-- HAPUS REQUIRED DISINI --}}
                    <input type="time" name="waktu_ujian" 
                           class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition outline-none">
                </div>

                {{-- Input Deskripsi --}}
                <div class="mb-6">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Lokasi & Keterangan <span class="text-red-500">*</span></label>
                    <textarea name="deskripsi" rows="4" required placeholder="Contoh: Gedung Farmasi Lantai 2. Harap membawa alat tulis dan kartu ujian."
                              class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition outline-none resize-none"></textarea>
                </div>

                <div class="flex items-center gap-3">
                    {{-- Tombol Kembali ke Dashboard (Opsional) --}}
                    <a href="{{ url('/admin/dashboard') }}" class="px-6 py-3 rounded-xl bg-slate-200 text-slate-700 font-bold hover:bg-slate-300 transition">
                        Batal
                    </a>
                    
                    {{-- Tombol Simpan --}}
                    <button type="submit" class="flex-1 px-6 py-3 rounded-xl bg-blue-600 text-white font-bold hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-500/30 transition transform hover:-translate-y-1">
                        <i class="fas fa-save mr-2"></i> Simpan Jadwal
                    </button>
                </div>
            </form>
        </div>

        {{-- BAGIAN KANAN: Preview / Info --}}
        <div class="hidden lg:flex flex-col justify-center space-y-6">
            
            {{-- Kartu Status Aktif --}}
            <div class="bg-white rounded-2xl shadow-lg p-6 border border-slate-100 relative overflow-hidden">
                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-4">Jadwal Aktif Saat Ini</h3>
                
                @if(isset($jadwalAktif) && $jadwalAktif)
                    {{-- TOMBOL HAPUS --}}
                    <form action="{{ route('admin.jadwal.destroy') }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus jadwal ini? Dashboard mahasiswa akan kembali ke status Menunggu Jadwal.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="absolute top-0 right-0 m-4 text-red-400 hover:text-red-600 transition" title="Hapus Jadwal">
                            <i class="fas fa-trash-alt text-lg"></i>
                        </button>
                    </form>

                    <div class="text-center py-4">
                        
                        {{-- LOGIKA TANGGAL (OPSIONAL) --}}
                        <div class="text-4xl font-black text-slate-800 mb-1">
                            @if($jadwalAktif->tanggal_ujian)
                                {{ \Carbon\Carbon::parse($jadwalAktif->tanggal_ujian)->format('d') }}
                            @else
                                <i class="fas fa-calendar-day text-3xl text-slate-300"></i>
                            @endif
                        </div>
                        
                        <div class="text-xl font-medium text-slate-500 uppercase mb-4">
                            @if($jadwalAktif->tanggal_ujian)
                                {{ \Carbon\Carbon::parse($jadwalAktif->tanggal_ujian)->translatedFormat('F Y') }}
                            @else
                                <span class="text-sm text-slate-400">Tanggal Belum Ditentukan</span>
                            @endif
                        </div>
                        
                        {{-- LOGIKA JAM (OPSIONAL) --}}
                        <div class="inline-flex items-center px-4 py-2 bg-blue-50 text-blue-700 rounded-lg text-sm font-bold mb-4">
                            @if($jadwalAktif->waktu_ujian)
                                <i class="far fa-clock mr-2"></i> {{ \Carbon\Carbon::parse($jadwalAktif->waktu_ujian)->format('H:i') }} WIB
                            @else
                                <i class="fas fa-hourglass-half mr-2"></i> Waktu Menyusul
                            @endif
                        </div>

                        <p class="text-slate-600 text-sm px-4">
                            {{ $jadwalAktif->deskripsi }}
                        </p>
                    </div>
                @else
                    {{-- TAMPILAN JIKA KOSONG --}}
                    <div class="flex flex-col items-center justify-center py-10 text-slate-400">
                        <i class="fas fa-calendar-times text-4xl mb-3 opacity-50"></i>
                        <p class="text-sm font-medium">Belum ada jadwal aktif.</p>
                        <p class="text-[10px] mt-1">Silakan input jadwal baru di formulir sebelah kiri.</p>
                    </div>
                @endif
            </div>

            <div class="bg-blue-600 rounded-2xl shadow-lg p-6 text-white">
                <h4 class="font-bold text-lg mb-2"><i class="fas fa-info-circle mr-2"></i> Informasi Sistem</h4>
                <p class="text-blue-100 text-sm leading-relaxed">
                    Jadwal yang Anda input di sini akan <strong>otomatis tampil</strong> di dashboard mahasiswa yang:
                </p>
                <ul class="list-disc list-inside mt-3 text-sm text-blue-50 space-y-1">
                    <li>Memilih Program Studi <strong>Farmasi</strong>.</li>
                    <li>Status kelulusannya sudah <strong>Lulus USM</strong>.</li>
                </ul>
            </div>

        </div>
    </div>

</body>
</html>