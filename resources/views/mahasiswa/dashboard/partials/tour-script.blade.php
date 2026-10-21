{{-- FILE: resources/views/mahasiswa/dashboard/partials/tour-script.blade.php --}}

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.css"/>
<script src="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.js.iife.js"></script>

<script>
    console.log("✅ Script Tour Dimuat (Fitur: Checkbox & Anti-Tabrak Robust)");

    document.addEventListener('DOMContentLoaded', () => {
        
        if (!window.driver) { console.error("❌ ERROR: Driver.js gagal."); return; }
        const driver = window.driver.js.driver;

        // --- STATUS STATE ---
        let mainTourDone = false; 
        let userPrefersStop = false; 
        
        // Antrian
        let queue = {
            upload: false,
            exam: false
        };

        // --- HTML Checkbox Component ---
        const checkboxHTML = `
            <div style="margin-top: 15px; padding-top: 10px; border-top: 1px solid #eee;">
                <label style="display: flex; align-items: center; gap: 8px; font-size: 12px; color: #64748b; cursor: pointer;">
                    <input type="checkbox" id="tour-dont-show" style="cursor: pointer;">
                    Jangan tampilkan panduan ini lagi
                </label>
            </div>
        `;

        // --- EVENT LISTENER CHECKBOX ---
        document.addEventListener('change', (e) => {
            if (e.target && e.target.id === 'tour-dont-show') {
                userPrefersStop = e.target.checked;
                console.log("User memilih jangan tampilkan lagi:", userPrefersStop);
            }
        });

        // ============================================================
        // 1. TOUR UTAMA
        // ============================================================
        const mainDriver = driver({
            showProgress: true, animate: true, allowClose: true,
            doneBtnText: 'Selesai', nextBtnText: 'Lanjut', prevBtnText: 'Mundur',
            steps: [
                { 
                    element: '#tour-hero', 
                    popover: { 
                        title: 'Selamat Datang! 👋', 
                        description: 'Ini adalah progress Pendaftaran Anda.' + checkboxHTML, 
                        side: "bottom", align: 'start' 
                    } 
                },
                { 
                    element: '#wave-monitor-widget', 
                    popover: { 
                        title: 'Info Grup WhatsApp & Gelombang 📱', 
                        description: 'Jika butuh info <b>Grup WhatsApp</b> atau Gelombang pendaftaran, Anda bisa cek kembali lewat menu ini.', 
                        side: "bottom", align: 'center' 
                    } 
                },
                { element: '#tour-progress-bar', popover: { title: 'Alur Tahapan 🚀', description: 'ini adalah alur pendaftaran Anda. Klik nomor di sini untuk berpindah antar tahapan (Biodata, Ujian, dll).', side: "right", align: 'start' } },
                { element: '#step-content-anchor', popover: { title: 'Area Formulir 📝', description: 'Bagian ini merupakan komponen yang sangat penting dan perlu diperhatikan dengan saksama, karena seluruh proses pendaftaran dilakukan melalui bagian ini.', side: "top", align: 'start' } }
            ],
            
            onDestroyed: () => {
                console.log("🏁 Overlay Main Tour tertutup.");
                mainTourDone = true; 

                // Sekali diakses atau ditutup, tandai agar tidak muncul otomatis lagi
                localStorage.setItem('main_tour_stopped', 'true');
                console.log("💾 Preferensi disimpan: Tour tidak akan muncul otomatis lagi.");

                setTimeout(() => {
                    checkQueue();
                }, 500); 
            }
        });

        // FUNGSI CEK ANTRIAN
        function checkQueue() {
            if (queue.upload) {
                console.log("✅ Menjalankan antrian: Upload Tour");
                window.runUploadTour();
            } else if (queue.exam) {
                console.log("✅ Menjalankan antrian: Exam Tour");
                window.runExamTour();
            } else {
                console.log("📭 Antrian kosong.");
            }
        }

        // ============================================================
        // LOGIKA AUTO START "PINTAR" (PERBAIKAN UTAMA DISINI)
        // ============================================================
        
        function tryStartTour() {
            // 1. DETEKSI POPUP WA / MODAL LAIN (Lebih Akurat)
            const popupWA = document.getElementById('wa-modal-overlay'); 
            
            // Kita juga cek apakah body punya class 'modal-open' (biasanya dipakai Bootstrap/Tailwind saat ada modal aktif)
            const isBodyLocked = document.body.classList.contains('modal-open') || document.body.style.overflow === 'hidden';

            let isWaVisible = false;
            if (popupWA) {
                // getComputedStyle bisa membaca CSS class (misal: hidden, d-none, opacity-0)
                const style = window.getComputedStyle(popupWA);
                isWaVisible = (style.display !== 'none' && style.visibility !== 'hidden' && style.opacity !== '0');
            }

            // JIKA MASIH ADA POPUP / MODAL
            if (isWaVisible || isBodyLocked) {
                console.log("⏳ Menunggu... (Popup WA/Modal masih aktif)");
                setTimeout(tryStartTour, 1500); // Cek lagi 1.5 detik kemudian
                return; 
            }

            // 2. CEK HALAMAN (Safety Check)
            if (!document.getElementById('tour-hero')) {
                return;
            }

            // 3. CEK PREFERENSI USER
            const isStopped = localStorage.getItem('main_tour_stopped') === 'true';

            if (!isStopped) {
                console.log("▶️ Area bersih. Menjalankan Tour Utama...");
                mainDriver.drive();
            } else {
                console.log("🚫 Tour Utama dimatikan oleh user.");
                mainTourDone = true; 
                setTimeout(() => { checkQueue(); }, 500);
            }
        }

        // --- EKSEKUSI PERTAMA KALI ---
        // Mulai cek setelah 3 detik (agar halaman loading sempurna dulu)
        setTimeout(tryStartTour, 3000);


        // ============================================================
        // 2. TOUR UPLOAD & 3. TOUR UJIAN (Tetap Sama)
        // ============================================================
        window.runUploadTour = function() {
            if (localStorage.getItem('upload_tour_stopped') === 'true') {
                console.log("🚫 Upload Tour dimatikan karena sudah pernah ditutup.");
                return;
            }
            if (!mainTourDone) {
                console.log("✋ Tahan! Main Tour jalan. Upload masuk antrian.");
                queue.upload = true;
                return;
            }
            const uploadDriver = driver({
                showProgress: false, animate: true, allowClose: true, doneBtnText: 'Siap Upload',
                steps: [
                    { element: '#tour-upload-btn', popover: { title: '📂 Lengkapi Dokumen', description: 'Biodata dasar sudah aman! Sekarang klik tombol ini untuk <b>Memilih Jurusan</b> dan <b>Upload Berkas</b>.', side: "top", align: 'center' } }
                ],
                onDestroyed: () => {
                    localStorage.setItem('upload_tour_stopped', 'true');
                    console.log("💾 Preferensi disimpan: Upload Tour tidak akan muncul otomatis lagi.");
                }
            });
            uploadDriver.drive();
        };

        window.runExamTour = function() {
            if (localStorage.getItem('exam_tour_stopped') === 'true') {
                console.log("🚫 Exam Tour dimatikan karena sudah pernah ditutup.");
                return;
            }
            if (!mainTourDone) {
                console.log("✋ Tahan! Main Tour jalan. Exam masuk antrian.");
                queue.exam = true;
                return;
            }
            const examDriver = driver({
                showProgress: false, animate: true, allowClose: true, doneBtnText: 'Mengerti',
                steps: [
                    { element: '#tour-credentials-box', popover: { title: '🔑 Info Login Ujian', description: 'Gunakan Username & Password di kotak ini untuk masuk ke aplikasi E-Learning (CBT).', side: "left", align: 'center' } }
                ],
                onDestroyed: () => {
                    localStorage.setItem('exam_tour_stopped', 'true');
                    console.log("💾 Preferensi disimpan: Exam Tour tidak akan muncul otomatis lagi.");
                }
            });
            examDriver.drive();
        };

        window.resetTour = function() {
            localStorage.removeItem('main_tour_stopped');
            localStorage.removeItem('upload_tour_stopped');
            localStorage.removeItem('exam_tour_stopped');
            alert("Memori Tour direset! Refresh halaman untuk melihat tour lagi.");
        };

    });
</script>