<div class="w-full relative z-20"
     id="wave-monitor-widget"
     x-data="{
        wave: 0,
        monthName: '',
        startDate: '',
        endDate: '',
        progress: 0,
        isOpen: true,
        daysLeft: 0,
        statusText: '',
        showHint: false, 
        groupList: {{ $listGroupWA->toJson() }},
        currentLink: '',
        currentName: '',
        currentDesc: '',

        init() {
            const now = new Date();
            let year = now.getFullYear();
            const monthIndex = now.getMonth();
            const today = now.getDate();

            this.monthName = now.toLocaleDateString('id-ID', { month: 'long' });

            if (monthIndex === 11 || monthIndex === 0) { 
                this.isOpen = true;
                this.wave = 1;
                this.statusText = 'SYSTEM ACTIVE (LONG WAVE)';

                let startYear = (monthIndex === 11) ? year : year - 1;
                let endYear = (monthIndex === 11) ? year + 1 : year;

                const wave1Start = new Date(startYear, 11, 11); 
                const wave1End = new Date(endYear, 0, 31); 

                const totalDuration = wave1End - wave1Start;
                const elapsedTime = now - wave1Start;
                const diffTime = wave1End - now;
                this.daysLeft = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

                let pct = Math.round((elapsedTime / totalDuration) * 100);
                this.progress = Math.max(0, Math.min(100, pct));

                this.startDate = wave1Start.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: '2-digit' }).toUpperCase();
                this.endDate = wave1End.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: '2-digit' }).toUpperCase();

            } else if (monthIndex === 10) { 
                this.isOpen = false;
                this.wave = '-';
                this.statusText = 'SYSTEM OFFLINE';
                
                const nextOpen = new Date(year, 11, 11);
                this.startDate = 'CLOSED';
                this.endDate = 'NEXT: 11 DEC';
                
                const diffTime = Math.abs(nextOpen - now);
                this.daysLeft = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); 
                this.progress = 100;
            } else {
                this.isOpen = true;
                this.wave = monthIndex + 1; 
                this.statusText = 'SYSTEM ACTIVE';
                const firstDay = new Date(year, monthIndex, 1);
                const lastDay = new Date(year, monthIndex + 1, 0); 
                const totalDays = lastDay.getDate();

                this.startDate = firstDay.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' }).toUpperCase();
                this.endDate = lastDay.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: '2-digit' }).toUpperCase();
                
                this.daysLeft = totalDays - today;
                this.progress = Math.round((today / totalDays) * 100);
            }

            let foundGroup = this.groupList.find(g => g.gelombang == this.wave);
            if (foundGroup) {
                this.currentLink = foundGroup.link_group;
                this.currentName = foundGroup.nama_gelombang;
                this.currentDesc = foundGroup.deskripsi || 'Official Candidate Group';
            }
        }
     }"
     x-init="init()">
     
    <div class="bg-[#0A1930]/40 backdrop-blur-xl border border-white/10 rounded-2xl shadow-inner overflow-hidden relative">
        <div class="absolute inset-0 opacity-[0.03]" style="background-image: linear-gradient(#000 1px, transparent 1px); background-size: 20px 20px;"></div>

        <div class="relative z-10 p-5">
            <div class="flex justify-between items-center mb-5 border-b border-white/10 pb-4">
                <div>
                    <span class="text-[10px] font-mono text-blue-200 uppercase">Current Phase</span>
                    <div class="text-xl font-black text-white tracking-widest uppercase">
                        GELOMBANG <span class="text-blue-300" x-text="wave"></span>
                    </div>
                </div>
                <div class="px-3 py-1.5 rounded-lg border backdrop-blur-sm shadow-sm"
                     :class="isOpen ? 'bg-green-500/20 border-green-400/30 text-green-300' : 'bg-red-500/20 border-red-400/30 text-red-300'">
                    <span class="text-[10px] font-bold font-mono uppercase tracking-wide flex items-center gap-1.5">
                        <i class="fas" :class="isOpen ? 'fa-satellite' : 'fa-ban'"></i>
                        <span x-text="statusText"></span>
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3 mb-5 text-center">
                <div class="bg-white/5 rounded-xl p-3 border border-white/10 shadow-sm">
                    <span class="block text-[8px] text-blue-200 uppercase font-mono mb-1">Start</span>
                    <span class="block text-xs font-bold text-white" x-text="startDate"></span>
                </div>
                <div class="bg-white/5 rounded-xl p-3 border border-white/10 shadow-sm">
                    <span class="block text-[8px] text-blue-200 uppercase font-mono mb-1">End</span>
                    <span class="block text-xs font-bold text-white" x-text="endDate"></span>
                </div>
                <div class="bg-white/5 rounded-xl p-3 border border-white/10 shadow-sm">
                    <span class="block text-[8px] text-blue-200 uppercase font-mono mb-1">Remaining</span>
                    <span class="block text-xs font-bold text-blue-300" x-text="daysLeft + ' DAYS'"></span>
                </div>
            </div>

            <div class="mb-6">
                <div class="flex justify-between text-[10px] text-blue-200 mb-2 font-mono">
                    <span>TIMELINE</span>
                    <span x-text="progress + '%'"></span>
                </div>
                <div class="w-full bg-white/10 h-2.5 rounded-full overflow-hidden shadow-inner">
                    <div class="h-full rounded-full transition-all duration-1000 shadow-[0_0_10px_rgba(59,130,246,0.5)]"
                         :class="isOpen ? 'bg-gradient-to-r from-blue-400 to-indigo-500' : 'bg-red-500'"
                         :style="'width: ' + progress + '%'"></div>
                </div>
            </div>

            <template x-if="currentLink">
                <a :href="currentLink" target="_blank" 
                   class="flex items-center justify-between bg-green-500/10 border border-green-400/30 rounded-xl p-3 shadow-sm hover:shadow-md hover:bg-green-500/20 hover:border-green-400/50 transition-all group">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-green-500/20 flex items-center justify-center text-green-400 border border-green-400/30">
                            <i class="fab fa-whatsapp text-lg"></i>
                        </div>
                        <div class="overflow-hidden">
                            <div class="text-[9px] font-mono text-green-300/80 uppercase">Secure Channel</div>
                            <div class="text-sm font-bold text-green-100 truncate w-32" x-text="currentName"></div>
                        </div>
                    </div>
                    <i class="fas fa-external-link-alt text-green-400 text-sm group-hover:translate-x-1 transition-transform"></i>
                </a>
            </template>

            <div class="mt-4 text-center">
                <p class="text-[9px] text-blue-200/50 font-mono">
                    System Cycle: 10 Waves (Dec - Oct)
                </p>
            </div>
        </div>
    </div>
</div>