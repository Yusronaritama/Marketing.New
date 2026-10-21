// File: public/js/mahasiswa/registration.js

function registrationApp(initialData) {
    return {
        // ========================
        // 1. TERIMA DATA DARI BLADE
        // ========================
        jurusan1: initialData.jurusan1 || '',
        jurusan2: initialData.jurusan2 || '',
        currentStep: initialData.currentStep || 1,
        
        // State Aplikasi
        loading: false,
        scanning: false,
        
        // Data Status Scan & Jalur
        scanStatus: {
            scan_status: 'not_scanned',
            scan_results: {},
            // Mengambil jalur dari data yang dikirim Blade
            jalur_pendaftaran: initialData.jalurPendaftaran || '', 
        },

        // ========================
        // 2. INIT (Saat Halaman Dimuat)
        // ========================
        init() {
            console.log('PMB App Loaded via External JS.');
            console.log('Jalur Pendaftaran:', this.scanStatus.jalur_pendaftaran);
            
            // Cek status ke server untuk sinkronisasi
            this.checkScanStatus();

            // Pasang listener input file (gunakan nextTick agar HTML siap dulu)
            this.$nextTick(() => {
                this.attachFileListeners();
            });

            // Watcher: Jika user pindah ke step 3, pasang listener lagi
            this.$watch('currentStep', (val) => {
                if(val === 3) this.$nextTick(() => this.attachFileListeners());
            });
        },

        // ========================
        // 3. LOGIC TAMPILAN & HELPER
        // ========================
        
        // Cek Jalur (untuk x-show di HTML)
        isJalur(namaJalur) {
            return this.scanStatus.jalur_pendaftaran === namaJalur;
        },

        // Cek Farmasi (untuk x-show di HTML)
        isFarmasi() {
            const j1 = this.jurusan1.toLowerCase();
            const j2 = this.jurusan2.toLowerCase();
            return j1.includes('farmasi') || j2.includes('farmasi');
        },

        // Teks Status yang User-Friendly
        getStatusText(status) {
            const map = { 
                'not_scanned': 'Berkas belum divalidasi', 
                'scanning': 'Sedang membaca berkas...', 
                'completed': 'Valid', 
                'partial_failed': 'Perlu Perbaikan', 
                'failed': 'Gagal' 
            };
            return map[status] || status;
        },

        // Update Tampilan Nama File saat dipilih
        handleFileChange(id) {
            const input = document.getElementById(id);
            const nameContainer = document.getElementById(id + '_name_container');
            const nameSpan = document.getElementById(id + '_name');

            if (input.files && input.files[0]) {
                nameSpan.textContent = input.files[0].name;
                nameContainer.classList.remove('hidden');
                // Reset status scan karena file berubah
                this.scanStatus.scan_status = 'not_scanned'; 
            }
        },

        // Pasang listener manual (Backup jika x-on:change tidak nempel)
        attachFileListeners() {
            setTimeout(() => {
                document.querySelectorAll('input[type="file"]').forEach(input => {
                    input.onchange = null; // Hapus listener lama
                    input.addEventListener('change', function(e) {
                        const fileName = this.files[0]?.name;
                        // Cari elemen penampung nama file berdasarkan ID input
                        const displayElement = document.getElementById(this.name + '_name');
                        const containerElement = document.getElementById(this.name + '_name_container');
                        
                        if (fileName && displayElement) {
                            displayElement.textContent = fileName;
                            if(containerElement) containerElement.classList.remove('hidden');
                        }
                    });
                });
            }, 500);
        },

        // ========================
        // 4. API & SERVER CALLS
        // ========================

        // Ambil Token CSRF dari Meta Tag (Wajib untuk Fetch di External JS)
        getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        },

        // Cek Status ke Server
        async checkScanStatus() {
            try {
                const res = await fetch('/mahasiswa/scanner/status', {
                    headers: { 
                        'X-CSRF-TOKEN': this.getCsrfToken(), 
                        'Accept': 'application/json' 
                    }
                });
                const data = await res.json();
                if (data.success && data.jalur_pendaftaran) {
                    this.scanStatus.jalur_pendaftaran = data.jalur_pendaftaran;
                    this.scanStatus.scan_status = data.scan_status;
                }
            } catch(e) { console.error('Gagal cek status:', e); }
        },

        // Kirim ke Node.js Validator
        async scanAndValidate() {
            this.scanning = true;
            const formData = new FormData(document.getElementById('step3Form'));

            try {
                const res = await fetch('/mahasiswa/scanner/scan-temp', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': this.getCsrfToken() },
                    body: formData
                });
                const data = await res.json();

                if (data.success) {
                    this.scanStatus.scan_status = data.scan_status;
                    this.scanStatus.scan_results = data.scan_results;
                    
                    if(data.scan_status === 'completed') {
                        Swal.fire({icon:'success', title:'Validasi Sukses', timer:2000, showConfirmButton:false});
                    } else {
                        Swal.fire({icon:'warning', title:'Periksa Kembali', text:'Beberapa dokumen mungkin buram.'});
                    }
                } else {
                    throw new Error(data.message);
                }
            } catch (e) {
                Swal.fire({icon:'error', title:'Gagal Scan', text: e.message});
            } finally {
                this.scanning = false;
            }
        },

        // Submit Akhir Step 3
        async submitStep3() {
            this.loading = true;
            const formData = new FormData(document.getElementById('step3Form'));
            
            try {
                // URL Endpoint bisa disesuaikan jika berbeda
                const res = await fetch('/mahasiswa/registration/step3', { 
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': this.getCsrfToken() },
                    body: formData
                });
                const data = await res.json();
                
                if (data.success) {
                    Swal.fire({icon: 'success', title: 'Berhasil', text: data.message, showConfirmButton:false, timer:1500})
                        .then(() => window.location.reload());
                } else {
                    let msg = data.message;
                    if(data.errors) msg = Object.values(data.errors).flat().join('\n');
                    throw new Error(msg);
                }
            } catch (e) {
                Swal.fire({icon:'error', title:'Gagal', text: e.message});
            } finally {
                this.loading = false;
            }
        },

        // Navigasi
        previousStep() { if(this.currentStep > 1) this.currentStep--; },
        showStep(n) { 
            // Cegah user loncat ke step yang belum terbuka
            if(n <= (initialData.completedStep + 1)) this.currentStep = n; 
        },
    }
}