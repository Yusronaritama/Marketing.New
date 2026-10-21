@extends('layouts.admin.app')

@section('title', 'Buat Postingan Baru')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Buat Postingan Baru</h1>
            <nav class="flex mt-2" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('admin.dashboard.index') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                            <i class="fas fa-home mr-2"></i>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <a href="{{ route('admin.posts.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">Postingan</a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400">Buat Baru</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
        <div class="mt-4 md:mt-0">
            <a href="{{ route('admin.posts.index') }}" class="flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali
            </a>
        </div>
    </div>

    <!-- Stats Cards - Improved Flex Layout -->
    <div class="flex flex-col sm:flex-row gap-4 overflow-x-auto pb-2">
        <!-- Total Posts -->
        <div class="flex-1 min-w-[200px] bg-white rounded-xl p-4 shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center dark:bg-blue-900/50">
                        <i class="fas fa-file-alt text-blue-600 text-lg dark:text-blue-400"></i>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white">1</p>
                </div>
            </div>
        </div>

        <!-- Published Posts -->
        <div class="flex-1 min-w-[200px] bg-white rounded-xl p-4 shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center dark:bg-green-900/50">
                        <i class="fas fa-check-circle text-green-600 text-lg dark:text-green-400"></i>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Diterbitkan</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white">1</p>
                </div>
            </div>
        </div>

        <!-- Draft Posts -->
        <div class="flex-1 min-w-[200px] bg-white rounded-xl p-4 shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center dark:bg-yellow-900/50">
                        <i class="fas fa-pencil-alt text-yellow-600 text-lg dark:text-yellow-400"></i>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Draft</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white">0</p>
                </div>
            </div>
        </div>

        <!-- This Month -->
        <div class="flex-1 min-w-[200px] bg-white rounded-xl p-4 shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center dark:bg-purple-900/50">
                        <i class="fas fa-calendar-alt text-purple-600 text-lg dark:text-purple-400"></i>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Bulan Ini</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white">1</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Form -->
        <div class="lg:col-span-2">
            <form action="{{ route('admin.posts.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                
                <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                    <!-- Title -->
                    <div class="space-y-2">
                        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Judul Postingan <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="title" 
                            name="title" 
                            value="{{ old('title') }}"
                            class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm placeholder-gray-500 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                            placeholder="Masukkan judul postingan..."
                            required
                        >
                        @error('title')
                            <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Content -->
                    <div class="mt-6 space-y-2">
                        <label for="content" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Konten <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            id="content" 
                            name="content" 
                            rows="15"
                            class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm placeholder-gray-500 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                            placeholder="Tulis konten postingan di sini..."
                            required
                        >{{ old('content') }}</textarea>
                        @error('content')
                            <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Publish Card -->
            <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Status Publikasi</h3>
                
                <div class="space-y-4">
                    <!-- Status -->
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                        <div class="space-y-2">
                            <div class="flex items-center">
                                <input 
                                    type="radio" 
                                    id="status-draft" 
                                    name="status" 
                                    value="draft" 
                                    {{ old('status', 'draft') == 'draft' ? 'checked' : '' }}
                                    class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700"
                                >
                                <label for="status-draft" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300">
                                        <i class="fas fa-pencil-alt mr-1"></i> Draft
                                    </span>
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input 
                                    type="radio" 
                                    id="status-published" 
                                    name="status" 
                                    value="published" 
                                    {{ old('status') == 'published' ? 'checked' : '' }}
                                    class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700"
                                >
                                <label for="status-published" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300">
                                        <i class="fas fa-check mr-1"></i> Terbitkan Sekarang
                                    </span>
                                </label>
                            </div>
                        </div>
                        @error('status')
                            <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Info Box -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 dark:bg-blue-900/20 dark:border-blue-800">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-2"></i>
                            <div class="text-xs text-blue-700 dark:text-blue-300">
                                <p class="font-medium">Info Publikasi:</p>
                                <p>Pilih "Terbitkan Sekarang" untuk langsung mempublikasikan postingan, atau "Draft" untuk menyimpannya sementara.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="pt-4 border-t border-gray-200 dark:border-gray-600">
                        <button type="submit" class="w-full flex justify-center items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium dark:text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-save mr-2"></i>
                            Simpan Postingan
                        </button>
                    </div>
                </div>
            </div>

            <!-- Featured Image Card -->
            <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Gambar Unggulan</h3>
                
                <div class="space-y-4">
                    <!-- Image Preview -->
                    <div id="image-preview" class="{{ old('image') ? '' : 'hidden' }}">
                        <img id="preview" class="w-full h-48 object-cover rounded-lg border border-gray-200 dark:border-gray-600">
                    </div>
                    
                    <!-- Upload Area -->
                    <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-4 text-center hover:border-gray-400 dark:hover:border-gray-500 transition-colors">
                        <input 
                            type="file" 
                            id="image" 
                            name="image" 
                            accept="image/*"
                            class="hidden"
                            onchange="previewImage(this)"
                        >
                        <label for="image" class="cursor-pointer">
                            <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                <span class="font-medium text-blue-600 hover:text-blue-500">Upload gambar</span> atau drag & drop
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                PNG, JPG, GIF hingga 2MB
                            </p>
                        </label>
                    </div>
                    @error('image')
                        <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Image preview
    function previewImage(input) {
        const preview = document.getElementById('preview');
        const previewContainer = document.getElementById('image-preview');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                previewContainer.classList.remove('hidden');
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Meta description counter
    document.addEventListener('DOMContentLoaded', function() {
        const metaDescription = document.getElementById('meta_description');
        const metaCounter = document.getElementById('meta-counter');
        
        if (metaDescription && metaCounter) {
            // Set initial counter value
            metaCounter.textContent = metaDescription.value.length;
            
            metaDescription.addEventListener('input', function() {
                metaCounter.textContent = this.value.length;
                
                if (this.value.length > 160) {
                    metaCounter.classList.add('text-red-600');
                } else {
                    metaCounter.classList.remove('text-red-600');
                }
            });
        }

        // Form validation
        const form = document.querySelector('form');
        
        if (form) {
            form.addEventListener('submit', function(e) {
                // Basic validation
                const title = document.getElementById('title');
                const content = document.getElementById('content');
                const status = document.querySelector('input[name="status"]:checked');
                
                let isValid = true;
                
                // Validate title
                if (!title.value.trim()) {
                    showError(title, 'Judul postingan harus diisi');
                    isValid = false;
                } else {
                    clearError(title);
                }
                
                // Validate content
                if (!content.value.trim()) {
                    showError(content, 'Konten postingan harus diisi');
                    isValid = false;
                } else {
                    clearError(content);
                }
                
                // Validate status
                if (!status) {
                    showError(document.querySelector('input[name="status"]'), 'Status harus dipilih');
                    isValid = false;
                } else {
                    clearError(document.querySelector('input[name="status"]'));
                }
                
                if (!isValid) {
                    e.preventDefault();
                    // Scroll to first error
                    const firstError = document.querySelector('.error-message');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            });
        }
        
        function showError(element, message) {
            // Remove existing error
            clearError(element);
            
            // Add error class to input
            element.classList.add('border-red-500');
            
            // Create error message
            const errorElement = document.createElement('p');
            errorElement.className = 'text-sm text-red-600 dark:text-red-400 error-message mt-1';
            errorElement.textContent = message;
            
            // Insert after input
            element.parentNode.appendChild(errorElement);
        }
        
        function clearError(element) {
            element.classList.remove('border-red-500');
            const existingError = element.parentNode.querySelector('.error-message');
            if (existingError) {
                existingError.remove();
            }
        }

        // Auto-slug generation (optional)
        document.getElementById('title')?.addEventListener('blur', function() {
            // You can implement auto-slug generation here if needed
            console.log('Title changed:', this.value);
        });
    });
</script>
@endsection

<script src="{{ asset('assets/js/bundle.js') }}"></script>