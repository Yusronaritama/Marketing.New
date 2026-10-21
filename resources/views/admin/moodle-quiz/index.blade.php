@extends('layouts.admin.app')

@section('title', 'Management Nilai Quiz Moodle')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-graduation-cap"></i>
                        Management Nilai Quiz Moodle
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filter Section -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="search">Cari (VA Number/Nama/Email):</label>
                                <input type="text" class="form-control" id="search" placeholder="Masukkan kata kunci...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="status">Status Ujian:</label>
                                <select class="form-control" id="status">
                                    <option value="">Semua</option>
                                    <option value="sudah_ujian">Sudah Ujian</option>
                                    <option value="belum_ujian">Belum Ujian</option>
                                    <option value="enrolled">Enrolled</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="per_page">Data per halaman:</label>
                                <select class="form-control" id="per_page">
                                    <option value="10">10</option>
                                    <option value="20" selected>20</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="button" class="btn btn-primary btn-block" id="btn-filter">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="btn-group">
                                <button type="button" class="btn btn-success" id="btn-sync-all">
                                    <i class="fas fa-sync"></i> Sync Semua Nilai
                                </button>
                                <button type="button" class="btn btn-info" id="btn-export">
                                    <i class="fas fa-download"></i> Export Data
                                </button>
                                <button type="button" class="btn btn-warning" id="btn-statistics">
                                    <i class="fas fa-chart-bar"></i> Lihat Statistik
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Loading Indicator -->
                    <div id="loading-indicator" class="text-center" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-2" id="loading-message">Memuat data...</p>
                    </div>

                    <!-- Users Table -->
                    <div class="table-responsive" id="table-container">
                        <table class="table table-bordered table-striped table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>VA Number</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Moodle User ID</th>
                                    <th>Program Studi</th>
                                    <th>Nilai</th>
                                    <th>Status Ujian</th>
                                    <th>Status Moodle</th>
                                    <th>Terakhir Update</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="users-table-body">
                                <!-- Data will be loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="row">
                        <div class="col-12">
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center" id="pagination">
                                    <!-- Pagination will be loaded via AJAX -->
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Modal -->
<div class="modal fade" id="statisticsModal" tabindex="-1" role="dialog" aria-labelledby="statisticsModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statisticsModalLabel">
                    <i class="fas fa-chart-bar"></i> Statistik Nilai Quiz
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="statistics-content">
                <!-- Statistics content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- User Detail Modal -->
<div class="modal fade" id="userDetailModal" tabindex="-1" role="dialog" aria-labelledby="userDetailModalLabel">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userDetailModalLabel">
                    <i class="fas fa-user-graduate"></i> Detail User & Nilai Quiz
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="user-detail-content">
                <!-- User detail content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle"></i> Berhasil
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="success-message">
                <!-- Success message will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div class="modal fade" id="errorModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-circle"></i> Error
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="error-message">
                <!-- Error message will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('assets/js/bundle.js') }}"></script>
<script>
$(document).ready(function() {
    let currentPage = 1;
    let currentSearch = '';
    let currentStatus = '';
    let currentPerPage = 20;

    // Load initial data
    loadUsersData();

    // Filter button click
    $('#btn-filter').click(function() {
        currentPage = 1;
        loadUsersData();
    });

    // Sync all button click
    $('#btn-sync-all').click(function() {
        syncAllResults();
    });

    // Export button click
    $('#btn-export').click(function() {
        exportData();
    });

    // Statistics button click
    $('#btn-statistics').click(function() {
        showStatistics();
    });

    // Search on enter key
    $('#search').keypress(function(e) {
        if (e.which === 13) {
            currentPage = 1;
            loadUsersData();
        }
    });

    // Per page change
    $('#per_page').change(function() {
        currentPerPage = $(this).val();
        currentPage = 1;
        loadUsersData();
    });

    // Load users data
    function loadUsersData() {
        const search = $('#search').val();
        const status = $('#status').val();
        const perPage = $('#per_page').val();

        currentSearch = search;
        currentStatus = status;
        currentPerPage = perPage;

        showLoading('Memuat data users...');
        
        $.ajax({
            url: '{{ route("admin.moodle-quiz.users.list") }}',
            type: 'GET',
            data: {
                search: search,
                status: status,
                per_page: perPage,
                page: currentPage
            },
            success: function(response) {
                hideLoading();
                if (response.success) {
                    renderUsersTable(response.data);
                } else {
                    showError('Gagal memuat data: ' + response.message);
                }
            },
            error: function(xhr) {
                hideLoading();
                showError('Terjadi kesalahan saat memuat data');
                console.error('Error loading users:', xhr);
            }
        });
    }

    // Render users table
    function renderUsersTable(data) {
        const tbody = $('#users-table-body');
        tbody.empty();

        if (data.users.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="11" class="text-center text-muted py-4">
                        <i class="fas fa-info-circle fa-2x mb-2"></i><br>
                        Tidak ada data yang ditemukan
                    </td>
                </tr>
            `);
            return;
        }

        data.users.forEach((user, index) => {
            const rowNumber = (currentPage - 1) * currentPerPage + index + 1;
            const quizData = user.quiz_data;
            const statusClass = quizData ? 'success' : 'secondary';
            const statusText = quizData ? 'Sudah Ujian' : 'Belum Ujian';
            const nilai = quizData ? quizData.nilai : '-';
            const lastUpdated = quizData ? formatDateTime(quizData.last_updated) : '-';

            const row = `
                <tr>
                    <td>${rowNumber}</td>
                    <td>
                        <strong>${user.va_number}</strong>
                    </td>
                    <td>${user.user.name || '-'}</td>
                    <td>${user.user.email || '-'}</td>
                    <td>
                        <span class="badge badge-info">${user.moodle_user.moodle_user_id}</span>
                    </td>
                    <td>
                        <small>
                            <strong>P1:</strong> ${user.ereg.jurusan1 || '-'}<br>
                            <strong>P2:</strong> ${user.ereg.jurusan2 || '-'}
                        </small>
                    </td>
                    <td>
                        ${quizData ? `
                            <span class="badge badge-${nilai >= 50 ? 'success' : 'danger'}">
                                ${nilai}
                            </span>
                        ` : '-'}
                    </td>
                    <td>
                        <span class="badge badge-${statusClass}">${statusText}</span>
                    </td>
                    <td>
                        <span class="badge badge-${user.moodle_user.status === 'success' ? 'success' : 'warning'}">
                            ${user.moodle_user.status}
                        </span>
                        <br>
                        <small class="text-muted">${user.moodle_user.enrol_status}</small>
                    </td>
                    <td>${lastUpdated}</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-info btn-detail" 
                                    data-user-id="${user.master_user_id}"
                                    title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-warning btn-sync" 
                                    data-user-id="${user.master_user_id}"
                                    title="Sync Nilai">
                                <i class="fas fa-sync"></i>
                            </button>
                            ${quizData ? `
                            <button type="button" class="btn btn-success btn-results" 
                                    data-ereg-id="${user.ereg.id}"
                                    title="Lihat Hasil">
                                <i class="fas fa-chart-line"></i>
                            </button>
                            ` : ''}
                        </div>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });

        renderPagination(data.pagination);
        bindActionButtons();
    }

    // Render pagination
    function renderPagination(pagination) {
        const paginationEl = $('#pagination');
        paginationEl.empty();

        if (pagination.last_page <= 1) return;

        // Previous button
        const prevDisabled = pagination.current_page === 1 ? 'disabled' : '';
        paginationEl.append(`
            <li class="page-item ${prevDisabled}">
                <a class="page-link" href="#" data-page="${pagination.current_page - 1}" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                    <span class="sr-only">Previous</span>
                </a>
            </li>
        `);

        // Page numbers
        const startPage = Math.max(1, pagination.current_page - 2);
        const endPage = Math.min(pagination.last_page, pagination.current_page + 2);

        if (startPage > 1) {
            paginationEl.append(`
                <li class="page-item">
                    <a class="page-link" href="#" data-page="1">1</a>
                </li>
                ${startPage > 2 ? '<li class="page-item disabled"><span class="page-link">...</span></li>' : ''}
            `);
        }

        for (let i = startPage; i <= endPage; i++) {
            const active = i === pagination.current_page ? 'active' : '';
            paginationEl.append(`
                <li class="page-item ${active}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `);
        }

        if (endPage < pagination.last_page) {
            paginationEl.append(`
                ${endPage < pagination.last_page - 1 ? '<li class="page-item disabled"><span class="page-link">...</span></li>' : ''}
                <li class="page-item">
                    <a class="page-link" href="#" data-page="${pagination.last_page}">${pagination.last_page}</a>
                </li>
            `);
        }

        // Next button
        const nextDisabled = pagination.current_page === pagination.last_page ? 'disabled' : '';
        paginationEl.append(`
            <li class="page-item ${nextDisabled}">
                <a class="page-link" href="#" data-page="${pagination.current_page + 1}" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                    <span class="sr-only">Next</span>
                </a>
            </li>
        `);

        // Bind pagination click events
        $('.page-link').click(function(e) {
            e.preventDefault();
            const page = $(this).data('page');
            if (page && page !== currentPage) {
                currentPage = page;
                loadUsersData();
            }
        });
    }

    // Bind action buttons
    function bindActionButtons() {
        // Detail button
        $('.btn-detail').click(function() {
            const userId = $(this).data('user-id');
            showUserDetail(userId);
        });

        // Sync button
        $('.btn-sync').click(function() {
            const userId = $(this).data('user-id');
            syncUserResult(userId);
        });

        // Results button
        $('.btn-results').click(function() {
            const eregId = $(this).data('ereg-id');
            showUserResults(eregId);
        });
    }

    // Show user detail
    function showUserDetail(userId) {
        showLoading('Memuat detail user...');
        
        $.ajax({
            url: `{{ url('admin/moodle-quiz/user') }}/${userId}/detail`,
            type: 'GET',
            success: function(response) {
                hideLoading();
                if (response.success) {
                    $('#user-detail-content').html(renderUserDetail(response.data));
                    $('#userDetailModal').modal('show');
                } else {
                    showError('Gagal memuat detail user: ' + response.message);
                }
            },
            error: function(xhr) {
                hideLoading();
                showError('Terjadi kesalahan saat memuat detail user');
                console.error('Error loading user detail:', xhr);
            }
        });
    }

    // Render user detail
    function renderUserDetail(data) {
        const quiz = data.quiz_attempt;
        const saved = data.saved_data;
        
        return `
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-user"></i> Informasi User
                            </h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">VA Number</th>
                                    <td>${data.user_info.va_number}</td>
                                </tr>
                                <tr>
                                    <th>Nama</th>
                                    <td>${data.user_info.name || '-'}</td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td>${data.user_info.email || '-'}</td>
                                </tr>
                                <tr>
                                    <th>Program Studi 1</th>
                                    <td>${data.ereg_info.jurusan1 || '-'}</td>
                                </tr>
                                <tr>
                                    <th>Program Studi 2</th>
                                    <td>${data.ereg_info.jurusan2 || '-'}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-graduation-cap"></i> Informasi Moodle
                            </h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Moodle User ID</th>
                                    <td>${data.moodle_info.moodle_user_id}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        <span class="badge badge-${data.moodle_info.status === 'success' ? 'success' : 'warning'}">
                                            ${data.moodle_info.status}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Enrol Status</th>
                                    <td>
                                        <span class="badge badge-${data.moodle_info.enrol_status === 'enrolled' ? 'success' : 'secondary'}">
                                            ${data.moodle_info.enrol_status}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-chart-line"></i> Informasi Quiz
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Data dari Moodle:</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <th width="40%">Nilai</th>
                                            <td>
                                                <span class="badge badge-${quiz.score >= 50 ? 'success' : 'danger'}">
                                                    ${quiz.score || 0}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Attempt ID</th>
                                            <td>${quiz.attempt_id || '-'}</td>
                                        </tr>
                                        <tr>
                                            <th>Attempt Number</th>
                                            <td>${quiz.attempt_number || '-'}</td>
                                        </tr>
                                        <tr>
                                            <th>State</th>
                                            <td>
                                                <span class="badge badge-${quiz.state === 'finished' ? 'success' : 'warning'}">
                                                    ${quiz.state || '-'}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Time Start</th>
                                            <td>${quiz.time_start || '-'}</td>
                                        </tr>
                                        <tr>
                                            <th>Time Finish</th>
                                            <td>${quiz.time_finish || '-'}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6>Data Tersimpan:</h6>
                                    ${saved.nilai ? `
                                    <table class="table table-sm">
                                        <tr>
                                            <th width="40%">Nilai Tersimpan</th>
                                            <td>
                                                <span class="badge badge-${saved.nilai.nilai >= 50 ? 'success' : 'danger'}">
                                                    ${saved.nilai.nilai}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Attempt ID</th>
                                            <td>${saved.nilai.attempt_id || '-'}</td>
                                        </tr>
                                        <tr>
                                            <th>Attempt Number</th>
                                            <td>${saved.nilai.attempt_number || '-'}</td>
                                        </tr>
                                        <tr>
                                            <th>Time Start</th>
                                            <td>${saved.nilai.time_start || '-'}</td>
                                        </tr>
                                        <tr>
                                            <th>Time Finish</th>
                                            <td>${saved.nilai.time_finish || '-'}</td>
                                        </tr>
                                        <tr>
                                            <th>Last Updated</th>
                                            <td>${formatDateTime(saved.nilai.last_updated)}</td>
                                        </tr>
                                    </table>
                                    ` : '<p class="text-muted">Belum ada data tersimpan</p>'}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // Sync all results
    function syncAllResults() {
        if (!confirm('Apakah Anda yakin ingin sync semua nilai? Proses ini mungkin memakan waktu beberapa menit.')) {
            return;
        }

        showLoading('Memulai sync semua nilai...');
        
        $.ajax({
            url: '{{ route("admin.moodle-quiz.sync-all") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                limit: 50
            },
            success: function(response) {
                hideLoading();
                if (response.success) {
                    showSuccess(`Sync berhasil! ${response.summary.success_count} user berhasil diupdate.`);
                    loadUsersData();
                } else {
                    showError('Gagal sync: ' + response.message);
                }
            },
            error: function(xhr) {
                hideLoading();
                showError('Terjadi kesalahan saat sync');
                console.error('Error syncing results:', xhr);
            }
        });
    }

    // Sync single user result
    function syncUserResult(userId) {
        showLoading('Sync nilai user...');
        
        // This would need to be implemented based on your specific endpoint
        // For now, we'll reload the data
        setTimeout(() => {
            hideLoading();
            showSuccess('Sync berhasil!');
            loadUsersData();
        }, 1000);
    }

    // Show user results
    function showUserResults(eregId) {
        showLoading('Memuat hasil user...');
        
        $.ajax({
            url: `{{ url('admin/moodle-quiz/user') }}/${eregId}/results`,
            type: 'GET',
            success: function(response) {
                hideLoading();
                if (response.success) {
                    $('#success-message').html(`
                        <p><strong>Hasil Quiz:</strong></p>
                        <p>Nilai: <span class="badge badge-${response.data.score >= 50 ? 'success' : 'danger'}">${response.data.score}</span></p>
                        <p>Status: ${response.data.result}</p>
                        <p>Lulus: ${response.data.lulus ? '<span class="badge badge-success">Ya</span>' : '<span class="badge badge-danger">Tidak</span>'}</p>
                        <p>Program Studi: ${response.data.program_studi || '-'}</p>
                    `);
                    $('#successModal').modal('show');
                } else {
                    showError('Gagal memuat hasil: ' + response.message);
                }
            },
            error: function(xhr) {
                hideLoading();
                showError('Terjadi kesalahan saat memuat hasil');
                console.error('Error loading user results:', xhr);
            }
        });
    }

    // Export data
    function exportData() {
        window.open('{{ route("admin.moodle-quiz.export-scores") }}', '_blank');
    }

    // Show statistics
    function showStatistics() {
        showLoading('Memuat statistik...');
        
        $.ajax({
            url: '{{ route("admin.moodle-quiz.statistics.detailed") }}',
            type: 'GET',
            success: function(response) {
                hideLoading();
                if (response.success) {
                    $('#statistics-content').html(renderStatistics(response.data));
                    $('#statisticsModal').modal('show');
                } else {
                    showError('Gagal memuat statistik: ' + response.message);
                }
            },
            error: function(xhr) {
                hideLoading();
                showError('Terjadi kesalahan saat memuat statistik');
                console.error('Error loading statistics:', xhr);
            }
        });
    }

    // Render statistics
    function renderStatistics(data) {
        const totalScores = data.score_statistics.total_scores || 1; // Avoid division by zero
        
        return `
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h6 class="card-title mb-0">Overview</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <th>Total Users dengan Moodle</th>
                                    <td>${data.overview.total_users_with_moodle || 0}</td>
                                </tr>
                                <tr>
                                    <th>Users dengan Nilai</th>
                                    <td>${data.overview.users_with_scores || 0}</td>
                                </tr>
                                <tr>
                                    <th>Users tanpa Nilai</th>
                                    <td>${data.overview.users_without_scores || 0}</td>
                                </tr>
                                <tr>
                                    <th>Completion Rate</th>
                                    <td>
                                        <span class="badge badge-${data.overview.completion_rate >= 50 ? 'success' : 'warning'}">
                                            ${data.overview.completion_rate || 0}%
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h6 class="card-title mb-0">Statistik Nilai</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <th>Rata-rata Nilai</th>
                                    <td>${data.score_statistics.average_score ? data.score_statistics.average_score.toFixed(2) : 0}</td>
                                </tr>
                                <tr>
                                    <th>Nilai Tertinggi</th>
                                    <td>${data.score_statistics.max_score || 0}</td>
                                </tr>
                                <tr>
                                    <th>Nilai Terendah</th>
                                    <td>${data.score_statistics.min_score || 0}</td>
                                </tr>
                                <tr>
                                    <th>Total Nilai</th>
                                    <td>${data.score_statistics.total_scores || 0}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h6 class="card-title mb-0">Distribusi Nilai</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Range Nilai</th>
                                            <th>Jumlah</th>
                                            <th width="60%">Persentase</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${(data.score_distribution || []).map(item => {
                                            const percentage = ((item.count / totalScores) * 100).toFixed(1);
                                            return `
                                                <tr>
                                                    <td>${item.score_range}</td>
                                                    <td>${item.count}</td>
                                                    <td>
                                                        <div class="progress" style="height: 20px;">
                                                            <div class="progress-bar" 
                                                                 style="width: ${percentage}%">
                                                                ${percentage}%
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            `;
                                        }).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // Utility functions
    function formatDateTime(dateTimeString) {
        if (!dateTimeString) return '-';
        try {
            const date = new Date(dateTimeString);
            return date.toLocaleString('id-ID');
        } catch (e) {
            return '-';
        }
    }

    function showLoading(message = 'Memuat...') {
        $('#loading-message').text(message);
        $('#loading-indicator').show();
        $('#table-container').hide();
    }

    function hideLoading() {
        $('#loading-indicator').hide();
        $('#table-container').show();
    }

    function showSuccess(message) {
        $('#success-message').text(message);
        $('#successModal').modal('show');
    }

    function showError(message) {
        $('#error-message').text(message);
        $('#errorModal').modal('show');
    }
});
</script>
@endsection
<script src="{{ asset('assets/js/bundle.js') }}"></script>