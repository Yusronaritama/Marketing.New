Changelog - 09 Oktober 2025
Versi: 1.4.0
Tipe: Perubahan Tata Letak (Layout)

Ringkasan
Merombak tata letak konten utama di halaman dashboard mahasiswa untuk memberikan fokus yang lebih besar pada progres pendaftaran dan memindahkan tombol aksi utama.

File yang Diubah
resources/views/mahasiswa/dashboard.blade.php

Detail Perubahan
1. "Progres Pendaftaran" Menjadi Kartu Utama
Apa: Kartu "Progres Pendaftaran Anda" (checklist) sekarang menjadi komponen utama yang lebih besar, menempati 2/3 lebar halaman.

Kenapa: Untuk menjadikan alur pendaftaran sebagai fokus utama bagi pengguna saat mereka pertama kali masuk ke dashboard, sesuai permintaan.

2. Tombol Aksi Dipindahkan
Apa: Tombol "Mulai Registrasi Ulang" yang sebelumnya berada di kartu terpisah, sekarang dipindahkan ke bagian bawah kartu "Progres Pendaftaran Anda".

Kenapa: Untuk menghubungkan secara logis dan visual antara progres yang ditampilkan dengan aksi utama yang harus diambil pengguna.

3. Penambahan Kartu "Informasi Penting"
Apa: Kartu "Proses Registrasi Ulang" yang lama telah dihilangkan dan diganti dengan kartu baru yang lebih kecil bernama "Informasi Penting" di sebelah kanan.

Kenapa: Untuk memberikan ruang bagi pengumuman atau catatan penting (seperti batas akhir pendaftaran) tanpa mengganggu alur utama.

Catatan Tambahan
Perubahan ini murni pada sisi tampilan dan tata letak untuk meningkatkan pengalaman pengguna. Tidak ada perubahan fungsionalitas atau logika backend.

Changelog - 08 Oktober 2025
Versi: 1.2.1
Tipe: Perbaikan Bug & Penambahan Fitur

Ringkasan
Melakukan beberapa perbaikan penting terkait alur login, logout, dan menambahkan rute dasar untuk fitur biodata.

File yang Diubah/Dibuat
resources/views/mahasiswa/dashboard.blade.php

routes/web.php

Detail Perubahan
1. Perbaikan Alur Logout
Apa: Semua tombol/link logout di dashboard.blade.php (tampilan desktop & mobile) telah diubah untuk menggunakan <form> dengan method POST dan token @csrf.

Kenapa: Untuk mengatasi error 419 Page Expired dan perilaku aneh saat logout. Ini adalah cara standar dan aman untuk melakukan logout di Laravel.

2. Penambahan Rute Biodata
Apa: Menambahkan rute baru GET /mahasiswa/biodata dengan nama mahasiswa.biodata.create di dalam file routes/web.php.

Kenapa: Untuk memperbaiki error Route not found saat link "Biodata" di dashboard diklik. Rute ini sekarang sudah terhubung ke BiodataController.

Catatan untuk Tim Backend
Penting: Pastikan BiodataController dan ProfileController sudah dibuat dan memiliki method yang sesuai (create untuk Biodata, edit untuk Profile) agar rute yang baru ditambahkan tidak menyebabkan error.

Alur redirect setelah login yang berdasarkan role (admin/mahasiswa) ada di dalam AuthenticatedSessionController.php. Mohon periksa kembali logika if-else di sana jika ada masalah redirect.

Changelog - 11 Oktober 2025
Versi: 2.3.1
Tipe: Peningkatan Pengalaman Pengguna (UX Enhancement)

Ringkasan
Mengimplementasikan animasi transisi fade-out/fade-in saat berpindah antar halaman (Dashboard dan Biodata) menggunakan metode HTML & CSS murni untuk menghindari masalah cache server. Ini menghilangkan kesan "berkedip" (blink) dan menciptakan pengalaman navigasi yang lebih halus (smooth).

File yang Diubah
resources/views/mahasiswa/dashboard.blade.php

resources/views/mahasiswa/biodata.blade.php

Detail Perubahan
1. Penambahan Animasi CSS
Apa: Menambahkan beberapa baris kode CSS @keyframes untuk fadeIn dan fadeOut di dalam tag <style> pada kedua file.

Kenapa: Untuk mendefinisikan efek visual muncul dan meredup yang lembut.

2. Penerapan Kelas Animasi
Apa: - Menambahkan kelas fade-in pada tag <main> di kedua file agar konten halaman muncul dengan animasi.

Menambahkan kelas page-link pada semua link navigasi <a> di dalam <header>.

Kenapa: Untuk menerapkan animasi masuk dan memberikan target yang jelas bagi JavaScript untuk animasi keluar.

3. Penambahan Logika JavaScript
Apa: Menambahkan sebuah blok skrip JavaScript baru di bagian bawah <body> yang:

Menargetkan semua link dengan kelas page-link.

Saat link diklik, ia akan mencegah perpindahan halaman instan.

Menerapkan kelas fade-out pada <body>.

Menunggu 400 milidetik sebelum mengarahkan browser ke halaman tujuan.

Kenapa: Untuk memberikan waktu bagi animasi fadeOut agar selesai berjalan, sehingga menciptakan ilusi perpindahan yang halus.

Catatan Tambahan
Metode ini menggunakan pendekatan HTML murni untuk menghindari ketergantungan pada sistem layout Blade yang sebelumnya mengalami masalah cache di lingkungan cPanel.

Efek akan terlihat konsisten saat bernavigasi antara halaman Dashboard dan Biodata.