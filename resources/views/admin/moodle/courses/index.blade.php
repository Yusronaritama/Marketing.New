@extends('layouts.admin.app')

@section('title', 'Data Moodle User Courses')

@section('content')
<div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
    <div class="px-4 py-6 md:px-6 xl:px-7.5">
        <div class="flex justify-between items-center">
            <h4 class="text-xl font-bold text-black dark:text-white">
                Data Moodle User Courses
            </h4>
            <div class="flex gap-2">
                <a href="{{ route('admin.moodle.quiz-attempts.index') }}" 
                   class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                    Data Nilai
                </a>
                <a href="{{ route('admin.moodle.local.index') }}" 
                   class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                    Data Moodle
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <form action="{{ route('admin.moodle.courses.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" 
                       class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                       placeholder="Cari kursus...">
            </div>

            <!-- Moodle User Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Moodle User</label>
                <select name="moodle_user_id" class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                    <option value="">Semua User</option>
                    @foreach($moodleUsers as $user)
                        <option value="{{ $user->moodle_user_id }}" {{ request('moodle_user_id') == $user->moodle_user_id ? 'selected' : '' }}>
                            {{ $user->username }} ({{ $user->moodle_user_id }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                <select name="completed" class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                    <option value="">Semua Status</option>
                    <option value="1" {{ request('completed') === '1' ? 'selected' : '' }}>Completed</option>
                    <option value="0" {{ request('completed') === '0' ? 'selected' : '' }}>Not Completed</option>
                </select>
            </div>

            <!-- Actions -->
            <div class="flex items-end gap-2">
                <button type="submit" class="inline-flex items-center justify-center rounded-md bg-blue-500 px-4 py-2 text-sm font-medium text-white hover:bg-blue-600">
                    Filter
                </button>
                <a href="{{ route('admin.moodle.courses.index') }}" 
                   class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full table-auto">
            <thead>
                <tr class="bg-gray-50 text-left dark:bg-gray-800">
                    <th class="px-4 py-4 font-medium text-black dark:text-white">ID</th>
                    <th class="px-4 py-4 font-medium text-black dark:text-white">User Moodle</th>
                    <th class="px-4 py-4 font-medium text-black dark:text-white">Kursus</th>
                    <th class="px-4 py-4 font-medium text-black dark:text-white">Progress</th>
                    <th class="px-4 py-4 font-medium text-black dark:text-white">Status</th>
                    <th class="px-4 py-4 font-medium text-black dark:text-white">Last Accessed</th>
                    <th class="px-4 py-4 font-medium text-black dark:text-white">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($courses as $course)
                <tr class="border-b border-stroke dark:border-strokedark">
                    <td class="px-4 py-5">
                        <div class="text-sm text-black dark:text-white">{{ $course->id }}</div>
                    </td>
                    {{-- Di bagian tabel, perbaiki kolom User Moodle --}}
<td class="px-4 py-5">
    <div class="text-sm text-black dark:text-white">
        @if($course->moodleUser)
            <div class="font-medium">
                <a href="{{ route('admin.moodle.local.show', $course->moodleUser->id) }}" 
                   class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                    {{ $course->moodleUser->username }}
                </a>
            </div>
            <div class="text-xs text-gray-500">
                Moodle ID: {{ $course->moodle_user_id }}
            </div>
            @if($course->moodleUser->masterUser && $course->moodleUser->masterUser->user)
                <div class="text-xs text-gray-500">
                    User: {{ $course->moodleUser->masterUser->user->name }}
                </div>
                <div class="text-xs text-gray-500">
                    Email: {{ $course->moodleUser->masterUser->user->email }}
                </div>
            @endif
        @else
            <span class="text-gray-500">User tidak ditemukan</span>
            <div class="text-xs text-gray-500">
                Moodle ID: {{ $course->moodle_user_id }}
            </div>
        @endif
    </div>
</td>
                    <td class="px-4 py-5">
                        <div class="max-w-xs">
                            <div class="text-sm font-medium text-black dark:text-white">{{ $course->displayname }}</div>
                            <div class="text-xs text-gray-500 truncate">{{ $course->shortname }}</div>
                        </div>
                    </td>
                    <td class="px-4 py-5">
                        <div class="flex items-center gap-2">
                            <div class="w-16 bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $course->progress }}%"></div>
                            </div>
                            <span class="text-sm text-black dark:text-white">{{ $course->progress }}%</span>
                        </div>
                    </td>
                    <td class="px-4 py-5">
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
                    </td>
                    <td class="px-4 py-5">
                        <div class="text-sm text-black dark:text-white">
                            {{ $course->last_accessed ? \Carbon\Carbon::parse($course->last_accessed)->format('d M Y H:i') : '-' }}
                        </div>
                    </td>
                    <td class="px-4 py-5">
                        <div class="flex items-center space-x-3.5">
                            <a href="{{ route('admin.moodle.courses.show', $course->id) }}" 
                               class="inline-flex items-center justify-center rounded-md bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700 hover:bg-blue-100 dark:bg-blue-900 dark:text-blue-300">
                                Detail
                            </a>
                            <form action="{{ route('admin.moodle.courses.destroy', $course->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')"
                                        class="inline-flex items-center justify-center rounded-md bg-red-50 px-3 py-1 text-xs font-medium text-red-700 hover:bg-red-100 dark:bg-red-900 dark:text-red-300">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                        Tidak ada data kursus ditemukan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($courses->hasPages())
    <div class="px-4 py-6 border-t border-stroke dark:border-strokedark">
        {{ $courses->links() }}
    </div>
    @endif
</div>
@endsection
<script src="{{ asset('assets/js/bundle.js') }}"></script>