@extends('layouts.admin.app')

@section('title', 'Detail Group Gelombang')

@push('styles')
@endpush

@section('content')
<div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
    <div class="flex flex-col gap-5.5 p-6.5">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-black dark:text-white">
                    Detail Group Gelombang
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Informasi lengkap group gelombang
                </p>
            </div>
            
            <a href="{{ route('admin.group-gelombang.index') }}" 
               class="inline-flex items-center justify-center rounded-md border border-stroke px-4 py-2.5 text-sm font-medium hover:bg-gray-100 dark:border-strokedark dark:hover:bg-meta-4">
                Kembali
            </a>
        </div>

        <!-- Detail Card -->
        <div class="grid grid-cols-1 gap-5.5 md:grid-cols-2">
            <!-- Informasi Gelombang -->
            <div class="space-y-4">
                <div class="rounded-lg border border-stroke bg-white p-4 dark:border-strokedark dark:bg-boxdark">
                    <h3 class="mb-4 text-lg font-medium text-black dark:text-white">
                        Informasi Gelombang
                    </h3>
                    
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Gelombang</span>
                            <span class="text-lg font-semibold text-primary">{{ $groupGelombang->gelombang }}</span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Urutan</span>
                            <span class="font-medium text-black dark:text-white">{{ $groupGelombang->urutan }}</span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Status</span>
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {{ $groupGelombang->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' }}">
                                {{ $groupGelombang->is_active ? 'Aktif' : 'Tidak Aktif' }}
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Link Group -->
                <div class="rounded-lg border border-stroke bg-white p-4 dark:border-strokedark dark:bg-boxdark">
                    <h3 class="mb-4 text-lg font-medium text-black dark:text-white">
                        Link Group
                    </h3>
                    
                    <div class="space-y-3">
                        <div>
                            <span class="text-sm text-gray-500 dark:text-gray-400">Tipe Group</span>
                            <div class="mt-1">
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium {{ $groupGelombang->warna_tipe }}">
                                    {{ ucfirst($groupGelombang->tipe_group) }}
                                </span>
                            </div>
                        </div>
                        
                        <div>
                            <span class="text-sm text-gray-500 dark:text-gray-400">Link</span>
                            <div class="mt-1 flex items-center space-x-2">
                                @if($groupGelombang->tipe_group == 'whatsapp')
                                <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                                @elseif($groupGelombang->tipe_group == 'telegram')
                                <svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                                </svg>
                                @endif
                                <a href="{{ $groupGelombang->link_group }}" 
                                   target="_blank"
                                   class="text-primary hover:underline truncate">
                                    {{ $groupGelombang->short_link }}
                                </a>
                            </div>
                        </div>
                        
                        <div class="pt-2">
                            <a href="{{ $groupGelombang->link_group }}" 
                               target="_blank"
                               class="inline-flex w-full items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-opacity-90">
                                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                                Buka Group
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Informasi Detail -->
            <div class="space-y-4">
                <div class="rounded-lg border border-stroke bg-white p-4 dark:border-strokedark dark:bg-boxdark">
                    <h3 class="mb-4 text-lg font-medium text-black dark:text-white">
                        Nama Gelombang
                    </h3>
                    
                    <div class="flex items-center space-x-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10">
                            <span class="text-xl font-bold text-primary">{{ $groupGelombang->gelombang }}</span>
                        </div>
                        <div>
                            <h4 class="text-xl font-bold text-black dark:text-white">
                                {{ $groupGelombang->nama_gelombang }}
                            </h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $groupGelombang->nama_lengkap }}
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Deskripsi -->
                <div class="rounded-lg border border-stroke bg-white p-4 dark:border-strokedark dark:bg-boxdark">
                    <h3 class="mb-4 text-lg font-medium text-black dark:text-white">
                        Deskripsi
                    </h3>
                    
                    <div class="prose max-w-none dark:prose-invert">
                        @if($groupGelombang->deskripsi)
                            {{ $groupGelombang->deskripsi }}
                        @else
                            <p class="text-gray-500 dark:text-gray-400 italic">Tidak ada deskripsi</p>
                        @endif
                    </div>
                </div>
                
                <!-- Informasi Tambahan -->
                <div class="rounded-lg border border-stroke bg-white p-4 dark:border-strokedark dark:bg-boxdark">
                    <h3 class="mb-4 text-lg font-medium text-black dark:text-white">
                        Informasi Tambahan
                    </h3>
                    
                    <div class="space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Dibuat</span>
                            <span class="text-black dark:text-white">{{ $groupGelombang->created_at->format('d M Y, H:i') }}</span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Diperbarui</span>
                            <span class="text-black dark:text-white">{{ $groupGelombang->updated_at->format('d M Y, H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tombol Aksi -->
        <div class="flex justify-end gap-3 pt-6 border-t border-stroke dark:border-strokedark">
            <a href="{{ route('admin.group-gelombang.edit', $groupGelombang->id) }}" 
               class="inline-flex items-center justify-center rounded-md border border-stroke px-6 py-3 text-sm font-medium hover:bg-gray-100 dark:border-strokedark dark:hover:bg-meta-4">
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit
            </a>
            
            <form action="{{ route('admin.group-gelombang.destroy', $groupGelombang->id) }}" 
                  method="POST" 
                  class="inline delete-form">
                @csrf
                @method('DELETE')
                <button type="button" 
                        class="inline-flex items-center justify-center rounded-md bg-danger px-6 py-3 text-sm font-medium text-white hover:bg-opacity-90 delete-btn">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Hapus
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Konfirmasi hapus
    document.querySelector('.delete-btn').addEventListener('click', function() {
        const form = this.closest('form');
        
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Group gelombang akan dihapus secara permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>
@endpush