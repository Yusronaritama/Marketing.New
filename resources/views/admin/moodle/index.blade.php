@extends('layouts.admin.app')

@section('title', 'Moodle Management')

@section('content')
<div class="grid grid-cols-1 gap-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-md font-bold text-gray-900 dark:text-white">
                Moodle Management
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                Kelola sinkronisasi nilai dan data pengguna Moodle
            </p>
        </div>
    </div>

    @if(session('success'))
    <div class="rounded-lg bg-success-50 p-4 text-sm text-success-700 dark:bg-success-500/15">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="rounded-lg bg-error-50 p-4 text-sm text-error-700 dark:bg-error-500/15">
        {{ session('error') }}
    </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center gap-4 mb-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-light-50 dark:bg-blue-light-500/15">
                    <svg class="fill-blue-light-500" width="24" height="24" viewBox="0 0 24 24">
                        <path d="M12 2L3 7L12 12L21 7L12 2Z" />
                        <path d="M3 17L12 22L21 17" />
                        <path d="M3 12L12 17L21 12" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Moodle Server</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Sync nilai dari Course ID: {{ env('MOODLE_DEFAULT_COURSE_ID', 384) }}</p>
                </div>
            </div>

            <div class="mt-6 space-y-3">
                <form action="{{ route('admin.moodle.sync') }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-success-500 px-4 py-3 text-sm font-medium text-white shadow-theme-xs hover:bg-success-600">
                        <svg class="fill-current" width="16" height="16" viewBox="0 0 20 20">
                            <path d="M10 3V1l-4 4 4 4V6c3.31 0 6 2.69 6 6 0 1.01-.25 1.97-.7 2.8l1.46 1.46C17.54 15.03 18 13.57 18 12c0-4.42-3.58-8-8-8zm-6 9c0-1.01.25-1.97.7-2.8L3.24 7.74C2.46 8.97 2 10.43 2 12c0 4.42 3.58 8 8 8v-2l4-4-4-4v3c-3.31 0-6-2.69-6-6z"/>
                        </svg>
                        Sinkronisasi Nilai Sekarang
                    </button>
                </form>

                <a href="{{ route('admin.moodle.list') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                    Lihat Users dari Moodle
                </a>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center gap-4 mb-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-50 dark:bg-brand-500/15">
                    <svg class="fill-brand-500" width="24" height="24" viewBox="0 0 24 24">
                        <path d="M4 7V17C4 18.6569 5.34315 20 7 20H17C18.6569 20 20 18.6569 20 17V7C20 5.34315 18.6569 4 17 4H7C5.34315 4 4 5.34315 4 7Z" stroke="currentColor" stroke-width="1.5" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Local Database</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Data nilai yang tersimpan</p>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('admin.moodle.local.index') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                    Buka Riwayat Nilai Lokal
                </a>
            </div>
        </div>
    </div>
</div>
@endsection