@extends('layouts.admin.app')

@section('title', 'Detail Kursus Moodle')

@section('content')
<div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
    <div class="px-4 py-6 md:px-6 xl:px-7.5">
        <div class="flex justify-between items-center">
            <h4 class="text-xl font-bold text-black dark:text-white">
                Detail Kursus Moodle
            </h4>
            <a href="{{ route('admin.moodle.courses.index') }}" 
               class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                Kembali
            </a>
        </div>
    </div>

    <div class="p-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Course Information -->
            <div class="space-y-4">
                <h5 class="text-lg font-semibold text-gray-900 dark:text-white">Informasi Kursus</h5>
                
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">ID:</span>
                    <span class="text-sm font-medium text-black dark:text-white">{{ $course->id }}</span>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Course ID:</span>
                    <span class="text-sm font-medium text-black dark:text-white">{{ $course->course_id }}</span>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Shortname:</span>
                    <span class="text-sm font-medium text-black dark:text-white">{{ $course->shortname }}</span>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Fullname:</span>
                    <span class="text-sm font-medium text-black dark:text-white">{{ $course->fullname }}</span>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Display Name:</span>
                    <span class="text-sm font-medium text-black dark:text-white">{{ $course->displayname }}</span>
                </div>

                @if($course->summary)
                <div>
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Summary:</span>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $course->summary }}</p>
                </div>
                @endif
            </div>

            <!-- Status & Progress -->
            <div class="space-y-4">
                <h5 class="text-lg font-semibold text-gray-900 dark:text-white">Status & Progress</h5>
                
                {{-- Di bagian Status & Progress --}}
<div class="flex justify-between">
    <span class="text-sm text-gray-600 dark:text-gray-400">Moodle User:</span>
    <div class="text-right">
        @if($course->moodleUser)
            <a href="{{ route('admin.moodle.local.show', $course->moodleUser->id) }}" 
               class="text-sm font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400">
                {{ $course->moodleUser->username }}
            </a>
            <div class="text-xs text-gray-500">Moodle ID: {{ $course->moodle_user_id }}</div>
            
            @if($course->moodleUser->masterUser && $course->moodleUser->masterUser->user)
                <div class="text-xs text-gray-500">
                    Master User: {{ $course->moodleUser->masterUser->user->name }}
                </div>
                <div class="text-xs text-gray-500">
                    Email: {{ $course->moodleUser->masterUser->user->email }}
                </div>
            @endif
        @else
            <span class="text-sm text-gray-500">User tidak ditemukan</span>
            <div class="text-xs text-gray-500">Moodle ID: {{ $course->moodle_user_id }}</div>
        @endif
    </div>
</div>
                
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Progress:</span>
                    <div class="text-right">
                        <div class="flex items-center gap-2">
                            <div class="w-20 bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $course->progress }}%"></div>
                            </div>
                            <span class="text-sm font-medium text-black dark:text-white">{{ $course->progress }}%</span>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Status:</span>
                    @php
                        $statusColor = match($course->status) {
                            'Completed' => 'success',
                            'In Progress' => 'warning',
                            default => 'gray'
                        };
                    @endphp
                    <span class="inline-flex rounded-full bg-{{ $statusColor }}-100 px-3 py-1 text-xs font-medium text-{{ $statusColor }}-800 dark:bg-{{ $statusColor }}-900 dark:text-{{ $statusColor }}-300">
                        {{ $course->status }}
                    </span>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Visible:</span>
                    <span class="text-sm font-medium text-black dark:text-white">
                        {{ $course->visible ? 'Yes' : 'No' }}
                    </span>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Completed:</span>
                    <span class="text-sm font-medium text-black dark:text-white">
                        {{ $course->completed ? 'Yes' : 'No' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Timeline -->
        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
            <h5 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Timeline</h5>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Start Date:</span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $course->startdate ? \Carbon\Carbon::parse($course->startdate)->format('d M Y H:i') : '-' }}
                    </span>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">End Date:</span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $course->enddate ? \Carbon\Carbon::parse($course->enddate)->format('d M Y H:i') : '-' }}
                    </span>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Last Accessed:</span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $course->last_accessed ? \Carbon\Carbon::parse($course->last_accessed)->format('d M Y H:i') : '-' }}
                    </span>
                </div>
            </div>
        </div>
        <!-- Timestamps -->
        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
            <h5 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">System Information</h5>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Created At:</span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ \Carbon\Carbon::parse($course->created_at)->format('d M Y H:i') }}
                    </span>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Updated At:</span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ \Carbon\Carbon::parse($course->updated_at)->format('d M Y H:i') }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
<script src="{{ asset('assets/js/bundle.js') }}"></script>