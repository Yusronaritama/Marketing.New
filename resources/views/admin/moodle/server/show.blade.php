@extends('layouts.admin.app')

@section('title', 'Detail Moodle User')

@section('content')
<div class="grid grid-cols-1 gap-6">
    <!-- Header Section -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-md font-bold text-gray-900 dark:text-white">
                Detail Moodle User
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                Informasi lengkap pengguna Moodle dan status pendaftaran
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.moodle.index') }}" 
               class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                <svg class="fill-current" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12.6667 8H3.33341" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M8.00008 12.6667L3.33341 8.00004L8.00008 3.33337" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Kembali
            </a>
        </div>
    </div>

    @if(session('error'))
    <!-- Error Alert -->
    <div class="rounded-lg border border-error-200 bg-error-50 p-4 dark:border-error-800 dark:bg-error-500/15">
        <div class="flex items-center gap-3">
            <svg class="fill-error-500 dark:fill-error-500" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M10 0C4.477 0 0 4.477 0 10C0 15.523 4.477 20 10 20C15.523 20 20 15.523 20 10C20 4.477 15.523 0 10 0ZM10 18C5.582 18 2 14.418 2 10C2 5.582 5.582 2 10 2C14.418 2 18 5.582 18 10C18 14.418 14.418 18 10 18Z"/>
                <path d="M10 5C10.5523 5 11 5.44772 11 6V10C11 10.5523 10.5523 11 10 11C9.44772 11 9 10.5523 9 10V6C9 5.44772 9.44772 5 10 5Z"/>
                <path d="M10 13C10.5523 13 11 13.4477 11 14C11 14.5523 10.5523 15 10 15C9.44772 15 9 14.5523 9 14C9 13.4477 9.44772 13 10 13Z"/>
            </svg>
            <div>
                <p class="text-sm font-medium text-error-800 dark:text-error-500">Error</p>
                <p class="text-sm text-error-700 dark:text-error-400">{{ session('error') }}</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <!-- Left Column: User Information -->
        <div class="space-y-6">
            <!-- Master Moodle Data Card -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-50 dark:bg-brand-500/15">
                        <svg class="fill-brand-500 dark:fill-brand-400" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10 10C12.7614 10 15 7.76142 15 5C15 2.23858 12.7614 0 10 0C7.23858 0 5 2.23858 5 5C5 7.76142 7.23858 10 10 10Z"/>
                            <path d="M10 12C5.58172 12 2 15.5817 2 20H18C18 15.5817 14.4183 12 10 12Z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Data Master Moodle</h3>
                </div>
                
                <div class="space-y-4">
                    @php
                        $masterData = $data['master_moodle_data'] ?? [];
                        $masterUser = $masterData['master_user'] ?? [];
                        
                        // Helper functions untuk class status
                        $getStatusClass = function($status) {
                            return match($status) {
                                'success' => 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500',
                                'error' => 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-500',
                                default => 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-500'
                            };
                        };
                        
                        $getEnrolStatusClass = function($enrolStatus) {
                            return match($enrolStatus) {
                                'enrolled' => 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500',
                                'not_enrolled' => 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-500',
                                default => 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-500'
                            };
                        };
                    @endphp

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">ID</label>
                            <p class="text-sm text-gray-900 dark:text-white">{{ $masterData['id'] ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Moodle User ID</label>
                            <p class="text-sm text-gray-900 dark:text-white">{{ $masterData['moodle_user_id'] ?? '-' }}</p>
                        </div>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Username</label>
                        <p class="text-sm text-gray-900 dark:text-white">{{ $masterData['username'] ?? '-' }}</p>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</label>
                            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium {{ $getStatusClass($masterData['status'] ?? 'unknown') }}">
                                {{ $masterData['status'] ?? 'unknown' }}
                            </span>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Enrollment Status</label>
                            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium {{ $getEnrolStatusClass($masterData['enrol_status'] ?? 'unknown') }}">
                                {{ $masterData['enrol_status'] ?? 'unknown' }}
                            </span>
                        </div>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Error Message</label>
                        <p class="text-sm text-gray-900 dark:text-white">{{ $masterData['error_message'] ?? 'Tidak ada error' }}</p>
                    </div>

                    <!-- Master User Information -->
                    @if(!empty($masterUser))
                    <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Informasi Master User</h4>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <label class="text-gray-500 dark:text-gray-400">VA Number</label>
                                <p class="text-gray-900 dark:text-white">{{ $masterUser['va_number'] ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="text-gray-500 dark:text-gray-400">VA Status</label>
                                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium 
                                    {{ ($masterUser['va_status'] ?? '') == 'paid' ? 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500' : 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-500' }}">
                                    {{ $masterUser['va_status'] ?? 'unknown' }}
                                </span>
                            </div>
                            <div>
                                <label class="text-gray-500 dark:text-gray-400">Transaction ID</label>
                                <p class="text-gray-900 dark:text-white">{{ $masterUser['trx_id'] ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="text-gray-500 dark:text-gray-400">Gelombang</label>
                                <p class="text-gray-900 dark:text-white">{{ $masterUser['gelombang'] ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Moodle User Data Card -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-light-50 dark:bg-blue-light-500/15">
                        <svg class="fill-blue-light-500 dark:fill-blue-light-500" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10 0C4.477 0 0 4.477 0 10C0 15.523 4.477 20 10 20C15.523 20 20 15.523 20 10C20 4.477 15.523 0 10 0ZM10 18C5.582 18 2 14.418 2 10C2 5.582 5.582 2 10 2C14.418 2 18 5.582 18 10C18 14.418 14.418 18 10 18Z"/>
                            <path d="M11 5H9V11H11V5ZM11 13H9V15H11V13Z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Data Pengguna Moodle</h3>
                </div>
                
                <div class="space-y-4">
                    @php
                        $moodleData = $data['moodle_user_data'] ?? [];
                    @endphp

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama Lengkap</label>
                            <p class="text-sm text-gray-900 dark:text-white">{{ $moodleData['fullname'] ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</label>
                            <p class="text-sm text-gray-900 dark:text-white">{{ $moodleData['email'] ?? '-' }}</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Status Akun</label>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium 
                                    {{ ($moodleData['suspended'] ?? false) ? 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-500' : 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500' }}">
                                    {{ ($moodleData['suspended'] ?? false) ? 'Suspended' : 'Active' }}
                                </span>
                                <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium 
                                    {{ ($moodleData['confirmed'] ?? false) ? 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500' : 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-500' }}">
                                    {{ ($moodleData['confirmed'] ?? false) ? 'Confirmed' : 'Unconfirmed' }}
                                </span>
                            </div>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Bahasa</label>
                            <p class="text-sm text-gray-900 dark:text-white">{{ $moodleData['lang'] ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">First Access</label>
                            <p class="text-sm text-gray-900 dark:text-white">
                                @if(isset($moodleData['firstaccess']) && $moodleData['firstaccess'] > 0)
                                    {{ date('d M Y H:i', $moodleData['firstaccess']) }}
                                @else
                                    Never
                                @endif
                            </p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Access</label>
                            <p class="text-sm text-gray-900 dark:text-white">
                                @if(isset($moodleData['lastaccess']) && $moodleData['lastaccess'] > 0)
                                    {{ date('d M Y H:i', $moodleData['lastaccess']) }}
                                @else
                                    Never
                                @endif
                            </p>
                        </div>
                    </div>
                    
                    <!-- Custom Fields -->
                    @if(isset($moodleData['customfields']) && count($moodleData['customfields']) > 0)
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2 block">Informasi Tambahan</label>
                        <div class="space-y-2">
                            @foreach($moodleData['customfields'] as $field)
                            <div class="flex justify-between border-b border-gray-100 pb-2 dark:border-gray-700">
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $field['name'] ?? $field['shortname'] }}</span>
                                <span class="text-sm text-gray-900 dark:text-white">{{ $field['value'] ?? '-' }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @else
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2 block">Informasi Tambahan</label>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ada informasi tambahan</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column: Enrollment & Courses -->
        <div class="space-y-6">
            <!-- Enrollment Status Card -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-theme-purple-500/10 dark:bg-theme-purple-500/15">
                        <svg class="fill-theme-purple-500 dark:fill-theme-purple-500" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M16 2H4C2.89543 2 2 2.89543 2 4V16C2 17.1046 2.89543 18 4 18H16C17.1046 18 18 17.1046 18 16V4C18 2.89543 17.1046 2 16 2Z"/>
                            <path d="M8 7H6V9H8V7Z"/>
                            <path d="M14 7H10V9H14V7Z"/>
                            <path d="M8 11H6V13H8V11Z"/>
                            <path d="M14 11H10V13H14V11Z"/>
                            <path d="M8 15H6V17H8V15Z"/>
                            <path d="M14 15H10V17H14V15Z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Status Enrollment</h3>
                </div>
                
                <div class="space-y-4">
                    @php
                        $enrollmentStatus = $data['enrollment_status'] ?? [];
                        $getEnrollmentStatusClass = function($status) {
                            return match($status) {
                                'enrolled' => 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500',
                                'not_enrolled' => 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-500',
                                default => 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-500'
                            };
                        };
                    @endphp

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</label>
                            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium {{ $getEnrollmentStatusClass($enrollmentStatus['status'] ?? 'unknown') }}">
                                {{ $enrollmentStatus['status'] ?? 'unknown' }}
                            </span>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Jumlah Kursus</label>
                            <p class="text-sm text-gray-900 dark:text-white">{{ $enrollmentStatus['enrolled_courses'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Courses Card -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-orange-500/10 dark:bg-orange-500/15">
                            <svg class="fill-orange-500 dark:fill-orange-400" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M10 2L3 7L10 12L17 7L10 2Z"/>
                                <path d="M3 13L10 18L17 13"/>
                                <path d="M3 10L10 15L17 10"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Kursus yang Diikuti</h3>
                    </div>
                    <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                        {{ count($data['user_courses'] ?? []) }} kursus
                    </span>
                </div>
                
                <div class="space-y-4">
                    @if(isset($data['user_courses']) && count($data['user_courses']) > 0)
                        @foreach($data['user_courses'] as $course)
                        <div class="rounded-lg border border-gray-100 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $course['fullname'] ?? $course['displayname'] ?? $course['shortname'] ?? 'Unknown Course' }}</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $course['shortname'] ?? '-' }}</p>
                                    
                                    <div class="mt-3 grid grid-cols-2 gap-4 text-xs">
                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400">Progress:</span>
                                            <span class="font-medium text-gray-900 dark:text-white">{{ $course['progress'] ?? 0 }}%</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400">Status:</span>
                                            <span class="font-medium {{ ($course['completed'] ?? false) ? 'text-success-600 dark:text-success-500' : 'text-warning-600 dark:text-warning-500' }}">
                                                {{ ($course['completed'] ?? false) ? 'Selesai' : 'Dalam Proses' }}
                                            </span>
                                        </div>
                                    </div>

                                    @if(isset($course['startdate']) && $course['startdate'] > 0)
                                    <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                        Start: {{ date('d M Y', $course['startdate']) }}
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Tidak ada kursus yang diikuti</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quiz Attempts Card -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-theme-pink-500/10 dark:bg-theme-pink-500/15">
                        <svg class="fill-theme-pink-500 dark:fill-theme-pink-500" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10 0C4.477 0 0 4.477 0 10C0 15.523 4.477 20 10 20C15.523 20 20 15.523 20 10C20 4.477 15.523 0 10 0ZM10 18C5.582 18 2 14.418 2 10C2 5.582 5.582 2 10 2C14.418 2 18 5.582 18 10C18 14.418 14.418 18 10 18Z"/>
                            <path d="M10 5C7.791 5 6 6.791 6 9C6 11.209 7.791 13 10 13C12.209 13 14 11.209 14 9C14 6.791 12.209 5 10 5Z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Attempt Quiz</h3>
                </div>
                
                <div class="text-center py-8">
                    @php
                        $quizAttempts = $data['quiz_attempts'] ?? [];
                        $attempts = $quizAttempts['attempts'] ?? [];
                    @endphp

                    @if(count($attempts) > 0)
                        <div class="space-y-3">
                            @foreach($attempts as $attempt)
                            <div class="rounded-lg border border-gray-100 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-800/50">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-900 dark:text-white">Quiz ID: {{ $attempt['quiz'] ?? 'Unknown' }}</span>
                                    <span class="text-gray-500 dark:text-gray-400">{{ $attempt['state'] ?? 'Unknown State' }}</span>
                                </div>
                                @if(isset($attempt['timefinish']) && $attempt['timefinish'] > 0)
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Finished: {{ date('d M Y H:i', $attempt['timefinish']) }}
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    @else
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Tidak ada attempt quiz</p>
                    @endif
                </div>
            </div>

            <!-- User Grades Card -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-success-50 dark:bg-success-500/15">
                        <svg class="fill-success-500 dark:fill-success-500" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10 0C4.477 0 0 4.477 0 10C0 15.523 4.477 20 10 20C15.523 20 20 15.523 20 10C20 4.477 15.523 0 10 0ZM10 18C5.582 18 2 14.418 2 10C2 5.582 5.582 2 10 2C14.418 2 18 5.582 18 10C18 14.418 14.418 18 10 18Z"/>
                            <path d="M8 12L11 15L16 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Nilai User</h3>
                </div>
                
                <div class="text-center py-8">
                    @if(isset($data['user_grades']) && !empty($data['user_grades']))
                        <div class="space-y-3">
                            <!-- Display grades information here -->
                            <p class="text-sm text-gray-600 dark:text-gray-400">Data nilai tersedia</p>
                        </div>
                    @else
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Tidak ada data nilai</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
<script src="{{ asset('assets/js/bundle.js') }}"></script>