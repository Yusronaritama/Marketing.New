@extends('layouts.admin.app')

@section('title', 'Detail Data Moodle')

@section('content')
<div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
    <div class="px-4 py-6 md:px-6 xl:px-7.5">
        <div class="flex justify-between items-center">
            <h4 class="text-xl font-bold text-black dark:text-white">
                Detail Data Moodle
            </h4>
            <a href="{{ route('admin.moodle.local.index') }}" 
               class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                Kembali
            </a>
        </div>
    </div>

    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Basic Information -->
            <div class="space-y-4">
                <h5 class="text-lg font-semibold text-gray-900 dark:text-white">Informasi Dasar</h5>
                
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">ID:</span>
                    <span class="text-sm font-medium text-black dark:text-white">{{ $moodle->id }}</span>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Master User ID:</span>
                    <span class="text-sm font-medium text-black dark:text-white">{{ $moodle->master_user_id }}</span>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Moodle User ID:</span>
                    <span class="text-sm font-medium text-black dark:text-white">{{ $moodle->moodle_user_id ?? '-' }}</span>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Username:</span>
                    <span class="text-sm font-medium text-black dark:text-white">{{ $moodle->username }}</span>
                </div>
            </div>

            <!-- Status Information -->
            <div class="space-y-4">
                <h5 class="text-lg font-semibold text-gray-900 dark:text-white">Status</h5>
                
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Status:</span>
                    @php
                        $statusColor = match($moodle->status) {
                            'success', 'active', 'synced' => 'success',
                            'pending' => 'warning',
                            'error', 'failed' => 'error',
                            default => 'gray'
                        };
                    @endphp
                    <span class="inline-flex rounded-full bg-{{ $statusColor }}-100 px-3 py-1 text-xs font-medium text-{{ $statusColor }}-800 dark:bg-{{ $statusColor }}-900 dark:text-{{ $statusColor }}-300">
                        {{ $moodle->status ?? 'Unknown' }}
                    </span>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Enrol Status:</span>
                    @php
                        $enrolColor = match($moodle->enrol_status) {
                            'enrolled' => 'success',
                            'pending' => 'warning',
                            'not_enrolled' => 'gray',
                            default => 'gray'
                        };
                    @endphp
                    <span class="inline-flex rounded-full bg-{{ $enrolColor }}-100 px-3 py-1 text-xs font-medium text-{{ $enrolColor }}-800 dark:bg-{{ $enrolColor }}-900 dark:text-{{ $enrolColor }}-300">
                        {{ $moodle->enrol_status ?? 'Not Enrolled' }}
                    </span>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Last Sync:</span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $moodle->last_sync_at ? \Carbon\Carbon::parse($moodle->last_sync_at)->format('d M Y H:i') : 'Never' }}
                    </span>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Created At:</span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ \Carbon\Carbon::parse($moodle->created_at)->format('d M Y H:i') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Additional Information -->
        @if($moodle->error_message || $moodle->sync_notes)
        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
            <h5 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informasi Tambahan</h5>
            
            @if($moodle->error_message)
            <div class="mb-4">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Error Message:</span>
                <p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $moodle->error_message }}</p>
            </div>
            @endif
            
            @if($moodle->sync_notes)
            <div>
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Sync Notes:</span>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $moodle->sync_notes }}</p>
            </div>
            @endif
        </div>
        @endif

        <!-- Actions -->
        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
            <a href="{{ route('admin.moodle.local.edit', $moodle->id) }}" 
               class="inline-flex items-center justify-center rounded-md bg-yellow-500 px-4 py-2 text-sm font-medium text-white hover:bg-yellow-600">
                Edit
            </a>
            <button onclick="syncData({{ $moodle->id }})" 
                    class="inline-flex items-center justify-center rounded-md bg-blue-500 px-4 py-2 text-sm font-medium text-white hover:bg-blue-600">
                Sync dengan Moodle
            </button>
        </div>
    </div>
</div>

<script>
function syncData(id) {
    if (confirm('Sync data dengan Moodle?')) {
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
                alert('Sync berhasil!');
                location.reload();
            } else {
                alert('Sync gagal: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat sync');
        });
    }
}
</script>
@endsection
<script src="{{ asset('assets/js/bundle.js') }}"></script>