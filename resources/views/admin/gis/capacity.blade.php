<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Kapasitas & Kuota - GIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: { dark: { 900: '#0f172a', 800: '#1e293b' } }
                }
            }
        }
    </script>
</head>
<body class="bg-dark-900 text-slate-200 font-sans min-h-screen p-8">

    {{-- HEADER & BACK BUTTON --}}
    <div class="max-w-7xl mx-auto mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-white tracking-wide">MONITORING KAPASITAS</h1>
            <p class="text-slate-400 text-sm mt-1">Data Real-time Kuota vs Pendaftar Seluruh Prodi</p>
        </div>
        <a href="{{ route('admin.gis.index') }}" class="px-6 py-2 rounded-full border border-white/10 hover:bg-white/5 text-slate-300 transition flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Kembali ke Peta
        </a>
    </div>

    <div class="max-w-7xl mx-auto space-y-8">
        
        {{-- 1. BIG CARDS SUMMARY --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Card Total Pendaftar --}}
            <div class="bg-slate-800/50 border border-blue-500/30 p-6 rounded-2xl relative overflow-hidden">
                <div class="absolute -right-4 -top-4 text-blue-500/10 text-9xl"><i class="fas fa-users"></i></div>
                <h3 class="text-blue-400 font-bold uppercase tracking-widest text-sm mb-2">Total Pendaftar Masuk</h3>
                <div class="text-5xl font-black text-white">{{ number_format($totalPendaftar) }}</div>
                <div class="mt-4 text-xs text-slate-400">Mahasiswa terdaftar di sistem</div>
            </div>

            {{-- Card Total Kuota --}}
            <div class="bg-slate-800/50 border border-purple-500/30 p-6 rounded-2xl relative overflow-hidden">
                <div class="absolute -right-4 -top-4 text-purple-500/10 text-9xl"><i class="fas fa-database"></i></div>
                <h3 class="text-purple-400 font-bold uppercase tracking-widest text-sm mb-2">Total Kuota Tersedia</h3>
                <div class="text-5xl font-black text-white">{{ number_format($totalKuota) }}</div>
                <div class="mt-4 text-xs text-slate-400">Akumulasi kuota seluruh prodi</div>
            </div>

            {{-- Card Sisa Kuota --}}
            <div class="bg-slate-800/50 border border-emerald-500/30 p-6 rounded-2xl relative overflow-hidden">
                <div class="absolute -right-4 -top-4 text-emerald-500/10 text-9xl"><i class="fas fa-chart-pie"></i></div>
                <h3 class="text-emerald-400 font-bold uppercase tracking-widest text-sm mb-2">Sisa Slot Kursi</h3>
                <div class="text-5xl font-black text-white">{{ number_format($sisaKuota) }}</div>
                
                {{-- Progress Bar --}}
                <div class="w-full bg-slate-700 h-2 rounded-full mt-4 overflow-hidden">
                    @php 
                        $persenTerisi = $totalKuota > 0 ? ($totalPendaftar / $totalKuota) * 100 : 0;
                        $color = $persenTerisi > 90 ? 'bg-red-500' : ($persenTerisi > 70 ? 'bg-yellow-500' : 'bg-emerald-500');
                    @endphp
                    <div class="h-full {{ $color }}" style="width: {{ $persenTerisi }}%"></div>
                </div>
                <div class="text-right text-xs mt-1 text-slate-300">{{ number_format($persenTerisi, 1) }}% Terisi</div>
            </div>
        </div>

        {{-- 2. TABEL RINCIAN PER PRODI --}}
        <div class="bg-slate-800/80 border border-white/10 rounded-3xl overflow-hidden shadow-2xl">
            <div class="p-6 border-b border-white/5 flex justify-between items-center">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <i class="fas fa-list text-cyan-400"></i> Rincian Program Studi
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-900/50 text-xs uppercase text-slate-400 tracking-wider">
                            <th class="p-4 border-b border-white/10">No</th>
                            <th class="p-4 border-b border-white/10">Nama Program Studi</th>
                            <th class="p-4 border-b border-white/10 text-center">Kuota</th>
                            <th class="p-4 border-b border-white/10 text-center">Pendaftar</th>
                            <th class="p-4 border-b border-white/10 text-center">Sisa</th>
                            <th class="p-4 border-b border-white/10 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-sm">
                        @foreach($dataProdi as $index => $prodi)
                            @php
                                $jumlahPendaftar = $prodi->pendaftar_count; 
                                
                                // UBAH: Pakai $prodi->daya_tampung
                                $kapasitas = $prodi->daya_tampung; 
                    
                                $sisa = $kapasitas - $jumlahPendaftar;
                                $persen = $kapasitas > 0 ? ($jumlahPendaftar / $kapasitas) * 100 : 0;
                            @endphp
                            <tr class="hover:bg-white/5 transition">
                                <td class="p-4 text-slate-500">{{ $index + 1 }}</td>
                                <td class="p-4 font-bold text-white">
                                    {{ $prodi->nama_prodi }}
                                    <div class="text-[10px] text-slate-500 font-normal">{{ $prodi->kode_prodi }}</div>
                                </td>
                                
                                {{-- UBAH: Tampilkan Daya Tampung --}}
                                <td class="p-4 text-center text-purple-300">{{ $kapasitas }}</td>
                                
                                <td class="p-4 text-center text-blue-300 font-bold">{{ $jumlahPendaftar }}</td>
                                
                                <td class="p-4 text-center">
                                    <span class="px-3 py-1 rounded-lg text-xs font-bold {{ $sisa <= 0 ? 'bg-red-500/20 text-red-400' : 'bg-emerald-500/20 text-emerald-400' }}">
                                        {{ $sisa }}
                                    </span>
                                </td>
                                
                                <td class="p-4 text-center">
                                    @if($persen >= 100)
                                        <span class="text-red-500 text-xs font-bold uppercase">Penuh</span>
                                    @elseif($persen >= 80)
                                        <span class="text-yellow-500 text-xs font-bold uppercase">Hampir Penuh</span>
                                    @else
                                        <span class="text-emerald-500 text-xs font-bold uppercase">Tersedia</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</body>
</html>