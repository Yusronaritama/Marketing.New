<!--ini layout utama/layout dashboard user-->

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PMB Online')</title>

    {{-- Tailwind CSS --}}
    @if (app()->environment('local', 'development'))
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- AlpineJS --}}
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    {{-- Additional Styles --}}
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .body-gradient {
            background-color: #ffffff;
            background-image: none;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
            }
            to {
                opacity: 0;
            }
        }

        .fade-in {
            animation: fadeIn 0.4s ease-out forwards;
        }

        .fade-out {
            animation: fadeOut 0.4s ease-out forwards;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-4px);
            }
        }

        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-slideInUp {
            animation: slideInUp 0.6s ease-out forwards;
        }
    </style>

    {{-- Additional Scripts --}}
    @stack('styles')
</head>

<body class="bg-[#0A1930] text-slate-100 pb-[env(safe-area-inset-bottom)]">
    <div class="min-h-screen flex flex-col relative">
        <!-- UBP Blue Glassmorphism Background -->
        <div class="fixed inset-0 -z-10 w-full h-full pointer-events-none overflow-hidden bg-[#0A1930]">
            <!-- Abstract Glowing Orbs -->
            <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] rounded-full bg-blue-600/30 mix-blend-screen blur-[120px] animate-pulse"></div>
            <div class="absolute bottom-[-10%] right-[-10%] w-[50%] h-[50%] rounded-full bg-cyan-500/20 mix-blend-screen blur-[120px] animate-pulse" style="animation-delay: 2s;"></div>
            <div class="absolute top-[20%] right-[10%] w-[30%] h-[30%] rounded-full bg-indigo-500/30 mix-blend-screen blur-[100px] animate-pulse" style="animation-delay: 4s;"></div>
            <!-- Subtle Grid -->
            <div class="absolute inset-0 bg-[linear-gradient(to_right,#ffffff0a_1px,transparent_1px),linear-gradient(to_bottom,#ffffff0a_1px,transparent_1px)] bg-[size:32px_32px]"></div>
            <!-- Overlay -->
            <div class="absolute inset-0 bg-gradient-to-br from-transparent via-[#0A1930]/50 to-[#030914] opacity-80"></div>
        </div>

        {{-- Navigation Header --}}
        <header class="bg-white shadow-sm sticky top-0 z-50 border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center space-x-8">
                        <a href="/" class="animate-float">
                            <img 
                                class="h-10 w-auto" 
                                src="{{ asset('images/logo_ubp.png') }}" 
                                alt="Logo UBP"
                                onerror="this.onerror=null; this.src='https://ubpkarawang.ac.id/wp-content/uploads/2021/08/Logo-UBP-Karawang.png';"
                            >
                        </a>
                        <nav class="hidden md:flex space-x-6">
                            <a href="{{ route('mahasiswa.dashboard') }}"
                                class="page-link text-gray-600 hover:text-gray-900 font-medium transition-colors duration-200 @if(request()->routeIs('mahasiswa.dashboard')) text-gray-900 font-semibold border-b-2 border-blue-500 px-1 @endif">Dashboard</a>
                            <a href="{{ route('mahasiswa.biodata.create') }}"
                                class="page-link text-gray-600 hover:text-gray-900 font-medium transition-colors duration-200 @if(request()->routeIs('mahasiswa.biodata.*')) text-gray-900 font-semibold border-b-2 border-blue-500 px-1 @endif">Biodata</a>
                        </nav>
                    </div>
                    
                    {{-- User Dropdown --}}
                    <div x-data="{ open: false }" @click.away="open = false" class="relative hidden md:block">
                        <button @click="open = !open" class="flex items-center space-x-3 text-gray-700 font-medium focus:outline-none transition hover:text-blue-600">
                            <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center ring-2 ring-gray-300 ring-offset-2">
                                <svg class="h-5 w-5 text-gray-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10 8a3 3 0 100-6 3 3 0 000 6zM3.465 14.493a1.23 1.23 0 00.41 1.412A9.957 9.957 0 0010 18c2.31 0 4.438-.784 6.131-2.1.43-.333.604-.903.41-1.412A6.998 6.998 0 0010 11.5a6.998 6.998 0 00-6.535 2.993z" />
                                </svg>
                            </div>
                            <span>{{ /** @var \App\Models\User $user */ Auth::user()->name }}</span>
                            <svg :class="{ 'rotate-180': open }" class="w-4 h-4 text-gray-500 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" x-transition class="absolute right-0 mt-2 w-56 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 py-1 z-50" style="display: none;">
                            <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <svg class="w-5 h-5 mr-3 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-5.5-2.5a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0zM10 12a5.99 5.99 0 00-4.793 2.39A6.483 6.483 0 0010 16.5a6.483 6.483 0 004.793-2.11A5.99 5.99 0 0010 12z" clip-rule="evenodd" />
                                </svg>
                                Profil Saya
                            </a>
                            <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <svg class="w-5 h-5 mr-3 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M11.49 3.17c.38-1.56-1.6-2.6-2.47-1.34l-3.854 6.352a1.25 1.25 0 00.98 1.956H8.25v2.852a2.5 2.5 0 004.47 1.34l3.854-6.352a1.25 1.25 0 00-.98-1.956H11.75V3.17z" clip-rule="evenodd" />
                                    <path d="M7.75 3.125a.75.75 0 01.75-.75h2a.75.75 0 010 1.5h-2a.75.75 0 01-.75-.75zM10 10a.75.75 0 000 1.5h2.5a.75.75 0 000-1.5H10zM10.875 14.875a.75.75 0 01.75-.75h.625a.75.75 0 010 1.5h-.625a.75.75 0 01-.75-.75z" />
                                </svg>
                                Pengaturan
                            </a>
                            <div class="border-t border-gray-100 my-1"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex items-center w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 hover:text-red-800">
                                    <svg class="w-5 h-5 mr-3 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M3 4.25A2.25 2.25 0 015.25 2h5.5A2.25 2.25 0 0113 4.25v2a.75.75 0 01-1.5 0v-2a.75.75 0 00-.75-.75h-5.5a.75.75 0 00-.75.75v11.5c0 .414.336.75.75.75h5.5a.75.75 0 00.75-.75v-2a.75.75 0 011.5 0v2A2.25 2.25 0 0110.75 18h-5.5A2.25 2.25 0 013 15.75V4.25z" clip-rule="evenodd" />
                                        <path fill-rule="evenodd" d="M6 10a.75.75 0 01.75-.75h9.546l-1.048-1.047a.75.75 0 111.06-1.06l2.5 2.5a.75.75 0 010 1.06l-2.5 2.5a.75.75 0 11-1.06-1.06L16.296 10.75H6.75A.75.75 0 016 10z" clip-rule="evenodd" />
                                    </svg>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    {{-- Mobile Menu Button --}}
                    <div class="md:hidden">
                        <button id="mobile-menu-button" class="text-gray-600 hover:text-gray-900">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16m-7 6h7"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            
            {{-- Mobile Menu --}}
            <div id="mobile-menu" class="hidden md:hidden bg-white border-t border-gray-200">
                <div class="px-4 py-2 space-y-1">
                    <a href="{{ route('mahasiswa.dashboard') }}" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-md font-medium">Dashboard</a>
                    <a href="{{ route('mahasiswa.biodata.create') }}" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-md font-medium">Biodata</a>
                    <div class="border-t border-gray-200 pt-2">
                        <a href="#" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-md">Profil Saya</a>
                        <a href="#" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-md">Pengaturan</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left block px-3 py-2 text-red-600 hover:bg-red-50 rounded-md font-medium">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        {{-- Main Content --}}
        <main class="flex-1 z-10">
            {{-- Gunakan @yield untuk konten --}}
            @yield('content')
        </main>
    </div>

    {{-- Scripts --}}
    <script>
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');

        mobileMenuButton?.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });

        // Page transition animation
        document.addEventListener('DOMContentLoaded', function () {
            const pageLinks = document.querySelectorAll('.page-link');
            pageLinks.forEach(link => {
                link.addEventListener('click', function (e) {
                    const href = this.getAttribute('href');
                    if (href && href !== window.location.href && !href.startsWith('#')) {
                        e.preventDefault();
                        document.body.classList.add('fade-out');
                        setTimeout(() => {
                            window.location.href = href;
                        }, 400);
                    }
                });
            });
        });
    </script>

    @stack('scripts')
</body>
</html>