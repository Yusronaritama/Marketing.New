<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @if (app()->environment('local', 'development'))
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/driver.js/0.9.8/driver.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/driver.js/0.9.8/driver.min.js"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            overflow-x: hidden; /* Mencegah scrollbar saat animasi */
        }
        .aurora-background {
            background-image: linear-gradient(rgba(26, 35, 126, 0.6), rgba(40, 53, 147, 0.6)), url('{{ asset('images/bp1.jpg') }}');
            background-size: cover;
            background-position: center;
            position: relative;
            overflow: hidden;
        }
        .driver-popover {
            max-width: 90vw !important;
            font-size: 14px !important;
        }
        .driver-popover .driver-popover-title {
            font-size: 16px !important;
        }
        
        .social-card {
            max-width: fit-content;
            border-radius: 15px;
            display: flex;
            justify-content: center;
            transition: 0.5s;
        }
        
        .social-card ul {
            padding: 10px;
            display: flex;
            list-style: none;
            gap: 2rem;
            align-items: center;
            justify-content: center;
        }
        
        .social-card ul li {
            position: relative;
            cursor: pointer;
            list-style: none;
            width: 50px;
            height: 50px;
        }
        
        .social-card .svg {
            transition: all 0.3s;
            padding: 12px;
            height: 50px;
            width: 50px;
            border-radius: 12px;
            color: #3b82f6; /* Default Blue UBP */
            fill: currentColor;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(59, 130, 246, 0.2);
            backdrop-filter: blur(5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .social-card .text {
            opacity: 0;
            border-radius: 5px;
            padding: 4px 10px;
            transition: all 0.3s;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            position: absolute;
            bottom: -30px;
            left: 50%;
            transform: translateX(-50%);
            white-space: nowrap;
            z-index: 99;
        }
        
        /* Isometric projection logic */
        .iso-pro:hover a > .svg {
            transform: translate(10px, -10px) rotate(-15deg);
            background: white;
        }
        
        .iso-pro:hover .text {
            opacity: 1;
            bottom: -35px;
        }
        
        .iso-pro span {
            opacity: 0;
            position: absolute;
            top: 0;
            left: 0;
            width: 50px;
            height: 50px;
            border-radius: 12px;
            transition: all 0.3s;
            border: 1px solid white;
        }
        
        .iso-pro:hover span { opacity: 1; }
        .iso-pro:hover span:nth-child(1) { opacity: 0.2; transform: translate(3px, -3px); }
        .iso-pro:hover span:nth-child(2) { opacity: 0.4; transform: translate(6px, -6px); }
        .iso-pro:hover span:nth-child(3) { opacity: 0.6; transform: translate(9px, -9px); }
        
        /* Custom Colors Per Platform */
        /* Instagram */
        .inst-svg { color: #e1306c !important; }
        .inst-text { color: #e1306c; background: rgba(225, 48, 108, 0.1); }
        .iso-pro:hover .inst { background: #e1306c; border-color: #e1306c; }
        
        /* Facebook */
        .fb-svg { color: #1877f2 !important; }
        .fb-text { color: #1877f2; background: rgba(24, 119, 242, 0.1); }
        .iso-pro:hover .fb { background: #1877f2; border-color: #1877f2; }
        
        /* Youtube */
        .yt-svg { color: #ff0000 !important; }
        .yt-text { color: #ff0000; background: rgba(255, 0, 0, 0.1); }
        .iso-pro:hover .yt { background: #ff0000; border-color: #ff0000; }

      
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-900">
    {{-- Fixed Background --}}
    <div class="fixed top-0 left-0 w-full h-full -z-10">
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('images/ubp_karawang_cover.png') }}');"></div>
        <div class="absolute inset-0 bg-black opacity-50"></div>
    </div>

    {{-- Main Container --}}
    <div class="flex items-center justify-center min-h-screen p-4 overflow-hidden">
        {{-- Card Wrapper --}}
        <div class="relative flex flex-col w-full max-w-4xl  shadow-2xl rounded-2xl md:flex-row">

            {{-- LEFT SIDE: Universitas Info (Aurora Background) --}}
            <div class="relative flex flex-col justify-between p-8 text-white rounded-t-2xl md:rounded-l-2xl md:rounded-tr-none md:w-1/2 aurora-background @if(!$errors->any()) animate-slide-in-left @endif">
                {{-- Header Content --}}
                <div class="z-10 text-center md:text-left">
                    <img src="{{ asset('images/logo_ubp.png') }}" alt="Logo UBP Karawang" class="w-20 h-20 mb-4 mx-auto md:mx-0">
                    <h1 class="mb-3 text-3xl font-bold">Universitas Buana Perjuangan Karawang</h1>
                    <p class="hidden md:block text-base font-light leading-snug tracking-wide">
                        Raihlah masa depanmu yang cerah, indah, dan berwarna. Bersama Kami!
                    </p>
                </div>

                {{-- Social Media Icons --}}
                <div class="flex justify-center mt-10 z-10 animate-intro-fade-in" style="animation-delay: 1.5s">
                    <div class="social-card">
                        <ul>
                            {{-- Instagram --}}
                            <li class="iso-pro">
                                <span class="inst"></span><span class="inst"></span><span class="inst"></span>
                                <a href="https://instagram.com/ubpkarawangofficial" target="_blank">
                                    <svg class="svg inst-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                                        <path d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z"></path>
                                    </svg>
                                </a>
                                <div class="text inst-text">Instagram</div>
                            </li>

                            {{-- Facebook --}}
                            <li class="iso-pro">
                                <span class="fb"></span><span class="fb"></span><span class="fb"></span>
                                <a href="https://facebook.com/ubpkarawangofficial" target="_blank">
                                    <svg class="svg fb-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                                        <path d="M279.14 288l14.22-92.66h-88.91v-60.13c0-25.35 12.42-50.06 52.24-50.06h40.42V6.26S260.43 0 225.36 0c-73.22 0-121.08 44.38-121.08 124.72v70.62H22.89V288h81.39v224h100.17V288z"></path>
                                    </svg>
                                </a>
                                <div class="text fb-text">Facebook</div>
                            </li>

                            {{-- YouTube --}}
                            <li class="iso-pro">
                                <span class="yt"></span><span class="yt"></span><span class="yt"></span>
                                <a href="https://youtube.com/@ubpkarawangofficial" target="_blank">
                                    <svg class="svg yt-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
                                        <path d="M549.655 124.083c-6.281-23.65-24.787-42.276-48.284-48.597C458.781 64 288 64 288 64S117.22 64 74.629 75.486c-23.497 6.322-42.003 24.947-48.284 48.597-11.412 42.867-11.412 132.305-11.412 132.305s0 89.438 11.412 132.305c6.281 23.65 24.787 41.503 48.284 47.824C117.219 448 288 448 288 448s170.781 0 213.371-11.486c23.497-6.321 42.003-24.175 48.284-47.824 11.412-42.867 11.412-132.305 11.412-132.305s0-89.438-11.412-132.305zm-317.51 213.508V175.185l142.739 81.205-142.739 81.201z"></path>
                                    </svg>
                                </a>
                                <div class="text yt-text">YouTube</div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- RIGHT SIDE: Form Login / Content --}}
            <div class="flex flex-col justify-center p-8 md:p-10 md:w-1/2 bg-white/70 backdrop-blur-md border-l border-white/50 rounded-b-2xl md:rounded-r-2xl md:rounded-bl-none">
                {{ $slot }}
            </div>

        </div>
    </div>

    @stack('scripts')
</body>
</html>