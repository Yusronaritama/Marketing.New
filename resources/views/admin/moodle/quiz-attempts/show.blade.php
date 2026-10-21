@extends('layouts.admin.app')

@section('title', 'Detail Quiz Attempt')

@section('content')
<div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
    <div class="px-4 py-6 md:px-6 xl:px-7.5">
        <div class="flex justify-between items-center">
            <h4 class="text-xl font-bold text-black dark:text-white">
                Detail Quiz Attempt
            </h4>
            <a href="{{ route('admin.moodle.quiz-attempts.index') }}" 
               class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                Kembali
            </a>
        </div>
    </div>

    <div class="p-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Attempt Information -->
            <div class="space-y-4">
                <h5 class="text-lg font-semibold text-gray-900 dark:text-white">Informasi Attempt</h5>
                
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">ID:</span>
                    <span class="text-sm font-medium text-black dark:text-white">{{ $attempt->id }}</span>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Attempt ID:</span>
                    <span class="text-sm font-medium text-black dark:text-white">{{ $attempt->attempt_id }}</span>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Quiz ID:</span>
                    <span class="text-sm font-medium text-black dark:text-white">{{ $attempt->quiz_id }}</span>
                </div>
                
                @if($attempt->ereg)
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Jurusan:</span>
                    <div class="text-right">
                        <div class="text-sm font-medium text-black dark:text-white">
                            {{ $attempt->ereg->jurusan1 }}
                        </div>
                        @if($attempt->ereg->jurusan2)
                            <div class="text-sm text-gray-500">
                                {{ $attempt->ereg->jurusan2 }}
                            </div>
                        @endif
                    </div>
                </div>
                @endif

                @if($attempt->jurusan_diterima)
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Jurusan Diterima:</span>
                    <div class="text-right">
                        <div class="text-lg font-bold text-green-600 dark:text-green-400">
                            {{ $attempt->jurusan_diterima['jurusan'] }}
                        </div>
                        <div class="text-sm text-gray-500">
                            {{ $attempt->jurusan_diterima['prioritas'] }}
                        </div>
                        <div class="text-sm text-green-500 font-medium">
                            +{{ $attempt->jurusan_diterima['selisih'] }} dari Passing Grade
                        </div>
                    </div>
                </div>
                @else
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Status Penerimaan:</span>
                    <div class="text-right">
                        <div class="text-lg font-bold text-red-600 dark:text-red-400">
                            TIDAK DITERIMA
                        </div>
                        <div class="text-sm text-gray-500">
                            Nilai tidak memenuhi passing grade
                        </div>
                    </div>
                </div>
                @endif

                <div class="flex justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Quiz:</span>
                    <div class="text-right">
                        @if($attempt->course)
                            <div class="text-sm font-medium text-black dark:text-white">{{ $attempt->course->displayname }}</div>
                            <div class="text-xs text-gray-500">{{ $attempt->course->shortname }}</div>
                        @else
                            <span class="text-sm text-gray-500">Quiz tidak ditemukan</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- User & Status -->
            <div class="space-y-4">
                <h5 class="text-lg font-semibold text-gray-900 dark:text-white">User & Status</h5>
                
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Moodle User:</span>
                    <div class="text-right">
                        @if($attempt->moodleUser)
                            <a href="{{ route('admin.moodle.local.show', $attempt->moodleUser->id) }}" 
                               class="text-sm font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                {{ $attempt->moodleUser->username }}
                            </a>
                            <div class="text-xs text-gray-500">ID: {{ $attempt->moodle_user_id }}</div>
                            
                            @if($attempt->moodleUser->masterUser && $attempt->moodleUser->masterUser->user)
                                <div class="text-xs text-gray-500">
                                    {{ $attempt->moodleUser->masterUser->user->name }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $attempt->moodleUser->masterUser->user->email }}
                                </div>
                            @endif
                        @else
                            <span class="text-sm text-gray-500">User tidak ditemukan</span>
                        @endif
                    </div>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Status:</span>
                    @php
                        $statusColor = match($attempt->state) {
                            'finished' => 'success',
                            'inprogress' => 'warning',
                            'overdue' => 'error',
                            default => 'gray'
                        };
                    @endphp
                    <span class="inline-flex rounded-full bg-{{ $statusColor }}-100 px-3 py-1 text-xs font-medium text-{{ $statusColor }}-800 dark:bg-{{ $statusColor }}-900 dark:text-{{ $statusColor }}-300">
                        {{ $attempt->status }}
                    </span>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Nilai:</span>
                    <div class="text-right">
                        <div class="text-lg font-bold text-black dark:text-white {{ $attempt->sumgrades >= 70 ? 'text-green-600' : ($attempt->sumgrades >= 50 ? 'text-yellow-600' : 'text-red-600') }}">
                            {{ $attempt->sumgrades ?? '0' }}
                        </div>
                        <div class="text-sm text-gray-500">{{ $attempt->grade_percentage }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Timeline -->
        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
            <h5 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Timeline</h5>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Mulai:</span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $attempt->timestart ? \Carbon\Carbon::parse($attempt->timestart)->format('d M Y H:i') : '-' }}
                    </span>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Selesai:</span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $attempt->timefinish ? \Carbon\Carbon::parse($attempt->timefinish)->format('d M Y H:i') : '-' }}
                    </span>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Durasi:</span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $attempt->duration }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Attempt Data (JSON) - Card Version -->
        @if($attempt->attempt_data)
        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
            <h5 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Attempt Data</h5>
            
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-4 py-3 bg-gray-100 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">JSON Data</span>
                        <button id="copy-json-btn" class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-500 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            Salin
                        </button>
                    </div>
                </div>
                <div class="p-4 max-h-64 overflow-auto">
                    <pre class="text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap"><code id="json-content">{{ json_encode($attempt->attempt_data, JSON_PRETTY_PRINT) }}</code></pre>
                </div>
            </div>
        </div>
        @endif

        <!-- Timestamps -->
        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
            <h5 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">System Information</h5>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Created At:</span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ \Carbon\Carbon::parse($attempt->created_at)->format('d M Y H:i') }}
                    </span>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Updated At:</span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ \Carbon\Carbon::parse($attempt->updated_at)->format('d M Y H:i') }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Copy JSON functionality
    const copyButton = document.getElementById('copy-json-btn');
    if (copyButton) {
        copyButton.addEventListener('click', function() {
            const jsonContent = document.getElementById('json-content').textContent;
            
            navigator.clipboard.writeText(jsonContent).then(function() {
                // Show success feedback
                const originalText = copyButton.innerHTML;
                copyButton.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Tersalin!
                `;
                copyButton.classList.remove('bg-white', 'dark:bg-gray-600', 'text-gray-700', 'dark:text-gray-300');
                copyButton.classList.add('bg-green-100', 'dark:bg-green-900', 'text-green-700', 'dark:text-green-300');
                
                setTimeout(function() {
                    copyButton.innerHTML = originalText;
                    copyButton.classList.remove('bg-green-100', 'dark:bg-green-900', 'text-green-700', 'dark:text-green-300');
                    copyButton.classList.add('bg-white', 'dark:bg-gray-600', 'text-gray-700', 'dark:text-gray-300');
                }, 2000);
            }).catch(function(err) {
                console.error('Failed to copy: ', err);
                alert('Gagal menyalin data JSON');
            });
        });
    }
});
</script>
@endsection

<script src="{{ asset('assets/js/bundle.js') }}"></script>