@extends('layouts.admin.app') 

@section('content')
<div class="container mx-auto px-4 py-8">
    
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Panel Verifikasi Program Studi</h1>
            <p class="text-gray-600">Selamat datang, Admin {{ Auth::user()->name }}</p>
        </div>
        <div class="text-right">
            <span class="px-4 py-2 bg-indigo-100 text-indigo-700 rounded-full font-semibold">
                Kode Prodi: {{ $data->first()->prodi_lulus ?? '-' }}
            </span>
        </div>
    </div>

    <div class="bg-white p-4 rounded-t-xl border-b flex flex-wrap gap-4 justify-between items-center">
        <form action="{{ route('admin.prodipage.index') }}" method="GET" class="flex gap-2 w-full md:w-auto">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari Nama atau Email..." 
                class="border rounded-lg px-4 py-2 w-full md:w-64 focus:ring-2 focus:ring-indigo-500 outline-none">
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
                Cari
            </button>
            @if($search)
                <a href="{{ route('admin.prodi.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300">Reset</a>
            @endif
        </form>
    </div>

    <div class="bg-white shadow-xl rounded-b-xl overflow-hidden">
        <table class="min-w-full leading-normal">
            <thead>
                <tr class="bg-gray-50 text-gray-600 text-left text-xs uppercase font-semibold">
                    <th class="px-5 py-3 border-b-2">No</th>
                    <th class="px-5 py-3 border-b-2">Mahasiswa</th>
                    <th class="px-5 py-3 border-b-2">Nilai USM</th>
                    <th class="px-5 py-3 border-b-2">Username</th>
                    <th class="px-5 py-3 border-b-2">Nomer Handphone</th>
                    <th class="px-5 py-3 border-b-2">Email</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $index => $item)
                <tr>
                    <td class="px-5 py-5 border-b text-sm">
                        {{ ($data->firstItem() + $index) }}
                    </td>
                    <td class="px-5 py-5 border-b text-sm">
                        <div class="font-bold text-gray-800">{{ $item->nama_lengkap }}</div>
                        <div class="text-gray-500">{{ $item->email }}</div>
                    </td>
                    <td class="px-5 py-5 border-b text-sm">
                        <span class="px-3 py-1 bg-gray-100 rounded-full font-mono text-indigo-600">
                            {{ $item->nilai_quiz }}
                        </span>
                    </td>
                    <td class="px-5 py-5 border-b text-sm">
                        <span class="px-3 py-1 bg-gray-100 rounded-full font-mono text-indigo-600">
                           {{ $userdatas[$item->email]->masterUser->trx_id ?? '-' }}
                        </span>
                    </td>
                    <td class="px-5 py-5 border-b text-sm">
                        <span class="px-3 py-1 bg-gray-100 rounded-full font-mono text-indigo-600">
                            {{ $userdatas[$item->email]->phone ?? '-' }}
                        </span>
                    </td>
                    <td class="px-5 py-5 border-b text-sm">
                        <span class="px-3 py-1 bg-gray-100 rounded-full font-mono text-indigo-600">
                            {{ $item->email }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-5 py-10 border-b text-center text-gray-500">
                        Tidak ada data mahasiswa ditemukan untuk program studi ini.
                    </td>
                </tr>
                @endforelse
                
            </tbody>
        </table>
                         
 
        <div class="px-5 py-5 bg-white border-t">
            {{ $data->appends(['search' => $search])->links() }}
        </div>
    </div>
</div>
@endsection