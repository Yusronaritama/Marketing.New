<template>
    <transition name="cinematic-exit">
        <div v-if="show" class="fixed inset-0 z-[9999] bg-black font-mono overflow-hidden select-none flex flex-col justify-center items-center">
            
            <!-- CINEMATIC LETTERBOXING (Top and Bottom Black Bars) -->
            <!-- These slide out when loading is complete -->
            <div class="absolute top-0 left-0 w-full h-16 md:h-24 bg-black z-50 transition-transform duration-[1200ms] ease-in-out" :class="completed ? '-translate-y-full' : 'translate-y-0'"></div>
            <div class="absolute bottom-0 left-0 w-full h-16 md:h-24 bg-black z-50 transition-transform duration-[1200ms] ease-in-out" :class="completed ? 'translate-y-full' : 'translate-y-0'"></div>
            
            <!-- BACKGROUND: BATIK DARK CINEMATIC -->
            <div class="absolute inset-0 z-0 transition-opacity duration-[1200ms]" :class="completed ? 'opacity-0' : 'opacity-100'">
                <!-- Base Dark Blue Color -->
                <div class="absolute inset-0 bg-[#030917]"></div>
                
                <!-- Batik Pattern Overlay (SVG Data URI) -->
                <!-- Note: Anda bisa mengganti URL ini dengan url('/images/batik.png') jika punya file gambar batik sendiri -->
                <div class="absolute inset-0 opacity-[0.25]" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%233b82f6\' fill-opacity=\'1\'%3E%3Cpath d=\'M30 60C13.431 60 0 46.569 0 30 0 13.431 13.431 0 30 0c16.569 0 30 13.431 30 30 0 16.569-13.431 30-30 30zM30 50c11.046 0 20-8.954 20-20 0-11.046-8.954-20-20-20-11.046 0-20 8.954-20 20 0 11.046 8.954 20 20 20zM30 40c5.523 0 10-4.477 10-10 0-5.523-4.477-10-10-10-5.523 0-10 4.477-10 10 0 5.523 4.477 10 10 10z\'/%3E%3Cpath d=\'M15 15h30v30H15z\' opacity=\'0.2\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E'); background-size: 60px;"></div>
                
                <!-- Dark Vignette (Shadow at the edges) -->
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_transparent_0%,_#000000_90%)]"></div>
                
                <!-- Sci-Fi Scanlines -->
                <div class="absolute inset-0 bg-[linear-gradient(transparent_50%,_rgba(0,0,0,0.5)_50%)] bg-[length:100%_4px] pointer-events-none opacity-40"></div>
            </div>
            
            <!-- MAIN CONTENT CONTAINER -->
            <div class="relative z-30 flex flex-col items-center justify-center w-full h-full transition-all duration-[1200ms] ease-[cubic-bezier(0.25,1,0.5,1)]" :class="completed ? 'scale-[1.3] opacity-0 blur-lg' : 'scale-100 opacity-100 blur-0'">
                
                <!-- TOP DECORATIVE HUD -->
                <div class="absolute top-28 md:top-32 w-full flex justify-between px-8 md:px-16 text-[8px] md:text-[10px] text-blue-500/60 tracking-widest font-mono">
                    <div class="flex items-center gap-2">
                        <div class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></div>
                        <span>SYS.VER.2.0.25</span>
                    </div>
                    <div>REC // <span class="text-blue-300">ACTV</span></div>
                </div>

                <!-- LOGO WITH KEN BURNS ZOOM IN -->
                <div class="relative mb-8 md:mb-12 flex justify-center items-center">
                    <!-- Subtle pulsing glow behind logo -->
                    <div class="absolute w-[250px] h-[250px] bg-blue-600/20 blur-[80px] rounded-full animate-pulse-slow"></div>
                    <img 
                        :src="logoUrl" 
                        @error="$event.target.src='https://ubpkarawang.ac.id/wp-content/uploads/2021/08/Logo-UBP-Karawang.png'"
                        alt="UBP Logo" 
                        class="h-32 md:h-48 relative z-10 filter drop-shadow-[0_0_20px_rgba(37,99,235,0.6)] animate-cinematic-zoom"
                    >
                </div>
                
                <!-- CINEMATIC TYPOGRAPHY -->
                <div class="text-center mb-16 overflow-hidden">
                    <p class="text-white/80 text-[10px] md:text-[13px] tracking-[0.8em] md:tracking-[1em] uppercase font-light animate-text-reveal drop-shadow-[0_0_5px_rgba(255,255,255,0.3)]">
                        Universitas Buana Perjuangan Karawang
                    </p>
                </div>
                
                <!-- GAME-STYLE SEGMENTED PROGRESS HUD -->
                <div class="w-[280px] md:w-[450px] flex flex-col items-center mt-2">
                    
                    <!-- Text Info -->
                    <div class="w-full flex justify-between items-center px-1 mb-3">
                        <div class="flex items-center gap-2 md:gap-3">
                            <div class="w-2 h-2 rounded-full" :class="progress >= 100 ? 'bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.8)]' : 'bg-red-500 shadow-[0_0_8px_rgba(239,68,68,0.8)] animate-pulse'"></div>
                            <span class="text-[9px] md:text-xs tracking-[0.2em] md:tracking-[0.3em] font-medium transition-colors duration-300" :class="progress >= 100 ? 'text-green-400' : 'text-blue-400'">
                                {{ loadingStatusText }}
                            </span>
                        </div>
                        <div class="text-sm md:text-lg font-bold tracking-wider" :class="progress >= 100 ? 'text-green-300' : 'text-blue-200'">
                            {{ Math.floor(progress) }}%
                        </div>
                    </div>

                    <!-- Segmented Blocks (The Bar) -->
                    <div class="flex gap-1 w-full h-2 md:h-3">
                        <div v-for="i in 20" :key="i" 
                             class="flex-1 h-full transition-all duration-100 ease-out skew-x-[-15deg]"
                             :class="(progress / 5) >= i ? 'bg-blue-500 shadow-[0_0_10px_rgba(59,130,246,0.8)]' : 'bg-blue-900/30 border border-blue-800/30'">
                        </div>
                    </div>
                </div>
                
                <!-- BOTTOM DECORATIVE HUD -->
                <div class="absolute bottom-28 md:bottom-32 w-full flex justify-between px-8 md:px-16 text-[8px] md:text-[10px] text-blue-500/60 tracking-widest font-mono">
                    <div>DATA.SYNC <span class="animate-pulse">_</span></div>
                    <div>SECURE.CONN</div>
                </div>
                
            </div>
        </div>
    </transition>
</template>

<script>
export default {
    props: {
        isLoggedIn: { type: Boolean, default: false },
        logoUrl: { type: String, default: '/images/logo_ubp.png' }
    },
    data() {
        return {
            show: false,
            completed: false,
            progress: 0,
            timer: null
        }
    },
    computed: {
        loadingStatusText() {
            if (this.progress < 20) return "ESTABLISHING CONNECTION";
            if (this.progress < 45) return "DECRYPTING ASSETS";
            if (this.progress < 75) return "SYNCHRONIZING DATA";
            if (this.progress < 100) return "FINALIZING";
            return "SYSTEM READY";
        }
    },
    mounted() {
        if (this.isLoggedIn) {
            // Cek apakah user sudah melihat opening screen di sesi ini
            const hasSeen = sessionStorage.getItem('hasSeenOpeningScreen');
            
            if (!hasSeen) {
                // Tandai bahwa user sudah melihat opening screen
                sessionStorage.setItem('hasSeenOpeningScreen', 'true');
                
                this.show = true;
                document.body.style.overflow = 'hidden';
                
                // Game style loading (random jumps and stalls for realism)
                this.timer = setInterval(() => {
                    let increment = Math.random() * 8 + 1; 
                    
                    // 30% chance to stall loading briefly (very game-like)
                    if (Math.random() > 0.7 && this.progress < 90) {
                        increment = 0; 
                    }
                    
                    this.progress += increment;
                    
                    if (this.progress >= 99) {
                        this.progress = 100;
                        clearInterval(this.timer);
                        
                        // Cinematic pause at 100% before opening the gates
                        setTimeout(() => {
                            this.completed = true; // Triggers the zoom out & blur effect + letterbox slide out
                            
                            // Wait for transition to complete before destroying component
                            setTimeout(() => {
                                this.show = false;
                                document.body.style.overflow = 'auto';
                            }, 1200); 
                        }, 1000); // 1 second pause on SYSTEM READY
                    }
                }, 120);
            }
        }
    }
}
</script>

<style scoped>
/* Vue Exit Transition (Backup fade) */
.cinematic-exit-leave-active { transition: opacity 1.2s ease; }
.cinematic-exit-leave-to { opacity: 0; }

/* Slow Ken Burns Effect Zoom */
@keyframes cinematicZoom {
    0% { transform: scale(0.85); opacity: 0; filter: blur(10px) brightness(0); }
    15% { opacity: 1; filter: blur(0px) brightness(1); }
    100% { transform: scale(1.15); opacity: 1; }
}
.animate-cinematic-zoom {
    animation: cinematicZoom 15s ease-out forwards;
}

/* Cinematic Text Reveal */
@keyframes textReveal {
    0% { transform: translateY(100%); opacity: 0; letter-spacing: 0.5em; }
    100% { transform: translateY(0); opacity: 1; letter-spacing: 0.8em; } /* Default letter-spacing fallback */
}
/* Responsive letter spacing via media query since keyframes override tailwind classes */
@media (min-width: 768px) {
    @keyframes textReveal {
        0% { transform: translateY(100%); opacity: 0; letter-spacing: 0.8em; }
        100% { transform: translateY(0); opacity: 1; letter-spacing: 1em; }
    }
}
.animate-text-reveal {
    animation: textReveal 2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    animation-delay: 0.8s;
    opacity: 0;
}

@keyframes pulse-slow {
    0%, 100% { opacity: 0.15; }
    50% { opacity: 0.4; }
}
.animate-pulse-slow {
    animation: pulse-slow 5s ease-in-out infinite;
}
</style>
