{{-- ==========================================
     OVERLAY PENGUMUMAN GRUP WA (Pop-up)
     ========================================== --}}
<div x-data="{
         showOverlay: false,
         userId: {{ auth()->id() ?? 0 }},
         
         init() { },
         
         cekDanTampilkan() {
             let isJoined = localStorage.getItem('hideGroupOverlay_' + this.userId);
             let snoozeData = localStorage.getItem('snoozeGroupOverlay_' + this.userId);
             let now = new Date().getTime();
             
             let isSnoozed = false;
             
             // Cek apakah ada data snooze dan apakah masih berlaku (contoh: di-snooze selama 1 jam)
             if (snoozeData) {
                 if (now < parseInt(snoozeData)) {
                     isSnoozed = true; // Masih dalam masa snooze
                 } else {
                     localStorage.removeItem('snoozeGroupOverlay_' + this.userId); // Snooze expired, hapus!
                 }
             }

             // Kalau belum join & tidak sedang di-snooze, tampilin pop-up
             if (!isJoined && !isSnoozed) {
                 setTimeout(() => { this.showOverlay = true; }, 600);
             }
         },
         
         joinGroup() {
             // Simpan ke localStorage biar mati selamanya
             localStorage.setItem('hideGroupOverlay_' + this.userId, 'true');
             this.showOverlay = false;
             
             // Buka link grup WA di tab baru
             window.open('https://chat.whatsapp.com/JoQWvRUdEoZ54VmdeUMjyp');
         },
         
         nantiSaja() {
             // Simpan ke sessionStorage biar mati selama tab ini belum di-close
             sessionStorage.setItem('snoozeGroupOverlay_' + this.userId, 'true');
             this.showOverlay = false;
         }
     }"
     @opening-selesai.window="cekDanTampilkan()"
     x-show="showOverlay"
     style="display: none;"
     class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
     
     {{-- Animasi Backdrop Hitam --}}
     x-transition:enter="transition ease-out duration-500"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    
    {{-- Background Gelap (Backdrop Blur) --}}
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="nantiSaja()"></div>

    {{-- Kotak Modal Pop-up --}}
    <div x-show="showOverlay"
         x-transition:enter="transition ease-out duration-500 delay-100"
         x-transition:enter-start="opacity-0 scale-90 translate-y-8"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-8"
         class="relative bg-white/95 backdrop-blur-xl w-full max-w-md rounded-3xl shadow-2xl border border-white/20 p-6 sm:p-8 text-center overflow-hidden">
        
        {{-- Efek dekorasi nyala di belakang icon --}}
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-32 h-32 bg-emerald-400/20 rounded-full blur-2xl"></div>

        {{-- Icon WA / Grup --}}
        <div class="relative w-16 h-16 mx-auto bg-emerald-100 text-emerald-500 rounded-full flex items-center justify-center shadow-inner mb-5">
            <i class="fab fa-whatsapp text-3xl"></i>
        </div>

        {{-- Teks Info --}}
        <h3 class="text-xl font-extrabold text-gray-900 tracking-tight mb-2">Gabung Grup Informasi</h3>
        <p class="text-sm text-gray-500 mb-8 leading-relaxed">
            Agar tidak ketinggalan informasi penting terkait pendaftaran dan ujian, silakan bergabung ke grup WhatsApp resmi calon mahasiswa <strong>{{ $namaProfesi ?? 'Profesi' }}</strong>.
        </p>

        {{-- Tombol Aksi --}}
        <div class="flex flex-col gap-3">
            <button @click="joinGroup()" class="w-full py-3.5 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-xl shadow-lg shadow-emerald-500/30 transition-all hover:-translate-y-0.5 flex items-center justify-center gap-2">
                <i class="fas fa-sign-in-alt"></i> Join Grup Sekarang
            </button>
            
            <button @click="nantiSaja()" class="w-full py-3 bg-gray-100 hover:bg-gray-200 text-gray-500 hover:text-gray-700 font-bold rounded-xl transition-colors">
                Nanti Saja
            </button>
        </div>
        
    </div>
</div>