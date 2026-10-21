@extends('layouts.admin.app')

@section('content')
<div class="container mx-auto p-4 lg:p-6">
    <div class="bg-white rounded-xl shadow-theme-lg overflow-hidden">
        <div class="border-b border-gray-200 px-6 py-4">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <h5 class="text-lg font-semibold text-gray-900">Mandiri Status Pembayaran UKT</h5>
                    <p class="text-sm text-gray-500 mt-1">Daftar transaksi tagihan dari tabel slik_billings</p>
                </div>
                
                <div class="flex flex-wrap items-center gap-3">
                    <form action="{{ route('admin.mandiri.ukt.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
                        <div class="relative">
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   placeholder="Cari Nama / VA / ID..." 
                                   class="block w-full rounded-lg border-gray-300 pl-4 text-sm focus:border-brand-500 focus:ring-brand-500">
                        </div>

                        <select name="status" class="rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                            <option value="">Semua Status</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>PAID</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>PENDING</option>
                            <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>EXPIRED</option>
                            <option value="cancel" {{ request('status') == 'cancel' ? 'selected' : '' }}>CANCEL</option>
                        </select>

                        <select name="filter_duplicate" class="rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                            <option value="">Cek Data Normal</option>
                            <option value="duplicate_user" {{ request('filter_duplicate') == 'duplicate_user' ? 'selected' : '' }}>Cek Mahasiswa Double</option>
                            <option value="duplicate_va" {{ request('filter_duplicate') == 'duplicate_va' ? 'selected' : '' }}>Cek VA Double</option>
                        </select>
                        <select name="per_page" class="rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                            <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>10 Data</option>
                            <option value="25" {{ request('per_page') == '25' || !request('per_page') ? 'selected' : '' }}>25 Data</option>
                            <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50 Data</option>
                            <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100 Data</option>
                            <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>Semua</option>
                        </select>

                        <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-black hover:bg-brand-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            Filter
                        </button>
                        
                        @if(request('search') || request('status') || request('per_page'))
                            <a href="{{ route('admin.mandiri.ukt.index') }}" class="text-sm text-gray-500 hover:text-brand-600 underline">Reset</a>
                        @endif
                    </form>

                    <a href="{{ route('admin.mandiri.ukt.export', request()->all()) }}" 
                       class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-black hover:bg-emerald-700 transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        Export Excel
                    </a>
                </div>
            </div>
        </div>

        <div class="p-6">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-10">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mahasiswa</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prodi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">VA & Order ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nominal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($billings as $item)
                        <tr class="hover:bg-gray-50 transition-colors text-sm">
                            <td class="px-4 py-4 whitespace-nowrap text-gray-500 font-medium">
                                {{ ($billings->currentPage() - 1) * $billings->perPage() + $loop->iteration }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-gray-900">{{ $item->nama_lengkap_user }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">{{ $item->nama_jurusan }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <code class="text-xs font-mono text-brand-600 bg-brand-50 px-2 py-1 rounded">{{ $item->virtual_account }}</code>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-semibold text-gray-900">
                                Rp {{ number_format($item->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusClasses = [
                                        'paid' => 'bg-green-100 text-green-800',
                                        'cancel' => 'bg-red-100 text-red-800',
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'expired' => 'bg-gray-100 text-gray-800'
                                    ];
                                    $class = $statusClasses[$item->status] ?? 'bg-blue-100 text-blue-800';
                                @endphp
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $class }}">
                                    {{ strtoupper($item->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex justify-center gap-2">
                                    <a href="{{ route('admin.mandiri.ukt.show', $item->id) }}" class="px-3 py-1.5 border border-gray-300 rounded-md text-xs font-medium text-gray-700 hover:bg-gray-50">Detail</a>
                                    <button onclick="openStatusModal('{{ $item->id }}', '{{ $item->nama_lengkap_user }}', '{{ $item->status }}')" 
                                            class="px-3 py-1.5 bg-brand-500 text-black rounded-md text-xs font-medium hover:bg-brand-600 shadow-sm">Ubah Status</button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-400">Data tidak ditemukan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $billings->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection