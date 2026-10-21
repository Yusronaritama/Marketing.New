@extends('layouts.admin.app')

@section('title', 'Kelola Program Studi')

@section('content')

<style>
    .cyber-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
    }
    
    .dark .cyber-card {
        background: #1f2937;
        border: 1px solid #374151;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
    }
    
    .cyber-glow-side {
        position: relative;
    }
    
    .pendaftar-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .kuota-tersedia-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .badge-info {
        background-color: #dbeafe;
        color: #1e40af;
    }

    .badge-warning {
        background-color: #fef3c7;
        color: #92400e;
    }

    .badge-danger {
        background-color: #fee2e2;
        color: #991b1b;
    }

    .dark .badge-info {
        background-color: rgba(59, 130, 246, 0.2);
        color: #93c5fd;
    }

    .dark .badge-warning {
        background-color: rgba(245, 158, 11, 0.2);
        color: #fcd34d;
    }

    .dark .badge-danger {
        background-color: rgba(239, 68, 68, 0.2);
        color: #fca5a5;
    }

    .cyber-glow-side::before {
        content: '';
        position: absolute;
        top: 0;
        left: -2px;
        width: 3px;
        height: 100%;
        background: linear-gradient(180deg, #4f46e5, #818cf8);
        border-radius: 2px;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .cyber-glow-side:hover::before {
        opacity: 1;
    }
    
    .status-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
        padding: 0.25rem 0.75rem;
    }
    
    .send-kuota-btn {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.875rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.2s ease;
    }

    .send-kuota-btn:hover {
        background: linear-gradient(135deg, #059669, #047857);
        transform: translateY(-1px);
    }

    .send-kuota-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
    }

    .modal-content {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        padding: 24px;
        border-radius: 12px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        z-index: 1001;
        min-width: 500px;
        max-width: 90vw;
    }

    .dark .modal-content {
        background: #1f2937;
        color: white;
    }

    .message-preview {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        padding: 12px;
        margin-top: 12px;
        font-size: 0.875rem;
        white-space: pre-wrap;
    }

    .dark .message-preview {
        background: #374151;
        border-color: #4b5563;
        color: #d1d5db;
    }
    
    .badge-success {
        background-color: #dcfce7;
        color: #166534;
    }
    
    .badge-warning {
        background-color: #fef3c7;
        color: #92400e;
    }
    
    .badge-danger {
        background-color: #fef2f2;
        color: #991b1b;
    }
    
    .dark .badge-success {
        background-color: rgba(21, 128, 61, 0.2);
        color: #4ade80;
    }
    
    .dark .badge-warning {
        background-color: rgba(146, 64, 14, 0.2);
        color: #fbbf24;
    }
    
    .dark .badge-danger {
        background-color: rgba(185, 28, 28, 0.2);
        color: #f87171;
    }
    
    .passing-grade-badge {
        font-size: 0.7rem;
        padding: 0.1rem 0.4rem;
        border-radius: 0.25rem;
        background: rgba(79, 70, 229, 0.1);
        color: #4f46e5;
    }
    
    .dark .passing-grade-badge {
        background: rgba(99, 102, 241, 0.2);
        color: #818cf8;
    }
    
    .kuota-badge {
        font-size: 0.7rem;
        padding: 0.1rem 0.4rem;
        border-radius: 0.25rem;
        background: rgba(16, 185, 129, 0.1);
        color: #047857;
    }
    
    .dark .kuota-badge {
        background: rgba(16, 185, 129, 0.2);
        color: #10b981;
    }
</style>

<div class="grid grid-cols-12 gap-4 md:gap-6">
    <div class="col-span-12">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-5">
            <!-- Total Prodi Card -->
            <div class="cyber-card cyber-glow-side rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $prodi->total() }}</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Total Program Studi</p>
                    </div>
                    <div class="p-3 rounded-lg bg-blue-100 dark:bg-blue-900/30">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Active Prodi Card -->
            <div class="cyber-card cyber-glow-side rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $prodi->where('status', 'active')->count() }}</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Program Studi Aktif</p>
                    </div>
                    <div class="p-3 rounded-lg bg-green-100 dark:bg-green-900/30">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Inactive Prodi Card -->
            <div class="cyber-card cyber-glow-side rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $prodi->where('status', 'inactive')->count() }}</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Program Studi Non-Aktif</p>
                    </div>
                    <div class="p-3 rounded-lg bg-yellow-100 dark:bg-yellow-900/30">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Kuota Card -->
            <div class="cyber-card cyber-glow-side rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($prodi->sum('kuota')) }}</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Total Kuota</p>
                    </div>
                    <div class="p-3 rounded-lg bg-purple-100 dark:bg-purple-900/30">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Pendaftar Card -->
            <div class="cyber-card cyber-glow-side rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($prodi->sum('jumlah_pendaftar')) }}</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Total Pendaftar</p>
                    </div>
                    <div class="p-3 rounded-lg bg-indigo-100 dark:bg-indigo-900/30">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="cyber-card cyber-glow-side rounded-xl px-4 pb-3 pt-4 sm:px-6">
            <div class="flex flex-col gap-4 mb-6 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Kelola Program Studi
                    </h3>
                    <p class="mt-1 text-gray-600 dark:text-gray-400 text-theme-sm">
                        Kelola data program studi, passing grade, dan kuota
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Filter Status -->
                    <select id="statusFilter" onchange="filterTable()" class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-theme-sm text-gray-900 dark:text-white focus:border-blue-500 dark:focus:border-blue-500">
                        <option value="all" {{ $status == 'all' ? 'selected' : '' }}>Semua Status</option>
                        <option value="active" {{ $status == 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ $status == 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                    </select>

                    <!-- Search -->
                    <div class="relative">
                        <input type="text" id="searchInput" placeholder="Cari kode atau nama prodi..." 
                               value="{{ $search }}"
                               class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 pl-10 pr-4 py-2.5 text-theme-sm text-gray-900 dark:text-white focus:border-blue-500 dark:focus:border-blue-500 w-64">
                        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    <!-- Add Button -->
                    <a href="{{ route('admin.prodi.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-theme-sm font-medium dark:text-white hover:bg-blue-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Prodi
                    </a>
                </div>
            </div>

            <!-- Table -->
            <div class="max-w-full overflow-x-auto custom-scrollbar">
                <table class="w-full table-auto">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800 text-left">
                            <th class="px-4 py-3 font-semibold text-gray-900 dark:text-white text-theme-sm min-w-[100px]">
                                Kode
                            </th>
                            <th class="px-4 py-3 font-semibold text-gray-900 dark:text-white text-theme-sm min-w-[200px]">
                                Nama Program Studi
                            </th>
                            <th class="px-4 py-3 font-semibold text-gray-900 dark:text-white text-theme-sm min-w-[120px]">
                                Passing Grade
                            </th>
                            <th class="px-4 py-3 font-semibold text-gray-900 dark:text-white text-theme-sm min-w-[100px]">
                                Kuota Total
                            </th>
                            <th class="px-4 py-3 font-semibold text-gray-900 dark:text-white text-theme-sm min-w-[120px]">
                                Sudah Daftar
                            </th>
                            <th class="px-4 py-3 font-semibold text-gray-900 dark:text-white text-theme-sm min-w-[120px]">
                                Kuota Tersedia
                            </th>
                            <th class="px-4 py-3 font-semibold text-gray-900 dark:text-white text-theme-sm min-w-[100px]">
                                Status
                            </th>
                            <th class="px-4 py-3 font-semibold text-gray-900 dark:text-white text-theme-sm min-w-[150px]">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        @forelse($prodi as $item)
                        <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-4 py-4">
                                <p class="font-mono font-medium text-gray-900 dark:text-white">{{ $item->kode_prodi }}</p>
                            </td>
                            <td class="px-4 py-4">
                                <p class="text-gray-900 dark:text-white font-medium">{{ $item->nama_prodi }}</p>
                            </td>
                            <td class="px-4 py-4">
                                <span class="passing-grade-badge">
                                    {{ number_format($item->passing_grade, 2) }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <span class="kuota-badge">
                                    {{ number_format($item->kuota) }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <span class="pendaftar-badge {{ $item->jumlah_pendaftar > $item->kuota ? 'badge-danger' : 'badge-info' }}">
                                    {{ number_format($item->jumlah_pendaftar) }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <span class="kuota-tersedia-badge {{ $item->kuota_tersedia == 0 ? 'badge-danger' : ($item->kuota_tersedia < 10 ? 'badge-warning' : 'badge-success') }}">
                                    {{ number_format($item->kuota_tersedia) }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                @if($item->status === 'active')
                                <span class="status-badge badge-success">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    AKTIF
                                </span>
                                @else
                                <span class="status-badge badge-warning">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    NON-AKTIF
                                </span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex flex-col gap-2">
                                    <button onclick="openSendKuotaModal({{ $item->id }}, '{{ $item->nama_prodi }}')" 
                                            class="send-kuota-btn">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                        </svg>
                                        Kirim Info Kuota
                                    </button>
                                    
                                    <button onclick="openUserSelectionModal({{ $item->id }}, '{{ $item->nama_prodi }}')" 
                                            class="send-kuota-btn" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8);">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        Pilih User
                                    </button>
                                    
                                    <!-- Edit Button -->
                                    <a href="{{ route('admin.prodi.edit', $item->id) }}" 
                                       class="send-kuota-btn" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Edit
                                    </a>
                                    
                                    <!-- Delete Button -->
                                    <button onclick="deleteProdi({{ $item->id }})" 
                                            class="send-kuota-btn" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                Tidak ada data program studi yang ditemukan
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Modals -->
            <div id="sendKuotaModal" class="modal-overlay">
                <div class="modal-content">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Kirim Informasi Kuota</h3>
                        <button onclick="closeSendKuotaModal()" class="text-gray-500 hover:text-gray-700">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    
                    <div class="mb-4">
                        <p><strong>Program Studi:</strong> <span id="modalProdiName"></span></p>
                    </div>

                    <form id="sendKuotaForm">
                        @csrf
                        <input type="hidden" id="prodiId" name="prodi_id">
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-2">Tipe Pesan</label>
                            <select id="messageType" name="message_type" class="w-full border border-gray-300 rounded-lg px-3 py-2" onchange="toggleCustomMessage()">
                                <option value="auto">Pesan Otomatis</option>
                                <option value="custom">Pesan Kustom</option>
                            </select>
                        </div>

                        <div id="customMessageContainer" class="mb-4 hidden">
                            <label class="block text-sm font-medium mb-2">Pesan Kustom</label>
                            <textarea id="customMessage" name="custom_message" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Tulis pesan kustom di sini..."></textarea>
                            <p class="text-xs text-gray-500 mt-1">Maksimal 1000 karakter</p>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-2">Pratinjau Pesan</label>
                            <div id="messagePreview" class="message-preview">
                                Pesan akan muncul di sini...
                            </div>
                        </div>

                        <div class="flex justify-end gap-3">
                            <button type="button" onclick="closeSendKuotaModal()" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                                Batal
                            </button>
                            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                                Kirim ke Semua User
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div id="userSelectionModal" class="modal-overlay">
                <div class="modal-content">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Kirim ke User Tertentu</h3>
                        <button onclick="closeUserSelectionModal()" class="text-gray-500 hover:text-gray-700">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    
                    <div class="mb-4">
                        <p><strong>Program Studi:</strong> <span id="userModalProdiName"></span></p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2">Pilih User</label>
                        <select id="userSelect" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            <option value="">Loading users...</option>
                        </select>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeUserSelectionModal()" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="button" onclick="sendToSelectedUser()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                            Kirim ke User Terpilih
                        </button>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Menampilkan {{ $prodi->firstItem() ?? 0 }} - {{ $prodi->lastItem() ?? 0 }} dari {{ $prodi->total() }} data
                </p>
                <div class="flex gap-2">
                    {{ $prodi->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function openSendKuotaModal(prodiId, prodiName) {
        document.getElementById('sendKuotaModal').style.display = 'block';
        document.getElementById('prodiId').value = prodiId;
        document.getElementById('modalProdiName').textContent = prodiName;
        updateMessagePreview();
    }

    function closeSendKuotaModal() {
        document.getElementById('sendKuotaModal').style.display = 'none';
    }

    function openUserSelectionModal(prodiId, prodiName) {
        document.getElementById('userSelectionModal').style.display = 'block';
        document.getElementById('userModalProdiName').textContent = prodiName;
        document.getElementById('userSelect').setAttribute('data-prodi-id', prodiId);
        loadUsers();
    }

    function closeUserSelectionModal() {
        document.getElementById('userSelectionModal').style.display = 'none';
    }

    // Toggle custom message field
    function toggleCustomMessage() {
        const messageType = document.getElementById('messageType').value;
        const customMessageContainer = document.getElementById('customMessageContainer');
        
        if (messageType === 'custom') {
            customMessageContainer.classList.remove('hidden');
        } else {
            customMessageContainer.classList.add('hidden');
        }
        updateMessagePreview();
    }

    // Update message preview
    function updateMessagePreview() {
        const messageType = document.getElementById('messageType').value;
        const customMessage = document.getElementById('customMessage').value;
        const prodiName = document.getElementById('modalProdiName').textContent;
        
        let preview = '';
        
        if (messageType === 'custom' && customMessage) {
            preview = customMessage;
        } else {
            preview = `📢 INFORMASI KUOTA PROGRAM STUDI\n\n🎓 ${prodiName}\n\n📊 Passing Grade: [Nilai]%\n👥 Kuota Total: [Jumlah] mahasiswa\n📈 Sudah Mendaftar: [Jumlah] mahasiswa\n✅ Kuota Tersedia: [Jumlah] mahasiswa\n\n📍 Status: ✅ AKTIF\n\nℹ️ Informasi lebih lanjut:\n🌐 Website: pmb.ubpkarawang.ac.id\n📞 Contact: admin@ubpkarawang.ac.id\n\n_Pesan ini dikirim otomatis oleh sistem PMB UBP Karawang_`;
        }
        
        document.getElementById('messagePreview').textContent = preview;
    }

    // Load users for selection
    function loadUsers() {
        fetch('{{ route("admin.prodi.usersWithPhone") }}')
            .then(response => response.json())
            .then(data => {
                const userSelect = document.getElementById('userSelect');
                userSelect.innerHTML = '';
                
                if (data.success && data.data.length > 0) {
                    data.data.forEach(user => {
                        const option = document.createElement('option');
                        option.value = user.id;
                        option.textContent = `${user.name} (${user.phone}) - ${user.email}`;
                        userSelect.appendChild(option);
                    });
                } else {
                    userSelect.innerHTML = '<option value="">Tidak ada user dengan nomor handphone</option>';
                }
            })
            .catch(error => {
                console.error('Error loading users:', error);
                document.getElementById('userSelect').innerHTML = '<option value="">Error loading users</option>';
            });
    }

    // Send to all users
    document.getElementById('sendKuotaForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const prodiId = document.getElementById('prodiId').value;
        const formData = new FormData(this);
        
        if (!confirm('Apakah Anda yakin ingin mengirim informasi kuota ke semua user?')) {
            return;
        }
        
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div> Mengirim...';
        
        fetch(`/admin/prodi/${prodiId}/send-kuota`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ ' + data.message + '\n\nBerhasil: ' + data.data.success_count + ' user\nGagal: ' + data.data.failed_count + ' user');
                closeSendKuotaModal();
            } else {
                alert('❌ ' + data.message);
            }
        })
        .catch(error => {
            alert('❌ Terjadi error: ' . error.message);
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Kirim ke Semua User';
        });
    });

    // Send to selected user
    function sendToSelectedUser() {
        const userSelect = document.getElementById('userSelect');
        const userId = userSelect.value;
        const prodiId = userSelect.getAttribute('data-prodi-id');
        
        if (!userId) {
            alert('Pilih user terlebih dahulu!');
            return;
        }
        
        if (!confirm('Kirim informasi kuota ke user ini?')) {
            return;
        }
        
        fetch(`/admin/prodi/${prodiId}/send-to-user/${userId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                message_type: 'auto'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ ' + data.message);
                closeUserSelectionModal();
            } else {
                alert('❌ ' + data.message);
            }
        })
        .catch(error => {
            alert('❌ Terjadi error: ' . error.message);
        });
    }

    // Event listeners for real-time preview
    document.getElementById('customMessage').addEventListener('input', updateMessagePreview);
    document.getElementById('messageType').addEventListener('change', updateMessagePreview);

    // Close modal when clicking outside
    document.getElementById('sendKuotaModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeSendKuotaModal();
        }
    });

    document.getElementById('userSelectionModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeUserSelectionModal();
        }
    });

    function filterTable() {
        const status = document.getElementById('statusFilter').value;
        const search = document.getElementById('searchInput').value;
        
        let url = '{{ route("admin.prodi.index") }}?';
        if (status && status !== 'all') {
            url += 'status=' + status + '&';
        }
        if (search) {
            url += 'search=' + encodeURIComponent(search);
        }
        
        window.location.href = url;
    }

    // Search functionality with debounce
    let searchTimeout;
    document.getElementById('searchInput').addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            filterTable();
        }, 500);
    });

    // Update status prodi
    function updateStatus(id, newStatus) {
        const statusText = newStatus === 'active' ? 'mengaktifkan' : 'menonaktifkan';
        
        if (!confirm(`Apakah Anda yakin ingin ${statusText} program studi ini?`)) {
            return;
        }
        
        fetch(`/admin/prodi/${id}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                status: newStatus
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Terjadi error: ' + error.message);
        });
    }

    // Delete prodi
    function deleteProdi(id) {
        if (!confirm('Apakah Anda yakin ingin menghapus program studi ini?')) {
            return;
        }
        
        if (!confirm('Data yang dihapus tidak dapat dikembalikan. Yakin要继续?')) {
            return;
        }
        
        fetch(`/admin/prodi/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (response.ok) {
                alert('Program studi berhasil dihapus!');
                window.location.reload();
            } else {
                alert('Error menghapus program studi');
            }
        })
        .catch(error => {
            alert('Terjadi error: ' + error.message);
        });
    }
</script>

@endsection