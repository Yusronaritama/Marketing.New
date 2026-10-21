@extends('layouts.admin.app')

@section('content')
<div class="container mx-auto p-4 lg:p-6">
    
    <!-- Widget Statistik (Bisa diklik untuk detail) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <a href="{{ route('admin.mandiri.register.index', ['amount' => '285000']) }}" 
           class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4 hover:border-brand-500 transition-all group">
            <div class="p-3 bg-brand-50 rounded-lg group-hover:bg-brand-500 transition-colors">
                <svg class="w-6 h-6 text-brand-600 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <div>
                <p class="text-[10px] uppercase tracking-wider text-gray-400 font-bold group-hover:text-brand-600">Klik Lihat Detail</p>
                <p class="text-sm text-gray-500 font-medium">Pembayaran Rp 285.000</p>
                <h3 class="text-2xl font-bold text-gray-900">{{ $totalSpecificAmount ?? 0 }} <span class="text-sm font-normal text-gray-400">Orang</span></h3>
            </div>
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-theme-lg overflow-hidden">
        <div class="border-b border-gray-200 px-6 py-4">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <h5 class="text-lg font-semibold text-gray-900">Mandiri Status Pembayaran Registrasi</h5>
                    <p class="text-sm text-gray-500 mt-1">Data pendaftar dengan VA awal 896 (Master Users)</p>
                </div>
                
                <div class="flex flex-wrap items-center gap-3">
                    <form action="{{ route('admin.mandiri.register.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Nama / VA / Trx ID" 
                               class="rounded-lg border-gray-300 text-sm focus:ring-brand-500 pl-4">

                        <select name="status" class="rounded-lg border-gray-300 text-sm focus:ring-brand-500">
                            <option value="">Semua Status</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>PAID</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>PENDING</option>
                        </select>

                        <select name="per_page" class="rounded-lg border-gray-300 text-sm focus:ring-brand-500">
                            <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>10 Data</option>
                            <option value="25" {{ request('per_page') == '25' || !request('per_page') ? 'selected' : '' }}>25 Data</option>
                            <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50 Data</option>
                            <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100 Data</option>
                            <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>Semua</option>
                        </select>

                        <button type="submit" class="bg-brand-500 text-black px-4 py-2 rounded-lg text-sm font-medium hover:bg-brand-600 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            Filter
                        </button>
                        
                        @if(request('search') || request('status') || request('per_page') || request('amount'))
                            <a href="{{ route('admin.mandiri.register.index') }}" class="text-sm text-gray-500 hover:text-brand-600 underline">Reset</a>
                        @endif
                    </form>

                    <a href="{{ route('admin.mandiri.register.export', request()->all()) }}" 
                       class="bg-emerald-600 text-black px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-700 flex items-center gap-2 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export Excel
                    </a>
                </div>
            </div>
        </div>

        <div class="p-6">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Indikator Filter Nominal Aktif -->
            @if(request('amount'))
            <div class="mb-4 flex items-center justify-between bg-brand-50 p-3 rounded-lg border border-brand-200">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-medium text-gray-700 font-bold">📍 Filter Aktif:</span>
                    <span class="bg-brand-500 text-black px-3 py-1 rounded-full text-xs font-bold border border-brand-600">
                        Rp {{ number_format(request('amount'), 0, ',', '.') }}
                    </span>
                </div>
                <a href="{{ route('admin.mandiri.register.index') }}" class="text-xs bg-white text-red-600 border border-red-200 px-2 py-1 rounded hover:bg-red-50 transition-colors">
                    Hapus Filter
                </a>
            </div>
            @endif

            <div class="overflow-x-auto custom-scrollbar">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr class="bg-gray-50 text-left">
                            <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase w-10">No</th>
                            <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Nama Lengkap</th>
                            <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">VA & TRX ID</th>
                            <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-center">Nominal</th>
                            <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-center">Status</th>
                            <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-center">Bukti</th>
                            <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-center">Kode Promo</th>
                            <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($registers as $item)
                        <tr class="hover:bg-gray-50 text-sm">
                            <td class="px-4 py-4 whitespace-nowrap text-gray-500 font-medium text-center">
                                {{ ($registers->currentPage() - 1) * $registers->perPage() + $loop->iteration }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">{{ $item->name }}</div>
                                <div class="text-[10px] text-gray-500 uppercase">Gel: {{ $item->gelombang ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-brand-600 font-mono text-xs bg-brand-50 px-2 py-1 rounded block w-fit">{{ $item->va_number }}</span>
                                <div class="mt-1 text-[10px] text-gray-400">Trx: {{ $item->trx_id }}</div>
                            </td>
                            <td class="px-6 py-4 font-semibold text-gray-900 text-center">
                                Rp {{ number_format($item->va_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($item->va_status == 'paid')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-green-100 text-green-800 uppercase">PAID</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-yellow-100 text-yellow-800 uppercase">{{ $item->va_status }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
            @if($item->promo_code_used)
                <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded text-xs font-bold font-mono">
                    {{ $item->promo_code_used }}
                </span>
            @else
                <span class="text-gray-400 text-xs">-</span>
            @endif
        </td>
                            <td class="px-6 py-4 text-center">
                                @if(isset($item->payment_proof) && $item->payment_proof)
                                    <a href="{{ asset('storage/' . $item->payment_proof) }}" target="_blank" class="group relative inline-block text-brand-600 hover:text-brand-700">
                                        <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block z-50">
                                            <img src="{{ asset('storage/' . $item->payment_proof) }}" class="w-32 h-32 object-cover rounded-lg shadow-xl border-2 border-white">
                                        </div>
                                    </a>
                                @else
                                    <span class="text-gray-300 text-[10px] italic">No file</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center gap-2">
                                    <a href="{{ route('admin.mandiri.register.show', $item->id) }}" class="p-1.5 border rounded-md hover:bg-gray-50 text-xs font-medium">Detail</a>
                                    <button onclick="openStatusModal('{{ $item->id }}', '{{ $item->name }}', '{{ $item->va_status }}')" 
                                            class="bg-brand-500 text-black px-3 py-1.5 rounded-md text-xs font-medium hover:bg-brand-600 shadow-sm">Status</button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="px-6 py-12 text-center text-gray-400">Tidak ada data pendaftaran.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-6">
                {{ $registers->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal dan Script Tetap Sama ... -->
<div id="statusModal" class="fixed inset-0 z-[999] hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500/75 transition-opacity" onclick="closeStatusModal()"></div>
        <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full overflow-hidden">
            <form id="statusForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold">Ubah Status Pembayaran</h3>
                    <p class="text-sm text-gray-500" id="modalStudentName"></p>
                </div>
                <div class="p-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Status Baru</label>
                    <select name="status" id="statusSelect" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-brand-500 focus:border-brand-500 text-sm">
                        <option value="pending">PENDING</option>
                        <option value="paid">PAID (Lunas)</option>
                    </select>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-3">
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-black bg-brand-500 rounded-lg hover:bg-brand-600">Simpan</button>
                    <button type="button" onclick="closeStatusModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openStatusModal(id, name, currentStatus) {
    const modal = document.getElementById('statusModal');
    const form = document.getElementById('statusForm');
    const nameDisplay = document.getElementById('modalStudentName');
    const select = document.getElementById('statusSelect');

    if (modal && form) {
        let urlTemplate = "{{ route('admin.mandiri.register.update_status', 'ID_DUMMY') }}";
        form.action = urlTemplate.replace('ID_DUMMY', id);
        nameDisplay.innerText = "Mahasiswa: " + name;
        select.value = currentStatus;
        modal.classList.remove('hidden');
    }
}

function closeStatusModal() {
    const modal = document.getElementById('statusModal');
    if (modal) modal.classList.add('hidden');
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeStatusModal();
});
</script>

<style>
    .custom-scrollbar::-webkit-scrollbar { height: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
</style>
@endsection