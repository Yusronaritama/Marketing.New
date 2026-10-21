@extends('layouts.admin.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card bg-white rounded-lg shadow-theme-sm border border-gray-200">
                <div class="card-header bg-gray-50 border-b border-gray-200 p-6 flex justify-between items-center">
                    <h3 class="card-title text-gray-900 font-semibold text-lg">Manajemen Hasil Quiz Moodle</h3>
                    <div class="card-tools flex gap-2">
                        <button class="btn bg-success-500 hover:bg-success-600 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-300 flex items-center gap-2" onclick="syncAllResults()">
                            <i class="fas fa-sync"></i> Sync Semua
                        </button>
                        <button class="btn bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-300 flex items-center gap-2" onclick="showStatistics()">
                            <i class="fas fa-chart-bar"></i> Statistik
                        </button>
                    </div>
                </div>
                <div class="card-body p-6">
                    <!-- Filter Section -->
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-6">
                        <div class="md:col-span-4">
                            <input type="text" id="searchInput" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 outline-hidden transition-colors duration-300" placeholder="Cari VA Number atau Nama...">
                        </div>
                        <div class="md:col-span-2">
                            <button class="btn bg-brand-500 hover:bg-brand-600 text-white w-full px-4 py-2 rounded-lg font-medium transition-colors duration-300 flex items-center justify-center gap-2" onclick="searchUsers()">
                                <i class="fas fa-search"></i> Cari
                            </button>
                        </div>
                        <div class="md:col-span-3">
                            <select id="statusFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 outline-hidden transition-colors duration-300" onchange="filterByStatus()">
                                <option value="">Semua Status</option>
                                <option value="lulus">Lulus</option>
                                <option value="tidak_lulus">Tidak Lulus</option>
                                <option value="belum_ujian">Belum Ujian</option>
                            </select>
                        </div>
                        <div class="md:col-span-3">
                            <select id="batchFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 outline-hidden transition-colors duration-300" onchange="filterByBatch()">
                                <option value="">Semua Batch</option>
                                <!-- Options akan diisi via JavaScript -->
                            </select>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6" id="statisticsCards" style="display: none;">
                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-4 text-white shadow-theme-sm">
                            <div class="flex items-center">
                                <div class="bg-white/20 rounded-lg p-3 mr-4">
                                    <i class="fas fa-users text-white text-xl"></i>
                                </div>
                                <div>
                                    <div class="text-sm opacity-90">Total Users</div>
                                    <div class="text-2xl font-bold" id="statTotalUsers">0</div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gradient-to-r from-success-500 to-success-600 rounded-xl p-4 text-white shadow-theme-sm">
                            <div class="flex items-center">
                                <div class="bg-white/20 rounded-lg p-3 mr-4">
                                    <i class="fas fa-check-circle text-white text-xl"></i>
                                </div>
                                <div>
                                    <div class="text-sm opacity-90">Lulus</div>
                                    <div class="text-2xl font-bold" id="statLulus">0</div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gradient-to-r from-error-500 to-error-600 rounded-xl p-4 text-white shadow-theme-sm">
                            <div class="flex items-center">
                                <div class="bg-white/20 rounded-lg p-3 mr-4">
                                    <i class="fas fa-times-circle text-white text-xl"></i>
                                </div>
                                <div>
                                    <div class="text-sm opacity-90">Tidak Lulus</div>
                                    <div class="text-2xl font-bold" id="statTidakLulus">0</div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gradient-to-r from-warning-500 to-warning-600 rounded-xl p-4 text-white shadow-theme-sm">
                            <div class="flex items-center">
                                <div class="bg-white/20 rounded-lg p-3 mr-4">
                                    <i class="fas fa-chart-line text-white text-xl"></i>
                                </div>
                                <div>
                                    <div class="text-sm opacity-90">Rata-rata Nilai</div>
                                    <div class="text-2xl font-bold" id="statAvgScore">0</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Loading Indicator -->
                    <div id="loadingIndicator" class="text-center py-8">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-brand-500 mx-auto"></div>
                        <p class="mt-4 text-gray-600">Memuat data users...</p>
                    </div>

                    <!-- Users Table -->
                    <div id="usersTable" class="mt-6"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal untuk hasil quiz -->
<div class="modal fixed inset-0 bg-gray-900/50 backdrop-blur-[32px] z-99999 hidden" id="quizResultModal">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-theme-lg w-full max-w-2xl">
            <div class="flex justify-between items-center p-6 border-b border-gray-200">
                <h5 class="text-xl font-semibold text-gray-900">Detail Hasil Quiz</h5>
                <button type="button" class="text-gray-400 hover:text-gray-600 text-2xl" onclick="closeModal('quizResultModal')">×</button>
            </div>
            <div class="p-6 max-h-96 overflow-y-auto" id="quizResultContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="flex justify-end gap-3 p-6 border-t border-gray-200">
                <button type="button" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors duration-300" onclick="closeModal('quizResultModal')">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal untuk statistik -->
<div class="modal fixed inset-0 bg-gray-900/50 backdrop-blur-[32px] z-99999 hidden" id="statisticsModal">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-theme-lg w-full max-w-4xl">
            <div class="flex justify-between items-center p-6 border-b border-gray-200">
                <h5 class="text-xl font-semibold text-gray-900">Statistik Hasil Quiz</h5>
                <button type="button" class="text-gray-400 hover:text-gray-600 text-2xl" onclick="closeModal('statisticsModal')">×</button>
            </div>
            <div class="p-6 max-h-96 overflow-y-auto" id="statisticsContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Global variables
let currentPage = 1;
let currentSearch = '';
let currentStatus = '';
let currentBatch = '';

document.addEventListener('DOMContentLoaded', function() {
    loadUsers();
    loadStatistics();
    loadBatchOptions();
});

function loadUsers(page = 1, search = '', status = '', batch = '') {
    currentPage = page;
    currentSearch = search;
    currentStatus = status;
    currentBatch = batch;

    showLoading(true);
    
    let url = `/quiz/admin/users?page=${page}`;
    if (search) url += `&search=${encodeURIComponent(search)}`;
    if (status) url += `&status=${status}`;
    if (batch) url += `&batch=${batch}`;

    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        showLoading(false);
        if (data.success) {
            renderUsersTable(data);
        } else {
            showError('Gagal memuat data: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showLoading(false);
        showError('Terjadi kesalahan saat memuat data');
    });
}

function renderUsersTable(data) {
    const container = document.getElementById('usersTable');
    
    if (data.data.data.length > 0) {
        let html = `
            <div class="overflow-x-auto border border-gray-200 rounded-lg">
                <table class="w-full table-auto">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">#</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">VA Number</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Moodle ID</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Nilai</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Program Studi</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
        `;

        data.data.data.forEach((user, index) => {
            const startIndex = (data.data.current_page - 1) * data.data.per_page;
            const rowNumber = startIndex + index + 1;
            
            const nilai = user.nilai ? user.nilai.nilai : '-';
            const statusLulus = user.kelulusan ? user.kelulusan.status_lulus : 'belum';
            
            // Determine status badge
            let statusBadge = '';
            if (statusLulus === '1') {
                statusBadge = '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-success-50 text-success-700 border border-success-300">Lulus</span>';
            } else if (statusLulus === '2') {
                statusBadge = '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-error-50 text-error-700 border border-error-300">Tidak Lulus</span>';
            } else {
                statusBadge = '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-warning-50 text-warning-700 border border-warning-300">Belum Ujian</span>';
            }

            // Determine program studi
            const prodi1 = user.ereg ? user.ereg.jurusan1 : '-';
            const prodi2 = user.ereg ? user.ereg.jurusan2 : '-';

            html += `
                <tr class="hover:bg-gray-50 transition-colors duration-300">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${rowNumber}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="font-semibold text-gray-900">${user.va_number}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${user.user.name}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${user.user.email}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${user.moodle_user ? user.moodle_user.moodle_user_id : '-'}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="font-semibold ${nilai >= 17 ? 'text-success-600' : 'text-error-600'}">
                            ${nilai}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">${statusBadge}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        <div class="space-y-1">
                            <div class="text-xs">P1: ${prodi1}</div>
                            <div class="text-xs">P2: ${prodi2}</div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <div class="flex items-center gap-1">
                            ${user.ereg ? `
                            <button class="btn bg-brand-500 hover:bg-brand-600 text-white p-2 rounded-lg transition-colors duration-300" onclick="getQuizResults(${user.ereg.id})" title="Lihat Hasil">
                                <i class="fas fa-chart-bar text-sm"></i>
                            </button>
                            <button class="btn bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-lg transition-colors duration-300" onclick="checkMoodleStatus(${user.ereg.id})" title="Status Moodle">
                                <i class="fas fa-info-circle text-sm"></i>
                            </button>
                            <button class="btn bg-warning-500 hover:bg-warning-600 text-white p-2 rounded-lg transition-colors duration-300" onclick="syncUserResult(${user.ereg.id})" title="Sync Ulang">
                                <i class="fas fa-sync-alt text-sm"></i>
                            </button>
                            ` : `
                            <button class="btn bg-gray-400 text-white p-2 rounded-lg cursor-not-allowed" disabled title="Data tidak lengkap">
                                <i class="fas fa-ban text-sm"></i>
                            </button>
                            `}
                        </div>
                    </td>
                </tr>
            `;
        });

        html += `
                    </tbody>
                </table>
            </div>
        `;

        // Add pagination
        if (data.data.last_page > 1) {
            html += `
                <div class="flex flex-col sm:flex-row justify-between items-center mt-6 gap-4">
                    <div class="text-sm text-gray-600">
                        Menampilkan ${data.data.from} sampai ${data.data.to} dari ${data.data.total} data
                    </div>
                    <div class="flex items-center gap-2">
            `;

            // Previous button
            if (data.data.current_page > 1) {
                html += `
                    <button class="btn bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg transition-colors duration-300" onclick="loadUsers(${data.data.current_page - 1}, '${currentSearch}', '${currentStatus}', '${currentBatch}')">
                        &laquo; Previous
                    </button>
                `;
            }

            // Page numbers (simplified)
            const startPage = Math.max(1, data.data.current_page - 2);
            const endPage = Math.min(data.data.last_page, data.data.current_page + 2);

            for (let i = startPage; i <= endPage; i++) {
                const active = i === data.data.current_page ? 'bg-brand-500 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50';
                html += `
                    <button class="btn ${active} px-4 py-2 rounded-lg transition-colors duration-300" onclick="loadUsers(${i}, '${currentSearch}', '${currentStatus}', '${currentBatch}')">
                        ${i}
                    </button>
                `;
            }

            // Next button
            if (data.data.current_page < data.data.last_page) {
                html += `
                    <button class="btn bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg transition-colors duration-300" onclick="loadUsers(${data.data.current_page + 1}, '${currentSearch}', '${currentStatus}', '${currentBatch}')">
                        Next &raquo;
                    </button>
                `;
            }

            html += `
                    </div>
                </div>
            `;
        }

        container.innerHTML = html;
    } else {
        container.innerHTML = `
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
                <i class="fas fa-info-circle text-blue-500 text-2xl mb-3"></i>
                <p class="text-blue-700">Tidak ada data yang ditemukan</p>
            </div>
        `;
    }
}

function searchUsers() {
    const search = document.getElementById('searchInput').value;
    loadUsers(1, search, currentStatus, currentBatch);
}

function filterByStatus() {
    const status = document.getElementById('statusFilter').value;
    loadUsers(1, currentSearch, status, currentBatch);
}

function filterByBatch() {
    const batch = document.getElementById('batchFilter').value;
    loadUsers(1, currentSearch, currentStatus, batch);
}

function loadBatchOptions() {
    const batches = [
        { value: 'batch1', text: 'Batch 1' },
        { value: 'batch2', text: 'Batch 2' },
        { value: 'batch3', text: 'Batch 3' }
    ];

    const select = document.getElementById('batchFilter');
    batches.forEach(batch => {
        const option = document.createElement('option');
        option.value = batch.value;
        option.textContent = batch.text;
        select.appendChild(option);
    });
}

// FUNGSI YANG MISSING - TAMBAHKAN DI SINI

function syncAllResults() {
    if (!confirm('Sync semua hasil quiz? Proses ini mungkin memakan waktu beberapa menit.')) {
        return;
    }

    showLoading(true);
    
    fetch('/quiz/sync-all', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        showLoading(false);
        if (data.success) {
            const summary = data.summary;
            showSuccess(`Sync completed! Success: ${summary.success_count}, Failed: ${summary.error_count}, Success Rate: ${summary.success_rate}%`);
            loadUsers();
            loadStatistics();
        } else {
            showError('Sync failed: ' + data.message);
        }
    })
    .catch(error => {
        showLoading(false);
        showError('Terjadi kesalahan saat sync');
    });
}

function showStatistics() {
    fetch('/quiz/statistics', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        const modalContent = document.getElementById('statisticsContent');
        
        if (data.success) {
            const stats = data.data;
            modalContent.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Statistik Umum</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <tr>
                                        <td>Total Users dengan Moodle</td>
                                        <td><strong>${stats.total_users_with_moodle}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Total Nilai Terekam</td>
                                        <td><strong>${stats.score_statistics.total_scores}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Rata-rata Nilai</td>
                                        <td><strong>${stats.score_statistics.average_score}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Nilai Tertinggi</td>
                                        <td><strong>${stats.score_statistics.max_score}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Nilai Terendah</td>
                                        <td><strong>${stats.score_statistics.min_score}</strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Statistik Kelulusan</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <tr>
                                        <td>Total Data Kelulusan</td>
                                        <td><strong>${stats.passing_statistics.total}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Lulus</td>
                                        <td><strong class="text-success">${stats.passing_statistics.lulus}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Tidak Lulus</td>
                                        <td><strong class="text-danger">${stats.passing_statistics.tidak_lulus}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Passing Rate</td>
                                        <td><strong>${stats.passing_statistics.passing_rate}%</strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Distribusi Nilai</h5>
                            </div>
                            <div class="card-body">
                                <div id="scoreDistributionChart">
                                    ${renderScoreDistribution(stats.score_distribution)}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        } else {
            modalContent.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
        }

        showModal('statisticsModal');
    });
}

function renderScoreDistribution(distribution) {
    if (!distribution || distribution.length === 0) {
        return '<p class="text-muted">Tidak ada data distribusi</p>';
    }

    let html = '<div class="distribution-bars">';
    distribution.forEach(item => {
        const percentage = (item.count / distribution.reduce((sum, d) => sum + d.count, 0)) * 100;
        html += `
            <div class="distribution-item mb-2">
                <div class="d-flex justify-content-between">
                    <span>${item.score_range}-${item.score_range + 9}</span>
                    <span>${item.count} (${percentage.toFixed(1)}%)</span>
                </div>
                <div class="progress" style="height: 20px;">
                    <div class="progress-bar" role="progressbar" style="width: ${percentage}%">
                        ${item.count}
                    </div>
                </div>
            </div>
        `;
    });
    html += '</div>';
    return html;
}

function loadStatistics() {
    fetch('/quiz/statistics', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const stats = data.data;
            document.getElementById('statTotalUsers').textContent = stats.total_users_with_moodle;
            document.getElementById('statLulus').textContent = stats.passing_statistics.lulus;
            document.getElementById('statTidakLulus').textContent = stats.passing_statistics.tidak_lulus;
            document.getElementById('statAvgScore').textContent = stats.score_statistics.average_score;
            document.getElementById('statisticsCards').style.display = 'grid';
        }
    })
    .catch(error => {
        console.error('Error loading statistics:', error);
    });
}

function syncUserResult(idPendaftaran) {
    if (!confirm('Sync hasil quiz untuk user ini?')) {
        return;
    }

    showLoading(true);
    
    fetch(`/quiz/user-results/${idPendaftaran}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        showLoading(false);
        if (data.success) {
            showSuccess('Berhasil sync hasil quiz');
            loadUsers(currentPage, currentSearch, currentStatus, currentBatch);
        } else {
            showError('Gagal sync: ' + data.message);
        }
    })
    .catch(error => {
        showLoading(false);
        showError('Terjadi kesalahan saat sync');
    });
}

function checkMoodleStatus(idPendaftaran) {
    showLoadingModal('Memeriksa status Moodle...');
    
    fetch(`/quiz/moodle-status/${idPendaftaran}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        const modalContent = document.getElementById('quizResultContent');
        
        if (data.success) {
            modalContent.innerHTML = `
                <div class="bg-blue-50 border border-blue-300 rounded-lg p-4 mb-4">
                    <div class="flex items-center">
                        <i class="fas fa-info-circle text-blue-500 text-xl mr-3"></i>
                        <h6 class="text-blue-700 font-semibold">Status Akun Moodle</h6>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <strong class="text-gray-700">VA Number:</strong> ${data.data.va_number}
                    </div>
                    <div>
                        <strong class="text-gray-700">Status Moodle:</strong> 
                        <span class="inline-flex items-center px-3 py-1 ${data.data.moodle_status === 'success' ? 'bg-success-50 text-success-700' : 'bg-warning-50 text-warning-700'} rounded-full font-semibold ml-2">
                            ${data.data.moodle_status}
                        </span>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <strong class="text-gray-700">Moodle User ID:</strong> ${data.data.moodle_user_id || '-'}
                    </div>
                    <div>
                        <strong class="text-gray-700">Enrol Status:</strong> 
                        <span class="inline-flex items-center px-3 py-1 ${data.data.enrol_status === 'enrolled' ? 'bg-success-50 text-success-700' : 'bg-warning-50 text-warning-700'} rounded-full font-semibold ml-2">
                            ${data.data.enrol_status}
                        </span>
                    </div>
                </div>
                ${data.data.error_message ? `
                <div class="bg-error-50 border border-error-300 rounded-lg p-4 mt-4">
                    <strong class="text-error-700">Error Message:</strong> ${data.data.error_message}
                </div>
                ` : ''}
                <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                    <strong class="text-gray-700">Informasi:</strong><br>
                    <p class="mt-2 text-sm text-gray-600">Pastikan status Moodle "success" dan enrol status "enrolled" untuk dapat mengikuti quiz.</p>
                </div>
            `;
        } else {
            modalContent.innerHTML = `
                <div class="bg-error-50 border border-error-300 rounded-lg p-4">
                    <div class="flex items-center">
                        <i class="fas fa-times-circle text-error-500 text-xl mr-3"></i>
                        <h6 class="text-error-700 font-semibold">Gagal Memuat Status</h6>
                    </div>
                    <p class="mt-2 text-error-600">${data.message}</p>
                </div>
            `;
        }

        showModal('quizResultModal');
    });
}

function getQuizResults(idPendaftaran) {
    showLoadingModal('Memuat hasil quiz...');
    
    fetch(`/quiz/user-results/${idPendaftaran}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        const modalContent = document.getElementById('quizResultContent');
        
        if (data.success) {
            modalContent.innerHTML = `
                <div class="bg-success-50 border border-success-300 rounded-lg p-4 mb-4">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-success-500 text-xl mr-3"></i>
                        <h6 class="text-success-700 font-semibold">Hasil Quiz Berhasil Diambil</h6>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <strong class="text-gray-700">Nilai:</strong> 
                        <span class="inline-flex items-center px-3 py-1 bg-brand-50 text-brand-700 rounded-full font-semibold ml-2">${data.data.score}</span>
                    </div>
                    <div>
                        <strong class="text-gray-700">Status:</strong> 
                        <span class="inline-flex items-center px-3 py-1 ${data.data.result.includes('Lulus') ? 'bg-success-50 text-success-700' : 'bg-error-50 text-error-700'} rounded-full font-semibold ml-2">
                            ${data.data.result}
                        </span>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <strong class="text-gray-700">Attempt ID:</strong> ${data.data.attempt_id || '-'}
                    </div>
                    <div>
                        <strong class="text-gray-700">Attempt Number:</strong> ${data.data.attempt_number || '-'}
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <strong class="text-gray-700">Waktu Mulai:</strong> ${formatTimestamp(data.data.time_start)}
                    </div>
                    <div>
                        <strong class="text-gray-700">Waktu Selesai:</strong> ${formatTimestamp(data.data.time_finish)}
                    </div>
                </div>
                <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                    <strong class="text-gray-700">Info User:</strong><br>
                    <div class="mt-2 space-y-1 text-sm">
                        <div>VA Number: ${data.data.user_info.va_number}</div>
                        <div>Moodle ID: ${data.data.user_info.moodle_user_id}</div>
                        <div>Quiz ID: ${data.data.user_info.quiz_id}</div>
                    </div>
                </div>
            `;
        } else {
            modalContent.innerHTML = `
                <div class="bg-warning-50 border border-warning-300 rounded-lg p-4">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle text-warning-500 text-xl mr-3"></i>
                        <h6 class="text-warning-700 font-semibold">Belum Ada Hasil Quiz</h6>
                    </div>
                    <p class="mt-2 text-warning-600">${data.message}</p>
                </div>
            `;
        }

        showModal('quizResultModal');
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('quizResultContent').innerHTML = `
            <div class="bg-error-50 border border-error-300 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="fas fa-times-circle text-error-500 text-xl mr-3"></i>
                    <h6 class="text-error-700 font-semibold">Terjadi Kesalahan</h6>
                </div>
                <p class="mt-2 text-error-600">Gagal memuat hasil quiz</p>
            </div>
        `;
        showModal('quizResultModal');
    });
}

// Utility functions
function showLoading(show) {
    document.getElementById('loadingIndicator').style.display = show ? 'block' : 'none';
    document.getElementById('usersTable').style.display = show ? 'none' : 'block';
}

function showLoadingModal(message) {
    document.getElementById('quizResultContent').innerHTML = `
        <div class="text-center py-8">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-brand-500 mx-auto"></div>
            <p class="mt-4 text-gray-600">${message}</p>
        </div>
    `;
}

function showSuccess(message) {
    // Anda bisa menggunakan toast notification atau alert
    alert('Success: ' + message);
}

function showError(message) {
    alert('Error: ' + message);
}

function formatTimestamp(timestamp) {
    if (!timestamp) return '-';
    const date = new Date(timestamp * 1000);
    return date.toLocaleString('id-ID');
}

// Modal functions
function showModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.classList.add('hidden');
    }
});
</script>

<style>
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem 1rem;
    font-weight: 500;
    border-radius: 0.5rem;
    transition: all 0.3s;
    cursor: pointer;
    border: none;
    outline: none;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.distribution-bars .progress-bar {
    background-color: #007bff;
}
.font-size-16 {
    font-size: 16px;
}
.font-size-14 {
    font-size: 14px;
}
</style>
@endsection
<script src="{{ asset('assets/js/bundle.js') }}"></script>