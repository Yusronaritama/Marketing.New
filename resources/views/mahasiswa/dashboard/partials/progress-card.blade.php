{{-- Progress Card --}}
<div class="lg:col-span-2 card-animate opacity-0 h-full" x-data="{ activeView: {{ $currentStep }} }">
    <div class="bg-white p-6 sm:p-8 rounded-xl shadow-lg border border-gray-200 h-full flex flex-col">
        
        {{-- Wrapper Flexbox untuk 2 Kolom --}}
        <div class="flex flex-col md:flex-row gap-6 md:gap-8 flex-grow">
            
            {{-- Kolom Kiri: Stepper --}}
            <div class="w-full md:w-2/5 lg:w-1/3">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Progres Pendaftaran Anda</h3>
                
                {{-- Mobile: Horizontal Scroll --}}
                <div class="md:hidden">
                    <div class="overflow-x-auto pb-4 custom-scrollbar-horizontal">
                        <div class="flex space-x-4 min-w-max">
                            {{-- Step 1: Login --}}
                            <div class="flex-shrink-0 w-40">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0 flex items-center justify-center h-8 w-8 rounded-full bg-green-100 text-green-600">
                                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.052-.143z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="clickable-item" data-action="login-info" @click="activeView = 1">
                                        <p class="text-sm font-medium text-gray-500 line-through">1. Login</p>
                                        <p class="text-xs text-gray-400">Verifikasi Akun</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Step 2: Data Diri --}}
                            <div class="flex-shrink-0 w-40">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0 flex items-center justify-center h-8 w-8 rounded-full 
                                        @if($currentStep >= 2 && $isPendaftaranComplete) 
                                            bg-green-100 text-green-600 
                                        @elseif($currentStep >= 2) 
                                            bg-blue-100 text-blue-600 ring-4 ring-blue-200
                                        @else 
                                            bg-gray-100 text-gray-400 
                                        @endif">
                                        @if($currentStep >= 2 && $isPendaftaranComplete)
                                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.052-.143z" clip-rule="evenodd" />
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M3.5 2.75a.75.75 0 00-1.5 0v14.5a.75.75 0 001.5 0v-4.392l1.657-.348a6.44 6.44 0 014.271 0l.11.023a.75.75 0 00.74-1.284l-.11-.023a4.94 4.94 0 00-3.235 0l-1.657.348V2.75z" />
                                                <path d="M6.5 2.75a.75.75 0 00-1.5 0v14.5a.75.75 0 001.5 0V9.618l1.657-.348a6.44 6.44 0 014.271 0l.11.023a.75.75 0 10.37-1.458l-.11-.023a4.94 4.94 0 00-3.235 0L6.5 8.152V2.75zM12 7a.75.75 0 01.75-.75h2.5a.75.75 0 010 1.5h-2.5A.75.75 0 0112 7z" />
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="clickable-item" data-action="data-diri" @click="activeView = 2">
                                        <p class="text-sm font-medium 
                                            @if($currentStep >= 2 && $isPendaftaranComplete) 
                                                text-green-700 line-through
                                            @elseif($currentStep >= 2) 
                                                font-semibold text-blue-700
                                            @else 
                                                text-gray-400 
                                            @endif">
                                            2. Data Diri
                                        </p>
                                        <p class="text-xs 
                                            @if($currentStep >= 2 && !$isPendaftaranComplete) 
                                                text-orange-600
                                            @elseif($currentStep >= 2 && $isPendaftaranComplete)
                                                text-green-600
                                            @else 
                                                text-gray-400 
                                            @endif">
                                            @if($currentStep >= 2 && !$isPendaftaranComplete)
                                                ⏳ Berjalan
                                            @elseif($currentStep >= 2 && $isPendaftaranComplete)
                                                ✅ Lengkap
                                            @else
                                                Menunggu
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- Step 3: Soal Ujian --}}
                            <div class="flex-shrink-0 w-40">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0 flex items-center justify-center h-8 w-8 rounded-full 
                                        @if($currentStep >= 3) 
                                            bg-blue-100 text-blue-600 ring-4 ring-blue-200
                                        @else 
                                            bg-gray-100 text-gray-400 
                                        @endif">
                                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                                            <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="clickable-item" data-action="soal-ujian" @click="activeView = 3">
                                        <p class="text-sm font-medium 
                                            @if($currentStep >= 3) 
                                                font-semibold text-blue-700
                                            @else 
                                                text-gray-400 
                                            @endif">
                                            3. Ujian
                                        </p>
                                        <p class="text-xs text-blue-600">
                                            @if($currentStep >= 3)
                                                🔜 Tersedia
                                            @else
                                                Menunggu
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- Step 4: Pembayaran --}}
                            <div class="flex-shrink-0 w-40">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0 flex items-center justify-center h-8 w-8 rounded-full 
                                        @if($currentStep >= 4) 
                                            bg-blue-100 text-blue-600 ring-4 ring-blue-200
                                        @else 
                                            bg-gray-100 text-gray-400 
                                        @endif">
                                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" />
                                            <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="clickable-item" data-action="pembayaran" @click="activeView = 4">
                                        <p class="text-sm font-medium 
                                            @if($currentStep >= 4) 
                                                font-semibold text-blue-700
                                            @else 
                                                text-gray-400 
                                            @endif">
                                            4. Pembayaran
                                        </p>
                                        <p class="text-xs text-blue-600">
                                            @if($currentStep >= 4)
                                                🔜 Tersedia
                                            @else
                                                Menunggu
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- Step 5: Portal --}}
                            <div class="flex-shrink-0 w-40">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0 flex items-center justify-center h-8 w-8 rounded-full 
                                        @if($currentStep >= 5) 
                                            bg-green-100 text-green-600 
                                        @else 
                                            bg-gray-100 text-gray-400 
                                        @endif">
                                        @if($currentStep >= 5)
                                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.052-.143z" clip-rule="evenodd" />
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" />
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="clickable-item" data-action="portal" @click="activeView = 5">
                                        <p class="text-sm font-medium 
                                            @if($currentStep >= 5) 
                                                text-green-700 line-through
                                            @else 
                                                text-gray-400 
                                            @endif">
                                            5. Portal
                                        </p>
                                        <p class="text-xs text-green-600">
                                            @if($currentStep >= 5)
                                                ✅ Akses
                                            @else
                                                Menunggu
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Desktop: Vertical dengan Scroll --}}
                <div class="hidden md:block pr-2 custom-scrollbar">
                    <ul class="space-y-2" id="progress-steps">
                        {{-- Step 1: Login --}}
                        <li class="flex items-start" @click="activeView = 1">
                            <div class="flex-shrink-0 flex flex-col items-center">
                                <div class="flex items-center justify-center h-8 w-8 rounded-full bg-green-100 text-green-600">
                                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.052-.143z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="h-6 w-0.5 bg-gray-300 mt-2"></div>
                            </div>
                            <div class="clickable-item ml-4" data-action="login-info">
                                <p class="text-sm font-medium text-gray-500 line-through">1. Login & Verifikasi Akun</p>
                            </div>
                        </li>
                
                        {{-- Step 2: Data Diri (Parent) dengan Dropdown --}}
                        <li class="flex items-start" @click="activeView = 2">
                            <div class="flex-shrink-0 flex flex-col items-center">
                                <div class="flex items-center justify-center h-8 w-8 rounded-full 
                                    @if($currentStep >= 2 && $isPendaftaranComplete) 
                                        bg-green-100 text-green-600 
                                    @elseif($currentStep >= 2) 
                                        bg-blue-100 text-blue-600 ring-4 ring-blue-200
                                    @else 
                                        bg-gray-100 text-gray-400 
                                    @endif">
                                    @if($currentStep >= 2 && $isPendaftaranComplete)
                                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.052-.143z" clip-rule="evenodd" />
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M3.5 2.75a.75.75 0 00-1.5 0v14.5a.75.75 0 001.5 0v-4.392l1.657-.348a6.44 6.44 0 014.271 0l.11.023a.75.75 0 00.74-1.284l-.11-.023a4.94 4.94 0 00-3.235 0l-1.657.348V2.75z" />
                                            <path d="M6.5 2.75a.75.75 0 00-1.5 0v14.5a.75.75 0 001.5 0V9.618l1.657-.348a6.44 6.44 0 014.271 0l.11.023a.75.75 0 10.37-1.458l-.11-.023a4.94 4.94 0 00-3.235 0L6.5 8.152V2.75zM12 7a.75.75 0 01.75-.75h2.5a.75.75 0 010 1.5h-2.5A.75.75 0 0112 7z" />
                                        </svg>
                                    @endif
                                </div>
                                <div class="h-6 w-0.5 
                                    @if($currentStep > 2) 
                                        bg-green-300 
                                    @else 
                                        bg-gray-300 
                                    @endif"></div>
                            </div>
                            <div class="ml-4 w-full">
                                {{-- Header Dropdown --}}
                                <div class="clickable-item dropdown-toggle flex items-center justify-between w-full" data-target="data-diri-dropdown">
                                    <p class="text-sm font-medium 
                                        @if($currentStep >= 2 && $isPendaftaranComplete) 
                                            text-green-700 line-through
                                        @elseif($currentStep >= 2) 
                                            font-semibold text-blue-700
                                        @else 
                                            text-gray-400 
                                        @endif">
                                        2. Data Diri
                                        @if($currentStep >= 2 && !$isPendaftaranComplete)
                                            <span class="text-xs text-orange-600 block mt-1">⏳ Sedang berjalan</span>
                                        @elseif($currentStep >= 2 && $isPendaftaranComplete)
                                            <span class="text-xs text-green-600 block mt-1">✅ Lengkap</span>
                                        @endif
                                    </p>
                                    {{-- Dropdown Arrow --}}
                                    <svg class="w-4 h-4 text-gray-500 dropdown-arrow transition-transform duration-300" 
                                         xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>

                                {{-- Dropdown Content --}}
                                <div id="data-diri-dropdown" class="dropdown-content ml-2 mt-2 space-y-2 border-l-2 border-gray-200 pl-4 hidden">
                                    {{-- Sub-step: Pilih Program Studi --}}
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 flex flex-col items-center">
                                            <div class="flex items-center justify-center h-6 w-6 rounded-full 
                                                @if($isJurusanComplete) 
                                                    bg-green-100 text-green-600 
                                                @else 
                                                    bg-gray-100 text-gray-400 
                                                @endif">
                                                @if($isJurusanComplete)
                                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.052-.143z" clip-rule="evenodd" />
                                                    </svg>
                                                @else
                                                    <span class="text-xs">•</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="clickable-item ml-3" data-action="pilih-jurusan">
                                            <p class="text-xs font-medium 
                                                @if($isJurusanComplete) 
                                                    text-green-600 
                                                @else 
                                                    text-gray-500 
                                                @endif">
                                                • Pilih Program Studi
                                                @if(!$isJurusanComplete)
                                                    <span class="text-xs text-orange-500 block mt-1">❌ Belum dipilih</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>

                                    {{-- Sub-step: Unggah Berkas --}}
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 flex flex-col items-center">
                                            <div class="flex items-center justify-center h-6 w-6 rounded-full 
                                                @if($isBerkasComplete) 
                                                    bg-green-100 text-green-600 
                                                @else 
                                                    bg-gray-100 text-gray-400 
                                                @endif">
                                                @if($isBerkasComplete)
                                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.052-.143z" clip-rule="evenodd" />
                                                    </svg>
                                                @else
                                                    <span class="text-xs">•</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="clickable-item ml-3" data-action="unggah-berkas">
                                            <p class="text-xs font-medium 
                                                @if($isBerkasComplete) 
                                                    text-green-600 
                                                @else 
                                                    text-gray-500 
                                                @endif">
                                                • Unggah Berkas
                                                @if(!$isBerkasComplete)
                                                    <span class="text-xs text-orange-500 block mt-1">❌ Belum lengkap</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>

                                    {{-- Sub-step: Selesaikan Pendaftaran --}}
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 flex flex-col items-center">
                                            <div class="flex items-center justify-center h-6 w-6 rounded-full 
                                                @if($isPendaftaranComplete) 
                                                    bg-green-100 text-green-600 
                                                @else 
                                                    bg-gray-100 text-gray-400 
                                                @endif">
                                                @if($isPendaftaranComplete)
                                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.052-.143z" clip-rule="evenodd" />
                                                    </svg>
                                                @else
                                                    <span class="text-xs">•</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="clickable-item ml-3" data-action="selesaikan-pendaftaran">
                                            <p class="text-xs font-medium 
                                                @if($isPendaftaranComplete) 
                                                    text-green-600 
                                                @else 
                                                    text-gray-500 
                                                @endif">
                                                • Selesaikan Pendaftaran
                                                @if(!$isPendaftaranComplete)
                                                    <span class="text-xs text-orange-500 block mt-1">⏳ Menunggu kelengkapan</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                
                        {{-- Step 3: Soal Ujian --}}
                        <li class="flex items-start" @click="activeView = 3">
                            <div class="flex-shrink-0 flex flex-col items-center">
                                <div class="flex items-center justify-center h-8 w-8 rounded-full 
                                    @if($currentStep >= 3) 
                                        bg-blue-100 text-blue-600 ring-4 ring-blue-200
                                    @else 
                                        bg-gray-100 text-gray-400 
                                    @endif">
                                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                                        <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="h-6 w-0.5 
                                    @if($currentStep > 3) 
                                        bg-green-300 
                                    @else 
                                        bg-gray-300 
                                    @endif"></div>
                            </div>
                            <div class="clickable-item ml-4" data-action="soal-ujian">
                                <p class="text-sm font-medium 
                                    @if($currentStep >= 3) 
                                        font-semibold text-blue-700
                                    @else 
                                        text-gray-400 
                                    @endif">
                                    3. Soal Ujian
                                    @if($currentStep >= 3)
                                        <span class="text-xs text-blue-600 block mt-1">🔜 Tersedia setelah data lengkap</span>
                                    @endif
                                </p>
                            </div>
                        </li>

                        {{-- Step 4: Pembayaran Tahap Pertama --}}
                        <li class="flex items-start" @click="activeView = 4">
                            <div class="flex-shrink-0 flex flex-col items-center">
                                <div class="flex items-center justify-center h-8 w-8 rounded-full 
                                    @if($currentStep >= 4) 
                                        bg-blue-100 text-blue-600 ring-4 ring-blue-200
                                    @else 
                                        bg-gray-100 text-gray-400 
                                    @endif">
                                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" />
                                        <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="h-6 w-0.5 
                                    @if($currentStep > 4) 
                                        bg-green-300 
                                    @else 
                                        bg-gray-300 
                                    @endif"></div>
                            </div>
                            <div class="clickable-item ml-4" data-action="pembayaran">
                                <p class="text-sm font-medium 
                                    @if($currentStep >= 4) 
                                        font-semibold text-blue-700
                                    @else 
                                        text-gray-400 
                                    @endif">
                                    4. Pembayaran Tahap Pertama
                                    @if($currentStep >= 4)
                                        <span class="text-xs text-blue-600 block mt-1">🔜 Tersedia setelah ujian</span>
                                    @endif
                                </p>
                            </div>
                        </li>

                        {{-- Step 5: Portal --}}
                        <li class="flex items-start" @click="activeView = 5">
                            <div class="flex-shrink-0 flex flex-col items-center">
                                <div class="flex items-center justify-center h-8 w-8 rounded-full 
                                    @if($currentStep >= 5) 
                                        bg-green-100 text-green-600 
                                    @else 
                                        bg-gray-100 text-gray-400 
                                    @endif">
                                    @if($currentStep >= 5)
                                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.052-.143z" clip-rule="evenodd" />
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" />
                                        </svg>
                                    @endif
                                </div>
                            </div>
                            <div class="clickable-item ml-4" data-action="portal">
                                <p class="text-sm font-medium 
                                    @if($currentStep >= 5) 
                                        text-green-700 line-through
                                    @else 
                                        text-gray-400 
                                    @endif">
                                    5. Portal
                                    @if($currentStep >= 5)
                                        <span class="text-xs text-green-600 block mt-1">✅ Akses diberikan</span>
                                    @endif
                                </p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Kolom Kanan: Teks & Tombol Aksi --}}
            <div class="w-full md:w-3/5 lg:w-2/3 md:border-l md:pl-8 flex flex-col">  
    
    {{-- Container dengan fixed height dan scroll untuk bagian kanan --}}
    <div class="pr-2 custom-scrollbar flex-grow">
        
        {{-- Status Progress --}}
        <div class="mb-4">
            <h4 class="text-lg font-semibold text-gray-800">Status Pendaftaran</h4>
            <div class="mt-2 bg-gray-50 rounded-lg p-4">
                @if($currentStep >= 5)
                    <p class="text-green-600 font-medium">✅ Pendaftaran Anda telah selesai!</p>
                    <p class="text-sm text-gray-600 mt-1">Selamat! Anda sekarang dapat mengakses portal mahasiswa.</p>
                @else
                    <p class="text-blue-600 font-medium">
                        Progress: {{ $currentStep }}/5 langkah selesai
                    </p>
                    <div class="w-full bg-gray-200 rounded-full h-2.5 mt-2">
                        <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ ($currentStep / 5) * 100 }}%"></div>
                    </div>
                    <p class="text-sm text-gray-600 mt-2">
                        @if($currentStep == 2 && !$isPendaftaranComplete)
                            ⏳ Silakan lengkapi semua data diri terlebih dahulu.
                        @elseif($currentStep == 3)
                            🔜 Siap mengerjakan soal ujian.
                        @elseif($currentStep == 4)
                            🔜 Menunggu proses pembayaran.
                        @else
                            Lanjutkan ke langkah berikutnya untuk menyelesaikan pendaftaran.
                        @endif
                    </p>
                @endif
            </div>
        </div>

        {{-- Tombol Aksi --}}
        
        <div class="space-y-3">
                                
            
            <template x-if="activeView == 1">
                <div class="animate-slideInUp">
                    <a href="{{ route('profile.edit') }}" {{-- Ganti ke rute profil Anda --}}
                    class="inline-flex items-center space-x-2 bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded-lg shadow-md transition-transform transform hover:scale-105 w-full justify-center">
                        <span>Atur Biodata Diri</span>
                        <i class="fas fa-key"></i>
                    </a>
                </div>
            </template>

            {{-- TAMPIL SAAT KLIK STEP 2: DATA DIRI --}}
            <template x-if="activeView == 2">
                <div class="animate-slideInUp">
                    @if($isPendaftaranComplete)
                        {{-- Skenario: Pendaftaran Selesai, redirect ke biodata --}}
                        <a href="{{ route('mahasiswa.biodata.create') }}"
                        class="inline-flex items-center space-x-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg shadow-md transition-transform transform hover:scale-105 w-full justify-center">
                            <span>Lihat Data Diri</span>
                            <i class="fas fa-search"></i>
                        </a>
                    @else
                        {{-- Skenario: Pendaftaran Belum Selesai, redirect ke form registrasi --}}
                        <a href="{{ route('mahasiswa.registration') }}"
                        class="inline-flex items-center space-x-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg shadow-md transition-transform transform hover:scale-105 w-full justify-center">
                            <span>Lanjutkan/Lihat Detail Pendaftaran</span>
                            <i class="fas fa-edit"></i>
                        </a>
                    @endif
                </div>
            </template>

            {{-- TAMPIL SAAT KLIK STEP 3: UJIAN --}}
            <template x-if="activeView == 3">
                <div class="animate-slideInUp">
                    <a href="#" {{-- Ganti ke rute ujian Anda --}}
                    class="inline-flex items-center space-x-2 bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg shadow-md transition-transform transform hover:scale-105 w-full justify-center 
                            @if($currentStep < 3) opacity-50 cursor-not-allowed @endif" 
                    @if($currentStep < 3) onclick="event.preventDefault()" @endif>
                        <span>Mulai Ujian</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </template>
            
            {{-- TAMPIL SAAT KLIK STEP 4: PEMBAYARAN --}}
            <template x-if="activeView == 4">
                <div class="animate-slideInUp">
                    <a href="{{ route('mahasiswa.pembayaran.ukt') }}" {{-- Ganti ke rute pembayaran Anda --}}
                    class="inline-flex items-center space-x-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg shadow-md transition-transform transform hover:scale-105 w-full justify-center
                            @if($currentStep < 4) opacity-50 cursor-not-allowed @endif"
                    @if($currentStep < 4) onclick="event.preventDefault()" @endif>
                        <span>Lakukan Pembayaran</span>
                        <i class="fas fa-credit-card"></i>
                    </a>
                </div>
            </template>
            
            {{-- TAMPIL SAAT KLIK STEP 5: PORTAL --}}
            <template x-if="activeView == 5">
                <div class="animate-slideInUp">
                    <button type="button" 
                            class="inline-flex items-center space-x-2 bg-gray-400 text-white font-bold py-3 px-6 rounded-lg w-full justify-center cursor-not-allowed"
                            disabled>
                        <span>Akses Portal (Otomatis)</span>
                        <i class="fas fa-check-circle"></i>
                    </button>
                </div>
            </template>

            {{-- Tombol Lihat Ringkasan (Selalu ada jika data ada) --}}
            @if($eregData)
                <a href="{{ route('mahasiswa.registration') }}?view=summary"
                    class="inline-flex items-center space-x-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 px-4 rounded-lg transition-colors w-full justify-center">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 12.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5z" />
                        <path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 010-1.186A10.004 10.004 0 0110 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0110 17c-4.257 0-7.893-2.66-9.336-6.41zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                    </svg>
                    <span>Lihat Ringkasan Pendaftaran</span>
                </a>
            @endif
        </div>

        {{-- Info Kelengkapan Data --}}
        @if($currentStep == 2 && $eregData)
        <div class="mt-4 p-4 bg-orange-50 border border-orange-200 rounded-lg">
            <h5 class="text-sm font-semibold text-orange-800 mb-2">Status Kelengkapan Data Diri:</h5>
            <ul class="text-xs text-orange-700 space-y-1">
                <li class="flex items-center">
                    @if($isDataDiriComplete) ✅ @else ❌ @endif
                    <span class="ml-2">Data Pribadi: {{ $isDataDiriComplete ? 'Lengkap' : 'Belum lengkap' }}</span>
                </li>
                <li class="flex items-center">
                    @if($isJurusanComplete) ✅ @else ❌ @endif
                    <span class="ml-2">Program Studi: {{ $isJurusanComplete ? 'Terpilih' : 'Belum dipilih' }}</span>
                </li>
                <li class="flex items-center">
                    @if($isBerkasComplete) ✅ @else ❌ @endif
                    <span class="ml-2">Berkas: {{ $isBerkasComplete ? 'Lengkap' : 'Belum lengkap' }}</span>
                </li>
            </ul>
        </div>
        @endif

        {{-- Info tambahan --}}
        <div class="mt-6 p-4 bg-blue-50 rounded-lg">
            <p class="text-sm text-blue-700">
                <strong>Tip:</strong> Klik pada setiap langkah di sebelah kiri untuk melihat detail dan aksi yang tersedia.
            </p>
            @if($eregData)
                <p class="text-xs text-blue-600 mt-2">
                    Terakhir diperbarui: {{ $eregData->updated_at->format('d M Y H:i') }}
                </p>
            @endif
        </div>

    </div> {{-- End scroll container --}}
    
</div>
        </div>
    </div>
</div>

<style>
.clickable-item {
    cursor: pointer;
}

.custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e0 #f7fafc;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #f7fafc;
    border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e0;
    border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #a0aec0;
}

/* Horizontal Scrollbar untuk Mobile */
.custom-scrollbar-horizontal {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e0 #f7fafc;
}

.custom-scrollbar-horizontal::-webkit-scrollbar {
    height: 6px;
}

.custom-scrollbar-horizontal::-webkit-scrollbar-track {
    background: #f7fafc;
    border-radius: 3px;
}

.custom-scrollbar-horizontal::-webkit-scrollbar-thumb {
    background: #cbd5e0;
    border-radius: 3px;
}

.custom-scrollbar-horizontal::-webkit-scrollbar-thumb:hover {
    background: #a0aec0;
}

/* Dropdown Styles */
.dropdown-toggle {
    cursor: pointer;
    transition: all 0.3s ease;
}

.dropdown-toggle:hover {
    background-color: #f8fafc;
    border-radius: 0.375rem;
    padding: 0.25rem 0.5rem;
    margin: -0.25rem -0.5rem;
}

.dropdown-toggle.active .dropdown-arrow {
    transform: rotate(180deg);
}

.dropdown-content {
    transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1), 
                opacity 0.3s ease-out, 
                transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    max-height: 0;
    opacity: 0;
    transform: translateY(-10px); 
    overflow: hidden;
}

.dropdown-content.show {
    max-height: 300px; 
    opacity: 1;
    transform: translateY(0); 
}



/* Mobile specific styles */
@media (max-width: 767px) {
    .min-w-max {
        min-width: max-content;
    }
    
    .w-40 {
        width: 10rem;
    }
}
</style>

<script>
// Dropdown functionality (hanya untuk desktop)
document.addEventListener('DOMContentLoaded', function() {
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            // Skip jika di mobile
            if (window.innerWidth < 768) return;
            
            e.stopPropagation();
            const targetId = this.getAttribute('data-target');
            const dropdownContent = document.getElementById(targetId);
            const arrow = this.querySelector('.dropdown-arrow');
            
            // Toggle dropdown
            dropdownContent.classList.toggle('hidden');
            dropdownContent.classList.toggle('show');
            this.classList.toggle('active');
            
            // Close other dropdowns
            dropdownToggles.forEach(otherToggle => {
                if (otherToggle !== this) {
                    const otherTargetId = otherToggle.getAttribute('data-target');
                    const otherDropdownContent = document.getElementById(otherTargetId);
                    const otherArrow = otherToggle.querySelector('.dropdown-arrow');
                    
                    otherDropdownContent.classList.add('hidden');
                    otherDropdownContent.classList.remove('show');
                    otherToggle.classList.remove('active');
                    otherArrow.style.transform = 'rotate(0deg)';
                }
            });
            
            // Rotate arrow
            if (dropdownContent.classList.contains('show')) {
                arrow.style.transform = 'rotate(180deg)';
            } else {
                arrow.style.transform = 'rotate(0deg)';
            }
        });
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        if (window.innerWidth < 768) return;
        
        dropdownToggles.forEach(toggle => {
            const targetId = toggle.getAttribute('data-target');
            const dropdownContent = document.getElementById(targetId);
            const arrow = toggle.querySelector('.dropdown-arrow');
            
            dropdownContent.classList.add('hidden');
            dropdownContent.classList.remove('show');
            toggle.classList.remove('active');
            arrow.style.transform = 'rotate(0deg)';
        });
    });
});

// Existing click effects functionality
function initializeClickEffects() {
    console.log('Initializing click effects...');
    
    const clickableItems = document.querySelectorAll('.clickable-item');
    console.log('Found clickable items:', clickableItems.length);
    
    clickableItems.forEach((item, index) => {
        console.log(`Setting up click listener for item ${index}:`, item.textContent);
        
        item.addEventListener('click', function(e) {
            // Skip if it's a dropdown toggle di desktop
            if (this.classList.contains('dropdown-toggle') && window.innerWidth >= 768) {
                return;
            }
            
            e.preventDefault();
            e.stopPropagation();
            
            console.log('Item clicked:', this.textContent);
            
            // Remove active class from all items
            clickableItems.forEach(i => {
                if (!i.classList.contains('dropdown-toggle') || window.innerWidth < 768) {
                    i.classList.remove('active', 'animate-pulse-click');
                }
            });
            
            // Add active class and animation to clicked item
            this.classList.add('active', 'animate-pulse-click');
            
            // Remove animation class after animation completes
            setTimeout(() => {
                this.classList.remove('animate-pulse-click');
            }, 300);
            
            // Show center button
            showCenterButton(this);
        });
    });
}

// Initialize after DOM is loaded
setTimeout(() => {
    initializeClickEffects();
}, 100);
</script>