<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <!-- Auto Redirect Script -->
        <script>
            @auth
            // Redirect otomatis jika sudah login
            var userRole = "{{ auth()->user()->role }}";
            var redirectUrl;
            
            if (userRole === 'mahasiswa_baru') {
                redirectUrl = "{{ route('mahasiswa.dashboard') }}";
            } else if (['admin', 'admin_akademik', 'admin_farmasi'].includes(userRole)) {
                redirectUrl = "{{ route('admin.dashboard.index') }}";
            } else {
                // Untuk role 'user' atau role lainnya, redirect ke home atau login
                redirectUrl = "{{ route('login') }}";
            }
            
            window.location.href = redirectUrl;
            @else
            // Redirect ke halaman login jika belum login
            window.location.href = "{{ route('login') }}";
            @endauth
        </script>
    </head>
    <body>
        <!-- Loading message atau konten sederhana -->
        <div style="display: flex; justify-content: center; align-items: center; height: 100vh; flex-direction: column;">
            <h2>Loading...</h2>
            <p>Mengarahkan ke halaman yang sesuai</p>
        </div>
    </body>
</html>