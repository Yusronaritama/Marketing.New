<template>
    <div class="max-w-5xl mx-auto mb-8 relative overflow-hidden rounded-2xl bg-white/60 backdrop-blur-xl border border-white/80 shadow-[0_8px_30px_rgb(225,29,72,0.06)] group transition-all duration-300 hover:shadow-[0_8px_30px_rgb(225,29,72,0.12)] animate-fade-in-down">
        
        <!-- Animated Background Orbs -->
        <div class="absolute -top-10 -right-10 w-40 h-40 bg-rose-400/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-orange-400/10 rounded-full blur-3xl animate-pulse" style="animation-delay: 2s;"></div>

        <div class="flex flex-col md:flex-row items-center justify-between gap-6 p-6 relative z-10">
            <!-- Left Info Section -->
            <div class="flex items-center gap-5 w-full md:w-auto">
                <!-- Icon Container -->
                <div class="relative shrink-0">
                    <div class="absolute inset-0 bg-rose-500 rounded-2xl blur opacity-30 group-hover:opacity-50 transition-opacity duration-300"></div>
                    <div class="relative h-14 w-14 rounded-2xl bg-gradient-to-br from-white to-rose-50 border border-rose-100 flex items-center justify-center shadow-sm">
                        <i class="fas fa-stopwatch text-2xl text-rose-500 transform group-hover:scale-110 transition-transform duration-300"></i>
                    </div>
                </div>
                
                <!-- Text Content -->
                <div class="flex flex-col">
                    <h3 class="text-xs font-black text-rose-500 uppercase tracking-[0.2em] mb-1.5 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                        Batas Waktu Sesi
                    </h3>
                    <p class="text-sm text-slate-600 font-medium leading-relaxed">
                        Selesaikan pendaftaran dalam <span class="text-slate-800 font-bold">3 hari</span>.<br>
                        Data akan <span class="bg-rose-100/80 text-rose-700 px-1.5 py-0.5 rounded text-xs font-bold border border-rose-200/50">direset otomatis</span> jika waktu habis.
                    </p>
                </div>
            </div>
            
            <!-- Right Timer Section -->
            <div class="flex items-center gap-2 md:gap-4 bg-white/50 p-3 rounded-2xl border border-slate-100 shadow-inner">
                <!-- Hours -->
                <div class="flex flex-col items-center min-w-[60px]">
                    <div class="bg-white border border-slate-100 rounded-xl w-12 h-14 md:w-14 md:h-16 flex items-center justify-center shadow-[0_2px_10px_rgba(0,0,0,0.03)] mb-1">
                        <span class="text-2xl md:text-3xl font-black text-slate-700 tabular-nums">{{ hours }}</span>
                    </div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Jam</span>
                </div>
                
                <span class="text-slate-300 font-black text-xl md:text-2xl pb-4 animate-pulse">:</span>
                
                <!-- Minutes -->
                <div class="flex flex-col items-center min-w-[60px]">
                    <div class="bg-white border border-slate-100 rounded-xl w-12 h-14 md:w-14 md:h-16 flex items-center justify-center shadow-[0_2px_10px_rgba(0,0,0,0.03)] mb-1">
                        <span class="text-2xl md:text-3xl font-black text-slate-700 tabular-nums">{{ minutes }}</span>
                    </div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Mnt</span>
                </div>
                
                <span class="text-slate-300 font-black text-xl md:text-2xl pb-4 animate-pulse" style="animation-delay: 0.5s;">:</span>
                
                <!-- Seconds -->
                <div class="flex flex-col items-center min-w-[60px]">
                    <div class="bg-white border border-rose-100 rounded-xl w-12 h-14 md:w-14 md:h-16 flex items-center justify-center shadow-[0_4px_15px_rgba(225,29,72,0.1)] mb-1 relative overflow-hidden group-hover:border-rose-300 transition-colors duration-300">
                        <span class="text-2xl md:text-3xl font-black text-rose-600 tabular-nums relative z-10">{{ seconds }}</span>
                        <!-- Filling Background Effect -->
                        <div class="absolute bottom-0 left-0 w-full bg-rose-50 transition-all duration-1000 ease-linear" :style="{ height: (Number(seconds) / 60 * 100) + '%' }"></div>
                    </div>
                    <span class="text-[10px] font-bold text-rose-400 uppercase tracking-wider">Dtk</span>
                </div>
            </div>
        </div>

        <!-- Sleek Bottom Progress Bar -->
        <div class="absolute bottom-0 left-0 h-1.5 bg-slate-100 w-full overflow-hidden">
            <div class="h-full bg-gradient-to-r from-rose-400 via-rose-500 to-orange-500 transition-all duration-1000 ease-linear relative" :style="{ width: percent + '%' }">
                <div class="absolute top-0 right-0 bottom-0 w-10 bg-gradient-to-r from-transparent to-white opacity-50 blur-[2px]"></div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        initialSeconds: {
            type: Number,
            required: true
        }
    },
    data() {
        return {
            totalOrigin: 259200, // 3 days in seconds
            remaining: this.initialSeconds,
            hours: '00',
            minutes: '00',
            seconds: '00',
            percent: 100,
            interval: null,
            paused: false
        }
    },
    mounted() {
        this.initTimer();
    },
    beforeUnmount() {
        if (this.interval) clearInterval(this.interval);
        window.removeEventListener('pause-timer', this.handlePause);
        window.removeEventListener('resume-timer', this.handleResume);
    },
    methods: {
        handlePause() {
            this.paused = true;
        },
        handleResume() {
            this.paused = false;
        },
        initTimer() {
            if (this.remaining <= 0) this.remaining = this.totalOrigin;
            this.updateDisplay();
            
            window.addEventListener('pause-timer', this.handlePause);
            window.addEventListener('resume-timer', this.handleResume);

            this.interval = setInterval(() => {
                if (!this.paused) {
                    this.remaining--;
                    this.updateDisplay();
                    
                    let p = (this.remaining / this.totalOrigin) * 100;
                    this.percent = p < 0 ? 0 : p;

                    if (this.remaining <= 0) {
                        clearInterval(this.interval);
                        this.timeIsUp();
                    }
                }
            }, 1000);
        },
        updateDisplay() {
            if (this.remaining < 0) this.remaining = 0;
            const h = Math.floor(this.remaining / 3600);
            const m = Math.floor((this.remaining % 3600) / 60);
            const s = this.remaining % 60;
            this.hours = String(h).padStart(2, '0');
            this.minutes = String(m).padStart(2, '0');
            this.seconds = String(s).padStart(2, '0');
        },
        timeIsUp() {
            if (window.Swal) {
                window.Swal.fire({
                    icon: 'error', 
                    title: 'Waktu Habis!',
                    text: 'Sesi pendaftaran Anda telah berakhir dan data akan direset.',
                    allowOutsideClick: false, 
                    confirmButtonText: 'Muat Ulang', 
                    confirmButtonColor: '#e11d48'
                }).then(() => { 
                    window.location.reload(); 
                });
            } else {
                alert('Waktu Habis! Sesi pendaftaran Anda telah berakhir dan data akan direset.');
                window.location.reload();
            }
        }
    }
}
</script>
