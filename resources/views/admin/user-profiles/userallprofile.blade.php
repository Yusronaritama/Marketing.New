@extends('layouts.admin.app')

@section('title', 'User All Profile')

@section('content')
<div class="p-4 sm:p-6 bg-white rounded-2xl shadow-md dark:bg-gray-900">
 
    {{-- Progress Container --}}
    <div id="progressContainer" class="hidden mb-4">
        <div class="flex justify-between mb-1">
            <span class="text-sm font-medium text-blue-700 dark:text-black" id="progressText">Memproses...</span>
            <span class="text-sm font-medium text-blue-700 dark:text-black" id="progressPercent">0%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
            <div id="progressBar" class="bg-blue-600 h-2.5 rounded-full" style="width: 0%"></div>
        </div>
        <p id="statusLog" class="text-xs mt-2 text-gray-500 italic"></p>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="mb-4 p-3 rounded-lg bg-green-100 text-green-800 border border-green-200 dark:bg-green-900/20 dark:text-black dark:border-green-800">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-3 rounded-lg bg-red-100 text-red-800 border border-red-200 dark:bg-red-900/20 dark:text-red-300 dark:border-red-800">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                {{ session('error') }}
            </div>
        </div>
    @endif

    @if(isset($failed_users) && count($failed_users) > 0)
        <div class="mb-4 p-3 rounded-lg bg-yellow-100 text-yellow-800 border border-yellow-200 dark:bg-yellow-900/20 dark:text-yellow-300 dark:border-yellow-800">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <strong>Perhatian!</strong> Beberapa user gagal dihapus:
                    @foreach($failed_users as $failed)
                        <div class="text-sm mt-1">User ID {{ $failed['user_id'] }}: {{ $failed['error'] }}</div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
<div class="flex flex-wrap gap-2 mb-4">
    <button id="bulkDeleteBtn" class="px-4 py-2 bg-red-600 text-black rounded-lg hover:bg-red-700 transition duration-150 ease-in-out flex items-center shadow-sm">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
        </svg>
        Hapus Terpilih (<span id="selectedCount">0</span>)
    </button>

    {{-- Anda bisa menambahkan tombol bulk enroll juga di sini jika perlu --}}
    <button id="bulkEnrollBtn" class="px-4 py-2 bg-purple-600 text-black rounded-lg hover:bg-purple-700 transition duration-150 ease-in-out flex items-center shadow-sm">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        Enroll Terpilih
    </button>
</div>
    <div class="mb-4">
       <form method="GET" action="{{ route('admin.userprofile.index') }}" class="mb-4 flex gap-2">
    <input type="text" name="search" value="{{ request('search') }}"
        placeholder="Cari nama, email, atau nomor VA..."
        class="w-full sm:w-1/3 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none dark:bg-gray-800 dark:text-white dark:border-gray-700"
    />
    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium">
        Cari
    </button>
    @if(request('search'))
        <a href="{{ route('admin.userprofile.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm font-medium dark:bg-gray-700 dark:text-gray-300">
            Reset
        </a>
    @endif
</form>
    </div>

    {{-- Status Alert --}}
    <div id="moodleSyncStatus" class="mb-4 p-4 rounded-lg hidden" role="alert"></div>
    <div id="deleteStatus" class="mb-4 p-4 rounded-lg hidden" role="alert"></div>
    <div id="enrollStatus" class="mb-4 p-4 rounded-lg hidden" role="alert"></div>

    {{-- Table Section --}}
    <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="overflow-x-auto">
            <table id="userTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-black uppercase tracking-wider">
                            <input type="checkbox" id="selectAll" class="rounded border-gray-300">
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-black uppercase tracking-wider">#</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-black uppercase tracking-wider">Nama</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-black uppercase tracking-wider">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-black uppercase tracking-wider">Nomor VA</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-black uppercase tracking-wider">Status VA</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-black uppercase tracking-wider">Moodle Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-black uppercase tracking-wider">Enrollment Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-black uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody id="userTableBody" class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                    @forelse($profiles as $profile)
                     @php $index = ($profiles->currentPage() - 1) * $profiles->perPage() + $loop->iteration; @endphp
                        @php
                            // PERBAIKAN: Ambil data moodle dari database
                            $moodleData = DB::table('master_moodle')->where('master_user_id', $profile->id)->first();
                            $moodleStatus = $moodleData->status ?? 'not_registered';
                            $enrollStatus = $moodleData->enrol_status ?? 'not_enrolled';
                            $moodleUserId = $moodleData->moodle_user_id ?? null;
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-150" 
                            data-id="{{ $profile->id }}" 
                            data-moodle-user-id="{{ $moodleUserId }}">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <input type="checkbox" class="user-checkbox rounded border-gray-300" 
                                    value="{{ $profile->id }}" 
                                    data-moodle-user-id="{{ $moodleUserId }}" 
                                    data-moodle-status="{{ $moodleStatus }}">
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-black">{{ $index + 1 }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-black">{{ $profile->user->name ?? '-' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-black">{{ $profile->user->email ?? '-' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-black font-mono">{{ $profile->va_number }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $profile->va_status == 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-black' : 
                                      ($profile->va_status == 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300' : 
                                      ($profile->va_status == 'paid' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' :
                                      'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300')) }}">
                                    {{ ucfirst($profile->va_status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($moodleStatus == 'success')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-black">
                                        ✅ Success
                                    </span>
                                @elseif($moodleStatus == 'failed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                                        ❌ Failed
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                        ⏳ Not Registered
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($enrollStatus == 'enrolled')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-black">
                                        ✅ Enrolled
                                    </span>
                                @elseif($enrollStatus == 'enrol_failed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                                        ❌ Enroll Failed
                                    </span>
                                @elseif($moodleStatus == 'success')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                        ⚠ Not Enrolled
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                        - 
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                <div class="flex items-center space-x-2">
                                    {{-- Tombol Lihat --}}
                                    <button 
                                        class="inline-flex items-center text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium transition-colors duration-150 p-1 rounded hover:bg-blue-50 dark:hover:bg-blue-900/30" 
                                        onclick="showUserDetail({{ $profile->id }})"
                                        title="Lihat Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
<button type="button" 
    class="inline-flex items-center text-yellow-600 hover:text-yellow-800 dark:text-yellow-400 font-medium p-1 rounded hover:bg-yellow-50 dark:hover:bg-yellow-900/30 transition-colors duration-150"
    onclick="openEditProfileModal('{{ $profile->id }}', '{{ addslashes($profile->user->name ?? '') }}', '{{ addslashes($profile->user->email ?? '') }}', '{{ addslashes($profile->user->phone ?? '') }}')"
    title="Edit Profile">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
    </svg>
</button>
                                    {{-- Tombol Enroll ke Course (hanya untuk user yang sudah success di Moodle) --}}
                                    @if($moodleStatus == 'success' && $enrollStatus != 'enrolled')
                                        <button 
                                            type="button"
                                            class="inline-flex items-center text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300 font-medium transition-colors duration-150 p-1 rounded hover:bg-purple-50 dark:hover:bg-purple-900/30"
                                            onclick="enrollSingleUser('{{ $profile->id }}', '{{ $moodleUserId }}', '{{ $profile->user->name ?? 'N/A' }}', '{{ $profile->va_number }}')"
                                            title="Daftarkan ke Course">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                        </button>
                                    @endif
                                    
                                    {{-- Tombol Hapus --}}
                                    <button 
                                        type="button"
                                        class="inline-flex items-center text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 font-medium transition-colors duration-150 p-1 rounded hover:bg-red-50 dark:hover:bg-red-900/30"
                                        onclick="openDeleteModal('{{ $profile->id }}', '{{ $profile->user->name ?? 'N/A' }}', '{{ $profile->va_number }}')"
                                        title="Hapus User">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center text-gray-500 dark:text-black">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
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
 {{-- Pagination --}}
    @if($profiles->hasPages())
    <div class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-3">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Menampilkan 
            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $profiles->firstItem() }}</span>
            –
            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $profiles->lastItem() }}</span>
            dari
            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $profiles->total() }}</span>
            pengguna
        </p>

        <div class="flex items-center gap-1">
            {{-- Tombol Pertama --}}
            @if($profiles->onFirstPage())
                <span class="px-3 py-1.5 rounded-lg text-sm text-gray-300 border border-gray-200 cursor-not-allowed dark:border-gray-700 dark:text-gray-600">«</span>
            @else
                <a href="{{ $profiles->url(1) }}" class="px-3 py-1.5 rounded-lg text-sm text-gray-600 border border-gray-200 hover:bg-gray-50 hover:border-gray-300 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition-colors">«</a>
            @endif

            {{-- Tombol Prev --}}
            @if($profiles->onFirstPage())
                <span class="px-3 py-1.5 rounded-lg text-sm text-gray-300 border border-gray-200 cursor-not-allowed dark:border-gray-700 dark:text-gray-600">‹</span>
            @else
                <a href="{{ $profiles->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg text-sm text-gray-600 border border-gray-200 hover:bg-gray-50 hover:border-gray-300 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition-colors">‹</a>
            @endif

            {{-- Nomor Halaman --}}
            @foreach($profiles->getUrlRange(max(1, $profiles->currentPage() - 2), min($profiles->lastPage(), $profiles->currentPage() + 2)) as $page => $url)
                @if($page == $profiles->currentPage())
                    <span class="px-3 py-1.5 rounded-lg text-sm font-semibold bg-blue-600 text-white border border-blue-600">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="px-3 py-1.5 rounded-lg text-sm text-gray-600 border border-gray-200 hover:bg-gray-50 hover:border-gray-300 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition-colors">{{ $page }}</a>
                @endif
            @endforeach

            {{-- Tombol Next --}}
            @if($profiles->hasMorePages())
                <a href="{{ $profiles->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg text-sm text-gray-600 border border-gray-200 hover:bg-gray-50 hover:border-gray-300 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition-colors">›</a>
            @else
                <span class="px-3 py-1.5 rounded-lg text-sm text-gray-300 border border-gray-200 cursor-not-allowed dark:border-gray-700 dark:text-gray-600">›</span>
            @endif

            {{-- Tombol Terakhir --}}
            @if($profiles->hasMorePages())
                <a href="{{ $profiles->url($profiles->lastPage()) }}" class="px-3 py-1.5 rounded-lg text-sm text-gray-600 border border-gray-200 hover:bg-gray-50 hover:border-gray-300 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition-colors">»</a>
            @else
                <span class="px-3 py-1.5 rounded-lg text-sm text-gray-300 border border-gray-200 cursor-not-allowed dark:border-gray-700 dark:text-gray-600">»</span>
            @endif
        </div>
    </div>
    @endif
</div>
{{-- Modal Edit Profile --}}
<div id="editProfileModal" class="fixed inset-0 hidden z-50">
  <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
  <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-[0_8px_40px_rgba(0,0,0,0.4)] ring-1 ring-black/5 w-full max-w-md">
      
      <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Edit Profile User</h3>
        <button onclick="closeEditProfileModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-red-500 transition p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>

      <div class="p-4 space-y-4">
        <input type="hidden" id="editProfileId">
        
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Lengkap</label>
            <input type="text" id="editProfileName" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Masukkan nama">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email Valid</label>
            <input type="email" id="editProfileEmail" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Masukkan email">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">No. Handphone</label>
            <input type="text" id="editProfilePhone" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Masukkan nomor handphone (opsional)">
        </div>
        
        <div id="editProfileError" class="hidden text-sm text-red-600 bg-red-50 p-2 rounded border border-red-200"></div>
      </div>

      <div class="flex justify-end gap-3 p-4 border-t border-gray-200 dark:border-gray-700">
        <button onclick="closeEditProfileModal()" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">Batal</button>
        <button id="submitEditProfileBtn" onclick="submitEditProfile()" class="px-4 py-2 bg-blue-600 text-white hover:bg-blue-700 rounded-lg transition duration-150">Simpan Perubahan</button>
      </div>

    </div>
  </div>
</div>
{{-- Modal Detail User --}}
<div id="userModal" class="fixed inset-0 hidden z-50">
  <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
  <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-[0_8px_40px_rgba(0,0,0,0.4)] ring-1 ring-black/5 w-full max-w-md max-h-[90vh] overflow-hidden transition-all duration-300 scale-100">
      
      <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-black">Detail User</h3>
        <button onclick="closeModal()" 
          class="text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-red-500 transition p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <div id="userDetailContent" class="p-4 overflow-y-auto">
        <div class="animate-pulse">
          <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4 mb-2"></div>
          <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2 mb-4"></div>
          <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-full mb-2"></div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Modal Konfirmasi Hapus --}}
<div id="deleteModal" class="fixed inset-0 hidden z-50">
  <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
  <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-[0_8px_40px_rgba(0,0,0,0.4)] ring-1 ring-black/5 w-full max-w-md">
      
      <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-black">Konfirmasi Hapus User</h3>
        <button onclick="closeDeleteModal()" 
          class="text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-red-500 transition p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <div class="p-4">
        <p class="text-gray-700 dark:text-gray-300 mb-4">
          Apakah Anda yakin ingin menghapus user <strong id="deleteUserName" class="text-gray-900 dark:text-black"></strong>?
        </p>
        <p class="text-gray-700 dark:text-gray-300 mb-2">
          VA Number: <strong id="deleteUserVA" class="font-mono text-gray-900 dark:text-black"></strong>
        </p>
        
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-3 mb-4">
          <div class="flex items-start">
            <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
            <div class="text-sm text-yellow-800 dark:text-yellow-300">
              <strong>Perhatian!</strong>
              <ul class="mt-1 ml-4 list-disc space-y-1">
                <li>User akan dihapus dari sistem</li>
                <li>Virtual Account akan dinonaktifkan otomatis</li>
                <li>Data terkait akan dihapus</li>
                <li>Aksi ini tidak dapat dibatalkan</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <div class="flex justify-end gap-3 p-4 border-t border-gray-200 dark:border-gray-700">
        <button onclick="closeDeleteModal()" 
          class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition duration-150 ease-in-out">
          Batal
        </button>
        <button id="confirmDeleteBtn" 
          class="px-4 py-2 bg-red-600 text-black hover:bg-red-700 rounded-lg transition duration-150 ease-in-out">
          Hapus User
        </button>
      </div>

    </div>
  </div>
</div>

{{-- Modal Enroll ke Course --}}
<div id="enrollModal" class="fixed inset-0 hidden z-50">
  <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
  <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-[0_8px_40px_rgba(0,0,0,0.4)] ring-1 ring-black/5 w-full max-w-md">
      
      <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-black">Daftarkan ke Course Moodle</h3>
        <button onclick="closeEnrollModal()" 
          class="text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-red-500 transition p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <div class="p-4">
        <p class="text-gray-700 dark:text-gray-300 mb-4">
          Akan mendaftarkan <strong id="enrollUserName" class="text-gray-900 dark:text-black"></strong> ke course Moodle.
        </p>
        <p class="text-gray-700 dark:text-gray-300 mb-2">
          VA Number: <strong id="enrollUserVA" class="font-mono text-gray-900 dark:text-black"></strong>
        </p>
        <p class="text-gray-700 dark:text-gray-300 mb-4">
          Moodle User ID: <strong id="enrollMoodleUserId" class="font-mono text-gray-900 dark:text-black"></strong>
        </p>
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Course ID (default: 2)
          </label>
          <input type="number" id="courseIdInput" value="2" 
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:border-gray-600 dark:text-black">
        </div>
        
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3 mb-4">
          <div class="flex items-start">
            <svg class="w-5 h-5 text-blue-600 dark:text-blue-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            <div class="text-sm text-blue-800 dark:text-blue-300">
              <strong>Informasi:</strong>
              <ul class="mt-1 ml-4 list-disc space-y-1">
                <li>User akan didaftarkan ke course dengan ID yang ditentukan</li>
                <li>Role student akan otomatis diberikan</li>
                <li>Status enrollment akan diperbarui di database</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <div class="flex justify-end gap-3 p-4 border-t border-gray-200 dark:border-gray-700">
        <button onclick="closeEnrollModal()" 
          class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition duration-150 ease-in-out">
          Batal
        </button>
        <button id="confirmEnrollBtn" 
          class="px-4 py-2 bg-purple-600 text-black hover:bg-purple-700 rounded-lg transition duration-150 ease-in-out">
          Daftarkan ke Course
        </button>
      </div>

    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
window.openEditProfileModal = function(id, name, email, phone) {
    document.getElementById('editProfileId').value = id;
    document.getElementById('editProfileName').value = name;
    document.getElementById('editProfileEmail').value = email;
    document.getElementById('editProfilePhone').value = phone; // Tangkap data phone
    document.getElementById('editProfileError').classList.add('hidden');
    document.getElementById('editProfileModal').classList.remove('hidden');
};

window.closeEditProfileModal = function() {
    document.getElementById('editProfileModal').classList.add('hidden');
};

window.submitEditProfile = function() {
    const id = document.getElementById('editProfileId').value;
    const name = document.getElementById('editProfileName').value;
    const email = document.getElementById('editProfileEmail').value;
    const phone = document.getElementById('editProfilePhone').value; // Ambil nilai phone
    const btn = document.getElementById('submitEditProfileBtn');
    const errorBox = document.getElementById('editProfileError');

    // Validasi basic frontend
    if (!name || !email) {
        errorBox.textContent = 'Nama dan Email tidak boleh kosong.';
        errorBox.classList.remove('hidden');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = 'Menyimpan...';
    errorBox.classList.add('hidden');

    fetch(`/admin/user-profiles/${id}/update`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            name: name,
            email: email,
            phone: phone // Kirim data phone
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);
            window.location.reload(); // Reload untuk melihat perubahan di tabel
        } else {
            errorBox.innerHTML = data.message + '<br>' + (data.errors ? Object.values(data.errors).join('<br>') : '');
            errorBox.classList.remove('hidden');
        }
    })
    .catch(error => {
        errorBox.textContent = 'Terjadi kesalahan sistem: ' + error.message;
        errorBox.classList.remove('hidden');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = 'Simpan Perubahan';
    });
};
// ==========================================
// VARIABEL GLOBAL
// ==========================================
let currentDeleteId = null;
let currentEnrollUserId = null;
let currentEnrollMoodleUserId = null;
let currentEnrollUserName = null;
let currentEnrollUserVA = null;

// ==========================================
// HELPER FUNCTIONS
// ==========================================
function showEnrollStatus(type, message) {
    const statusDiv = document.getElementById('enrollStatus');
    if (!statusDiv) return;

    statusDiv.classList.remove('hidden');
    
    let classes = 'mb-4 p-4 rounded-lg border ';
    let iconColor = '';
    
    if (type === 'success') {
        classes += 'bg-green-100 text-green-800 border-green-200 dark:bg-green-900/20 dark:text-black dark:border-green-800';
        iconColor = 'text-green-500';
    } else if (type === 'error') {
        classes += 'bg-red-100 text-red-800 border-red-200 dark:bg-red-900/20 dark:text-red-300 dark:border-red-800';
        iconColor = 'text-red-500';
    } else {
        classes += 'bg-yellow-100 text-yellow-800 border-yellow-200 dark:bg-yellow-900/20 dark:text-yellow-300 dark:border-yellow-800';
        iconColor = 'text-yellow-500';
    }
    
    statusDiv.className = classes;
    statusDiv.innerHTML = `
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2 ${iconColor}" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            <div>${message}</div>
        </div>
    `;
    
    // Auto hide after 5 seconds for success messages
    if (type === 'success') {
        setTimeout(() => {
            statusDiv.classList.add('hidden');
        }, 5000);
    }
}

function showDeleteStatus(type, message) {
    const statusDiv = document.getElementById('deleteStatus');
    if (!statusDiv) return;

    statusDiv.classList.remove('hidden');
    
    let classes = 'mb-4 p-4 rounded-lg border ';
    let iconColor = '';
    
    if (type === 'success') {
        classes += 'bg-green-100 text-green-800 border-green-200 dark:bg-green-900/20 dark:text-black dark:border-green-800';
        iconColor = 'text-green-500';
    } else if (type === 'error') {
        classes += 'bg-red-100 text-red-800 border-red-200 dark:bg-red-900/20 dark:text-red-300 dark:border-red-800';
        iconColor = 'text-red-500';
    } else {
        classes += 'bg-yellow-100 text-yellow-800 border-yellow-200 dark:bg-yellow-900/20 dark:text-yellow-300 dark:border-yellow-800';
        iconColor = 'text-yellow-500';
    }
    
    statusDiv.className = classes;
    statusDiv.innerHTML = `
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2 ${iconColor}" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            <div>${message}</div>
        </div>
    `;
}

function showMoodleStatus(type, message) {
    const statusDiv = document.getElementById('moodleSyncStatus');
    if (!statusDiv) return;

    statusDiv.classList.remove('hidden');
    
    let classes = 'mb-4 p-4 rounded-lg border ';
    let iconColor = '';
    
    if (type === 'success') {
        classes += 'bg-green-100 text-green-800 border-green-200 dark:bg-green-900/20 dark:text-black dark:border-green-800';
        iconColor = 'text-green-500';
    } else if (type === 'error') {
        classes += 'bg-red-100 text-red-800 border-red-200 dark:bg-red-900/20 dark:text-red-300 dark:border-red-800';
        iconColor = 'text-red-500';
    } else {
        classes += 'bg-blue-100 text-blue-800 border-blue-200 dark:bg-blue-900/20 dark:text-blue-300 dark:border-blue-800';
        iconColor = 'text-blue-500';
    }
    
    statusDiv.className = classes;
    statusDiv.innerHTML = `
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2 ${iconColor}" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            <div>${message}</div>
        </div>
    `;
}

// ==========================================
// MODAL FUNCTIONS
// ==========================================
window.openDeleteModal = function(id, name, va) {
    currentDeleteId = id;
    const nameEl = document.getElementById('deleteUserName');
    const vaEl = document.getElementById('deleteUserVA');
    const modal = document.getElementById('deleteModal');
    const confirmBtn = document.getElementById('confirmDeleteBtn');

    if(nameEl) nameEl.textContent = name;
    if(vaEl) vaEl.textContent = va;
    
    if(confirmBtn) {
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = 'Hapus User';
    }
    
    if(modal) modal.classList.remove('hidden');
};

window.closeDeleteModal = function() {
    const modal = document.getElementById('deleteModal');
    if(modal) modal.classList.add('hidden');
    currentDeleteId = null;
};

window.openEnrollModal = function(userId, moodleUserId, name, va) {
    currentEnrollUserId = userId;
    currentEnrollMoodleUserId = moodleUserId;
    currentEnrollUserName = name;
    currentEnrollUserVA = va;
    
    const nameEl = document.getElementById('enrollUserName');
    const vaEl = document.getElementById('enrollUserVA');
    const moodleIdEl = document.getElementById('enrollMoodleUserId');
    const modal = document.getElementById('enrollModal');
    const confirmBtn = document.getElementById('confirmEnrollBtn');

    if(nameEl) nameEl.textContent = name;
    if(vaEl) vaEl.textContent = va;
    if(moodleIdEl) moodleIdEl.textContent = moodleUserId;
    
    if(confirmBtn) {
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = 'Daftarkan ke Course';
    }
    
    if(modal) modal.classList.remove('hidden');
};

window.closeEnrollModal = function() {
    const modal = document.getElementById('enrollModal');
    if(modal) modal.classList.add('hidden');
    currentEnrollUserId = null;
    currentEnrollMoodleUserId = null;
    currentEnrollUserName = null;
    currentEnrollUserVA = null;
};

window.closeModal = function() {
    const modal = document.getElementById('userModal');
    if(modal) modal.classList.add('hidden');
};

window.showUserDetail = function(id) {
    const modal = document.getElementById('userModal');
    const content = document.getElementById('userDetailContent');
    if(!modal || !content) return;

    modal.classList.remove('hidden');
    // Loading state
    content.innerHTML = `<div class="p-4"><div class="animate-pulse flex space-x-4"><div class="flex-1 space-y-4 py-1"><div class="h-4 bg-gray-200 rounded w-3/4"></div><div class="space-y-2"><div class="h-4 bg-gray-200 rounded"></div></div></div></div></div>`;

    fetch(`{{ url('admin/userprofile/show-ajax') }}/${id}`)
        .then(res => res.json())
        .then(data => {
            if (data.status !== 'success') throw new Error('Gagal memuat data');
            
            const user = data.master_user;
            const moodle = data.master_moodle;

            // Helper untuk warna status VA
            let vaStatusClass = 'text-gray-600';
            if(user.va_status === 'paid') vaStatusClass = 'text-green-600 font-bold';
            else if(user.va_status === 'pending') vaStatusClass = 'text-yellow-600 font-bold';
            else vaStatusClass = 'text-red-600';

            let html = `
                <div class="space-y-4">
                    {{-- Informasi User Utama --}}
                    <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg">
                        <h4 class="font-medium text-gray-900 dark:text-black mb-2">Informasi Personal</h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between"><span class="text-gray-600 dark:text-black">Nama:</span><span class="font-medium text-gray-900 dark:text-black text-right">${user.name}</span></div>
                            <div class="flex justify-between"><span class="text-gray-600 dark:text-black">Email:</span><span class="font-medium text-gray-900 dark:text-black text-right">${user.email}</span></div>
                            <div class="flex justify-between"><span class="text-gray-600 dark:text-black">No. VA:</span><span class="font-mono font-medium text-gray-900 dark:text-black text-right">${user.va_number}</span></div>
                        </div>
                    </div>

<div class="bg-purple-50 dark:bg-purple-900/20 p-3 rounded-lg">
    <h4 class="font-medium text-purple-900 dark:text-black mb-2">Akun Penmabar :</h4>
    <div class="text-sm space-y-1">
        <div class="flex justify-between">
            <span>Status Pembayaran:</span>
            <span class="${vaStatusClass} uppercase">${user.va_status}</span>
        </div>
        
        <div class="flex justify-between border-t border-purple-200 dark:border-purple-800 pt-1 mt-1">
            <span>Username:</span>
            <span class="font-mono font-bold text-purple-800 dark:text-black">${user.trx_id}</span>
        </div>

        {{-- Menampilkan Password Default (Saat Daftar) --}}
        <div class="flex justify-between border-t border-purple-200 dark:border-purple-800 pt-1 mt-1">
            <span>Password Default:</span>
            <span class="font-mono font-bold text-purple-800 dark:text-black">${user.password_awal}</span>
        </div>

        {{-- Menampilkan Password Terbaru (Setelah diubah/reset) --}}
        <div class="flex justify-between border-t border-purple-200 dark:border-purple-800 pt-1 mt-1">
            <span>Password Terbaru:</span>
            <span class="font-mono font-bold text-red-600 dark:text-red-500">${user.plain_password}</span>
        </div>
    </div>

                    {{-- BOX 2: Akun Moodle (Menggunakan Username Moodle) --}}
                    <div class="bg-indigo-50 dark:bg-indigo-900/20 p-3 rounded-lg">
                        <h4 class="font-medium text-indigo-900 dark:text-black mb-2">Akun Moodle :</h4>
                        <div class="text-sm space-y-1">
                            <div class="flex justify-between">
                                <span>Status Sync:</span>
                                <span class="${moodle?.status === 'success' ? 'text-green-600 font-bold' : 'text-red-600'}">
                                    ${moodle?.status || 'Not Registered'}
                                </span>
                            </div>
                             <div class="flex justify-between">
                                <span>Enrollment:</span>
                                <span class="${moodle?.enrol_status === 'enrolled' ? 'text-green-600 font-bold' : 'text-yellow-600'}">
                                    ${moodle?.enrol_status || 'Not Enrolled'}
                                </span>
                            </div>
                            <div class="flex justify-between border-t border-indigo-200 dark:border-indigo-800 pt-1 mt-1">
                                <span>Username:</span>
                                <span class="font-mono font-bold text-indigo-800 dark:text-black">
                                    ${moodle?.username || '-'}
                                </span>
                            </div>
                            <div class="flex justify-between border-t border-indigo-200 dark:border-indigo-800 pt-1 mt-1">
                                <span>Password:</span>
                                <span class="font-mono font-bold text-indigo-800 dark:text-black">
                                    Ubp2026*
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span>Moodle User ID:</span>
                                <span class="font-mono text-gray-600 dark:text-black">
                                    ${moodle?.moodle_user_id || '-'}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>`;
            content.innerHTML = html;
        })
        .catch(err => {
            console.error(err);
            content.innerHTML = `<div class="text-center text-red-500 py-4">Gagal memuat data: ${err.message}</div>`;
        });
};

// ==========================================
// MOODLE FUNCTIONS
// ==========================================
function enrollSingleUser(userId, moodleUserId, userName, userVA) {
    if (!moodleUserId || moodleUserId === 'null') {
        alert('User belum memiliki akun Moodle. Silakan sync ke Moodle terlebih dahulu.');
        return;
    }
    
    openEnrollModal(userId, moodleUserId, userName, userVA);
}

async function testMoodleConnection() {
    const btn = $('#testMoodleConnectionBtn');
    const log = $('#statusLog');
    const progressContainer = $('#progressContainer');
    
    btn.prop('disabled', true).addClass('opacity-50');
    progressContainer.removeClass('hidden');
    log.text('⏳ Menguji koneksi ke Moodle...');

    try {
        const response = await fetch("{{ route('admin.moodle.test-connection') }}");
        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Server error');
        }

        if (data.success) {
            const testInfo = data.connection_test;
            let message = `
                ✅ Koneksi berhasil!\n
                Site: ${testInfo.site_name}\n
                Course: ${testInfo.course_name}\n
                Token memiliki ${testInfo.token_permissions.total_enrol_functions} fungsi enrol.\n
            `;

            if (!testInfo.token_permissions.has_enrol_manual_enrol_users) {
                message += '\n⚠ PERINGATAN: Token tidak memiliki akses ke "enrol_manual_enrol_users"!';
                message += '\n⚠ PERINGATAN: Token tidak memiliki akses ke "core_enrol_enrol_user"!';
            }

            showMoodleStatus('success', message.replace(/\n/g, '<br>'));
            log.text('✅ Koneksi Moodle OK');
        } else {
            throw new Error(data.message || 'Test failed');
        }

    } catch (error) {
        log.text('❌ Koneksi gagal');
        showMoodleStatus('error', 'Error: ' + error.message);
    } finally {
        btn.prop('disabled', false).removeClass('opacity-50');
    }
}

async function enrollAllSuccessfulUsers() {
    const btn = $('#enrollAllSuccessfulBtn');
    const log = $('#statusLog');
    const progressContainer = $('#progressContainer');
    const progressBar = $('#progressBar');
    
    if(!confirm('Proses ini akan mendaftarkan semua user yang berhasil di Moodle (status: success) ke course default (ID: 2). Lanjutkan?')) return;

    btn.prop('disabled', true).addClass('opacity-50');
    progressContainer.removeClass('hidden');
    log.text('⏳ Memulai proses enroll semua user sukses...');

    try {
        // Call the API to enroll all successful users
        const response = await fetch("{{ route('admin.moodle.enrol-all') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                course_id: 2,
                limit: 100,
                test_mode: false
            })
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Server error');
        }

        if (data.success) {
            log.text(`✅ Proses selesai: ${data.summary.success} berhasil, ${data.summary.failed} gagal.`);
            showEnrollStatus('success', `Enrollment selesai: ${data.summary.success} berhasil, ${data.summary.failed} gagal.`);
            
            // Update progress bar
            progressBar.css('width', '100%');
            $('#progressPercent').text('100%');
            
            // Reload page after 2 seconds
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            throw new Error(data.message || 'Enrollment failed');
        }
    } catch (error) {
        log.text('💥 Terjadi kesalahan sistem.');
        showEnrollStatus('error', 'Error: ' + error.message);
    } finally {
        btn.prop('disabled', false).removeClass('opacity-50');
    }
}

async function bulkEnrollUsers() {
    const selectedCheckboxes = document.querySelectorAll('.user-checkbox:checked');
    if (selectedCheckboxes.length === 0) {
        alert('Pilih minimal satu user untuk didaftarkan ke course.');
        return;
    }

    // Filter hanya user yang sudah punya akun Moodle (status success)
    const validUsers = [];
    const invalidUsers = [];
    
    selectedCheckboxes.forEach(checkbox => {
        const userId = checkbox.value;
        const moodleUserId = checkbox.dataset.moodleUserId;
        const moodleStatus = checkbox.dataset.moodleStatus;
        
        if (moodleStatus === 'success' && moodleUserId && moodleUserId !== 'null') {
            validUsers.push({
                user_id: userId,
                moodle_user_id: moodleUserId
            });
        } else {
            invalidUsers.push(userId);
        }
    });

    if (validUsers.length === 0) {
        alert('Tidak ada user yang valid untuk di-enroll. Pastikan user sudah memiliki akun Moodle (status: success).');
        return;
    }

    if (invalidUsers.length > 0) {
        alert(`${invalidUsers.length} user tidak valid (belum punya akun Moodle) dan akan dilewati.`);
    }

    if(!confirm(`Akan mendaftarkan ${validUsers.length} user ke course Moodle. Lanjutkan?`)) return;

    const btn = $('#bulkEnrollBtn');
    const log = $('#statusLog');
    const progressContainer = $('#progressContainer');
    const progressBar = $('#progressBar');
    
    btn.prop('disabled', true).addClass('opacity-50');
    progressContainer.removeClass('hidden');
    log.text('⏳ Memulai bulk enrollment...');

    try {
        const moodleUserIds = validUsers.map(user => user.moodle_user_id);
        
        const response = await fetch("{{ route('admin.moodle.bulk-enrol') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                moodle_user_ids: moodleUserIds,
                course_id: 2,
                role_id: 5,
                skip_existing: true
            })
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Server error');
        }

        if (data.success) {
            log.text(`✅ Bulk enrollment selesai: ${data.summary.success} berhasil, ${data.summary.failed} gagal.`);
            showEnrollStatus('success', `Bulk enrollment selesai: ${data.summary.success} berhasil, ${data.summary.failed} gagal.`);
            
            // Update progress bar
            progressBar.css('width', '100%');
            $('#progressPercent').text('100%');
            
            // Reload page after 2 seconds
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            throw new Error(data.message || 'Enrollment failed');
        }
    } catch (error) {
        log.text('💥 Terjadi kesalahan sistem.');
        showEnrollStatus('error', 'Error: ' + error.message);
    } finally {
        btn.prop('disabled', false).removeClass('opacity-50');
    }
}

async function syncFailedNew() {
    const btn = $('#syncFailedNewBtn');
    const log = $('#statusLog');
    const progressContainer = $('#progressContainer');
    
    if(!confirm('Proses ini akan mencoba mendaftarkan ulang user yang statusnya FAILED atau belum punya akun. Lanjutkan?')) return;

    btn.prop('disabled', true).addClass('opacity-50');
    progressContainer.removeClass('hidden');
    log.text('⏳ Menghubungi server untuk memproses data failed/new...');

    try {
        const response = await fetch("{{ route('admin.moodle.sync-failed') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Server error');
        }

        if (data.status === 'completed') {
            log.text(`✅ Selesai: ${data.summary.success} Berhasil, ${data.summary.failed} Gagal.`);
            alert(`Sinkronisasi Selesai!\nSukses: ${data.summary.success}\nGagal: ${data.summary.failed}`);
            location.reload();
        } else {
            log.text(`info: ${data.message}`);
        }
    } catch (error) {
        log.text('💥 Terjadi kesalahan sistem.');
        alert('Error: ' + (error.responseJSON?.message || 'Server Error'));
    } finally {
        btn.prop('disabled', false).removeClass('opacity-50');
    }
}

async function startSync() {
    const btn = $('#syncMoodleButton');
    const log = $('#statusLog');
    const progressContainer = $('#progressContainer');
    const progressBar = $('#progressBar');

    btn.prop('disabled', true).addClass('opacity-50');
    progressContainer.removeClass('hidden');
    
    try {
        const response = await fetch("{{ route('admin.moodle.failed-users') }}?status=all");
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || 'Server error');
        }

        const users = data.users.data; 
        const total = users.length;

        if (total === 0) {
            alert('Tidak ada user untuk disinkronisasi.');
            location.reload();
            return;
        }

        for (let i = 0; i < total; i++) {
            const userId = users[i].id;
            try {
                await fetch("{{ route('admin.moodle.batch-register') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        user_ids: [userId]
                    })
                });
                log.text(`✅ Berhasil memproses ${i+1}/${total}`);
            } catch (err) {
                log.text(`❌ Gagal pada baris ${i+1}`);
            }

            const percent = Math.round(((i + 1) / total) * 100);
            progressBar.css('width', percent + '%');
            $('#progressPercent').text(percent + '%');
        }

        alert('Proses Sinkronisasi Semua User Selesai!');
        location.reload();
    } catch (error) {
        alert('Gagal mengambil data antrean.');
    } finally {
        btn.prop('disabled', false).removeClass('opacity-50');
    }
}

// ==========================================
// EVENT LISTENERS
// ==========================================
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            document.querySelectorAll('#userTableBody tr').forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchValue) ? '' : 'none';
            });
        });
    }

    // Select all checkbox
    const selectAll = document.getElementById('selectAll');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            document.querySelectorAll('.user-checkbox').forEach(cb => cb.checked = this.checked);
        });
    }

    // Bulk enroll button
    const bulkEnrollBtn = document.getElementById('bulkEnrollBtn');
    if (bulkEnrollBtn) {
        bulkEnrollBtn.addEventListener('click', bulkEnrollUsers);
    }

    // Enroll all successful button
    const enrollAllSuccessfulBtn = document.getElementById('enrollAllSuccessfulBtn');
    if (enrollAllSuccessfulBtn) {
        enrollAllSuccessfulBtn.addEventListener('click', enrollAllSuccessfulUsers);
    }

    // Test Moodle connection button
    const testMoodleConnectionBtn = document.getElementById('testMoodleConnectionBtn');
    if (testMoodleConnectionBtn) {
        testMoodleConnectionBtn.addEventListener('click', testMoodleConnection);
    }

    // Sync failed/new button
    const syncFailedNewBtn = document.getElementById('syncFailedNewBtn');
    if (syncFailedNewBtn) {
        syncFailedNewBtn.addEventListener('click', syncFailedNew);
    }

    // Sync all button
    const syncMoodleButton = document.getElementById('syncMoodleButton');
    if (syncMoodleButton) {
        syncMoodleButton.addEventListener('click', startSync);
    }

    // Confirm enroll button
    const confirmEnrollBtn = document.getElementById('confirmEnrollBtn');
    if (confirmEnrollBtn) {
        confirmEnrollBtn.addEventListener('click', async function() {
            if (!currentEnrollMoodleUserId) return;
            
            const enrollBtn = this;
            const originalText = enrollBtn.textContent;
            const courseId = document.getElementById('courseIdInput').value || 2;
            
            enrollBtn.disabled = true;
            enrollBtn.innerHTML = 'Memproses...';

            try {
                const response = await fetch("{{ route('admin.moodle.enrol-user') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        moodle_user_id: currentEnrollMoodleUserId,
                        course_id: courseId,
                        role_id: 5
                    })
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Terjadi kesalahan server');
                }

                if (data.success) {
                    showEnrollStatus('success', `Berhasil mendaftarkan user ke course: ${data.course_name}`);
                    closeEnrollModal();
                    
                    // Reload page after 1.5 seconds
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    throw new Error(data.message || 'Gagal mendaftarkan user');
                }

            } catch (error) {
                console.error('Enroll error:', error);
                showEnrollStatus('error', 'Error: ' + error.message);
            } finally {
                enrollBtn.disabled = false;
                enrollBtn.innerHTML = originalText;
            }
        });
    }

    // Confirm delete button
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            if (!currentDeleteId) return;
            
            const deleteBtn = this;
            const originalText = deleteBtn.textContent;
            
            deleteBtn.disabled = true;
            deleteBtn.innerHTML = 'Menghapus...';

            fetch(`/admin/user-profiles/${currentDeleteId}/delete`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(async response => {
                const isJson = response.headers.get('content-type')?.includes('application/json');
                const data = isJson ? await response.json() : null;

                if (!response.ok) {
                    const errorMsg = isJson ? (data.message || 'Error Server') : 'Terjadi kesalahan server (500/404)';
                    throw new Error(errorMsg);
                }
                
                if (!data) {
                    console.error('Server merespon dengan HTML, bukan JSON. Cek Network Tab.');
                    throw new Error('Respon tidak valid (Redirect terdeteksi). User mungkin sudah terhapus.');
                }

                return data;
            })
            .then(data => {
                if (data && data.status === 'success') {
                    showDeleteStatus('success', data.message || 'User berhasil dihapus.');
                    const row = document.querySelector(`tr[data-id="${currentDeleteId}"]`);
                    if (row) row.remove();
                    closeDeleteModal();
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    throw new Error(data?.message || 'Gagal menghapus user');
                }
            })
            .catch(error => {
                console.error('Delete error:', error);
                showDeleteStatus('error', error.message);
                closeDeleteModal();
            })
            .finally(() => {
                deleteBtn.disabled = false;
                deleteBtn.innerHTML = originalText;
            });
        });
    }

    // Bulk delete button
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
if (bulkDeleteBtn) {
    bulkDeleteBtn.addEventListener('click', function() {
        // Ambil ID, pastikan dikonversi ke Integer jika perlu
        const selectedIds = Array.from(document.querySelectorAll('.user-checkbox:checked'))
            .map(cb => parseInt(cb.value)); 

        if (selectedIds.length === 0) {
            alert('Pilih minimal satu user untuk dihapus.');
            return;
        }

        if (!confirm(`Yakin ingin menghapus ${selectedIds.length} user terpilih?`)) return;

        const originalText = this.innerHTML;
        this.disabled = true;
        this.innerHTML = 'Menghapus...';

        fetch('/admin/user-profiles/bulk-delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ 
                user_ids: selectedIds // Pastikan nama key ini SAMA dengan di Controller
            })
        })
        .then(async response => {
            const data = await response.json();
            if (!response.ok) {
                // Jika error 422, tampilkan pesan validasi dari Laravel
                if (response.status === 422) {
                    console.error('Validation Errors:', data.errors);
                    throw new Error(data.message || "Data yang dikirim tidak valid");
                }
                throw new Error(data.message || "Server Error");
            }
            return data;
        })
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
                window.location.reload();
            } else {
                alert('Gagal: ' + data.message);
            }
        })
        .catch(err => {
            console.error('Bulk Delete Error:', err);
            alert('Terjadi kesalahan: ' + err.message);
        })
        .finally(() => {
            this.disabled = false;
            this.innerHTML = originalText;
        });
    });
}
});
</script>
<script src="{{ asset('assets/js/bundle.js') }}"></script>
@endsection