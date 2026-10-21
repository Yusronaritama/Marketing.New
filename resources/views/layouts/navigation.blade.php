<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PMB Online') - Universitas Buana Perjuangan</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Frameworks --}}
    @if (app()->environment('local', 'development'))
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    {{-- Global Styles --}}
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', sans-serif; opacity: 0; }

        /* Glassmorphism Utilities */
        .glass-blue {
            background: rgba(30, 64, 175, 0.2);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .glass-nav-blue {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        .user-dropdown-glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
    </style>
    
    @stack('styles')

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.body.style.transition = 'opacity 0.5s ease-in-out';
            document.body.style.opacity = '1'; 

            const links = document.querySelectorAll('a');
            links.forEach(link => {
                link.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    // Jangan fade out jika href kosong, #, atau javascript
                    if (!href || href === '#' || href.startsWith('javascript') || this.target === '_blank') return;
                    
                    e.preventDefault();
                    document.body.style.opacity = '0'; 
                    setTimeout(() => { window.location.href = href; }, 400);
                });
            });
        });
    </script>
</head>

<body class="antialiased text-gray-100 bg-[#0f172a] min-h-screen flex flex-col relative overflow-x-hidden">

    {{-- SETUP VARIABEL USER --}}
    @php
        $user = Auth::user();
        $userName = $user ? $user->name : 'Guest';
        $userEmail = $user ? $user->email : '-';
        $userAvatar = $user ? $user->profile_photo_path : null; 
        $initials = strtoupper(substr($userName, 0, 1));
        
        $avatarUrl = null;
        if ($userAvatar) {
            if (strpos($userAvatar, 'http') === 0) {
                $avatarUrl = $userAvatar;
            } else {
                $avatarUrl = asset('storage/' . $userAvatar);
            }
        }
    @endphp

    {{-- Background --}}
    <div class="fixed inset-0 -z-10 w-full h-full">
        <img src="{{ asset('images/Rektorat.jpg') }}" alt="Background" class="absolute inset-0 w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-900/85 to-indigo-900/90"></div>
    </div>

    {{-- Navbar --}}
    <nav class="sticky top-0 z-50 w-full transition-all duration-300 border-b border-white/10"
         x-data="{ open: false, scrolled: false, dropdownOpen: false }" 
         @scroll.window="scrolled = (window.pageYOffset > 20)"
         :class="scrolled ? 'bg-blue-900/90 backdrop-blur-xl shadow-lg' : 'bg-transparent'">
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="flex justify-between h-24 items-center">
                
                {{-- LOGO --}}
                <div class="flex-shrink-0 flex items-center">
                    <a href="{{ route('mahasiswa.dashboard') }}" class="flex items-center gap-3 group">
                        <img src="{{ asset('images/Logo-UBP-Karawang-1.png') }}" alt="Logo" 
                             class="h-10 w-auto drop-shadow-md transition-transform duration-300 group-hover:scale-110">
                        <div class="hidden xl:block leading-tight">
                            <h1 class="font-bold text-white text-lg tracking-tight">PMB Online</h1>
                            <p class="text-[10px] text-blue-200 font-medium uppercase tracking-widest">Univ. Buana Perjuangan</p>
                        </div>
                    </a>
                </div>

                {{-- MENU TENGAH --}}
                <div class="hidden md:flex absolute left-1/2 top-1/2 transform -translate-x-1/2 -translate-y-1/2 
                            bg-white/10 backdrop-blur-md border border-white/20 rounded-full p-1.5 shadow-xl gap-1">
                    <a href="{{ route('mahasiswa.dashboard') }}" 
                       class="px-6 py-2.5 rounded-full text-sm font-medium transition-all duration-300
                       {{ request()->routeIs('mahasiswa.dashboard') ? 'bg-blue-600 text-white shadow-lg' : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('mahasiswa.biodata.create') }}" 
                       class="px-6 py-2.5 rounded-full text-sm font-medium transition-all duration-300
                       {{ request()->routeIs('mahasiswa.biodata.*') ? 'bg-blue-600 text-white shadow-lg' : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                        Biodata
                    </a>
                    <a href="{{ route('mahasiswa.chat.index') }}" 
                       class="px-6 py-2.5 rounded-full text-sm font-medium transition-all duration-300
                       {{ request()->routeIs('mahasiswa.chat.*') ? 'bg-blue-600 text-white shadow-lg' : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                        Pesan
                    </a>
                </div>

                {{-- KANAN: PROFIL --}}
                <div class="flex items-center gap-4">
                    <div class="relative hidden md:block">
                        <button @click="dropdownOpen = !dropdownOpen" @click.away="dropdownOpen = false"
                                class="flex items-center gap-3 pl-4 pr-1 py-1 rounded-full bg-white/5 hover:bg-white/10 border border-white/10 transition-all group">
                            <div class="text-right">
                                <p class="text-xs font-bold text-white group-hover:text-blue-100 transition">{{ $userName }}</p>
                            </div>
                            <div class="h-9 w-9 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold shadow-md ring-2 ring-white/20 overflow-hidden">
                                @if($avatarUrl)
                                    <img src="{{ $avatarUrl }}" alt="Avatar" class="h-full w-full object-cover">
                                @else
                                    {{ $initials }}
                                @endif
                            </div>
                        </button>

                        {{-- Dropdown Menu --}}
                        <div x-show="dropdownOpen"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             class="absolute right-0 mt-3 w-56 origin-top-right bg-white/95 backdrop-blur-xl rounded-2xl shadow-2xl ring-1 ring-black/5 focus:outline-none py-2 z-[60]"
                             style="display: none;">
                            
                            <div class="px-4 py-3 border-b border-gray-100">
                                <p class="text-xs text-gray-500">Akun Terdaftar</p>
                                <p class="text-sm font-bold text-gray-800 truncate">{{ $userEmail }}</p>
                            </div>

                            <a href="#" class="block px-4 py-2 text-sm text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition rounded-lg mx-2">
                                Profil Saya
                            </a>
                            
                            {{-- FORM LOGOUT (METODE BUTTON) --}}
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                {{-- Ganti <a> dengan <button> agar browser dipaksa mengirim POST --}}
                                <button type="submit" class="w-full text-left block px-4 py-2 text-sm text-red-500 hover:bg-red-50 hover:text-red-600 font-medium transition rounded-lg mx-2">
                                    Keluar Aplikasi
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Mobile Toggle --}}
                    <div class="flex items-center md:hidden">
                        <button @click="open = !open" class="p-2 rounded-full text-white bg-white/10 hover:bg-white/20 transition border border-white/10">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path x-show="!open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                <path x-show="open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- MOBILE MENU --}}
        <div x-show="open" x-collapse class="md:hidden bg-blue-900/95 backdrop-blur-xl border-t border-white/10 shadow-xl">
            <div class="px-4 py-6 space-y-3">
                <a href="{{ route('mahasiswa.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-base font-medium {{ request()->routeIs('mahasiswa.dashboard') ? 'bg-blue-600 text-white' : 'text-blue-100 hover:bg-white/10' }}">
                    <i class="fas fa-home w-5"></i> Dashboard
                </a>
                <a href="{{ route('mahasiswa.biodata.create') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-base font-medium {{ request()->routeIs('mahasiswa.biodata.*') ? 'bg-blue-600 text-white' : 'text-blue-100 hover:bg-white/10' }}">
                    <i class="fas fa-user-edit w-5"></i> Biodata Diri
                </a>
                <a href="{{ route('mahasiswa.chat.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-base font-medium {{ request()->routeIs('mahasiswa.chat.*') ? 'bg-blue-600 text-white' : 'text-blue-100 hover:bg-white/10' }}">
                    <i class="fas fa-comments w-5"></i> Pesan Bantuan
                </a>
                
                <div class="border-t border-white/10 my-4 pt-4">
                    <div class="px-4 flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold shadow-lg overflow-hidden">
                            @if($avatarUrl)
                                <img src="{{ $avatarUrl }}" alt="Avatar" class="h-full w-full object-cover">
                            @else
                                {{ $initials }}
                            @endif
                        </div>
                        <div>
                            <p class="text-white font-bold">{{ $userName }}</p>
                            <p class="text-xs text-blue-300">{{ $userEmail }}</p>
                        </div>
                    </div>
                    
                    {{-- FORM LOGOUT MOBILE (METODE BUTTON) --}}
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-3 rounded-2xl text-base font-medium text-red-300 hover:bg-red-900/30 hover:text-red-200 flex items-center gap-3">
                            <i class="fas fa-sign-out-alt w-5"></i> Keluar Aplikasi
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-grow relative z-10 py-8 px-4 sm:px-6 lg:px-8">
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-transition 
                 class="max-w-7xl mx-auto mb-6 glass-blue rounded-2xl p-4 border-l-4 border-white flex justify-between items-center shadow-lg">
                <div class="flex items-center">
                    <div class="bg-white/20 rounded-full p-2 mr-3 text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <p class="text-white font-medium">{{ session('success') }}</p>
                </div>
                <button @click="show = false" class="text-white/70 hover:text-white">&times;</button>
            </div>
        @endif
        @yield('content')
    </main>

    <footer class="relative z-10 mt-auto bg-transparent pt-6">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center border-t border-white/20 pt-6">
            <p class="text-sm text-blue-200">
                &copy; {{ date('Y') }} <span class="font-semibold text-white">Universitas Buana Perjuangan</span>.
            </p>
            <div class="flex space-x-4 mt-4 md:mt-0">
                <a href="#" class="text-blue-200 hover:text-white transition text-sm">Bantuan</a>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>