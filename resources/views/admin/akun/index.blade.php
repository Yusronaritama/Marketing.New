@extends('layouts.admin.app')

@section('title', 'Manajemen Akun')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Manajemen Akun</h1>
            <nav class="flex mt-2" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 text-sm">
                    <li class="inline-flex items-center">
                        <a href="https://pmb.ubpkarawang.ac.id/admin/dashboard" class="text-gray-500 hover:text-brand-500 dark:text-gray-400">
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mx-1 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">Semua Akun</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.akun.create') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-brand-500 rounded-lg hover:bg-brand-600 focus:ring-4 focus:ring-brand-300 dark:focus:ring-brand-800">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Akun Baru
        </a>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
    <div class="flex items-center p-4 mb-4 text-success-700 bg-success-50 rounded-lg dark:bg-success-900 dark:text-success-300" role="alert">
        <svg class="flex-shrink-0 w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
        </svg>
        <span>{{ session('success') }}</span>
        <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-success-50 text-success-500 rounded-lg focus:ring-2 focus:ring-success-400 p-1.5 hover:bg-success-200 dark:bg-success-900 dark:text-success-300 dark:hover:bg-success-800" data-dismiss-target="#alert-success" aria-label="Close">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
        </button>
    </div>
    @endif

    @if(session('error'))
    <div class="flex items-center p-4 mb-4 text-error-700 bg-error-50 rounded-lg dark:bg-error-900 dark:text-error-300" role="alert">
        <svg class="flex-shrink-0 w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
        </svg>
        <span>{{ session('error') }}</span>
        <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-error-50 text-error-500 rounded-lg focus:ring-2 focus:ring-error-400 p-1.5 hover:bg-error-200 dark:bg-error-900 dark:text-error-300 dark:hover:bg-error-800" data-dismiss-target="#alert-error" aria-label="Close">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
        </button>
    </div>
    @endif

    <!-- Statistics Cards -->
    <div class="flex gap-4 overflow-x-auto pb-3 snap-x snap-mandatory scrollbar-thin scrollbar-thumb-gray-400 dark:scrollbar-thumb-gray-600 scrollbar-track-transparent">
        <!-- Total Akun -->
        <div class="min-w-[220px] flex-shrink-0 p-5 bg-brand-500 rounded-2xl shadow-theme-md flex items-center justify-between text-white snap-start hover:scale-[1.02] transition-transform">
            <div class="flex items-center">
                <div class="flex items-center justify-center w-12 h-12 bg-white/20 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-2xl font-bold leading-none">{{ $users->total() }}</h3>
                    <p class="text-sm text-brand-100">Total Akun</p>
                </div>
            </div>
        </div>

        <!-- Admin -->
        <div class="min-w-[220px] flex-shrink-0 p-5 bg-success-500 rounded-2xl shadow-theme-md flex items-center justify-between text-white snap-start hover:scale-[1.02] transition-transform">
            <div class="flex items-center">
                <div class="flex items-center justify-center w-12 h-12 bg-white/20 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-2xl font-bold leading-none">{{ $users->where('role', 'admin')->count() }}</h3>
                    <p class="text-sm text-success-100">Admin</p>
                </div>
            </div>
        </div>

        <!-- Mahasiswa -->
        <div class="min-w-[220px] flex-shrink-0 p-5 bg-gray-700 rounded-2xl shadow-theme-md flex items-center justify-between text-white snap-start hover:scale-[1.02] transition-transform">
            <div class="flex items-center">
                <div class="flex items-center justify-center w-12 h-12 bg-white/20 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-2xl font-bold leading-none">{{ $users->where('role', 'mahasiswa_baru')->count() }}</h3>
                    <p class="text-sm text-theme-purple-100">Mahasiswa</p>
                </div>
            </div>
        </div>

        <!-- Dengan Telepon -->
        <div class="min-w-[220px] flex-shrink-0 p-5 bg-warning-500 rounded-2xl shadow-theme-md flex items-center justify-between text-white snap-start hover:scale-[1.02] transition-transform">
            <div class="flex items-center">
                <div class="flex items-center justify-center w-12 h-12 bg-white/20 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-2xl font-bold leading-none">{{ $users->whereNotNull('phone')->count() }}</h3>
                    <p class="text-sm text-warning-100">Dengan Telepon</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Card -->
    <div class="bg-white rounded-lg shadow-theme-md dark:bg-gray-800">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h5 class="text-xl font-semibold text-gray-900 dark:text-white">Daftar Semua Akun</h5>
        </div>
        <div class="p-6">
            <!-- Search and Filter Bar -->
            <div class="flex flex-col gap-4 mb-6 md:flex-row md:items-center md:justify-between">
                <div class="relative flex-1 max-w-md">
                    <input type="text" id="searchInput" class="w-full py-2 pl-10 pr-4 text-gray-700 bg-gray-50 border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-brand-500 dark:focus:border-brand-500" placeholder="Cari akun...">
                </div>
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" type="button" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:ring-4 focus:ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                        </svg>
                        Filter
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 z-10 mt-2 bg-white rounded-lg shadow-theme-md w-44 dark:bg-gray-700">
                        <ul class="py-1 text-sm text-gray-700 dark:text-gray-200">
                            <li><a href="#" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600">Semua Role</a></li>
                            <li><a href="#" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600">Admin</a></li>
                            <li><a href="#" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600">Mahasiswa</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Users Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Pengguna</th>
                            <th class="px-4 py-3">Kontak</th>
                            <th class="px-4 py-3">Role</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Tanggal Dibuat</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="px-4 py-3 text-gray-500">
                                {{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center">
                                    <div class="flex items-center justify-center w-10 h-10 mr-3 text-white rounded-full bg-brand-500">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <h6 class="font-medium text-gray-900 dark:text-white">{{ $user->name }}</h6>
                                        @if($user->id === auth()->id())
                                        <span class="inline-flex items-center text-xs text-success-600 dark:text-success-400">
                                            <span class="w-2 h-2 mr-1 bg-success-600 rounded-full"></span>
                                            Akun Anda
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="space-y-1 text-sm">
                                    <div class="flex items-center text-gray-600 dark:text-gray-300">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        {{ $user->email }}
                                    </div>
                                    @if($user->phone)
                                    <div class="flex items-center text-gray-600 dark:text-gray-300">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                        {{ $user->phone }}
                                    </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->role === 'admin' ? 'bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-300' : 'bg-blue-light-100 text-blue-light-800 dark:bg-blue-light-900 dark:text-blue-light-300' }}">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        @if($user->role === 'admin')
                                        <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        @else
                                        <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"></path>
                                        @endif
                                    </svg>
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if($user->gelombang)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-light-100 text-blue-light-800 dark:bg-blue-light-900 dark:text-blue-light-300">
                                    {{ $user->gelombang }}
                                </span>
                                @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                    Tidak Ada Gelombang
                                </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    <div>{{ $user->created_at->format('d M Y') }}</div>
                                    <small class="text-xs">{{ $user->created_at->format('H:i') }}</small>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.akun.edit', $user->id) }}" class="p-2 text-warning-600 transition rounded-lg hover:bg-warning-50 dark:hover:bg-warning-900/20" title="Edit Akun">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    @if($user->id !== auth()->id())
                                    <form action="{{ route('admin.akun.destroy', $user->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-error-600 transition rounded-lg hover:bg-error-50 dark:hover:bg-error-900/20" title="Hapus Akun" onclick="return confirm('Apakah Anda yakin ingin menghapus akun {{ $user->name }}?')">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                    @else
                                    <button class="p-2 text-gray-400 rounded-lg cursor-not-allowed" disabled title="Tidak dapat menghapus akun sendiri">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-16 h-16 mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    <h5 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">Tidak ada data akun</h5>
                                    <p class="mb-4 text-gray-500 dark:text-gray-400">Mulai dengan menambahkan akun baru.</p>
                                    <a href="{{ route('admin.akun.create') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-brand-500 rounded-lg hover:bg-brand-600 focus:ring-4 focus:ring-brand-300">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        Tambah Akun Baru
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($users->hasPages())
            <div class="flex flex-col items-center justify-between gap-4 pt-6 mt-6 border-t border-gray-200 dark:border-gray-700 md:flex-row">
                <div class="text-sm text-gray-700 dark:text-gray-400">
                    Menampilkan <span class="font-medium">{{ $users->firstItem() }}</span> - <span class="font-medium">{{ $users->lastItem() }}</span> 
                    dari <span class="font-medium">{{ $users->total() }}</span> akun
                </div>
                <div>
                    {{ $users->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('[role="alert"]');
        alerts.forEach(alert => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        });
    }, 5000);

    // Alert dismiss buttons
    document.querySelectorAll('[data-dismiss-target]').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-dismiss-target');
            const target = document.querySelector(targetId) || this.closest('[role="alert"]');
            if (target) {
                target.style.transition = 'opacity 0.3s';
                target.style.opacity = '0';
                setTimeout(() => target.remove(), 300);
            }
        });
    });

    // Search functionality
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const value = this.value.toLowerCase();
            const rows = document.querySelectorAll('table tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.indexOf(value) > -1) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
});
</script>
@endsection
<script src="{{ asset('assets/js/bundle.js') }}"></script>