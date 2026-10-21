<template>
    <div class="max-w-6xl mx-auto pb-20 px-3 sm:px-4 font-sans text-slate-800">

        <!-- ======================================================== -->
        <!-- 1. TOP HEADER & QUICK ACTIONS (RESPONSIVE)               -->
        <!-- ======================================================== -->
        <header class="bg-white/85 backdrop-blur-md rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm mb-4 sm:mb-6 transition-all">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                
                <!-- Title & Ref -->
                <div class="flex items-center gap-3 w-full md:w-auto">
                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-tr from-blue-600 via-indigo-600 to-indigo-700 text-white flex items-center justify-center shadow-md shadow-blue-500/20 shrink-0">
                        <i class="fas fa-file-contract text-lg sm:text-xl"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-base sm:text-xl font-black text-slate-800 tracking-tight uppercase truncate">
                                Admission Manifest
                            </h1>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold font-mono uppercase bg-blue-50 text-blue-700 border border-blue-200/60">
                                #{{ ereg.trx_id || 'PENDING' }}
                            </span>
                        </div>
                        <p class="text-[11px] sm:text-xs text-slate-500 font-medium truncate">
                            Ringkasan resmi berkas & status seleksi penerimaan PMB
                        </p>
                    </div>
                </div>

                <!-- Action Buttons: 2x2 Grid on Mobile, Flex on Desktop -->
                <div class="grid grid-cols-2 sm:flex sm:flex-wrap items-center gap-2 w-full md:w-auto">
                    <!-- Tombol Dashboard -->
                    <a :href="routes.dashboard" 
                       class="inline-flex items-center justify-center gap-1.5 px-3 py-2.5 sm:py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition active:scale-95">
                        <i class="fas fa-arrow-left text-[11px]"></i>
                        <span>Dashboard</span>
                    </a>

                    <!-- Tombol Pilih Almamater (Muncul jika Lulus) -->
                    <button v-if="status.isLulus" 
                            type="button"
                            @click="openAlmetModal"
                            class="inline-flex items-center justify-center gap-1.5 px-3 py-2.5 sm:py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-sm shadow-indigo-500/20 transition active:scale-95 truncate">
                        <i class="fas fa-tshirt"></i>
                        <span class="truncate">{{ selectedAlmet ? 'Almet: ' + selectedAlmet : 'Pilih Almet' }}</span>
                        <span v-if="!selectedAlmet" class="w-2 h-2 rounded-full bg-yellow-300 animate-ping"></span>
                    </button>

                    <!-- Tombol Surat Lulus -->
                    <a v-if="status.isLulus" 
                       :href="routes.suratDownload" 
                       target="_blank" 
                       class="inline-flex items-center justify-center gap-1.5 px-3 py-2.5 sm:py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm shadow-emerald-500/20 transition active:scale-95 truncate">
                        <i class="fas fa-certificate"></i>
                        <span>Surat Lulus</span>
                    </a>
                    <button v-else 
                            disabled 
                            class="inline-flex items-center justify-center gap-1.5 px-3 py-2.5 sm:py-2 rounded-xl bg-slate-100 text-slate-400 text-xs font-bold cursor-not-allowed border border-slate-200/60 truncate">
                        <i class="fas fa-certificate"></i>
                        <span>Surat Lulus</span>
                    </button>

                    <!-- Tombol Cetak Bukti Daftar -->
                    <a :href="routes.print" 
                       target="_blank" 
                       class="inline-flex items-center justify-center gap-1.5 px-3 py-2.5 sm:py-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-xs font-bold shadow-sm shadow-blue-500/20 transition active:scale-95 truncate">
                        <i class="fas fa-print"></i>
                        <span>Cetak PDF</span>
                    </a>
                </div>

            </div>
        </header>

        <!-- ======================================================== -->
        <!-- 2. HERO STATUS BANNER (MOBILE FRIENDLY)                   -->
        <!-- ======================================================== -->
        <!-- Banner Jika LULUS -->
        <div v-if="status.isLulus" 
             class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-600 via-teal-600 to-indigo-700 p-4 sm:p-6 text-white shadow-lg mb-4 sm:mb-6">
            <div class="absolute -right-8 -bottom-10 opacity-10 pointer-events-none transform rotate-12">
                <i class="fas fa-graduation-cap text-8xl sm:text-9xl"></i>
            </div>
            <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-3 sm:gap-4">
                <div class="flex items-start gap-3 sm:gap-4">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center shrink-0 text-xl sm:text-2xl shadow-inner">
                        <i class="fas fa-award text-yellow-300 animate-bounce"></i>
                    </div>
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-white/20 text-[9px] sm:text-[10px] font-black uppercase tracking-wider mb-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-300 animate-pulse"></span>
                            RESMI DITERIMA
                        </div>
                        <h2 class="text-base sm:text-xl font-black leading-tight">
                            Selamat! Anda Dinyatakan LULUS Seleksi PMB
                        </h2>
                        <p class="text-xs sm:text-sm text-emerald-100 mt-0.5">
                            Program Studi: 
                            <strong class="text-white underline decoration-yellow-400 decoration-2 underline-offset-4">
                                {{ status.jurusanDiterima || ereg.jurusan1_nama || '-' }}
                            </strong>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 self-start md:self-auto">
                    <span class="px-2.5 py-1 rounded-lg bg-white/10 backdrop-blur-md border border-white/20 text-[11px] sm:text-xs font-bold text-emerald-100">
                        {{ ereg.gelombang || 'Gelombang PMB 2026' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Banner Jika GAGAL -->
        <div v-else-if="status.isGagal" 
             class="rounded-2xl bg-gradient-to-r from-rose-600 to-red-700 p-4 sm:p-6 text-white shadow-md mb-4 sm:mb-6 flex items-center gap-3 sm:gap-4">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-white/20 flex items-center justify-center shrink-0 text-xl sm:text-2xl">
                <i class="fas fa-times-circle text-rose-200"></i>
            </div>
            <div>
                <span class="text-[9px] font-black uppercase tracking-wider bg-white/20 px-2 py-0.5 rounded">Status Seleksi</span>
                <h2 class="text-sm sm:text-lg font-bold mt-0.5">Belum Memenuhi Syarat Kelulusan</h2>
                <p class="text-xs text-rose-100">Silakan hubungi panitia PMB untuk informasi gelombang pendaftaran berikutnya.</p>
            </div>
        </div>

        <!-- Banner Jika MENUNGGU / BELUM UJIAN -->
        <div v-else 
             class="rounded-2xl bg-gradient-to-r from-blue-500 via-indigo-600 to-indigo-700 p-4 sm:p-5 text-white shadow-sm mb-4 sm:mb-6 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                    <i class="fas fa-hourglass-half text-white"></i>
                </div>
                <div>
                    <span class="text-[9px] font-bold uppercase tracking-wider bg-white/20 px-2 py-0.5 rounded">Status Tahapan</span>
                    <h3 class="text-xs sm:text-base font-bold mt-0.5">Tahap Seleksi: {{ status.statusText }}</h3>
                    <p class="text-[11px] sm:text-xs text-blue-100">Pantau secara berkala hasil seleksi Anda di portal ini.</p>
                </div>
            </div>
            <a :href="routes.dashboard" class="px-3 py-1.5 rounded-lg bg-white/20 hover:bg-white/30 text-xs font-bold transition shrink-0">
                Cek Progres
            </a>
        </div>

        <!-- ======================================================== -->
        <!-- 3. MOBILE SEGMENTED TABS (HANYA MUNCUL DI LAYAR HP/MOBILE)-->
        <!-- ======================================================== -->
        <div class="lg:hidden mb-4 bg-slate-100 p-1 rounded-xl border border-slate-200/80 grid grid-cols-3 gap-1">
            <button type="button" 
                    @click="mobileTab = 'overview'" 
                    class="py-2.5 px-1 rounded-lg text-xs font-bold transition flex flex-col sm:flex-row items-center justify-center gap-1"
                    :class="mobileTab === 'overview' ? 'bg-white text-blue-700 shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                <i class="fas fa-list-check text-xs"></i>
                <span>Data Admisi</span>
            </button>
            <button type="button" 
                    @click="mobileTab = 'billing'" 
                    class="py-2.5 px-1 rounded-lg text-xs font-bold transition flex flex-col sm:flex-row items-center justify-center gap-1"
                    :class="mobileTab === 'billing' ? 'bg-white text-indigo-700 shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                <i class="fas fa-credit-card text-xs"></i>
                <span>Pembayaran</span>
            </button>
            <button type="button" 
                    @click="mobileTab = 'idcard'" 
                    class="py-2.5 px-1 rounded-lg text-xs font-bold transition flex flex-col sm:flex-row items-center justify-center gap-1"
                    :class="mobileTab === 'idcard' ? 'bg-white text-purple-700 shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                <i class="fas fa-id-card text-xs"></i>
                <span>ID Mahasiswa</span>
            </button>
        </div>

        <!-- ======================================================== -->
        <!-- 4. MAIN LAYOUT: DESKTOP 2-COL / MOBILE ADAPTIVE          -->
        <!-- ======================================================== -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <!-- ==================================================== -->
            <!-- KOLOM KIRI: STUDENT ID PASS                          -->
            <!-- (Tampil selalu di desktop, tampil di mobile jika tab idcard) -->
            <!-- ==================================================== -->
            <div class="lg:col-span-4 space-y-4"
                 :class="{ 'hidden lg:block': mobileTab !== 'idcard' }">
                
                <!-- Digital Student Pass Card -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6 text-center relative overflow-hidden group">
                    <!-- Decorative Gradient Top Bar -->
                    <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600"></div>

                    <!-- Watermark Stamp Acceptance -->
                    <div v-if="status.isLulus" 
                         class="absolute -top-1 -right-2 transform rotate-12 border-2 border-emerald-500/30 text-emerald-600/40 px-2 py-0.5 text-[10px] font-black uppercase tracking-widest pointer-events-none rounded">
                        ACCEPTED
                    </div>

                    <!-- Foto Profil Mahasiswa -->
                    <div class="relative w-28 h-28 sm:w-32 sm:h-32 mx-auto my-3">
                        <div class="w-full h-full rounded-2xl overflow-hidden border-4 border-white shadow-md ring-2 ring-slate-100 bg-slate-100 flex items-center justify-center">
                            <img v-if="user.photo" 
                                 :src="user.photo" 
                                 :alt="user.name" 
                                 class="w-full h-full object-cover">
                            <div v-else class="text-3xl sm:text-4xl font-black text-slate-300">
                                {{ (user.name || 'M').charAt(0).toUpperCase() }}
                            </div>
                        </div>
                        <span v-if="status.isLulus" 
                              class="absolute -bottom-1 -right-1 w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-emerald-500 text-white flex items-center justify-center text-xs shadow-md"
                              title="Telah Terverifikasi">
                            <i class="fas fa-check"></i>
                        </span>
                    </div>

                    <!-- Nama & Kontak Mahasiswa -->
                    <h3 class="text-base sm:text-lg font-black text-slate-800 leading-snug">
                        {{ dataFinal.nama_mahasiswa || biodata.nama_lengkap || user.name }}
                    </h3>
                    <p class="text-xs font-mono text-blue-600 font-bold mt-0.5 truncate" :title="user.email">
                        {{ user.email }}
                    </p>
                    <p class="text-xs text-slate-500 font-mono mt-0.5">
                        {{ dataFinal.handphone || ereg.phone || '-' }}
                    </p>

                    <!-- Divider -->
                    <div class="my-3.5 border-t border-slate-100"></div>

                    <!-- Badges Info Mahasiswa -->
                    <div class="grid grid-cols-2 gap-2 text-left mb-3.5">
                        <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Jalur</span>
                            <span class="text-xs font-bold text-slate-700 truncate block">
                                {{ ereg.jalur_pendaftaran || 'Reguler' }}
                            </span>
                        </div>
                        <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Gelombang</span>
                            <span class="text-xs font-bold text-slate-700 truncate block">
                                {{ ereg.gelombang || 'Gelombang 1' }}
                            </span>
                        </div>
                    </div>

                    <!-- Status Almamater Card (Widget Mini) -->
                    <div class="bg-indigo-50/70 border border-indigo-100 rounded-xl p-3 text-left flex items-center justify-between gap-3 mb-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-sm shadow-xs shrink-0">
                                <i class="fas fa-tshirt"></i>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-indigo-900 uppercase block">Jas Almamater</span>
                                <span class="text-xs font-black text-indigo-700">
                                    {{ selectedAlmet ? 'Ukuran ' + selectedAlmet : 'Belum Dipilih' }}
                                </span>
                            </div>
                        </div>
                        <button type="button" 
                                @click="openAlmetModal"
                                class="text-xs font-bold text-indigo-600 hover:text-indigo-800 underline underline-offset-2 py-1 px-1.5 active:scale-95">
                            {{ selectedAlmet ? 'Ubah' : 'Pilih' }}
                        </button>
                    </div>

                    <!-- Authorization Stamp Box -->
                    <div class="pt-2 border-t border-slate-100 text-center">
                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Disahkan Oleh</span>
                        <div class="h-12 sm:h-14 flex items-center justify-center mb-1">
                            <img :src="assets.logoUrl" alt="UBP Karawang" class="h-full object-contain opacity-90 drop-shadow-xs">
                        </div>
                        <p class="text-xs font-black text-slate-800">Panitia PMB UBP Karawang</p>
                        <p class="text-[10px] text-slate-400 uppercase font-mono">Tahun Akademik 2026/2027</p>
                        <div class="mt-2 inline-flex items-center gap-1 text-[10px] font-mono font-bold text-blue-700 bg-blue-50/80 px-2.5 py-1 rounded-md border border-blue-100">
                            <i class="fas fa-shield-alt text-blue-500"></i>
                            <span>Verified: {{ currentDate }}</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ==================================================== -->
            <!-- KOLOM KANAN: STATUS PIPELINE, RINCIAN & VA BILLING    -->
            <!-- ==================================================== -->
            <div class="lg:col-span-8 space-y-4 sm:space-y-6">

                <!-- 1. 4-Pillar Pipeline Status (Tampil di Overview atau Desktop) -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 sm:p-5"
                     :class="{ 'hidden lg:block': mobileTab !== 'overview' }">
                    <h4 class="text-xs font-black text-slate-400 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                        <i class="fas fa-tasks text-blue-600"></i>
                        <span>Status Tahapan Admisi</span>
                    </h4>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 sm:gap-3">
                        <!-- 1. Biodata -->
                        <div class="p-2.5 sm:p-3 rounded-xl border transition-all"
                             :class="biodata.isCompleted ? 'bg-emerald-50/70 border-emerald-200' : 'bg-slate-50 border-slate-200'">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[9px] sm:text-[10px] font-bold uppercase" :class="biodata.isCompleted ? 'text-emerald-700' : 'text-slate-500'">Biodata</span>
                                <i :class="biodata.isCompleted ? 'fas fa-check-circle text-emerald-600 text-xs' : 'fas fa-clock text-slate-400 text-xs'"></i>
                            </div>
                            <span class="text-xs font-black" :class="biodata.isCompleted ? 'text-emerald-800' : 'text-slate-600'">
                                {{ biodata.isCompleted ? 'Lengkap' : 'Belum' }}
                            </span>
                        </div>

                        <!-- 2. Seleksi Ujian -->
                        <div class="p-2.5 sm:p-3 rounded-xl border transition-all"
                             :class="status.isLulus ? 'bg-emerald-50/70 border-emerald-200' : (status.isGagal ? 'bg-rose-50/70 border-rose-200' : 'bg-amber-50/70 border-amber-200')">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[9px] sm:text-[10px] font-bold uppercase" 
                                      :class="status.isLulus ? 'text-emerald-700' : (status.isGagal ? 'text-rose-700' : 'text-amber-700')">
                                    Seleksi
                                </span>
                                <i :class="status.isLulus ? 'fas fa-check-circle text-emerald-600 text-xs' : (status.isGagal ? 'fas fa-times-circle text-rose-600 text-xs' : 'fas fa-hourglass-half text-amber-500 text-xs')"></i>
                            </div>
                            <span class="text-xs font-black truncate block" 
                                  :class="status.isLulus ? 'text-emerald-800' : (status.isGagal ? 'text-rose-800' : 'text-amber-800')">
                                {{ status.statusText }}
                            </span>
                        </div>

                        <!-- 3. Keuangan -->
                        <div class="p-2.5 sm:p-3 rounded-xl border transition-all"
                             :class="billing.isLunas ? 'bg-emerald-50/70 border-emerald-200' : 'bg-orange-50/70 border-orange-200'">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[9px] sm:text-[10px] font-bold uppercase" :class="billing.isLunas ? 'text-emerald-700' : 'text-orange-700'">Keuangan</span>
                                <i :class="billing.isLunas ? 'fas fa-check-circle text-emerald-600 text-xs' : 'fas fa-receipt text-orange-500 text-xs'"></i>
                            </div>
                            <span class="text-xs font-black" :class="billing.isLunas ? 'text-emerald-800' : 'text-orange-800'">
                                {{ billing.isLunas ? 'Lunas' : 'Ada Tagihan' }}
                            </span>
                        </div>

                        <!-- 4. PDDIKTI / Final -->
                        <div class="p-2.5 sm:p-3 rounded-xl border transition-all"
                             :class="dataFinal.isReady ? 'bg-purple-50/70 border-purple-200' : 'bg-slate-50 border-slate-200'">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[9px] sm:text-[10px] font-bold uppercase" :class="dataFinal.isReady ? 'text-purple-700' : 'text-slate-500'">PDDIKTI</span>
                                <i :class="dataFinal.isReady ? 'fas fa-check-circle text-purple-600 text-xs' : 'fas fa-minus-circle text-slate-400 text-xs'"></i>
                            </div>
                            <span class="text-xs font-black" :class="dataFinal.isReady ? 'text-purple-800' : 'text-slate-600'">
                                {{ dataFinal.isReady ? 'Sinkron' : 'Belum' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- 2. Rincian Data Mahasiswa (Tampil di Overview atau Desktop) -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 sm:p-6 space-y-5 sm:space-y-6"
                     :class="{ 'hidden lg:block': mobileTab !== 'overview' }">

                    <!-- Bagian A: Program Studi & Kelulusan -->
                    <div>
                        <div class="flex items-center gap-2 mb-3 pb-2 border-b border-slate-100">
                            <i class="fas fa-graduation-cap text-indigo-600 text-sm"></i>
                            <h4 class="text-xs font-black text-slate-700 uppercase tracking-wider">
                                Program Studi & Pendaftaran
                            </h4>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                            <div class="bg-slate-50 p-3 sm:p-3.5 rounded-xl border border-slate-100">
                                <span class="text-[9px] sm:text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Status Kelulusan Prodi</span>
                                <div v-if="status.isLulus && status.jurusanDiterima" class="flex flex-wrap items-center gap-2">
                                    <span class="text-xs sm:text-sm font-black text-emerald-700">
                                        {{ status.jurusanDiterima }}
                                    </span>
                                    <span class="text-[9px] font-black bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded uppercase">Diterima</span>
                                </div>
                                <div v-else class="space-y-1">
                                    <p class="text-xs text-slate-700 font-bold">1. {{ ereg.jurusan1_nama || '-' }}</p>
                                    <p class="text-xs text-slate-500">2. {{ ereg.jurusan2_nama || '-' }}</p>
                                </div>
                            </div>
                            <div class="bg-slate-50 p-3 sm:p-3.5 rounded-xl border border-slate-100">
                                <span class="text-[9px] sm:text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Gelombang Pendaftaran</span>
                                <span class="text-xs font-black text-slate-700 block">
                                    {{ ereg.gelombang || 'Gelombang Tidak Diketahui' }}
                                </span>
                                <span class="text-[10px] text-slate-400 mt-0.5 block">
                                    Jalur Masuk: {{ ereg.jalur_pendaftaran || 'Reguler' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Bagian B: Data Pribadi & Pendidikan -->
                    <div>
                        <div class="flex items-center gap-2 mb-3 pb-2 border-b border-slate-100">
                            <i class="fas fa-user text-blue-600 text-sm"></i>
                            <h4 class="text-xs font-black text-slate-700 uppercase tracking-wider">
                                Data Pribadi & Asal Sekolah
                            </h4>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                            <div class="space-y-2.5">
                                <div>
                                    <span class="text-[9px] sm:text-[10px] font-bold text-slate-400 uppercase tracking-wider block">NIK (KTP)</span>
                                    <span class="text-xs font-mono font-bold text-slate-800">
                                        {{ dataFinal.nik || biodata.nik || '-' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-[9px] sm:text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Tempat, Tanggal Lahir</span>
                                    <span class="text-xs font-bold text-slate-800">
                                        {{ dataFinal.tempat_lahir || biodata.tempat_lahir || '-' }}, 
                                        {{ dataFinal.tanggal_lahir || biodata.tanggal_lahir || '-' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-[9px] sm:text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Alamat Domisili</span>
                                    <span class="text-xs text-slate-700 leading-relaxed block">
                                        {{ formattedAddress || '-' }}
                                    </span>
                                </div>
                            </div>

                            <div class="space-y-2.5 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                                <div>
                                    <span class="text-[9px] sm:text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Asal Sekolah</span>
                                    <span class="text-xs font-bold text-slate-800">
                                        {{ biodata.asal_sekolah || '-' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-[9px] sm:text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Tahun Lulus</span>
                                    <span class="text-xs font-mono font-bold text-slate-800">
                                        {{ biodata.tahun_lulus || '-' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bagian C: Data Orang Tua -->
                    <div>
                        <div class="flex items-center gap-2 mb-3 pb-2 border-b border-slate-100">
                            <i class="fas fa-users text-purple-600 text-sm"></i>
                            <h4 class="text-xs font-black text-slate-700 uppercase tracking-wider">
                                Data Orang Tua / Wali
                            </h4>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 sm:gap-3">
                            <div class="bg-slate-50 p-2.5 sm:p-3 rounded-xl border border-slate-100">
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Nama Ayah</span>
                                <span class="text-xs font-bold text-slate-800 truncate block mt-0.5">
                                    {{ biodata.nama_ayah || '-' }}
                                </span>
                            </div>
                            <div class="bg-slate-50 p-2.5 sm:p-3 rounded-xl border border-slate-100">
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Nama Ibu</span>
                                <span class="text-xs font-bold text-slate-800 truncate block mt-0.5">
                                    {{ biodata.nama_ibu || '-' }}
                                </span>
                            </div>
                            <div class="bg-slate-50 p-2.5 sm:p-3 rounded-xl border border-slate-100">
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Kontak Orang Tua</span>
                                <span class="text-xs font-mono font-bold text-slate-800 truncate block mt-0.5">
                                    {{ biodata.hp_ortu || '-' }}
                                </span>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- 3. Smart Virtual Account Card (Tampil di Billing atau Desktop) -->
                <div v-if="billing.virtual_account" 
                     class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-indigo-950 to-blue-950 text-white p-5 sm:p-6 shadow-md border border-slate-800"
                     :class="{ 'hidden lg:block': mobileTab !== 'billing' }">
                    <div class="absolute top-0 right-0 p-8 opacity-10 pointer-events-none">
                        <i class="fas fa-credit-card text-8xl text-white"></i>
                    </div>

                    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4 sm:gap-5">
                        <div class="w-full md:w-auto">
                            <div class="flex items-center gap-2 mb-1.5">
                                <span class="text-[9px] font-black tracking-widest text-blue-300 uppercase bg-blue-500/20 px-2 py-0.5 rounded border border-blue-400/30">
                                    Payment Virtual Account
                                </span>
                                <span class="text-[10px] text-slate-400 font-mono">Bank Rekomendasi</span>
                            </div>

                            <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3 my-2">
                                <span class="text-xl sm:text-2xl font-mono font-black tracking-wider text-white select-all">
                                    {{ billing.virtual_account }}
                                </span>
                                <!-- Tombol Salin VA (Lebar Penuh di HP agar Gampang Dipencet Jempol) -->
                                <button type="button" 
                                        @click="copyVA(billing.virtual_account)"
                                        class="w-full sm:w-auto justify-center px-4 py-2 sm:py-1 rounded-xl sm:rounded-lg bg-blue-600/30 hover:bg-blue-600/50 text-blue-200 hover:text-white text-xs font-bold transition flex items-center gap-2 border border-blue-400/30 active:scale-95">
                                    <i :class="copiedVA ? 'fas fa-check text-emerald-400' : 'fas fa-copy'"></i>
                                    <span>{{ copiedVA ? 'Nomor VA Tersalin!' : 'Salin Nomor VA' }}</span>
                                </button>
                            </div>
                            <p class="text-[11px] text-slate-400">
                                Dapat dibayarkan melalui ATM, Mobile Banking (Semua Bank), atau Teller.
                            </p>
                        </div>

                        <div class="flex flex-col md:items-end justify-center pt-3 md:pt-0 border-t md:border-t-0 border-white/10">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">
                                Total Tagihan & Status
                            </span>
                            <div class="flex items-center justify-between md:justify-end gap-3 w-full md:w-auto">
                                <span class="text-lg sm:text-xl font-mono font-black text-white">
                                    Rp {{ formatRupiah(billing.amount) }}
                                </span>

                                <span v-if="billing.isLunas" 
                                      class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-emerald-500/20 border border-emerald-400 text-emerald-300 text-xs font-black uppercase">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Lunas</span>
                                </span>
                                <span v-else 
                                      class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-amber-500/20 border border-amber-400 text-amber-300 text-xs font-black uppercase animate-pulse">
                                    <i class="fas fa-clock"></i>
                                    <span>Menunggu</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <!-- ======================================================== -->
        <!-- 5. MODAL ALMAMATER (MOBILE BOTTOM-SHEET / DESKTOP MODAL)  -->
        <!-- ======================================================== -->
        <div v-if="showModalAlmet" 
             class="fixed inset-0 z-[9999] flex items-end sm:items-center justify-center p-0 sm:p-4">
            
            <!-- Backdrop Gelap -->
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" 
                 @click="showModalAlmet = false"></div>

            <!-- Modal Content (Bottom Sheet on Mobile, Centered Modal on Desktop) -->
            <div class="bg-white w-full sm:max-w-lg rounded-t-3xl sm:rounded-2xl shadow-2xl relative z-10 overflow-hidden transform transition-all border border-slate-100 flex flex-col max-h-[85vh] sm:max-h-[90vh]">
                
                <!-- Mobile Drag Bar Indicator -->
                <div class="sm:hidden w-full pt-3 pb-1 flex justify-center bg-gradient-to-r from-indigo-700 to-purple-700">
                    <div class="w-12 h-1.5 bg-white/40 rounded-full"></div>
                </div>

                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-indigo-600 via-indigo-700 to-purple-700 px-5 py-4 sm:p-5 text-white text-center relative shrink-0">
                    <button type="button" 
                            @click="showModalAlmet = false" 
                            class="absolute top-3.5 right-4 text-white/70 hover:text-white transition p-1">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                    <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-2 backdrop-blur-md shadow-inner">
                        <i class="fas fa-tshirt text-xl sm:text-2xl"></i>
                    </div>
                    <h3 class="text-base sm:text-lg font-black uppercase tracking-tight">Pilih Ukuran Jas Almamater</h3>
                    <p class="text-[11px] sm:text-xs text-indigo-100">Pastikan memilih ukuran yang nyaman sesuai postur tubuh Anda.</p>
                </div>

                <!-- Modal Sub-Tabs -->
                <div class="flex border-b border-slate-200 bg-slate-50 shrink-0">
                    <button type="button" 
                            @click="almetTab = 'select'" 
                            class="flex-1 py-3 text-xs font-bold text-center border-b-2 transition"
                            :class="almetTab === 'select' ? 'border-indigo-600 text-indigo-700 bg-white' : 'border-transparent text-slate-500 hover:text-slate-800'">
                        <i class="fas fa-check-circle mr-1"></i> Pilih Ukuran
                    </button>
                    <button type="button" 
                            @click="almetTab = 'chart'" 
                            class="flex-1 py-3 text-xs font-bold text-center border-b-2 transition"
                            :class="almetTab === 'chart' ? 'border-indigo-600 text-indigo-700 bg-white' : 'border-transparent text-slate-500 hover:text-slate-800'">
                        <i class="fas fa-ruler-combined mr-1"></i> Panduan Ukuran (Chart)
                    </button>
                </div>

                <!-- Modal Body (Scrollable) -->
                <div class="p-4 sm:p-5 overflow-y-auto flex-1 overscroll-contain">
                    
                    <!-- TAB 1: PILIHAN UKURAN -->
                    <div v-show="almetTab === 'select'">
                        <form :action="routes.simpanAlmet" method="POST" @submit="handleSubmitAlmet">
                            <input type="hidden" name="_token" :value="routes.csrfToken">
                            
                            <!-- Ukuran Grid -->
                            <div class="grid grid-cols-3 sm:grid-cols-4 gap-2 mb-4">
                                <label v-for="size in sizeOptions" 
                                       :key="size" 
                                       class="cursor-pointer group relative">
                                    <input type="radio" 
                                           name="ukuran_almamater" 
                                           :value="size" 
                                           v-model="tempSelectedAlmet"
                                           class="sr-only">
                                    
                                    <div class="py-3 px-2 rounded-xl border-2 text-center transition-all duration-150 active:scale-95"
                                         :class="tempSelectedAlmet === size 
                                            ? 'border-indigo-600 bg-indigo-600 text-white shadow-md font-black' 
                                            : 'border-slate-200 hover:border-indigo-300 hover:bg-indigo-50/50 text-slate-700 font-bold'">
                                        <span class="block text-sm sm:text-base">{{ size }}</span>
                                    </div>
                                </label>
                            </div>

                            <!-- Opsi JUMBO -->
                            <div class="mb-4">
                                <label class="cursor-pointer group block">
                                    <input type="radio" 
                                           name="ukuran_almamater" 
                                           value="JUMBO" 
                                           v-model="tempSelectedAlmet"
                                           class="sr-only">
                                    <div class="py-3 px-3 rounded-xl border-2 text-center transition-all active:scale-95"
                                         :class="tempSelectedAlmet === 'JUMBO' 
                                            ? 'border-indigo-600 bg-indigo-600 text-white shadow-md font-black' 
                                            : 'border-slate-200 hover:border-indigo-300 hover:bg-indigo-50/50 text-slate-700 font-bold'">
                                        <span class="block text-xs sm:text-sm">Ukuran JUMBO (Khusus Berat Badan > 100 kg)</span>
                                    </div>
                                </label>
                            </div>

                            <button type="submit" 
                                    :disabled="!tempSelectedAlmet"
                                    class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white font-bold rounded-xl shadow-md transition transform active:scale-95 text-xs sm:text-sm uppercase tracking-wider flex items-center justify-center gap-2">
                                <i class="fas fa-save"></i>
                                <span>Simpan Ukuran Almamater</span>
                            </button>
                        </form>
                    </div>

                    <!-- TAB 2: SIZE CHART -->
                    <div v-show="almetTab === 'chart'" class="space-y-3">
                        <div class="overflow-x-auto rounded-xl border border-slate-200 -mx-1 sm:mx-0">
                            <table class="w-full text-left text-xs whitespace-nowrap">
                                <thead class="bg-slate-100 text-slate-600 font-bold uppercase text-[10px]">
                                    <tr>
                                        <th class="p-2 sm:p-2.5">Size</th>
                                        <th class="p-2 sm:p-2.5">Lebar Dada</th>
                                        <th class="p-2 sm:p-2.5">Panjang</th>
                                        <th class="p-2 sm:p-2.5">Rekomendasi Postur</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-for="item in sizeGuideTable" :key="item.size" class="hover:bg-slate-50">
                                        <td class="p-2 sm:p-2.5 font-bold text-indigo-700">{{ item.size }}</td>
                                        <td class="p-2 sm:p-2.5 font-mono">{{ item.ld }}</td>
                                        <td class="p-2 sm:p-2.5 font-mono">{{ item.pb }}</td>
                                        <td class="p-2 sm:p-2.5 text-slate-600">{{ item.note }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="text-[10px] sm:text-[11px] text-slate-400 italic">
                            *Toleransi ukuran jahit ± 1-2 cm. Disarankan memilih 1 tingkat lebih longgar untuk kenyamanan bergerak.
                        </p>
                    </div>

                </div>

            </div>

        </div>

    </div>
</template>

<script>
export default {
    name: 'RingkasanApp',
    props: {
        initialData: {
            type: Object,
            default: () => ({})
        }
    },
    data() {
        return {
            user: this.initialData.user || {},
            ereg: this.initialData.ereg || {},
            biodata: this.initialData.biodata || {},
            dataFinal: this.initialData.dataFinal || {},
            billing: this.initialData.billing || {},
            status: this.initialData.status || {},
            routes: this.initialData.routes || {},
            assets: this.initialData.assets || {},
            
            // Tab state untuk mobile view ('overview' | 'billing' | 'idcard')
            mobileTab: 'overview',

            showModalAlmet: false,
            almetTab: 'select',
            selectedAlmet: (this.initialData.ereg && this.initialData.ereg.ukuran_almamater) || '',
            tempSelectedAlmet: (this.initialData.ereg && this.initialData.ereg.ukuran_almamater) || '',
            copiedVA: false,

            sizeOptions: ['S', 'M', 'L', 'XL', 'XXL', 'XXXL', '4XL', '5XL', '6XL'],
            sizeGuideTable: [
                { size: 'S', ld: '48 cm', pb: '66 cm', note: 'TB 150-160 cm, BB 45-55 kg' },
                { size: 'M', ld: '51 cm', pb: '68 cm', note: 'TB 160-168 cm, BB 55-65 kg' },
                { size: 'L', ld: '54 cm', pb: '70 cm', note: 'TB 165-175 cm, BB 65-75 kg' },
                { size: 'XL', ld: '57 cm', pb: '72 cm', note: 'TB 170-180 cm, BB 75-85 kg' },
                { size: 'XXL', ld: '60 cm', pb: '74 cm', note: 'TB 175-185 cm, BB 85-95 kg' },
                { size: '3XL', ld: '63 cm', pb: '76 cm', note: 'TB > 180 cm, BB 95-105 kg' },
                { size: 'JUMBO', ld: '> 66 cm', pb: '> 78 cm', note: 'Khusus BB > 105 kg' }
            ]
        };
    },
    computed: {
        currentDate() {
            const d = new Date();
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
        },
        formattedAddress() {
            if (this.dataFinal && this.dataFinal.jalan) {
                const parts = [
                    this.dataFinal.jalan,
                    this.dataFinal.rt ? `RT ${this.dataFinal.rt}` : '',
                    this.dataFinal.rw ? `RW ${this.dataFinal.rw}` : '',
                    this.dataFinal.kelurahan
                ].filter(Boolean);
                return parts.join(', ');
            }
            return this.biodata.alamat || '-';
        }
    },
    methods: {
        openAlmetModal() {
            this.tempSelectedAlmet = this.selectedAlmet || '';
            this.almetTab = 'select';
            this.showModalAlmet = true;
        },
        formatRupiah(number) {
            if (!number) return '0';
            return new Intl.NumberFormat('id-ID').format(number);
        },
        copyVA(vaNumber) {
            if (!vaNumber) return;
            
            // Haptic feedback jika didukung device mobile
            if (navigator.vibrate) {
                try {
                    navigator.vibrate(50);
                } catch (e) {}
            }

            navigator.clipboard.writeText(vaNumber).then(() => {
                this.copiedVA = true;
                setTimeout(() => {
                    this.copiedVA = false;
                }, 2500);
            });
        },
        handleSubmitAlmet() {
            // Standar POST form submission ke routes.simpanAlmet
        }
    }
};
</script>
