<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <meta name="theme-color" content="#213448" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard')</title>
    <link rel="icon" href="{{ asset('images/PENMABAR.gif') }}" type="image/png" />
    @stack('styles')
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
</head>

<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<body
    x-data="{
        page: 'ecommerce',
        loaded: true,
        darkMode: false,
        stickyMenu: false,
        sidebarToggle: false,
        sidebarOpen: false,
        scrollTop: false
    }"
    x-init="
        darkMode = JSON.parse(localStorage.getItem('darkMode'));
        $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))
    "
    :class="{ 'dark bg-gray-900': darkMode === true }"
    @keydown.escape.window="sidebarOpen = false"
>

    {{-- Preloader --}}
    <div
        x-show="loaded"
        x-init="window.addEventListener('DOMContentLoaded', () => { setTimeout(() => loaded = false, 500) })"
        class="fixed left-0 top-0 z-[999999] flex h-screen w-screen items-center justify-center bg-white dark:bg-black"
    >
        <div class="h-16 w-16 animate-spin rounded-full border-4 border-solid border-brand-500 border-t-transparent"></div>
    </div>

    {{-- Layout Wrapper --}}
    <div class="flex h-screen overflow-hidden">

        {{-- Sidebar --}}
        @include('layouts.admin.partials.sidebar')

        {{-- Main Content Area --}}
        <div class="relative flex flex-col flex-1 overflow-x-hidden overflow-y-auto min-w-0">

            {{-- Header --}}
            @include('layouts.admin.partials.header')

            {{-- Page Content --}}
            <main class="flex-1">
                <div class="p-4 mx-auto max-w-screen-2xl md:p-6">
                    @yield('content')
                </div>
            </main>

        </div>
    </div>

    @stack('scripts')
    <script src="{{ asset('assets/js/bundle.js') }}"></script>

</body>
</html>