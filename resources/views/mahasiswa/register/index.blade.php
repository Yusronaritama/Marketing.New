@extends('layouts.mahasiswa.register')

@section('title', 'Pendaftaran Mahasiswa Baru')

@section('content')

    {{-- A. WIDGET COUNTDOWN TIMER (MIGRATED TO VUE) --}}
    @if(isset($remainingSeconds) && $remainingSeconds > 0)
        <div id="vue-timer-app" data-initial-seconds="{{ $remainingSeconds }}"></div>
    @endif

    {{-- B. MAIN APP (STEPPER & CONTENT) --}}
    <div x-data="registrationApp()" x-init="init()" x-cloak>
        
        
       
        {{-- STEPPER NAVIGATION (PREMIUM REDESIGN) --}}
        <div class="mb-10 relative">
            <!-- Decorative background blur -->
            <div class="absolute -inset-1 bg-gradient-to-r from-blue-600 via-indigo-500 to-purple-600 rounded-[2rem] blur opacity-20 animate-pulse pointer-events-none"></div>
            
            <div class="relative bg-white/80 backdrop-blur-xl rounded-[2rem] shadow-xl border border-white p-6 sm:p-10">
                
                <div class="relative flex items-start justify-between max-w-4xl mx-auto">
        
                    <!-- TRACK LINE -->
                    <div class="absolute top-6 left-0 right-0 mx-10 md:mx-14 -z-10 h-1.5 md:h-2">
                        {{-- Background Track --}}
                        <div class="absolute inset-0 bg-slate-100 rounded-full shadow-inner"></div>
                        
                        {{-- Animated Progress Track --}}
                        <div class="absolute top-0 left-0 h-full bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500 rounded-full transition-all duration-1000 ease-out shadow-[0_0_15px_rgba(99,102,241,0.5)]"
                             x-bind:style="'width: ' + ((currentStep - 1) / 3) * 100 + '%'"></div>
                    </div>
                    
                    {{-- DATA STEPS --}}
                    @php
                        $steps = ['Data Pribadi', 'Pilihan Jurusan', 'Upload Berkas', 'Validasi'];
                        $icons = ['user', 'graduation-cap', 'file-upload', 'shield-check'];
                    @endphp
                    
                    @foreach($steps as $index => $step)
                        <div class="flex flex-col items-center group cursor-pointer w-20 md:w-28 relative" 
                             x-on:click="showStep({{ $index + 1 }})">
                            
                            {{-- SHAPE ICON --}}
                            <div class="w-12 h-12 md:w-14 md:h-14 rounded-2xl flex items-center justify-center transition-all duration-500 z-10 box-border relative overflow-hidden"
                                 x-bind:class="currentStep >= {{ $index + 1 }} 
                                    ? 'bg-gradient-to-br from-blue-600 to-indigo-600 shadow-[0_8px_20px_-6px_rgba(79,70,229,0.6)] scale-110 -translate-y-1' 
                                    : 'bg-white border-2 border-slate-100 shadow-sm group-hover:border-indigo-300 group-hover:shadow-md'">
                                
                                <!-- Active Glow Ring -->
                                <div x-show="currentStep === {{ $index + 1 }}" 
                                     class="absolute inset-0 border-2 border-white/30 rounded-2xl animate-ping" style="display: none;"></div>

                                <!-- Icon -->
                                <i class="fas text-lg md:text-xl transition-all duration-300 relative z-10" 
                                   x-bind:class="currentStep > {{ $index + 1 }} 
                                        ? 'fa-check text-white' 
                                        : (currentStep === {{ $index + 1 }} ? 'fa-{{ $icons[$index] }} text-white' : 'fa-{{ $icons[$index] }} text-slate-400 group-hover:text-indigo-400')"></i>
                            </div>
        
                            {{-- LABEL TEKS --}}
                            <span class="mt-4 text-[10px] md:text-[12px] font-extrabold uppercase tracking-widest text-center transition-all duration-500" 
                                  x-bind:class="currentStep === {{ $index + 1 }} ? 'text-indigo-600 scale-105' : (currentStep > {{ $index + 1 }} ? 'text-blue-500' : 'text-slate-400')">
                                {{ $step }}
                            </span>
                            
                            <!-- Subtitle / Indicator -->
                            <span class="text-[9px] text-slate-400 font-semibold tracking-widest mt-1 opacity-0 transition-all duration-300 transform translate-y-1"
                                  x-bind:class="currentStep === {{ $index + 1 }} ? 'opacity-100 translate-y-0' : ''">
                                TAHAP {{ $index + 1 }}
                            </span>
                        </div>
                    @endforeach
        
                </div>
            </div>
        </div>

        {{-- CONTENT WRAPPER --}}
        <div>
            {{-- INCLUDE FILE BLADE LANGSUNG (Pastikan file ada di folder steps) --}}
            <div x-show="currentStep === 1" class="animate-fade-in-up">
                @include('mahasiswa.register.steps.step1')
            </div>
            <div x-show="currentStep === 2" x-cloak class="animate-fade-in-up">
                @include('mahasiswa.register.steps.step2')
            </div>
            <div x-show="currentStep === 3" x-cloak class="animate-fade-in-up">
                @include('mahasiswa.register.steps.step3')
            </div>
            <div x-show="currentStep === 4" x-cloak class="animate-fade-in-up">
                @include('mahasiswa.register.steps.step4')
            </div>
        </div>
    </div>

    {{-- MAIN LOGIC SCRIPT --}}
    <script>
    function registrationApp() {
        return {
            // ============================================================
            // 1. STATE & PROPERTIES
            // ============================================================
            jurusan1: '{{ $registration->jurusan1 }}',
            jurusan2: '{{ $registration->jurusan2 }}',
            currentStep: {{ $registration->step_completed + 1 }},
            loading: false,
            scanning: false,
            
            // [BARU] Variable untuk melacak status upload agar UI bereaksi real-time
            filesUploaded: {}, 

            scanStatus: {
                scan_status: 'not_scanned',
                scan_results: {},
                jalur_pendaftaran: '{{ $registration->jalur_pendaftaran }}'
            },
            
            // ============================================================
            // 2. INITIALIZATION
            // ============================================================
            init() {
                console.log('Registration App Initialized. Current Step:', this.currentStep);
                
                // Cek file yang mungkin sudah ada saat reload (Browser caching)
                this.$nextTick(() => {
                    document.querySelectorAll('input[type="file"]').forEach(input => {
                        if (input.files.length > 0) {
                            this.handleFileChange(input.id);
                        }
                    });
                });

                if (this.currentStep === 3) {
                    this.$nextTick(() => { this.checkScanStatus(); });
                }
            },

            // ============================================================
            // 3. NAVIGATION & STEPPER
            // ============================================================
            showStep(stepNumber) {
                if (stepNumber <= {{ $registration->step_completed }} + 1) {
                    this.currentStep = stepNumber;
                    if (stepNumber === 3) this.$nextTick(() => { this.checkScanStatus(); });
                }
            },
            previousStep() {
                if (this.currentStep > 1) this.currentStep--;
            },
            
            // ============================================================
            // 4. HELPER FUNCTIONS (FILE HANDLING) - DIPERBAIKI
            // ============================================================
            
            // [FIX] Cek dari reactive state DULU, baru cek DOM
            hasFile(inputId) {
                // Jika status reactive true, return true (UI langsung update)
                if (this.filesUploaded[inputId]) return true;
                
                // Fallback: Cek manual elemen input (untuk load awal)
                const input = document.getElementById(inputId);
                return input && input.files && input.files.length > 0;
            },

            formatBytes(bytes, decimals = 2) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const dm = decimals < 0 ? 0 : decimals;
                const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
            },

            handleFileChange(fileType) {
                // 1. [MOBILE FIX] Gunakan setTimeout agar browser tidak crash/blank di HP
                setTimeout(() => {
                    const input = document.getElementById(fileType);
                    const displayElement = document.getElementById(fileType + '_name');
                    
                    if (input && input.files && input.files[0]) {
                        const file = input.files[0];
                        
                        // 2. [VALIDASI PDF] Cek tipe file
                        if (file.type !== 'application/pdf') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Format Salah',
                                text: 'Mohon upload file bertipe PDF.',
                                confirmButtonColor: '#e11d48'
                            });
                            
                            // Reset Input jika salah
                            input.value = ''; 
                            
                            // Hapus status file dari Alpine
                            const newFiles = { ...this.filesUploaded };
                            delete newFiles[fileType];
                            this.filesUploaded = newFiles;

                            // Kosongkan tampilan nama
                            if (displayElement) displayElement.innerHTML = '';
                            
                            return; // Stop proses
                        }

                        // 3. [LOGIKA ASLI ANDA] Ambil Nama & Size
                        const fileName = file.name;
                        const fileSize = this.formatBytes(file.size);

                        // Update Tampilan HTML
                        if (displayElement) {
                            displayElement.innerHTML = `
                                <div class="flex flex-col items-center justify-center animate-fade-in-up">
                                    <span class="line-clamp-1 break-all text-xs font-bold text-slate-700 text-center">${fileName}</span>
                                    <span class="mt-1 px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-bold border border-emerald-200 shadow-sm">
                                        <i class="fas fa-hdd mr-1"></i> ${fileSize}
                                    </span>
                                </div>
                            `;
                        }
                        
                        // Update State Reactive agar x-show muncul
                        this.filesUploaded = { ...this.filesUploaded, [fileType]: true };
                        
                        // Reset status scan
                        this.scanStatus.scan_status = 'not_scanned';
                    }
                }, 150); // Delay 150ms sangat penting untuk Mobile
            },
            
            // ============================================================
            // 5. STEP 1 SUBMISSION (DATA DIRI)
            // ============================================================
            async submitStep1() {
                const form = document.getElementById('step1Form');
                if (!form) return;
                this.loading = true;
                const formData = new FormData(form);
                try {
                    const response = await fetch('{{ route("mahasiswa.registration.step1") }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                        body: formData
                    });
                    const data = await response.json();
                    if (data.success) {
                        Swal.fire({icon: 'success', title: 'Berhasil!', text: data.message, timer: 1500, showConfirmButton: false})
                            .then(() => window.location.reload());
                    } else throw new Error(data.message);
                } catch (error) {
                    Swal.fire({icon: 'error', title: 'Error', text: error.message});
                } finally { this.loading = false; }
            },
            
            // ============================================================
            // 6. STEP 2 SUBMISSION (JURUSAN)
            // ============================================================
            async submitStep2() {
                const form = document.getElementById('step2Form'); 
                if (!form) return; 
                this.loading = true;
                const formData = new FormData(form);
                try {
                    const response = await fetch('{{ route("mahasiswa.registration.step2") }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                        body: formData
                    });
                    const data = await response.json();
                    if (data.success) {
                        Swal.fire({icon: 'success', title: 'Berhasil!', text: data.message, timer: 1500, showConfirmButton: false})
                            .then(() => window.location.reload());
                    } else throw new Error(data.message);
                } catch (error) {
                    Swal.fire({icon: 'error', title: 'Error', text: error.message});
                } finally { this.loading = false; }
            },

            // ============================================================
            // 7. STEP 3 LOGIC (UPLOAD & SCAN)
            // ============================================================
            async checkScanStatus() {
                try {
                    const response = await fetch('/mahasiswa/scanner/status', {
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                    });
                    const data = await response.json();
                    if (data.success) this.scanStatus = data;
                } catch (error) { console.error(error); }
            },

            async scanAndValidate() {
                if (this.scanning) return;
                this.scanning = true;
                
                window.dispatchEvent(new CustomEvent('pause-timer'));

                try {
                    const form = document.getElementById('step3Form');
                    const fileInputs = form.querySelectorAll('input[type="file"][required]');
                    let allFilesUploaded = true;
                    fileInputs.forEach(input => { if (!input.files || input.files.length === 0) allFilesUploaded = false; });
                    
                    if (!allFilesUploaded) throw new Error('Harap upload semua berkas wajib sebelum melakukan validasi.');
                    
                    const formData = new FormData(form);
                    
                    const response = await fetch('/mahasiswa/scanner/scan-temp', {
                        method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: formData
                    });
                    const data = await response.json();
                    
                    if (data.success) {
                        this.scanStatus = {
                            scan_status: data.scan_status,
                            scan_results: data.scan_results,
                            jalur_pendaftaran: data.jalur_pendaftaran
                        };
                        if (data.scan_status === 'completed') {
                            Swal.fire({icon: 'success', title: 'Valid!', text: 'Semua berkas valid.', timer: 2000, showConfirmButton: false});
                        } else {
                            Swal.fire({icon: 'warning', title: 'Perhatian', text: 'Beberapa berkas perlu diperbaiki.', confirmButtonColor: '#f59e0b'});
                        }
                    } else throw new Error(data.message);
                } catch (error) {
                    Swal.fire({icon: 'error', title: 'Gagal', text: error.message});
                } finally { 
                    this.scanning = false;
                    window.dispatchEvent(new CustomEvent('resume-timer'));
                }
            },

            async submitStep3() {
                this.loading = true;
                const form = document.getElementById('step3Form');
                const formData = new FormData(form);
                formData.append('scan_results', JSON.stringify(this.scanStatus.scan_results));
                formData.append('scan_status', this.scanStatus.scan_status);
                try {
                    const response = await fetch('{{ route("mahasiswa.registration.step3") }}', {
                        method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: formData
                    });
                    const data = await response.json();
                    if (data.success) {
                        Swal.fire({icon: 'success', title: 'Berhasil!', text: data.message, timer: 2000, showConfirmButton: false})
                            .then(() => window.location.reload());
                    } else throw new Error(data.message);
                } catch (error) {
                    Swal.fire({icon: 'error', title: 'Gagal', text: error.message});
                } finally { this.loading = false; }
            },
            
            canProceedToNextStep() {
                if (this.loading || this.scanning) return false;
                const form = document.getElementById('step3Form');
                if(form) {
                     const fileInputs = form.querySelectorAll('input[type="file"][required]');
                     for (let input of fileInputs) {
                         if (!input.files || input.files.length === 0) return false;
                     }
                }
                return true;
            },

            // --- TAMPILAN VIEW STATUS ---
            getStatusText(status) {
                const map = {'not_scanned': 'Belum Divalidasi', 'scanning': 'Sedang Memindai...', 'completed': 'Validasi Selesai', 'partial_failed': 'Perlu Perbaikan', 'failed': 'Validasi Gagal'};
                return map[status] || status;
            },
            getFileName(fileType) {
                const map = {
                    'file_ktp': 'KTP', 
                    'file_kk': 'Kartu Keluarga', 
                    'file_ijazah': 'Ijazah/SKL', 
                    'file_transkrip_nilai': 'Transkrip Nilai', 
                    'file_surat_pindah': 'Surat Pindah',
                    'file_ktm': 'KTM Asal',
                    'file_ijazah_s1': 'Ijazah S1',
                    'file_transkrip_s1': 'Transkrip S1',
                    'file_surat_tugas_kerja': 'Surat Tugas Kerja',
                    'file_transkrip_nilai_rpl': 'Transkrip RPL',
                    'file_surat_buta_warna': 'Surat Buta Warna',
                    'file_surat_akademik_ekstensi': 'Transkrip Nilai (Ekstensi)'
                };
                return map[fileType] || fileType.replace('file_', '').replace(/_/g, ' ').toUpperCase();
            },

            // ============================================================
            // 8. STEP 4: FINALISASI
            // ============================================================
            completeRegistration() {
                Swal.fire({
                    title: '<span class="text-xl font-bold text-slate-800">Konfirmasi Akhir</span>',
                    html: `<div class="mt-2 mb-4"><p class="text-sm text-slate-600">Yakin data sudah benar?</p></div>
                           <div class="bg-amber-50 border border-amber-100 rounded-xl p-3 text-left flex gap-3">
                               <div class="shrink-0 text-amber-500 mt-0.5"><i class="fas fa-exclamation-triangle"></i></div>
                               <div><p class="text-xs font-bold text-amber-800">Peringatan:</p><p class="text-xs text-amber-700">Data akan dikunci permanen setelah ini.</p></div>
                           </div>`,
                    icon: null, showCancelButton: true, confirmButtonColor: '#10b981', cancelButtonColor: '#94a3b8',
                    confirmButtonText: 'Ya, Selesaikan!', cancelButtonText: 'Cek Lagi', reverseButtons: true,
                    customClass: { popup: 'rounded-2xl font-sans', confirmButton: 'rounded-xl px-6 py-2.5 font-bold', cancelButton: 'rounded-xl px-6 py-2.5 font-bold' }
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.loading = true;
                        fetch('{{ route("mahasiswa.registration.complete") }}', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
                            body: JSON.stringify({ scan_results: this.scanStatus ? this.scanStatus.scan_results : {} })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({icon: 'success', title: 'Selesai!', text: 'Pendaftaran berhasil.', timer: 3000, showConfirmButton: false})
                                    .then(() => { window.location.href = '{{ route("mahasiswa.dashboard") }}'; });
                            } else throw new Error(data.message);
                        })
                        .catch(error => {
                            this.loading = false;
                            Swal.fire({icon: 'error', title: 'Kesalahan', text: error.message});
                        });
                    }
                });
            }
        };
    }
    

</script>   
@endsection