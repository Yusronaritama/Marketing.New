@extends('layouts.admin.app')

@section('title', 'Quiz Attempt Details')

@section('content')
    <div class="flex">
        <!-- Main Content -->
        <main class="flex-1 p-6">
            <div class="max-w-6xl mx-auto">
                <!-- Breadcrumb -->
                <nav class="mb-6">
                    <ol class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                        <li><a href="{{ route('admin.dashboard.index') }}" class="hover:text-gray-900 dark:hover:text-white">Dashboard</a></li>
                        <li class="flex items-center gap-2">
                            <span class="text-gray-400">/</span>
                            <a href="{{ route('admin.moodle.quiz-complete.index') }}" class="hover:text-gray-900 dark:hover:text-white">Quiz Attempts</a>
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="text-gray-400">/</span>
                            <span class="text-gray-900 dark:text-white">Attempt #{{ $attempt->id }}</span>
                        </li>
                    </ol>
                </nav>

                <!-- Page Header -->
                <div class="mb-8">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Quiz Attempt Details</h2>
                            <p class="text-gray-600 dark:text-gray-400">Detailed information about this quiz attempt</p>
                        </div>
                        <div class="flex items-center gap-3 mt-4 sm:mt-0">
                            <a href="{{ route('admin.moodle.quiz-complete.edit', $attempt->id) }}" 
                               class="px-4 py-2.5 text-sm font-medium text-white bg-brand-500 rounded-lg hover:bg-brand-600 transition-colors focus:outline-hidden focus:ring-3 focus:ring-brand-500/10">
                                Edit Attempt
                            </a>
                            <a href="{{ route('admin.moodle.quiz-complete.index') }}" 
                               class="px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                Back to List
                            </a>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Information -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Basic Information Card -->
                        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-theme-sm border border-gray-200 dark:border-gray-800">
                            <div class="p-6 border-b border-gray-200 dark:border-gray-800">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Basic Information</h3>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Attempt ID</label>
                                        <p class="text-lg font-semibold text-gray-900 dark:text-white">#{{ $attempt->id }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Moodle Attempt ID</label>
                                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $attempt->attempt_id }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Moodle User ID</label>
                                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $attempt->moodle_user_id }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Quiz ID</label>
                                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $attempt->quiz_id }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Kelulusan Information Card -->
                        @php
                            $kelulusan = \App\Models\MasterMoodleLulus::where('moodle_user_id', $attempt->moodle_user_id)
                                ->where('quiz_id', $attempt->quiz_id)
                                ->first();
                        @endphp

                        @if($kelulusan)
                        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-theme-sm border border-success-500 dark:border-success-500/30">
                            <div class="p-6 border-b border-success-500 dark:border-success-500/30 bg-success-50 dark:bg-success-500/15">
                                <h3 class="text-lg font-semibold text-success-700 dark:text-success-500 flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Informasi Kelulusan
                                </h3>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Status Kelulusan -->
                                    <div class="text-center p-4 bg-success-50 dark:bg-success-500/15 rounded-lg border border-success-200 dark:border-success-500/30">
                                        <label class="block text-sm font-medium text-success-700 dark:text-success-500 mb-2">Status Kelulusan</label>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-success-500 text-white">
                                            {{ $kelulusan->status_kelulusan ?? 'LULUS' }}
                                        </span>
                                    </div>

                                    <!-- Prodi Lulus -->
                                    <div class="text-center p-4 bg-success-50 dark:bg-success-500/15 rounded-lg border border-success-200 dark:border-success-500/30">
                                        <label class="block text-sm font-medium text-success-700 dark:text-success-500 mb-2">Prodi Lulus</label>
                                        <p class="text-lg font-bold text-success-700 dark:text-success-500">{{ $kelulusan->prodi_lulus }}</p>
                                        <p class="text-sm text-success-600 dark:text-success-400 mt-1">{{ $kelulusan->nama_prodi_lulus }}</p>
                                    </div>
                                </div>

                                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Pilihan Lulus</label>
                                        <p class="text-gray-900 dark:text-white font-medium">
                                            Pilihan {{ $kelulusan->pilihan_lulus }}
                                            @if($kelulusan->pilihan_lulus == 1)
                                                <span class="text-success-600 dark:text-success-400 ml-2">(Jurusan Utama)</span>
                                            @else
                                                <span class="text-warning-600 dark:text-warning-400 ml-2">(Jurusan Alternatif)</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Nilai Quiz</label>
                                        <p class="text-gray-900 dark:text-white font-medium">{{ number_format($kelulusan->nilai_quiz, 2) }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Jumlah Attempt</label>
                                        <p class="text-gray-900 dark:text-white font-medium">{{ $kelulusan->attempt_count }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Tanggal Lulus</label>
                                        <p class="text-gray-900 dark:text-white font-medium">
                                            {{ \Carbon\Carbon::parse($kelulusan->tanggal_lulus)->format('Y-m-d H:i:s') }}
                                        </p>
                                    </div>
                                </div>

                                @if($kelulusan->keterangan)
                                <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Keterangan</label>
                                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $kelulusan->keterangan }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                        @else
                        <!-- Status Tidak Lulus -->
                        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-theme-sm border border-gray-200 dark:border-gray-800">
                            <div class="p-6 border-b border-gray-200 dark:border-gray-800">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                    </svg>
                                    Status Kelulusan
                                </h3>
                            </div>
                            <div class="p-6">
                                <div class="text-center py-4">
                                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                    </svg>
                                    <p class="text-gray-600 dark:text-gray-400 mb-4">Belum ada data kelulusan untuk attempt ini.</p>
                                    
                                    @if($attempt->state == 'finished')
                                    <div class="bg-blue-light-50 dark:bg-blue-light-500/15 border border-blue-light-500 dark:border-blue-light-500/30 rounded-lg p-4">
                                        <p class="text-sm text-blue-light-500 dark:text-blue-light-500">
                                            Attempt sudah selesai. Data kelulusan akan tersedia setelah proses sinkronisasi.
                                        </p>
                                    </div>
                                    @else
                                    <div class="bg-warning-50 dark:bg-warning-500/15 border border-warning-500 dark:border-warning-500/30 rounded-lg p-4">
                                        <p class="text-sm text-warning-500 dark:text-warning-500">
                                            Attempt belum selesai. Kelulusan hanya dapat diproses untuk attempt yang sudah finished.
                                        </p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Grades & Status Card -->
                        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-theme-sm border border-gray-200 dark:border-gray-800">
                            <div class="p-6 border-b border-gray-200 dark:border-gray-800">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Grades & Status</h3>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Total Grades -->
                                    <div class="text-center p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Total Grades</label>
                                        <p class="text-3xl font-bold text-brand-500">{{ number_format($attempt->sumgrades ?? 0, 2) }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Sum of all grades</p>
                                    </div>

                                    <!-- Attempt State -->
                                    <div class="text-center p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Attempt State</label>
                                        @php
                                            $stateColors = [
                                                'finished' => 'bg-success-500 text-white',
                                                'inprogress' => 'bg-warning-500 text-white',
                                                'overdue' => 'bg-error-500 text-white'
                                            ];
                                            $stateLabels = [
                                                'finished' => 'Finished',
                                                'inprogress' => 'In Progress',
                                                'overdue' => 'Overdue'
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $stateColors[$attempt->state] ?? 'bg-gray-500 text-white' }}">
                                            {{ $stateLabels[$attempt->state] ?? ucfirst($attempt->state) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Timestamps Card -->
                        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-theme-sm border border-gray-200 dark:border-gray-800">
                            <div class="p-6 border-b border-gray-200 dark:border-gray-800">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Timestamps</h3>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Time Start</label>
                                        <p class="text-gray-900 dark:text-white font-medium">
                                            @if($attempt->timestart)
                                                {{ \Carbon\Carbon::parse($attempt->timestart)->format('Y-m-d H:i:s') }}
                                            @else
                                                <span class="text-gray-400">N/A</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Time Finish</label>
                                        <p class="text-gray-900 dark:text-white font-medium">
                                            @if($attempt->timefinish)
                                                {{ \Carbon\Carbon::parse($attempt->timefinish)->format('Y-m-d H:i:s') }}
                                            @else
                                                <span class="text-gray-400">N/A</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Duration</label>
                                        <p class="text-gray-900 dark:text-white font-medium">
                                            @if($attempt->duration)
                                                {{ $attempt->duration }} seconds
                                            @else
                                                <span class="text-gray-400">N/A</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Attempted At</label>
                                        <p class="text-gray-900 dark:text-white font-medium">
                                            @if($attempt->attempted_at)
                                                {{ \Carbon\Carbon::parse($attempt->attempted_at)->format('Y-m-d H:i:s') }}
                                            @else
                                                <span class="text-gray-400">N/A</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Attempt Data Card -->
                        @if($attempt->attempt_data)
                        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-theme-sm border border-gray-200 dark:border-gray-800">
                            <div class="p-6 border-b border-gray-200 dark:border-gray-800">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Attempt Data</h3>
                            </div>
                            <div class="p-6">
                                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                                    <pre class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap overflow-x-auto"><code>{{ json_encode(json_decode($attempt->attempt_data, true), JSON_PRETTY_PRINT) }}</code></pre>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Sidebar Information -->
                    <div class="space-y-6">
                        <!-- User Information Card -->
                        @if($attempt->moodleUser && $attempt->moodleUser->masterUser)
                        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-theme-sm border border-gray-200 dark:border-gray-800">
                            <div class="p-6 border-b border-gray-200 dark:border-gray-800">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">User Information</h3>
                            </div>
                            <div class="p-6">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="w-12 h-12 bg-brand-500 rounded-full flex items-center justify-center">
                                        <span class="text-white font-semibold text-sm">
                                            {{ substr($attempt->moodleUser->masterUser->user->name ?? 'U', 0, 1) }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-white">
                                            {{ $attempt->moodleUser->masterUser->user->name ?? 'Unknown User' }}
                                        </p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $attempt->moodleUser->username }}
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-500 dark:text-gray-400">Email:</span>
                                        <span class="text-gray-900 dark:text-white font-medium">
                                            {{ $attempt->moodleUser->masterUser->user->email ?? 'N/A' }}
                                        </span>
                                    </div>
                                    @if($attempt->moodleUser->masterUser->ereg)
                                    <div class="flex justify-between">
                                        <span class="text-gray-500 dark:text-gray-400">Jurusan 1:</span>
                                        <span class="text-gray-900 dark:text-white font-medium">
                                            {{ $attempt->moodleUser->masterUser->ereg->jurusan1 ?? 'N/A' }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500 dark:text-gray-400">Jurusan 2:</span>
                                        <span class="text-gray-900 dark:text-white font-medium">
                                            {{ $attempt->moodleUser->masterUser->ereg->jurusan2 ?? 'N/A' }}
                                        </span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Course Information Card -->
                        @if($attempt->course)
                        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-theme-sm border border-gray-200 dark:border-gray-800">
                            <div class="p-6 border-b border-gray-200 dark:border-gray-800">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Course Information</h3>
                            </div>
                            <div class="p-6">
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Short Name</label>
                                        <p class="text-gray-900 dark:text-white font-medium">{{ $attempt->course->shortname }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Full Name</label>
                                        <p class="text-gray-900 dark:text-white font-medium">{{ $attempt->course->fullname }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Display Name</label>
                                        <p class="text-gray-900 dark:text-white font-medium">{{ $attempt->course->displayname }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- System Information Card -->
                        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-theme-sm border border-gray-200 dark:border-gray-800">
                            <div class="p-6 border-b border-gray-200 dark:border-gray-800">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">System Information</h3>
                            </div>
                            <div class="p-6">
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-500 dark:text-gray-400">Created:</span>
                                        <span class="text-gray-900 dark:text-white font-medium">
                                            {{ $attempt->created_at ? \Carbon\Carbon::parse($attempt->created_at)->format('Y-m-d H:i:s') : 'N/A' }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500 dark:text-gray-400">Last Updated:</span>
                                        <span class="text-gray-900 dark:text-white font-medium">
                                            {{ $attempt->updated_at ? \Carbon\Carbon::parse($attempt->updated_at)->format('Y-m-d H:i:s') : 'N/A' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions Card -->
                        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-theme-sm border border-gray-200 dark:border-gray-800">
                            <div class="p-6 border-b border-gray-200 dark:border-gray-800">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Quick Actions</h3>
                            </div>
                            <div class="p-6">
                                <div class="space-y-3">
                                    <a href="{{ route('admin.moodle.quiz-complete.edit', $attempt->id) }}" 
                                       class="w-full flex items-center justify-center px-4 py-2.5 text-sm font-medium text-white bg-brand-500 rounded-lg hover:bg-brand-600 transition-colors focus:outline-hidden focus:ring-3 focus:ring-brand-500/10">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit Attempt
                                    </a>
                                    
                                    @if($attempt->moodleUser)
                                    <a href="{{ route('admin.moodle.quiz-complete.by-user', $attempt->moodle_user_id) }}" 
                                       class="w-full flex items-center justify-center px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        View User Attempts
                                    </a>
                                    @endif
                                    
                                    @if($attempt->quiz_id)
                                    <a href="{{ route('admin.moodle.quiz-complete.by-quiz', $attempt->quiz_id) }}" 
                                       class="w-full flex items-center justify-center px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        View Quiz Attempts
                                    </a>
                                    @endif

                                    <!-- Sync Lulus Button -->
                                    @if($attempt->state == 'finished' && !$kelulusan)
                                    <form action="{{ route('admin.moodle.quiz-complete.sync-lolos') }}" method="POST" class="w-full">
                                        @csrf
                                        <input type="hidden" name="attempt_id" value="{{ $attempt->id }}">
                                        <button type="submit" 
                                                class="w-full flex items-center justify-center px-4 py-2.5 text-sm font-medium text-white bg-success-500 rounded-lg hover:bg-success-600 transition-colors focus:outline-hidden focus:ring-3 focus:ring-success-500/10">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                            </svg>
                                            Sync Kelulusan
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
@endsection

<style>
    pre {
        font-family: 'Courier New', monospace;
        font-size: 0.875rem;
        line-height: 1.25;
    }
    
    code {
        background: transparent !important;
        padding: 0 !important;
    }
</style>
<script src="{{ asset('assets/js/bundle.js') }}"></script>