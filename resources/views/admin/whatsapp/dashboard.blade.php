<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Bot Dashboard - PMB UBP Karawang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-top: 20px;
            padding-bottom: 20px;
        }
        
        .card {
            border-radius: 20px;
            border: none;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            margin-bottom: 20px;
        }
        
        .card-header {
            background: linear-gradient(135deg, #25D366, #128C7E);
            color: white;
            border-radius: 20px 20px 0 0 !important;
            padding: 1.5rem;
        }
        
        .status-badge {
            padding: 8px 15px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
            display: inline-block;
        }
        
        .status-connected {
            background: #d4edda;
            color: #155724;
        }
        
        .status-waiting {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-disconnected {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-loading {
            background: #cce5ff;
            color: #004085;
        }
        
        .status-error {
            background: #f8d7da;
            color: #721c24;
        }
        
        #qr-container {
            padding: 20px;
            background: white;
            border-radius: 15px;
            border: 2px dashed #e0e0e0;
            min-height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-pmb {
            background: linear-gradient(135deg, #25D366, #128C7E);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-pmb:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(37, 211, 102, 0.3);
            color: white;
        }
        
        .server-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            font-family: 'Courier New', monospace;
        }
        
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #25D366;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .instructions {
            background: #e8f5e9;
            border-left: 4px solid #25D366;
            padding: 15px;
            border-radius: 0 10px 10px 0;
        }
        
        .qr-expiry {
            background: #fff3cd;
            padding: 8px 15px;
            border-radius: 8px;
            font-size: 0.9rem;
        }
        
        .debug-panel {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            font-size: 0.8rem;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .status-history {
            max-height: 200px;
            overflow-y: auto;
        }
        
        .history-item {
            border-left: 3px solid #25D366;
            padding-left: 10px;
            margin-bottom: 8px;
            font-size: 0.85rem;
        }
        
        .history-item.disconnected {
            border-left-color: #dc3545;
        }
        
        .history-item.error {
            border-left-color: #ffc107;
        }
        
        .auto-refresh-indicator {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="row">
            <div class="col-lg-8">
                <!-- Main Card -->
                <div class="card mb-4">
                    <div class="card-header text-center">
                        <h1 class="h3 mb-2"><i class="bi bi-whatsapp"></i> WhatsApp Bot PMB UBP</h1>
                        <p class="mb-0">Monitor & Kelola Koneksi WhatsApp Bot</p>
                    </div>
                    <div class="card-body">
                        <!-- Status Display -->
                        <div class="row mb-4 align-items-center">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <div class="status-badge" id="status-badge">
                                            <i class="bi bi-circle"></i> Loading...
                                        </div>
                                    </div>
                                    <div>
                                        <h4 id="status-message" class="mb-1">Memuat status...</h4>
                                        <div class="text-muted small">
                                            <i class="bi bi-phone"></i> 
                                            <span id="phone-number">-</span>
                                            <span class="mx-2">•</span>
                                            <i class="bi bi-clock"></i> 
                                            <span id="last-updated">-</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="btn-group" role="group">
                                    <button class="btn btn-primary btn-sm" onclick="refreshStatus(true)">
                                        <i class="bi bi-arrow-clockwise"></i> Refresh
                                    </button>
                                    <button class="btn btn-warning btn-sm ms-2" onclick="restartBot()">
                                        <i class="bi bi-arrow-repeat"></i> Restart
                                    </button>
                                    <button class="btn btn-danger btn-sm ms-2" onclick="forceNewSession()">
                                        <i class="bi bi-trash"></i> New Session
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Connection Progress -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">Status Koneksi</h6>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="auto-refresh" checked>
                                    <label class="form-check-label" for="auto-refresh">Auto-refresh</label>
                                </div>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div id="connection-progress" class="progress-bar" role="progressbar" style="width: 0%"></div>
                            </div>
                        </div>

                        <!-- QR Code Display -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0"><i class="bi bi-qr-code-scan"></i> QR Code WhatsApp</h5>
                                <div class="qr-expiry">
                                    <i class="bi bi-clock-history"></i> 
                                    <span id="qr-status">Memuat...</span>
                                </div>
                            </div>
                            <div id="qr-container">
                                <div class="text-center w-100" id="qr-content">
                                    <div id="qr-loading">
                                        <div class="loading-spinner"></div>
                                        <p class="mt-3 text-muted">Memuat QR Code...</p>
                                    </div>
                                    <div id="qr-code" style="display: none;"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="text-center mb-4">
                            <div class="btn-group" role="group">
                                <button class="btn btn-pmb" onclick="downloadQRCode()" id="download-btn" disabled>
                                    <i class="bi bi-download"></i> Download QR
                                </button>
                                <button class="btn btn-outline-primary ms-2" onclick="showQRFullscreen()" id="fullscreen-btn" disabled>
                                    <i class="bi bi-arrows-fullscreen"></i> Fullscreen
                                </button>
                                <button class="btn btn-outline-info ms-2" onclick="copyQRCode()" id="copy-btn" disabled>
                                    <i class="bi bi-clipboard"></i> Copy QR
                                </button>
                                <button class="btn btn-outline-secondary ms-2" onclick="toggleDebug()" id="debug-btn">
                                    <i class="bi bi-bug"></i> Debug
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Debug Panel -->
                <div class="card mb-4" id="debug-panel" style="display: none;">
                    <div class="card-header bg-secondary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="bi bi-code-slash"></i> Debug Information</h6>
                            <button class="btn btn-sm btn-light" onclick="clearDebugLog()">
                                <i class="bi bi-trash"></i> Clear
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <small class="text-muted">Status:</small><br>
                                <span id="debug-status" class="badge bg-secondary">-</span>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">Phone:</small><br>
                                <strong id="debug-phone">-</strong>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">Session:</small><br>
                                <strong id="debug-session">-</strong>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">Heartbeat:</small><br>
                                <strong id="debug-heartbeat">-</strong>
                            </div>
                        </div>
                        <div class="debug-panel">
                            <div id="debug-log">
                                <div class="text-muted">Debug log akan muncul di sini...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Info Card -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="bi bi-info-circle"></i> Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="instructions mb-3">
                            <h6><i class="bi bi-phone"></i> Cara Scan QR:</h6>
                            <ol class="small mb-0">
                                <li>Buka WhatsApp di HP</li>
                                <li>Menu → Linked Devices</li>
                                <li>Tap Link a Device</li>
                                <li>Scan QR Code</li>
                                <li>Tunggu hingga Connected</li>
                            </ol>
                        </div>
                        
                        <div class="server-info mb-3">
                            <h6><i class="bi bi-server"></i> Server Info</h6>
                            <div class="mb-2">
                                <small>URL:</small><br>
                                <strong id="server-url">{{ config('app.node_server_url', 'http://103.23.198.160:3001') }}</strong>
                            </div>
                            <div>
                                <small>Health:</small><br>
                                <span id="server-health" class="badge bg-secondary">Checking...</span>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <h6><i class="bi bi-clock-history"></i> Auto Refresh</h6>
                            <div class="small text-muted">
                                Status akan diperbarui otomatis setiap 30 detik
                                <div class="mt-1">
                                    <i class="bi bi-check-circle text-success"></i>
                                    <span id="refresh-timer">30s</span> sampai refresh berikutnya
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- History Card -->
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h6 class="mb-0"><i class="bi bi-history"></i> Connection History</h6>
                    </div>
                    <div class="card-body status-history">
                        <div id="history-list">
                            <div class="text-center text-muted">
                                <div class="spinner-border spinner-border-sm" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2 small">Memuat history...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- QR Fullscreen Modal -->
    <div class="modal fade" id="qrModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">QR Code Fullscreen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <div id="qr-fullscreen"></div>
                    <div class="mt-3">
                        <button class="btn btn-primary" onclick="downloadQRCode()">
                            <i class="bi bi-download"></i> Download
                        </button>
                        <button class="btn btn-secondary ms-2" data-bs-dismiss="modal">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1055">
        <div id="toastContainer"></div>
    </div>

    <!-- Auto Refresh Indicator -->
    <div class="auto-refresh-indicator" id="refresh-indicator" style="display: none;">
        <i class="bi bi-arrow-clockwise"></i> Auto-refresh aktif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Configuration
        const STATUS_API_URL = '{{ route("admin.whatsapp.status") }}';
        const HISTORY_API_URL = '{{ route("admin.whatsapp.history") }}';
        const RESTART_API_URL = '{{ route("admin.whatsapp.restart") }}';
        const FORCE_NEW_API_URL = '{{ route("admin.whatsapp.force-new-session") }}';
        const WEBHOOK_URL = '{{ route("admin.whatsapp.status-update") }}';
        
        // State management
        let currentState = {
            isConnected: false,
            qrCode: null,
            status: 'LOADING',
            phoneNumber: null,
            lastUpdate: null,
            autoRefresh: true,
            refreshInterval: null,
            refreshTimer: 30,
            debugLog: []
        };

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 WhatsApp Dashboard initialized');
            
            // Load initial data
            loadStatus();
            loadHistory();
            
            // Setup auto-refresh
            setupAutoRefresh();
            
            // Setup auto-refresh toggle
            document.getElementById('auto-refresh').addEventListener('change', function(e) {
                currentState.autoRefresh = e.target.checked;
                if (currentState.autoRefresh) {
                    startAutoRefresh();
                    showToast('Auto-refresh diaktifkan', 'info');
                } else {
                    stopAutoRefresh();
                    showToast('Auto-refresh dimatikan', 'warning');
                }
            });
            
            // Setup EventSource for real-time updates (if supported)
            setupRealtimeUpdates();
        });

        // Load status from server
        async function loadStatus() {
            try {
                showLoading();
                addDebugLog('📡 Mengambil status dari server...');
                
                const response = await fetch(STATUS_API_URL);
                const result = await response.json();
                
                if (result.success) {
                    processStatusData(result.data);
                    showToast('Status berhasil dimuat', 'success');
                } else {
                    throw new Error(result.message || 'Gagal mengambil status');
                }
            } catch (error) {
                addDebugLog(`❌ Error: ${error.message}`, 'error');
                showErrorState(error.message);
                showToast(`Error: ${error.message}`, 'error');
            }
        }

        // Process status data
        function processStatusData(statusData) {
            console.log('📊 Status data:', statusData);
            addDebugLog('📊 Memproses data status...');
            
            // Extract data based on source
            let data = statusData;
            if (statusData.data) {
                data = statusData.data;
            }
            
            // Update current state
            currentState.isConnected = data.isConnected || false;
            currentState.qrCode = data.qrCode || null;
            currentState.status = data.status || 'UNKNOWN';
            currentState.phoneNumber = data.phoneNumber || null;
            currentState.lastUpdate = data.timestamp || new Date().toISOString();
            currentState.sessionName = data.sessionName || 'pmb-ubp-bot';
            currentState.serverInfo = data.serverInfo || {};
            currentState.heartbeatActive = data.heartbeatActive || false;
            
            // Update UI
            updateStatusDisplay();
            updateQRDisplay();
            updateDebugInfo();
            
            // Check connection and handle accordingly
            if (currentState.isConnected) {
                handleConnectedState();
            } else if (currentState.qrCode) {
                handleQRState();
            } else {
                handleDisconnectedState();
            }
        }

        // Update status display
        function updateStatusDisplay() {
            const statusBadge = document.getElementById('status-badge');
            const statusMessage = document.getElementById('status-message');
            const phoneNumber = document.getElementById('phone-number');
            const lastUpdated = document.getElementById('last-updated');
            const progressBar = document.getElementById('connection-progress');
            
            // Update badge
            if (currentState.isConnected) {
                statusBadge.innerHTML = '<span class="status-badge status-connected"><i class="bi bi-check-circle"></i> CONNECTED</span>';
                statusMessage.textContent = 'WhatsApp Bot Terhubung';
                progressBar.className = 'progress-bar bg-success';
                progressBar.style.width = '100%';
            } else if (currentState.qrCode) {
                statusBadge.innerHTML = '<span class="status-badge status-waiting"><i class="bi bi-hourglass-split"></i> WAITING FOR SCAN</span>';
                statusMessage.textContent = 'Scan QR Code untuk menghubungkan';
                progressBar.className = 'progress-bar bg-warning';
                progressBar.style.width = '50%';
            } else {
                statusBadge.innerHTML = '<span class="status-badge status-disconnected"><i class="bi bi-plug"></i> DISCONNECTED</span>';
                statusMessage.textContent = 'WhatsApp Bot Tidak Terhubung';
                progressBar.className = 'progress-bar bg-danger';
                progressBar.style.width = '25%';
            }
            
            // Update details
            phoneNumber.textContent = currentState.phoneNumber || 'Not connected';
            lastUpdated.textContent = formatDateTime(currentState.lastUpdate);
            
            // Update QR status
            document.getElementById('qr-status').textContent = 
                currentState.isConnected ? 'WhatsApp terhubung' : 
                currentState.qrCode ? 'Scan untuk menghubungkan' : 'Menunggu QR code';
        }

        // Update QR display
        function updateQRDisplay() {
            const qrContainer = document.getElementById('qr-container');
            const qrContent = document.getElementById('qr-content');
            const qrLoading = document.getElementById('qr-loading');
            const qrCodeDiv = document.getElementById('qr-code');
            
            if (currentState.qrCode) {
                // Hide loading, show QR
                qrLoading.style.display = 'none';
                qrCodeDiv.style.display = 'block';
                
                // Generate QR code
                generateQRCode(qrCodeDiv, currentState.qrCode);
                
                // Enable buttons
                enableQRButtons(true);
                
                addDebugLog('✅ QR Code berhasil dibuat');
            } else if (currentState.isConnected) {
                // Show connected message
                qrLoading.style.display = 'none';
                qrCodeDiv.style.display = 'block';
                qrCodeDiv.innerHTML = `
                    <div class="alert alert-success text-center">
                        <i class="bi bi-check-circle-fill" style="font-size: 3rem;"></i>
                        <h4 class="mt-3">WhatsApp Terhubung</h4>
                        <p class="mb-0">Bot sudah terhubung dengan WhatsApp</p>
                        <small class="text-muted">${currentState.phoneNumber || 'Phone number not available'}</small>
                    </div>
                `;
                
                // Disable QR buttons
                enableQRButtons(false);
            } else {
                // Show loading
                qrLoading.style.display = 'block';
                qrCodeDiv.style.display = 'none';
                qrCodeDiv.innerHTML = '';
                
                // Disable buttons
                enableQRButtons(false);
            }
        }

        // Generate QR code
        function generateQRCode(container, qrData) {
            try {
                container.innerHTML = '';
                new QRCode(container, {
                    text: qrData,
                    width: 280,
                    height: 280,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H
                });
            } catch (error) {
                console.error('QR generation error:', error);
                container.innerHTML = `
                    <div class="alert alert-warning">
                        <p>QR Code Data:</p>
                        <code style="word-break: break-all; font-size: 0.8rem;">${qrData.substring(0, 100)}...</code>
                        <p class="mt-2 small">Gunakan aplikasi QR scanner manual</p>
                    </div>
                `;
            }
        }

        // Handle connected state
        function handleConnectedState() {
            addDebugLog('✅ WhatsApp terhubung!', 'success');
            
            // Update server health
            document.getElementById('server-health').className = 'badge bg-success';
            document.getElementById('server-health').textContent = 'Healthy';
            
            // Show success toast
            showToast(`WhatsApp terhubung dengan ${currentState.phoneNumber || 'unknown number'}`, 'success');
            
            // Clear any existing QR refresh interval
            if (currentState.qrRefreshInterval) {
                clearInterval(currentState.qrRefreshInterval);
            }
            
            // Add to history
            addHistoryItem('CONNECTED', `Terhubung dengan ${currentState.phoneNumber}`);
        }

        // Handle QR state
        function handleQRState() {
            addDebugLog('📱 QR Code tersedia, menunggu scan...', 'info');
            
            // Start QR expiry countdown
            startQRExpiryCountdown();
            
            // Add to history
            addHistoryItem('QR_GENERATED', 'QR Code dihasilkan');
        }

        // Handle disconnected state
        function handleDisconnectedState() {
            addDebugLog('⚠️ WhatsApp tidak terhubung', 'warning');
            
            // Update server health
            document.getElementById('server-health').className = 'badge bg-warning';
            document.getElementById('server-health').textContent = 'Disconnected';
            
            // Add to history
            addHistoryItem('DISCONNECTED', 'WhatsApp terputus');
        }

        // Show loading state
        function showLoading() {
            const statusBadge = document.getElementById('status-badge');
            statusBadge.innerHTML = '<span class="status-badge status-loading"><i class="bi bi-arrow-repeat"></i> LOADING...</span>';
            
            document.getElementById('status-message').textContent = 'Memuat status...';
            document.getElementById('phone-number').textContent = '-';
            document.getElementById('last-updated').textContent = '-';
        }

        // Show error state
        function showErrorState(message) {
            const statusBadge = document.getElementById('status-badge');
            statusBadge.innerHTML = '<span class="status-badge status-error"><i class="bi bi-exclamation-triangle"></i> ERROR</span>';
            
            document.getElementById('status-message').textContent = 'Terjadi kesalahan';
            document.getElementById('phone-number').textContent = '-';
            document.getElementById('last-updated').textContent = '-';
            
            const qrContainer = document.getElementById('qr-container');
            qrContainer.innerHTML = `
                <div class="alert alert-danger text-center">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <h5 class="mt-2">Error</h5>
                    <p>${message}</p>
                    <button class="btn btn-sm btn-outline-danger mt-2" onclick="loadStatus()">
                        <i class="bi bi-arrow-clockwise"></i> Coba Lagi
                    </button>
                </div>
            `;
        }

        // Enable/disable QR buttons
        function enableQRButtons(enabled) {
            document.getElementById('download-btn').disabled = !enabled;
            document.getElementById('fullscreen-btn').disabled = !enabled;
            document.getElementById('copy-btn').disabled = !enabled;
        }

        // Setup auto-refresh
        function setupAutoRefresh() {
            // Start refresh timer
            setInterval(() => {
                if (currentState.autoRefresh) {
                    currentState.refreshTimer--;
                    document.getElementById('refresh-timer').textContent = `${currentState.refreshTimer}s`;
                    
                    if (currentState.refreshTimer <= 0) {
                        refreshStatus(false);
                        currentState.refreshTimer = 30;
                    }
                }
            }, 1000);
            
            // Start auto-refresh interval
            startAutoRefresh();
        }

        // Start auto-refresh
        function startAutoRefresh() {
            if (currentState.refreshInterval) {
                clearInterval(currentState.refreshInterval);
            }
            
            currentState.refreshInterval = setInterval(() => {
                if (currentState.autoRefresh && !currentState.isConnected) {
                    refreshStatus(false);
                }
            }, 30000); // 30 seconds
            
            document.getElementById('refresh-indicator').style.display = 'block';
        }

        // Stop auto-refresh
        function stopAutoRefresh() {
            if (currentState.refreshInterval) {
                clearInterval(currentState.refreshInterval);
                currentState.refreshInterval = null;
            }
            
            document.getElementById('refresh-indicator').style.display = 'none';
            document.getElementById('refresh-timer').textContent = 'Paused';
        }

        // Start QR expiry countdown
        function startQRExpiryCountdown() {
            let timeLeft = 300; // 5 minutes in seconds
            
            const countdown = setInterval(() => {
                if (!currentState.qrCode || currentState.isConnected) {
                    clearInterval(countdown);
                    return;
                }
                
                timeLeft--;
                const minutes = Math.floor(timeLeft / 60);
                const seconds = timeLeft % 60;
                
                document.getElementById('qr-status').textContent = 
                    `Kadaluarsa dalam: ${minutes}:${seconds.toString().padStart(2, '0')}`;
                
                if (timeLeft <= 0) {
                    clearInterval(countdown);
                    document.getElementById('qr-status').textContent = 'QR Code expired';
                    addDebugLog('⚠️ QR Code expired', 'warning');
                }
            }, 1000);
        }

        // Refresh status
        async function refreshStatus(force = false) {
            try {
                const url = force ? `${STATUS_API_URL}?force_refresh=true` : STATUS_API_URL;
                addDebugLog(`🔄 Refreshing status${force ? ' (force)' : ''}...`);
                
                const response = await fetch(url);
                const result = await response.json();
                
                if (result.success) {
                    processStatusData(result.data);
                    if (force) {
                        showToast('Status diperbarui', 'success');
                    }
                }
            } catch (error) {
                addDebugLog(`❌ Refresh error: ${error.message}`, 'error');
            }
        }

        // Restart bot
        async function restartBot() {
            if (!confirm('Restart WhatsApp bot?\nKoneksi saat ini akan terputus sementara.')) {
                return;
            }
            
            try {
                addDebugLog('🔄 Restarting WhatsApp bot...');
                showToast('Merestart bot...', 'info');
                
                const response = await fetch(RESTART_API_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast('Bot berhasil direstart', 'success');
                    setTimeout(() => loadStatus(), 3000);
                } else {
                    throw new Error(result.message || 'Restart failed');
                }
            } catch (error) {
                showToast(`Error: ${error.message}`, 'error');
                addDebugLog(`❌ Restart error: ${error.message}`, 'error');
            }
        }

        // Force new session
        async function forceNewSession() {
            if (!confirm('Buat session baru?\nQR Code saat ini akan kadaluarsa dan koneksi terputus.')) {
                return;
            }
            
            try {
                addDebugLog('🔄 Creating new session...');
                showToast('Membuat session baru...', 'info');
                
                const response = await fetch(FORCE_NEW_API_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast('Session baru dibuat', 'success');
                    setTimeout(() => loadStatus(), 3000);
                } else {
                    throw new Error(result.message || 'Failed to create new session');
                }
            } catch (error) {
                showToast(`Error: ${error.message}`, 'error');
                addDebugLog(`❌ New session error: ${error.message}`, 'error');
            }
        }

        // Download QR code
        function downloadQRCode() {
            if (!currentState.qrCode) {
                showToast('Tidak ada QR Code untuk didownload', 'warning');
                return;
            }
            
            const qrCodeDiv = document.getElementById('qr-code');
            const canvas = qrCodeDiv.querySelector('canvas');
            
            if (canvas) {
                const link = document.createElement('a');
                const timestamp = new Date().toISOString().slice(0,19).replace(/[:]/g, '-');
                link.download = `whatsapp-qr-${timestamp}.png`;
                link.href = canvas.toDataURL('image/png');
                link.click();
                showToast('QR Code berhasil didownload', 'success');
            } else {
                showToast('Tidak dapat mendownload QR Code', 'error');
            }
        }

        // Copy QR code data
        function copyQRCode() {
            if (!currentState.qrCode) {
                showToast('Tidak ada QR Code untuk disalin', 'warning');
                return;
            }
            
            navigator.clipboard.writeText(currentState.qrCode)
                .then(() => {
                    showToast('QR Code berhasil disalin', 'success');
                })
                .catch(err => {
                    showToast('Gagal menyalin QR Code', 'error');
                });
        }

        // Show QR fullscreen
        function showQRFullscreen() {
            if (!currentState.qrCode) {
                showToast('Tidak ada QR Code', 'warning');
                return;
            }
            
            const fullscreenDiv = document.getElementById('qr-fullscreen');
            fullscreenDiv.innerHTML = '';
            generateQRCode(fullscreenDiv, currentState.qrCode);
            
            const modal = new bootstrap.Modal(document.getElementById('qrModal'));
            modal.show();
        }

        // Load connection history
        async function loadHistory() {
            try {
                const response = await fetch(HISTORY_API_URL);
                const result = await response.json();
                
                if (result.success) {
                    updateHistoryDisplay(result.data);
                }
            } catch (error) {
                console.error('Error loading history:', error);
            }
        }

        // Update history display
        function updateHistoryDisplay(historyData) {
            const historyList = document.getElementById('history-list');
            
            if (!historyData || historyData.length === 0) {
                historyList.innerHTML = '<div class="text-center text-muted small">Belum ada history</div>';
                return;
            }
            
            let html = '';
            historyData.slice(0, 10).forEach(item => {
                const time = formatDateTime(item.created_at);
                const statusClass = item.status === 'CONNECTED' ? 'success' : 
                                  item.status === 'DISCONNECTED' ? 'disconnected' : 'error';
                
                html += `
                    <div class="history-item ${statusClass}">
                        <div class="d-flex justify-content-between">
                            <strong>${item.status}</strong>
                            <small>${time.split(' ')[1]}</small>
                        </div>
                        <small class="text-muted">
                            ${item.phone_number ? item.phone_number : 'No phone number'}
                        </small>
                    </div>
                `;
            });
            
            historyList.innerHTML = html;
        }

        // Add history item
        function addHistoryItem(status, message) {
            const historyList = document.getElementById('history-list');
            const time = formatDateTime(new Date().toISOString());
            const statusClass = status === 'CONNECTED' ? 'success' : 
                              status === 'DISCONNECTED' ? 'disconnected' : 'error';
            
            const itemHtml = `
                <div class="history-item ${statusClass}">
                    <div class="d-flex justify-content-between">
                        <strong>${status}</strong>
                        <small>${time.split(' ')[1]}</small>
                    </div>
                    <small class="text-muted">${message}</small>
                </div>
            `;
            
            // Add to top
            historyList.innerHTML = itemHtml + historyList.innerHTML;
            
            // Limit to 10 items
            const items = historyList.querySelectorAll('.history-item');
            if (items.length > 10) {
                items[10].remove();
            }
        }

        // Update debug info
        function updateDebugInfo() {
            document.getElementById('debug-status').textContent = currentState.status;
            document.getElementById('debug-status').className = 
                `badge ${currentState.isConnected ? 'bg-success' : 'bg-warning'}`;
            
            document.getElementById('debug-phone').textContent = currentState.phoneNumber || '-';
            document.getElementById('debug-session').textContent = currentState.sessionName || '-';
            document.getElementById('debug-heartbeat').textContent = currentState.heartbeatActive ? 'Active' : 'Inactive';
        }

        // Add debug log
        function addDebugLog(message, type = 'info') {
            const debugLog = document.getElementById('debug-log');
            const time = new Date().toLocaleTimeString('id-ID');
            
            const logItem = document.createElement('div');
            logItem.className = `mb-1 ${type === 'error' ? 'text-danger' : type === 'success' ? 'text-success' : 'text-muted'}`;
            logItem.innerHTML = `<small>[${time}] ${message}</small>`;
            
            debugLog.prepend(logItem);
            
            // Limit log items
            const items = debugLog.querySelectorAll('div');
            if (items.length > 50) {
                items[50].remove();
            }
            
            // Add to current state
            currentState.debugLog.unshift({ time, message, type });
        }

        // Clear debug log
        function clearDebugLog() {
            document.getElementById('debug-log').innerHTML = 
                '<div class="text-muted">Debug log cleared</div>';
            currentState.debugLog = [];
        }

        // Toggle debug panel
        function toggleDebug() {
            const debugPanel = document.getElementById('debug-panel');
            const debugBtn = document.getElementById('debug-btn');
            
            if (debugPanel.style.display === 'none') {
                debugPanel.style.display = 'block';
                debugBtn.innerHTML = '<i class="bi bi-bug-fill"></i> Hide Debug';
            } else {
                debugPanel.style.display = 'none';
                debugBtn.innerHTML = '<i class="bi bi-bug"></i> Debug';
            }
        }

        // Setup real-time updates (EventSource/WebSocket)
        function setupRealtimeUpdates() {
            // Try using EventSource first
            if (typeof EventSource !== 'undefined') {
                try {
                    const eventSource = new EventSource(`${WEBHOOK_URL}/stream`);
                    
                    eventSource.onmessage = function(event) {
                        try {
                            const data = JSON.parse(event.data);
                            if (data.type === 'status_update') {
                                addDebugLog('📡 Real-time update received', 'info');
                                processStatusData(data.data);
                            }
                        } catch (e) {
                            console.error('Error parsing event:', e);
                        }
                    };
                    
                    eventSource.onerror = function() {
                        addDebugLog('⚠️ Real-time connection lost', 'warning');
                    };
                    
                    addDebugLog('✅ Real-time updates enabled (EventSource)', 'success');
                } catch (error) {
                    addDebugLog(`❌ Real-time error: ${error.message}`, 'error');
                }
            }
        }

        // Show toast notification
        function showToast(message, type = 'info') {
            const toastContainer = document.getElementById('toastContainer');
            
            const toastId = 'toast-' + Date.now();
            const toastHtml = `
                <div id="${toastId}" class="toast align-items-center text-white bg-${type} border-0" role="alert">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="bi ${type === 'success' ? 'bi-check-circle' : 
                                        type === 'error' ? 'bi-exclamation-triangle' : 
                                        'bi-info-circle'} me-2"></i>
                            ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            `;
            
            toastContainer.insertAdjacentHTML('beforeend', toastHtml);
            
            const toastElement = document.getElementById(toastId);
            const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
            toast.show();
            
            toastElement.addEventListener('hidden.bs.toast', function () {
                toastElement.remove();
            });
        }

        // Format date time
        function formatDateTime(isoString) {
            const date = new Date(isoString);
            return date.toLocaleString('id-ID', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + R to refresh
            if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
                e.preventDefault();
                refreshStatus(true);
            }
            // Ctrl/Cmd + F for force refresh
            if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                e.preventDefault();
                loadStatus();
            }
            // Ctrl/Cmd + D for debug
            if ((e.ctrlKey || e.metaKey) && e.key === 'd') {
                e.preventDefault();
                toggleDebug();
            }
            // Ctrl/Cmd + H for history
            if ((e.ctrlKey || e.metaKey) && e.key === 'h') {
                e.preventDefault();
                loadHistory();
            }
        });
    </script>
</body>
</html>