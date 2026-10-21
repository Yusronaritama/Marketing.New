{{-- ==========================================
     OVERLAY DRAGGABLE WHATSAPP (ULTIMATE WIDGET)
     Menggabungkan Kontak Admin & Join Grup WA
     ========================================== --}}
<div x-data="waWidget()" 
     x-init="initPosition()"
     @opening-selesai.window="munculkanDanSapa()">
    
    <div x-show="showWidget"
         style="display: none;"
         x-transition:enter="transition ease-out duration-700"
         x-transition:enter-start="opacity-0 scale-90"
         x-transition:enter-end="opacity-100 scale-100"
         class="fixed z-[999999] flex items-center justify-center" 
         :style="isGreeting 
            ? 'top: 50%; left: 50%; transform: translate(-50%, -50%); transition: all 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);' 
            : `left: ${pos.x}px; top: ${pos.y}px; transition: ${isDragging ? 'none' : 'all 0.8s cubic-bezier(0.34, 1.56, 0.64, 1)'};`">
        
        {{-- Menu Dropdown Popup --}}
        <div x-show="isOpen && !isGreeting" 
             @click.away="isOpen = false"
             x-transition:enter="transition ease-out duration-400"
             x-transition:enter-start="opacity-0 scale-90 translate-y-6"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-90 translate-y-6"
             style="display: none;"
             class="absolute bg-white rounded-3xl shadow-2xl p-5 w-[280px] border border-gray-100 flex flex-col gap-4"
             :class="{
                'right-0': pos.x > (window.innerWidth / 2),
                'left-0': pos.x <= (window.innerWidth / 2),
                'bottom-full mb-4': pos.y > (window.innerHeight / 2),
                'top-full mt-4': pos.y <= (window.innerHeight / 2)
             }">
             
             <div class="flex justify-between items-center border-b pb-3">
                 <h4 class="text-sm font-extrabold text-gray-800">Pusat Bantuan</h4>
                 <button @click="isOpen = false" class="text-gray-400 hover:text-red-500 bg-gray-100 hover:bg-red-50 w-7 h-7 rounded-full flex items-center justify-center transition"><i class="fas fa-times"></i></button>
             </div>
             
             {{-- BANNER JOIN GRUP (Hanya muncul jika belum join) --}}
             <template x-if="!hasJoinedGroup">
                 <div class="bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-100 rounded-2xl p-4 shadow-sm relative overflow-hidden">
                     <div class="absolute -right-4 -top-4 text-emerald-200 opacity-50"><i class="fas fa-users text-5xl"></i></div>
                     
                     <div class="relative z-10">
                         <h5 class="text-xs font-bold text-emerald-800 uppercase tracking-wider mb-1">Grup Resmi Maba</h5>
                         <p class="text-[11px] text-emerald-600 mb-3 leading-relaxed">Jangan sampai ketinggalan info ujian & pemberkasan!</p>
                         <button @click="joinGroupAction()" class="w-full py-2 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold rounded-xl shadow-md shadow-emerald-500/30 transition-all hover:-translate-y-0.5 flex items-center justify-center gap-2">
                             <i class="fab fa-whatsapp text-sm"></i> Gabung Sekarang
                         </button>
                     </div>
                 </div>
             </template>

             {{-- List Admin --}}
             <div class="flex flex-col gap-2">
                 <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-1">Kontak Admin</p>
                 
                 <a href="https://wa.me/message/H3UAHPO4YOI5B1" target="_blank" rel="noopener noreferrer"
                    class="flex items-center gap-3 p-2.5 w-full text-left bg-gray-50 hover:bg-blue-50 hover:border-blue-200 border border-transparent rounded-xl transition group">
                     <div class="w-9 h-9 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center shrink-0 group-hover:bg-[#25D366] group-hover:text-white transition">
                         <i class="fab fa-whatsapp text-lg"></i>
                     </div>
                     <div>
                         <p class="text-xs font-bold text-gray-800 group-hover:text-blue-700">Admin Sistem</p>
                         <p class="text-[10px] text-gray-500 font-mono mt-0.5">0811-1111-1111</p>
                     </div>
                 </a>

                 <a href="#" target="_blank" rel="noopener noreferrer"
                    class="flex items-center gap-3 p-2.5 w-full text-left bg-gray-50 hover:bg-blue-50 hover:border-blue-200 border border-transparent rounded-xl transition group">
                     <div class="w-9 h-9 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center shrink-0 group-hover:bg-[#25D366] group-hover:text-white transition">
                         <i class="fab fa-whatsapp text-lg"></i>
                     </div>
                     <div>
                         <p class="text-xs font-bold text-gray-800 group-hover:text-blue-700">Admin Farmasi</p>
                         <p class="text-[10px] text-gray-500 font-mono mt-0.5">0822-2222-2222</p>
                     </div>
                 </a>
             </div>
        </div>

        {{-- Tombol Utama (Cerdas nyesuain lebar & teks) --}}
        <button @mousedown.prevent="startDrag"
                @touchstart="startDrag"
                @click="toggleMenu"
                class="bg-blue-600 text-white flex items-center justify-center shadow-[0_4px_25px_rgba(37,99,235,0.5)] transition-all duration-700 ease-in-out border-2 border-white focus:outline-none overflow-hidden"
                :class="isGreeting 
                    ? 'w-60 h-16 rounded-full cursor-default scale-110' 
                    : 'w-14 h-14 rounded-full hover:bg-blue-700 cursor-move'">
            
            {{-- Icon Cerdas (Bisa jadi logo WA atau Headset) --}}
            <i class="pointer-events-none transition-all duration-700"
               :class="(isGreeting && !hasJoinedGroup) ? 'fab fa-whatsapp text-3xl mr-3' : (isGreeting ? 'fas fa-headset text-3xl mr-3' : 'fas fa-headset text-2xl')"></i>
            
            {{-- Teks Cerdas (Panjang lebar otomatis nyesuain isi) --}}
            <span class="font-bold tracking-wide whitespace-nowrap overflow-hidden transition-all duration-500"
                  :class="isGreeting ? 'w-40 opacity-100' : 'w-0 opacity-0'"
                  x-text="hasJoinedGroup ? 'Pusat Bantuan' : 'Grup WA & Bantuan'">
            </span>
        </button>
        
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('waWidget', () => ({
        showWidget: false, 
        isGreeting: false,
        isOpen: false,
        isDragging: false,
        pos: { x: 0, y: 0 },
        start: { x: 0, y: 0 },
        
        userId: {{ auth()->id() ?? 0 }},
        hasJoinedGroup: false,

        initPosition() {
            // Cek status grup di awal buat nentuin teks greeting
            this.hasJoinedGroup = localStorage.getItem('hideGroupOverlay_' + this.userId) === 'true';

            let savedPos = localStorage.getItem('waWidgetPosition');
            if (savedPos) {
                let parsedPos = JSON.parse(savedPos);
                this.pos.x = Math.min(parsedPos.x, window.innerWidth - 70);
                this.pos.y = Math.min(parsedPos.y, window.innerHeight - 70);
            } else {
                this.pos.x = window.innerWidth - 80;
                this.pos.y = window.innerHeight - 80;
            }
            
            window.addEventListener('resize', () => {
                if (!this.isGreeting) {
                    if (this.pos.x > window.innerWidth - 70) this.pos.x = window.innerWidth - 80;
                    if (this.pos.y > window.innerHeight - 70) this.pos.y = window.innerHeight - 80;
                }
            });
        },

        munculkanDanSapa() {
            // Cek apakah di sesi ini user udah pernah disapa
            if (sessionStorage.getItem('waGreetingDone')) {
                // Kalau udah, langsung munculin di pojok aja (mode drag)
                this.showWidget = true;
                this.isGreeting = false;
                return; // Hentikan fungsi di sini
            }

            // Kalau belum pernah (baru banget login), sapa dari tengah!
            this.isGreeting = true;
            this.showWidget = true;
            
            // Catat di memori browser kalau user ini udah disapa
            sessionStorage.setItem('waGreetingDone', 'true');

            // Kasih waktu 2 detik buat pamer, terus suruh minggir ke posisinya
            setTimeout(() => {
                this.isGreeting = false;
            }, 2000);
        },

        joinGroupAction() {
            this.hasJoinedGroup = true;
            localStorage.setItem('hideGroupOverlay_' + this.userId, 'true');
            window.open('https://chat.whatsapp.com/JoQWvRUdEoZ54VmdeUMjyp', '_blank');
        },

        startDrag(e) {
            if (this.isGreeting) return;

            this.isDragging = false; 
            let clientX = e.type.includes('mouse') ? e.clientX : e.touches[0].clientX;
            let clientY = e.type.includes('mouse') ? e.clientY : e.touches[0].clientY;
            
            this.start.x = clientX - this.pos.x;
            this.start.y = clientY - this.pos.y;

            const moveHandler = (ev) => {
                if (ev.type.includes('touch')) ev.preventDefault(); 
                
                let mX = ev.type.includes('mouse') ? ev.clientX : ev.touches[0].clientX;
                let mY = ev.type.includes('mouse') ? ev.clientY : ev.touches[0].clientY;
                
                // [BARU] Hitung jarak getaran/geseran mouse
                let moveDistance = Math.abs(mX - clientX) + Math.abs(mY - clientY);
                
                // [BARU] Kalau gesernya lebih dari 5 pixel, baru resmi dianggap Dragging
                if (moveDistance > 5) {
                    this.isDragging = true; 
                }
                
                // Cuma pindahin posisi tombol kalau statusnya beneran Dragging
                if (this.isDragging) {
                    let newX = Math.max(0, Math.min(mX - this.start.x, window.innerWidth - 70));
                    let newY = Math.max(0, Math.min(mY - this.start.y, window.innerHeight - 70));

                    this.pos.x = newX;
                    this.pos.y = newY;
                }
            };

            const upHandler = () => {
                window.removeEventListener('mousemove', moveHandler);
                window.removeEventListener('mouseup', upHandler);
                window.removeEventListener('touchmove', moveHandler);
                window.removeEventListener('touchend', upHandler);
                
                if (this.isDragging) {
                    localStorage.setItem('waWidgetPosition', JSON.stringify({ x: this.pos.x, y: this.pos.y }));
                }
            };

            window.addEventListener('mousemove', moveHandler);
            window.addEventListener('mouseup', upHandler);
            window.addEventListener('touchmove', moveHandler, { passive: false });
            window.addEventListener('touchend', upHandler);
        },

        toggleMenu(e) {
            if (!this.isDragging && !this.isGreeting) {
                this.isOpen = !this.isOpen;
            }
        }
    }));
});
</script>