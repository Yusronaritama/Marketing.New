<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mahasiswa</title>
    @if (app()->environment('local', 'development'))
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col">

    <!-- Navbar -->
    <nav class="bg-white shadow sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <img src="{{ asset('images/logo.svg') }}" alt="Logo" class="h-8">
                <h1 class="font-semibold text-lg text-gray-700">Sistem Pembayaran Mahasiswa</h1>
            </div>

            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-600">Halo, {{ Auth::user()->name }}</span>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="bg-red-500 hover:bg-red-600 text-white text-sm px-3 py-1.5 rounded">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-1 max-w-5xl mx-auto p-6">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white shadow mt-6 py-4 text-center text-sm text-gray-500">
        &copy; {{ date('Y') }} Universitas Buana Perjuangan Karawang — Sistem Pembayaran Mahasiswa.
    </footer>

</body>
</html>
