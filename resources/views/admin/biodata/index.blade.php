@extends('layouts.admin.app')

@section('title', 'Manajemen Biodata')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-title-sm font-bold text-gray-900 dark:text-white">Manajemen Biodata</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Kelola data biodata mahasiswa dengan mudah dan efisien
            </p>
        </div>
        <div class="mt-4 sm:mt-0">
            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Terakhir diperbarui: {{ now()->format('d M Y, H:i') }}</span>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <!-- Total Biodata Card -->
        <div class="rounded-2xl bg-white p-6 shadow-theme-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
            <div class="flex items-center gap-4">
                <div class="flex-shrink-0">
                    <div class="rounded-xl bg-blue-light-50 p-3 dark:bg-blue-light-500/15">
                        <svg class="h-6 w-6 text-blue-light-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-300">Total Biodata</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $totalBiodata }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Data terkumpul</p>
                </div>
            </div>
        </div>

        <!-- Completion Rate Card -->
        <div class="rounded-2xl bg-white p-6 shadow-theme-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
            <div class="flex items-center gap-4">
                <div class="flex-shrink-0">
                    <div class="rounded-xl bg-success-50 p-3 dark:bg-success-500/15">
                        <svg class="h-6 w-6 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-300">Kelengkapan</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $completionPercentage }}%</p>
                    <div class="mt-2">
                        <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-gray-700">
                            <div class="h-2 rounded-full bg-success-500 transition-all duration-500" style="width: {{ $completionPercentage }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Users Card -->
        <div class="rounded-2xl bg-white p-6 shadow-theme-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
            <div class="flex items-center gap-4">
                <div class="flex-shrink-0">
                    <div class="rounded-xl bg-brand-50 p-3 dark:bg-brand-500/15">
                        <svg class="h-6 w-6 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                        </svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-300">Total Mahasiswa</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $totalUsers }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 flex items-center gap-1">
                        {{ $totalBiodata }}/{{ $totalUsers }} lengkap
                        <span class="inline-flex items-center gap-0.5 text-success-600 dark:text-success-400">
                            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            Paid
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Section -->
    <div class="rounded-2xl bg-white p-6 shadow-theme-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex-1">
                <div class="relative max-w-lg">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        id="searchInput"
                        placeholder="Ketik untuk mencari nama, sekolah, jurusan, atau email..." 
                        class="block w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-10 pr-4 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-brand-500"
                    >
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-sm text-gray-600 dark:text-gray-300">
                    Total data: <span id="totalCount" class="font-semibold">{{ $biodatas->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Biodata Table -->
    <div class="rounded-2xl bg-white shadow-theme-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-full">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr class="divide-x divide-gray-200 dark:divide-gray-600">
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300">
                            ID
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300">
                            Nama Lengkap
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300">
                            Asal Sekolah
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300">
                            Jurusan
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300">
                            Email
                        </th>
                        @if(!in_array(auth()->user()->role, ['admin_akademik', 'admin_farmasi']))
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300">
                            Status
                        </th>
                        @endif
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300">
                            Tanggal
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody id="biodataTableBody" class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($biodatas as $biodata)
                    <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/50" data-id="{{ $biodata->id }}">
                        <td class="whitespace-nowrap px-6 py-4">
                            <div class="flex items-center justify-center h-8 w-8 rounded-full bg-brand-50 text-xs font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                {{ $biodata->id }}
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $biodata->nama_lengkap }}</div>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            <div class="text-sm text-gray-900 dark:text-white">{{ $biodata->asal_sekolah }}</div>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            <span class="inline-flex rounded-full bg-success-50 px-3 py-1 text-xs font-medium text-success-700 dark:bg-success-500/15 dark:text-success-400">
                                {{ $biodata->jurusan_sekolah }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            <div class="text-sm text-gray-900 dark:text-white">{{ $biodata->user->email ?? 'N/A' }}</div>
                        </td>
                        @if(!in_array(auth()->user()->role, ['admin_akademik', 'admin_farmasi']))
                        <td class="whitespace-nowrap px-6 py-4">
                            <div class="flex items-center gap-2">
                                <button type="button" 
                                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-300 ease-in-out focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 status-toggle {{ $biodata->is_verified ? 'bg-success-500' : 'bg-gray-300' }}"
                                        data-id="{{ $biodata->id }}"
                                        data-status="{{ $biodata->is_verified ? 1 : 0 }}"
                                        role="switch"
                                        aria-checked="{{ $biodata->is_verified ? 'true' : 'false' }}"
                                        title="{{ $biodata->is_verified ? 'Klik untuk menonaktifkan' : 'Klik untuk memverifikasi' }}">
                                    <span class="sr-only">Toggle verification status</span>
                                    <span aria-hidden="true" 
                                          class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-lg ring-0 transition duration-300 ease-in-out {{ $biodata->is_verified ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                </button>
                                
                                <span class="text-sm font-medium {{ $biodata->is_verified ? 'text-success-600 dark:text-success-400' : 'text-gray-600 dark:text-gray-400' }}">
                                    {{ $biodata->is_verified ? 'Terverifikasi' : 'Pending' }}
                                </span>
                            </div>
                        </td>
                        @endif
                        <td class="whitespace-nowrap px-6 py-4">
                            <div class="text-sm text-gray-900 dark:text-white">{{ $biodata->created_at->format('d M Y') }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $biodata->created_at->format('H:i') }}</div>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.biodata.show', $biodata->id) }}" 
                                   class="inline-flex items-center gap-1.5 rounded-lg bg-blue-light-50 px-3 py-1.5 text-xs font-medium text-blue-light-700 transition-colors hover:bg-blue-light-100 dark:bg-blue-light-500/15 dark:text-blue-light-400 dark:hover:bg-blue-light-500/25">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    Detail
                                </a>
                                @if(!in_array(auth()->user()->role, ['admin_akademik', 'admin_farmasi']))
                                <a href="{{ route('admin.biodata.edit', $biodata->id) }}" 
                                   class="inline-flex items-center gap-1.5 rounded-lg bg-warning-50 px-3 py-1.5 text-xs font-medium text-warning-700 transition-colors hover:bg-warning-100 dark:bg-warning-500/15 dark:text-warning-400 dark:hover:bg-warning-500/25">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Edit
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    Belum ada data biodata
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($biodatas->hasPages())
        <div class="border-t border-gray-200 px-6 py-4 dark:border-gray-700">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-sm text-gray-600 dark:text-gray-300">
                    Menampilkan {{ $biodatas->firstItem() ?? 0 }} - {{ $biodatas->lastItem() ?? 0 }} dari {{ $biodatas->total() }} hasil
                </div>
                <div class="flex gap-2">
                    {{ $biodatas->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const rows = document.querySelectorAll('#biodataTableBody tr');
    const totalCount = document.getElementById('totalCount');

    // Auto focus pada input search
    if (searchInput) {
        searchInput.focus();
    }

    // Fungsi untuk update counter
    function updateCounter(visible) {
        if (totalCount) {
            totalCount.textContent = visible;
        }
    }

    // Event listener untuk input search
    searchInput.addEventListener('keyup', function() {
        const searchValue = this.value.toLowerCase();
        let visibleRows = 0;

        rows.forEach(row => {
            const nama = row.cells[1]?.textContent.toLowerCase() || '';
            const sekolah = row.cells[2]?.textContent.toLowerCase() || '';
            const jurusan = row.cells[3]?.textContent.toLowerCase() || '';
            const email = row.cells[4]?.textContent.toLowerCase() || '';

            if (nama.includes(searchValue) || sekolah.includes(searchValue) || jurusan.includes(searchValue) || email.includes(searchValue)) {
                row.style.display = '';
                visibleRows++;
            } else {
                row.style.display = 'none';
            }
        });

        updateCounter(visibleRows);
    });

    // Toggle status verifikasi
    const toggleButtons = document.querySelectorAll('.status-toggle');
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const biodataId = this.getAttribute('data-id');
            const currentStatus = this.getAttribute('data-status') === '1';
            const newStatus = !currentStatus;
            
            // Update UI immediately for better UX
            this.setAttribute('data-status', newStatus ? '1' : '0');
            this.setAttribute('aria-checked', newStatus ? 'true' : 'false');
            
            const toggleCircle = this.querySelector('span');
            const statusText = this.nextElementSibling;
            
            if (newStatus) {
                this.classList.remove('bg-gray-300');
                this.classList.add('bg-success-500');
                toggleCircle.classList.add('translate-x-5');
                statusText.textContent = 'Terverifikasi';
                statusText.classList.remove('text-gray-600', 'dark:text-gray-400');
                statusText.classList.add('text-success-600', 'dark:text-success-400');
            } else {
                this.classList.remove('bg-success-500');
                this.classList.add('bg-gray-300');
                toggleCircle.classList.remove('translate-x-5');
                statusText.textContent = 'Pending';
                statusText.classList.remove('text-success-600', 'dark:text-success-400');
                statusText.classList.add('text-gray-600', 'dark:text-gray-400');
            }

            // Send AJAX request to update status
            updateStatus(biodataId, newStatus);
        });
    });

    function updateStatus(biodataId, isVerified) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        fetch(`/admin/biodata/${biodataId}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                is_verified: isVerified
            })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                // Revert UI changes if failed
                showNotification(data.message || 'Gagal mengupdate status', 'error');
                location.reload(); // Reload to sync with server state
            } else {
                showNotification(data.message, 'success');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Terjadi kesalahan saat mengupdate status', 'error');
            location.reload(); // Reload to sync with server state
        });
    }

    function showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `fixed top-5 right-5 z-99999 p-4 rounded-lg shadow-theme-sm text-white max-w-sm ${
            type === 'success' ? 'bg-success-500' : 
            type === 'error' ? 'bg-error-500' : 
            'bg-brand-500'
        }`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Remove notification after 3 seconds
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
});
</script>
<script src="{{ asset('assets/js/bundle.js') }}"></script>