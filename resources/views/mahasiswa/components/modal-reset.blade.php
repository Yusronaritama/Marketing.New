@if(isset($showResetPopup) && $showResetPopup)
    <div class="fixed inset-0 z-[9999] flex items-center justify-center bg-indigo-950/95 backdrop-blur-md p-4">
        <div class="bg-white rounded-[2.5rem] shadow-2xl max-w-lg w-full overflow-hidden border border-white/20 animate-in fade-in zoom-in duration-300">
            
            <div class="bg-gradient-to-br from-amber-400 to-orange-600 p-8 text-center relative overflow-hidden">
                <div class="absolute -top-10 -right-10 w-32 h-32 bg-white/10 rounded-full"></div>
                <div class="relative z-10">
                    <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-2xl bg-white shadow-xl mb-4 rotate-3">
                        <i class="fas fa-user-check text-4xl text-orange-600"></i>
                    </div>
                    <h3 class="text-2xl font-black text-white uppercase tracking-tight leading-none">Validasi Data Final</h3>
                    <p class="text-orange-100 text-sm font-medium mt-2">Pembaruan Parameter PDDIKTI Nasional</p>
                </div>
            </div>

            <div class="p-10 text-center">
                <div class="text-gray-700 leading-relaxed mb-8">
                    <p class="mb-4 text-lg">Halo, <strong>{{ Auth::user()->name }}</strong>.</p>
                    <p class="mb-4">
                        Sehubungan dengan adanya pembaruan standar data pada sistem <strong>PDDIKTI</strong>, Anda wajib melakukan <strong>validasi ulang secara menyeluruh</strong>.
                    </p>
                    
                    <div class="bg-amber-50 p-4 rounded-2xl border border-amber-100 text-left">
                        <h4 class="text-xs font-bold text-amber-800 uppercase mb-2 tracking-wider">Poin Penting Pengecekan:</h4>
                        
                        {{-- [UPDATE DISINI] Menambahkan poin untuk cek NISN --}}
                        <ul class="text-xs text-amber-700 space-y-2">
                            <li class="flex gap-2 items-start"><i class="fas fa-check-circle mt-0.5 text-amber-500"></i> <span>Pastikan pengisian <strong>10 digit NISN</strong> (Wajib).</span></li>
                            <li class="flex gap-2 items-start"><i class="fas fa-check-circle mt-0.5 text-amber-500"></i> <span>Pilih ulang <strong>Provinsi, Kota, dan Kecamatan</strong>.</span></li>
                            <li class="flex gap-2 items-start"><i class="fas fa-check-circle mt-0.5 text-amber-500"></i> <span>Isi data <strong>Kebutuhan Khusus</strong> (Mahasiswa & Orang Tua).</span></li>
                            <li class="flex gap-2 items-start"><i class="fas fa-check-circle mt-0.5 text-amber-500"></i> <span>Cek kembali <strong>Biodata Orang Tua/Wali</strong> (Slide 1 - 4).</span></li>
                        </ul>

                    </div>
                </div>
                
                <div class="flex flex-col gap-4">
                    <a href="{{ route('mahasiswa.data-final.create') }}" 
                       class="flex items-center justify-center gap-3 w-full py-5 text-lg font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-2xl transition-all shadow-xl shadow-indigo-200 active:scale-95">
                        <i class="fas fa-sync-alt"></i>
                        Mulai Validasi Menyeluruh
                    </a>
                    
                    <p class="text-[11px] text-gray-400 italic">
                        *Tombol "Simpan Permanen" akan membuka kembali akses Dashboard Anda.
                    </p>
                </div>
            </div>

            <div class="bg-gray-50 py-4 border-t border-gray-100 text-center">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">PMB Universitas Buana Perjuangan Karawang</span>
            </div>
        </div>
    </div>

    <style>
        body { 
            overflow: hidden !important; 
            height: 100vh !important;
            padding-right: 0 !important;
        }
    </style>
@endif