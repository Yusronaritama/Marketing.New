@extends('layouts.admin.app')
@section('title', 'Data Quiz Attempts')

@section('content')
@php
use Illuminate\Support\Facades\DB;
@endphp
<div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-dark">
    <!-- Header -->
    <div class="px-6 py-6 border-b border-gray-200 dark:border-gray-800">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h4 class="text-xl font-semibold text-gray-900 dark:text-white mb-1">
                    Data Quiz Attempts & Kelolosan
                </h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Monitoring hasil quiz dan status kelolosan mahasiswa (Menggunakan Score)
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                <!-- Sync Button -->
                <button type="button" id="syncLolosBtn" onclick="syncLolos()"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-500 px-4 py-2.5 text-sm font-medium text-black hover:bg-emerald-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Sync Kelulusan
                </button>
                
                <!-- Auto Refresh Status -->
                <div class="flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center gap-2">
                        <div id="autoRefreshStatus" class="w-3 h-3 rounded-full bg-success-500 animate-pulse"></div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Auto Refresh</span>
                        <span id="refreshTimer" class="text-xs text-gray-500 dark:text-gray-400">(1:00)</span>
                    </div>
                    <button type="button" id="toggleAutoRefresh" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <a href=""
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    Download data lengkap
                </a>
                <a href="{{ route('admin.moodle.local.index') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
        <form action="{{ route('admin.moodle.quiz-complete.index') }}" method="GET" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Search
                        </div>
                    </label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 transition-colors dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder:text-gray-400 dark:focus:border-brand-800"
                        placeholder="Cari attempt...">
                </div>

                <!-- Moodle User Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Moodle User
                        </div>
                    </label>
                    <select name="moodle_user_id" class="w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 transition-colors dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:border-brand-800">
                        <option value="">Semua User</option>
                        @foreach($moodleUsers as $user)
                        <option value="{{ $user->moodle_user_id }}" {{ request('moodle_user_id') == $user->moodle_user_id ? 'selected' : '' }}>
                            {{ $user->username }} ({{ $user->moodle_user_id }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Quiz Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                            Quiz
                        </div>
                    </label>
                    <select name="quiz_id" class="w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 transition-colors dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:border-brand-800">
                        <option value="">Semua Quiz</option>
                        @foreach($quizzes as $quiz)
                        <option value="{{ $quiz->course_id }}" {{ request('quiz_id') == $quiz->course_id ? 'selected' : '' }}>
                            {{ $quiz->shortname }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Program Studi Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            Program Studi
                        </div>
                    </label>
                    <select name="program_studi" class="w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 transition-colors dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:border-brand-800">
                        <option value="">Semua Program Studi</option>
                        @foreach($programStudiList as $prodi)
                            <option value="{{ $prodi->nama_prodi }}" {{ request('program_studi') == $prodi->nama_prodi ? 'selected' : '' }}>
                                {{ $prodi->kode_prodi }} - {{ $prodi->nama_prodi }}
                                @if($prodi->passing_grade)
                                    (PG: {{ $prodi->passing_grade }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Status
                        </div>
                    </label>
                    <select name="state" class="w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 transition-colors dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:border-brand-800">
                        <option value="">Semua Status</option>
                        <option value="finished" {{ request('state') == 'finished' ? 'selected' : '' }}>Finished</option>
                        <option value="inprogress" {{ request('state') == 'inprogress' ? 'selected' : '' }}>In Progress</option>
                        <option value="overdue" {{ request('state') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                    </select>
                </div>

                <!-- Actions -->
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        Filter
                    </button>
                    <a href="{{ route('admin.moodle.quiz-complete.index') }}"
                        class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Loading Indicator -->
    <div id="loadingIndicator" class="hidden fixed top-0 left-0 w-full h-1 bg-brand-500 z-50">
        <div class="h-full bg-brand-600 animate-pulse"></div>
    </div>

    <!-- Notification -->
    <div id="notification" class="hidden fixed top-4 right-4 z-50 max-w-sm">
        <div class="rounded-lg border border-success-300 bg-success-50 dark:border-success-700 dark:bg-success-500/15 p-4 shadow-lg">
            <div class="flex items-center gap-3">
                <div class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-success-500 text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h4 class="text-sm font-semibold text-success-800 dark:text-success-400" id="notificationTitle">
                        Data Berhasil Diperbarui
                    </h4>
                    <p class="text-xs text-success-600 dark:text-success-500" id="notificationMessage">
                        Data peserta yang lolos telah disimpan ke database
                    </p>
                </div>
                <button type="button" onclick="hideNotification()" class="text-success-400 hover:text-success-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Sync Info -->
    <div class="bg-blue-50 border-b border-blue-200 dark:bg-blue-900/20 dark:border-blue-800 px-6 py-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm text-blue-700 dark:text-blue-300">
                    <strong>CATATAN:</strong> Sistem menggunakan <strong>Score</strong> sebagai nilai untuk seleksi kelolosan
                </span>
            </div>
            <div class="text-xs text-blue-600 dark:text-blue-400">
                Last sync: <span data-last-sync>{{ now()->format('H:i:s') }}</span>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto custom-scrollbar">
        <div class="min-w-[1600px]">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                        <th class="px-4 py-4 text-left">
                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">ID</span>
                        </th>
                        <th class="px-4 py-4 text-left">
                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Email</span>
                        </th>
                        <th class="px-4 py-4 text-left">
                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Username</span>
                        </th>
                        <th class="px-4 py-4 text-left">
                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Nama Lengkap</span>
                        </th>
                        <th class="px-4 py-4 text-left">
                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Program Studi & Kelolosan</span>
                        </th>
                        <th class="px-4 py-4 text-left">
                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Program Studi Diterima</span>
                        </th>
                        <th class="px-4 py-4 text-left">
                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Quiz</span>
                        </th>
                        <th class="px-4 py-4 text-left">
                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Attempt</span>
                        </th>
                        <th class="px-4 py-4 text-left">
                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Score</span>
                        </th>
                        <th class="px-4 py-4 text-left">
                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Status</span>
                        </th>
                        <th class="px-4 py-4 text-left">
                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Durasi</span>
                        </th>
                        <th class="px-4 py-4 text-left">
                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Waktu Selesai</span>
                        </th>
                        <th class="px-4 py-4 text-right">
                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody id="attemptsTableBody">
                    @forelse($attempts as $attempt)
                    @php
                        // Ambil nilai score sebagai dasar perhitungan
                        $nilai = $attempt->score ?? $attempt->grade ?? 0;
                        // Hitung persentase jika ada grade dan score
                        $percentage = $attempt->percentage;
                        if (!$percentage && $attempt->grade && $attempt->score) {
                            $percentage = ($attempt->score / $attempt->grade) * 100;
                        }
                    @endphp
                    <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <!-- ID -->
                        <td class="px-4 py-4">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-brand-50 dark:bg-brand-500/15 text-brand-600 dark:text-brand-400 text-xs font-semibold">
                                {{ $attempt->id }}
                            </span>
                        </td>

                        <!-- Email -->
                        <td class="px-4 py-4">
                            <div class="min-w-0 max-w-xs">
                                @if($attempt->moodleUser && $attempt->moodleUser->masterUser && $attempt->moodleUser->masterUser->user)
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    <span class="text-sm text-gray-700 dark:text-gray-300 truncate" title="{{ $attempt->moodleUser->masterUser->user->email }}">
                                        {{ $attempt->moodleUser->masterUser->user->email }}
                                    </span>
                                </div>
                                @else
                                <span class="text-sm text-gray-500 dark:text-gray-400">-</span>
                                @endif
                            </div>
                        </td>

                        <!-- Username -->
                        <td class="px-4 py-4">
                            <div class="min-w-0 max-w-xs">
                                @if($attempt->moodleUser)
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-gradient-to-br from-brand-500 to-brand-600 flex items-center justify-center text-white text-xs font-semibold flex-shrink-0">
                                        {{ strtoupper(substr($attempt->moodleUser->username, 0, 1)) }}
                                    </div>
                                    <a href="{{ route('admin.moodle.local.show', $attempt->moodleUser->id) }}"
                                        class="text-sm font-semibold text-brand-600 dark:text-brand-400 hover:underline truncate" title="{{ $attempt->moodleUser->username }}">
                                        {{ $attempt->moodleUser->username }}
                                    </a>
                                </div>
                                @else
                                <span class="text-sm text-gray-500 dark:text-gray-400">-</span>
                                @endif
                            </div>
                        </td>

                        <!-- Nama Lengkap -->
                        <td class="px-4 py-4">
                            <div class="min-w-0 max-w-xs">
                                @if($attempt->moodleUser && $attempt->moodleUser->masterUser && $attempt->moodleUser->masterUser->user)
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <span class="text-sm text-gray-700 dark:text-gray-300 truncate" title="{{ $attempt->moodleUser->masterUser->user->name }}">
                                        {{ $attempt->moodleUser->masterUser->user->name }}
                                    </span>
                                </div>
                                @else
                                <span class="text-sm text-gray-500 dark:text-gray-400">-</span>
                                @endif
                            </div>
                        </td>

                        <!-- Program Studi & Kelolosan -->
                        <td class="px-4 py-4">
                            <div class="max-w-xs space-y-3">
                                @if($attempt->moodleUser && $attempt->moodleUser->masterUser && $attempt->moodleUser->masterUser->ereg)
                                    @php
                                        $ereg = $attempt->moodleUser->masterUser->ereg;
                                        
                                        // Ambil data program studi dari tabel program_studi untuk jurusan1
                                        $prodi1Data = DB::table('program_studi')
                                            ->where('status_prodi', '1')
                                            ->where(function($query) use ($ereg) {
                                                $query->where('kode_prodi', $ereg->jurusan1)
                                                      ->orWhere('nama_prodi', 'like', "%{$ereg->jurusan1}%")
                                                      ->orWhere('nama_prodi', $ereg->jurusan1);
                                            })
                                            ->first();
                                        
                                        // Ambil data program studi dari tabel program_studi untuk jurusan2
                                        $prodi2Data = null;
                                        if ($ereg->jurusan2) {
                                            $prodi2Data = DB::table('program_studi')
                                                ->where('status_prodi', '1')
                                                ->where(function($query) use ($ereg) {
                                                    $query->where('kode_prodi', $ereg->jurusan2)
                                                          ->orWhere('nama_prodi', 'like', "%{$ereg->jurusan2}%")
                                                          ->orWhere('nama_prodi', $ereg->jurusan2);
                                                })
                                                ->first();
                                        }

                                        // Hitung status kelolosan berdasarkan SCORE
                                        $statusProdi1 = 'TIDAK LOLOS';
                                        $statusProdi2 = 'TIDAK LOLOS';

                                        if ($prodi1Data && $prodi1Data->passing_grade !== null) {
                                            if ($nilai >= $prodi1Data->passing_grade) {
                                                $statusProdi1 = 'LOLOS';
                                            }
                                        }

                                        if ($prodi2Data && $prodi2Data->passing_grade !== null) {
                                            if ($nilai >= $prodi2Data->passing_grade) {
                                                $statusProdi2 = 'LOLOS';
                                            }
                                        }
                                    @endphp

                                    <!-- Program Studi 1 -->
                                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-3">
                                        <div class="flex items-start justify-between gap-2 mb-2">
                                            <div class="flex-1">
                                                <div class="text-sm font-semibold text-gray-900 dark:text-white mb-1">
                                                    @if($prodi1Data)
                                                        {{ $prodi1Data->kode_prodi }} - {{ $prodi1Data->nama_prodi }}
                                                    @else
                                                        {{ $ereg->jurusan1 }} <span class="text-xs text-red-500">(Data tidak ditemukan)</span>
                                                    @endif
                                                </div>
                                                @if($prodi1Data && $prodi1Data->passing_grade !== null)
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                                        PG: {{ $prodi1Data->passing_grade }} | Kuota: {{ $prodi1Data->kuota ?? 'N/A' }}
                                                    </div>
                                                @else
                                                    <div class="text-xs text-red-500">
                                                        Passing grade tidak ditemukan
                                                    </div>
                                                @endif
                                            </div>
                                            <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-md bg-brand-50 dark:bg-brand-500/15 text-xs font-medium text-brand-600 dark:text-brand-400">
                                                #1
                                            </span>
                                        </div>
                                        @if($statusProdi1 === 'LOLOS')
                                            <div class="inline-flex items-center gap-1.5 rounded-lg bg-success-50 dark:bg-success-500/15 px-2.5 py-1 text-xs font-semibold text-success-700 dark:text-success-500">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                LOLOS (+{{ number_format($nilai - $prodi1Data->passing_grade, 2) }})
                                            </div>
                                        @else
                                            <div class="inline-flex items-center gap-1.5 rounded-lg bg-error-50 dark:bg-error-500/15 px-2.5 py-1 text-xs font-semibold text-error-700 dark:text-error-500">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                                @if($prodi1Data && $prodi1Data->passing_grade !== null)
                                                    TIDAK LOLOS (-{{ number_format($prodi1Data->passing_grade - $nilai, 2) }})
                                                @else
                                                    DATA PG TIDAK DITEMUKAN
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Program Studi 2 -->
                                    @if($ereg->jurusan2)
                                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-3">
                                        <div class="flex items-start justify-between gap-2 mb-2">
                                            <div class="flex-1">
                                                <div class="text-sm font-semibold text-gray-900 dark:text-white mb-1">
                                                    @if($prodi2Data)
                                                        {{ $prodi2Data->kode_prodi }} - {{ $prodi2Data->nama_prodi }}
                                                    @else
                                                        {{ $ereg->jurusan2 }} <span class="text-xs text-red-500">(Data tidak ditemukan)</span>
                                                    @endif
                                                </div>
                                                @if($prodi2Data && $prodi2Data->passing_grade !== null)
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                                        PG: {{ $prodi2Data->passing_grade }} | Kuota: {{ $prodi2Data->kuota ?? 'N/A' }}
                                                    </div>
                                                @else
                                                    <div class="text-xs text-red-500">
                                                        Passing grade tidak ditemukan
                                                    </div>
                                                @endif
                                            </div>
                                            <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-md bg-warning-50 dark:bg-warning-500/15 text-xs font-medium text-warning-600 dark:text-warning-400">
                                                #2
                                            </span>
                                        </div>
                                        @if($statusProdi2 === 'LOLOS')
                                            <div class="inline-flex items-center gap-1.5 rounded-lg bg-success-50 dark:bg-success-500/15 px-2.5 py-1 text-xs font-semibold text-success-700 dark:text-success-500">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                LOLOS (+{{ number_format($nilai - $prodi2Data->passing_grade, 2) }})
                                            </div>
                                        @else
                                            <div class="inline-flex items-center gap-1.5 rounded-lg bg-error-50 dark:bg-error-500/15 px-2.5 py-1 text-xs font-semibold text-error-700 dark:text-error-500">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                                @if($prodi2Data && $prodi2Data->passing_grade !== null)
                                                    TIDAK LOLOS (-{{ number_format($prodi2Data->passing_grade - $nilai, 2) }})
                                                @else
                                                    DATA PG TIDAK DITEMUKAN
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                    @endif
                                @else
                                    <div class="text-center py-4">
                                        <svg class="w-8 h-8 text-gray-300 dark:text-gray-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                        </svg>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Data ereg tidak tersedia</span>
                                    </div>
                                @endif
                            </div>
                        </td>

                        <!-- Program Studi Diterima -->
                        <td class="px-4 py-4">
                            <div class="max-w-xs">
                                @php
                                // Tentukan program studi yang diterima berdasarkan SCORE
                                $prodiDiterima = null;

                                if ($attempt->moodleUser && $attempt->moodleUser->masterUser && $attempt->moodleUser->masterUser->ereg) {
                                    $ereg = $attempt->moodleUser->masterUser->ereg;

                                    // Cek program studi 1
                                    $prodi1Data = DB::table('program_studi')
                                        ->where('status_prodi', '1')
                                        ->where(function($query) use ($ereg) {
                                            $query->where('kode_prodi', $ereg->jurusan1)
                                                  ->orWhere('nama_prodi', 'like', "%{$ereg->jurusan1}%")
                                                  ->orWhere('nama_prodi', $ereg->jurusan1);
                                        })
                                        ->first();

                                    if ($prodi1Data && $prodi1Data->passing_grade !== null && $nilai >= $prodi1Data->passing_grade) {
                                        $prodiDiterima = [
                                            'kode_prodi' => $prodi1Data->kode_prodi,
                                            'nama_prodi' => $prodi1Data->nama_prodi,
                                            'prioritas' => 'Pilihan 1',
                                            'selisih' => number_format($nilai - $prodi1Data->passing_grade, 2),
                                            'passing_grade' => $prodi1Data->passing_grade
                                        ];
                                    } else {
                                        // Cek program studi 2
                                        if ($ereg->jurusan2) {
                                            $prodi2Data = DB::table('program_studi')
                                                ->where('status_prodi', '1')
                                                ->where(function($query) use ($ereg) {
                                                    $query->where('kode_prodi', $ereg->jurusan2)
                                                          ->orWhere('nama_prodi', 'like', "%{$ereg->jurusan2}%")
                                                          ->orWhere('nama_prodi', $ereg->jurusan2);
                                                })
                                                ->first();

                                            if ($prodi2Data && $prodi2Data->passing_grade !== null && $nilai >= $prodi2Data->passing_grade) {
                                                $prodiDiterima = [
                                                    'kode_prodi' => $prodi2Data->kode_prodi,
                                                    'nama_prodi' => $prodi2Data->nama_prodi,
                                                    'prioritas' => 'Pilihan 2',
                                                    'selisih' => number_format($nilai - $prodi2Data->passing_grade, 2),
                                                    'passing_grade' => $prodi2Data->passing_grade
                                                ];
                                            }
                                        }
                                    }
                                }
                                @endphp

                                @if($prodiDiterima)
                                <div class="rounded-lg border-2 border-success-300 dark:border-success-700 bg-success-50 dark:bg-success-500/15 p-3 text-center">
                                    <div class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-success-500 text-white mb-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="text-sm font-bold text-success-700 dark:text-success-500 mb-1">
                                        {{ $prodiDiterima['kode_prodi'] }} - {{ $prodiDiterima['nama_prodi'] }}
                                    </div>
                                    <div class="text-xs text-success-600 dark:text-success-400 mb-1">
                                        {{ $prodiDiterima['prioritas'] }}
                                    </div>
                                    <div class="text-xs text-success-600 dark:text-success-400 mb-1">
                                        PG: {{ $prodiDiterima['passing_grade'] }} | Score: {{ $nilai }}
                                    </div>
                                    <div class="inline-flex items-center gap-1 text-xs font-semibold text-success-700 dark:text-success-500">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                        </svg>
                                        +{{ $prodiDiterima['selisih'] }} dari PG
                                    </div>
                                </div>
                                @else
                                <div class="rounded-lg border-2 border-error-300 dark:border-error-700 bg-error-50 dark:bg-error-500/15 p-3 text-center">
                                    <div class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-error-500 text-white mb-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </div>
                                    <div class="text-sm font-bold text-error-700 dark:text-error-500 mb-1">
                                        TIDAK DITERIMA
                                    </div>
                                    <div class="text-xs text-error-600 dark:text-error-400">
                                        @if($attempt->moodleUser && $attempt->moodleUser->masterUser && $attempt->moodleUser->masterUser->ereg)
                                            Score {{ $nilai }} tidak memenuhi passing grade
                                        @else
                                            Data program studi tidak tersedia
                                        @endif
                                    </div>
                                </div>
                                @endif
                            </div>
                        </td>

                        <!-- Quiz -->
                        <td class="px-4 py-4">
                            <div class="max-w-xs">
                                @if($attempt->course)
                                <div class="flex items-start gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-500/15 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ $attempt->course->shortname }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                            {{ $attempt->course->displayname }}
                                        </div>
                                    </div>
                                </div>
                                @else
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    Quiz ID: {{ $attempt->quiz_id }}
                                </div>
                                @endif
                            </div>
                        </td>

                        <!-- Attempt ID -->
                        <td class="px-4 py-4">
                            <div class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 dark:bg-gray-700 px-3 py-1.5">
                                <svg class="w-3.5 h-3.5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                                </svg>
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    #{{ $attempt->attempt_id ?? '-' }}
                                </span>
                            </div>
                        </td>

                        <!-- Score -->
                        <td class="px-4 py-4">
                            <div class="space-y-1">
                                @php
                                // Tentukan warna berdasarkan score
                                $scoreColor = $nilai >= 70 ? 'success' : ($nilai >= 50 ? 'warning' : 'error');
                                @endphp
                                <div class="inline-flex items-center gap-2 rounded-lg bg-{{ $scoreColor }}-50 dark:bg-{{ $scoreColor }}-500/15 px-3 py-1.5">
                                    @if($nilai >= 70)
                                        <svg class="w-4 h-4 text-{{ $scoreColor }}-600 dark:text-{{ $scoreColor }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    @elseif($nilai >= 50)
                                        <svg class="w-4 h-4 text-{{ $scoreColor }}-600 dark:text-{{ $scoreColor }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4 text-{{ $scoreColor }}-600 dark:text-{{ $scoreColor }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    @endif
                                    <span class="text-lg font-bold text-{{ $scoreColor }}-700 dark:text-{{ $scoreColor }}-500">
                                        {{ $nilai }}
                                    </span>
                                </div>
                                @if($percentage)
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ number_format($percentage, 1) }}%
                                </div>
                                @endif
                                @if($attempt->grade)
                                <div class="text-xs text-gray-400 dark:text-gray-500">
                                    Max: {{ $attempt->grade }}
                                </div>
                                @endif
                            </div>
                        </td>

                        <!-- Status -->
                        <td class="px-4 py-4">
                            @php
                            $statusConfig = match($attempt->state) {
                            'finished' => [
                            'bg' => 'bg-success-50 dark:bg-success-500/15',
                            'text' => 'text-success-700 dark:text-success-500',
                            'icon' => '
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />'
                            ],
                            'inprogress' => [
                            'bg' => 'bg-warning-50 dark:bg-warning-500/15',
                            'text' => 'text-warning-700 dark:text-warning-400',
                            'icon' => '
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />'
                            ],
                            'overdue' => [
                            'bg' => 'bg-error-50 dark:bg-error-500/15',
                            'text' => 'text-error-700 dark:text-error-500',
                            'icon' => '
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'
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
                                    {{ ucfirst($attempt->state) }}
                                </span>
                            </div>
                        </td>

                        <!-- Durasi -->
                        <!-- Durasi -->
<td class="px-4 py-4">
    @php
    if (!$attempt->timefinish || !$attempt->timestart) {
        $duration = 'N/A';
    } else {
        // Pastikan kita menggunakan timestamp integer
        $timestart = is_object($attempt->timestart) ? $attempt->timestart->getTimestamp() : $attempt->timestart;
        $timefinish = is_object($attempt->timefinish) ? $attempt->timefinish->getTimestamp() : $attempt->timefinish;
        
        if (is_numeric($timestart) && is_numeric($timefinish)) {
            $durasi = $timefinish - $timestart;
            
            // Jika durasi negatif, set ke 0
            if ($durasi < 0) {
                $duration = '00:00';
            } else {
                $hours = floor($durasi / 3600);
                $minutes = floor(($durasi % 3600) / 60);
                $seconds = $durasi % 60;
                
                if ($hours > 0) {
                    $duration = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                } else {
                    $duration = sprintf('%02d:%02d', $minutes, $seconds);
                }
            }
        } else {
            $duration = 'N/A';
        }
    }
    @endphp
    <div class="flex items-center gap-2">
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span class="text-sm text-gray-700 dark:text-gray-300 font-medium">
            {{ $duration }}
        </span>
    </div>
</td>

                       <!-- Waktu Selesai -->
<td class="px-4 py-4">
    <div class="flex items-center gap-2">
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <span class="text-sm text-gray-700 dark:text-gray-300">
            @php
                if ($attempt->timefinish) {
                    if (is_object($attempt->timefinish)) {
                        // Jika sudah Carbon object
                        echo $attempt->timefinish->format('d M Y H:i');
                    } elseif (is_numeric($attempt->timefinish)) {
                        // Jika timestamp integer
                        echo \Carbon\Carbon::createFromTimestamp($attempt->timefinish)->format('d M Y H:i');
                    } else {
                        // Jika string
                        echo \Carbon\Carbon::parse($attempt->timefinish)->format('d M Y H:i');
                    }
                } else {
                    echo '-';
                }
            @endphp
        </span>
    </div>
</td>
                        <!-- Actions -->
                        <td class="px-4 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <!-- View Details Button -->
                                <a href="{{ route('admin.moodle.quiz-complete.show', $attempt->id) }}"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-brand-50 text-brand-600 hover:bg-brand-100 dark:bg-brand-500/15 dark:text-brand-400 dark:hover:bg-brand-500/25 transition-colors"
                                    title="View Details">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>

                               <a href="{{ route('admin.moodle.quiz-complete.edit', $attempt->id) }}"
    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-warning-50 text-warning-600 hover:bg-warning-100 dark:bg-warning-500/15 dark:text-warning-400 dark:hover:bg-warning-500/25 transition-colors"
    title="Edit Data">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
    </svg>
</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="13" class="px-4 py-16 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 mb-4">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                    Tidak ada data quiz attempts
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Belum ada quiz attempts yang ditemukan dengan filter yang dipilih.
                                </p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($attempts->hasPages())
    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-800">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-sm text-gray-500 dark:text-gray-400">
                Menampilkan <span class="font-medium text-gray-900 dark:text-white">{{ $attempts->firstItem() }}</span> - <span class="font-medium text-gray-900 dark:text-white">{{ $attempts->lastItem() }}</span> dari <span class="font-medium text-gray-900 dark:text-white">{{ $attempts->total() }}</span> hasil
            </div>
            <div>
                {{ $attempts->links() }}
            </div>
        </div>
    </div>
    @endif
</div>

@include('admin.moodle.complete.scripts')

<script src="{{ asset('assets/js/bundle.js') }}"></script>
@endsection