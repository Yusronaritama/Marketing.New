@extends('layouts.admin.app')

@section('title', 'Pengaturan Sistem Terpadu')

@section('content')
<div class="min-h-screen pb-12">
    <!-- Header Section with Gradient & Glassmorphism -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-900 via-slate-800 to-slate-900 p-8 shadow-2xl mb-8">
        <!-- Decorative blobs -->
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-64 h-64 rounded-full bg-blue-500 opacity-20 blur-3xl mix-blend-screen pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-80 h-80 rounded-full bg-purple-500 opacity-20 blur-3xl mix-blend-screen pointer-events-none"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
            <div>
                <h2 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-200 to-white mb-2 tracking-tight flex items-center">
                    <i class="fas fa-sliders-h mr-3 text-blue-300"></i>Pusat Pengaturan Sistem
                </h2>
                <p class="text-slate-400 font-medium text-sm md:text-base max-w-2xl leading-relaxed">
                    Kelola jadwal pendaftaran, status gelombang, dan pemeliharaan aplikasi (Maintenance) dalam satu dasbor kendali terpadu yang canggih.
                </p>
            </div>
            <div class="hidden md:flex p-4 rounded-2xl bg-white/10 backdrop-blur-md border border-white/10 shadow-inner">
                <i class="fas fa-cogs text-4xl text-blue-300 opacity-80"></i>
            </div>
        </div>
    </div>

    <!-- Cards Grid Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 px-2">
        
        {{-- CARD 1: MAINTENANCE --}}
        <div x-data="{ 
            isActive: false, scheduledAt: null, estimatedEnd: null, loading: false,
            async init() {
                const check = async () => {
                    try {
                        let resp = await fetch('{{ route('admin.perbaikan.status') }}', { headers: { 'Accept': 'application/json' } });
                        let data = await resp.json();
                        this.isActive = data.is_maintenance;
                        this.scheduledAt = data.scheduled_at;
                        this.estimatedEnd = data.estimated_end;
                    } catch (e) { console.error(e); }
                };
                await check(); setInterval(() => { check(); }, 5000); 
            },
            async openMenu() {
                const formatTime = (dateStr) => dateStr ? new Date(dateStr).toLocaleString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }) : '-';
                if (this.isActive || this.scheduledAt) {
                    const startTime = this.scheduledAt ? formatTime(this.scheduledAt) : (this.isActive ? 'Sekarang' : '-');
                    const endTime = this.estimatedEnd ? formatTime(this.estimatedEnd) : 'Tidak Ditentukan';
                    const { isConfirmed, isDenied } = await Swal.fire({
                        title: '<span style=\'font-size: 15px; font-weight: 700\'>🛠️ Status Maintenance</span>', 
                        html: `<div style='text-align: left; font-size: 12px; background: #f8fafc; padding: 12px; border-radius: 12px; border: 1px solid #e2e8f0'>
                                <div style='display: flex; justify-content: space-between; margin-bottom: 6px'>
                                    <span style='color: #64748b'>Mulai</span><span style='font-weight: 600'>${startTime}</span>
                                </div>
                                <div style='display: flex; justify-content: space-between'>
                                    <span style='color: #64748b'>Estimasi Selesai</span><span style='font-weight: 600; color: #3b82f6'>${endTime}</span>
                                </div>
                            </div>`,
                        showCancelButton: true, showDenyButton: true,
                        confirmButtonColor: '#ef4444', denyButtonColor: '#f59e0b',
                        confirmButtonText: 'Matikan', denyButtonText: 'Edit Jadwal', cancelButtonText: 'Tutup'
                    });
                    if (isConfirmed) { await this.saveStatus(false, null, null); return; }
                    else if (!isDenied) return;
                }
                this.openForm();
            },
            async openForm() {
                await Swal.fire({
                    title: '<span style=\'font-size: 15px; font-weight: 700\'>⚙️ Konfigurasi Sistem</span>',
                    html: `<div style='display: flex; flex-direction: column; gap: 10px; text-align: left'>
                            <button id='btn-now' style='background: #ef4444; color: white; border: none; padding: 10px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 12px'>🔴 Aktifkan Sekarang</button>
                            <div style='border-top: 1px dotted #e2e8f0; margin: 4px 0'></div>
                            <label style='font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase'>Waktu Mulai</label>
                            <input id='swal-start' type='datetime-local' style='width: 100%; padding: 8px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 13px' value='${this.scheduledAt ? this.scheduledAt.slice(0,16) : ''}'>
                            <label style='font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase'>Estimasi Selesai</label>
                            <input id='swal-end' type='datetime-local' style='width: 100%; padding: 8px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 13px' value='${this.estimatedEnd ? this.estimatedEnd.slice(0,16) : ''}'>
                            <button id='btn-save' style='background: #0f172a; color: white; border: none; padding: 12px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 12px; margin-top: 5px'>💾 Simpan Perubahan</button>
                        </div>`,
                    showConfirmButton: false, showCloseButton: true,
                    didOpen: () => {
                        document.getElementById('btn-now').onclick = () => { Swal.close(); this.saveStatus(true, null, document.getElementById('swal-end').value); };
                        document.getElementById('btn-save').onclick = () => {
                            const s = document.getElementById('swal-start').value;
                            if (!this.isActive && !s) { Swal.showValidationMessage('Isi waktu mulai!'); return; }
                            Swal.close(); this.saveStatus(this.isActive, s, document.getElementById('swal-end').value);
                        };
                    }
                });
            },
            async saveStatus(active, start, end) {
                this.loading = true;
                try {
                    await fetch('{{ route('admin.perbaikan.toggle') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ active, scheduled_at: start, estimated_end: end })
                    });
                    this.isActive = active; this.scheduledAt = start; this.estimatedEnd = end;
                } catch (e) { console.error(e); } finally { this.loading = false; }
            }
        }" 
        class="group relative bg-white dark:bg-slate-800 rounded-3xl p-6 md:p-8 shadow-lg hover:shadow-2xl transition-all duration-500 border border-slate-100 dark:border-slate-700 hover:-translate-y-2 overflow-hidden flex flex-col">
            <!-- Glow effect on hover -->
            <div class="absolute inset-0 bg-gradient-to-br from-red-500/5 to-orange-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
            
            <div class="relative z-10 flex flex-col h-full">
                <div class="flex items-start justify-between mb-6">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center flex-shrink-0 transition-all duration-500 shadow-md"
                             :class="isActive ? 'bg-gradient-to-br from-red-500 to-rose-600 text-white shadow-red-500/40' : (scheduledAt ? 'bg-gradient-to-br from-amber-400 to-orange-500 text-white shadow-orange-500/40' : 'bg-slate-50 dark:bg-slate-700 text-slate-500 dark:text-slate-400 group-hover:bg-red-50 dark:group-hover:bg-slate-600')">
                            <template x-if="loading"><i class="fas fa-circle-notch fa-spin text-2xl"></i></template>
                            <template x-if="!loading"><i class="fas fa-server text-2xl" :class="isActive ? 'animate-pulse' : ''"></i></template>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-1">Maintenance Server</h3>
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider transition-colors"
                                 :class="isActive ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : (scheduledAt ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400')">
                                <span class="w-1.5 h-1.5 rounded-full" :class="isActive ? 'bg-red-500 animate-ping' : (scheduledAt ? 'bg-amber-500' : 'bg-emerald-500')"></span>
                                <span x-text="isActive ? 'Sistem Offline' : (scheduledAt ? 'Terjadwal' : 'Sistem Berjalan')"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed flex-grow mb-8">
                    Gunakan fitur ini untuk mematikan sementara akses pengguna ke aplikasi saat sedang dilakukan perbaikan server atau pembaharuan sistem. Data pendaftar tetap aman.
                </p>

                <button @click="openMenu()" :disabled="loading" 
                        class="w-full relative overflow-hidden group/btn px-6 py-4 rounded-2xl font-semibold text-sm transition-all duration-300 shadow-sm"
                        :class="isActive 
                            ? 'bg-red-50 text-red-600 hover:bg-red-100 hover:shadow-md hover:shadow-red-500/20 dark:bg-red-500/10 dark:text-red-400 dark:hover:bg-red-500/20' 
                            : 'bg-slate-900 text-white hover:bg-slate-800 hover:shadow-xl hover:shadow-slate-900/20 dark:bg-indigo-600 dark:hover:bg-indigo-500'">
                    <div class="relative z-10 flex items-center justify-center gap-2">
                        <span x-text="isActive ? 'Matikan Maintenance' : 'Atur Konfigurasi'"></span>
                        <i class="fas fa-arrow-right transition-transform group-hover/btn:translate-x-1"></i>
                    </div>
                </button>
            </div>
        </div>

        {{-- CARD 2: GELOMBANG --}}
        <div x-data="{ 
            isClosed: false, closingAt: null, nextOpeningAt: null, loading: false,
            init() {
                const check = async () => {
                    try {
                        let resp = await fetch('{{ route('admin.gelombang.status') }}', { headers: { 'Accept': 'application/json' } });
                        let data = await resp.json();
                        this.isClosed = data.is_closed; 
                        this.closingAt = data.closing_at; 
                        this.nextOpeningAt = data.next_opening_at;
                    } catch (e) { console.error(e); }
                };
                check(); setInterval(() => { check(); }, 5000); 
            },
            async openMenu() {
                const formatTime = (dateStr) => dateStr ? new Date(dateStr).toLocaleString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }) : '-';
                if (this.isClosed || this.closingAt) {
                    const closeTime = this.closingAt ? formatTime(this.closingAt) : (this.isClosed ? 'Sudah Ditutup' : '-');
                    const nextOpen = this.nextOpeningAt ? formatTime(this.nextOpeningAt) : 'Belum Ditentukan';
                    const { isConfirmed, isDenied } = await Swal.fire({
                        title: '<span style=\'font-size: 15px; font-weight: 700\'>📅 Status Gelombang</span>',
                        html: `<div style='text-align: left; font-size: 12px; background: #f8fafc; padding: 12px; border-radius: 12px; border: 1px solid #e2e8f0'>
                                <div style='display: flex; justify-content: space-between; margin-bottom: 6px'>
                                    <span style='color: #64748b'>Penutupan</span><span style='font-weight: 600; color: ${this.isClosed ? '#ef4444' : '#0f172a'}'>${closeTime}</span>
                                </div>
                                <div style='display: flex; justify-content: space-between'>
                                    <span style='color: #64748b'>Buka Kembali</span><span style='font-weight: 600; color: #3b82f6'>${nextOpen}</span>
                                </div>
                            </div>`,
                        showCancelButton: true, showDenyButton: true,
                        confirmButtonText: 'Buka Sekarang', denyButtonText: 'Ubah Jadwal', cancelButtonText: 'Batal',
                        confirmButtonColor: '#10b981', denyButtonColor: '#3b82f6',
                    });
                    if (isConfirmed) { await this.saveStatus(false, null, null); return; } 
                    else if (!isDenied) return;
                }
                this.openForm();
            },
            async openForm() {
                await Swal.fire({
                    title: '<span style=\'font-size: 15px; font-weight: 700\'>🗓️ Jadwal Gelombang</span>',
                    html: `<div style='display: flex; flex-direction: column; gap: 10px; text-align: left'>
                            <button id='btn-close-now' style='background: #f97316; color: white; border: none; padding: 10px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 12px'>🚫 Tutup Sekarang</button>
                            <div style='border-top: 1px dotted #e2e8f0; margin: 4px 0'></div>
                            <label style='font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase'>Tgl Tutup Otomatis</label>
                            <input id='swal-close' type='datetime-local' style='width: 100%; padding: 8px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 13px' value='${this.closingAt ? this.closingAt.slice(0,16) : ''}'>
                            <label style='font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase'>Estimasi Buka Kembali</label>
                            <input id='swal-next' type='datetime-local' style='width: 100%; padding: 8px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 13px' value='${this.nextOpeningAt ? this.nextOpeningAt.slice(0,16) : ''}'>
                            <button id='btn-save' style='background: #0f172a; color: white; border: none; padding: 12px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 12px; margin-top: 5px'>💾 Simpan Jadwal</button>
                        </div>`,
                    showConfirmButton: false, showCloseButton: true,
                    didOpen: () => {
                        document.getElementById('btn-close-now').onclick = () => { Swal.close(); this.saveStatus(true, null, null); };
                        document.getElementById('btn-save').onclick = () => {
                            const c = document.getElementById('swal-close').value;
                            if (!c) { Swal.showValidationMessage('Isi waktu penutupan!'); return; }
                            Swal.close(); this.saveStatus(false, c, document.getElementById('swal-next').value);
                        };
                    }
                });
            },
            async saveStatus(is_closed, closing_at, next_opening_at) {
                this.loading = true;
                try {
                    await fetch('{{ route('admin.gelombang.toggle') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ is_closed, closing_at, next_opening_at })
                    });
                    this.isClosed = is_closed; this.closingAt = closing_at; this.nextOpeningAt = next_opening_at;
                    Swal.fire({ icon: 'success', title: 'Berhasil', timer: 1500, showConfirmButton: false });
                } catch (e) { console.error(e); } finally { this.loading = false; }
            }
        }" 
        class="group relative bg-white dark:bg-slate-800 rounded-3xl p-6 md:p-8 shadow-lg hover:shadow-2xl transition-all duration-500 border border-slate-100 dark:border-slate-700 hover:-translate-y-2 overflow-hidden flex flex-col">
            <!-- Glow effect on hover -->
            <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-indigo-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
            
            <div class="relative z-10 flex flex-col h-full">
                <div class="flex items-start justify-between mb-6">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center flex-shrink-0 transition-all duration-500 shadow-md"
                             :class="isClosed ? 'bg-gradient-to-br from-orange-400 to-red-500 text-white shadow-orange-500/40' : (closingAt ? 'bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-blue-500/40' : 'bg-slate-50 dark:bg-slate-700 text-slate-500 dark:text-slate-400 group-hover:bg-blue-50 dark:group-hover:bg-slate-600')">
                            <template x-if="loading"><i class="fas fa-circle-notch fa-spin text-2xl"></i></template>
                            <template x-if="!loading"><i class="fas fa-calendar-alt text-2xl"></i></template>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-1">Jadwal Gelombang</h3>
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider transition-colors"
                                 :class="isClosed ? 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400' : (closingAt ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400')">
                                <span class="w-1.5 h-1.5 rounded-full" :class="isClosed ? 'bg-orange-500' : 'bg-emerald-500 animate-pulse'"></span>
                                <span x-text="isClosed ? 'Pendaftaran Ditutup' : (closingAt ? 'Terjadwal Otomatis' : 'Pendaftaran Dibuka')"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed flex-grow mb-8">
                    Atur kapan gelombang pendaftaran dibuka dan ditutup. Sistem dapat mengelola jadwal penutupan otomatis sesuai tanggal yang ditentukan.
                </p>

                <button @click="openMenu()" :disabled="loading" 
                        class="w-full relative overflow-hidden group/btn px-6 py-4 rounded-2xl font-semibold text-sm transition-all duration-300 shadow-sm"
                        :class="isClosed 
                            ? 'bg-orange-50 text-orange-600 hover:bg-orange-100 hover:shadow-md hover:shadow-orange-500/20 dark:bg-orange-500/10 dark:text-orange-400 dark:hover:bg-orange-500/20' 
                            : 'bg-slate-900 text-white hover:bg-slate-800 hover:shadow-xl hover:shadow-slate-900/20 dark:bg-indigo-600 dark:hover:bg-indigo-500'">
                    <div class="relative z-10 flex items-center justify-center gap-2">
                        <span x-text="isClosed ? 'Buka Pendaftaran' : 'Kelola Gelombang'"></span>
                        <i class="fas fa-arrow-right transition-transform group-hover/btn:translate-x-1"></i>
                    </div>
                </button>
            </div>
        </div>

        {{-- CARD 3: S2 PASCASARJANA --}}
        <div x-data="{ 
            isActive: false, loading: false,
            init() {
                this.check();
                setInterval(() => { this.check(); }, 5000); 
            },
            async check() {
                try {
                    let resp = await fetch('{{ route('admin.settings.s2.status') }}', { headers: { 'Accept': 'application/json' } });
                    let data = await resp.json();
                    this.isActive = data.active;
                } catch (e) { console.error(e); }
            },
            async toggle() {
                const { isConfirmed } = await Swal.fire({
                    title: '<span style=\'font-size: 15px; font-weight: 700\'>🎓 Status S2 Pascasarjana</span>',
                    html: `<p style='font-size: 13px; text-align: left;'>Apakah Anda yakin ingin <b>${this.isActive ? 'menonaktifkan' : 'mengaktifkan'}</b> tombol pendaftaran S2 Pascasarjana?</p>`,
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Lanjutkan',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: this.isActive ? '#ef4444' : '#10b981',
                });
                
                if (isConfirmed) {
                    this.loading = true;
                    try {
                        await fetch('{{ route('admin.settings.s2.toggle') }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ active: !this.isActive })
                        });
                        this.isActive = !this.isActive;
                        Swal.fire({ icon: 'success', title: 'Berhasil diperbarui!', timer: 1500, showConfirmButton: false });
                    } catch (e) { console.error(e); } finally { this.loading = false; }
                }
            }
        }" 
        class="group relative bg-white dark:bg-slate-800 rounded-3xl p-6 md:p-8 shadow-lg hover:shadow-2xl transition-all duration-500 border border-slate-100 dark:border-slate-700 hover:-translate-y-2 overflow-hidden flex flex-col">
            <!-- Glow effect on hover -->
            <div class="absolute inset-0 bg-gradient-to-br from-fuchsia-500/5 to-purple-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
            
            <div class="relative z-10 flex flex-col h-full">
                <div class="flex items-start justify-between mb-6">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center flex-shrink-0 transition-all duration-500 shadow-md"
                             :class="!isActive ? 'bg-gradient-to-br from-slate-400 to-slate-500 text-white shadow-slate-500/40' : 'bg-gradient-to-br from-fuchsia-500 to-purple-600 text-white shadow-purple-500/40'">
                            <template x-if="loading"><i class="fas fa-circle-notch fa-spin text-2xl"></i></template>
                            <template x-if="!loading"><i class="fas fa-graduation-cap text-2xl"></i></template>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-1">Pendaftaran S2</h3>
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider transition-colors"
                                 :class="!isActive ? 'bg-slate-100 text-slate-700 dark:bg-slate-900/30 dark:text-slate-400' : 'bg-fuchsia-100 text-fuchsia-700 dark:bg-fuchsia-900/30 dark:text-fuchsia-400'">
                                <span class="w-1.5 h-1.5 rounded-full" :class="!isActive ? 'bg-slate-500' : 'bg-fuchsia-500 animate-pulse'"></span>
                                <span x-text="!isActive ? 'Tombol Dinonaktifkan' : 'Tombol Aktif'"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed flex-grow mb-8">
                    Gunakan pengaturan ini untuk mematikan atau mengaktifkan tombol pendaftaran S2 Pascasarjana pada halaman registrasi utama.
                </p>

                <button @click="toggle()" :disabled="loading" 
                        class="w-full relative overflow-hidden group/btn px-6 py-4 rounded-2xl font-semibold text-sm transition-all duration-300 shadow-sm"
                        :class="!isActive 
                            ? 'bg-slate-900 text-white hover:bg-slate-800 hover:shadow-xl hover:shadow-slate-900/20 dark:bg-indigo-600 dark:hover:bg-indigo-500' 
                            : 'bg-fuchsia-50 text-fuchsia-600 hover:bg-fuchsia-100 hover:shadow-md hover:shadow-fuchsia-500/20 dark:bg-fuchsia-500/10 dark:text-fuchsia-400 dark:hover:bg-fuchsia-500/20'">
                    <div class="relative z-10 flex items-center justify-center gap-2">
                        <span x-text="isActive ? 'Nonaktifkan S2' : 'Aktifkan S2'"></span>
                        <i class="fas fa-power-off transition-transform group-hover/btn:scale-110"></i>
                    </div>
                </button>
            </div>
        </div>
        {{-- CARD 4: PROFESI APOTEKER --}}
        <div x-data="{ 
            isActive: false, loading: false,
            init() {
                this.check();
                setInterval(() => { this.check(); }, 5000); 
            },
            async check() {
                try {
                    let resp = await fetch('{{ route('admin.settings.apoteker.status') }}', { headers: { 'Accept': 'application/json' } });
                    let data = await resp.json();
                    this.isActive = data.active;
                } catch (e) { console.error(e); }
            },
            async toggle() {
                const { isConfirmed } = await Swal.fire({
                    title: '<span style=\'font-size: 15px; font-weight: 700\'>💊 Status Profesi Apoteker</span>',
                    html: `<p style='font-size: 13px; text-align: left;'>Apakah Anda yakin ingin <b>${this.isActive ? 'menonaktifkan' : 'mengaktifkan'}</b> tombol pendaftaran Profesi Apoteker?</p>`,
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Lanjutkan',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: this.isActive ? '#ef4444' : '#10b981',
                });
                
                if (isConfirmed) {
                    this.loading = true;
                    try {
                        await fetch('{{ route('admin.settings.apoteker.toggle') }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ active: !this.isActive })
                        });
                        this.isActive = !this.isActive;
                        Swal.fire({ icon: 'success', title: 'Berhasil diperbarui!', timer: 1500, showConfirmButton: false });
                    } catch (e) { console.error(e); } finally { this.loading = false; }
                }
            }
        }" 
        class="group relative bg-white dark:bg-slate-800 rounded-3xl p-6 md:p-8 shadow-lg hover:shadow-2xl transition-all duration-500 border border-slate-100 dark:border-slate-700 hover:-translate-y-2 overflow-hidden flex flex-col">
            <!-- Glow effect on hover -->
            <div class="absolute inset-0 bg-gradient-to-br from-teal-500/5 to-emerald-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
            
            <div class="relative z-10 flex flex-col h-full">
                <div class="flex items-start justify-between mb-6">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center flex-shrink-0 transition-all duration-500 shadow-md"
                             :class="!isActive ? 'bg-gradient-to-br from-slate-400 to-slate-500 text-white shadow-slate-500/40' : 'bg-gradient-to-br from-teal-500 to-emerald-600 text-white shadow-emerald-500/40'">
                            <template x-if="loading"><i class="fas fa-circle-notch fa-spin text-2xl"></i></template>
                            <template x-if="!loading"><i class="fas fa-pills text-2xl"></i></template>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-1">Profesi Apoteker</h3>
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider transition-colors"
                                 :class="!isActive ? 'bg-slate-100 text-slate-700 dark:bg-slate-900/30 dark:text-slate-400' : 'bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-400'">
                                <span class="w-1.5 h-1.5 rounded-full" :class="!isActive ? 'bg-slate-500' : 'bg-teal-500 animate-pulse'"></span>
                                <span x-text="!isActive ? 'Tombol Dinonaktifkan' : 'Tombol Aktif'"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed flex-grow mb-8">
                    Gunakan pengaturan ini untuk mematikan atau mengaktifkan tombol pendaftaran Profesi Apoteker pada halaman registrasi utama.
                </p>

                <button @click="toggle()" :disabled="loading" 
                        class="w-full relative overflow-hidden group/btn px-6 py-4 rounded-2xl font-semibold text-sm transition-all duration-300 shadow-sm"
                        :class="!isActive 
                            ? 'bg-slate-900 text-white hover:bg-slate-800 hover:shadow-xl hover:shadow-slate-900/20 dark:bg-indigo-600 dark:hover:bg-indigo-500' 
                            : 'bg-teal-50 text-teal-600 hover:bg-teal-100 hover:shadow-md hover:shadow-teal-500/20 dark:bg-teal-500/10 dark:text-teal-400 dark:hover:bg-teal-500/20'">
                    <div class="relative z-10 flex items-center justify-center gap-2">
                        <span x-text="isActive ? 'Nonaktifkan Apoteker' : 'Aktifkan Apoteker'"></span>
                        <i class="fas fa-power-off transition-transform group-hover/btn:scale-110"></i>
                    </div>
                </button>
            </div>
        </div>

    </div>
</div>
@endsection
