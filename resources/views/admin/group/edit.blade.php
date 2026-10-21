@extends('layouts.admin.app')

@section('title', 'Edit Group Gelombang')

@section('content')
<div class="mx-auto max-w-(--breakpoint-2xl)">
    <div class="mb-6">
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('admin.group-gelombang.index') }}" class="text-sm font-medium text-gray-700 hover:text-brand-600 dark:text-gray-400 dark:hover:text-white">
                        Group Gelombang
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400">Edit Data</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-dark shadow-sm">
        <div class="border-b border-gray-200 dark:border-gray-800 px-6 py-4">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                Edit Group: {{ $groupGelombang->nama_gelombang }}
            </h2>
        </div>

        <form action="{{ route('admin.group-gelombang.update', $groupGelombang->id) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label class="mb-2.5 block font-medium text-black dark:text-white">
                        Gelombang Ke (1-10) <span class="text-meta-1">*</span>
                    </label>
                    <input type="number" name="gelombang" value="{{ old('gelombang', $groupGelombang->gelombang) }}" 
                           class="w-full rounded-lg border-[1.5px] border-stroke bg-transparent py-3 px-5 font-medium outline-none transition focus:border-brand-500 active:border-brand-500 disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary @error('gelombang') border-red-500 @enderror"
                           placeholder="Contoh: 1" min="1" max="10">
                    @error('gelombang')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2.5 block font-medium text-black dark:text-white">
                        Nama Gelombang <span class="text-meta-1">*</span>
                    </label>
                    <input type="text" name="nama_gelombang" value="{{ old('nama_gelombang', $groupGelombang->nama_gelombang) }}"
                           class="w-full rounded-lg border-[1.5px] border-stroke bg-transparent py-3 px-5 font-medium outline-none transition focus:border-brand-500 active:border-brand-500 dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary @error('nama_gelombang') border-red-500 @enderror"
                           placeholder="Contoh: Gelombang 1 - Early Bird">
                    @error('nama_gelombang')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2.5 block font-medium text-black dark:text-white">
                        Tipe Platform <span class="text-meta-1">*</span>
                    </label>
                    <select name="tipe_group" class="w-full rounded-lg border-[1.5px] border-stroke bg-transparent py-3 px-5 font-medium outline-none transition focus:border-brand-500 active:border-brand-500 dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary">
                        <option value="whatsapp" {{ old('tipe_group', $groupGelombang->tipe_group) == 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                        <option value="telegram" {{ old('tipe_group', $groupGelombang->tipe_group) == 'telegram' ? 'selected' : '' }}>Telegram</option>
                        <option value="discord" {{ old('tipe_group', $groupGelombang->tipe_group) == 'discord' ? 'selected' : '' }}>Discord</option>
                    </select>
                </div>

                <div>
                    <label class="mb-2.5 block font-medium text-black dark:text-white">
                        Urutan Tampil <span class="text-meta-1">*</span>
                    </label>
                    <input type="number" name="urutan" value="{{ old('urutan', $groupGelombang->urutan) }}"
                           class="w-full rounded-lg border-[1.5px] border-stroke bg-transparent py-3 px-5 font-medium outline-none transition focus:border-brand-500 active:border-brand-500 dark:border-form-strokedark dark:bg-form-input @error('urutan') border-red-500 @enderror">
                    @error('urutan')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2.5 block font-medium text-black dark:text-white">
                        Link Group URL <span class="text-meta-1">*</span>
                    </label>
                    <input type="url" name="link_group" value="{{ old('link_group', $groupGelombang->link_group) }}"
                           class="w-full rounded-lg border-[1.5px] border-stroke bg-transparent py-3 px-5 font-medium outline-none transition focus:border-brand-500 active:border-brand-500 dark:border-form-strokedark dark:bg-form-input @error('link_group') border-red-500 @enderror"
                           placeholder="https://chat.whatsapp.com/...">
                    @error('link_group')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2.5 block font-medium text-black dark:text-white">
                        Deskripsi
                    </label>
                    <textarea name="deskripsi" rows="3" 
                              class="w-full rounded-lg border-[1.5px] border-stroke bg-transparent py-3 px-5 font-medium outline-none transition focus:border-brand-500 active:border-brand-500 dark:border-form-strokedark dark:bg-form-input">{{ old('deskripsi', $groupGelombang->deskripsi) }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="flex items-center cursor-pointer select-none">
                        <div class="relative">
                            <input type="checkbox" name="is_active" value="1" class="sr-only" {{ old('is_active', $groupGelombang->is_active) ? 'checked' : '' }}>
                            <div class="block h-8 w-14 rounded-full bg-gray-300 dark:bg-gray-700"></div>
                            <div class="dot absolute left-1 top-1 h-6 w-6 rounded-full bg-white transition-all peer-checked:translate-x-full"></div>
                        </div>
                        <div class="ml-3 font-medium text-black dark:text-white">
                            Aktifkan Group ini (Muncul di pendaftaran)
                        </div>
                    </label>
                </div>
            </div>

            <div class="mt-8 flex items-center justify-end gap-4 border-t border-gray-200 dark:border-gray-800 pt-6">
                <a href="{{ route('admin.group-gelombang.index') }}" 
                   class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-6 py-2.5 text-center font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5 transition">
                    Batal
                </a>
                <button type="submit" 
                        class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-10 py-2.5 text-center font-medium text-white hover:bg-brand-600 transition shadow-lg shadow-brand-500/20">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Styling khusus untuk toggle switch manual jika Tailwind peer belum aktif */
    input:checked ~ .dot {
        transform: translateX(100%);
        background-color: #3B82F6; /* brand-500 */
    }
    input:checked ~ .block {
        background-color: #DBEAFE; /* brand-100 */
    }
</style>
@endpush