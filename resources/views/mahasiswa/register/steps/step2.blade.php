    @php
        $isUserS2Manajemen = false;
        $masterUser = auth()->user()->masterUser;
        if ($masterUser && floatval($masterUser->va_amount) == 500000) {
            $isUserS2Manajemen = true;
        }

        $initialJalur = $registration->jalur_pendaftaran ?? '';
        if ($isUserS2Manajemen && empty($initialJalur)) {
            $initialJalur = 'Jalur S2 Manajemen';
        }
    @endphp

<div x-data="formStep2({
    jalur: '{{ $initialJalur }}',
    j1: '{{ $registration->jurusan1 ?? '' }}',
    j2: '{{ $registration->jurusan2 ?? '' }}',
    alasan1: `{{ $registration->alasan_jurusan_1 ?? '' }}`,
    alasan2: `{{ $registration->alasan_jurusan_2 ?? '' }}`
})" x-init="initData()" class="animate-fade-in-up relative">

    <!-- Decorative background glow for the card -->
    <div class="absolute -inset-1 bg-gradient-to-r from-blue-100 to-purple-100 rounded-[2.5rem] blur-xl opacity-50 pointer-events-none"></div>

    <div class="relative bg-white/80 backdrop-blur-xl rounded-[2rem] shadow-xl border border-white p-6 sm:p-10">

        {{-- HEADER SECTION --}}
        <div class="flex items-center gap-4 mb-8 pb-6 border-b border-indigo-100/50 relative">
            <!-- Animated Icon Container -->
            <div class="relative h-14 w-14 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-[0_8px_16px_-6px_rgba(79,70,229,0.5)] overflow-hidden group-hover:scale-105 transition-transform">
                <div class="absolute inset-0 bg-white/20 blur-sm translate-y-full group-hover:translate-y-0 transition-transform duration-500"></div>
                <i class="fas fa-university text-xl relative z-10"></i>
            </div>
            <div>
                <h2 class="text-2xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-slate-800 to-indigo-800 tracking-tight">Pilihan Program Studi</h2>
                <p class="text-sm font-medium text-slate-500 mt-0.5">Tentukan masa depan akademis Anda di sini.</p>
            </div>
        </div>

    {{-- ALERT INFO KUOTA --}}
    <div class="relative overflow-hidden bg-blue-50/50 border border-blue-100 rounded-xl p-4 mb-8">
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0 mt-0.5">
                <div class="h-5 w-5 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                    <i class="fas fa-info text-xs"></i>
                </div>
            </div>
            <div class="text-sm text-blue-800/80 leading-relaxed">
                <span class="font-bold text-blue-700">Info Kuota:</span> 
                Program studi dengan label <span class="inline-block px-1.5 py-0.5 rounded bg-red-100 text-red-600 text-[10px] font-bold">PENUH</span> 
                tidak dapat dipilih karena kuota telah habis.
            </div>
        </div>
    </div>
    
    <form @submit.prevent="submitForm">
        @csrf

        {{-- 1. JALUR PENDAFTARAN --}}
        <div class="mb-8" {!! $isUserS2Manajemen ? 'style="display: none;"' : '' !!}>
            <label class="block text-[11px] font-extrabold text-slate-600 uppercase tracking-widest mb-4 ml-1">
                Jalur Pendaftaran <span class="text-pink-500">*</span>
            </label>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
			    @foreach($jalurList as $key => $dataJalur)
			        @php 
			            $namaJalur = is_string($dataJalur) ? $dataJalur : ($dataJalur['nama_jalur'] ?? $dataJalur->nama_jalur ?? 'Jalur Undefined');
			            $kodeJalur = $key;
			            
			            $isS2Jalur = \Illuminate\Support\Str::contains(strtolower($namaJalur), ['s2', 'magister']);
			        @endphp
                    
                    {{-- Sembunyikan pilihan S2 untuk pendaftar S1 --}}
                    @if($isS2Jalur && !$isUserS2Manajemen)
                        @continue
                    @endif
			        
			        <div class="relative group">
			            <input type="radio" 
			                   name="jalur_pendaftaran" 
			                   value="{{ $kodeJalur }}" 
			                   id="jalur_{{ Str::slug($kodeJalur) }}" 
			                   x-model="form.jalur"
			                   @change="filterJurusan()"
			                   class="radio-card-input peer hidden">
			            
			            <label for="jalur_{{ Str::slug($kodeJalur) }}" 
			                   class="radio-card-label block w-full text-left p-4 rounded-xl border border-slate-200 transition-all duration-200 cursor-pointer hover:border-blue-400 hover:bg-white/80 hover:shadow-md peer-checked:border-blue-600 peer-checked:bg-blue-50/90 peer-checked:shadow-lg peer-checked:shadow-blue-500/10">
			                
			                <div class="flex items-center justify-between">
			                    <div class="flex flex-col">
			                        <span class="text-sm font-bold text-slate-700 peer-checked:text-blue-700 transition-colors">
			                            {{ $namaJalur }}
			                        </span>
			                        <span class="text-[10px] text-slate-400 mt-0.5 peer-checked:text-blue-600/70">
			                            Klik untuk memilih
			                        </span>
			                    </div>
			    
			                    <div class="relative">
			                        <div class="h-6 w-6 rounded-full border-2 border-slate-300 bg-white transition-all duration-300 peer-checked:opacity-0 absolute top-0 right-0"></div>
			                        <div class="h-6 w-6 rounded-full bg-blue-600 border-2 border-blue-600 flex items-center justify-center transform scale-0 opacity-0 transition-all duration-300 group-has-[:checked]:scale-100 group-has-[:checked]:opacity-100">
			                            <i class="fas fa-check text-white text-[10px]"></i>
			                        </div>
			                    </div>
			                </div>
			            </label>
			        </div>
			    @endforeach
			</div>
        </div>
        
        {{-- 2. JURUSAN 1 --}}
        <div class="mb-6 space-y-2 group">
            <label class="text-[11px] font-extrabold text-slate-600 uppercase tracking-widest ml-1">
                Pilihan Program Studi 1 <span class="text-pink-500">*</span>
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="fas fa-graduation-cap text-slate-400 group-focus-within:text-indigo-500 transition-colors duration-300"></i>
                </div>
                <select name="jurusan1" 
                        id="selectJurusan1"
                        x-model="form.j1"
                        class="w-full pl-12 pr-10 py-3.5 bg-white/50 backdrop-blur-sm border border-white/80 rounded-xl text-slate-700 focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-400 focus:bg-white transition-all shadow-[inset_0_2px_4px_rgba(0,0,0,0.02)] hover:shadow-md appearance-none cursor-pointer"
                        required>
                    <option value="">-- Pilih Program Studi 1 --</option>
                    @foreach($jurusanList as $jurusan)
                        @php
                            // Ambil data (support array/object)
                            $kodeProdi = is_array($jurusan) ? ($jurusan['kode_prodi'] ?? '') : ($jurusan->kode_prodi ?? '');
                            $namaProdi = is_array($jurusan) ? ($jurusan['nama_prodi'] ?? $jurusan['nama'] ?? '') : ($jurusan->nama_prodi ?? $jurusan->nama ?? '');
                            $jenjangRaw = strtolower(is_array($jurusan) ? ($jurusan['jenjang'] ?? '') : ($jurusan->jenjang ?? ''));

                            // [PERBAIKAN 1] Ubah 'sisa_kuota' menjadi 'kuota'
                            $sisa = is_array($jurusan) ? ($jurusan['kuota'] ?? 0) : ($jurusan->kuota ?? 0);

                            // [PERBAIKAN 2] Hitung status penuh berdasarkan sisa kuota yg benar
                            $isPenuh = ($sisa <= 0);

                            if(empty($kodeProdi)) continue;

                            $isS2 = \Illuminate\Support\Str::contains($jenjangRaw, ['s2', 'magister', 'pasca']);
                            $tipeJalur = $isS2 ? 's2' : 'all';

                            // --- LOGIKA KUNCI FARMASI ---
                            $isLocked = false; 
                            
                            // [PERBAIKAN 3] Disable jika Penuh ATAU Terkunci
                            $isDisabled = $isPenuh || $isLocked;

                            // Atur Label & Warna
                            if ($isPenuh) {
                                $labelStatus = '[PENUH]';
                                $classOption = 'bg-red-50 text-red-400';
                            } else {
                                $labelStatus = "(Sisa: $sisa)";
                                $classOption = '';
                            }
                        @endphp

                        <option value="{{ $kodeProdi }}" 
                                class="{{ $classOption }} jurusan-option"
                                data-jalur="{{ $tipeJalur }}"
                                data-nama="{{ strtolower($namaProdi) }}"
                                {{-- Gunakan variabel $isDisabled --}}
                                {{ $isDisabled ? 'disabled' : '' }}>
                            ({{ strtoupper($jenjangRaw) }}) - {{ $namaProdi }} {{ $labelStatus }}
                        </option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                    <i class="fas fa-chevron-down text-xs text-indigo-400"></i>
                </div>
            </div>
        </div>
        
        {{-- ALASAN 1 --}}
        <div class="mb-8 space-y-2">
            <div class="flex justify-between items-center ml-1">
                <label class="text-[11px] font-extrabold text-slate-600 uppercase tracking-widest">
                    Alasan Memilih Jurusan 1 <span class="text-pink-500">*</span>
                </label>
                <span class="text-[10px] text-slate-400 font-semibold" :class="form.alasan1.length < 10 ? 'text-pink-400' : 'text-emerald-500'">
                    <span x-text="form.alasan1.length"></span>/1000 Karakter
                </span>
            </div>
            <div class="relative">
                <textarea x-model="form.alasan1" rows="3" 
                          class="w-full p-4 bg-white/50 backdrop-blur-sm border border-white/80 rounded-xl text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-400 focus:bg-white transition-all shadow-[inset_0_2px_4px_rgba(0,0,0,0.02)] hover:shadow-md resize-none"
                          placeholder="Jelaskan motivasi dan alasan Anda memilih program studi ini..." required></textarea>
                <div class="absolute bottom-2 right-2">
                    <i class="fas fa-pen text-slate-200 text-xs"></i>
                </div>
            </div>
        </div>
        
        {{-- ========================================================= --}}
        {{-- AREA JURUSAN 2 (BUNGKUS DENGAN X-SHOW) --}}
        {{-- ========================================================= --}}
        <div x-show="!isSingleChoice" 
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="opacity-0 -translate-y-4" 
             x-transition:enter-end="opacity-100 translate-y-0">
            
            {{-- SEPARATOR: PILIHAN KEDUA --}}
            <div class="relative flex py-8 items-center">
                <div class="flex-grow border-t border-indigo-100/50"></div>
                <span class="flex-shrink-0 mx-4 text-[10px] font-extrabold text-indigo-300 uppercase tracking-widest">Pilihan Kedua</span>
                <div class="flex-grow border-t border-indigo-100/50"></div>
            </div>

            {{-- 3. JURUSAN 2 --}}
            <div class="mb-6 space-y-2 group">
                <div class="flex items-center gap-1 ml-1">
                    <label class="text-[11px] font-extrabold text-slate-600 uppercase tracking-widest">
                        Pilihan Program Studi 2
                    </label>
                    <span class="text-pink-500 font-bold">*</span>
                </div>

                <div class="relative">
                    {{-- Icon Kiri --}}
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fas fa-graduation-cap text-slate-400 group-focus-within:text-indigo-500 transition-colors duration-300"></i>
                    </div>

                    {{-- Dropdown Select --}}
                    <select name="jurusan2" 
                            id="selectJurusan2"
                            x-model="form.j2"
                            class="w-full pl-12 pr-10 py-3.5 bg-white/50 backdrop-blur-sm border border-white/80 rounded-xl text-slate-700 focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-400 focus:bg-white transition-all shadow-[inset_0_2px_4px_rgba(0,0,0,0.02)] hover:shadow-md appearance-none cursor-pointer disabled:bg-white/30 disabled:text-slate-400"
                            :required="!isSingleChoice">
                        
                        <option value="">-- Pilih Program Studi 2 --</option>
                        
                        @foreach($jurusanList as $jurusan)
                         @php
                            // Ambil data (support array/object)
                            $kodeProdi = is_array($jurusan) ? ($jurusan['kode_prodi'] ?? '') : ($jurusan->kode_prodi ?? '');
                            $namaProdi = is_array($jurusan) ? ($jurusan['nama_prodi'] ?? $jurusan['nama'] ?? '') : ($jurusan->nama_prodi ?? $jurusan->nama ?? '');
                            $jenjangRaw = strtolower(is_array($jurusan) ? ($jurusan['jenjang'] ?? '') : ($jurusan->jenjang ?? ''));

                            // [PERBAIKAN 1] Ubah 'sisa_kuota' menjadi 'kuota'
                            $sisa = is_array($jurusan) ? ($jurusan['kuota'] ?? 0) : ($jurusan->kuota ?? 0);

                            // [PERBAIKAN 2] Hitung status penuh berdasarkan sisa kuota yg benar
                            $isPenuh = ($sisa <= 0);

                            if(empty($kodeProdi)) continue;

                            $isS2 = \Illuminate\Support\Str::contains($jenjangRaw, ['s2', 'magister', 'pasca']);
                            $tipeJalur = $isS2 ? 's2' : 'all';

                            // --- LOGIKA KUNCI FARMASI ---
                            $isLocked = false; 
                            
                            // [PERBAIKAN 3] Disable jika Penuh ATAU Terkunci
                            $isDisabled = $isPenuh || $isLocked;

                            // Atur Label & Warna
                            if ($isPenuh) {
                                $labelStatus = '[PENUH]';
                                $classOption = 'bg-red-50 text-red-400';
                            } else {
                                $labelStatus = "(Sisa: $sisa)";
                                $classOption = '';
                            }
                        @endphp

                        <option value="{{ $kodeProdi }}" 
                                class="{{ $classOption }} jurusan-option"
                                data-jalur="{{ $tipeJalur }}"
                                data-nama="{{ strtolower($namaProdi) }}"
                                {{-- Gunakan variabel $isDisabled --}}
                                {{ $isDisabled ? 'disabled' : '' }}>
                            ({{ strtoupper($jenjangRaw) }}) - {{ $namaProdi }} {{ $labelStatus }}
                        </option>
                    @endforeach
                    </select>

                    {{-- Icon Panah Kanan --}}
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                        <i class="fas fa-chevron-down text-xs text-indigo-400"></i>
                    </div>
                </div>
            </div>
            
            {{-- ALASAN 2 --}}
            <div class="mb-6 space-y-2">
                <div class="flex justify-between items-center ml-1">
                    <label class="text-[11px] font-extrabold text-slate-600 uppercase tracking-widest">
                        Alasan Memilih Jurusan 2 <span class="text-pink-500">*</span>
                    </label>
                    <span class="text-[10px] text-slate-400 font-semibold" :class="(form.alasan2 && form.alasan2.length < 10) ? 'text-pink-400' : 'text-emerald-500'">
                        <span x-text="form.alasan2 ? form.alasan2.length : 0"></span>/1000
                    </span>
                </div>
                
                <div class="relative">
                    <textarea x-model="form.alasan2" rows="3" 
                              class="w-full p-4 bg-white/50 backdrop-blur-sm border border-white/80 rounded-xl text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-400 focus:bg-white transition-all shadow-[inset_0_2px_4px_rgba(0,0,0,0.02)] hover:shadow-md resize-none"
                              placeholder="Jelaskan alasan memilih jurusan kedua..."
                              :required="!isSingleChoice"></textarea>
                    
                    <div class="absolute bottom-2 right-2">
                        <i class="fas fa-pen text-slate-200 text-xs"></i>
                    </div>
                </div>
            </div>

        </div>
        
        {{-- FOOTER NAVIGATION --}}
        <div class="flex items-center justify-between mt-12 pt-8 border-t border-indigo-100/50">
            <button type="button" @click="goBack" 
                    class="group inline-flex items-center gap-2 px-6 py-3.5 text-sm font-bold text-slate-500 uppercase tracking-widest bg-white/50 border border-white rounded-xl hover:bg-white hover:text-indigo-600 hover:shadow-md transition-all duration-300">
                <i class="fas fa-arrow-left transition-transform group-hover:-translate-x-1"></i>
                Kembali
            </button>
            
            <button type="submit" 
                    class="group relative inline-flex items-center justify-center gap-3 px-9 py-4 text-sm font-extrabold text-white uppercase tracking-widest transition-all duration-500 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 rounded-xl hover:shadow-[0_0_25px_rgba(79,70,229,0.5)] focus:ring-4 focus:ring-indigo-500/20 disabled:opacity-70 disabled:cursor-not-allowed overflow-hidden"
                    :disabled="loading">
                
                <!-- Shimmer effect -->
                <div class="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/20 to-transparent group-hover:animate-shimmer pointer-events-none"></div>

                <span x-show="!loading" class="flex items-center gap-3 relative z-10">
                    Simpan & Lanjutkan 
                    <span class="bg-white/20 p-1.5 rounded-lg group-hover:bg-white/30 transition-colors">
                        <i class="fas fa-arrow-right transition-transform group-hover:translate-x-1"></i>
                    </span>
                </span>
                
                <span x-show="loading" class="flex items-center gap-2 relative z-10" x-cloak>
                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Memproses...
                </span>
            </button>
        </div>
    </form>
    </div>
</div>

<script>
    function formStep2(initialData) {
        return {
            form: {
                jalur: initialData.jalur,
                j1: initialData.j1,
                j2: initialData.j2,
                alasan1: initialData.alasan1,
                alasan2: initialData.alasan2
            },
            loading: false,

            // Getter untuk Cek S2
            get isSingleChoice() {
                // Sesuaikan string ini dengan value yang ada di database kamu
                return this.form.jalur === 'Jalur S2 Manajemen' || this.form.jalur === 'Jalur RPL';
            },

            initData() {
                this.$nextTick(() => { this.filterJurusan(); });
            },

            filterJurusan() {
                if(!this.form.jalur) return;
                const jalurLower = this.form.jalur.toLowerCase();
                
                // Jika ganti ke S2, dan ada pilihan J2, mungkin perlu direset atau dibiarkan
                // Di sini kita biarkan user yang mengatur
                
                const filterLogic = (selectId) => {
                    const select = document.getElementById(selectId);
                    if(!select) return;
                    const options = select.querySelectorAll('.jurusan-option');
                    
                    options.forEach(opt => {
                        const nama = opt.getAttribute('data-nama');
                        const tipe = opt.getAttribute('data-jalur');
                        let show = false;

                        if (jalurLower.includes('rpl')) {
                            if (nama.includes('teknik industri')) show = true;
                        } else if (jalurLower.includes('s2') || jalurLower.includes('magister')) {
                            if (tipe === 's2') show = true;
                        } else {
                            if (tipe !== 's2') show = true;
                        }

                        opt.hidden = !show;
                        opt.style.display = show ? 'block' : 'none';
                    });
                };

                filterLogic('selectJurusan1');
                filterLogic('selectJurusan2');
            },

            submitForm() {
                // 1. VALIDASI JALUR
                if (!this.form.jalur) return Swal.fire('Peringatan', 'Silakan pilih Jalur Pendaftaran.', 'warning');
                
                // 2. VALIDASI JURUSAN 1 & ALASAN 1 (SELALU WAJIB)
                if (!this.form.j1) return Swal.fire('Peringatan', 'Silakan pilih Jurusan 1.', 'warning');
                if (!this.form.alasan1 || this.form.alasan1.length < 10) return Swal.fire('Peringatan', 'Alasan Jurusan 1 minimal 10 karakter.', 'warning');
                
                // 3. VALIDASI JURUSAN 2 & ALASAN 2 (WAJIB JIKA BUKAN S2)
                if (!this.isSingleChoice) {
                    if (!this.form.j2) return Swal.fire('Peringatan', 'Silakan pilih Jurusan 2.', 'warning');
                    if (this.form.j1 === this.form.j2) return Swal.fire('Peringatan', 'Jurusan 1 dan 2 tidak boleh sama.', 'warning');
                    if (!this.form.alasan2 || this.form.alasan2.length < 10) return Swal.fire('Peringatan', 'Alasan Jurusan 2 minimal 10 karakter.', 'warning');
                } else {
                    // Jika Single Choice, kita kosongkan data J2 supaya bersih saat dikirim
                    this.form.j2 = null;
                    this.form.alasan2 = null;
                }

                this.loading = true;
                const formData = new FormData();
                formData.append('jalur_pendaftaran', this.form.jalur);
                formData.append('jurusan1', this.form.j1);
                formData.append('alasan_jurusan_1', this.form.alasan1);
                
                // Kirim J2 & Alasan2 jika diisi (untuk S2 opsional, S1 wajib)
                if (!this.isSingleChoice && this.form.j2) {
                    formData.append('jurusan2', this.form.j2);
                    formData.append('alasan_jurusan_2', this.form.alasan2);
                }

                fetch('{{ route("mahasiswa.registration.step2") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    this.loading = false;
                    if(data.success) {
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Data tersimpan.', showConfirmButton: false, timer: 1500 })
                        .then(() => { window.location.href = data.redirect_url || window.location.href; });
                    } else {
                        Swal.fire('Gagal', data.message, 'error');
                    }
                })
                .catch(err => {
                    this.loading = false;
                    Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
                });
            },

            goBack() { window.history.back(); }
        }
    }
</script>