<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GIS Command Center - PMB UBP</title>
    
    {{-- 1. TAILWIND CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: { 900: '#0f172a', 800: '#1e293b', 700: '#334155' },
                        neon: { blue: '#3b82f6', cyan: '#06b6d4', purple: '#8b5cf6', green: '#10b981' }
                    },
                    animation: {
                        'spin-slow': 'spin 3s linear infinite',
                    }
                }
            }
        }
    </script>
    
    {{-- 2. FONT AWESOME --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    {{-- 3. ALPINE JS --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- 4. HIGHCHARTS (Core + Map Module) --}}
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/maps/modules/map.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>
    {{-- Data Peta Indonesia --}}
    <script src="https://code.highcharts.com/mapdata/countries/id/id-all.js"></script>

    <style>
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
        
        .glass-dark {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .glass-panel-heavy {
            background: rgba(15, 23, 42, 0.85); 
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
        }

        .map-bg {
            background: radial-gradient(circle at center, #1e293b 0%, #0f172a 100%);
        }
        
        /* === TACTICAL ANIMATIONS === */
        .crosshair-container {
            position: absolute;
            pointer-events: none;
            transform: translate(-50%, -50%);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 40;
        }

        .reticle-box {
            width: 80px; height: 80px;
            border: 2px solid rgba(6, 182, 212, 0.3);
            position: relative;
            animation: snap-in 0.2s cubic-bezier(0, 0, 0.2, 1) forwards;
        }

        .reticle-corner {
            position: absolute; width: 15px; height: 15px;
            border: 2px solid #06b6d4; transition: all 0.3s;
        }
        .tl { top: -2px; left: -2px; border-right: none; border-bottom: none; }
        .tr { top: -2px; right: -2px; border-left: none; border-bottom: none; }
        .bl { bottom: -2px; left: -2px; border-right: none; border-top: none; }
        .br { bottom: -2px; right: -2px; border-left: none; border-top: none; }

        .reticle-ring {
            position: absolute; width: 100%; height: 100%;
            border: 1px dashed rgba(6, 182, 212, 0.6);
            border-radius: 50%;
            animation: spin 4s linear infinite; 
        }

        .reticle-dot {
            width: 6px; height: 6px;
            background-color: #ef4444;
            border-radius: 50%;
            box-shadow: 0 0 10px #ef4444;
        }

        .lock-text {
            position: absolute; bottom: -25px;
            color: #06b6d4; font-family: monospace;
            font-size: 10px; font-weight: bold;
            letter-spacing: 2px; text-shadow: 0 0 5px #06b6d4;
            white-space: nowrap;
        }

        @keyframes snap-in {
            0% { opacity: 0; transform: scale(1.2); }
            100% { opacity: 1; transform: scale(1); }
        }
    </style>
</head>
<body class="bg-dark-900 text-slate-200 font-sans overflow-hidden" x-data="gisSystem()">

    {{-- NAVBAR --}}
    <nav class="fixed top-0 left-0 w-full z-40 glass-dark border-b border-white/5 px-6 py-3 flex justify-between items-center h-16">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-cyan-500 flex items-center justify-center shadow-lg shadow-blue-500/30">
                <i class="fas fa-globe-asia text-white text-lg"></i>
            </div>
            <div>
                <h1 class="font-bold text-lg leading-tight tracking-wide text-white">
                    GIS <span class="text-cyan-400">COMMAND</span>
                </h1>
                <p class="text-[10px] text-slate-400 uppercase tracking-widest">Tactical Data View</p>
            </div>
        </div>
        
        {{-- MODE SWITCHER (PETA vs KELULUSAN) --}}
        <div class="hidden md:flex bg-slate-800/80 rounded-lg p-1 border border-white/10">
            <button @click="switchMode('peta')" 
                :class="currentMode === 'peta' ? 'bg-cyan-600 text-white shadow-lg shadow-cyan-500/30' : 'text-slate-400 hover:text-white'"
                class="px-4 py-1.5 rounded-md text-xs font-bold uppercase tracking-wider transition-all">
                <i class="fas fa-map-marked-alt mr-2"></i> Peta Sebaran
            </button>
            <button @click="switchMode('chart')" 
                :class="currentMode === 'chart' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-500/30' : 'text-slate-400 hover:text-white'"
                class="px-4 py-1.5 rounded-md text-xs font-bold uppercase tracking-wider transition-all">
                <i class="fas fa-chart-bar mr-2"></i> Data Kelulusan
            </button>
            <button @click="switchMode('rekap')" 
                :class="currentMode === 'rekap' ? 'bg-purple-600 text-white shadow-lg shadow-purple-500/30' : 'text-slate-400 hover:text-white'"
                class="px-4 py-1.5 rounded-md text-xs font-bold uppercase tracking-wider transition-all">
                <i class="fas fa-table mr-2"></i> Rekap Tahunan
            </button>
            <button @click="switchMode('pembayaran')" 
                :class="currentMode === 'pembayaran' ? 'bg-yellow-600 text-white shadow-lg shadow-yellow-500/30' : 'text-slate-400 hover:text-white'"
                class="px-4 py-1.5 rounded-md text-xs font-bold uppercase tracking-wider transition-all">
                <i class="fas fa-chart-pie mr-2"></i> Grafik Pembayaran
            </button>
        </div>

        <div class="flex items-center gap-4">
            <select x-model="selectedYear" @change="refreshData()" class="bg-slate-800 border border-white/10 text-slate-300 text-xs rounded-lg px-3 py-2 outline-none focus:border-cyan-500">
                @foreach($availableYears as $year)
                    <option value="{{ $year }}">{{ $year }}</option>
                @endforeach
            </select>
            
            <a href="{{ route('admin.gis.capacity') }}" 
               class="bg-indigo-600 hover:bg-indigo-500 border border-white/10 text-white text-sm font-bold px-5 py-2 rounded-full transition flex items-center gap-2 shadow-lg shadow-indigo-500/30">
                <i class="fas fa-database"></i> 
                <span>Cek Kapasitas & Kuota</span>
            </a>

            <a href="{{ route('mahasiswa.dashboard') }}" class="bg-slate-800 hover:bg-slate-700 border border-white/10 text-slate-200 text-sm font-medium px-5 py-2 rounded-full transition flex items-center gap-2 hover:shadow-lg hover:shadow-cyan-500/20">
                <i class="fas fa-arrow-left text-cyan-400"></i> 
                <span>Exit</span>
            </a>
        </div>
    </nav>

    {{-- MAIN CONTENT --}}
    <div class="flex h-screen pt-16">
        
        {{-- SIDEBAR STATISTIK (Hanya Muncul di Mode Peta) --}}
        <div x-show="currentMode === 'peta'" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-x-10"
             x-transition:enter-end="opacity-100 translate-x-0"
             class="w-1/4 h-full glass-dark border-r border-white/5 overflow-y-auto p-6 z-10 hidden md:block">
            
            <div class="mb-8">
                <h2 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4">Statistik Global</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gradient-to-br from-slate-800 to-slate-900 p-4 rounded-2xl border border-white/5 relative overflow-hidden group">
                        <div class="text-3xl font-bold text-white mb-1" x-text="totalMaba">0</div>
                        <div class="text-[10px] text-blue-400 font-bold uppercase tracking-wider">Pendaftar</div>
                    </div>
                    <div class="bg-gradient-to-br from-slate-800 to-slate-900 p-4 rounded-2xl border border-white/5 relative overflow-hidden group">
                        <div class="text-3xl font-bold text-white mb-1" x-text="totalProvinsi">0</div>
                        <div class="text-[10px] text-emerald-400 font-bold uppercase tracking-wider">Provinsi</div>
                    </div>
                </div>
            </div>

            <div class="mb-8">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <i class="fas fa-city text-cyan-500"></i> Top Kota
                </h3>
                <div class="space-y-2">
                    <template x-for="(item, index) in topKota" :key="index">
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-800/50 border border-white/5 hover:bg-slate-700/50 transition">
                            <span class="text-xs font-medium text-slate-300 truncate w-32" x-text="formatName(item.kota_kabupaten_asal)"></span>
                            <span class="font-bold text-cyan-400 text-xs" x-text="item.total"></span>
                        </div>
                    </template>
                </div>
            </div>

            <div>
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <i class="fas fa-map text-purple-500"></i> Top Provinsi
                </h3>
                <div class="space-y-1">
                    <template x-for="(item, index) in topProvinsi.slice(0, 8)" :key="index">
                        <div @click="focusRegion(item.provinsi_asal)" 
                             class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-cyan-500/10 cursor-pointer group transition border border-transparent hover:border-cyan-500/50">
                            <div class="flex items-center gap-3">
                                <div class="w-1.5 h-1.5 rounded-full bg-slate-600 group-hover:bg-cyan-400 shadow-lg transition"></div>
                                <span class="text-sm text-slate-400 group-hover:text-cyan-200 font-medium transition truncate w-32" x-text="formatName(item.provinsi_asal)"></span>
                            </div>
                            <span class="text-xs font-bold text-slate-500 group-hover:text-cyan-400" x-text="item.total"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- AREA UTAMA (PETA / CHART / REKAP) --}}
        {{-- PERBAIKAN: Semua view (Map, Chart, Payment, Rekap) harus ada di dalam div flex-1 ini --}}
        <div class="flex-1 h-full relative map-bg overflow-hidden">
            
            {{-- Loading Spinner --}}
            <div x-show="isLoading" class="absolute inset-0 z-50 flex flex-col items-center justify-center bg-dark-900/90 backdrop-blur-sm" style="display: none;">
                <div class="w-16 h-16 border-4 border-cyan-500/30 border-t-cyan-500 rounded-full animate-spin"></div>
                <p class="text-cyan-400 font-medium mt-4 tracking-widest text-sm animate-pulse">PROCESSING DATA...</p>
            </div>

            {{-- 1. CONTAINER PETA --}}
            <div x-show="currentMode === 'peta'" class="w-full h-full relative" style="display: none;">
                {{-- Crosshair Animation --}}
                <div x-show="isTargeting" class="crosshair-container" style="display: none;"
                     :style="`left: ${crosshairPos.x}px; top: ${crosshairPos.y}px;`">
                     <div class="reticle-box">
                         <div class="reticle-corner tl"></div><div class="reticle-corner tr"></div>
                         <div class="reticle-corner bl"></div><div class="reticle-corner br"></div>
                         <div class="reticle-ring"></div>
                     </div>
                     <div class="reticle-dot"></div>
                     <div class="lock-text">LOCKING...</div>
                </div>

                <div id="map-container" class="w-full h-full"></div>

                {{-- Legend Peta --}}
                <div class="absolute bottom-8 right-8 glass-dark p-4 rounded-2xl shadow-2xl max-w-xs border border-white/10">
                    <h4 class="font-bold text-[10px] text-slate-400 uppercase tracking-widest mb-3 border-b border-white/10 pb-2">Density Scan</h4>
                    <div class="space-y-2">
                        <div class="flex items-center gap-3"><span class="w-3 h-3 bg-cyan-400 rounded-full shadow-[0_0_10px_cyan]"></span> <span class="text-xs text-slate-300">High Sig (>50)</span></div>
                        <div class="flex items-center gap-3"><span class="w-3 h-3 bg-blue-600 rounded-full"></span> <span class="text-xs text-slate-300">Mid Sig (10-50)</span></div>
                        <div class="flex items-center gap-3"><span class="w-3 h-3 bg-slate-700 rounded-full"></span> <span class="text-xs text-slate-400">Low Sig (<10)</span></div>
                    </div>
                </div>
            </div>

            {{-- 2. CONTAINER CHART KELULUSAN --}}
            <div x-show="currentMode === 'chart'" class="w-full h-full p-10 flex flex-col items-center justify-center" style="display: none;">
                
                {{-- KOTAK TOTAL BESAR --}}
                <div class="mb-4 bg-emerald-500/20 border border-emerald-500/50 px-8 py-4 rounded-2xl flex flex-col items-center shadow-[0_0_30px_rgba(16,185,129,0.2)]">
                    <span class="text-emerald-400 text-xs font-bold uppercase tracking-widest mb-1">Total Mahasiswa Lulus</span>
                    <span class="text-4xl font-black text-white" x-text="chartTotal">0</span>
                </div>

                <div class="w-full h-[80%] glass-panel-heavy rounded-3xl p-6 relative">
                    <h3 class="text-xl font-bold text-white mb-2 flex items-center gap-2">
                        <i class="fas fa-graduation-cap text-emerald-400"></i> STATISTIK KELULUSAN
                    </h3>
                    <div id="chart-container" class="w-full h-[90%]"></div>
                </div>
            </div>
            
            {{-- 3. CONTAINER CHART PEMBAYARAN --}}
            <div x-show="currentMode === 'pembayaran'" class="w-full h-full p-6 flex flex-col" style="display: none;">
                {{-- KOTAK TOTAL --}}
                <div class="grid grid-cols-2 gap-4 mb-6 w-full max-w-4xl mx-auto">
                    <div class="bg-gradient-to-r from-emerald-900/50 to-emerald-800/30 border border-emerald-500/30 p-4 rounded-2xl flex items-center justify-between shadow-lg">
                        <div>
                            <p class="text-emerald-400 text-xs font-bold uppercase tracking-widest mb-1">Total Lunas</p>
                            <h2 class="text-4xl font-black text-white" x-text="paymentStats.lunas">0</h2>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-emerald-500/20 flex items-center justify-center">
                            <i class="fas fa-check text-emerald-400 text-xl"></i>
                        </div>
                    </div>
                    <div class="bg-gradient-to-r from-rose-900/50 to-rose-800/30 border border-rose-500/30 p-4 rounded-2xl flex items-center justify-between shadow-lg">
                        <div>
                            <p class="text-rose-400 text-xs font-bold uppercase tracking-widest mb-1">Total Pending</p>
                            <h2 class="text-4xl font-black text-white" x-text="paymentStats.pending">0</h2>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-rose-500/20 flex items-center justify-center">
                            <i class="fas fa-clock text-rose-400 text-xl"></i>
                        </div>
                    </div>
                </div>
        
                <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-6 w-full">
                    <div class="glass-panel-heavy rounded-3xl p-6 relative">
                        <h3 class="text-sm font-bold text-slate-300 mb-4 flex items-center gap-2">
                            <i class="fas fa-chart-column text-cyan-400"></i> STATUS PEMBAYARAN
                        </h3>
                        <div id="payment-status-chart" class="w-full h-[85%]"></div>
                    </div>
                    <div class="glass-panel-heavy rounded-3xl p-6 relative">
                        <h3 class="text-sm font-bold text-slate-300 mb-4 flex items-center gap-2">
                            <i class="fas fa-chart-pie text-purple-400"></i> METODE / TENOR
                        </h3>
                        <div id="payment-tenor-chart" class="w-full h-[85%]"></div>
                    </div>
                </div>
            </div>

            {{-- 4. CONTAINER REKAP (PERBAIKAN: SEKARANG ADA DI DALAM FLEX-1) --}}
            <div x-show="currentMode === 'rekap'" class="w-full h-full p-10 overflow-auto" style="display: none;">
                <div class="w-full glass-panel-heavy rounded-3xl p-6 relative">
                    <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                        <i class="fas fa-table text-purple-400"></i> KOMPARASI DATA KELULUSAN
                    </h3>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-white/10 text-xs text-slate-400 uppercase tracking-wider">
                                    <th class="p-3 font-bold text-white">Program Studi</th>
                                    <template x-for="year in rekapYears" :key="year">
                                        <th class="p-3 text-center text-cyan-400" x-text="year"></th>
                                    </template>
                                    <th class="p-3 text-right text-emerald-400">Grand Total</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm divide-y divide-white/5">
                                <template x-for="(stats, prodi) in rekapData" :key="prodi">
                                    <tr class="hover:bg-white/5 transition">
                                        <td class="p-3 font-medium text-slate-300" x-text="prodi"></td>
                                        <template x-for="year in rekapYears" :key="year">
                                            <td class="p-3 text-center text-slate-400">
                                                <span x-text="stats[year] || '-'" :class="stats[year] ? 'text-white font-bold' : 'text-slate-600'"></span>
                                            </td>
                                        </template>
                                        <td class="p-3 text-right font-bold text-emerald-400" x-text="stats.total_all"></td>
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot class="bg-slate-800/80 font-bold border-t-2 border-white/20">
                                <tr>
                                    <td class="p-4 text-white uppercase tracking-wider">TOTAL KESELURUHAN</td>
                                    <template x-for="year in rekapYears" :key="year">
                                        <td class="p-4 text-center text-cyan-400 text-lg" x-text="rekapSummary[year] || 0"></td>
                                    </template>
                                    <td class="p-4 text-right text-emerald-400 text-xl" x-text="rekapSummary.grand_total || 0"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div> 
    </div>

    {{-- OVERLAY MODAL DETAIL (Untuk Peta) --}}
    <div x-show="selectedRegion" 
         style="display: none;"
         class="fixed inset-0 z-50 flex items-center justify-center bg-transparent p-4"
         x-transition:enter="transition ease-out duration-300 delay-100" 
         x-transition:enter-start="opacity-0 scale-90"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-90">

        <div class="glass-panel-heavy rounded-3xl w-full max-w-lg overflow-hidden transform transition-all border border-cyan-500/20 shadow-[0_0_50px_rgba(6,182,212,0.15)]"
             @click.away="resetZoom()">
            
            <div class="relative p-8 bg-gradient-to-r from-slate-900 to-slate-800 border-b border-white/10">
                <div class="absolute top-0 left-1/2 transform -translate-x-1/2 w-32 h-1 bg-cyan-500 shadow-[0_0_20px_rgba(6,182,212,0.8)]"></div>
                <button @click="resetZoom()" class="absolute top-6 right-6 text-slate-400 hover:text-white transition w-8 h-8 flex items-center justify-center rounded-full hover:bg-white/10"><i class="fas fa-times text-lg"></i></button>

                <div class="flex flex-col items-center">
                    <div class="w-16 h-16 rounded-2xl bg-slate-800 border border-white/10 flex items-center justify-center mb-4 shadow-lg">
                        <i class="fas fa-map-location-dot text-3xl text-cyan-400"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-white tracking-wide" x-text="selectedRegion?.name"></h3>
                    <div class="mt-3 px-4 py-1.5 bg-blue-500/20 border border-blue-500/30 rounded-full">
                        <span class="text-blue-400 text-sm font-medium">Total: <span x-text="selectedRegion?.total" class="font-bold text-white"></span> Personnel</span>
                    </div>
                </div>
            </div>

            <div class="p-8 max-h-[50vh] overflow-y-auto">
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <i class="fas fa-layer-group"></i> Minat Program Studi
                </h4>
                <template x-if="selectedRegion && selectedRegion.jurusan.length > 0">
                    <div class="grid gap-2">
                        <template x-for="jur in selectedRegion.jurusan" :key="jur.nama">
                            <div class="flex justify-between items-center bg-slate-800/50 hover:bg-slate-700/80 p-3 rounded-xl border border-white/5 transition">
                                <span class="text-xs font-bold text-slate-300" x-text="jur.nama"></span>
                                <span class="font-bold bg-slate-900 text-cyan-400 px-3 py-1 rounded-lg text-xs border border-white/10" x-text="jur.total"></span>
                            </div>
                        </template>
                    </div>
                </template>
                <template x-if="selectedRegion && selectedRegion.jurusan.length === 0">
                    <div class="text-center py-8 text-slate-500 text-sm italic">No data available.</div>
                </template>
            </div>
        </div>
    </div>

    <script>
    function gisSystem() {
        return {
            // --- STATE ---
            currentMode: 'peta',
            selectedYear: 'Semua',
            isLoading: true,
            
            // --- DATA ---
            totalMaba: 0,
            totalProvinsi: 0,
            topKota: [],
            topProvinsi: [],
            detailJurusanRaw: [],
            
            rekapData: {}, rekapYears: [], rekapSummary: {},
            chartTotal: 0,
            
            // Data Khusus Pembayaran
            paymentStats: { lunas: 0, pending: 0 },

            // UI State
            selectedRegion: null,
            mapInstance: null,
            chartInstance: null, // Instance untuk Chart Kelulusan
            paymentChartInstance: null, // Instance untuk Chart Pembayaran
            isTargeting: false,
            crosshairPos: { x: 0, y: 0 },

            init() { this.refreshData(); },

            switchMode(mode) {
                this.currentMode = mode;
                // Cleanup Map jika keluar dari mode peta
                if(mode !== 'peta' && this.mapInstance) {
                    this.mapInstance.destroy();
                    this.mapInstance = null;
                }
                this.refreshData();
            },

            refreshData() {
                if(this.currentMode === 'peta') this.fetchMapData();
                else if (this.currentMode === 'pembayaran') this.fetchPaymentData();
                else if (this.currentMode === 'chart') this.fetchChartData();
                else if (this.currentMode === 'rekap') this.fetchRekapData();
            },

            formatName(str) {
                if(!str) return '-';
                if (typeof str !== 'string') return String(str);
                let formatted = str.replace(/\w\S*/g, txt => txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase());
                return formatted.replace(/\bDki\b/g, 'DKI').replace(/\bDi\b/g, 'DI').replace(/\bNad\b/g, 'NAD');
            },

            // ------------------------------------------
            // 1. MODE PETA SEBARAN (MARKETING)
            // ------------------------------------------
            async fetchMapData() {
                this.isLoading = true;
                try {
                    let response = await fetch(`{{ route('admin.gis.data') }}?year=${this.selectedYear}`);
                    let data = await response.json();
                    
                    this.topProvinsi = data.provinsi || [];
                    this.topKota = data.kota || [];
                    this.detailJurusanRaw = data.detail_jurusan || [];
                    this.totalMaba = this.topProvinsi.reduce((n, item) => n + (item.total || 0), 0);
                    this.totalProvinsi = this.topProvinsi.length;
                    
                    this.renderMap();
                } catch (error) { console.error('Error map:', error); }
                this.isLoading = false;
            },

            async renderMap() {
                try {
                    if (!window.indoTopology) window.indoTopology = await fetch('https://code.highcharts.com/mapdata/countries/id/id-all.topo.json').then(r => r.json());
                    const topology = window.indoTopology;
                    
                    const mapData = this.topProvinsi.map(item => {
                        let dbName = item.provinsi_asal; 
                        if (!dbName) return null;
                        let mapName = this.findMapName(dbName);
                        return { name: mapName, value: item.total, originalName: dbName };
                    }).filter(i => i !== null);

                    const self = this;
                    if(this.mapInstance) this.mapInstance.destroy();

                    this.mapInstance = Highcharts.mapChart('map-container', {
                        chart: { map: topology, backgroundColor: 'transparent', style: { fontFamily: 'inherit' } },
                        title: { text: '' },
                        mapNavigation: { enabled: true, buttonOptions: { verticalAlign: 'bottom', theme: { fill: '#1e293b', stroke: '#334155', style: { color: 'white' } } } },
                        colorAxis: { min: 0, minColor: '#334155', maxColor: '#06b6d4', gridLineColor: '#334155' },
                        tooltip: { backgroundColor: 'rgba(15, 23, 42, 0.9)', style: { color: '#fff' }, pointFormat: '<b>{point.name}</b><br>Jumlah: {point.value}' },
                        series: [{
                            data: mapData, name: 'Mahasiswa', joinBy: 'name', 
                            borderColor: '#0f172a', borderWidth: 1, nullColor: '#1e293b',
                            states: { hover: { color: '#f59e0b' }, select: { color: '#ec4899' } },
                            dataLabels: { enabled: true, format: '{point.name}', style: { fontSize: '9px', fontWeight: 'normal', color: '#e2e8f0', textOutline: 'none' } },
                            point: { events: { click: function () { self.focusRegion(this.originalName || this.name); } } }
                        }],
                        credits: { enabled: false }
                    });
                } catch (err) { console.error("Gagal Render Peta:", err); }
            },

            // ------------------------------------------
            // 2. MODE GRAFIK PEMBAYARAN (CHART)
            // ------------------------------------------
            async fetchPaymentData() {
                this.isLoading = true;
                try {
                    // Pastikan route ini sudah diganti jadi getPaymentStats di web.php
                    let response = await fetch(`{{ route('admin.gis.getPaymentStats') }}?year=${this.selectedYear}`);
                    let json = await response.json();
                    
                    if (json.status === 'success') {
                        // 1. Update Angka Total di Kotak Atas
                        // Gunakan json.summary bukan json.data
                        this.paymentStats = { 
                            lunas: json.summary.lunas, 
                            pending: json.summary.pending 
                        };

                        // 2. Render Chart Kiri (Bar Status Lunas vs Pending)
                        this.renderStatusChart(json.summary.lunas, json.summary.pending);

                        // 3. Render Chart Kanan (Pie Chart Tenor)
                        // Data tenor sudah disiapkan controller dalam format {name, y}
                        this.renderTenorChart(json.tenor_distribution);
                    }
                } catch (error) { 
                    console.error('Error fetching payment stats:', error); 
                }
                this.isLoading = false;
            },

            renderStatusChart(lunas, pending) {
                if(this.paymentChartInstance) this.paymentChartInstance.destroy();

                // Kita pakai chart container yang KIRI (buat Status)
                // Pastikan di HTML ID-nya: payment-status-chart
                Highcharts.chart('payment-status-chart', {
                    chart: { type: 'column', backgroundColor: 'transparent' },
                    title: { text: '' },
                    xAxis: { 
                        categories: ['Status Pembayaran'], 
                        visible: false 
                    },
                    yAxis: { 
                        title: { text: 'Jumlah Mahasiswa', style: { color: '#94a3b8' } },
                        gridLineColor: '#334155',
                        labels: { style: { color: '#cbd5e1' } }
                    },
                    legend: { itemStyle: { color: '#fff' } },
                    plotOptions: { 
                        column: { 
                            borderRadius: 10,
                            dataLabels: { enabled: true, color: '#fff', style: { textOutline: 'none', fontSize: '14px' } } 
                        } 
                    },
                    series: [{
                        name: 'Lunas',
                        data: [lunas],
                        color: { linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 }, stops: [[0, '#10b981'], [1, '#064e3b']] }
                    }, {
                        name: 'Pending',
                        data: [pending],
                        color: { linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 }, stops: [[0, '#f43f5e'], [1, '#881337']] }
                    }],
                    credits: { enabled: false }
                });
            },

            renderTenorChart(dataTenor) {
                // Kita pakai chart container yang KANAN (buat Tenor)
                // Pastikan di HTML ID-nya: payment-tenor-chart
                Highcharts.chart('payment-tenor-chart', {
                    chart: { type: 'pie', backgroundColor: 'transparent' },
                    title: { text: '' },
                    tooltip: { pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b> ({point.y} orang)' },
                    plotOptions: {
                        pie: {
                            allowPointSelect: true,
                            cursor: 'pointer',
                            dataLabels: {
                                enabled: true,
                                format: '<b>{point.name}</b>: {point.y}',
                                style: { color: '#cbd5e1', textOutline: 'none' },
                                connectorColor: '#475569'
                            },
                            innerSize: '50%', // Donut Chart
                            borderWidth: 0
                        }
                    },
                    series: [{
                        name: 'Persentase',
                        colorByPoint: true,
                        data: dataTenor
                    }],
                    colors: ['#3b82f6', '#f59e0b', '#8b5cf6'], 
                    credits: { enabled: false }
                });
            },

            // ------------------------------------------
            // 3. UTILITIES & LAINNYA
            // ------------------------------------------
            findMapName(dbName) {
                if (!dbName) return '';
                let cleanInput = dbName.toUpperCase().replace('PROVINSI ', '').trim();
                const manualMap = {
                    'DKI JAKARTA': 'DKI Jakarta', 'JAWA BARAT': 'Jawa Barat', 'JAWA TENGAH': 'Jawa Tengah',
                    'DI YOGYAKARTA': 'DI Yogyakarta', 'JAWA TIMUR': 'Jawa Timur', 'BANTEN': 'Banten',
                    'BALI': 'Bali', 'NUSA TENGGARA BARAT': 'Nusa Tenggara Barat', 'NUSA TENGGARA TIMUR': 'Nusa Tenggara Timur',
                    'KEPULAUAN RIAU': 'Kepulauan Riau', 'RIAU': 'Riau', 'SUMATERA BARAT': 'Sumatera Barat',
                    'SUMATERA UTARA': 'Sumatera Utara', 'SUMATERA SELATAN': 'Sumatera Selatan', 'LAMPUNG': 'Lampung',
                    'JAMBI': 'Jambi', 'BENGKULU': 'Bengkulu', 'KEPULAUAN BANGKA BELITUNG': 'Bangka Belitung',
                    'BANGKA BELITUNG': 'Bangka Belitung', 'ACEH': 'Aceh', 'NANGGROE ACEH DARUSSALAM': 'Aceh',
                    'KALIMANTAN BARAT': 'Kalimantan Barat', 'KALIMANTAN TIMUR': 'Kalimantan Timur', 'KALIMANTAN SELATAN': 'Kalimantan Selatan',
                    'KALIMANTAN TENGAH': 'Kalimantan Tengah', 'KALIMANTAN UTARA': 'Kalimantan Utara',
                    'SULAWESI UTARA': 'Sulawesi Utara', 'SULAWESI BARAT': 'Sulawesi Barat', 'SULAWESI TENGAH': 'Sulawesi Tengah',
                    'SULAWESI TENGGARA': 'Sulawesi Tenggara', 'SULAWESI SELATAN': 'Sulawesi Selatan', 'GORONTALO': 'Gorontalo',
                    'MALUKU': 'Maluku', 'MALUKU UTARA': 'Maluku Utara', 'PAPUA': 'Papua', 'PAPUA BARAT': 'Papua Barat',
                    'PAPUA SELATAN': 'Papua', 'PAPUA TENGAH': 'Papua', 'PAPUA PEGUNUNGAN': 'Papua', 'PAPUA BARAT DAYA': 'Papua Barat'
                };
                return manualMap[cleanInput] || this.formatName(dbName);
            },

            focusRegion(rawName) {
                if(!this.mapInstance || !rawName) return;
                this.selectedRegion = null;
                this.mapInstance.series[0].points.forEach(p => p.select(false));
                let searchKey = String(rawName).toLowerCase().replace(/provinsi/g, '').trim();
                let point = this.mapInstance.series[0].points.find(p => {
                    let pName = (p.name || '').toLowerCase();
                    let pOriginal = (p.originalName || '').toLowerCase();
                    return pName.includes(searchKey) || pOriginal.includes(searchKey);
                });
                if (point) {
                    let pixelX = point.plotX + this.mapInstance.plotLeft;
                    let pixelY = point.plotY + this.mapInstance.plotTop;
                    this.crosshairPos = { x: pixelX, y: pixelY };
                    this.isTargeting = true;
                    setTimeout(() => {
                        point.zoomTo(); point.select(true);
                        setTimeout(() => { this.isTargeting = false; }, 100);
                        setTimeout(() => { this.showOverlay(point.originalName, point.value, point.name); }, 600);
                    }, 400); 
                } else {
                    this.showOverlay(rawName, 0, this.formatName(rawName));
                }
            },

            showOverlay(dbName, total, niceName) {
                if (!dbName) return;
                let cleanDB = String(dbName).toLowerCase().trim();
                let jurusans = this.detailJurusanRaw
                    .filter(item => item.provinsi_asal && item.provinsi_asal.toLowerCase().trim() === cleanDB)
                    .map(item => ({ nama: (item.nama_jurusan || 'BELUM TERDETEKSI').toUpperCase(), total: item.total }))
                    .sort((a, b) => b.total - a.total);
                this.selectedRegion = { name: niceName, total: total, jurusan: jurusans };
            },

            resetZoom() {
                this.selectedRegion = null;
                if(this.mapInstance) {
                    this.mapInstance.mapZoom();
                    this.mapInstance.series[0].points.forEach(p => p.select(false));
                }
            },

            async fetchChartData() {
                this.isLoading = true;
                try {
                    let response = await fetch(`{{ route('admin.gis.kelulusan') }}?year=${this.selectedYear}`);
                    let data = await response.json();
                    if(data.status === 'success') {
                        this.chartTotal = data.total_lulus;
                        this.renderChart(data.data);
                    }
                } catch (error) { console.error('Error chart:', error); }
                this.isLoading = false;
            },

            renderChart(chartData) {
                if(this.chartInstance) this.chartInstance.destroy();
                this.chartInstance = Highcharts.chart('chart-container', {
                    chart: { type: 'column', backgroundColor: 'transparent' },
                    title: { text: '' },
                    xAxis: { type: 'category', labels: { style: { color: '#cbd5e1' }, rotation: -45 } },
                    yAxis: { title: { text: 'Jumlah', style: { color: '#94a3b8' } }, gridLineColor: '#334155', labels: { style: { color: '#cbd5e1' } } },
                    legend: { enabled: false },
                    tooltip: { backgroundColor: 'rgba(15, 23, 42, 0.9)', style: { color: '#fff' }, pointFormat: 'Lulus: <b>{point.y}</b>' },
                    plotOptions: { column: { borderRadius: 4, color: { linearGradient: { x1: 0, x2: 0, y1: 0, y2: 1 }, stops: [[0, '#10b981'], [1, '#064e3b']] }, borderColor: '#10b981' } },
                    series: [{ name: 'Lulus', data: chartData, dataLabels: { enabled: true, color: '#fff' } }],
                    credits: { enabled: false }
                });
            },

            async fetchRekapData() {
                this.isLoading = true;
                try {
                    let url = `{{ route('admin.gis.rekap') }}?year=${this.selectedYear}`;
                    let response = await fetch(url);
                    let json = await response.json();
                    if(json.status === 'success') {
                        this.rekapYears = json.years;
                        this.rekapData = json.data;
                        this.rekapSummary = json.summary;
                    }
                } catch (error) { console.error('Error rekap:', error); }
                this.isLoading = false;
            }
        }
    }
</script>
</body>
</html>