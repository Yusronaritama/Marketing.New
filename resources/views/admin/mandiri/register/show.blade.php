@extends('layouts.admin.app')

@section('content')
<div class="container mx-auto p-4 lg:p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-semibold text-gray-900">Detail Pembayaran Registrasi</h2>
            <p class="text-sm text-gray-500">Informasi lengkap transaksi pendaftaran mahasiswa</p>
        </div>
        <a href="{{ route('admin.mandiri.register.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors text-sm font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-theme-lg border border-gray-100 overflow-hidden">
                <div class="bg-gray-50/50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="font-semibold text-gray-800">Data Pendaftar</h3>
                    @php
                        $statusClasses = [
                            'paid' => 'bg-green-100 text-green-800',
                            'cancel' => 'bg-red-100 text-red-800',
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'expired' => 'bg-gray-100 text-gray-800'
                        ];
                        $currentClass = $statusClasses[$data->va_status] ?? 'bg-blue-100 text-blue-800';
                    @endphp
                    <span class="px-3 py-1 rounded-full text-xs font-bold {{ $currentClass }}">
                        {{ strtoupper($data->va_status) }}
                    </span>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="text-xs font-medium text-gray-400 uppercase tracking-wider">Nama Lengkap</label>
                        <p class="text-gray-900 font-medium mt-1">{{ $data->name }}</p>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-400 uppercase tracking-wider">Email</label>
                        <p class="text-gray-900 mt-1">{{ $data->email }}</p>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-400 uppercase tracking-wider">Nomor Virtual Account</label>
                        <p class="text-brand-600 font-mono font-bold mt-1">{{ $data->va_number }}</p>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-400 uppercase tracking-wider">Transaction ID</label>
                        <p class="text-gray-900 mt-1 font-mono">{{ $data->trx_id }}</p>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-400 uppercase tracking-wider">Gelombang</label>
                        <p class="text-gray-900 mt-1">Gelombang {{ $data->gelombang }}</p>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-400 uppercase tracking-wider">Metode Pembayaran</label>
                        <p class="text-gray-900 mt-1">{{ $data->payment_method ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-theme-lg border border-gray-100 overflow-hidden">
                <div class="bg-gray-50/50 px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">Rincian Biaya</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Harga Asli (Original Amount)</span>
                            <span class="text-gray-900 font-medium">Rp {{ number_format($data->original_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Diskon ({{ $data->discount_percentage ?? 0 }}%)</span>
                            <span class="text-red-600 font-medium">- Rp {{ number_format($data->discount_amount ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <hr class="border-dashed">
                        <div class="flex justify-between items-center">
                            <span class="text-base font-semibold text-gray-900">Total Tagihan (VA Amount)</span>
                            <span class="text-xl font-bold text-brand-600">Rp {{ number_format($data->va_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            @if($data->payment_proof)
            <div class="bg-white rounded-xl shadow-theme-lg border border-gray-100 overflow-hidden">
                <div class="bg-gray-50/50 px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">Bukti Pembayaran</h3>
                </div>
                <div class="p-6 text-center">
                    <img src="{{ asset('storage/' . $data->payment_proof) }}" alt="Bukti Pembayaran" class="max-w-full h-auto rounded-lg shadow-sm border mx-auto">
                    <div class="mt-4">
                        <a href="{{ asset('storage/' . $data->payment_proof) }}" target="_blank" class="text-brand-600 text-sm font-medium hover:underline">Lihat Gambar Penuh</a>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-theme-lg border border-gray-100 p-6">
                <h3 class="font-semibold text-gray-800 mb-4 border-b pb-2">Log Waktu</h3>
                <div class="space-y-4">
                    <div class="flex gap-3">
                        <div class="w-1 bg-brand-200 rounded-full"></div>
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase font-bold">Terdaftar Pada</p>
                            <p class="text-sm text-gray-700">{{ \Carbon\Carbon::parse($data->created_at)->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <div class="w-1 bg-orange-200 rounded-full"></div>
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase font-bold">Kadaluarsa VA</p>
                            <p class="text-sm text-gray-700">{{ $data->va_expired_at ? \Carbon\Carbon::parse($data->va_expired_at)->format('d M Y, H:i') : '-' }}</p>
                        </div>
                    </div>
                    @if($data->paid_at)
                    <div class="flex gap-3">
                        <div class="w-1 bg-green-500 rounded-full"></div>
                        <div>
                            <p class="text-[10px] text-green-600 uppercase font-bold">Dibayar Pada</p>
                            <p class="text-sm text-green-700 font-bold">{{ \Carbon\Carbon::parse($data->paid_at)->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <div class="bg-brand-900 rounded-xl p-6 text-black shadow-lg">
                <h4 class="font-semibold mb-2">Aksi Cepat Admin</h4>
                <p class="text-xs text-brand-200 mb-4 leading-relaxed">Gunakan fitur ini untuk mengubah status pembayaran secara manual jika terjadi kendala pada sinkronisasi otomatis.</p>
                <button onclick="openStatusModal('{{ $data->id }}', '{{ $data->name }}', '{{ $data->va_status }}')" class="w-full py-2 bg-brand-500 hover:bg-brand-400 text-black rounded-lg text-sm font-bold transition-colors">
                    Ubah Status Pembayaran
                </button>
            </div>
        </div>
    </div>
</div>

<div id="statusModal" class="fixed inset-0 z-[999] hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75" onclick="closeStatusModal()"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="statusForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="bg-white px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900">Ubah Status Pembayaran</h3>
                    <p class="text-sm text-gray-500" id="modalStudentName"></p>
                </div>
                <div class="p-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Status Baru</label>
                    <select name="status" id="statusSelect" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                        <option value="pending">PENDING</option>
                        <option value="paid">PAID (Lunas)</option>
                        <option value="cancel">CANCEL (Batal)</option>
                        <option value="expired">EXPIRED (Kadaluarsa)</option>
                    </select>
                    <div class="mt-4 p-3 bg-blue-50 rounded-lg">
                        <p class="text-xs text-blue-700 leading-relaxed">
                            <strong>Catatan:</strong> Mengubah status ke <b>PAID</b> akan mencatat waktu pembayaran saat ini secara otomatis.
                        </p>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-3">
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-lg text-black bg-brand-500 hover:bg-brand-600">
                        Simpan Perubahan
                    </button>
                    <button type="button" onclick="closeStatusModal()" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50">
                        Batal
                    </button>
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

window.onclick = function(event) {
    const modal = document.getElementById('statusModal');
    if (event.target == modal) {
        closeStatusModal();
    }
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeStatusModal();
});
</script>
@endsection