@extends('layouts.admin.app')

@section('content')
<div class="container mx-auto p-4 lg:p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-semibold text-gray-900">Detail Pembayaran UKT</h2>
            <p class="text-sm text-gray-500">Informasi lengkap transaksi tagihan biaya kuliah mahasiswa</p>
        </div>
        <a href="{{ route('admin.mandiri.ukt.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors text-sm font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-theme-lg border border-gray-100 overflow-hidden">
                <div class="bg-gray-50/50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="font-semibold text-gray-800">Data Transaksi UKT</h3>
                    @php
                        $statusClasses = [
                            'paid' => 'bg-green-100 text-green-800',
                            'cancel' => 'bg-red-100 text-red-800',
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'expired' => 'bg-gray-100 text-gray-800'
                        ];
                        $currentClass = $statusClasses[$billing->status] ?? 'bg-blue-100 text-blue-800';
                    @endphp
                    <span class="px-3 py-1 rounded-full text-xs font-bold {{ $currentClass }}">
                        {{ strtoupper($billing->status) }}
                    </span>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="text-xs font-medium text-gray-400 uppercase tracking-wider">Nama Mahasiswa</label>
                        <p class="text-gray-900 font-medium mt-1">{{ $billing->customer_name }}</p>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-400 uppercase tracking-wider">Virtual Account (VA)</label>
                        <p class="text-brand-600 font-mono font-bold mt-1 text-lg">{{ $billing->virtual_account }}</p>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-400 uppercase tracking-wider">Order ID</label>
                        <p class="text-gray-900 mt-1 font-mono">{{ $billing->order_id }}</p>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-400 uppercase tracking-wider">Program Studi</label>
                        <p class="text-gray-900 mt-1">{{ $billing->nama_jurusan }} ({{ $billing->kode_prodi }})</p>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-400 uppercase tracking-wider">Tenor Pembayaran</label>
                        <p class="text-gray-900 mt-1 capitalize">{{ $billing->tenor_pembayaran }}</p>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-400 uppercase tracking-wider">Status Aktif</label>
                        <p class="mt-1">
                            @if($billing->is_active)
                                <span class="text-green-600 flex items-center gap-1 text-sm font-medium">
                                    <span class="w-2 h-2 bg-green-500 rounded-full"></span> Aktif
                                </span>
                            @else
                                <span class="text-red-600 flex items-center gap-1 text-sm font-medium">
                                    <span class="w-2 h-2 bg-red-500 rounded-full"></span> Tidak Aktif
                                </span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-theme-lg border border-gray-100 overflow-hidden">
                <div class="bg-gray-50/50 px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">Rincian Tagihan</h3>
                </div>
                <div class="p-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="text-sm text-gray-500">Nominal yang harus dibayar</span>
                            <h4 class="text-2xl font-bold text-brand-600 mt-1">Rp {{ number_format($billing->amount, 0, ',', '.') }}</h4>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] font-bold text-gray-400 uppercase">Standard Amount</span>
                            <p class="text-gray-600">Rp {{ number_format($billing->standard_amount ?? $billing->amount, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-theme-lg border border-gray-100 p-6">
                <h3 class="font-semibold text-gray-800 mb-4 border-b pb-2">Log Waktu</h3>
                <div class="space-y-4">
                    <div class="flex gap-3">
                        <div class="w-1 bg-brand-200 rounded-full"></div>
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase font-bold">Tagihan Dibuat</p>
                            <p class="text-sm text-gray-700">{{ \Carbon\Carbon::parse($billing->created_at)->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <div class="w-1 bg-orange-200 rounded-full"></div>
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase font-bold">Kadaluarsa VA</p>
                            <p class="text-sm text-gray-700">{{ \Carbon\Carbon::parse($billing->datetime_expired)->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                    @if($billing->datetime_payment)
                    <div class="flex gap-3">
                        <div class="w-1 bg-green-500 rounded-full"></div>
                        <div>
                            <p class="text-[10px] text-green-600 uppercase font-bold">Waktu Pembayaran</p>
                            <p class="text-sm text-green-700 font-bold">{{ \Carbon\Carbon::parse($billing->datetime_payment)->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <div class="bg-indigo-50 rounded-xl p-5 border border-indigo-100">
                <h4 class="text-indigo-700 font-semibold text-sm mb-2 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Catatan Sistem
                </h4>
                <p class="text-xs text-indigo-600 leading-relaxed italic">
                    "{{ $billing->description ?? 'Tidak ada deskripsi tambahan untuk transaksi ini.' }}"
                </p>
            </div>

            <div class="bg-brand-900 rounded-xl p-6 text-black shadow-lg">
                <h4 class="font-semibold mb-2">Aksi Cepat Admin</h4>
                <p class="text-xs text-brand-200 mb-4 leading-relaxed">Ubah status secara manual jika sinkronisasi bank mengalami gangguan.</p>
                <button onclick="openStatusModal('{{ $billing->id }}', '{{ $billing->customer_name }}', '{{ $billing->status }}')" 
                        class="w-full py-2 bg-brand-500 hover:bg-brand-400 text-black rounded-lg text-sm font-bold transition-colors shadow-md">
                    Ubah Status Pembayaran
                </button>
            </div>
        </div>
    </div>
</div>

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
                        <option value="cancel">CANCEL (Batal)</option>
                        <option value="expired">EXPIRED (Kadaluarsa)</option>
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
            form.action = `/admin/mandiri/ukt/${id}/update-status`;
            nameDisplay.innerText = "Mahasiswa: " + name;
            select.value = currentStatus;
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeStatusModal() {
        const modal = document.getElementById('statusModal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeStatusModal();
    });
</script>
@endsection