<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Tidak Ditemukan (404) - PMB</title>
    
    {{-- Kita panggil aset manual agar tampilan independen dari layout utama --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
        .text-gradient {
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-image: linear-gradient(to right, #2563eb, #06b6d4);
        }
        /* Animasi melayang yang halus */
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
    </style>
</head>
<body class="bg-slate-50 relative overflow-hidden h-screen flex items-center justify-center">

    {{-- Dekorasi Background Blob --}}
    <div class="absolute top-0 left-0 w-96 h-96 bg-blue-200/30 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2 pointer-events-none"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-indigo-200/30 rounded-full blur-3xl translate-x-1/2 translate-y-1/2 pointer-events-none"></div>

    {{-- Kontainer Utama --}}
    <div class="relative z-10 max-w-lg w-full px-6 text-center">
        
        {{-- Ilustrasi / Icon --}}
        <div class="mb-8 relative inline-block">
            {{-- Lingkaran Glow --}}
            <div class="absolute inset-0 bg-blue-100 rounded-full blur-xl opacity-50"></div>
            
            {{-- Icon Utama --}}
            <div class="relative bg-white h-32 w-32 mx-auto rounded-3xl shadow-xl shadow-blue-500/10 flex items-center justify-center border border-white/50 animate-float">
                <i class="fas fa-compass text-6xl text-gradient"></i>
                
                {{-- Badge Tanda Tanya --}}
                <div class="absolute -top-2 -right-2 h-10 w-10 bg-rose-500 rounded-xl flex items-center justify-center text-white shadow-lg rotate-12">
                    <i class="fas fa-question text-xl"></i>
                </div>
            </div>
        </div>

        {{-- Teks 404 Besar --}}
        <h1 class="text-8xl font-black text-slate-200 leading-none select-none tracking-tighter mb-4">
            4<span class="text-blue-500">0</span>4
        </h1>

        {{-- Pesan Error --}}
        <h2 class="text-2xl font-bold text-slate-800 mb-2">Oops! Kamu Tersesat?</h2>
        <p class="text-slate-500 mb-8 leading-relaxed">
            Halaman yang kamu cari sepertinya sudah dipindahkan, dihapus, atau memang tidak pernah ada.
        </p>

        {{-- Tombol Aksi --}}
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            
            {{-- Logic Tombol Utama --}}
            @auth
                {{-- Jika Login: Ke Dashboard --}}
                <a href="{{ route('mahasiswa.dashboard') }}" 
                   class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 hover:-translate-y-1 transition-all duration-300 flex items-center justify-center gap-2 group">
                    <i class="fas fa-home group-hover:scale-110 transition-transform"></i>
                    Kembali ke Dashboard
                </a>
            @else
                {{-- Jika Belum Login: Ke Halaman Depan --}}
                <a href="{{ url('/') }}" 
                   class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 hover:-translate-y-1 transition-all duration-300 flex items-center justify-center gap-2 group">
                    <i class="fas fa-home group-hover:scale-110 transition-transform"></i>
                    Ke Halaman Utama
                </a>
            @endauth

            {{-- Tombol Sekunder --}}
            <button onclick="history.back()" 
                    class="w-full sm:w-auto px-8 py-3.5 rounded-xl border border-slate-200 bg-white text-slate-600 font-bold hover:bg-slate-50 hover:text-blue-600 transition-all duration-300 flex items-center justify-center gap-2">
                <i class="fas fa-arrow-left"></i>
                Halaman Sebelumnya
            </button>
        </div>

        {{-- Footer Kecil --}}
        <div class="mt-12 pt-6 border-t border-slate-200/60">
            <p class="text-xs text-slate-400 font-medium">
                Sistem Penerimaan Mahasiswa Baru &copy; {{ date('Y') }}
            </p>
        </div>

    </div>

</body>
</html>