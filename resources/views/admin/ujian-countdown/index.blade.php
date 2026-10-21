@extends('layouts.admin.app')

@section('title', 'Pengaturan Countdown Ujian')

@push('styles')
<style>
    .countdown-badge {
        padding: 6px 14px;
        border-radius: 24px;
        font-size: 13px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    
    .badge-upcoming {
        background-color: #dbeafe;
        color: #1e40af;
    }
    
    .badge-ongoing {
        background-color: #d1fae5;
        color: #065f46;
    }
    
    .badge-finished {
        background-color: #f3f4f6;
        color: #6b7280;
    }
    
    .dark .badge-upcoming {
        background-color: rgba(59, 130, 246, 0.15);
        color: #60a5fa;
    }
    
    .dark .badge-ongoing {
        background-color: rgba(16, 185, 106, 0.15);
        color: #34d399;
    }
    
    .dark .badge-finished {
        background-color: rgba(107, 114, 128, 0.15);
        color: #9ca3af;
    }
    
    .active-toggle {
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .info-card {
        transition: all 0.3s ease;
    }
    
    .info-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    .table-row {
        transition: background-color 0.2s ease;
    }
    
    .table-row:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }
    
    .dark .table-row:hover {
        background-color: rgba(255, 255, 255, 0.03);
    }
    
    .action-button {
        transition: all 0.2s ease;
        padding: 8px;
        border-radius: 6px;
    }
    
    .action-button:hover {
        background-color: rgba(0, 0, 0, 0.05);
    }
    
    .dark .action-button:hover {
        background-color: rgba(255, 255, 255, 0.05);
    }
    
    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.5;
        }
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                    Pengaturan Countdown Ujian
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Kelola dan monitor waktu pelaksanaan ujian secara terpusat
                </p>
            </div>
            
            <a href="{{ route('admin.ujian-countdown.create') }}" 
               class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white hover:bg-brand-600 transition-colors shadow-sm">
                <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Countdown
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Card Akan Datang -->
        <div class="info-card rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Akan Datang</p>
                    <h4 class="text-3xl font-bold text-gray-900 dark:text-white">
                        {{ $countdowns->where('status', 'upcoming')->count() }}
                    </h4>
                </div>
                <div class="rounded-full bg-blue-100 p-4 dark:bg-blue-900/30">
                    <svg class="h-8 w-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Card Berlangsung -->
        <div class="info-card rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Berlangsung</p>
                    <h4 class="text-3xl font-bold text-gray-900 dark:text-white">
                        {{ $countdowns->where('status', 'ongoing')->count() }}
                    </h4>
                </div>
                <div class="rounded-full bg-green-100 p-4 dark:bg-green-900/30">
                    <svg class="h-8 w-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Card Selesai -->
        <div class="info-card rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Selesai</p>
                    <h4 class="text-3xl font-bold text-gray-900 dark:text-white">
                        {{ $countdowns->where('status', 'finished')->count() }}
                    </h4>
                </div>
                <div class="rounded-full bg-gray-100 p-4 dark:bg-gray-700/30">
                    <svg class="h-8 w-8 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            Nama Ujian
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            Waktu Mulai
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            Waktu Berakhir
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            Durasi
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            Aktif
                        </th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($countdowns as $countdown)
                    <tr class="table-row">
                        <td class="px-6 py-5">
                            <div>
                                <h5 class="font-semibold text-gray-900 dark:text-white mb-1">
                                    {{ $countdown->ujian_name }}
                                </h5>
                                <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-1">
                                    {{ $countdown->description ?: 'Tidak ada deskripsi' }}
                                </p>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $countdown->start_date->format('d M Y') }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $countdown->start_date->format('H:i') }} WIB
                                </p>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $countdown->end_date->format('d M Y') }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $countdown->end_date->format('H:i') }} WIB
                                </p>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            @php
                                $duration = $countdown->start_date->diff($countdown->end_date);
                                $days = $duration->d;
                                $hours = $duration->h;
                                $minutes = $duration->i;
                            @endphp
                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                @if($days > 0)
                                    {{ $days }} hari
                                @endif
                                @if($hours > 0)
                                    {{ $hours }} jam
                                @endif
                                @if($minutes > 0)
                                    {{ $minutes }} menit
                                @endif
                                @if($days == 0 && $hours == 0 && $minutes == 0)
                                    0 menit
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            @php
                                $badgeClass = [
                                    'upcoming' => 'badge-upcoming',
                                    'ongoing' => 'badge-ongoing',
                                    'finished' => 'badge-finished'
                                ][$countdown->status] ?? 'badge-upcoming';
                                
                                $statusText = [
                                    'upcoming' => 'Akan Datang',
                                    'ongoing' => 'Berlangsung',
                                    'finished' => 'Selesai'
                                ][$countdown->status] ?? 'Akan Datang';
                            @endphp
                            <span class="countdown-badge {{ $badgeClass }}">
                                @if($countdown->status == 'ongoing')
                                    <span class="status-dot bg-green-500"></span>
                                @endif
                                {{ $statusText }}
                            </span>
                        </td>
                        <td class="px-6 py-5">
                            <form id="toggle-form-{{ $countdown->id }}">
                                @csrf
                                <label class="relative inline-flex items-center cursor-pointer active-toggle" 
                                       data-id="{{ $countdown->id }}">
                                    <input type="checkbox" 
                                           class="sr-only peer" 
                                           {{ $countdown->is_active ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-300 dark:peer-focus:ring-brand-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-brand-500"></div>
                                </label>
                            </form>
                        </td>
                        <td class="px-6 py-5">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.ujian-countdown.edit', $countdown->id) }}" 
                                   class="action-button hover:text-brand-500 dark:hover:text-brand-400"
                                   title="Edit">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <form action="{{ route('admin.ujian-countdown.destroy', $countdown->id) }}" 
                                      method="POST" 
                                      class="inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" 
                                            class="action-button hover:text-red-500 dark:hover:text-red-400 delete-btn"
                                            title="Hapus">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="rounded-full bg-gray-100 dark:bg-gray-800 p-6 mb-4">
                                    <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <p class="text-gray-900 dark:text-white font-medium mb-2">
                                    Belum ada countdown ujian
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                    Mulai dengan membuat countdown ujian pertama Anda
                                </p>
                                <a href="{{ route('admin.ujian-countdown.create') }}" 
                                   class="inline-flex items-center text-brand-500 hover:text-brand-600 font-medium">
                                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Buat countdown pertama
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle active status
    document.querySelectorAll('.active-toggle').forEach(toggle => {
        toggle.addEventListener('click', function() {
            const countdownId = this.dataset.id;
            const checkbox = this.querySelector('input');
            const isChecked = checkbox.checked;
            
            fetch(`/admin/ujian-countdown/${countdownId}/toggle-active`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    _method: 'POST'
                })
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showToast('Status berhasil diperbarui', 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    checkbox.checked = isChecked;
                    showToast('Gagal memperbarui status', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                checkbox.checked = isChecked;
                showToast('Terjadi kesalahan', 'error');
            });
        });
    });
    
    // Konfirmasi hapus
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const form = this.closest('form');
            
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Countdown ujian akan dihapus secara permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
    
    // Fungsi toast notification
    function showToast(message, type = 'success') {
        const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
        const icon = type === 'success' 
            ? '<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>'
            : '<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>';
        
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 z-50 flex items-center gap-3 ${bgColor} text-white px-6 py-4 rounded-lg shadow-lg transform transition-all duration-300 ease-in-out`;
        toast.style.animation = 'slideIn 0.3s ease-out';
        toast.innerHTML = `${icon}<span class="font-medium">${message}</span>`;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease-in';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
});

// Animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
</script>