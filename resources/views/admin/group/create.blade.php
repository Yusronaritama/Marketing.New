@extends('layouts.admin.app')

@section('title', 'Edit Group Gelombang')

@push('styles')
@endpush

@section('content')
<div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
    <div class="flex flex-col gap-5.5 p-6.5">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-black dark:text-white">
                    Edit Group Gelombang
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Edit informasi group gelombang
                </p>
            </div>
            
            <a href="{{ route('admin.group-gelombang.index') }}" 
               class="inline-flex items-center justify-center rounded-md border border-stroke px-4 py-2.5 text-sm font-medium hover:bg-gray-100 dark:border-strokedark dark:hover:bg-meta-4">
                Kembali
            </a>
        </div>

        <!-- Form -->
        <form action="{{ route('admin.group-gelombang.update', $groupGelombang->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 gap-5.5 md:grid-cols-2">
                <!-- Gelombang -->
                <div>
                    <label class="mb-3 block text-sm font-medium text-black dark:text-white">
                        Gelombang <span class="text-danger">*</span>
                    </label>
                    <input type="number" 
                           name="gelombang" 
                           value="{{ old('gelombang', $groupGelombang->gelombang) }}"
                           min="1"
                           max="10"
                           placeholder="1-10"
                           class="w-full rounded-lg border border-stroke bg-transparent px-5 py-3 text-black outline-none transition focus:border-primary dark:border-strokedark dark:bg-form-input dark:text-white dark:focus:border-primary"
                           required>
                    @error('gelombang')
                        <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Urutan -->
                <div>
                    <label class="mb-3 block text-sm font-medium text-black dark:text-white">
                        Urutan <span class="text-danger">*</span>
                    </label>
                    <input type="number" 
                           name="urutan" 
                           value="{{ old('urutan', $groupGelombang->urutan) }}"
                           min="1"
                           max="100"
                           placeholder="Urutan tampilan"
                           class="w-full rounded-lg border border-stroke bg-transparent px-5 py-3 text-black outline-none transition focus:border-primary dark:border-strokedark dark:bg-form-input dark:text-white dark:focus:border-primary"
                           required>
                    @error('urutan')
                        <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Nama Gelombang -->
                <div class="md:col-span-2">
                    <label class="mb-3 block text-sm font-medium text-black dark:text-white">
                        Nama Gelombang <span class="text-danger">*</span>
                    </label>
                    <input type="text" 
                           name="nama_gelombang" 
                           value="{{ old('nama_gelombang', $groupGelombang->nama_gelombang) }}"
                           placeholder="Contoh: Gelombang 1 - Early Bird"
                           class="w-full rounded-lg border border-stroke bg-transparent px-5 py-3 text-black outline-none transition focus:border-primary dark:border-strokedark dark:bg-form-input dark:text-white dark:focus:border-primary"
                           required>
                    @error('nama_gelombang')
                        <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Deskripsi -->
                <div class="md:col-span-2">
                    <label class="mb-3 block text-sm font-medium text-black dark:text-white">
                        Deskripsi (Opsional)
                    </label>
                    <textarea name="deskripsi" 
                              rows="3"
                              placeholder="Deskripsi tentang gelombang ini"
                              class="w-full rounded-lg border border-stroke bg-transparent px-5 py-3 text-black outline-none transition focus:border-primary dark:border-strokedark dark:bg-form-input dark:text-white dark:focus:border-primary">{{ old('deskripsi', $groupGelombang->deskripsi) }}</textarea>
                    @error('deskripsi')
                        <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Tipe Group -->
                <div>
                    <label class="mb-3 block text-sm font-medium text-black dark:text-white">
                        Tipe Group <span class="text-danger">*</span>
                    </label>
                    <select name="tipe_group" 
                            class="w-full rounded-lg border border-stroke bg-transparent px-5 py-3 text-black outline-none transition focus:border-primary dark:border-strokedark dark:bg-form-input dark:text-white dark:focus:border-primary"
                            required>
                        <option value="">Pilih Tipe Group</option>
                        <option value="whatsapp" {{ old('tipe_group', $groupGelombang->tipe_group) == 'whatsapp' ? 'selected' : '' }}>WhatsApp Group</option>
                        <option value="telegram" {{ old('tipe_group', $groupGelombang->tipe_group) == 'telegram' ? 'selected' : '' }}>Telegram Group</option>
                        <option value="discord" {{ old('tipe_group', $groupGelombang->tipe_group) == 'discord' ? 'selected' : '' }}>Discord Server</option>
                        <option value="zoom" {{ old('tipe_group', $groupGelombang->tipe_group) == 'zoom' ? 'selected' : '' }}>Zoom Meeting</option>
                        <option value="meet" {{ old('tipe_group', $groupGelombang->tipe_group) == 'meet' ? 'selected' : '' }}>Google Meet</option>
                    </select>
                    @error('tipe_group')
                        <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Link Group -->
                <div>
                    <label class="mb-3 block text-sm font-medium text-black dark:text-white">
                        Link Group <span class="text-danger">*</span>
                    </label>
                    <input type="url" 
                           name="link_group" 
                           value="{{ old('link_group', $groupGelombang->link_group) }}"
                           placeholder="https://chat.whatsapp.com/xxxxxxxx"
                           class="w-full rounded-lg border border-stroke bg-transparent px-5 py-3 text-black outline-none transition focus:border-primary dark:border-strokedark dark:bg-form-input dark:text-white dark:focus:border-primary"
                           required>
                    @error('link_group')
                        <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Status Aktif -->
                <div class="md:col-span-2">
                    <div class="flex items-center space-x-3">
                        <input type="checkbox" 
                               name="is_active" 
                               id="is_active"
                               value="1"
                               class="h-5 w-5 rounded border-stroke bg-transparent"
                               {{ old('is_active', $groupGelombang->is_active) ? 'checked' : '' }}>
                        <label for="is_active" class="text-sm font-medium text-black dark:text-white">
                            Aktifkan gelombang ini
                        </label>
                    </div>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Jika aktif, link akan tersedia untuk peserta
                    </p>
                </div>
            </div>
            
            <!-- Tombol Aksi -->
            <div class="mt-8 flex justify-end gap-3">
                <a href="{{ route('admin.group-gelombang.index') }}" 
                   class="inline-flex items-center justify-center rounded-md border border-stroke px-6 py-3 text-sm font-medium hover:bg-gray-100 dark:border-strokedark dark:hover:bg-meta-4">
                    Batal
                </a>
                <button type="submit" 
                        class="inline-flex items-center justify-center rounded-md bg-primary px-6 py-3 text-sm font-medium text-white hover:bg-opacity-90">
                    Update Gelombang
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validasi form
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const linkGroup = document.querySelector('input[name="link_group"]').value;
        
        if (!linkGroup.startsWith('http://') && !linkGroup.startsWith('https://')) {
            e.preventDefault();
            showToast('Link harus diawali dengan http:// atau https://', 'error');
            return;
        }
    });
    
    // Fungsi toast
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-md shadow-lg text-white ${
            type === 'success' ? 'bg-green-500' : 'bg-red-500'
        }`;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }
});
</script>
@endpush