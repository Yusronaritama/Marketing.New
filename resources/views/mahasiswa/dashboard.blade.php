@extends('layouts.mahasiswa.app')

@section('title', 'Dashboard Mahasiswa')

{{-- ======================================================================= --}}
{{-- 1. FLOATING HELP WIDGET (DRAGGABLE & BUBBLE MASCOT)                     --}}
{{-- ======================================================================= --}}
<div x-data="{ 
        open: false,
        showTooltip: false,
        isDragging: false,
        isClicking: false,
        offset: { x: 0, y: 0 },
        start: { x: 0, y: 0 },
        
        init() {
            // Tampilkan tooltip sapaan secara otomatis setelah 4 detik
            setTimeout(() => {
                if(!this.open) this.showTooltip = true;
            }, 4000);

            // Sembunyikan otomatis setelah 15 detik agar tidak mengganggu
            setTimeout(() => {
                this.showTooltip = false;
            }, 19000);
        },

        dragStart(e) {
            this.isDragging = true;
            this.isClicking = true;
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            this.start.x = clientX - this.offset.x;
            this.start.y = clientY - this.offset.y;
        },

        dragging(e) {
            if (!this.isDragging) return;
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            this.offset.x = clientX - this.start.x;
            this.offset.y = clientY - this.start.y;

            if (Math.abs(this.offset.x) > 5 || Math.abs(this.offset.y) > 5) {
                this.isClicking = false;
                this.showTooltip = false; // Hilangkan tooltip jika mulai digeser
            }
        },

        dragEnd() {
            this.isDragging = false;
        },

        toggle() {
            if (this.isClicking) {
                this.open = !this.open;
                if(this.open) this.showTooltip = false; // Sembunyikan tooltip saat menu dibuka
            }
        }
     }" 
     x-init="init()"
     x-on:mousemove.window="dragging($event)"
     x-on:mouseup.window="dragEnd()"
     x-on:touchmove.window="dragging($event)"
     x-on:touchend.window="dragEnd()"
     class="fixed bottom-28 md:bottom-10 right-6 md:right-10 z-[990] flex flex-col items-end"
     :style="`transform: translate(${offset.x}px, ${offset.y}px); touch-action: none;`">

    {{-- Chat Bubble Tooltip (Otomatis muncul di awal) --}}
    <div x-show="showTooltip && !open" 
         x-transition:enter="transition ease-out duration-500"
         x-transition:enter-start="opacity-0 scale-50 translate-y-8 translate-x-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0 translate-x-0"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-50 translate-y-4"
         class="absolute bottom-[4.5rem] right-0 w-56 bg-[#0A1930]/95 backdrop-blur-xl border border-emerald-500/40 p-4 rounded-3xl rounded-br-sm shadow-[0_20px_40px_rgba(16,185,129,0.25)] pointer-events-auto cursor-pointer"
         @click="toggle()">
        
        <button @click.stop="showTooltip = false" class="absolute top-2 right-3 text-slate-400 hover:text-red-400 text-xs transition-colors">
            <i class="fas fa-times"></i>
        </button>
        
        <div class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-full bg-emerald-500/20 flex items-center justify-center flex-shrink-0 border border-emerald-400/50">
                <i class="fas fa-headset text-emerald-400"></i>
            </div>
            <div>
                <h4 class="text-[11px] text-emerald-400 font-bold uppercase tracking-wider mb-0.5">Butuh Bantuan?</h4>
                <p class="text-[11px] text-slate-300 font-medium leading-relaxed pr-2">
                    👋 Hai! Jika ada kendala pendaftaran, silakan <span class="text-emerald-300 font-bold">Chat Admin</span> di sini ya!
                </p>
            </div>
        </div>
        
        {{-- Segitiga ekor chat --}}
        <div class="absolute -bottom-2 right-4 w-4 h-4 bg-[#0A1930] border-b border-r border-emerald-500/40 rotate-45"></div>
    </div>

    {{-- DROPDOWN MENU (Glassmorphism) --}}
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         class="mb-4 w-64 bg-[#0A1930]/95 backdrop-blur-xl rounded-3xl shadow-[0_20px_50px_rgba(0,0,0,0.5)] border border-emerald-500/30 p-3 pointer-events-auto origin-bottom-right relative overflow-hidden select-none">
        
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-emerald-400 to-cyan-500"></div>
        <div class="p-3 border-b border-white/10 mb-3 text-center">
            <h4 class="text-sm font-black text-white uppercase tracking-wider">Pusat Bantuan</h4>
            <p class="text-[10px] text-emerald-400 font-bold tracking-widest uppercase mt-1">Pilih Admin PMB</p>
        </div>
        <div class="space-y-2">
            <a href="https://wa.me/6281234567890" target="_blank" class="group flex items-center gap-4 p-3 rounded-2xl bg-white/5 hover:bg-emerald-500/20 transition-all border border-white/5 hover:border-emerald-500/40">
                <div class="w-10 h-10 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0 shadow-lg relative group-hover:scale-110 transition-transform">
                    <div class="absolute inset-0 bg-emerald-400/20 rounded-full animate-ping opacity-0 group-hover:opacity-100"></div>
                    <i class="fab fa-whatsapp text-xl relative z-10"></i>
                </div>
                <div>
                    <span class="block text-xs font-bold text-white tracking-wide group-hover:text-emerald-300 transition-colors">Admin 1</span>
                    <span class="block text-[10px] text-slate-400 mt-0.5 flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse shadow-[0_0_5px_rgba(16,185,129,0.8)]"></span> <span class="uppercase tracking-widest font-bold">Online</span></span>
                </div>
            </a>
            <a href="https://wa.me/6289876543210" target="_blank" class="group flex items-center gap-4 p-3 rounded-2xl bg-white/5 hover:bg-emerald-500/20 transition-all border border-white/5 hover:border-emerald-500/40">
                <div class="w-10 h-10 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0 shadow-lg relative group-hover:scale-110 transition-transform">
                    <div class="absolute inset-0 bg-emerald-400/20 rounded-full animate-ping opacity-0 group-hover:opacity-100"></div>
                    <i class="fab fa-whatsapp text-xl relative z-10"></i>
                </div>
                <div>
                    <span class="block text-xs font-bold text-white tracking-wide group-hover:text-emerald-300 transition-colors">Admin 2</span>
                    <span class="block text-[10px] text-slate-400 mt-0.5 flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse shadow-[0_0_5px_rgba(16,185,129,0.8)]"></span> <span class="uppercase tracking-widest font-bold">Online</span></span>
                </div>
            </a>
        </div>
    </div>

    {{-- TRIGGER BUTTON (Glassmorphism & Neon) --}}
    <button x-on:mousedown="dragStart($event)"
            x-on:touchstart.passive="dragStart($event)"
            x-on:click="toggle()"
            class="pointer-events-auto relative group flex items-center justify-center w-14 h-14 rounded-full shadow-[0_10px_25px_rgba(16,185,129,0.4)] hover:shadow-[0_15px_35px_rgba(16,185,129,0.6)] transition-all duration-300 transform hover:scale-110 active:scale-95 bg-emerald-500 text-white cursor-move select-none border-2 border-white/20">
        
        <span class="absolute inset-0 bg-emerald-400 rounded-full animate-ping opacity-50 group-hover:opacity-100 transition-opacity pointer-events-none"></span>
        
        <div class="relative w-6 h-6 pointer-events-none z-10">
            <i class="fas fa-headset text-[1.6rem] absolute inset-0 transition-all duration-300 transform flex items-center justify-center"
               :class="open ? 'opacity-0 rotate-90 scale-50' : 'opacity-100 rotate-0 scale-100 drop-shadow-md'"></i>
            <i class="fas fa-times text-[1.6rem] absolute inset-0 transition-all duration-300 transform flex items-center justify-center"
               :class="open ? 'opacity-100 rotate-0 scale-100 drop-shadow-md' : 'opacity-0 -rotate-90 scale-50'"></i>
        </div>
    </button>
</div>

                {{-- ======================================================================= --}}
                {{-- [BARU] BUTTON GRUP WA (DRAGGABLE / BISA DIGESER)                        --}}
                {{-- ======================================================================= --}}
                @php
                    $linkWaAktif = '';
                    if(isset($listGroupWA) && count($listGroupWA) > 0) {
                        $grup = $listGroupWA->where('is_active', true)->first() ?? $listGroupWA->last();
                        if($grup && !empty($grup->link_group) && $grup->link_group !== '#') {
                            $linkWaAktif = $grup->link_group;
                        }
                    }
                @endphp
            
                @if($linkWaAktif !== '')
                    <div x-data="{ 
                            isDragging: false,
                            hasDragged: false,
                            offset: { x: 0, y: 0 },
                            start: { x: 0, y: 0 },
                            
                            dragStart(e) {
                                this.isDragging = true;
                                this.hasDragged = false;
                                const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                                const clientY = e.touches ? e.touches[0].clientY : e.clientY;
                                this.start.x = clientX - this.offset.x;
                                this.start.y = clientY - this.offset.y;
                            },
            
                            dragging(e) {
                                if (!this.isDragging) return;
                                const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                                const clientY = e.touches ? e.touches[0].clientY : e.clientY;
                                
                                const newOffsetX = clientX - this.start.x;
                                const newOffsetY = clientY - this.start.y;
                                
                                // Kalau kegeser lebih dari 5 pixel, berarti dia niatnya nge-drag, bukan nge-klik
                                if (Math.abs(newOffsetX - this.offset.x) > 5 || Math.abs(newOffsetY - this.offset.y) > 5) {
                                    this.hasDragged = true;
                                }
                                
                                this.offset.x = newOffsetX;
                                this.offset.y = newOffsetY;
                            },
            
                            dragEnd() {
                                this.isDragging = false;
                            },
                            
                            goToLink(link) {
                                // Kalau murni di-klik (tidak digeser), baru buka linknya
                                if (!this.hasDragged) {
                                    window.open(link, '_blank');
                                }
                            }
                         }" 
                         x-on:mousemove.window="dragging($event)"
                         x-on:mouseup.window="dragEnd()"
                         x-on:touchmove.window="dragging($event)"
                         x-on:touchend.window="dragEnd()"
                         class="fixed bottom-6 left-6 z-[990] flex flex-col items-start"
                         :style="`transform: translate(${offset.x}px, ${offset.y}px); touch-action: none;`">
            
                        <button x-on:mousedown="dragStart($event)"
                                x-on:touchstart.passive="dragStart($event)"
                                x-on:click="goToLink('{{ $linkWaAktif }}')"
                                title="Join Grup WhatsApp PMB" 
                                class="bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 text-white px-4 py-2.5 rounded-full shadow-[0_4px_15px_rgba(16,185,129,0.4)] border-2 border-white flex items-center gap-2 transition-transform active:scale-95 group cursor-move select-none">
                            
                            {{-- pointer-events-none biar kliknya nggak nyangkut di icon --}}
                            <div class="relative flex items-center justify-center pointer-events-none">
                                <span class="absolute inline-flex h-full w-full rounded-full bg-white opacity-20 group-hover:animate-ping"></span>
                                <i class="fab fa-whatsapp text-xl"></i>
                            </div>
                            <span class="text-xs font-black uppercase tracking-wider pointer-events-none">Grup WA Maba</span>
                        </button>
                    </div>
                @endif

{{-- ======================================================================= --}}
{{-- 2. OPENING SCREEN (LOADING)                                             --}}
{{-- ======================================================================= --}}
@push('opening_screen')
    {{-- BYPASS SEMENTARA: Selalu memunculkan opening saat di-refresh untuk testing --}}
    <div id="vue-opening-app" data-is-logged-in="true" data-logo-url="{{ asset('images/logo_ubp.png') }}"></div>
@endpush



{{-- ======================================================================= --}}
{{-- 3. MAIN CONTENT (DENGAN LOGIKA AUTO-REDIRECT LULUS)                     --}}
{{-- ======================================================================= --}}
@section('content')

    {{-- [SUPER AMAN] WADAH DATA JSON RAHASIA BIAR KUTIP NGGAK TABRAKAN --}}
    <script id="waGroupData" type="application/json">
        {!! json_encode($listGroupWA ?? []) !!}
    </script>

    <div class="relative mt-4">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                
            {{-- BARIS 1: PROGRESS UTAMA (Isinya Wave Monitor, Step 3 Ujian, dll) --}}
            <div class="lg:col-span-12 order-1">
                @include('mahasiswa.dashboard.progress-layouts')
            </div>

            {{-- ========================================================= --}}
            {{-- [BARU] BARIS 1.5: BANNER GRUP WA PERMANEN                 --}}
            {{-- ========================================================= --}}
            <div class="lg:col-span-12 order-2" 
                 x-data="{ 
                     waLink: '',
                     groupList: [], 
                     init() {
                         this.$nextTick(() => {
                             // Tarik data aman dari script rahasia di atas
                             let rawData = document.getElementById('waGroupData').textContent;
                             this.groupList = JSON.parse(rawData || '[]');

                             const now = new Date();
                             const month = now.getMonth(); 
                             let wave = (month === 11 || month === 0) ? 1 : (month === 10 ? -1 : month + 1);
                             
                             let group = Array.isArray(this.groupList) ? this.groupList.find(g => g.gelombang == wave) : null;
                             
                             if (group && group.link_group) {
                                 this.waLink = group.link_group;
                             }
                         });
                     }
                 }">
                
                <div x-show="waLink !== ''" 
                     x-transition.opacity.duration.500ms
                     style="display: none;"
                     class="bg-gradient-to-r from-emerald-500 to-green-600 rounded-xl p-5 shadow-md flex flex-col md:flex-row items-center justify-between gap-4 border border-emerald-400 relative overflow-hidden group">
                    
                    {{-- Hiasan Background WA --}}
                    <div class="absolute right-0 top-0 opacity-10 transform translate-x-4 -translate-y-4 pointer-events-none transition-transform duration-500 group-hover:scale-110">
                        <i class="fab fa-whatsapp text-8xl"></i>
                    </div>
                    
                    <div class="flex items-center gap-4 text-white relative z-10 w-full md:w-auto">
                        <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center shrink-0 border border-white/30 backdrop-blur-sm">
                            <i class="fab fa-whatsapp text-3xl animate-bounce"></i>
                        </div>
                        <div>
                            <h4 class="font-black text-lg md:text-xl leading-none mb-1 uppercase tracking-wide">Grup WhatsApp PMB</h4>
                            <p class="text-xs md:text-sm text-emerald-50 font-medium">Gabung sekarang agar tidak ketinggalan informasi penting!</p>
                        </div>
                    </div>
                    
                    <a :href="waLink" target="_blank" class="w-full md:w-auto shrink-0 bg-white text-emerald-700 hover:bg-emerald-50 px-8 py-3 rounded-xl font-black text-sm shadow-lg transition-all transform hover:scale-105 hover:-translate-y-1 text-center uppercase tracking-wider relative z-10 flex items-center justify-center gap-2">
                        <i class="fas fa-sign-in-alt"></i> Gabung Grup
                    </a>
                </div>
            </div>
            {{-- ========================================================= --}}

            {{-- ========================================================= --}}
            {{-- BARIS 2: TOMBOL POPUP INFORMASI PMB (GELOMBANG & KUOTA)   --}}
            {{-- ========================================================= --}}
            <div class="lg:col-span-12 order-3 flex justify-center gap-4 flex-wrap mt-2 mb-8" x-data="{ openInfoModal: false }">
                
                {{-- Tombol Utama: Pusat Informasi --}}
                <button @click="openInfoModal = true" 
                        class="bg-[#0A1930]/60 backdrop-blur-xl border border-blue-400/30 text-white px-8 py-4 rounded-3xl shadow-[0_10px_30px_rgba(37,99,235,0.2)] hover:shadow-[0_10px_40px_rgba(37,99,235,0.4)] hover:bg-[#0A1930]/90 hover:border-blue-400/60 transition-all duration-500 flex items-center gap-4 group">
                    <div class="w-12 h-12 rounded-full bg-blue-500/20 flex items-center justify-center text-blue-300 group-hover:scale-110 transition-transform">
                        <i class="fas fa-layer-group text-xl"></i>
                    </div>
                    <div class="text-left">
                        <h4 class="font-black text-sm md:text-base tracking-widest uppercase">Pusat Informasi</h4>
                        <p class="text-[10px] md:text-xs text-blue-200">Cek Status Gelombang & Kuota</p>
                    </div>
                    <i class="fas fa-chevron-right text-blue-400/50 ml-4 group-hover:translate-x-2 transition-transform"></i>
                </button>

                {{-- Tombol Baru: AI Assistant (Chatbot) --}}
                <a href="{{ route('mahasiswa.chatbot.index') }}" 
                        class="bg-[#0A1930]/60 backdrop-blur-xl border border-indigo-400/30 text-white px-8 py-4 rounded-3xl shadow-[0_10px_30px_rgba(79,70,229,0.2)] hover:shadow-[0_10px_40px_rgba(79,70,229,0.4)] hover:bg-[#0A1930]/90 hover:border-indigo-400/60 transition-all duration-500 flex items-center gap-4 group cursor-pointer">
                    <div class="w-12 h-12 rounded-full bg-indigo-500/20 flex items-center justify-center text-indigo-300 group-hover:scale-110 transition-transform relative">
                        <i class="fas fa-robot text-xl"></i>
                        <span class="absolute -top-1 -right-1 w-3 h-3 bg-emerald-500 border-2 border-[#0A1930] rounded-full animate-pulse"></span>
                    </div>
                    <div class="text-left">
                        <h4 class="font-black text-sm md:text-base tracking-widest uppercase">AI Assistant</h4>
                        <p class="text-[10px] md:text-xs text-indigo-200">Tanya seputar Prodi & Biaya</p>
                    </div>
                    <i class="fas fa-chevron-right text-indigo-400/50 ml-4 group-hover:translate-x-2 transition-transform"></i>
                </a>

                {{-- MODAL POPUP (GLASSMORPHISM - BOTTOM SHEET PADA MOBILE) --}}
                <div x-show="openInfoModal" x-cloak 
                     class="fixed inset-0 z-[99999] flex items-end md:items-center justify-center p-0 md:p-6"
                     x-data="{ activeTab: 'gelombang' }">
                    
                    {{-- Overlay Hitam/Blur --}}
                    <div class="absolute inset-0 bg-black/60 backdrop-blur-md" 
                         @click="openInfoModal = false"
                         x-show="openInfoModal"
                         x-transition:enter="transition ease-out duration-500"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="transition ease-in duration-300"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0">
                    </div>

                    {{-- Kotak Modal --}}
                    <div class="relative w-full md:max-w-3xl bg-[#0A1930]/95 md:bg-[#0A1930]/80 backdrop-blur-3xl border-t border-white/20 md:border md:border-white/20 rounded-t-[2.5rem] md:rounded-[2.5rem] shadow-[0_-10px_40px_rgba(0,0,0,0.5)] overflow-hidden flex flex-col max-h-[85vh] md:max-h-[90vh]"
                         x-show="openInfoModal"
                         x-transition:enter="transition ease-out duration-500"
                         x-transition:enter-start="opacity-0 md:scale-95 translate-y-full md:translate-y-10"
                         x-transition:enter-end="opacity-100 md:scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-300"
                         x-transition:leave-start="opacity-100 md:scale-100 translate-y-0"
                         x-transition:leave-end="opacity-0 md:scale-95 translate-y-full md:translate-y-10"
                         @click.stop>
                        
                        {{-- Hiasan Latar Modal (Glowing Orbs) --}}
                        <div class="absolute top-0 left-0 w-64 h-64 bg-blue-500/20 rounded-full blur-[80px] pointer-events-none"></div>
                        <div class="absolute bottom-0 right-0 w-64 h-64 bg-cyan-500/20 rounded-full blur-[80px] pointer-events-none"></div>
                        <div class="absolute inset-0 bg-[linear-gradient(to_right,#ffffff05_1px,transparent_1px),linear-gradient(to_bottom,#ffffff05_1px,transparent_1px)] bg-[size:24px_24px] pointer-events-none"></div>

                        {{-- Handle for mobile (Visual only) --}}
                        <div class="w-full flex justify-center pt-3 pb-1 md:hidden bg-white/5 border-b border-white/5 relative z-20 cursor-pointer" @click="openInfoModal = false">
                            <div class="w-12 h-1.5 bg-white/30 rounded-full"></div>
                        </div>

                        {{-- Header Modal --}}
                        <div class="relative z-10 p-5 md:p-8 border-b border-white/10 flex justify-between items-center bg-white/5">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-blue-500/20 border border-blue-400/30 flex items-center justify-center text-blue-300 shadow-inner shrink-0">
                                    <i class="fas fa-chart-pie md:text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-base md:text-xl font-black text-white uppercase tracking-widest leading-tight">Pusat Informasi</h3>
                                    <p class="text-[10px] md:text-xs text-blue-200 mt-0.5">PMB Universitas Buana Perjuangan Karawang</p>
                                </div>
                            </div>
                            <button @click="openInfoModal = false" class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-white/5 hover:bg-red-500/20 border border-transparent hover:border-red-500/30 text-slate-300 hover:text-red-400 transition-all flex items-center justify-center group shrink-0">
                                <i class="fas fa-times md:text-lg group-hover:rotate-90 transition-transform"></i>
                            </button>
                        </div>

                        {{-- Tab Navigation --}}
                        <div class="relative z-10 flex border-b border-white/10 bg-white/5">
                            <button @click="activeTab = 'gelombang'" class="flex-1 py-3 text-xs md:text-sm font-bold uppercase tracking-widest transition-all border-b-2"
                                    :class="activeTab === 'gelombang' ? 'text-blue-300 border-blue-400 bg-blue-500/10' : 'text-slate-400 border-transparent hover:bg-white/5 hover:text-slate-300'">
                                <i class="fas fa-broadcast-tower md:mr-2 block md:inline mb-1 md:mb-0 text-lg md:text-sm"></i> 
                                <span class="block md:inline">Status Gelombang</span>
                            </button>
                            <button @click="activeTab = 'kuota'" class="flex-1 py-3 text-xs md:text-sm font-bold uppercase tracking-widest transition-all border-b-2"
                                    :class="activeTab === 'kuota' ? 'text-cyan-300 border-cyan-400 bg-cyan-500/10' : 'text-slate-400 border-transparent hover:bg-white/5 hover:text-slate-300'">
                                <i class="fas fa-search-location md:mr-2 block md:inline mb-1 md:mb-0 text-lg md:text-sm"></i> 
                                <span class="block md:inline">Cek Kuota</span>
                            </button>
                        </div>

                        {{-- Isi Modal --}}
                        <div class="relative z-10 p-5 pb-12 md:p-8 overflow-y-auto custom-scrollbar flex-grow">
                            
                            {{-- Tab Konten: Gelombang --}}
                            <div x-show="activeTab === 'gelombang'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                                @include('mahasiswa.dashboard.partials.carousel_kiri')
                            </div>

                            {{-- Tab Konten: Kuota --}}
                            <div x-show="activeTab === 'kuota'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" style="display: none;">
                                @include('mahasiswa.dashboard.partials.info-carousel')
                            </div>
                            
                        </div>
                        
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- 4. GLOBAL POPUP WHATSAPP (ONCE PER WAVE - WITH HINT TRIGGER)        --}}
    {{-- =================================================================== --}}
    <div x-data="{ 
            showWAModal: false,
            waLink: '',
            groupList: [], 

            init() {
                // Tarik data aman dari script rahasia di atas
                let rawData = document.getElementById('waGroupData');
                if(!rawData) return;
                this.groupList = JSON.parse(rawData.textContent || '[]');

                const now = new Date();
                const month = now.getMonth(); 
                let wave = (month === 11 || month === 0) ? 1 : (month === 10 ? -1 : month + 1);

                let group = Array.isArray(this.groupList) ? this.groupList.find(g => g.gelombang == wave) : null;

                if (group && group.link_group) {
                    this.waLink = group.link_group;

                    const storageKey = 'wa_popup_count_wave_' + wave;
                    let currentCount = parseInt(localStorage.getItem(storageKey) || '0');

                    if (currentCount < 2) {
                        setTimeout(() => { 
                            this.showWAModal = true;
                            localStorage.setItem(storageKey, currentCount + 1);
                        }, 3500);
                    }
                }
            },

            closeModal() {
                this.showWAModal = false;
            },

            joinGroup() {
                if (this.waLink) {
                    window.open(this.waLink, '_blank');
                    this.showWAModal = false; 
                    this.markAsDoneForever();
                }
            },

            markAsDoneForever() {
                const now = new Date();
                const month = now.getMonth(); 
                const wave = (month === 11 || month === 0) ? 1 : (month === 10 ? -1 : month + 1);
                localStorage.setItem('wa_popup_count_wave_' + wave, '99');
            }
        }"
        x-init="init()"
        class="relative z-[9995]">

        {{-- Overlay Gelap --}}
        <div x-show="showWAModal" 
             x-transition.opacity
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
             style="display: none;"></div>

        {{-- Modal Box (Glassmorphism WhatsApp) --}}
        <div id="wa-modal-overlay" x-show="showWAModal"
             style="display: none;"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-90 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-90 translate-y-4"
             class="fixed inset-0 flex items-center justify-center p-4">
            
            <div class="relative w-[90vw] max-w-[360px] bg-[#0A1930]/95 backdrop-blur-3xl border border-emerald-500/30 rounded-[2.5rem] shadow-[0_20px_60px_rgba(16,185,129,0.25)] overflow-hidden">
                
                {{-- Hiasan Glow & Gradient --}}
                <div class="absolute top-0 right-0 w-48 h-48 bg-emerald-500/20 rounded-full blur-[70px] pointer-events-none"></div>
                <div class="absolute bottom-0 left-0 w-32 h-32 bg-cyan-500/10 rounded-full blur-[50px] pointer-events-none"></div>

                {{-- Tombol Close X --}}
                <button @click="closeModal()" class="absolute top-5 right-5 text-slate-400 hover:text-white bg-white/5 hover:bg-red-500/20 hover:border-red-500/30 border border-transparent rounded-full w-9 h-9 flex items-center justify-center transition-all cursor-pointer z-20 group">
                    <i class="fas fa-times group-hover:rotate-90 transition-transform"></i>
                </button>

                {{-- Header & Icon Animasi --}}
                <div class="pt-10 pb-4 flex flex-col items-center text-center relative z-10 border-b border-white/5">
                    <div class="w-24 h-24 bg-emerald-500/10 border-2 border-emerald-400/50 rounded-full flex items-center justify-center mb-5 shadow-[0_0_25px_rgba(52,211,153,0.3)] relative">
                        <div class="absolute inset-0 bg-emerald-400/20 rounded-full animate-ping opacity-50"></div>
                        <i class="fab fa-whatsapp text-[3rem] text-emerald-400 drop-shadow-[0_0_15px_rgba(52,211,153,0.8)]"></i>
                    </div>
                    <h3 class="font-black text-white text-xl tracking-wider uppercase mb-1">Grup WhatsApp</h3>
                    <p class="text-[10px] text-emerald-400 font-bold tracking-widest uppercase bg-emerald-500/10 border border-emerald-500/20 px-3 py-1 rounded-full">
                        Info Seleksi PMB
                    </p>
                </div>

                {{-- Body Content --}}
                <div class="px-7 py-6 text-center relative z-10 bg-black/10">
                    <p class="text-[11px] text-slate-400 mb-6 leading-relaxed font-medium">
                        Pastikan Anda bergabung ke grup resmi WhatsApp untuk mendapatkan informasi dan jadwal seleksi terbaru.
                    </p>
                    <div class="space-y-3">
                        <button @click="joinGroup()" class="w-full flex items-center justify-center gap-3 bg-emerald-500/15 hover:bg-emerald-500/25 text-emerald-400 hover:text-emerald-300 font-black tracking-wide py-4 rounded-2xl transition-all border border-emerald-500/40 hover:shadow-[0_0_20px_rgba(52,211,153,0.3)] group">
                            <i class="fab fa-whatsapp text-xl group-hover:scale-110 transition-transform"></i>
                            GABUNG SEKARANG
                        </button>
                        <button @click="closeModal()" class="w-full text-[10px] text-slate-500 hover:text-slate-300 font-bold py-3 uppercase tracking-widest transition-colors">
                            Nanti Saja
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@include('mahasiswa.components.modal-reset')