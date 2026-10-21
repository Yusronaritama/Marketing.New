@extends('layouts.admin.app')

@section('title', 'Kelola Group Gelombang')

@push('styles')
<style>
    .sortable-handle {
        cursor: move;
        cursor: grab;
        cursor: -moz-grab;
        cursor: -webkit-grab;
    }
    
    .sortable-handle:active {
        cursor: grabbing;
        cursor: -moz-grabbing;
        cursor: -webkit-grabbing;
    }
    
    .sortable-ghost {
        opacity: 0.4;
        background: var(--color-gray-100);
    }
    
    .dark .sortable-ghost {
        background: var(--color-gray-800);
    }
    
    .badge-tipe {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
        white-space: nowrap;
    }
    
    .badge-whatsapp {
        background-color: #dcfce7;
        color: #166534;
    }
    
    .dark .badge-whatsapp {
        background-color: rgba(22, 163, 74, 0.15);
        color: #4ade80;
    }
    
    .badge-telegram {
        background-color: #dbeafe;
        color: #1e40af;
    }
    
    .dark .badge-telegram {
        background-color: rgba(59, 130, 246, 0.15);
        color: #60a5fa;
    }
</style>
@endpush

@section('content')
<div class="mx-auto max-w-(--breakpoint-2xl)">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-title-md font-semibold text-black dark:text-white">
                    Kelola Group Gelombang
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Kelola link group WhatsApp/Telegram untuk setiap gelombang
                </p>
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                <form action="{{ route('admin.group-gelombang.bulk-create') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" 
                            class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-dark px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 transition hover:bg-gray-50 dark:hover:bg-white/5 focus:outline-hidden">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Buat 10 Gelombang
                    </button>
                </form>
                
                <a href="{{ route('admin.group-gelombang.create') }}" 
                   class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600 focus:outline-hidden">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Gelombang
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4 mb-6">
        <!-- Total Gelombang -->
        <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-dark p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">
                        Total Gelombang
                    </p>
                    <h4 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $stats['total'] }}
                    </h4>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-blue-500/10 dark:bg-blue-500/20">
                    <svg class="h-7 w-7 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Aktif -->
        <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-dark p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">
                        Aktif
                    </p>
                    <h4 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $stats['active'] }}
                    </h4>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-success-500/10 dark:bg-success-500/20">
                    <svg class="h-7 w-7 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- WhatsApp -->
        <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-dark p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">
                        WhatsApp
                    </p>
                    <h4 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $stats['whatsapp'] }}
                    </h4>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-success-500/10 dark:bg-success-500/20">
                    <svg class="h-7 w-7 text-success-600 dark:text-success-400" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Telegram -->
        <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-dark p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">
                        Telegram
                    </p>
                    <h4 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $stats['telegram'] }}
                    </h4>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-blue-light-500/10 dark:bg-blue-light-500/20">
                    <svg class="h-7 w-7 text-blue-light-500" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-dark shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full" id="sortable-table">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-white/[0.03]">
                        <th class="px-6 py-4 text-left">
                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                                #
                            </span>
                        </th>
                        <th class="px-6 py-4 text-left">
                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                                Gelombang
                            </span>
                        </th>
                        <th class="px-6 py-4 text-left">
                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                                Nama Gelombang
                            </span>
                        </th>
                        <th class="px-6 py-4 text-left">
                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                                Tipe Group
                            </span>
                        </th>
                        <th class="px-6 py-4 text-left">
                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                                Link Group
                            </span>
                        </th>
                        <th class="px-6 py-4 text-center">
                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                                Status
                            </span>
                        </th>
                        <th class="px-6 py-4 text-center">
                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                                Urutan
                            </span>
                        </th>
                        <th class="px-6 py-4 text-right">
                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                                Aksi
                            </span>
                        </th>
                    </tr>
                </thead>
                <tbody class="sortable-tbody divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse($gelombangs as $gelombang)
                    <tr class="group hover:bg-gray-50 dark:hover:bg-white/[0.03] transition-colors" data-id="{{ $gelombang->id }}">
                        <td class="px-6 py-4">
                            <div class="sortable-handle text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 transition-colors">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                                </svg>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-brand-500/10 dark:bg-brand-500/20">
                                <span class="text-lg font-bold text-brand-600 dark:text-brand-400">
                                    {{ $gelombang->gelombang }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ $gelombang->nama_gelombang }}
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5 line-clamp-1">
                                    {{ $gelombang->deskripsi ?: 'Tidak ada deskripsi' }}
                                </p>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($gelombang->tipe_group == 'whatsapp')
                                <span class="badge-tipe badge-whatsapp">
                                    <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                    </svg>
                                    WhatsApp
                                </span>
                            @else
                                <span class="badge-tipe badge-telegram">
                                    <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                                    </svg>
                                    Telegram
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ $gelombang->link_group }}" 
                               target="_blank"
                               class="inline-flex items-center gap-1.5 text-sm text-brand-600 dark:text-brand-400 hover:text-brand-700 dark:hover:text-brand-300 transition-colors">
                                <span class="truncate max-w-[200px]">
                                    {{ $gelombang->short_link }}
                                </span>
                                <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </a>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <label class="relative inline-flex items-center cursor-pointer active-toggle" 
                                   data-id="{{ $gelombang->id }}">
                                <input type="checkbox" 
                                       class="sr-only peer" 
                                       {{ $gelombang->is_active ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 rounded-full peer peer-focus:ring-4 peer-focus:ring-brand-500/20 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-500"></div>
                            </label>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-gray-100 dark:bg-gray-800 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                {{ $gelombang->urutan }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.group-gelombang.show', $gelombang->id) }}" 
                                   class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300 transition-colors"
                                   title="Lihat detail">
                                    <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <a href="{{ route('admin.group-gelombang.edit', $gelombang->id) }}" 
                                   class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300 transition-colors"
                                   title="Edit">
                                    <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <form action="{{ route('admin.group-gelombang.destroy', $gelombang->id) }}" 
                                      method="POST" 
                                      class="inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" 
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 hover:bg-error-50 hover:text-error-600 dark:text-gray-400 dark:hover:bg-error-500/10 dark:hover:text-error-400 transition-colors delete-btn"
                                            title="Hapus">
                                        <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-16">
                            <div class="flex flex-col items-center justify-center text-center">
                                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 mb-4">
                                    <svg class="h-10 w-10 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                    Belum ada group gelombang
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                    Mulai dengan membuat gelombang pertama Anda
                                </p>
                                <div class="flex flex-wrap items-center justify-center gap-2">
                                    <form action="{{ route('admin.group-gelombang.bulk-create') }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300 transition-colors">
                                            Buat 10 gelombang default
                                        </button>
                                    </form>
                                    <span class="text-gray-400">atau</span>
                                    <a href="{{ route('admin.group-gelombang.create') }}" 
                                       class="text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300 transition-colors">
                                        Tambah gelombang manual
                                    </a>
                                </div>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Sortable
    const sortableTbody = document.querySelector('.sortable-tbody');
    if (sortableTbody && sortableTbody.children.length > 0) {
        new Sortable(sortableTbody, {
            handle: '.sortable-handle',
            animation: 150,
            ghostClass: 'sortable-ghost',
            onEnd: function(evt) {
                updateOrder();
            }
        });
    }

    // Function to update order
    function updateOrder() {
        const rows = document.querySelectorAll('.sortable-tbody tr[data-id]');
        const order = Array.from(rows).map(row => row.dataset.id);
        
        fetch('/admin/group-gelombang/update-order', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ order: order })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Urutan berhasil diperbarui', 'success');
                
                // Update order numbers in the table
                rows.forEach((row, index) => {
                    const orderCell = row.querySelector('td:nth-last-child(2) span');
                    if (orderCell) {
                        orderCell.textContent = index + 1;
                    }
                });
            } else {
                showToast('Gagal memperbarui urutan', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Terjadi kesalahan saat memperbarui urutan', 'error');
        });
    }

    // Toggle active status
    document.querySelectorAll('.active-toggle').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const gelombangId = this.dataset.id;
            const checkbox = this.querySelector('input[type="checkbox"]');
            const currentState = checkbox.checked;
            
            fetch(`/admin/group-gelombang/${gelombangId}/toggle-active`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    checkbox.checked = !currentState;
                    showToast(
                        checkbox.checked ? 'Gelombang diaktifkan' : 'Gelombang dinonaktifkan', 
                        'success'
                    );
                } else {
                    showToast('Gagal mengubah status', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
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
                text: "Group gelombang akan dihapus secara permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                customClass: {
                    popup: 'rounded-xl',
                    confirmButton: 'rounded-lg px-5 py-2.5 font-medium',
                    cancelButton: 'rounded-lg px-5 py-2.5 font-medium'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
    
    // Fungsi toast notification
    function showToast(message, type = 'success') {
        const bgColor = type === 'success' 
            ? 'bg-success-500 dark:bg-success-600' 
            : 'bg-error-500 dark:bg-error-600';
        
        const icon = type === 'success'
            ? '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
            : '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
        
        const toast = document.createElement('div');
        toast.className = `fixed top-20 right-4 z-50 flex items-center gap-3 ${bgColor} text-white px-4 py-3 rounded-lg shadow-lg transition-all duration-300 transform translate-x-0`;
        toast.innerHTML = `
            ${icon}
            <span class="font-medium">${message}</span>
        `;
        
        document.body.appendChild(toast);
        
        // Animate in
        setTimeout(() => {
            toast.style.transform = 'translateX(0)';
        }, 10);
        
        // Remove after 3 seconds
        setTimeout(() => {
            toast.style.transform = 'translateX(400px)';
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 3000);
    }
    
    // Auto show success/error messages from session
    @if(session('success'))
        showToast('{{ session("success") }}', 'success');
    @endif
    
    @if(session('error'))
        showToast('{{ session("error") }}', 'error');
    @endif
});
</script>
@endpush