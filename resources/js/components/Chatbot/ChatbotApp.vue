<template>
    <div class="flex flex-col xl:flex-row flex-1 bg-slate-50/30 rounded-b-3xl overflow-hidden h-full">
        
        <!-- KOLOM KIRI (Chat Interface) -->
        <div class="w-full xl:w-1/2 flex flex-col border-b xl:border-b-0 xl:border-r border-slate-200/50 bg-white/40">
            
            <!-- Chat Header -->
            <div class="p-5 border-b border-slate-100 bg-white/60 backdrop-blur-sm flex justify-between items-center">
                <div>
                    <h2 class="font-bold text-slate-800 flex items-center gap-2">
                        <i class="fas fa-sparkles text-blue-500"></i> PMB Assistant
                    </h2>
                    <p class="text-[10px] text-slate-500 font-mono mt-0.5">Tanyakan info prodi, jadwal, atau biaya</p>
                </div>
                <button @click="resetChat" class="text-slate-400 hover:text-blue-500 transition-colors" title="Clear Chat">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>

            <!-- Chat Messages Area -->
            <div class="flex-1 overflow-y-auto p-5 space-y-6" ref="chatContainer">
                
                <!-- Initial Bot Message -->
                <div class="flex gap-3">
                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center shrink-0 border border-blue-200">
                        <i class="fas fa-robot text-blue-500 text-sm"></i>
                    </div>
                    <div class="bg-white border border-slate-100 shadow-sm rounded-2xl rounded-tl-none p-4 max-w-[85%]">
                        <p class="text-sm text-slate-700">Halo {{ userName }}! 👋 Saya adalah asisten virtual PMB Universitas Buana Perjuangan.</p>
                        <p class="text-sm text-slate-700 mt-2">Ada yang bisa saya bantu terkait pendaftaran mahasiswa baru hari ini?</p>
                        
                        <!-- Quick Reply Chips -->
                        <div class="flex flex-wrap gap-2 mt-3">
                            <button @click="sendMessage('Brosur Teknik Informatika')" class="text-[10px] font-bold bg-slate-50 border border-slate-200 hover:border-blue-300 hover:bg-blue-50 text-slate-600 px-3 py-1.5 rounded-full transition-all">
                                🎓 Teknik Informatika
                            </button>
                            <button @click="sendMessage('Jadwal Ujian Gelombang 1')" class="text-[10px] font-bold bg-slate-50 border border-slate-200 hover:border-amber-300 hover:bg-amber-50 text-slate-600 px-3 py-1.5 rounded-full transition-all">
                                📅 Jadwal Ujian
                            </button>
                            <button @click="sendMessage('Berapa rincian biayanya?')" class="text-[10px] font-bold bg-slate-50 border border-slate-200 hover:border-emerald-300 hover:bg-emerald-50 text-slate-600 px-3 py-1.5 rounded-full transition-all">
                                💰 Rincian Biaya
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Dynamic Messages -->
                <div v-for="(msg, index) in messages" :key="index" :class="['flex gap-3', msg.isUser ? 'flex-row-reverse' : '']">
                    
                    <!-- Avatar -->
                    <div v-if="!msg.isUser" class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center shrink-0 border border-blue-200">
                        <i class="fas fa-robot text-blue-500 text-sm"></i>
                    </div>
                    <div v-else class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center shrink-0 border border-slate-700">
                        <i class="fas fa-user text-white text-sm"></i>
                    </div>

                    <!-- Bubble -->
                    <div :class="[
                        'p-4 max-w-[85%] shadow-sm',
                        msg.isUser 
                            ? 'bg-slate-800 text-white rounded-2xl rounded-tr-none' 
                            : 'bg-white border border-slate-100 text-slate-700 rounded-2xl rounded-tl-none'
                    ]">
                        <p class="text-sm leading-relaxed" v-html="msg.text"></p>
                    </div>
                </div>

                <!-- Typing Indicator -->
                <div v-if="isTyping" class="flex gap-3">
                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center shrink-0 border border-blue-200">
                        <i class="fas fa-robot text-blue-500 text-sm"></i>
                    </div>
                    <div class="bg-white border border-slate-100 shadow-sm rounded-2xl rounded-tl-none p-4 w-16 flex items-center justify-center gap-1">
                        <div class="w-1.5 h-1.5 bg-blue-400 rounded-full animate-bounce"></div>
                        <div class="w-1.5 h-1.5 bg-blue-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                        <div class="w-1.5 h-1.5 bg-blue-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                    </div>
                </div>

            </div>

            <!-- Chat Input Area -->
            <div class="p-4 bg-white/80 backdrop-blur-md border-t border-slate-100">
                <form @submit.prevent="handleFormSubmit" class="relative flex items-center">
                    <input 
                        type="text" 
                        v-model="inputText" 
                        placeholder="Ketik pertanyaan Anda..." 
                        class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-full py-3 pl-5 pr-12 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all shadow-inner"
                        :disabled="isTyping"
                    >
                    <button 
                        type="submit" 
                        class="absolute right-2 w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center hover:bg-blue-600 transition-transform hover:scale-105 active:scale-95 disabled:opacity-50 disabled:hover:scale-100"
                        :disabled="!inputText.trim() || isTyping"
                    >
                        <i class="fas fa-paper-plane text-[11px] -ml-0.5 mt-0.5"></i>
                    </button>
                </form>
            </div>
            
        </div>

        <!-- KOLOM KANAN (Dynamic Context Panel) -->
        <div class="w-full xl:w-1/2 bg-transparent relative flex flex-col p-6 lg:p-8">
            
            <!-- Default State (Empty / Idle) -->
            <div v-if="activeContext === 'idle'" class="flex-1 flex flex-col items-center justify-center text-center opacity-60">
                <div class="w-24 h-24 bg-slate-200 rounded-full flex items-center justify-center mb-6">
                    <i class="fas fa-id-card text-4xl text-slate-400"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-600 mb-2">Informasi Dinamis</h3>
                <p class="text-sm text-slate-500 max-w-xs">Data brosur prodi, jadwal ujian, atau rincian biaya akan muncul di sini sesuai dengan topik obrolan Anda.</p>
            </div>

            <!-- Context: Brosur Prodi -->
            <div v-else-if="activeContext === 'prodi_informatika'" class="animate-fade-in-up flex-1 flex flex-col">
                <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden flex-1 flex flex-col relative group">
                    <div class="h-32 bg-gradient-to-r from-blue-600 to-indigo-700 relative overflow-hidden">
                        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-20 mix-blend-overlay"></div>
                        <i class="fas fa-laptop-code absolute -bottom-5 -right-5 text-[100px] text-white/10 rotate-[-15deg]"></i>
                    </div>
                    
                    <div class="px-8 pb-8 pt-0 flex-1 flex flex-col">
                        <div class="w-20 h-20 rounded-2xl bg-white shadow-lg border-4 border-white flex items-center justify-center -mt-10 mb-4 z-10 mx-auto relative overflow-hidden">
                            <div class="absolute inset-0 bg-blue-50"></div>
                            <i class="fas fa-code text-3xl text-blue-600 relative z-10"></i>
                        </div>
                        
                        <div class="text-center mb-6">
                            <span class="text-[10px] font-bold text-blue-600 uppercase tracking-widest bg-blue-50 px-3 py-1 rounded-full border border-blue-100">Fakultas Ilmu Komputer</span>
                            <h3 class="text-2xl font-black text-slate-800 mt-3">Teknik Informatika</h3>
                            <p class="text-slate-500 text-sm mt-2">Mempelajari pengembangan perangkat lunak, kecerdasan buatan, dan keamanan siber.</p>
                        </div>
                        
                        <div class="space-y-3 mb-6 flex-1">
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100">
                                <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0"><i class="fas fa-check"></i></div>
                                <div class="text-sm font-medium text-slate-700">Akreditasi Baik Sekali</div>
                            </div>
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100">
                                <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center shrink-0"><i class="fas fa-briefcase"></i></div>
                                <div class="text-sm font-medium text-slate-700">Prospek Kerja: Software Engineer, Data Scientist</div>
                            </div>
                        </div>

                        <button class="w-full py-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold transition-colors shadow-lg hover:shadow-xl flex items-center justify-center gap-2">
                            <i class="fas fa-mouse-pointer"></i> Pilih Program Studi Ini
                        </button>
                    </div>
                </div>
            </div>

            <!-- Context: Jadwal Ujian -->
            <div v-else-if="activeContext === 'jadwal_ujian'" class="animate-fade-in-up flex-1 flex flex-col">
                <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-8 flex-1 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-amber-400/10 rounded-full blur-3xl -mr-20 -mt-20"></div>
                    
                    <div class="flex items-center gap-4 mb-8 relative z-10">
                        <div class="w-14 h-14 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center text-2xl shadow-inner border border-amber-200">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-slate-800">Timeline Pendaftaran</h3>
                            <p class="text-sm text-slate-500">Gelombang 1 - Tahun Akademik 2026/2027</p>
                        </div>
                    </div>

                    <div class="relative border-l-2 border-slate-100 ml-7 space-y-8 mt-4 relative z-10">
                        <div class="relative pl-8">
                            <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full bg-emerald-500 border-4 border-white shadow-sm"></div>
                            <p class="text-[10px] font-bold text-emerald-500 uppercase tracking-wider mb-1">Sekarang - 30 Mar</p>
                            <h4 class="font-bold text-slate-700">Pendaftaran & Pembayaran Formulir</h4>
                        </div>
                        <div class="relative pl-8">
                            <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full bg-blue-500 border-4 border-white shadow-sm animate-pulse"></div>
                            <p class="text-[10px] font-bold text-blue-500 uppercase tracking-wider mb-1">1 April 2026</p>
                            <h4 class="font-bold text-slate-800">Ujian CBT Online</h4>
                            <div class="mt-2 p-3 rounded-lg bg-blue-50 border border-blue-100">
                                <p class="text-xs text-blue-700"><i class="fas fa-info-circle mr-1"></i> Ujian dapat diakses melalui menu Ujian di dashboard ini pada tanggal tersebut.</p>
                            </div>
                        </div>
                        <div class="relative pl-8 opacity-50">
                            <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full bg-slate-300 border-4 border-white"></div>
                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">3 April 2026</p>
                            <h4 class="font-bold text-slate-700">Pengumuman Kelulusan</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Context: Rincian Biaya -->
            <div v-else-if="activeContext === 'rincian_biaya'" class="animate-fade-in-up flex-1 flex flex-col">
                <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-3xl shadow-xl border border-slate-700 p-8 flex-1 text-white relative overflow-hidden">
                    <div class="absolute -bottom-20 -right-20 w-80 h-80 bg-emerald-500/20 rounded-full blur-3xl pointer-events-none"></div>
                    
                    <div class="flex items-center justify-between mb-8 relative z-10">
                        <div>
                            <span class="text-[10px] font-bold text-emerald-400 uppercase tracking-widest bg-emerald-900/50 px-3 py-1 rounded-full border border-emerald-500/30">Estimasi</span>
                            <h3 class="text-2xl font-black mt-3">Biaya Pendidikan</h3>
                            <p class="text-slate-400 text-sm mt-1">Gelombang 1 - Reguler Pagi</p>
                        </div>
                        <i class="fas fa-wallet text-5xl text-white/10"></i>
                    </div>

                    <div class="space-y-4 relative z-10">
                        <div class="flex justify-between items-end border-b border-white/10 pb-4">
                            <div>
                                <h4 class="font-bold text-slate-200">Biaya Formulir</h4>
                                <p class="text-xs text-slate-400">Dibayar sekali di awal</p>
                            </div>
                            <div class="font-mono text-lg font-bold text-white">Rp 250.000</div>
                        </div>
                        <div class="flex justify-between items-end border-b border-white/10 pb-4">
                            <div>
                                <h4 class="font-bold text-slate-200">Biaya PKKMB & Almamater</h4>
                                <p class="text-xs text-slate-400">Dibayar setelah lulus ujian</p>
                            </div>
                            <div class="font-mono text-lg font-bold text-white">Rp 1.500.000</div>
                        </div>
                        <div class="flex justify-between items-end pb-2">
                            <div>
                                <h4 class="font-bold text-slate-200">Biaya SPP (Semester 1)</h4>
                                <p class="text-xs text-slate-400">Dapat dicicil 3x</p>
                            </div>
                            <div class="font-mono text-lg font-bold text-emerald-400">Rp 4.500.000</div>
                        </div>
                    </div>
                    
                    <div class="mt-8 p-4 rounded-xl bg-white/5 border border-white/10 flex items-start gap-3 relative z-10">
                        <i class="fas fa-lightbulb text-amber-400 mt-0.5"></i>
                        <p class="text-xs text-slate-300 leading-relaxed">Pendaftar Gelombang 1 mendapatkan <span class="text-amber-400 font-bold">Potongan SPP 20%</span> untuk semester pertama jika melakukan pelunasan sebelum ujian CBT.</p>
                    </div>
                </div>
            </div>

        </div>
        
    </div>

    <Teleport to="body">
        <!-- Chatbot Cinematic Intro -->
        <transition name="fade">
            <div v-if="showOpening" class="fixed inset-0 z-[99999] bg-[#0A1930] flex flex-col items-center justify-center overflow-hidden">
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_transparent_0%,_#000000_100%)]"></div>
                
                <!-- Background grid -->
                <div class="absolute inset-0 bg-[linear-gradient(to_right,#ffffff05_1px,transparent_1px),linear-gradient(to_bottom,#ffffff05_1px,transparent_1px)] bg-[size:32px_32px]"></div>

                <div class="relative z-10 flex flex-col items-center">
                    <div class="w-32 h-32 rounded-full border border-indigo-500/20 flex items-center justify-center animate-[spin_4s_linear_infinite] relative shadow-[0_0_50px_rgba(99,102,241,0.2)]">
                        <div class="absolute inset-0 rounded-full border-t-2 border-l-2 border-indigo-400 animate-pulse"></div>
                        <!-- Reverse spin on the icon so it stays upright -->
                        <i class="fas fa-robot text-5xl text-indigo-300 animate-[spin_4s_linear_infinite_reverse]"></i>
                    </div>
                    
                    <div class="mt-10 flex flex-col items-center text-center">
                        <h2 class="text-xl md:text-3xl font-black tracking-[0.4em] text-white uppercase">Initializing <span class="text-indigo-400">AI</span></h2>
                        
                        <!-- Game style loading bar -->
                        <div class="w-48 h-1 bg-slate-800 mt-6 rounded-full overflow-hidden">
                            <div class="h-full bg-indigo-500 rounded-full animate-[progress_1.5s_ease-in-out_forwards]"></div>
                        </div>
                        
                        <p class="mt-4 text-indigo-400/80 text-[10px] md:text-xs font-mono tracking-widest animate-pulse">CONNECTING TO KNOWLEDGE BASE...</p>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Tour Modal -->
        <transition name="fade-up">
            <div v-if="showTour" class="fixed inset-0 z-[99998] flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
                <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-3xl border border-indigo-500/30 shadow-2xl p-8 max-w-lg w-full relative overflow-hidden text-center">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none -mr-20 -mt-20"></div>
                    <div class="absolute bottom-0 left-0 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none -ml-20 -mb-20"></div>
                    
                    <div class="relative z-10">
                        <div class="w-20 h-20 bg-indigo-900/50 rounded-2xl mx-auto flex items-center justify-center border border-indigo-400/30 mb-6 shadow-[0_0_30px_rgba(99,102,241,0.3)] rotate-3">
                            <i class="fas fa-bolt text-4xl text-indigo-400"></i>
                        </div>
                        
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-900/50 border border-indigo-500/30 mb-4">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span class="text-[10px] font-bold text-indigo-200 tracking-widest uppercase">Tersedia 24/7</span>
                        </div>
                        
                        <h3 class="text-2xl font-black text-white mb-4 uppercase tracking-wide">Asisten Cerdas Anda</h3>
                        <p class="text-slate-300 leading-relaxed mb-8 text-sm">
                            Selamat datang! Chatbot AI ini dirancang untuk memberikan informasi secara <b>instan</b> kepada Anda. 
                            <br><br>
                            Anda tidak perlu lagi menunggu balasan dari admin manusia. Tanyakan seputar jadwal, kuota, brosur, atau rincian biaya kapan saja!
                        </p>
                        <button @click="showTour = false" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-8 py-4 rounded-full w-full shadow-[0_10px_20px_rgba(79,70,229,0.3)] hover:shadow-[0_10px_30px_rgba(79,70,229,0.5)] transition-all transform hover:-translate-y-1 uppercase tracking-widest text-sm">
                            Mulai Eksplorasi
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </Teleport>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';

const props = defineProps({
    userName: {
        type: String,
        default: 'Calon Mahasiswa'
    }
});

const messages = ref([]);
const inputText = ref('');
const isTyping = ref(false);
const chatContainer = ref(null);

// activeContext determines what's shown on the right panel
// 'idle', 'prodi_informatika', 'jadwal_ujian', 'rincian_biaya'
const activeContext = ref('idle');
const showOpening = ref(false);
const showTour = ref(false);

const scrollToBottom = async () => {
    await nextTick();
    if (chatContainer.value) {
        chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
    }
};

const handleFormSubmit = () => {
    if (!inputText.value.trim() || isTyping.value) return;
    sendMessage(inputText.value);
    inputText.value = '';
};

const sendMessage = (text) => {
    // 1. Add user message
    messages.value.push({ text: text, isUser: true });
    scrollToBottom();
    
    // 2. Simulate Bot thinking
    isTyping.value = true;
    
    setTimeout(() => {
        isTyping.value = false;
        processBotResponse(text);
        scrollToBottom();
    }, 1200 + Math.random() * 800); // Random delay 1.2s - 2s
};

const processBotResponse = (userText) => {
    const text = userText.toLowerCase();
    
    if (text.includes('informatika') || text.includes('it') || text.includes('software')) {
        activeContext.value = 'prodi_informatika';
        messages.value.push({ 
            text: "Wah, pilihan yang bagus! <b>Teknik Informatika</b> adalah salah satu prodi favorit di UBP. Fokus utamanya ada di Software Engineering dan AI. Silakan cek detail prospek kerja dan akreditasinya di panel kanan ya! 👉", 
            isUser: false 
        });
    } 
    else if (text.includes('jadwal') || text.includes('ujian') || text.includes('kapan') || text.includes('gelombang')) {
        activeContext.value = 'jadwal_ujian';
        messages.value.push({ 
            text: "Untuk Gelombang 1, Ujian CBT akan dilaksanakan secara online pada <b>1 April 2026</b>. Jangan lupa pastikan koneksi internet stabil ya. Saya sudah menampilkan timeline lengkapnya di sebelah kanan. 📅", 
            isUser: false 
        });
    }
    else if (text.includes('biaya') || text.includes('bayar') || text.includes('spp') || text.includes('harga')) {
        activeContext.value = 'rincian_biaya';
        messages.value.push({ 
            text: "Tentu. Rincian biaya untuk pendaftaran awal dan semester 1 mencakup Biaya Formulir, PKKMB, dan SPP. Khusus Gelombang 1 ada promo <b>Potongan SPP 20%</b> lho! Rincian lengkapnya bisa dilihat di kartu sebelah kanan. 💳", 
            isUser: false 
        });
    }
    else {
        messages.value.push({ 
            text: "Maaf, saat ini saya difokuskan untuk menjawab seputar <b>Brosur Prodi</b>, <b>Jadwal Ujian</b>, dan <b>Rincian Biaya</b>. Silakan gunakan tombol saran di atas atau ketik kata kunci tersebut. 😊", 
            isUser: false 
        });
    }
};

const resetChat = () => {
    if(confirm('Mulai ulang percakapan?')) {
        messages.value = [];
        activeContext.value = 'idle';
        inputText.value = '';
    }
};

onMounted(() => {
    const hasSeenIntro = sessionStorage.getItem('hasSeenChatbotIntro');
    
    if (!hasSeenIntro) {
        showOpening.value = true;
        document.body.style.overflow = 'hidden'; // Prevent scrolling during intro
        
        // After 2.5 seconds, hide opening and show tour
        setTimeout(() => {
            showOpening.value = false;
            
            // Wait for fade out transition before showing tour
            setTimeout(() => {
                showTour.value = true;
                sessionStorage.setItem('hasSeenChatbotIntro', 'true');
                document.body.style.overflow = 'auto'; // Restore scrolling
            }, 800); 
        }, 2500);
    }
});
</script>

<style scoped>
.animate-fade-in-up {
    animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes fadeInUp {
    0% {
        opacity: 0;
        transform: translateY(20px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Vue Transitions */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.8s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.fade-up-enter-active,
.fade-up-leave-active {
    transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}
.fade-up-enter-from,
.fade-up-leave-to {
    opacity: 0;
    transform: translateY(30px) scale(0.95);
}

/* Loading Bar Animation */
@keyframes progress {
    0% { width: 0%; }
    50% { width: 70%; }
    100% { width: 100%; }
}
</style>
