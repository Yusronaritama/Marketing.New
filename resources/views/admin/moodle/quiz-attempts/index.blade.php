@extends('layouts.admin.app')

@section('title', 'Data Quiz Attempts')

@section('content')
<div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-dark">
    <!-- Header -->
    <div class="px-6 py-6 border-b border-gray-200 dark:border-gray-800">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h4 class="text-xl font-semibold text-gray-900 dark:text-white mb-1">
                    Data Quiz Attempts & Kelolosan
                </h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Monitoring hasil quiz dan status kelolosan mahasiswa
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="" 
                   class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    Download data lengkap
                </a>
                <a href="{{ route('admin.moodle.courses.index') }}" 
                   class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    Edit Semua Data
                </a>
                <a href="{{ route('admin.moodle.local.index') }}" 
                   class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
        <form action="{{ route('admin.moodle.quiz-attempts.index') }}" method="GET" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
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

                <!-- Jurusan Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            Jurusan
                        </div>
                    </label>
                    <select name="jurusan" class="w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 transition-colors dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:border-brand-800">
                        <option value="">Semua Jurusan</option>
                        @foreach($jurusanList as $jurusan)
                            <option value="{{ $jurusan }}" {{ request('jurusan') == $jurusan ? 'selected' : '' }}>
                                {{ $jurusan }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Filter
                    </button>
                    <a href="{{ route('admin.moodle.quiz-attempts.index') }}" 
                       class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto custom-scrollbar">
        <div class="min-w-[1400px]">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                        <th class="px-4 py-4 text-left">
                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">ID</span>
                        </th>
                        <th class="px-4 py-4 text-left">
                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">User</span>
                        </th>
                        <th class="px-4 py-4 text-left">
                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Jurusan & Kelolosan</span>
                        </th>
                        <th class="px-4 py-4 text-left">
                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Jurusan Diterima</span>
                        </th>
                        <th class="px-4 py-4 text-left">
                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Quiz</span>
                        </th>
                        <th class="px-4 py-4 text-left">
                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Attempt</span>
                        </th>
                        <th class="px-4 py-4 text-left">
                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Nilai</span>
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
                <tbody>
                    @forelse($attempts as $attempt)
                    <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <!-- ID -->
                        <td class="px-4 py-4">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-brand-50 dark:bg-brand-500/15 text-brand-600 dark:text-brand-400 text-xs font-semibold">
                                {{ $attempt->id }}
                            </span>
                        </td>

                        <!-- User -->
                        <td class="px-4 py-4">
                            <div class="min-w-0">
                                @if($attempt->moodleUser)
                                    <div class="flex items-center gap-2 mb-1">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-brand-500 to-brand-600 flex items-center justify-center text-white text-xs font-semibold flex-shrink-0">
                                            {{ strtoupper(substr($attempt->moodleUser->username, 0, 2)) }}
                                        </div>
                                        <a href="{{ route('admin.moodle.local.show', $attempt->moodleUser->id) }}" 
                                           class="text-sm font-semibold text-brand-600 dark:text-brand-400 hover:underline">
                                            {{ $attempt->moodleUser->username }}
                                        </a>
                                    </div>
                                    @if($attempt->moodleUser->masterUser && $attempt->moodleUser->masterUser->user)
                                        <div class="text-xs text-gray-700 dark:text-gray-300 ml-10">
                                            {{ $attempt->moodleUser->masterUser->user->name }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 ml-10 truncate">
                                            {{ $attempt->moodleUser->masterUser->user->email }}
                                        </div>
                                    @endif
                                @else
                                    <span class="text-sm text-gray-500 dark:text-gray-400">User tidak ditemukan</span>
                                @endif
                            </div>
                        </td>

                        <!-- Jurusan & Kelolosan -->
                        <td class="px-4 py-4">
                            <div class="max-w-xs space-y-3">
                                @if($attempt->ereg)
                                    <!-- Jurusan 1 -->
                                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-3">
                                        <div class="flex items-start justify-between gap-2 mb-2">
                                            <div class="flex-1">
                                                <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                                    {{ $attempt->ereg->jurusan1 }}
                                                </div>
                                                @if($attempt->passing_grade_jurusan1)
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                                        PG: {{ $attempt->passing_grade_jurusan1 }}
                                                    </div>
                                                @endif
                                            </div>
                                            <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-md bg-brand-50 dark:bg-brand-500/15 text-xs font-medium text-brand-600 dark:text-brand-400">
                                                #1
                                            </span>
                                        </div>
                                        @if($attempt->status_lolos_jurusan1 === 'LOLOS')
                                            <div class="inline-flex items-center gap-1.5 rounded-lg bg-success-50 dark:bg-success-500/15 px-2.5 py-1 text-xs font-semibold text-success-700 dark:text-success-500">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                LOLOS
                                            </div>
                                        @elseif($attempt->status_lolos_jurusan1 === 'TIDAK LOLOS')
                                            <div class="inline-flex items-center gap-1.5 rounded-lg bg-error-50 dark:bg-error-500/15 px-2.5 py-1 text-xs font-semibold text-error-700 dark:text-error-500">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                                TIDAK LOLOS
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $attempt->status_lolos_jurusan1 }}</span>
                                        @endif
                                    </div>

                                    <!-- Jurusan 2 -->
                                    @if($attempt->ereg->jurusan2)
                                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-3">
                                        <div class="flex items-start justify-between gap-2 mb-2">
                                            <div class="flex-1">
                                                <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                                    {{ $attempt->ereg->jurusan2 }}
                                                </div>
                                                @if($attempt->passing_grade_jurusan2)
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                                        PG: {{ $attempt->passing_grade_jurusan2 }}
                                                    </div>
                                                @endif
                                            </div>
                                            <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-md bg-warning-50 dark:bg-warning-500/15 text-xs font-medium text-warning-600 dark:text-warning-400">
                                                #2
                                            </span>
                                        </div>
                                        @if($attempt->status_lolos_jurusan2 === 'LOLOS')
                                            <div class="inline-flex items-center gap-1.5 rounded-lg bg-success-50 dark:bg-success-500/15 px-2.5 py-1 text-xs font-semibold text-success-700 dark:text-success-500">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                LOLOS
                                            </div>
                                        @elseif($attempt->status_lolos_jurusan2 === 'TIDAK LOLOS')
                                            <div class="inline-flex items-center gap-1.5 rounded-lg bg-error-50 dark:bg-error-500/15 px-2.5 py-1 text-xs font-semibold text-error-700 dark:text-error-500">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                                TIDAK LOLOS
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $attempt->status_lolos_jurusan2 }}</span>
                                        @endif
                                    </div>
                                    @endif
                                @else
                                    <div class="text-center py-4">
                                        <svg class="w-8 h-8 text-gray-300 dark:text-gray-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                        </svg>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Data tidak tersedia</span>
                                    </div>
                                @endif
                            </div>
                        </td>

                        <!-- Jurusan Diterima -->
                        <td class="px-4 py-4">
                            <div class="max-w-xs">
                                @if($attempt->jurusan_diterima)
                                    <div class="rounded-lg border-2 border-success-300 dark:border-success-700 bg-success-50 dark:bg-success-500/15 p-3 text-center">
                                        <div class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-success-500 text-white mb-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                        <div class="text-sm font-bold text-success-700 dark:text-success-500 mb-1">
                                            {{ $attempt->jurusan_diterima['jurusan'] }}
                                        </div>
                                        <div class="text-xs text-success-600 dark:text-success-400 mb-1">
                                            {{ $attempt->jurusan_diterima['prioritas'] }}
                                        </div>
                                        <div class="inline-flex items-center gap-1 text-xs font-semibold text-success-700 dark:text-success-500">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                            </svg>
                                            +{{ $attempt->jurusan_diterima['selisih'] }} dari PG
                                        </div>
                                    </div>
                                @else
                                    <div class="rounded-lg border-2 border-error-300 dark:border-error-700 bg-error-50 dark:bg-error-500/15 p-3 text-center">
                                        <div class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-error-500 text-white mb-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </div>
                                        <div class="text-sm font-bold text-error-700 dark:text-error-500 mb-1">
                                            TIDAK DITERIMA
                                        </div>
                                        <div class="text-xs text-error-600 dark:text-error-400">
                                            Nilai tidak memenuhi
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
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
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

                        <!-- Attempt -->
                        <td class="px-4 py-4">
                            <div class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 dark:bg-gray-700 px-3 py-1.5">
                                <svg class="w-3.5 h-3.5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                                </svg>
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    {{ $attempt->sumgrades ?? '-' }}
                                </span>
                            </div>
                        </td>

                        <!-- Nilai -->
                        <td class="px-4 py-4">
                            <div class="space-y-1">
                                @php
                                    $scoreColor = $attempt->sumgrades >= 70 ? 'success' : ($attempt->sumgrades >= 50 ? 'warning' : 'error');
                                @endphp
                                <div class="inline-flex items-center gap-2 rounded-lg bg-{{ $scoreColor }}-50 dark:bg-{{ $scoreColor }}-500/15 px-3 py-1.5">
                                    <svg class="w-4 h-4 text-{{ $scoreColor }}-600 dark:text-{{ $scoreColor }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($attempt->sumgrades >= 70)
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        @elseif($attempt->sumgrades >= 50)
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        @endif
                                    </svg>
                                    <span class="text-lg font-bold text-{{ $scoreColor }}-700 dark:text-{{ $scoreColor }}-500">
                                        {{ $attempt->sumgrades ?? '0' }}
                                    </span>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $attempt->grade_percentage }}
                                </div>
                            </div>
                        </td>

                        <!-- Status -->
                        <td class="px-4 py-4">
                            @php
                                $statusConfig = match($attempt->state) {
                                    'finished' => [
                                        'bg' => 'bg-success-50 dark:bg-success-500/15',
                                        'text' => 'text-success-700 dark:text-success-500',
                                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>'
                                    ],
                                    'inprogress' => [
                                        'bg' => 'bg-warning-50 dark:bg-warning-500/15',
                                        'text' => 'text-warning-700 dark:text-warning-400',
                                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>'
                                    ],
                                    'overdue' => [
                                        'bg' => 'bg-error-50 dark:bg-error-500/15',
                                        'text' => 'text-error-700 dark:text-error-500',
                                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'
                                    ],
                                    default => [
                                        'bg' => 'bg-gray-100 dark:bg-gray-700',
                                        'text' => 'text-gray-700 dark:text-gray-300',
                                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'
                                    ]
                                };
                            @endphp
                            <div class="inline-flex items-center gap-2 rounded-lg {{ $statusConfig['bg'] }} px-3 py-1.5">
                                <svg class="w-4 h-4 {{ $statusConfig['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    {!! $statusConfig['icon'] !!}
                                </svg>
                                <span class="text-xs font-semibold {{ $statusConfig['text'] }}">
                                    {{ ucfirst($attempt->status) }}
                                </span>
                            </div>
                        </td>

                        <!-- Durasi -->
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-sm text-gray-700 dark:text-gray-300 font-medium">
                                    {{ $attempt->duration }}
                                </span>
                            </div>
                        </td>

                        <!-- Waktu Selesai -->
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-sm text-gray-700 dark:text-gray-300">
                                    {{ $attempt->timefinish ? \Carbon\Carbon::parse($attempt->timefinish)->format('d M Y H:i') : '-' }}
                                </span>
                            </div>
                        </td>

                        <!-- Actions -->
                        <td class="px-4 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.moodle.quiz-attempts.show', $attempt->id) }}" 
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-brand-50 text-brand-600 hover:bg-brand-100 dark:bg-brand-500/15 dark:text-brand-400 dark:hover:bg-brand-500/25 transition-colors"
                                   title="View Details">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="px-4 py-16 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 mb-4">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
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

<!-- Statistics Summary (Optional) -->
@if($attempts->total() > 0)
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
    <!-- Total Attempts -->
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-dark">
        <div class="flex items-center justify-between mb-2">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-brand-50 dark:bg-brand-500/15">
                <svg class="w-6 h-6 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
        </div>
        <div class="text-2xl font-bold text-gray-900 dark:text-white mb-1">
            {{ $attempts->total() }}
        </div>
        <div class="text-sm text-gray-500 dark:text-gray-400">
            Total Attempts
        </div>
    </div>

    <!-- Finished -->
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-dark">
        <div class="flex items-center justify-between mb-2">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-success-50 dark:bg-success-500/15">
                <svg class="w-6 h-6 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <div class="text-2xl font-bold text-gray-900 dark:text-white mb-1">
            {{ $attempts->where('state', 'finished')->count() }}
        </div>
        <div class="text-sm text-gray-500 dark:text-gray-400">
            Finished
        </div>
    </div>

    <!-- In Progress -->
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-dark">
        <div class="flex items-center justify-between mb-2">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-warning-50 dark:bg-warning-500/15">
                <svg class="w-6 h-6 text-warning-600 dark:text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <div class="text-2xl font-bold text-gray-900 dark:text-white mb-1">
            {{ $attempts->where('state', 'inprogress')->count() }}
        </div>
        <div class="text-sm text-gray-500 dark:text-gray-400">
            In Progress
        </div>
    </div>

    <!-- Average Score -->
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-dark">
        <div class="flex items-center justify-between mb-2">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-500/15">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
        </div>
        <div class="text-2xl font-bold text-gray-900 dark:text-white mb-1">
            {{ number_format($attempts->avg('sumgrades'), 1) }}
        </div>
        <div class="text-sm text-gray-500 dark:text-gray-400">
            Rata-rata Nilai
        </div>
    </div>
</div>
@endif
@endsection
<script src="{{ asset('assets/js/bundle.js') }}"></script>