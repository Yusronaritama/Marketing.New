{{-- File: resources/views/mahasiswa/dashboard/partials/welcome.blade.php --}}
<div class="max-w-7xl mx-auto mb-10 text-white relative text-center"
     x-data="{
        greeting: '',
        icon: '',
        init() {
            const hour = new Date().getHours();
            
            // PERBAIKAN DI SINI:
            // Ubah batas bawah dari 4 menjadi 0 agar jam 1 pagi masuk kategori 'Selamat Pagi'
            if (hour >= 0 && hour < 10) { 
                this.greeting = 'Selamat Pagi';
                this.icon = '🌅';
            } else if (hour >= 10 && hour < 15) {
                this.greeting = 'Selamat Siang';
                this.icon = '☀️';
            } else if (hour >= 15 && hour < 18) {
                this.greeting = 'Selamat Sore';
                this.icon = '🌇';
            } else {
                this.greeting = 'Selamat Malam';
                this.icon = '🌙';
            }
        }
     }">
    
    <p class="text-blue-200 font-medium mb-1 text-lg flex justify-center items-center gap-2 animate-fade-in-up">
        <span x-text="icon"></span> <span x-text="greeting"></span>, {{ Auth::user()->name }}!
    </p>
    
    <h1 class="text-3xl md:text-5xl font-extrabold tracking-tight text-white drop-shadow-2xl mt-2">
        Portal Penerimaan Mahasiswa Baru
    </h1>
</div>