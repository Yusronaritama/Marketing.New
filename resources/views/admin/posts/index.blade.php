@extends('layouts.admin.app')

@section('title', 'Manajemen Postingan')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Manajemen Postingan</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Kelola semua postingan blog Anda di sini</p>
        </div>
        <div class="mt-4 md:mt-0">
            <a href="{{ route('admin.posts.create') }}" class="flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200">
                <i class="fas fa-plus mr-2"></i>
                Buat Postingan
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <!-- Total Posts -->
        <div class="rounded-lg bg-white p-4 shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $posts->total() }}</p>
                </div>
                <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900/50">
                    <i class="fas fa-newspaper text-blue-600 dark:text-blue-400"></i>
                </div>
            </div>
        </div>

        <!-- Published Posts -->
        <div class="rounded-lg bg-white p-4 shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Diterbitkan</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-white">
                        {{ \App\Models\Post::where('status', 'published')->count() }}
                    </p>
                </div>
                <div class="rounded-lg bg-green-100 p-2 dark:bg-green-900/50">
                    <i class="fas fa-check-circle text-green-600 dark:text-green-400"></i>
                </div>
            </div>
        </div>

        <!-- Draft Posts -->
        <div class="rounded-lg bg-white p-4 shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Draft</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-white">
                        {{ \App\Models\Post::where('status', 'draft')->count() }}
                    </p>
                </div>
                <div class="rounded-lg bg-yellow-100 p-2 dark:bg-yellow-900/50">
                    <i class="fas fa-pencil-alt text-yellow-600 dark:text-yellow-400"></i>
                </div>
            </div>
        </div>

        <!-- This Month -->
        <div class="rounded-lg bg-white p-4 shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Bulan Ini</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-white">
                        {{ \App\Models\Post::whereMonth('created_at', now()->month)->count() }}
                    </p>
                </div>
                <div class="rounded-lg bg-purple-100 p-2 dark:bg-purple-900/50">
                    <i class="fas fa-calendar text-purple-600 dark:text-purple-400"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
        <div class="flex flex-col space-y-4 md:flex-row md:items-center md:justify-between md:space-y-0">
            <div class="flex-1">
                <form action="{{ route('admin.posts.index') }}" method="GET">
                    <div class="relative max-w-md">
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ request('search') }}"
                            placeholder="Cari postingan..." 
                            class="block w-full rounded-lg border border-gray-300 bg-white py-2 pl-10 pr-4 text-sm placeholder-gray-500 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                        >
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                </form>
            </div>
            <div class="flex space-x-3">
                <div class="relative">
                    <select name="status" class="appearance-none rounded-lg border border-gray-300 bg-white py-2 pl-3 pr-8 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Semua Status</option>
                        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Diterbitkan</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-300">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>
                <button type="submit" class="flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                    <i class="fas fa-filter mr-2"></i>
                    Filter
                </button>
            </div>
        </div>
    </div>

    <!-- Posts Table -->
    <div class="rounded-xl bg-white shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Postingan
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Tags
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Penulis
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Tanggal
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                    @forelse($posts as $post)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                @if($post->image)
                                <div class="h-10 w-10 flex-shrink-0">
                                    <img class="h-10 w-10 rounded-lg object-cover" src="{{ Storage::url($post->image) }}" alt="{{ $post->title }}">
                                </div>
                                @else
                                <div class="h-10 w-10 flex-shrink-0">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100 text-gray-400 dark:bg-gray-700">
                                        <i class="fas fa-newspaper"></i>
                                    </div>
                                </div>
                                @endif
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        <a href="{{ route('admin.posts.show', $post) }}" class="hover:text-blue-600 dark:hover:text-blue-400">
                                            {{ Str::limit($post->title, 50) }}
                                        </a>
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ Str::limit(strip_tags($post->content), 70) }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($post->tags)
                                <div class="flex flex-wrap gap-1">
                                    @foreach($post->tags_array as $tag)
                                        <span class="inline-flex rounded-full bg-gray-100 px-2 text-xs font-semibold leading-5 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                            {{ trim($tag) }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-xs text-gray-400 dark:text-gray-500">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($post->effective_status === 'published')
                                <span class="inline-flex rounded-full bg-green-100 px-2 text-xs font-semibold leading-5 text-green-800 dark:bg-green-900/50 dark:text-green-300">
                                    <i class="fas fa-check mr-1"></i> Diterbitkan
                                </span>
                            @elseif($post->effective_status === 'scheduled')
                                <span class="inline-flex rounded-full bg-blue-100 px-2 text-xs font-semibold leading-5 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300">
                                    <i class="fas fa-clock mr-1"></i> Terjadwal
                                </span>
                            @elseif($post->effective_status === 'expired')
                                <span class="inline-flex rounded-full bg-gray-100 px-2 text-xs font-semibold leading-5 text-gray-800 dark:bg-gray-900/50 dark:text-gray-300">
                                    <i class="fas fa-history mr-1"></i> Kedaluwarsa
                                </span>
                            @else
                                <span class="inline-flex rounded-full bg-yellow-100 px-2 text-xs font-semibold leading-5 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300">
                                    <i class="fas fa-pencil-alt mr-1"></i> Draft
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            {{ $post->author->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            {{ $post->created_at->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end">
                                <!-- Dropdown Actions -->
                                <div class="relative" x-data="{ open: false }">
                                    <button 
                                        @click="open = !open"
                                        class="flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600 transition-colors duration-200"
                                    >
                                        <i class="fas fa-ellipsis-v text-gray-500 dark:text-gray-400 text-sm"></i>
                                    </button>
                                    
                                    <!-- Dropdown Menu -->
                                    <div 
                                        x-show="open" 
                                        @click.away="open = false"
                                        x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="transform opacity-0 scale-95"
                                        x-transition:enter-end="transform opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-75"
                                        x-transition:leave-start="transform opacity-100 scale-100"
                                        x-transition:leave-end="transform opacity-0 scale-95"
                                        class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none dark:bg-gray-800 dark:ring-gray-700"
                                    >
                                        <div class="py-1">
                                            <!-- View Action -->
                                            <a 
                                                href="{{ route('admin.posts.show', $post) }}" 
                                                class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
                                            >
                                                <i class="fas fa-eye mr-3 text-blue-500"></i>
                                                Detail
                                            </a>
                                            
                                            <!-- Edit Action -->
                                            <a 
                                                href="{{ route('admin.posts.edit', $post) }}" 
                                                class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
                                            >
                                                <i class="fas fa-edit mr-3 text-green-500"></i>
                                                Edit
                                            </a>
                                            
                                            <!-- Publish/Unpublish Action -->
                                            @if($post->status == 'published')
                                                <form action="{{ route('admin.posts.update', $post) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="status" value="draft">
                                                    <button type="submit" class="w-full text-left">
                                                        <span class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                                                            <i class="fas fa-eye-slash mr-3 text-yellow-500"></i>
                                                            Jadikan Draft
                                                        </span>
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('admin.posts.update', $post) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="status" value="published">
                                                    <button type="submit" class="w-full text-left">
                                                        <span class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                                                            <i class="fas fa-check mr-3 text-green-500"></i>
                                                            Terbitkan
                                                        </span>
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            <!-- Delete Action -->
                                            <form action="{{ route('admin.posts.destroy', $post) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button 
                                                    type="submit" 
                                                    onclick="return confirm('Apakah Anda yakin ingin menghapus postingan ini?')"
                                                    class="w-full text-left"
                                                >
                                                    <span class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-gray-100 dark:text-red-400 dark:hover:bg-gray-700">
                                                        <i class="fas fa-trash mr-3"></i>
                                                        Hapus
                                                    </span>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                                <i class="fas fa-newspaper text-4xl mb-4 text-gray-300 dark:text-gray-600"></i>
                                <p class="text-lg font-medium">Belum ada postingan</p>
                                <p class="mt-1 text-sm">Mulai dengan membuat postingan pertama Anda</p>
                                <a href="{{ route('admin.posts.create') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200">
                                    <i class="fas fa-plus mr-2"></i>
                                    Buat Postingan
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($posts->hasPages())
        <div class="border-t border-gray-200 px-6 py-4 dark:border-gray-700">
            {{ $posts->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

<script>
    // Function untuk toggle status publish/draft
    function togglePostStatus(postId, currentStatus) {
        if (confirm(`Apakah Anda yakin ingin ${currentStatus === 'published' ? 'menjadikan draft' : 'menerbitkan'} postingan ini?`)) {
            const form = document.getElementById(`status-form-${postId}`);
            form.submit();
        }
    }

    // Function untuk quick actions
    document.addEventListener('DOMContentLoaded', function() {
        // Quick view modal (jika diperlukan)
        const quickViewButtons = document.querySelectorAll('.quick-view');
        quickViewButtons.forEach(button => {
            button.addEventListener('click', function() {
                const postId = this.dataset.postId;
                // Implement quick view modal here
                console.log('Quick view for post:', postId);
            });
        });
    });
</script>
<script src="{{ asset('assets/js/bundle.js') }}"></script>