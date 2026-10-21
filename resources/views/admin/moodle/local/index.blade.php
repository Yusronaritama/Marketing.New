@extends('layouts.admin.app')

@section('title', 'Master Moodle Data - Local Database')

@section('content')
<div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-dark">
    <!-- Header -->
    <div class="px-6 py-6 border-b border-gray-200 dark:border-gray-800">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h4 class="text-xl font-semibold text-gray-900 dark:text-white mb-1">
                    Master Moodle Data
                </h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Data dari tabel master_moodle dengan informasi user
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.moodle.quiz-attempts.index') }}"
                    class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                    Masuk Tanpa Sync
                </a>

                {{-- Tombol Refresh Data --}}
                <button type="button"
                    onclick="refreshAttempts()"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-green-300 bg-green-50 px-4 py-2.5 text-sm font-medium text-green-700 hover:bg-green-100 dark:border-green-700 dark:bg-green-500/15 dark:text-green-400 dark:hover:bg-green-500/25 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Masuk dengan sync ke Moodle Nilai
                </button>

                {{-- Progress Indicator --}}
                <div id="syncProgress" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
                        <div class="mt-3 text-center">
                            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                            </div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mt-4" id="syncTitle">Sedang Sync Data</h3>
                            <div class="mt-2 px-7 py-3">
                                <p class="text-sm text-gray-500 dark:text-gray-400" id="syncMessage">
                                    Mengambil data quiz attempts dari Moodle...
                                </p>
                                <div class="mt-4" id="syncDetails"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    function refreshAttempts() {
                        showSyncProgress('Refresh Data', 'Mengambil data quiz attempts terbaru dari Moodle...');

                        fetch('{{ route("admin.moodle.quiz-attempts.force-sync") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            })
                            .then(response => {
                                console.log('Response status:', response.status);

                                if (!response.ok) {
                                    // Coba baca response body untuk error details
                                    return response.text().then(text => {
                                        let errorData;
                                        try {
                                            errorData = JSON.parse(text);
                                        } catch (e) {
                                            errorData = {
                                                message: text || 'Unknown error'
                                            };
                                        }
                                        throw new Error(`HTTP ${response.status}: ${errorData.message || response.statusText}`);
                                    });
                                }
                                return response.json();
                            })
                            .then(data => {
                                console.log('Success data:', data);

                                if (data.success) {
                                    updateSyncProgress('Refresh Berhasil!', `
                <div class="text-green-600 text-sm">
                    <p>✅ ${data.message}</p>
                    <p class="mt-2">📊 Summary:</p>
                    <ul class="text-xs text-green-500 mt-1">
                        <li>Total Users: ${data.summary.total_users}</li>
                        <li>Berhasil: ${data.summary.users_updated}</li>
                        <li>Gagal: ${data.summary.users_failed}</li>
                        <li>Attempts Disinkronisasi: ${data.summary.total_attempts_synced}</li>
                        <li>Users dengan Attempts: ${data.summary.users_with_attempts}</li>
                    </ul>
                </div>
            `);

                                    setTimeout(() => {
                                        hideSyncProgress();
                                        window.location.href = "{{ route('admin.moodle.quiz-attempts.index') }}";
                                    }, 3000);

                                } else {
                                    updateSyncProgress('Refresh Gagal!', `
                <div class="text-red-600 text-sm">
                    <p>❌ ${data.message}</p>
                    ${data.debug ? `<p class="text-xs mt-1">Debug: ${JSON.stringify(data.debug)}</p>` : ''}
                </div>
            `);
                                    setTimeout(hideSyncProgress, 5000);
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                updateSyncProgress('Error!', `
            <div class="text-red-600 text-sm">
                <p>❌ Terjadi error saat refresh: ${error.message}</p>
                <p class="text-xs mt-1">Cek log server untuk detail lebih lanjut.</p>
            </div>
        `);
                                setTimeout(hideSyncProgress, 5000);
                            });
                    }

                    function syncAllQuizAttempts() {
                        showSyncProgress('Sync Data Baru', 'Mengambil data quiz attempts baru dari Moodle...');

                        fetch('{{ route("admin.moodle.quiz-attempts.sync-all") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    updateSyncProgress('Sync Berhasil!', `
                <div class="text-green-600 text-sm">
                    <p>✅ ${data.message}</p>
                    <p class="mt-2">📊 Summary:</p>
                    <ul class="text-xs text-green-500 mt-1">
                        <li>Total Users: ${data.summary.total_users}</li>
                        <li>Berhasil: ${data.summary.users_success}</li>
                        <li>Gagal: ${data.summary.users_failed}</li>
                        <li>Attempts Baru: ${data.summary.total_attempts_synced}</li>
                        <li>Users dengan Attempts: ${data.summary.users_with_attempts}</li>
                    </ul>
                </div>
            `);

                                    setTimeout(() => {
                                        hideSyncProgress();
                                        window.location.href = "{{ route('admin.moodle.quiz-attempts.index') }}";
                                    }, 3000);

                                } else {
                                    updateSyncProgress('Sync Gagal!', `
                <div class="text-red-600 text-sm">
                    <p>❌ ${data.message}</p>
                </div>
            `);
                                    setTimeout(hideSyncProgress, 5000);
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                updateSyncProgress('Error!', `
            <div class="text-red-600 text-sm">
                <p>❌ Terjadi error saat sync: ${error.message}</p>
            </div>
        `);
                                setTimeout(hideSyncProgress, 5000);
                            });
                    }

                    function showSyncProgress(title, message) {
                        document.getElementById('syncTitle').textContent = title;
                        document.getElementById('syncMessage').textContent = message;
                        document.getElementById('syncDetails').innerHTML = '';
                        document.getElementById('syncProgress').classList.remove('hidden');
                    }

                    function updateSyncProgress(title, details) {
                        document.getElementById('syncTitle').textContent = title;
                        document.getElementById('syncDetails').innerHTML = details;
                    }

                    function hideSyncProgress() {
                        document.getElementById('syncProgress').classList.add('hidden');
                    }

                    // Fungsi untuk menampilkan statistik
                    function showStatistics() {
                        fetch('{{ route("admin.moodle.quiz-attempts.statistics") }}')
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    const stats = data.data;
                                    alert(`
📊 Statistik Quiz Attempts (Quiz ID: ${stats.quiz_id})

👥 Users:
- Total Users: ${stats.total_users}
- Users dengan Attempts: ${stats.users_with_attempts}
- Users tanpa Attempts: ${stats.users_without_attempts}
- Completion Rate: ${stats.completion_rate}%

📈 Attempts:
- Total Attempts: ${stats.total_attempts}

🎯 Nilai:
- Rata-rata: ${stats.grade_statistics.average_grade}
- Tertinggi: ${stats.grade_statistics.highest_grade}
- Terendah: ${stats.grade_statistics.lowest_grade}

🔄 Last Sync: ${stats.last_sync ? new Date(stats.last_sync).toLocaleString() : 'Belum ada sync'}
            `);
                                } else {
                                    alert('❌ Gagal mengambil statistik: ' + data.message);
                                }
                            })
                            .catch(error => {
                                alert('❌ Error mengambil statistik: ' + error.message);
                            });
                    }
                </script>
                <a href="{{ route('admin.moodle.index') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Table Container with Scroll -->
    <div class="overflow-x-auto">
        <div class="min-w-[1000px]">
            <!-- Table Header -->
            <div class="grid grid-cols-12 gap-4 bg-gray-50 dark:bg-gray-800 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="col-span-1">
                    <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">ID</p>
                </div>
                <div class="col-span-2">
                    <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">User Info</p>
                </div>
                <div class="col-span-2">
                    <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Moodle ID</p>
                </div>
                <div class="col-span-2">
                    <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Username</p>
                </div>
                <div class="col-span-2">
                    <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Status</p>
                </div>
                <div class="col-span-2">
                    <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Enrol</p>
                </div>
                <div class="col-span-1 text-right">
                    <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Actions</p>
                </div>
            </div>

            <!-- Table Rows -->
            @forelse($moodleData as $data)
            <div class="grid grid-cols-12 gap-4 px-6 py-4 border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                <!-- ID -->
                <div class="col-span-1 flex items-center">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-brand-50 dark:bg-brand-500/15 text-brand-600 dark:text-brand-400 text-xs font-semibold">
                        {{ $data->id }}
                    </span>
                </div>

                <!-- User Info -->
                <div class="col-span-2 flex items-center">
                    <div class="min-w-0">
                        @if($data->masterUser && $data->masterUser->user)
                        <div class="flex items-center gap-2 mb-1">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-brand-500 to-brand-600 flex items-center justify-center text-white text-xs font-semibold flex-shrink-0">
                                {{ strtoupper(substr($data->masterUser->user->name, 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                    {{ $data->masterUser->user->name }}
                                </p>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate ml-10">
                            {{ $data->masterUser->user->email }}
                        </p>
                        @if($data->masterUser->user->phone)
                        <p class="text-xs text-gray-400 dark:text-gray-500 ml-10">
                            {{ $data->masterUser->user->phone }}
                        </p>
                        @endif
                        @else
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">User tidak ditemukan</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">ID: {{ $data->master_user_id }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Moodle User ID -->
                <div class="col-span-2 flex items-center">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span class="text-sm text-gray-700 dark:text-gray-300 font-mono">
                            {{ $data->moodle_user_id ?? '-' }}
                        </span>
                    </div>
                </div>

                <!-- Username -->
                <div class="col-span-2 flex items-center">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $data->username }}
                        </span>
                    </div>
                </div>

                <!-- Status -->
                <div class="col-span-2 flex items-center">
                    @php
                    $statusConfig = match($data->status) {
                    'success', 'active', 'synced' => [
                    'bg' => 'bg-success-50 dark:bg-success-500/15',
                    'text' => 'text-success-700 dark:text-success-500',
                    'icon' => '
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />'
                    ],
                    'pending' => [
                    'bg' => 'bg-warning-50 dark:bg-warning-500/15',
                    'text' => 'text-warning-700 dark:text-warning-400',
                    'icon' => '
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />'
                    ],
                    'error', 'failed' => [
                    'bg' => 'bg-error-50 dark:bg-error-500/15',
                    'text' => 'text-error-700 dark:text-error-500',
                    'icon' => '
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />'
                    ],
                    default => [
                    'bg' => 'bg-gray-100 dark:bg-gray-700',
                    'text' => 'text-gray-700 dark:text-gray-300',
                    'icon' => '
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'
                    ]
                    };
                    @endphp
                    <div class="inline-flex items-center gap-2 rounded-lg {{ $statusConfig['bg'] }} px-3 py-1.5">
                        <svg class="w-4 h-4 {{ $statusConfig['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            {!! $statusConfig['icon'] !!}
                        </svg>
                        <span class="text-xs font-semibold {{ $statusConfig['text'] }}">
                            {{ ucfirst($data->status ?? 'Unknown') }}
                        </span>
                    </div>
                </div>

                <!-- Enrol Status -->
                <div class="col-span-2 flex items-center">
                    @php
                    $enrolConfig = match($data->enrol_status) {
                    'enrolled' => [
                    'bg' => 'bg-success-50 dark:bg-success-500/15',
                    'text' => 'text-success-700 dark:text-success-500',
                    'icon' => '
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />'
                    ],
                    'pending' => [
                    'bg' => 'bg-warning-50 dark:bg-warning-500/15',
                    'text' => 'text-warning-700 dark:text-warning-400',
                    'icon' => '
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />'
                    ],
                    default => [
                    'bg' => 'bg-gray-100 dark:bg-gray-700',
                    'text' => 'text-gray-700 dark:text-gray-300',
                    'icon' => '
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />'
                    ]
                    };
                    @endphp
                    <div class="inline-flex items-center gap-2 rounded-lg {{ $enrolConfig['bg'] }} px-3 py-1.5">
                        <svg class="w-4 h-4 {{ $enrolConfig['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            {!! $enrolConfig['icon'] !!}
                        </svg>
                        <span class="text-xs font-semibold {{ $enrolConfig['text'] }}">
                            {{ ucfirst(str_replace('_', ' ', $data->enrol_status ?? 'Not Enrolled')) }}
                        </span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="col-span-1 flex items-center justify-end gap-2">
                    <!-- Sync Button -->
                    <button onclick="syncData({{ $data->id }})"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-500/15 dark:text-blue-400 dark:hover:bg-blue-500/25 transition-colors"
                        title="Sync with Moodle">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </button>

                    <!-- View Button -->
                    <a href="{{ route('admin.moodle.local.show', $data->id) }}"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 dark:bg-green-500/15 dark:text-green-400 dark:hover:bg-green-500/25 transition-colors"
                        title="View Details">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </a>


                </div>
            </div>
            @empty
            <div class="px-6 py-16 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Tidak ada data</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Belum ada data di tabel master_moodle.</p>
                <a href="{{ route('admin.moodle.local.create') }}"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-500 text-white text-sm font-medium rounded-lg hover:bg-brand-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Data Pertama
                </a>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Pagination -->
    @if($moodleData->hasPages())
    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-800">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-500 dark:text-gray-400">
                Menampilkan {{ $moodleData->firstItem() }} - {{ $moodleData->lastItem() }} dari {{ $moodleData->total() }} data
            </div>
            <div>
                {{ $moodleData->links() }}
            </div>
        </div>
    </div>
    @endif
</div>

<script>
    function syncData(id) {
        if (confirm('Sync data dengan Moodle?')) {
            const button = event.target.closest('button');
            const originalHTML = button.innerHTML;

            // Loading state
            button.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>';
            button.disabled = true;
            button.classList.add('opacity-50', 'cursor-not-allowed');

            fetch(`/admin/moodle/local/${id}/sync`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Success notification
                        showNotification('success', 'Sync berhasil!');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showNotification('error', 'Sync gagal: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('error', 'Terjadi kesalahan saat sync');
                })
                .finally(() => {
                    button.innerHTML = originalHTML;
                    button.disabled = false;
                    button.classList.remove('opacity-50', 'cursor-not-allowed');
                });
        }
    }

    function showNotification(type, message) {
        const colors = {
            success: 'bg-success-50 text-success-700 border-success-300 dark:bg-success-500/15 dark:text-success-400',
            error: 'bg-error-50 text-error-700 border-error-300 dark:bg-error-500/15 dark:text-error-400'
        };

        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-lg border ${colors[type]} shadow-lg transition-all duration-300 transform translate-x-full`;
        notification.textContent = message;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);

        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
</script>
@endsection
<script src="{{ asset('assets/js/bundle.js') }}"></script>