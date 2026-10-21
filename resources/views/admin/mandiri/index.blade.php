@extends('layouts.admin.app')

@section('content')
<div class="container mx-auto p-4 lg:p-6">
    <div class="bg-white rounded-xl shadow-theme-lg overflow-hidden">
        <!-- Header -->
        <div class="border-b border-gray-200 px-6 py-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h5 class="text-lg font-semibold text-gray-900">BNI Payment Sync (Direct Inquiry)</h5>
                    <p class="text-sm text-gray-500 mt-1">Sinkronisasi status pembayaran Virtual Account dengan server BNI</p>
                </div>
                <div>
                    <button onclick="syncAll()" class="inline-flex items-center gap-2 rounded-lg bg-success-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-success-600 transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                        </svg>
                        Sinkron Semua dari BNI
                    </button>
                </div>
            </div>
        </div>

        <!-- Body -->
        <div class="p-6">
            @if(session('success'))
                <div class="mb-6 rounded-lg bg-success-50 border border-success-300 p-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-success-500 mr-3" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <p class="text-sm text-success-700 font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if(session('info'))
                <div class="mb-6 rounded-lg bg-blue-light-50 border border-blue-light-500/30 p-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-blue-light-500 mr-3" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        <p class="text-sm text-gray-700 font-medium">{{ session('info') }}</p>
                    </div>
                </div>
            @endif

            <div class="overflow-x-auto custom-scrollbar">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Mahasiswa</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">VA Number</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Database</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php 
                            $displayUsers = \App\Models\User::whereNotNull('va_number')->orderBy('updated_at', 'desc')->get(); 
                        @endphp
                        
                        @forelse($displayUsers as $user)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 flex-shrink-0 rounded-full bg-brand-50 flex items-center justify-center">
                                        <span class="text-brand-500 font-semibold">{{ substr($user->name, 0, 1) }}</span>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <code class="inline-block px-3 py-1.5 bg-gray-100 rounded-md text-sm font-mono text-gray-800">{{ $user->va_number }}</code>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($user->va_status == 'PAID')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        PAID / LUNAS
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-warning-100 text-warning-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                        </svg>
                                        PENDING
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="syncSingle('{{ $user->va_number }}', '{{ $user->name }}')" class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 hover:border-brand-500 hover:text-brand-600 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    Cek BNI
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p class="text-lg font-medium">Tidak ada data VA.</p>
                                    <p class="text-sm mt-1">Belum ada Virtual Account yang terdaftar.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination atau info count -->
            @if($displayUsers->count() > 0)
            <div class="mt-6 flex items-center justify-between border-t border-gray-200 pt-4">
                <div class="text-sm text-gray-500">
                    Menampilkan <span class="font-medium">{{ $displayUsers->count() }}</span> data VA
                </div>
                <div class="text-sm text-gray-500">
                    Terakhir diperbarui: {{ now()->format('d/m/Y H:i') }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
function syncAll() {
    if (confirm('Mulai sinkronisasi semua data dengan server BNI?\nProses ini mungkin memakan waktu beberapa menit.')) {
        // Show loading state
        const button = document.querySelector('button[onclick="syncAll()"]');
        const originalHtml = button.innerHTML;
        button.innerHTML = `
            <div class="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent"></div>
            Memproses...
        `;
        button.disabled = true;
        
        // Redirect to sync route
        window.location.href = "{{ route('bni.sync_all') }}";
    }
}

function syncSingle(vaNumber, name) {
    if (confirm(`Cek status VA ${vaNumber} untuk ${name}?`)) {
        // You can implement AJAX call here or redirect to specific route
        alert(`Fitur cek BNI untuk VA ${vaNumber} akan segera diimplementasikan.`);
    }
}
</script>

<style>
/* Custom styles for dark mode */
.dark .bg-white {
    background-color: var(--color-gray-dark);
}
.dark .text-gray-900 {
    color: var(--color-white);
}
.dark .text-gray-700 {
    color: var(--color-gray-300);
}
.dark .text-gray-500 {
    color: var(--color-gray-400);
}
.dark .border-gray-200 {
    border-color: var(--color-gray-800);
}
.dark .bg-gray-50 {
    background-color: var(--color-gray-900);
}
.dark .divide-gray-200 > :not([hidden]) ~ :not([hidden]) {
    border-color: var(--color-gray-800);
}
</style>
@endsection