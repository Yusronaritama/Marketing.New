@extends('layouts.admin.app')

@section('title', 'User All Profile')

@section('content')
<div class="p-4 sm:p-6 bg-white rounded-2xl shadow-md dark:bg-gray-900">
    
    {{-- Header Section --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white">📋 Daftar Semua User</h2>
            <p class="text-sm text-gray-600 dark:text-white mt-1">Master Users Management</p>
        </div>

        {{-- Tombol Sinkronisasi ke Moodle --}}
        <form id="syncMoodleForm" action="{{ route('admin.userprofile.registerMoodle') }}" method="POST" class="w-full sm:w-auto">
            @csrf
            <button type="submit" id="syncMoodleButton"
                class="w-full sm:w-auto flex items-center justify-center px-5 py-2.5 bg-blue-600 dark:text-white rounded-lg shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-150 ease-in-out">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                </svg>
                Kirim Semua User ke Moodle
            </button>
        </form>
    </div>

    {{-- 🔍 Search Input --}}
    <div class="mb-4">
        <input type="text" id="searchInput" placeholder="Cari nama, email, atau nomor VA..."
            class="w-full sm:w-1/3 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none dark:bg-gray-800 dark:text-white dark:border-gray-700"
        />
    </div>

    {{-- Status Alert --}}
    <div id="moodleSyncStatus" class="mb-4 p-4 rounded-lg hidden" role="alert"></div>

    @if(session('success'))
        <div
            class="mb-4 p-3 rounded-lg bg-green-100 text-green-800 border border-green-200 dark:bg-green-900/20 dark:text-white dark:border-green-800">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"
                    xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd"></path>
                </svg>
                {{ session('success') }}
            </div>
        </div>
    @endif

    {{-- Table Section --}}
    <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="overflow-x-auto">
            <table id="userTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-white uppercase tracking-wider">#</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-white uppercase tracking-wider">Nama</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-white uppercase tracking-wider">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-white uppercase tracking-wider">Nomor VA</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-white uppercase tracking-wider">Status VA</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-white uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody id="userTableBody" class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                    @forelse($profiles as $index => $profile)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-150">
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-white">{{ $index + 1 }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $profile->user->name ?? '-' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-white">{{ $profile->user->email ?? '-' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-white font-mono">{{ $profile->va_number }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $profile->va_status == 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-white' : 
                                       ($profile->va_status == 'pending' ? 'bg-yellow-100 dark:text-white ' : 
                                       'bg-red-100 dark:bg-red-900 dark:text-white') }}">
                                    {{ ucfirst($profile->va_status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                <button 
                                    class="inline-flex items-center text-blue-600 hover:text-blue-800 dark:text-white dark:hover:text-blue-300 font-medium transition-colors duration-150" 
                                    onclick="showUserDetail({{ $profile->id }})">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    Lihat
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-white">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-400 mb-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <p class="text-lg">Tidak ada data pengguna</p>
                                    <p class="text-sm mt-1">Data pengguna akan muncul di sini setelah ditambahkan</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- Modal Popup --}}
<div id="userModal" class="fixed inset-0 hidden z-50">
  <!-- Overlay -->
  <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>

  <!-- Modal Box -->
  <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-[0_8px_40px_rgba(0,0,0,0.4)] ring-1 ring-black/5 w-full max-w-md max-h-[90vh] overflow-hidden transition-all duration-300 scale-100">
      
      <!-- Header -->
      <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Detail User</h3>
        <button onclick="closeModal()" 
          class="text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-red-500 transition p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <!-- Body -->
      <div id="userDetailContent" class="p-4 overflow-y-auto">
        <div class="animate-pulse">
          <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4 mb-2"></div>
          <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2 mb-4"></div>
          <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-full mb-2"></div>
          <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-5/6"></div>
        </div>
      </div>

    </div>
  </div>
</div>


<script>
    document.getElementById('searchInput').addEventListener('keyup', function() {
    const searchValue = this.value.toLowerCase();
    const rows = document.querySelectorAll('#userTableBody tr');

    rows.forEach(row => {
        const name = row.cells[1]?.textContent.toLowerCase() || '';
        const email = row.cells[2]?.textContent.toLowerCase() || '';
        const va = row.cells[3]?.textContent.toLowerCase() || '';

        if (name.includes(searchValue) || email.includes(searchValue) || va.includes(searchValue)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
// Fungsi showUserDetail
function showUserDetail(id) {
    const modal = document.getElementById('userModal');
    const content = document.getElementById('userDetailContent');
    modal.classList.remove('hidden');
    
    // Loading state
    content.innerHTML = `
        <div class="animate-pulse">
            <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4 mb-2"></div>
            <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2 mb-4"></div>
            <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-full mb-2"></div>
            <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-5/6"></div>
        </div>
    `;

    fetch(`{{ url('admin/userprofile/show-ajax') }}/${id}`)
        .then(res => res.json())
        .then(data => {
            if (data.status !== 'success') {
                content.innerHTML = `
                    <div class="text-center py-4">
                        <svg class="w-12 h-12 text-red-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-red-500 font-medium">Gagal memuat data pengguna.</p>
                    </div>
                `;
                return;
            }

            const user = data.master_user;
            const moodle = data.master_moodle;

            let html = `
                <div class="space-y-4">
                    <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg">
                        <h4 class="font-medium text-gray-900 dark:text-white mb-2">Informasi Utama</h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-white">Nama:</span>
                                <span class="font-medium text-gray-900 dark:text-white">${user.name}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-white">Email:</span>
                                <span class="font-medium text-gray-900 dark:text-white">${user.email}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-white">Nomor HP:</span>
                                <span class="font-medium text-gray-900 dark:text-white">${user.phone || '-'}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-white">Nomor VA:</span>
                                <span class="font-mono font-medium text-gray-900 dark:text-white">${user.va_number}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-white">Status VA:</span>
                                <span class="font-medium ${user.va_status === 'active' ? 'text-green-600' : user.va_status === 'pending' ? 'dark:text-white' : 'dark:text-white'}">${user.va_status}</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg">
                        <h4 class="font-medium text-blue-900 dark:text-white mb-2">Kredensial Penmabar :</h4>
                        
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-blue-700 dark:text-white">Username:</span>
                                <span class="font-mono font-medium text-blue-900 dark:text-white">${user.trx_id}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-blue-700 dark:text-white">Password:</span>
                                <span class="font-mono font-medium text-blue-900 dark:text-white">${user.plain_password}</span>
                            </div>
                        </div>
                    </div>
            `;

            if (moodle && moodle.username) {
                html += `
                    <div class="bg-green-50 dark:bg-green-900/20 p-3 rounded-lg">
                        <h4 class="font-medium text-green-900 dark:text-white mb-2">Kredensial Elearning</h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-green-700 dark:text-white">Username Moodle:</span>
                                <span class="font-mono font-medium text-green-900 dark:text-white">${moodle.username}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-green-700 dark:text-white">Password Moodle:</span>
                                <span class="font-mono font-medium text-green-900 dark:text-white">${user.plain_password}</span>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                html += `
                    <div class="bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg">
                        <h4 class="font-medium text-gray-900 dark:text-white mb-2">Kredensial Elearning</h4>
                        <div class="flex items-center text-gray-500 dark:text-white">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-sm italic">User belum terdaftar di Moodle</span>
                        </div>
                    </div>
                `;
            }

            html += `</div>`;
            content.innerHTML = html;
        })
        .catch((error) => {
            console.error('Error:', error);
            content.innerHTML = `
                <div class="text-center py-4">
                    <svg class="w-12 h-12 text-red-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-red-500 font-medium">Terjadi kesalahan saat memuat data.</p>
                </div>
            `;
        });
}

// Fungsi closeModal
function closeModal() {
    document.getElementById('userModal').classList.add('hidden');
}

// 🔹 SCRIPT AJAX UNTUK SYNC MOODLE
document.addEventListener('DOMContentLoaded', function() {
    const syncForm = document.getElementById('syncMoodleForm');
    const syncButton = document.getElementById('syncMoodleButton');
    const statusDiv = document.getElementById('moodleSyncStatus');

    if(syncForm) {
        syncForm.addEventListener('submit', function(e) {
            e.preventDefault(); 

            const token = this.querySelector('input[name="_token"]').value;
            const actionUrl = this.action;

            // Tampilkan status loading
            syncButton.disabled = true;
            syncButton.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Sedang Mengirim...
            `;
            statusDiv.classList.remove('hidden');
            statusDiv.className = 'mb-4 p-4 rounded-lg bg-blue-100 text-blue-800 border border-blue-200 dark:bg-blue-900/20 dark:text-white dark:border-blue-800';
            statusDiv.innerHTML = `
                <div class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <div>
                        <strong>Memulai proses sinkronisasi ke Moodle...</strong>
                        <p class="text-sm mt-1">Ini mungkin perlu waktu beberapa saat. Jangan tutup halaman ini.</p>
                    </div>
                </div>
            `;

            fetch(actionUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                let statusHtml = '';

                if (data.success) {
                    if (data.failed > 0) {
                        statusDiv.className = 'mb-4 p-4 rounded-lg bg-yellow-100 text-yellow-800 border border-yellow-200 dark:bg-yellow-900/20 dark:text-yellow-300 dark:border-yellow-800';
                    } else {
                        statusDiv.className = 'mb-4 p-4 rounded-lg bg-green-100 text-green-800 border border-green-200 dark:bg-green-900/20 dark:text-white dark:border-green-800';
                    }
                    
                    // 1. Buat Ringkasan
                    statusHtml = `
                        <div class="flex items-start">
                            <svg class="h-5 w-5 mr-3 mt-0.5 ${data.failed > 0 ? 'text-yellow-500' : 'text-green-500'}" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <strong class="block">Proses Selesai!</strong>
                                <p class="mt-1">Total User: <strong>${data.total}</strong>, Berhasil Dibuat: <strong class="text-green-600">${data.created}</strong>, Gagal: <strong class="dark:text-white">${data.failed}</strong>.</p>
                            </div>
                        </div>
                    `;

                    // 2. Tampilkan Detail User yang Berhasil Dibuat
                    if (data.details && data.details.created_users && data.details.created_users.length > 0) {
                        statusHtml += '<div class="mt-4"><strong class="text-green-700 dark:text-green-400">✔ User Berhasil Dibuat:</strong>';
                        statusHtml += '<div class="mt-2 text-sm max-h-40 overflow-y-auto space-y-1">';
                        data.details.created_users.forEach(user => {
                            statusHtml += `<div class="flex justify-between items-center py-1 px-2 bg-green-50 dark:bg-green-900/30 rounded">
                                <span class="font-medium">${user.username}</span>
                                <span class="text-xs text-green-600 dark:text-green-400">ID: ${user.moodle_id}</span>
                            </div>`;
                        });
                        statusHtml += '</div></div>';
                    }

                    // 3. Tampilkan Detail User yang Gagal Dibuat
                    if (data.details && data.details.failed_users && data.details.failed_users.length > 0) {
                        statusHtml += '<div class="mt-4"><strong class="text-red-700 dark:text-red-400">❌ User Gagal Dibuat:</strong>';
                        statusHtml += '<div class="mt-2 text-sm max-h-40 overflow-y-auto space-y-1">';
                        data.details.failed_users.forEach(user => {
                            statusHtml += `<div class="py-1 px-2 bg-red-50 dark:bg-red-900/30 rounded">
                                <div class="font-medium">${user.username}</div>
                                <div class="text-xs dark:text-white dark:text-red-400 mt-1">${user.error}</div>
                            </div>`;
                        });
                        statusHtml += '</div></div>';
                    }
                    
                    statusDiv.innerHTML = statusHtml;

                } else {
                    statusDiv.className = 'mb-4 p-4 rounded-lg bg-red-100 text-red-800 border border-red-200 dark:bg-red-900/20 dark:text-red-300 dark:border-red-800';
                    statusDiv.innerHTML = `
                        <div class="flex items-start">
                            <svg class="h-5 w-5 mr-3 mt-0.5 text-red-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <strong>Error:</strong> ${data.message || 'Terjadi kesalahan saat sinkronisasi.'}
                            </div>
                        </div>
                    `;
                }
            })
            .catch(error => {
                statusDiv.className = 'mb-4 p-4 rounded-lg bg-red-100 text-red-800 border border-red-200 dark:bg-red-900/20 dark:text-red-300 dark:border-red-800';
                statusDiv.innerHTML = `
                    <div class="flex items-start">
                        <svg class="h-5 w-5 mr-3 mt-0.5 text-red-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <strong>Error Jaringan:</strong> Gagal terhubung ke server. Silakan coba lagi.
                        </div>
                    </div>
                `;
                console.error('Fetch Error:', error);
            })
            .finally(() => {
                syncButton.disabled = false;
                syncButton.innerHTML = `
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                    </svg>
                    Kirim Semua User ke Moodle
                `;
            });
        });
    }
});
</script>
<script src="{{ asset('assets/js/bundle.js') }}"></script>
@endsection