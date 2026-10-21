<script>
const SYNC_URL = "{{ route('admin.moodle.quiz-complete.sync-lolos') }}";
const STATS_URL = "{{ route('admin.moodle.quiz-complete.statistics') }}";
const CSRF_TOKEN = "{{ csrf_token() }}";
function syncLolos() {
    if (window.autoRefreshManager) {
        console.log('Manual sync initiated for all data...');
        window.autoRefreshManager.refreshData();
    } else {
        alert('Sistem belum siap. Silakan refresh halaman.');
    }
}
class AutoRefreshManager {
    constructor() {
        this.isAutoRefreshEnabled = true;
        this.refreshInterval = 60000; // 1 menit dalam milidetik
        this.timerInterval = null;
        this.refreshIntervalId = null;
        this.timeLeft = 60; // 60 detik
        this.lastSyncTime = null;
        this.isTabActive = true;
        this.nextSyncTime = null;
        this.storageKey = 'autoRefreshState_v2'; // Key dengan version untuk avoid conflict
        this.init();
    }

    init() {
        console.log('AutoRefreshManager initializing...');
        this.loadStateFromStorage();
        this.setupEventListeners();
        this.startAutoRefresh();
        this.startTimer();
        this.initializeBackgroundSync();
        
        // Immediate sync jika waktu sudah lewat
        if (this.timeLeft <= 1) {
            setTimeout(() => {
                this.refreshData();
            }, 1000);
        }
    }

    loadStateFromStorage() {
        try {
            const savedState = localStorage.getItem(this.storageKey);
            if (savedState) {
                const state = JSON.parse(savedState);
                console.log('Loaded state from storage:', state);
                
                // Validasi data yang di-load
                if (state && typeof state === 'object') {
                    // Calculate remaining time dengan buffer time
                    if (state.nextSyncTime) {
                        const now = Date.now();
                        const nextSync = new Date(state.nextSyncTime).getTime();
                        const timeUntilNextSync = nextSync - now;
                        
                        console.log('Time until next sync:', timeUntilNextSync);
                        
                        if (timeUntilNextSync > 1000) { // Buffer 1 detik
                            this.timeLeft = Math.max(1, Math.min(60, Math.floor(timeUntilNextSync / 1000)));
                            this.nextSyncTime = state.nextSyncTime;
                        } else {
                            // Waktu sudah lewat, sync segera
                            this.timeLeft = 1;
                            this.nextSyncTime = new Date(now + 1000).toISOString();
                        }
                    }
                    
                    this.isAutoRefreshEnabled = state.isAutoRefreshEnabled !== false;
                    this.lastSyncTime = state.lastSyncTime;
                    
                    console.log('Timer state loaded:', {
                        timeLeft: this.timeLeft,
                        isEnabled: this.isAutoRefreshEnabled,
                        lastSync: this.lastSyncTime
                    });
                }
            } else {
                console.log('No saved state found, using defaults');
                this.calculateNextSyncTime(); // Set initial nextSyncTime
            }
        } catch (error) {
            console.error('Error loading state from storage:', error);
            this.resetToDefaults();
        }
    }

    resetToDefaults() {
        this.timeLeft = 60;
        this.isAutoRefreshEnabled = true;
        this.calculateNextSyncTime();
        console.log('Reset to defaults');
    }

    saveStateToStorage() {
        try {
            // Pastikan nextSyncTime selalu ada
            if (!this.nextSyncTime) {
                this.calculateNextSyncTime();
            }
            
            const state = {
                isAutoRefreshEnabled: this.isAutoRefreshEnabled,
                lastSyncTime: this.lastSyncTime,
                nextSyncTime: this.nextSyncTime,
                savedAt: new Date().toISOString(),
                version: '2.0'
            };
            
            localStorage.setItem(this.storageKey, JSON.stringify(state));
            console.log('State saved to storage');
        } catch (error) {
            console.error('Error saving state to storage:', error);
        }
    }

    calculateNextSyncTime() {
        const now = new Date();
        const nextSync = new Date(now.getTime() + this.refreshInterval);
        this.nextSyncTime = nextSync.toISOString();
        console.log('Next sync calculated:', nextSync.toLocaleTimeString());
        return nextSync;
    }

    setupEventListeners() {
        // Toggle auto refresh
        const toggleBtn = document.getElementById('toggleAutoRefresh');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                this.toggleAutoRefresh();
            });
        }

        // Enhanced visibility change handling
        document.addEventListener('visibilitychange', () => {
            const wasActive = this.isTabActive;
            this.isTabActive = !document.hidden;
            
            if (document.hidden) {
                console.log('Tab menjadi tidak aktif');
                this.pauseTimerVisual();
                // Save state sebelum tab inactive
                this.saveStateToStorage();
            } else {
                console.log('Tab menjadi aktif kembali');
                if (!wasActive) {
                    this.resumeTimerVisual();
                    this.updateStatistics();
                    this.recalculateTimer();
                }
            }
        });

        // Enhanced focus handling
        window.addEventListener('focus', () => {
            console.log('Window focused');
            this.isTabActive = true;
            this.updateStatistics();
            this.recalculateTimer();
        });

        window.addEventListener('blur', () => {
            console.log('Window blurred');
            this.isTabActive = false;
            this.saveStateToStorage();
        });

        // Multiple save strategies
        window.addEventListener('beforeunload', () => {
            console.log('Page unloading, saving state...');
            this.saveStateToStorage();
        });

        window.addEventListener('pagehide', () => {
            console.log('Page hiding, saving state...');
            this.saveStateToStorage();
        });

        // Periodic saving dengan exponential backoff
        let saveInterval = 5000; // 5 detik pertama
        const saveIntervalId = setInterval(() => {
            this.saveStateToStorage();
            // Setelah 30 detik, kurangi frekuensi saving
            if (saveInterval === 5000) {
                clearInterval(saveIntervalId);
                setInterval(() => {
                    this.saveStateToStorage();
                }, 30000); // 30 detik setelahnya
            }
        }, saveInterval);
    }

    recalculateTimer() {
        if (this.nextSyncTime) {
            const now = Date.now();
            const nextSync = new Date(this.nextSyncTime).getTime();
            const timeUntilNextSync = nextSync - now;
            
            console.log('Recalculating timer:', {
                now: new Date(now).toLocaleTimeString(),
                nextSync: new Date(nextSync).toLocaleTimeString(),
                timeUntilNextSync: timeUntilNextSync
            });
            
            if (timeUntilNextSync > 0) {
                const newTimeLeft = Math.floor(timeUntilNextSync / 1000);
                this.timeLeft = Math.max(1, Math.min(60, newTimeLeft));
                console.log('New time left:', this.timeLeft);
            } else {
                // Waktu sudah lewat, sync segera
                this.timeLeft = 1;
                console.log('Sync time passed, triggering immediate sync');
            }
            this.updateTimerDisplay();
        } else {
            console.log('No nextSyncTime available, calculating new one');
            this.calculateNextSyncTime();
            this.timeLeft = 60;
        }
    }

    initializeBackgroundSync() {
        // Web Locks API untuk background persistence
        if ('locks' in navigator) {
            this.acquireLock();
        }
        
        // Fallback mechanism
        this.setupBackgroundFallback();
    }

    async acquireLock() {
        try {
            await navigator.locks.request('autoRefreshBackgroundLock', { 
                mode: 'shared' 
            }, async lock => {
                console.log('Background sync lock acquired');
                // Lock akan tetap aktif
                return new Promise(() => {}); // Keep lock forever
            });
        } catch (error) {
            console.log('Web Locks API tidak didukung:', error);
        }
    }

    setupBackgroundFallback() {
        // Minimal background task untuk menjaga thread
        this.backgroundInterval = setInterval(() => {
            // No-op, just keep the thread alive
        }, 30000);
    }

    startAutoRefresh() {
        if (this.isAutoRefreshEnabled && !this.refreshIntervalId) {
            console.log('🔄 Auto refresh started');
            this.refreshIntervalId = setInterval(() => {
                this.refreshData();
            }, this.refreshInterval);
        }
    }

    toggleAutoRefresh() {
        this.isAutoRefreshEnabled = !this.isAutoRefreshEnabled;
        console.log('Auto refresh toggled:', this.isAutoRefreshEnabled);
        
        const statusElement = document.getElementById('autoRefreshStatus');
        const toggleButton = document.getElementById('toggleAutoRefresh');
        
        if (this.isAutoRefreshEnabled) {
            statusElement.className = 'w-3 h-3 rounded-full bg-success-500 animate-pulse';
            toggleButton.innerHTML = `
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            `;
            this.startAutoRefresh();
            this.startTimer();
        } else {
            statusElement.className = 'w-3 h-3 rounded-full bg-gray-400';
            toggleButton.innerHTML = `
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            `;
            this.stopAutoRefresh();
            this.stopTimer();
        }
        
        this.saveStateToStorage();
    }

    stopAutoRefresh() {
        if (this.refreshIntervalId) {
            clearInterval(this.refreshIntervalId);
            this.refreshIntervalId = null;
            console.log('Auto refresh stopped');
        }
    }

    startTimer() {
        // Clear existing timer
        if (this.timerInterval) {
            clearInterval(this.timerInterval);
        }

        console.log('Timer started with time left:', this.timeLeft);
        this.updateTimerDisplay();
        
        this.timerInterval = setInterval(() => {
            this.timeLeft--;
            this.updateTimerDisplay();
            
            if (this.timeLeft <= 0) {
                console.log('Timer reached zero, resetting...');
                this.timeLeft = 60;
                this.calculateNextSyncTime();
                this.saveStateToStorage();
            }
        }, 1000);
    }

    stopTimer() {
        if (this.timerInterval) {
            clearInterval(this.timerInterval);
            this.timerInterval = null;
            console.log('Timer stopped');
        }
        const timerElement = document.getElementById('refreshTimer');
        if (timerElement) {
            timerElement.textContent = '(Paused)';
        }
    }

    pauseTimerVisual() {
        if (this.timerInterval) {
            clearInterval(this.timerInterval);
            this.timerInterval = null;
            console.log('Timer visual paused');
        }
    }

    resumeTimerVisual() {
        if (this.isAutoRefreshEnabled && !this.timerInterval) {
            console.log('Resuming timer visual');
            this.recalculateTimer();
            this.startTimer();
        }
    }

    updateTimerDisplay() {
        const timerElement = document.getElementById('refreshTimer');
        if (timerElement) {
            const minutes = Math.floor(this.timeLeft / 60);
            const seconds = this.timeLeft % 60;
            timerElement.textContent = `(${minutes}:${seconds.toString().padStart(2, '0')})`;
            
            // Visual feedback untuk waktu hampir habis
            if (this.timeLeft <= 10) {
                timerElement.classList.add('text-red-500', 'font-bold');
            } else {
                timerElement.classList.remove('text-red-500', 'font-bold');
            }
        }
    }

    async refreshData() {
    try {
        this.showLoading();
        console.log('Syncing data to server...');
        
        const response = await fetch(SYNC_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                sync_lolos: true,
                background: !this.isTabActive
            })
        });

        const data = await response.json();
        
        if (data.success) {
            console.log('Sync success:', data);
            this.lastSyncTime = new Date();
            this.updateStatistics();
            
            this.showNotification(
                'Data Diperbarui', 
                `Berhasil memproses ${data.total_processed} data (${data.lolos_count} baru).`
            );

            // Jika ada data baru (lolos_count > 0), reload halaman agar tabel terupdate
            if (data.lolos_count > 0 && this.isTabActive) {
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            }
        } else {
            throw new Error(data.message || 'Gagal menyimpan data');
        }
    } catch (error) {
        console.error('Auto Sync Failed:', error);
        this.showNotification('Error Sync', error.message, 'error');
    } finally {
        this.hideLoading();
    }
}
    async updateStatistics() {
        try {
            const response = await fetch('{{ route("admin.moodle.quiz-complete.statistics") }}', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Cache-Control': 'no-cache'
                }
            });
            
            if (!response.ok) throw new Error('Network response was not ok');
            
            const data = await response.json();
            this.updateStatisticsUI(data);
        } catch (error) {
            console.error('Error fetching statistics:', error);
        }
    }

    updateStatisticsUI(statistics) {
        const statsElements = {
            'total_lulus': document.querySelector('[data-stat="total_lulus"]'),
            'lulus_hari_ini': document.querySelector('[data-stat="lulus_hari_ini"]'),
            'lulus_minggu_ini': document.querySelector('[data-stat="lulus_minggu_ini"]'),
            'lulus_bulan_ini': document.querySelector('[data-stat="lulus_bulan_ini"]')
        };

        for (const [key, element] of Object.entries(statsElements)) {
            if (element && statistics[key] !== undefined) {
                // Only update if value changed
                if (element.textContent !== statistics[key].toString()) {
                    element.textContent = statistics[key];
                    element.classList.add('text-brand-600', 'animate-pulse');
                    setTimeout(() => {
                        element.classList.remove('text-brand-600', 'animate-pulse');
                    }, 1000);
                }
            }
        }

        const lastSyncElement = document.querySelector('[data-last-sync]');
        if (lastSyncElement && this.lastSyncTime) {
            lastSyncElement.textContent = this.lastSyncTime.toLocaleTimeString();
        }
    }

    showLoading() {
        if (this.isTabActive) {
            const loadingIndicator = document.getElementById('loadingIndicator');
            if (loadingIndicator) {
                loadingIndicator.classList.remove('hidden');
            }
        }
    }

    hideLoading() {
        const loadingIndicator = document.getElementById('loadingIndicator');
        if (loadingIndicator) {
            loadingIndicator.classList.add('hidden');
        }
    }

    showNotification(title, message, type = 'success') {
        if (!this.isTabActive) return;

        const notification = document.getElementById('notification');
        if (!notification) return;

        let bgColor = type === 'success' ? 'bg-success-500' : 'bg-error-500';
        let borderColor = type === 'success' ? 'border-success-300' : 'border-error-300';
        let textColor = type === 'success' ? 'text-success-800' : 'text-error-800';
        
        notification.innerHTML = `
            <div class="rounded-lg border ${borderColor} ${type === 'success' ? 'bg-success-50 dark:bg-success-500/15' : 'bg-error-50 dark:bg-error-500/15'} p-4 shadow-lg">
                <div class="flex items-center gap-3">
                    <div class="inline-flex items-center justify-center w-8 h-8 rounded-full ${bgColor} text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${type === 'success' ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12'}"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-sm font-semibold ${textColor} dark:${type === 'success' ? 'text-success-400' : 'text-error-400'}">${title}</h4>
                        <p class="text-xs ${type === 'success' ? 'text-success-600' : 'text-error-600'} dark:${type === 'success' ? 'text-success-500' : 'text-error-500'}">${message}</p>
                    </div>
                    <button type="button" onclick="hideNotification()" class="${type === 'success' ? 'text-success-400' : 'text-error-400'} hover:${type === 'success' ? 'text-success-600' : 'text-error-600'}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        `;
        
        notification.classList.remove('hidden');
        
        setTimeout(() => {
            this.hideNotification();
        }, 5000);
    }

    hideNotification() {
        const notification = document.getElementById('notification');
        if (notification) {
            notification.classList.add('hidden');
        }
    }

    cleanup() {
        console.log('🧹 Cleaning up AutoRefreshManager...');
        this.stopAutoRefresh();
        this.stopTimer();
        if (this.backgroundInterval) {
            clearInterval(this.backgroundInterval);
        }
        this.saveStateToStorage();
    }
}

// Enhanced initialization dengan error handling
document.addEventListener('DOMContentLoaded', function() {
    try {
        // Cleanup existing instance
        if (window.autoRefreshManager) {
            window.autoRefreshManager.cleanup();
        }
        
        // Initialize new instance
        window.autoRefreshManager = new AutoRefreshManager();
        
        // Global error handler untuk class ini
        window.addEventListener('error', function(e) {
            if (e.target && e.target.src && e.target.src.includes('autoRefresh')) {
                console.error('Error in AutoRefreshManager:', e.error);
            }
        });
        
    } catch (error) {
        console.error('Failed to initialize AutoRefreshManager:', error);
    }
});

// Global function to hide notification
function hideNotification() {
    if (window.autoRefreshManager) {
        window.autoRefreshManager.hideNotification();
    }
}

// Handle various page navigation scenarios
if (typeof Turbolinks !== 'undefined') {
    document.addEventListener('turbolinks:load', function() {
        if (window.autoRefreshManager) {
            window.autoRefreshManager.recalculateTimer();
        }
    });
}

// Export untuk penggunaan modular
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AutoRefreshManager;
}
</script>
