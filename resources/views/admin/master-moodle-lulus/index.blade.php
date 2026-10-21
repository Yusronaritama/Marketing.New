@extends('layouts.admin.app')

@section('title', 'Kelola Kelulusan Moodle')

@section('content')

<style>
    .cyber-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
    }

    .dark .cyber-card {
        background: #1f2937;
        border: 1px solid #374151;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
    }

    .cyber-glow-side {
        position: relative;
    }

    .cyber-glow-side::before {
        content: '';
        position: absolute;
        top: 0;
        left: -2px;
        width: 3px;
        height: 100%;
        background: linear-gradient(180deg, #4f46e5, #818cf8);
        border-radius: 2px;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .cyber-glow-side:hover::before {
        opacity: 1;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
        padding: 0.25rem 0.75rem;
    }

    .badge-success {
        background-color: #dcfce7;
        color: #166534;
    }

    .badge-warning {
        background-color: #fef3c7;
        color: #92400e;
    }

    .badge-danger {
        background-color: #fef2f2;
        color: #991b1b;
    }

    .badge-info {
        background-color: #dbeafe;
        color: #1e40af;
    }

    .dark .badge-success {
        background-color: rgba(21, 128, 61, 0.2);
        color: #4ade80;
    }

    .dark .badge-warning {
        background-color: rgba(146, 64, 14, 0.2);
        color: #fbbf24;
    }

    .dark .badge-danger {
        background-color: rgba(185, 28, 28, 0.2);
        color: #f87171;
    }

    .dark .badge-info {
        background-color: rgba(30, 64, 175, 0.2);
        color: #60a5fa;
    }

    .select-all-checkbox {
        transform: scale(1.1);
    }

    .action-buttons {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .stats-card {
        background: linear-gradient(135deg, #4f46e5 0%, #818cf8 100%);
        color: white;
        border-radius: 0.75rem;
        padding: 1.5rem;
        position: relative;
        overflow: hidden;
    }

    .stats-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 100px;
        height: 100px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        transform: translate(30%, -30%);
    }

    .dark .stats-card {
        background: linear-gradient(135deg, #6366f1 0%, #818cf8 100%);
    }

    .quiz-score {
        font-size: 0.7rem;
        padding: 0.1rem 0.4rem;
        border-radius: 0.25rem;
        background: rgba(79, 70, 229, 0.1);
        color: #4f46e5;
    }

    .dark .quiz-score {
        background: rgba(99, 102, 241, 0.2);
        color: #818cf8;
    }
</style>

<div class="grid grid-cols-12 gap-4 md:gap-6">
    <div class="col-span-12">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-4">
            <!-- Total Users Card -->
            <div class="cyber-card cyber-glow-side rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-2xl font-bold text-gray-900 dark:text-white" id="totalUsers">0</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Total Users</p>
                    </div>
                    <div class="p-3 rounded-lg bg-blue-100 dark:bg-blue-900/30">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Lulus Users Card -->
            <div class="cyber-card cyber-glow-side rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-2xl font-bold text-gray-900 dark:text-white" id="lulusUsers">0</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Sudah Lulus</p>
                    </div>
                    <div class="p-3 rounded-lg bg-green-100 dark:bg-green-900/30">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Belum Lulus Card -->
            <div class="cyber-card cyber-glow-side rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-2xl font-bold text-gray-900 dark:text-white" id="belumLulusUsers">0</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Belum Lulus</p>
                    </div>
                    <div class="p-3 rounded-lg bg-yellow-100 dark:bg-yellow-900/30">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Percentage Card -->
            <div class="stats-card">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-2xl font-bold dark:text-white" id="lulusPercentage">0%</h4>
                        <p class="text-sm text-blue-100">Presentase Kelulusan</p>
                    </div>
                    <div class="p-3 rounded-lg bg-white/20">
                        <svg class="w-6 h-6 dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Stats -->
        <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-3">
            <div class="cyber-card cyber-glow-side rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white" id="quizAttempts">0</h4>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Quiz Attempts</p>
                    </div>
                    <div class="p-2 rounded-lg bg-purple-100 dark:bg-purple-900/30">
                        <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="cyber-card cyber-glow-side rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white" id="masterLulus">0</h4>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Master Lulus</p>
                    </div>
                    <div class="p-2 rounded-lg bg-indigo-100 dark:bg-indigo-900/30">
                        <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="cyber-card cyber-glow-side rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white" id="manualLulus">0</h4>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Manual Lulus</p>
                    </div>
                    <div class="p-2 rounded-lg bg-pink-100 dark:bg-pink-900/30">
                        <svg class="w-4 h-4 text-pink-600 dark:text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="cyber-card cyber-glow-side rounded-xl px-4 pb-3 pt-4 sm:px-6">
            <div class="flex flex-col gap-4 mb-6 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Kelola Kelulusan Moodle
                    </h3>
                    <p class="mt-1 text-gray-600 dark:text-gray-400 text-theme-sm">
                        Kelola status kelulusan users berdasarkan multiple sources
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Bulk Actions -->
                    <div class="flex items-center gap-2" id="bulkActions" style="display: none;">
                        <button onclick="luluskanMultiple()" class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2.5 text-theme-sm font-medium dark:text-white hover:bg-green-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Luluskan Selected
                        </button>
                        <span id="selectedCount" class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full dark:bg-blue-900 dark:text-blue-200">0</span>
                    </div>

                    <!-- Filter Status -->
                    <select id="statusFilter" onchange="filterTable()" class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-theme-sm text-gray-900 dark:text-white focus:border-blue-500 dark:focus:border-blue-500">
                        <option value="all">Semua Status</option>
                        <option value="lulus">Sudah Lulus</option>
                        <option value="belum_lulus">Belum Lulus</option>
                    </select>

                    <!-- Search -->
                    <div class="relative">
                        <input type="text" id="searchInput" placeholder="Cari username atau nama..."
                            class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 pl-10 pr-4 py-2.5 text-theme-sm text-gray-900 dark:text-white focus:border-blue-500 dark:focus:border-blue-500 w-64">
                        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Table -->
<div class="max-w-full overflow-x-auto custom-scrollbar">
    <table class="w-full table-auto">
        <thead>
            <tr class="bg-gray-50 dark:bg-gray-800 text-left">
                <th class="px-4 py-3 font-semibold text-gray-900 dark:text-white text-theme-sm w-12">
                    <input type="checkbox" id="selectAll" class="select-all-checkbox" onchange="toggleSelectAll()">
                </th>
                <th class="px-4 py-3 font-semibold text-gray-900 dark:text-white text-theme-sm min-w-[120px]">
                    Username
                </th>
                <th class="px-4 py-3 font-semibold text-gray-900 dark:text-white text-theme-sm min-w-[120px]">
                    User Moodle ID
                </th>
                <th class="px-4 py-3 font-semibold text-gray-900 dark:text-white text-theme-sm min-w-[150px]">
                    Nama User
                </th>
                <th class="px-4 py-3 font-semibold text-gray-900 dark:text-white text-theme-sm min-w-[120px]">
                    Status Kelulusan
                </th>
                <th class="px-4 py-3 font-semibold text-gray-900 dark:text-white text-theme-sm min-w-[100px]">
                    Quiz Score
                </th>
                <th class="px-4 py-3 font-semibold text-gray-900 dark:text-white text-theme-sm min-w-[120px]">
                    Sumber
                </th>
                <th class="px-4 py-3 font-semibold text-gray-900 dark:text-white text-theme-sm min-w-[120px]">
                    Aksi
                </th>
            </tr>
        </thead>
        <tbody id="tableBody">
            @forelse($masterMoodle as $item)
            @php
                $status = $item->getLulusStatus();
                $isLulus = $status['is_lulus'];
                $sources = $status['sources'];
                
                // AMBIL NILAI QUIZ DARI master_moodle_lulus (kolom nilai_quiz)
                $quizScore = $item->masterMoodleLulus ? $item->masterMoodleLulus->nilai_quiz : 0;
                
                // Jika tidak ada di master_moodle_lulus, ambil dari quiz attempts sebagai fallback
                if ($quizScore == 0 && $item->quizAttempts->count() > 0) {
                    $quizScore = $item->quizAttempts->where('state', 'finished')->max('sumgrades') ?? 0;
                }
                
                // Cek apakah ini kelulusan manual untuk tombol batalkan
                $isManual = false;
                if ($item->masterMoodleLulus) {
                    $isManual = $item->masterMoodleLulus->nilai_quiz == 100.00 && 
                               $item->masterMoodleLulus->attempt_count == 1 &&
                               str_contains($item->masterMoodleLulus->keterangan ?? '', 'manual');
                }
                if (!$isManual && str_contains($item->sync_notes ?? '', 'manual')) {
                    $isManual = true;
                }
            @endphp
            <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                <td class="px-4 py-4">
                    <input type="checkbox" class="user-checkbox" value="{{ $item->id }}" onchange="updateBulkActions()">
                </td>
                <td class="px-4 py-4">
                    <p class="font-medium text-gray-900 dark:text-white">{{ $item->username }}</p>
                </td>
                <td class="px-4 py-4">
                    <p class="text-gray-600 dark:text-gray-400">{{ $item->moodle_user_id }}</p>
                </td>
                <td class="px-4 py-4">
                    <p class="text-gray-600 dark:text-gray-400">
                        {{ $item->user->name ?? 'N/A' }}
                    </p>
                </td>
                <td class="px-4 py-4">
                    @if($isLulus)
                    <span class="status-badge badge-success">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        LULUS
                    </span>
                    @else
                    <span class="status-badge badge-warning">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        BELUM LULUS
                    </span>
                    @endif
                </td>
                <td class="px-4 py-4">
                    @if($quizScore > 0)
                    <span class="quiz-score">
                        {{ number_format($quizScore, 1) }}
                    </span>
                    @else
                    <span class="text-gray-400 text-sm">-</span>
                    @endif
                </td>
                <td class="px-4 py-4">
                    <div class="flex flex-col gap-1">
                        @foreach($sources as $source)
                        <span class="text-xs px-2 py-1 rounded 
                            @if($source === 'sync_notes') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                            @elseif($source === 'master_moodle_lulus') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                            @elseif($source === 'quiz_attempt') bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200
                            @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200
                            @endif">
                            {{ ucfirst(str_replace('_', ' ', $source)) }}
                        </span>
                        @endforeach
                        @if(empty($sources))
                        <span class="text-xs text-gray-400">-</span>
                        @endif
                    </div>
                </td>
                <td class="px-4 py-4">
                    <div class="action-buttons">
                        @if(!$isLulus)
                        <button onclick="luluskanUser({{ $item->id }})" class="inline-flex items-center gap-1 rounded-lg bg-green-600 px-3 py-1.5 text-xs font-medium dark:text-white hover:bg-green-700 transition-colors">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Luluskan
                        </button>
                        @else
                            @if($isManual)
                            <button onclick="batalkanLulus({{ $item->id }})" class="inline-flex items-center gap-1 rounded-lg bg-yellow-600 px-3 py-1.5 text-xs font-medium dark:text-white hover:bg-yellow-700 transition-colors">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Batalkan
                            </button>
                            @else
                            <button disabled class="inline-flex items-center gap-1 rounded-lg bg-gray-400 px-3 py-1.5 text-xs font-medium dark:text-white cursor-not-allowed" title="Tidak dapat dibatalkan (Lulus Sistem)">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Sistem
                            </button>
                            @endif
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                    Tidak ada data yang ditemukan
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

            <!-- Pagination -->
            <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Menampilkan {{ $masterMoodle->firstItem() ?? 0 }} - {{ $masterMoodle->lastItem() ?? 0 }} dari {{ $masterMoodle->total() }} data
                </p>
                <div class="flex gap-2">
                    {{ $masterMoodle->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Global variables
let selectedUsers = new Set();
let searchTimeout = null;
// Load statistics on page load
document.addEventListener('DOMContentLoaded', function() {
    loadStatistics();
    
    // Set filter from URL
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    const search = urlParams.get('search');
    
    if (status) {
        document.getElementById('statusFilter').value = status;
    }
    if (search) {
        document.getElementById('searchInput').value = search;
    }
});

// Load statistics
function loadStatistics() {
    fetch('{{ route("admin.master-moodle-lulus.statistics") }}', {
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            document.getElementById('totalUsers').textContent = data.data.total_users;
            document.getElementById('lulusUsers').textContent = data.data.lulus_users;
            document.getElementById('belumLulusUsers').textContent = data.data.belum_lulus_users;
            document.getElementById('lulusPercentage').textContent = data.data.lulus_percentage + '%';
            
            // Additional stats
            document.getElementById('quizAttempts').textContent = data.data.lulus_from_quiz || 0;
            document.getElementById('masterLulus').textContent = data.data.lulus_from_master_lulus || 0;
            document.getElementById('manualLulus').textContent = data.data.lulus_from_sync_notes || 0;
        }
    })
    .catch(error => {
        console.error('Error loading statistics:', error);
        // Fallback values
        document.getElementById('totalUsers').textContent = '0';
        document.getElementById('lulusUsers').textContent = '0';
        document.getElementById('belumLulusUsers').textContent = '0';
        document.getElementById('lulusPercentage').textContent = '0%';
    });
}

// Filter table
function filterTable() {
    const status = document.getElementById('statusFilter').value.toLowerCase();
    const tableRows = document.querySelectorAll('#tableBody tr');

    tableRows.forEach(row => {
        // Ambil teks status dari kolom Status Kelulusan (index 4)
        const statusText = row.cells[4] ? row.cells[4].textContent.toLowerCase().trim() : '';
        
        let showByStatus = true;
        if (status === 'lulus') {
            showByStatus = statusText.includes('lulus') && !statusText.includes('belum');
        } else if (status === 'belum_lulus') {
            showByStatus = statusText.includes('belum lulus');
        }

        if (status === 'all' || showByStatus) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
}
let currentSearchRequest = null;

document.getElementById('searchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value;
    const status = document.getElementById('statusFilter').value;
    const tableBody = document.getElementById('tableBody');

    // Jika input kosong, Anda bisa memilih untuk memuat ulang halaman asli atau membiarkannya
    if (searchTerm.length < 2 && searchTerm.length > 0) return; 

    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        // Tampilkan placeholder loading
        tableBody.innerHTML = '<tr><td colspan="8" class="px-4 py-8 text-center text-blue-500">🔍 Mencari di database...</td></tr>';

        // Batalkan request sebelumnya jika masih jalan
        if (currentSearchRequest) currentSearchRequest.abort();
        
        const controller = new AbortController();
        currentSearchRequest = controller;

        fetch(`{{ route("admin.master-moodle-lulus.search-ajax") }}?search=${encodeURIComponent(searchTerm)}&status=${status}`, {
            signal: controller.signal,
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                renderTable(res.data);
            }
        })
        .catch(err => {
            if (err.name !== 'AbortError') console.error(err);
        });
    }, 500);
});
function renderTable(data) {
    const tableBody = document.getElementById('tableBody');
    tableBody.innerHTML = '';

    if (data.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="8" class="px-4 py-8 text-center text-gray-500">Data tidak ditemukan di database</td></tr>';
        return;
    }

    data.forEach(item => {
        const row = `
            <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 transition-colors">
                <td class="px-4 py-4"><input type="checkbox" class="user-checkbox" value="${item.id}" onchange="updateBulkActions()"></td>
                <td class="px-4 py-4"><p class="font-medium">${item.username}</p></td>
                <td class="px-4 py-4">${item.moodle_user_id}</td>
                <td class="px-4 py-4">${item.name}</td>
                <td class="px-4 py-4">
                    <span class="status-badge ${item.is_lulus ? 'badge-success' : 'badge-warning'}">
                        ${item.is_lulus ? 'LULUS' : 'BELUM LULUS'}
                    </span>
                </td>
                <td class="px-4 py-4"><span class="quiz-score">${item.quiz_score}</span></td>
                <td class="px-4 py-4">${item.sources.join(', ')}</td>
                <td class="px-4 py-4">
                    <button onclick="luluskanUser(${item.id})" class="bg-green-600 text-white px-3 py-1.5 rounded-lg text-xs">Luluskan</button>
                </td>
            </tr>
        `;
        tableBody.insertAdjacentHTML('beforeend', row);
    });
    
    // Sembunyikan pagination karena kita sedang dalam mode pencarian global
    document.querySelector('.flex.items-center.justify-between.mt-4').style.display = 'none';
}
// Select all functionality
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.user-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
        if (selectAll.checked) {
            selectedUsers.add(checkbox.value);
        } else {
            selectedUsers.delete(checkbox.value);
        }
    });
    
    updateBulkActions();
}

// Update bulk actions
function updateBulkActions() {
    const checkboxes = document.querySelectorAll('.user-checkbox:checked');
    selectedUsers = new Set(Array.from(checkboxes).map(cb => cb.value));
    
    const bulkActions = document.getElementById('bulkActions');
    const selectedCount = document.getElementById('selectedCount');
    
    if (selectedUsers.size > 0) {
        bulkActions.style.display = 'flex';
        selectedCount.textContent = selectedUsers.size;
    } else {
        bulkActions.style.display = 'none';
    }
    
    // Update select all checkbox
    const totalCheckboxes = document.querySelectorAll('.user-checkbox').length;
    const selectAll = document.getElementById('selectAll');
    selectAll.checked = selectedUsers.size === totalCheckboxes && totalCheckboxes > 0;
    selectAll.indeterminate = selectedUsers.size > 0 && selectedUsers.size < totalCheckboxes;
}

// Luluskan single user - FIXED URL
function luluskanUser(id) {
    if (!confirm('Apakah Anda yakin ingin meluluskan user ini?')) {
        return;
    }
    
    showLoading('Meluluskan user...');
    
    // PERBAIKAN: Gunakan route dengan parameter yang benar
    fetch(`{{ route("admin.master-moodle-lulus.luluskan", "") }}/${id}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            // Jika response bukan JSON, throw error
            if (response.status === 404) {
                throw new Error('Endpoint tidak ditemukan (404). Periksa route.');
            }
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        hideLoading();
        if (data.success) {
            showSuccess(data.message);
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showError(data.message);
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error details:', error);
        showError('Terjadi error: ' + error.message);
    });
}

// Luluskan multiple users
function luluskanMultiple() {
    if (selectedUsers.size === 0) {
        showError('Pilih setidaknya satu user!');
        return;
    }
    
    if (!confirm(`Apakah Anda yakin ingin meluluskan ${selectedUsers.size} user?`)) {
        return;
    }
    
    showLoading(`Meluluskan ${selectedUsers.size} user...`);
    
    fetch('{{ route("admin.master-moodle-lulus.luluskan-multiple") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            ids: Array.from(selectedUsers)
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        hideLoading();
        if (data.success) {
            showSuccess(data.message);
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showError(data.message);
        }
    })
    .catch(error => {
        hideLoading();
        showError('Terjadi error: ' + error.message);
    });
}

// Batalkan lulus - FIXED URL
function batalkanLulus(id) {
    if (!confirm('Apakah Anda yakin ingin membatalkan kelulusan user ini?')) {
        return;
    }
    
    showLoading('Membatalkan kelulusan...');
    
    // PERBAIKAN: Gunakan route dengan parameter yang benar
    fetch(`{{ route("admin.master-moodle-lulus.batalkan-lulus", "") }}/${id}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            if (response.status === 404) {
                throw new Error('Endpoint tidak ditemukan (404). Periksa route.');
            }
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        hideLoading();
        if (data.success) {
            showSuccess(data.message);
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showError(data.message);
        }
    })
    .catch(error => {
        hideLoading();
        showError('Terjadi error: ' + error.message);
    });
}

// Utility functions for notifications
function showLoading(message = 'Memproses...') {
    // Simple loading implementation
    const loadingEl = document.createElement('div');
    loadingEl.id = 'loading-overlay';
    loadingEl.innerHTML = `
        <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;">
            <div style="background: white; padding: 20px; border-radius: 8px; text-align: center;">
                <div>${message}</div>
            </div>
        </div>
    `;
    document.body.appendChild(loadingEl);
}

function hideLoading() {
    const loadingEl = document.getElementById('loading-overlay');
    if (loadingEl) {
        loadingEl.remove();
    }
}

function showSuccess(message) {
    alert('Sukses: ' + message);
}

function showError(message) {
    alert('Error: ' + message);
}

// Debug function untuk test routes
function testRoutes() {
    console.log('Route luluskan:', '{{ route("admin.master-moodle-lulus.luluskan", "TEST_ID") }}');
    console.log('Route batalkan:', '{{ route("admin.master-moodle-lulus.batalkan-lulus", "TEST_ID") }}');
    console.log('Route statistics:', '{{ route("admin.master-moodle-lulus.statistics") }}');
}

// Panggil test routes untuk debugging
// testRoutes();
</script>
@endsection