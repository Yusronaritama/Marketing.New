@extends('layouts.admin.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold text-gray-800">Daftar Gagal Sync Moodle</h1>
            
            <div>
                <button id="btnStartSync" onclick="startSyncProcess()" class="bg-blue-600 hover:bg-blue-700 text-black font-bold py-2 px-4 rounded shadow flex items-center transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span id="btnText">Mulai Sync Otomatis</span>
                </button>
            </div>
        </div>

        @php
            $totalFailed = \DB::table('master_moodle')->where('status', 'failed')->count();
            $totalEnrolFailed = \DB::table('master_moodle')
                ->where('status', 'success')
                ->where('enrol_status', 'enrol_failed')
                ->count();
        @endphp

        <div class="grid grid-cols-3 gap-4">
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="text-red-600 text-sm font-semibold mb-1">User Creation Failed</div>
                <div class="text-3xl font-bold text-red-700">{{ $totalFailed }}</div>
                <div class="text-xs text-red-500 mt-1">Belum dibuat di Moodle</div>
            </div>
            
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="text-yellow-600 text-sm font-semibold mb-1">Enrollment Failed</div>
                <div class="text-3xl font-bold text-yellow-700">{{ $totalEnrolFailed }}</div>
                <div class="text-xs text-yellow-500 mt-1">User ada, belum dienroll</div>
            </div>
            
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="text-blue-600 text-sm font-semibold mb-1">Total Error</div>
                <div class="text-3xl font-bold text-blue-700">{{ $totalFailed + $totalEnrolFailed }}</div>
                <div class="text-xs text-blue-500 mt-1">Proses 1 user/batch</div>
            </div>
        </div>
    </div>

    <div id="progressContainer" class="hidden mb-6 bg-white p-4 rounded shadow border border-gray-200">
        <h3 class="font-bold text-lg mb-2">Proses Sinkronisasi (1 User Per Request)</h3>
        
        <div class="w-full bg-gray-200 rounded-full h-4 mb-2">
            <div id="progressBar" class="bg-blue-600 h-4 rounded-full transition-all duration-500" style="width: 0%"></div>
        </div>
        
        <div class="flex justify-between text-sm font-medium text-gray-600">
            <span id="statusText">Menyiapkan...</span>
            <span id="counterText">0 / ??</span>
        </div>

        <div id="logConsole" class="mt-4 h-40 overflow-y-auto bg-gray-900 text-green-400 p-2 text-xs font-mono rounded">
            <div>> Siap memulai proses...</div>
        </div>
    </div>

    {{-- Tabel Data --}}
    <div class="bg-white shadow-md rounded my-6 overflow-x-auto">
        <table class="min-w-full w-full table-auto">
            <thead>
                <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                    <th class="py-3 px-6 text-left">VA Number</th>
                    <th class="py-3 px-6 text-left">Nama</th>
                    <th class="py-3 px-6 text-center">Status</th>
                    <th class="py-3 px-6 text-left">Error Message</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 text-sm font-light">
                @forelse($failedUsers as $user)
                    <tr class="border-b border-gray-200 hover:bg-gray-100">
                        <td class="py-3 px-6 text-left font-medium">{{ $user->username }}</td>
                        <td class="py-3 px-6 text-left">{{ $user->name }}</td>
                        <td class="py-3 px-6 text-center">
                            <span class="bg-red-200 text-red-600 py-1 px-3 rounded-full text-xs">{{ $user->status }}</span>
                        </td>
                        <td class="py-3 px-6 text-left max-w-xs truncate">{{ Str::limit($user->error_message, 50) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-3 px-6 text-center text-lg text-gray-500">Tidak ada error.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    const ajaxUrl = "{{ route('admin.moodle.retry.ajax') }}";
    const csrfToken = "{{ csrf_token() }}";
    
    let totalErrors = {{ $failedUsers->total() }}; 
    let processedTotal = 0;
    let consecutiveErrors = 0;
    const maxConsecutiveErrors = 3;
    
    // PENTING: Delay lebih panjang untuk server lambat
    const delayBetweenBatches = 5000;  // 5 detik (dari 3 detik)
    const errorRetryDelay = 15000;     // 15 detik jika error (dari 10 detik)

    async function startSyncProcess() {
        const btn = document.getElementById('btnStartSync');
        const btnText = document.getElementById('btnText');
        const container = document.getElementById('progressContainer');
        
        btn.disabled = true;
        btn.classList.add('opacity-50', 'cursor-not-allowed');
        btnText.innerText = "Sedang Memproses...";
        container.classList.remove('hidden');

        addLog("⚙ Memulai koneksi ke server Moodle...");
        addLog("📌 Mode: 1 User Per Request (Slow & Stable)");

        let keepGoing = true;

        while (keepGoing) {
            try {
                addLog("📤 Mengirim request ke server...");
                
                // Timeout 3 menit untuk frontend
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 180000); // 3 menit

                const response = await fetch(ajaxUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    signal: controller.signal
                });

                clearTimeout(timeoutId);

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                consecutiveErrors = 0; // Reset error counter

                if (data.status === 'done') {
                    keepGoing = false;
                    finishProcess();
                } else if (data.status === 'partial') {
                    processedTotal += data.processed_count;
                    const remaining = data.remaining;
                    
                    const processType = data.process_type === 'enrol_only' ? '📝 ENROL' : '👤 CREATE';
                    const emoji = data.success > 0 ? '✅' : '⚠️';
                    addLog(`${emoji} ${processType} | Sukses: ${data.success}, Gagal: ${data.fail} | Sisa: ${remaining}`);
                    
                    // Update Progress
                    let percent = 0;
                    if(totalErrors > 0) {
                         percent = Math.round(((totalErrors - remaining) / totalErrors) * 100);
                    }
                    percent = Math.max(0, Math.min(100, percent));

                    document.getElementById('progressBar').style.width = percent + "%";
                    document.getElementById('counterText').innerText = `Sisa: ${remaining}`;
                    
                    const statusMsg = data.process_type === 'enrol_only' ? 
                        'Memproses enrollment...' : 'Membuat user & enrollment...';
                    document.getElementById('statusText').innerText = statusMsg;

                    if (remaining <= 0) {
                        keepGoing = false;
                        finishProcess();
                    } else {
                        addLog(`⏳ Delay ${delayBetweenBatches/1000}s sebelum batch berikutnya...`);
                        await sleep(delayBetweenBatches);
                    }
                } else if (data.status === 'error') {
                    throw new Error(data.message || 'Unknown server error');
                }

            } catch (error) {
                consecutiveErrors++;
                console.error(error);
                
                if (error.name === 'AbortError') {
                    addLog(`❌ ERROR: Request timeout (>3 menit)`);
                } else {
                    addLog(`❌ ERROR: ${error.message}`);
                }
                
                if (consecutiveErrors >= maxConsecutiveErrors) {
                    addLog(`🛑 Terlalu banyak error berturut (${consecutiveErrors}x). Proses dihentikan.`);
                    addLog("💡 Saran: Cek koneksi internet atau status server Moodle.");
                    
                    btn.disabled = false;
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                    btnText.innerText = "Coba Lagi";
                    
                    keepGoing = false;
                } else {
                    addLog(`🔄 Retry dalam ${errorRetryDelay/1000}s... (${consecutiveErrors}/${maxConsecutiveErrors})`);
                    await sleep(errorRetryDelay);
                }
            }
        }
    }

    function finishProcess() {
        document.getElementById('progressBar').style.width = "100%";
        document.getElementById('progressBar').classList.add('bg-green-500');
        document.getElementById('progressBar').classList.remove('bg-blue-600');
        
        document.getElementById('statusText').innerText = "SELESAI!";
        document.getElementById('counterText').innerText = "0 Sisa";
        addLog("🎉 Semua proses selesai!");
        addLog("🔄 Refresh halaman dalam 3 detik...");
        
        setTimeout(() => {
            location.reload();
        }, 3000);
    }

    function addLog(message) {
        const consoleDiv = document.getElementById('logConsole');
        const time = new Date().toLocaleTimeString('id-ID', { hour12: false });
        const newLine = document.createElement('div');
        newLine.innerHTML = `<span class="text-gray-500">[${time}]</span> ${message}`;
        consoleDiv.appendChild(newLine);
        consoleDiv.scrollTop = consoleDiv.scrollHeight;
    }

    function sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
</script>
@endsection