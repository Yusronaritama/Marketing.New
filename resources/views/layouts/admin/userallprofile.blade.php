@extends('layouts.admin.app')

@section('title', 'User All Profile')

@section('content')
<div class="p-6 bg-white rounded-2xl shadow-md dark:bg-gray-900">
    <h2 class="text-2xl font-semibold mb-6 text-gray-800 dark:text-white">📋 Daftar Semua Profil User</h2>

    {{-- Pesan sukses --}}
    @if(session('success'))
        <div class="mb-4 p-3 rounded bg-green-100 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    {{-- Tabel Data User --}}
    <div class="overflow-x-auto">
        <table class="min-w-full border border-gray-200 rounded-lg">
            <thead class="bg-gray-100 dark:bg-gray-800">
                <tr>
                    <th class="px-4 py-2 text-left text-gray-600 dark:text-gray-300">#</th>
                    <th class="px-4 py-2 text-left text-gray-600 dark:text-gray-300">Nama</th>
                    <th class="px-4 py-2 text-left text-gray-600 dark:text-gray-300">Email</th>
                    <th class="px-4 py-2 text-left text-gray-600 dark:text-gray-300">Telepon</th>
                    <th class="px-4 py-2 text-left text-gray-600 dark:text-gray-300">Alamat</th>
                    <th class="px-4 py-2 text-left text-gray-600 dark:text-gray-300">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($profiles as $index => $profile)
                    <tr class="border-t hover:bg-gray-50 dark:hover:bg-gray-800">
                        <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $index + 1 }}</td>
                        <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $profile->user->name ?? '-' }}</td>
                        <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $profile->user->email ?? '-' }}</td>
                        <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $profile->phone ?? '-' }}</td>
                        <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $profile->address ?? '-' }}</td>
                        <td class="px-4 py-2">
                            <a href="{{ route('admin.userprofile.show', $profile->id) }}" class="text-blue-500 hover:text-blue-700 font-medium">Lihat</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">Tidak ada data profil pengguna.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
