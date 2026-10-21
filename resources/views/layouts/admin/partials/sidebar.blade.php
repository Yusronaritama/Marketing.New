@if(auth()->check() && !in_array(auth()->user()->role, ['guest']))

<aside :class="sidebarToggle ? 'translate-x-0 lg:w-[90px]' : '-translate-x-full'" class="sidebar fixed left-0 top-0 z-9999 flex h-screen w-[290px] flex-col overflow-y-hidden border-r border-gray-200 bg-white px-5 dark:border-gray-800 dark:bg-black lg:static lg:translate-x-0 cyber-sidebar">
  <div :class="sidebarToggle ? 'justify-center' : 'justify-between'" class="flex items-center gap-2 pt-8 pb-7 sidebar-header">
    <a href="{{ route('admin.dashboard.index') }}">
      <span class="logo" :class="sidebarToggle ? 'hidden' : ''">
        <img src="{{ asset('images/PENMABAR.gif') }}" alt="Logo" class="w-48 h-auto" />
      </span>
      <img class="logo-icon" :class="sidebarToggle ? 'lg:block' : 'hidden'" src="{{ asset('images/PENMABAR.gif') }}" alt="Logo Icon" class="w-12 h-auto" />
    </a>
  </div>
  <div class="flex flex-col overflow-y-auto duration-300 ease-linear no-scrollbar">
    <nav x-data="{selected: $persist('Dashboard')}">
      <div>
        <h3 class="mb-4 text-xs uppercase leading-[20px] text-gray-400 cyber-menu-title">
          <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">
            MENU
          </span>
          <svg :class="sidebarToggle ? 'lg:block hidden' : 'hidden'" class="mx-auto fill-current menu-group-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M5.99915 10.2451C6.96564 10.2451 7.74915 11.0286 7.74915 11.9951V12.0051C7.74915 12.9716 6.96564 13.7551 5.99915 13.7551C5.03265 13.7551 4.24915 12.9716 4.24915 12.0051V11.9951C4.24915 11.0286 5.03265 10.2451 5.99915 10.2451ZM17.9991 10.2451C18.9656 10.2451 19.7491 11.0286 19.7491 11.9951V12.0051C19.7491 12.9716 18.9656 13.7551 17.9991 13.7551C17.0326 13.7551 16.2491 12.9716 16.2491 12.0051V11.9951C16.2491 11.0286 17.0326 10.2451 17.9991 10.2451ZM13.7491 11.9951C13.7491 11.0286 12.9656 10.2451 11.9991 10.2451C11.0326 10.2451 10.2491 11.0286 10.2491 11.9951V12.0051C10.2491 12.9716 11.0326 13.7551 11.9991 13.7551C12.9656 13.7551 13.7491 12.9716 13.7491 12.0051V11.9951Z" fill="" />
          </svg>
        </h3>
                  @if(!in_array(auth()->user()->role, ['admin_farmasi', 'admin_akademik', 'admin_keuangan', 'admin_profesi','admin']))
<li>
                  <a href="{{ route('admin.prodipage.index') }}" class="menu-dropdown-item group cyber-dropdown-item">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Daftar User
                  </a>
                </li>
                @endif
@php
    $restrictedRoles = [
        'admin_akademik',
        'admin_farmasi',
        'admin_teknik_mesin',
        'admin_teknik_industri',
        'admin_teknik_informatika',
        'admin_sistem_informasi',
        'admin_manajemen_s2',
        'admin_ilmu_hukum',
        'admin_psikologi',
        'admin_akuntansi',
        'admin_pendidikan_guru_sekolah_dasar',
        'admin_pendidikan_pancasila_dan_kewarganegaraan',
        'admin_pendidikan_agama_islam',
        'admin_manajemen', 'admin_profesi'
    ];
@endphp

@if (!in_array(auth()->user()->role, $restrictedRoles))

<ul class="flex flex-col gap-4 mb-6">
    <li>
        <a
            href=""
            @click.prevent="selected = (selected === 'Dashboard' ? '' : 'Dashboard')"
            class="menu-item group cyber-menu-item"
            :class="(selected === 'Dashboard') ||
                     (page === 'ecommerce' || page === 'analytics' || page === 'marketing' || page === 'crm' || page === 'stocks')
                     ? 'menu-item-active cyber-menu-active'
                     : 'menu-item-inactive'"
        >
            {{-- Icon --}}
            <svg
                :class="(selected === 'Dashboard') ||
                         (page === 'ecommerce' || page === 'analytics' || page === 'marketing' || page === 'crm' || page === 'stocks')
                         ? 'menu-item-icon-active'
                         : 'menu-item-icon-inactive'"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    fill-rule="evenodd"
                    clip-rule="evenodd"
                    d="M5.5 3.25C4.25736 3.25 3.25 4.25736 3.25 5.5V8.99998C3.25 10.2426 4.25736 11.25 5.5 11.25H9C10.2426 11.25 11.25 10.2426 11.25 8.99998V5.5C11.25 4.25736 10.2426 3.25 9 3.25H5.5ZM4.75 5.5C4.75 5.08579 5.08579 4.75 5.5 4.75H9C9.41421 4.75 9.75 5.08579 9.75 5.5V8.99998C9.75 9.41419 9.41421 9.74998 9 9.74998H5.5C5.08579 9.74998 4.75 9.41419 4.75 8.99998V5.5ZM5.5 12.75C4.25736 12.75 3.25 13.7574 3.25 15V18.5C3.25 19.7426 4.25736 20.75 5.5 20.75H9C10.2426 20.75 11.25 19.7427 11.25 18.5V15C11.25 13.7574 10.2426 12.75 9 12.75H5.5ZM4.75 15C4.75 14.5858 5.08579 14.25 5.5 14.25H9C9.41421 14.25 9.75 14.5858 9.75 15V18.5C9.75 18.9142 9.41421 19.25 9 19.25H5.5C5.08579 19.25 4.75 18.9142 4.75 18.5V15ZM12.75 5.5C12.75 4.25736 13.7574 3.25 15 3.25H18.5C19.7426 3.25 20.75 4.25736 20.75 5.5V8.99998C20.75 10.2426 19.7426 11.25 18.5 11.25H15C13.7574 11.25 12.75 10.2426 12.75 8.99998V5.5ZM15 4.75C14.5858 4.75 14.25 5.08579 14.25 5.5V8.99998C14.25 9.41419 14.5858 9.74998 15 9.74998H18.5C18.9142 9.74998 19.25 9.41419 19.25 8.99998V5.5C19.25 5.08579 18.9142 4.75 18.5 4.75H15ZM15 12.75C13.7574 12.75 12.75 13.7574 12.75 15V18.5C12.75 19.7426 13.7574 20.75 15 20.75H18.5C19.7426 20.75 20.75 19.7427 20.75 18.5V15C20.75 13.7574 19.7426 12.75 18.5 12.75H15ZM14.25 15C14.25 14.5858 14.5858 14.25 15 14.25H18.5C18.9142 14.25 19.25 14.5858 19.25 15V18.5C19.25 18.9142 18.9142 19.25 18.5 19.25H15C14.5858 19.25 14.25 18.9142 14.25 18.5V15Z"
                    fill=""
                />
            </svg>

            {{-- Text --}}
            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                Beranda
            </span>

            {{-- Arrow --}}
            <svg
                class="menu-item-arrow"
                :class="[
                    (selected === 'Dashboard')
                        ? 'menu-item-arrow-active'
                        : 'menu-item-arrow-inactive',
                    sidebarToggle ? 'lg:hidden' : ''
                ]"
                width="20"
                height="20"
                viewBox="0 0 20 20"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585"
                    stroke=""
                    stroke-width="1.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>
        </a>

        {{-- Dropdown --}}
        <div
            class="overflow-hidden transform translate"
            :class="(selected === 'Dashboard') ? 'block' : 'hidden'"
        >
            <ul
                :class="sidebarToggle ? 'lg:hidden' : 'flex'"
                class="flex flex-col gap-1 mt-2 pl-9 menu-dropdown cyber-dropdown"
            >
                {{-- Beranda Utama --}}
                <li>
                    <a
                        href="{{ route('admin.dashboard.index') }}"
                        class="menu-dropdown-item group cyber-dropdown-item"
                        :class="page === 'ecommerce'
                            ? 'menu-dropdown-item-active cyber-dropdown-active'
                            : 'menu-dropdown-item-inactive'"
                    >
                        <svg
                            class="w-4 h-4 mr-2"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                            />
                        </svg>

                        Beranda Utama
                    </a>
                </li>

                {{-- Report Beranda --}}
                <li>
                    <a
                        href="{{ route('admin.show-all.index') }}"
                        class="menu-dropdown-item group cyber-dropdown-item"
                        :class="page === 'ecommerce'
                            ? 'menu-dropdown-item-active cyber-dropdown-active'
                            : 'menu-dropdown-item-inactive'"
                    >
                        <svg
                            class="w-4 h-4 mr-2"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            />
                        </svg>

                        Report Beranda
                    </a>
                </li>

                {{-- Tren Multi-Tahun --}}
                <li>
                    <a
                        href="{{ route('admin.show-all.tren') }}"
                        class="menu-dropdown-item group cyber-dropdown-item"
                        :class="page === 'tren-multi-tahun'
                            ? 'menu-dropdown-item-active cyber-dropdown-active'
                            : 'menu-dropdown-item-inactive'"
                    >
                        <svg
                            class="w-4 h-4 mr-2"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M7 12l3-3 3 3 4-4M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"
                            />
                        </svg>

                        Tren Multi-Tahun
                    </a>
                </li>
            </ul>
        </div>
    </li>
</ul>

@endif
@php
    $restrictedRoles = [
        'admin_akademik',
        'admin_farmasi',
        'admin_teknik_mesin',
        'admin_teknik_industri',
        'admin_teknik_informatika',
        'admin_sistem_informasi',
        'admin_manajemen_s2',
        'admin_ilmu_hukum',
        'admin_psikologi',
        'admin_akuntansi',
        'admin_pendidikan_guru_sekolah_dasar',
        'admin_pendidikan_pancasila_dan_kewarganegaraan',
        'admin_pendidikan_agama_islam',
        'admin_manajemen'
    ];
@endphp

@if (!in_array(auth()->user()->role, $restrictedRoles))

<ul class="flex flex-col gap-4 mb-6">
    <li>
        <a
            href="#"
            @click.prevent="selected = (selected === 'Profesi' ? '' : 'Profesi')"
            class="menu-item group cyber-menu-item"
            :class="(selected === 'Profesi') ||
                     (page === 'list_profesi' || page === 'attendance_profesi' || page === 'settings_register')
                     ? 'menu-item-active cyber-menu-active'
                     : 'menu-item-inactive'"
        >
            {{-- Icon --}}
            <svg
                :class="(selected === 'Profesi') ||
                         (page === 'list_profesi' || page === 'attendance_profesi' || page === 'settings_register')
                         ? 'menu-item-icon-active'
                         : 'menu-item-icon-inactive'"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    fill-rule="evenodd"
                    clip-rule="evenodd"
                    d="M5.5 3.25C4.25736 3.25 3.25 4.25736 3.25 5.5V8.99998C3.25 10.2426 4.25736 11.25 5.5 11.25H9C10.2426 11.25 11.25 10.2426 11.25 8.99998V5.5C11.25 4.25736 10.2426 3.25 9 3.25H5.5ZM4.75 5.5C4.75 5.08579 5.08579 4.75 5.5 4.75H9C9.41421 4.75 9.75 5.08579 9.75 5.5V8.99998C9.75 9.41419 9.41421 9.74998 9 9.74998H5.5C5.08579 9.74998 4.75 9.41419 4.75 8.99998V5.5ZM5.5 12.75C4.25736 12.75 3.25 13.7574 3.25 15V18.5C3.25 19.7426 4.25736 20.75 5.5 20.75H9C10.2426 20.75 11.25 19.7427 11.25 18.5V15C11.25 13.7574 10.2426 12.75 9 12.75H5.5ZM4.75 15C4.75 14.5858 5.08579 14.25 5.5 14.25H9C9.41421 14.25 9.75 14.5858 9.75 15V18.5C9.75 18.9142 9.41421 19.25 9 19.25H5.5C5.08579 19.25 4.75 18.9142 4.75 18.5V15ZM12.75 5.5C12.75 4.25736 13.7574 3.25 15 3.25H18.5C19.7426 3.25 20.75 4.25736 20.75 5.5V8.99998C20.75 10.2426 19.7426 11.25 18.5 11.25H15C13.7574 11.25 12.75 10.2426 12.75 8.99998V5.5ZM15 4.75C14.5858 4.75 14.25 5.08579 14.25 5.5V8.99998C14.25 9.41419 14.5858 9.74998 15 9.74998H18.5C18.9142 9.74998 19.25 9.41419 19.25 8.99998V5.5C19.25 5.08579 18.9142 4.75 18.5 4.75H15ZM15 12.75C13.7574 12.75 12.75 13.7574 12.75 15V18.5C12.75 19.7426 13.7574 20.75 15 20.75H18.5C19.7426 20.75 20.75 19.7427 20.75 18.5V15C20.75 13.7574 19.7426 12.75 18.5 12.75H15ZM14.25 15C14.25 14.5858 14.5858 14.25 15 14.25H18.5C18.9142 14.25 19.25 14.5858 19.25 15V18.5C19.25 18.9142 18.9142 19.25 18.5 19.25H15C14.5858 19.25 14.25 18.9142 14.25 18.5V15Z"
                    fill=""
                />
            </svg>

            {{-- Text --}}
            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                Profesi
            </span>

            {{-- Arrow --}}
            <svg
                class="menu-item-arrow"
                :class="[
                    (selected === 'Profesi')
                        ? 'menu-item-arrow-active'
                        : 'menu-item-arrow-inactive',
                    sidebarToggle ? 'lg:hidden' : ''
                ]"
                width="20"
                height="20"
                viewBox="0 0 20 20"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585"
                    stroke=""
                    stroke-width="1.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>
        </a>

        {{-- Dropdown --}}
        <div
            class="overflow-hidden transform translate"
            :class="(selected === 'Profesi') ? 'block' : 'hidden'"
        >
            <ul
                :class="sidebarToggle ? 'lg:hidden' : 'flex'"
                class="flex flex-col gap-1 mt-2 pl-9 menu-dropdown cyber-dropdown"
            >
                <li>
                    <a
                        href="{{ route('admin.profesi.dashboard') }}" {{-- Jangan lupa ganti route ini sesuai kebutuhan --}}
                        class="menu-dropdown-item group cyber-dropdown-item"
                        :class="page === 'list_profesi'
                            ? 'menu-dropdown-item-active cyber-dropdown-active'
                            : 'menu-dropdown-item-inactive'"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                        </svg>
                        Pendaftar Profesi
                    </a>
                </li>
                <li>
                    <a
                        href="{{ route('admin.profesi.cbt.index') }}" {{-- Jangan lupa ganti route ini sesuai kebutuhan --}}
                        class="menu-dropdown-item group cyber-dropdown-item"
                        :class="page === 'attendance_profesi'
                            ? 'menu-dropdown-item-active cyber-dropdown-active'
                            : 'menu-dropdown-item-inactive'"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        Nilai CBT & Wawancara
                    </a>
                </li>
                <li>
                    <a
                        href="{{ route('admin.profesi.setting.gelombang') }}" {{-- Jangan lupa ganti route ini sesuai kebutuhan --}}
                        class="menu-dropdown-item group cyber-dropdown-item"
                        :class="page === 'settings_register'
                            ? 'menu-dropdown-item-active cyber-dropdown-active'
                            : 'menu-dropdown-item-inactive'"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        Pengaturan Pendaftaran
                    </a>
                </li>
            </ul>
        </div>
    </li>
</ul>

@endif

          @if(!in_array(auth()->user()->role, ['admin_akademik', 'admin_farmasi',  'admin_teknik_mesin', 'admin_teknik_industri', 'admin_teknik_informatika', 'admin_sistem_informasi', 'admin_manajemen_s2', 'admin_ilmu_hukum', 'admin_psikologi', 'admin_akuntansi', 'admin_pendidikan_guru_sekolah_dasar', 'admin_pendidikan_pancasila_dan_kewarganegaraan', 'admin_pendidikan_agama_islam', 'admin_manajemen', 'admin_profesi']))
        <li>
  <a href="#" @click.prevent="selected = (selected === 'Pembayaran' ? '' : 'Pembayaran')" 
     class="menu-item group cyber-menu-item" 
     :class="(selected === 'Pembayaran') || (page === 'PembayaranRegister' || page === 'PembayaranUkt' || page === 'PindahanUkt') ? 'menu-item-active cyber-menu-active' : 'menu-item-inactive'">
    
    <svg :class="(selected === 'Pembayaran') || (page === 'PembayaranRegister' || page === 'PembayaranUkt' || page === 'PindahanUkt') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path fill-rule="evenodd" clip-rule="evenodd" d="M5.5 3.25C4.25736 3.25 3.25 4.25736 3.25 5.5V18.5C3.25 19.7426 4.25736 20.75 5.5 20.75H18.5001C19.7427 20.75 20.7501 19.7426 20.7501 18.5V5.5C20.7501 4.25736 19.7427 3.25 18.5001 3.25H5.5ZM4.75 5.5C4.75 5.08579 5.08579 4.75 5.5 4.75H18.5001C18.9143 4.75 19.2501 5.08579 19.2501 5.5V18.5C19.2501 18.9142 18.9143 19.25 18.5001 19.25H5.5C5.08579 19.25 4.75 18.9142 4.75 18.5V5.5ZM6.25005 9.7143C6.25005 9.30008 6.58583 8.9643 7.00005 8.9643L17 8.96429C17.4143 8.96429 17.75 9.30008 17.75 9.71429C17.75 10.1285 17.4143 10.4643 17 10.4643L7.00005 10.4643C6.58583 10.4643 6.25005 10.1285 6.25005 9.7143ZM6.25005 14.2857C6.25005 13.8715 6.58583 13.5357 7.00005 13.5357H17C17.4143 13.5357 17.75 13.8715 17.75 14.2857C17.75 14.6999 17.4143 15.0357 17 15.0357H7.00005C6.58583 15.0357 6.25005 14.6999 6.25005 14.2857Z" fill="currentColor" />
    </svg>
    
    <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Informasi Pembayaran</span>
    
    <svg class="absolute stroke-current menu-item-arrow right-2.5 top-1/2 -translate-y-1/2" 
         :class="[(selected === 'Pembayaran') ? 'rotate-180' : '', sidebarToggle ? 'lg:hidden' : '' ]" 
         width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
  </a>

  <div class="overflow-hidden" :class="(selected === 'Pembayaran') ? 'block' : 'hidden'">
    <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'" class="flex flex-col gap-1 mt-2 menu-dropdown pl-9 cyber-dropdown">
      <li>
        <a href="{{ route('admin.mandiri.register.index') }}" 
           class="menu-dropdown-item group cyber-dropdown-item" 
           :class="page === 'PembayaranRegister' ? 'menu-dropdown-item-active cyber-dropdown-active' : 'menu-dropdown-item-inactive'">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
          </svg>
          Mandiri Status Pembayaran Registrasi
        </a>
      </li>
      <li>
        <a href="{{ route('admin.mandiri.ukt.index') }}" 
           class="menu-dropdown-item group cyber-dropdown-item" 
           :class="page === 'PembayaranUkt' ? 'menu-dropdown-item-active cyber-dropdown-active' : 'menu-dropdown-item-inactive'">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z" />
          </svg>
          Mandiri Status Pembayaran UKT
        </a>
      </li>
                      @if(!in_array(auth()->user()->role, ['admin_keuangan', 'admin_teknik_mesin', 'admin_teknik_industri', 'admin_teknik_informatika', 'admin_sistem_informasi', 'admin_manajemen_s2', 'admin_ilmu_hukum', 'admin_psikologi', 'admin_akuntansi', 'admin_pendidikan_guru_sekolah_dasar', 'admin_pendidikan_pancasila_dan_kewarganegaraan', 'admin_pendidikan_agama_islam', 'admin_manajemen', 'admin_profesi']))
      <li>
        <a href="{{ route('admin.ubah-prodi.index') }}" 
           class="menu-dropdown-item group cyber-dropdown-item" 
           :class="page === 'PindahanProdi' ? 'menu-dropdown-item-active cyber-dropdown-active' : 'menu-dropdown-item-inactive'">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z" />
          </svg>
          Ganti Prodi
        </a>
      </li>
      @endif
    </ul>
  </div>
</li>
          @endif
@if(!in_array(auth()->user()->role, ['admin_keuangan', 'admin_teknik_mesin', 'admin_teknik_industri', 'admin_teknik_informatika', 'admin_sistem_informasi', 'admin_manajemen_s2', 'admin_ilmu_hukum', 'admin_psikologi', 'admin_akuntansi', 'admin_pendidikan_guru_sekolah_dasar', 'admin_pendidikan_pancasila_dan_kewarganegaraan', 'admin_pendidikan_agama_islam', 'admin_manajemen', 'admin_profesi']))
          <!-- Menu Tabel Data -->
          <li>
            <a href="{{ route('admin.moodle.index') }}" @click.prevent="selected = (selected === 'Tables' ? '' : 'Tables')" class="menu-item group cyber-menu-item" :class="(selected === 'Tables') || (page === 'basicTables' || page === 'dataTables' || page === 'moodle' || page === 'farmasi' || page === 'gelombang') ? 'menu-item-active cyber-menu-active' : 'menu-item-inactive'">
              <svg :class="(selected === 'Tables') || (page === 'basicTables' || page === 'dataTables' || page === 'moodle' || page === 'farmasi' || page === 'gelombang') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M3.25 5.5C3.25 4.25736 4.25736 3.25 5.5 3.25H18.5C19.7426 3.25 20.75 4.25736 20.75 5.5V18.5C20.75 19.7426 19.7426 20.75 18.5 20.75H5.5C4.25736 20.75 3.25 19.7426 3.25 18.5V5.5ZM5.5 4.75C5.08579 4.75 4.75 5.08579 4.75 5.5V8.58325L19.25 8.58325V5.5C19.25 5.08579 18.9142 4.75 18.5 4.75H5.5ZM19.25 10.0833H15.416V13.9165H19.25V10.0833ZM13.916 10.0833L10.083 10.0833V13.9165L13.916 13.9165V10.0833ZM8.58301 10.0833H4.75V13.9165H8.58301V10.0833ZM4.75 18.5V15.4165H8.58301V19.25H5.5C5.08579 19.25 4.75 18.9142 4.75 18.5ZM10.083 19.25V15.4165L13.916 15.4165V19.25H10.083ZM15.416 19.25V15.4165H19.25V18.5C19.25 18.9142 18.9142 19.25 18.5 19.25H15.416Z" fill="" />
              </svg>
              <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Tabel data</span>
              <svg class="absolute stroke-current menu-item-arrow right-2.5 top-1/2 -translate-y-1/2" :class="[(selected === 'Tables') ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'lg:hidden' : '' ]" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </a>
            <div class="overflow-hidden transform translate" :class="(selected === 'Tables') ? 'block' : 'hidden'">
              <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'" class="flex flex-col gap-1 mt-2 menu-dropdown pl-9 cyber-dropdown">
                <li>
                  <h3 class="mt-4 mb-2 text-xs uppercase leading-[20px] text-gray-400 cyber-subtitle">
                    <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">
                      Pengelolaan Semua Berkas
                    </span>
                  </h3>
                </li>
                
          @if(!in_array(auth()->user()->role, ['admin_farmasi']))
                <li>
                  <a href="{{ route('admin.profile-lengkap.index') }}" class="menu-dropdown-item group cyber-dropdown-item" :class="page === 'profile' ? 'menu-dropdown-item-active cyber-dropdown-active' : 'menu-dropdown-item-inactive'">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Data Peserta
                  </a>
                </li>
                @endif
                
          @if(!in_array(auth()->user()->role, ['admin_akademik']))
                <li>
                  <a href="{{ route('admin.farmasi.index') }}" class="menu-dropdown-item group cyber-dropdown-item" :class="page === 'profile' ? 'menu-dropdown-item-active cyber-dropdown-active' : 'menu-dropdown-item-inactive'">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                    Kelola Farmasi
                  </a>
                </li>
                
         @endif
          @if(!in_array(auth()->user()->role, ['admin_farmasi']))
                <li>
                  <a href="{{ route('admin.download-data.index') }}" class="menu-dropdown-item group cyber-dropdown-item" :class="page === 'biodata' ? 'menu-dropdown-item-active cyber-dropdown-active' : 'menu-dropdown-item-inactive'">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Export Semua Berkas
                  </a>
                </li>
@endif
                @if(!in_array(auth()->user()->role, ['admin_keuangan', 'admin_teknik_mesin', 'admin_teknik_industri', 'admin_teknik_informatika', 'admin_sistem_informasi', 'admin_manajemen_s2', 'admin_ilmu_hukum', 'admin_psikologi', 'admin_akuntansi', 'admin_pendidikan_guru_sekolah_dasar', 'admin_pendidikan_pancasila_dan_kewarganegaraan', 'admin_pendidikan_agama_islam', 'admin_manajemen', 'admin_profesi']))
                <li>
                  <a href="{{ route('admin.pengelolaan.index') }}" class="menu-dropdown-item group cyber-dropdown-item" :class="page === 'profile' ? 'menu-dropdown-item-active cyber-dropdown-active' : 'menu-dropdown-item-inactive'">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                    Kelola Jalur
                  </a>
                </li>
                <li>
                  <a href="{{ route('admin.mastergelombang.index') }}" class="menu-dropdown-item group cyber-dropdown-item" :class="page === 'biodata' ? 'menu-dropdown-item-active cyber-dropdown-active' : 'menu-dropdown-item-inactive'">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Kelola Gelombang 
                  </a>
                </li>
                <li>
                  <a href="{{ route('admin.qr-attendance.index') }}" class="menu-dropdown-item group cyber-dropdown-item" :class="page === 'biodata' ? 'menu-dropdown-item-active cyber-dropdown-active' : 'menu-dropdown-item-inactive'">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Generate QR daftar Hadir Orang tua
                  </a>
                </li>
                <li>
                  <a href="{{ route('admin.master-moodle-lulus.index') }}" class="menu-dropdown-item group cyber-dropdown-item {{ request()->is('admin/master-moodle-lulus*') ? 'menu-dropdown-item-active cyber-dropdown-active' : 'menu-dropdown-item-inactive' }}">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Kelola Kelulusan
                  </a>
                </li>
                <li>
                  <a href="{{ route('admin.prodi.index') }}" class="menu-dropdown-item group cyber-dropdown-item {{ request()->is('admin/prodi*') ? 'menu-dropdown-item-active cyber-dropdown-active' : 'menu-dropdown-item-inactive' }}">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    Kelola Prodi
                  </a>
                </li>
                <li>
                  <h3 class="mt-4 mb-2 text-xs uppercase leading-[20px] text-gray-400 cyber-subtitle">
                    <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">
                      Akun Moodle
                    </span>
                  </h3>
                </li>
                <li>
                  <div id="syncProgress" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
                    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                      <div class="mt-3 text-center">
                        <!-- Loading Spinner -->
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
                          <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                          </svg>
                        </div>

                        <!-- Title -->
                        <h3 id="syncTitle" class="text-lg font-medium text-gray-900 mt-4">Memproses...</h3>

                        <!-- Message -->
                        <p id="syncMessage" class="text-sm text-gray-500 mt-2"></p>

                        <!-- Details -->
                        <div id="syncDetails" class="mt-4 text-left max-h-40 overflow-y-auto"></div>

                        <!-- Progress Bar -->
                        <div class="mt-4 w-full bg-gray-200 rounded-full h-2.5">
                          <div id="progressBar" class="bg-blue-600 h-2.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>

                        <!-- Cancel Button -->
                        <div class="mt-6">
                          <button onclick="hideSyncProgress()" class="px-4 py-2 bg-gray-300 text-gray-700 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                            Tutup
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
<button class="menu-dropdown-item group cyber-dropdown-item" onclick="refreshAttempts()">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
    </svg>
   Ubah Nilai Peserta
</button>

<script>
    let isSyncing = false;

    function refreshAttempts() {
        if (isSyncing) return;
        
        if(!confirm('Apakah Anda yakin ingin menyinkronkan ulang semua data nilai dari Moodle? Proses ini mungkin memakan waktu.')) {
            return;
        }

        isSyncing = true;
        
        showSyncProgress('Memulai Sinkronisasi', 'Menghubungkan ke Moodle...');
        
        processBatch(0);
    }

    function processBatch(offset) {
        const LIMIT = 5; 

        fetch('{{ route("admin.moodle.quiz-complete.force-sync") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                offset: offset,
                limit: LIMIT
            })
        })
        .then(response => {
            if (!response.ok) throw new Error("HTTP Status: " + response.status);
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const totalUsers = parseInt(data.total_users) || 0;
                const processedInBatch = parseInt(data.processed_in_batch) || 0;
                const currentProcessed = offset + processedInBatch;
                let percent = data.progress !== undefined ? parseInt(data.progress) : 0;
                if (isNaN(percent)) percent = 0;

                if (data.completed) {
                    updateSyncProgress('✅ Sinkronisasi Selesai!', `
                        <div class="text-center mt-2">
                            <div class="w-full bg-gray-200 rounded-full h-2.5 mb-2">
                                <div class="bg-green-500 h-2.5 rounded-full" style="width: 100%"></div>
                            </div>
                            <p class="text-green-700 font-bold">100% Selesai</p>
                            <p class="text-sm text-gray-600 mt-1">Total ${totalUsers} data berhasil diproses.</p>
                            <p class="text-xs text-gray-400 mt-2">Mengalihkan halaman...</p>
                        </div>
                    `);
                    
                   setTimeout(() => {
    window.open(
        "https://pmb.ubpkarawang.ac.id/admin/moodle/quiz-complete",
        "_blank"
    );
}, 1500);

                    isSyncing = false;
                } else {
                    updateSyncProgress(`Sinkronisasi Berjalan (${percent}%)`, `
                        <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 mt-3">
                            <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-300" style="width: ${percent}%"></div>
                        </div>
                        <div class="flex justify-between text-xs mt-2 text-gray-600">
                            <span>Proses: <strong>${currentProcessed}</strong> / ${totalUsers}</span>
                            <span>Batch: ${data.results.success} OK, ${data.results.failed} Gagal</span>
                        </div>
                    `);
                    
                    processBatch(offset + processedInBatch); 
                }
            } else {
                throw new Error(data.message || 'Unknown error');
            }
        })
        .catch(error => {
            console.error('Sync Error:', error);
            updateSyncProgress('❌ Terjadi Kesalahan', `
                <div class="text-red-600 text-sm p-2 bg-red-50 rounded border border-red-200">
                    <p class="font-bold">Gagal pada data ke-${offset}</p>
                    <p class="mt-1">${error.message}</p>
                    <button onclick="processBatch(${offset})" class="mt-3 px-4 py-2 bg-red-600 text-white rounded shadow hover:bg-red-700 w-full">
                        Coba Lanjutkan
                    </button>
                    <button onclick="window.location.reload()" class="mt-2 px-4 py-2 bg-gray-500 text-white rounded shadow hover:bg-gray-600 w-full">
                        Batalkan
                    </button>
                </div>
            `);
            isSyncing = false;
        });
    }

    function showSyncProgress(title, message) {
        const el = document.getElementById('syncProgress');
        if(el) el.classList.remove('hidden');
        updateSyncProgress(title, message);
    }

    function updateSyncProgress(title, htmlContent) {
    const titleEl = document.getElementById('syncTitle');
    const detailEl = document.getElementById('syncDetails');
    if(titleEl) titleEl.innerText = title;
    if(detailEl) detailEl.innerHTML = htmlContent; 
    if(!titleEl || !detailEl) {
        console.warn("Elemen modal sync tidak ditemukan di DOM.");
    }
}

    function hideSyncProgress() {
        const el = document.getElementById('syncProgress');
        if(el) el.classList.add('hidden');
    }
</script>
<div id="syncProgress" class="fixed inset-0 z-[9999] hidden flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-md border border-gray-200 dark:border-gray-700">
        <h3 id="syncTitle" class="text-lg font-bold text-gray-900 dark:text-white mb-4 text-center">Menyiapkan...</h3>
        <div id="syncDetails" class="space-y-3">
            </div>
    </div>
</div>
                </li>
                <li>
                  <a href="{{ route('admin.moodle.index') }}" class="menu-dropdown-item group cyber-dropdown-item" :class="page === 'moodle' ? 'menu-dropdown-item-active cyber-dropdown-active' : 'menu-dropdown-item-inactive'">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Datas Moodle
                  </a>
                </li>
                @endif

                <!-- Berkas Users - HIDDEN untuk admin_akademik dan admin_farmasi -->
                @if(!in_array(auth()->user()->role, ['admin_akademik', 'admin_farmasi']))
                <li>
                  <h3 class="mt-4 mb-2 text-xs uppercase leading-[20px] text-gray-400 cyber-subtitle">
                    <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">
                      Berkas users
                    </span>
                  </h3>
                </li>
                <li>
                  <a href="{{ route('admin.userprofile.index') }}" class="menu-dropdown-item group cyber-dropdown-item" :class="page === 'profile' ? 'menu-dropdown-item-active cyber-dropdown-active' : 'menu-dropdown-item-inactive'">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zM2 20h20" />
                    </svg>
                    Akun Users
                  </a>
                </li>
                 <li>
                  <a href="{{ route('admin.group-gelombang.index') }}" class="menu-dropdown-item group cyber-dropdown-item" :class="page === 'gelombang' ? 'menu-dropdown-item-active cyber-dropdown-active' : 'menu-dropdown-item-inactive'">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Group Gelombang
                  </a>
                </li>

                @if(!in_array(auth()->user()->role, ['admin_keuangan', 'admin_teknik_mesin', 'admin_teknik_industri', 'admin_teknik_informatika', 'admin_sistem_informasi', 'admin_manajemen_s2', 'admin_ilmu_hukum', 'admin_psikologi', 'admin_akuntansi', 'admin_pendidikan_guru_sekolah_dasar', 'admin_pendidikan_pancasila_dan_kewarganegaraan', 'admin_pendidikan_agama_islam', 'admin_manajemen', 'admin_profesi']))
                <li>
                  <a href="{{ route('admin.biodata.index') }}" class="menu-dropdown-item group cyber-dropdown-item" :class="page === 'biodata' ? 'menu-dropdown-item-active cyber-dropdown-active' : 'menu-dropdown-item-inactive'">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0" />
                    </svg>
                    Biodata
                  </a>
                </li>
                @endif
                                @if(!in_array(auth()->user()->role, ['admin_keuangan', 'admin_teknik_mesin', 'admin_teknik_industri', 'admin_teknik_informatika', 'admin_sistem_informasi', 'admin_manajemen_s2', 'admin_ilmu_hukum', 'admin_psikologi', 'admin_akuntansi', 'admin_pendidikan_guru_sekolah_dasar', 'admin_pendidikan_pancasila_dan_kewarganegaraan', 'admin_pendidikan_agama_islam', 'admin_manajemen', 'admin_profesi']))
                <li>
                  <a href="{{ route('admin.generate-va.index') }}" class="menu-dropdown-item group cyber-dropdown-item" :class="page === 'biodata' ? 'menu-dropdown-item-active cyber-dropdown-active' : 'menu-dropdown-item-inactive'">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                    </svg>
                    Generate SLIK
                  </a>
                </li>
                @endif
                @endif
                               @if(!in_array(auth()->user()->role, ['admin_keuangan', 'admin_teknik_mesin', 'admin_teknik_industri', 'admin_teknik_informatika', 'admin_sistem_informasi', 'admin_manajemen_s2', 'admin_ilmu_hukum', 'admin_psikologi', 'admin_akuntansi', 'admin_pendidikan_guru_sekolah_dasar', 'admin_pendidikan_pancasila_dan_kewarganegaraan', 'admin_pendidikan_agama_islam', 'admin_manajemen', 'admin_profesi']))
                <li>
                  <a href="{{ route('admin.berkas.index') }}" class="menu-dropdown-item group cyber-dropdown-item" :class="page === 'berkas' ? 'menu-dropdown-item-active cyber-dropdown-active' : 'menu-dropdown-item-inactive'">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Berkas
                  </a>
                </li>
                @endif
                <!-- Komunikasi Users - HIDDEN untuk admin_akademik dan admin_farmasi -->
                               @if(!in_array(auth()->user()->role, ['admin_keuangan', 'admin_teknik_mesin', 'admin_teknik_industri', 'admin_teknik_informatika', 'admin_sistem_informasi', 'admin_manajemen_s2', 'admin_ilmu_hukum', 'admin_psikologi', 'admin_akuntansi', 'admin_pendidikan_guru_sekolah_dasar', 'admin_pendidikan_pancasila_dan_kewarganegaraan', 'admin_pendidikan_agama_islam', 'admin_manajemen', 'admin_profesi']))
                <li>
                  <h3 class="mt-4 mb-2 text-xs uppercase leading-[20px] text-gray-400 cyber-subtitle">
                    <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">
                      Komunikasi Users
                    </span>
                  </h3>
                </li>
                <li>
                  <a href="{{ route('admin.chat.index') }}" class="menu-dropdown-item group cyber-dropdown-item" :class="page === 'chat' ? 'menu-dropdown-item-active cyber-dropdown-active' : 'menu-dropdown-item-inactive'">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    Chat Users
                  </a>
                </li>
                <li>
                  <a href="{{route('admin.whatsapp.qr') }}" class="menu-dropdown-item group cyber-dropdown-item" :class="page === 'chat' ? 'menu-dropdown-item-active cyber-dropdown-active' : 'menu-dropdown-item-inactive'">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    Whatsapp Apps
                  </a>
                </li>
                @endif
              </ul>
            </div>
          </li>
          @endif
                @if(!in_array(auth()->user()->role, ['admin_keuangan', 'admin_teknik_mesin', 'admin_teknik_industri', 'admin_teknik_informatika', 'admin_sistem_informasi', 'admin_manajemen_s2', 'admin_ilmu_hukum', 'admin_psikologi', 'admin_akuntansi', 'admin_pendidikan_guru_sekolah_dasar', 'admin_pendidikan_pancasila_dan_kewarganegaraan', 'admin_pendidikan_agama_islam', 'admin_manajemen', 'admin_profesi']))
          <!-- Menu Generate NIM -->
          <li>
            <a href="#" @click.prevent="selected = (selected === 'Nim' ? '' : 'Nim')" class="menu-item group cyber-menu-item" :class="(selected === 'Nim') ? 'menu-item-active cyber-menu-active' : 'menu-item-inactive'">
              <svg :class="(selected === 'Nim')  ? 'menu-item-icon-active' : 'menu-item-icon-inactive'" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M3.25 5.5C3.25 4.25736 4.25736 3.25 5.5 3.25H18.5C19.7426 3.25 20.75 4.25736 20.75 5.5V18.5C20.75 19.7426 19.7426 20.75 18.5 20.75H5.5C4.25736 20.75 3.25 19.7426 3.25 18.5V5.5ZM5.5 4.75C5.08579 4.75 4.75 5.08579 4.75 5.5V8.58325L19.25 8.58325V5.5C19.25 5.08579 18.9142 4.75 18.5 4.75H5.5ZM19.25 10.0833H15.416V13.9165H19.25V10.0833ZM13.916 10.0833L10.083 10.0833V13.9165L13.916 13.9165V10.0833ZM8.58301 10.0833H4.75V13.9165H8.58301V10.0833ZM4.75 18.5V15.4165H8.58301V19.25H5.5C5.08579 19.25 4.75 18.9142 4.75 18.5ZM10.083 19.25V15.4165L13.916 15.4165V19.25H10.083ZM15.416 19.25V15.4165H19.25V18.5C19.25 18.9142 18.9142 19.25 18.5 19.25H15.416Z" fill="" />
              </svg>
              <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Generate NIM</span>
              <svg class="absolute stroke-current menu-item-arrow right-2.5 top-1/2 -translate-y-1/2" :class="[(selected === 'Nim') ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'lg:hidden' : '' ]" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </a>
            <div class="overflow-hidden transform translate" :class="(selected === 'Nim') ? 'block' : 'hidden'">
              <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'" class="flex flex-col gap-1 mt-2 menu-dropdown pl-9 cyber-dropdown">
                <li>
                  <h3 class="mt-4 mb-2 text-xs uppercase leading-[20px] text-gray-400 cyber-subtitle">
                    <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">
                      Pengelolaan NIM semua JENJANG
                    </span>
                  </h3>
                </li>
              
                <li>
                  <a href="{{ route('admin.generate-nim.index', ['tab'=>'S1']) }}" class="menu-dropdown-item group cyber-dropdown-item" :class="page === 'Nim' ? 'menu-dropdown-item-active cyber-dropdown-active' : 'menu-dropdown-item-inactive'">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                    </svg>
                    Generate NIM
                  </a>
                </li>
                <li>
                  <h3 class="mt-4 mb-2 text-xs uppercase leading-[20px] text-gray-400 cyber-subtitle">
                    <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">
                      Pengelolaan Elearning
                    </span>
                  </h3>
                </li>
              
                <li>
                  <a href="{{ route('admin.generate-nim.elearning-list', ['tab'=>'S1']) }}" class="menu-dropdown-item group cyber-dropdown-item" :class="page === 'Nim' ? 'menu-dropdown-item-active cyber-dropdown-active' : 'menu-dropdown-item-inactive'">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                    </svg>
                    Generate Elearning (BOC)
                  </a>
                </li>
                <!--<li>-->
                <!--  <a href="" class="menu-dropdown-item group cyber-dropdown-item" :class="page === 'Nim' ? 'menu-dropdown-item-active cyber-dropdown-active' : 'menu-dropdown-item-inactive'">-->
                <!--    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">-->
                <!--      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />-->
                <!--    </svg>-->
                <!--    S1 NIM manual-->
                <!--  </a>-->
                <!--</li>-->
              </ul>
            </div>
            <!--<div class="overflow-hidden transform translate" :class="(selected === 'Nim') ? 'block' : 'hidden'">-->
            <!--  <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'" class="flex flex-col gap-1 mt-2 menu-dropdown pl-9 cyber-dropdown">-->
            <!--    <li>-->
            <!--      <h3 class="mt-4 mb-2 text-xs uppercase leading-[20px] text-gray-400 cyber-subtitle">-->
            <!--        <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">-->
            <!--          Pengelolaan NIM S2-->
            <!--        </span>-->
            <!--      </h3>-->
            <!--    </li>-->
              
            <!--    <li>-->
            <!--      <a href="{{ route('admin.generate-nim.index', ['tab'=>'S2']) }}" class="menu-dropdown-item group cyber-dropdown-item" :class="page === 'Nim' ? 'menu-dropdown-item-active cyber-dropdown-active' : 'menu-dropdown-item-inactive'">-->
            <!--        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">-->
            <!--          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />-->
            <!--        </svg>-->
            <!--        S2 NIM otomatis-->
            <!--      </a>-->
            <!--    </li>-->
            <!--    <li>-->
            <!--      <a href="" class="menu-dropdown-item group cyber-dropdown-item" :class="page === 'Nim' ? 'menu-dropdown-item-active cyber-dropdown-active' : 'menu-dropdown-item-inactive'">-->
            <!--        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">-->
            <!--          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />-->
            <!--        </svg>-->
            <!--        S2 NIM manual-->
            <!--      </a>-->
            <!--    </li>-->
            <!--  </ul>-->
            <!--</div>-->
            <div class="overflow-hidden transform translate" :class="(selected === 'Nim') ? 'block' : 'hidden'">
              <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'" class="flex flex-col gap-1 mt-2 menu-dropdown pl-9 cyber-dropdown">
                <li>
                  <h3 class="mt-4 mb-2 text-xs uppercase leading-[20px] text-gray-400 cyber-subtitle">
                    <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">
                      Pengelolaan NIM Apoteker
                    </span>
                  </h3>
                </li>
                <!--<li>-->
                <!--  <a href="{{ route('admin.generate-nim.index', ['tab'=>'Apoteker']) }}" class="menu-dropdown-item group cyber-dropdown-item" :class="page === 'Nim' ? 'menu-dropdown-item-active cyber-dropdown-active' : 'menu-dropdown-item-inactive'">-->
                <!--    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">-->
                <!--      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />-->
                <!--    </svg>-->
                <!--    Apoteker NIM otomatis-->
                <!--  </a>-->
                <!--</li>-->
                <li>
                  <a href="{{ route('admin.generate-nim.list') }}" class="menu-dropdown-item group cyber-dropdown-item {{ request()->routeIs('admin.generate-nim.list') ? 'menu-dropdown-item-active cyber-dropdown-active' : 'menu-dropdown-item-inactive' }}">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                   Daftarkan ke SIPT
                  </a>
                </li>
              </ul>
            </div>
            <!--<div class="overflow-hidden transform translate" :class="(selected === 'Nim') ? 'block' : 'hidden'">-->
            <!--  <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'" class="flex flex-col gap-1 mt-2 menu-dropdown pl-9 cyber-dropdown">-->
            <!--    <li>-->
            <!--      <h3 class="mt-4 mb-2 text-xs uppercase leading-[20px] text-gray-400 cyber-subtitle">-->
            <!--        <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">-->
            <!--          Pengelolaan NIM Beasiswa Keluarga-->
            <!--        </span>-->
            <!--      </h3>-->
            <!--    </li>-->
              
            <!--    <li>-->
            <!--      <a href="" class="menu-dropdown-item group cyber-dropdown-item" :class="page === 'Nim' ? 'menu-dropdown-item-active cyber-dropdown-active' : 'menu-dropdown-item-inactive'">-->
            <!--        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">-->
            <!--          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />-->
            <!--        </svg>-->
            <!--        Generate NIM Manual-->
            <!--      </a>-->
            <!--    </li>-->
            <!--  </ul>-->
            <!--</div>-->
          </li>
  <!-- Menu Forms - HIDDEN untuk admin_akademik dan admin_farmasi -->
          @if(!in_array(auth()->user()->role, ['admin_akademik', 'admin_farmasi', 'admin_profesi']))
          <li>
            <a href="#" @click.prevent="selected = (selected === 'Forms' ? '' : 'Forms')" class="menu-item group cyber-menu-item" :class="(selected === 'Forms') || (page === 'formElements' || page === 'formLayout' || page === 'proFormElements' || page === 'proFormLayout') ? 'menu-item-active cyber-menu-active' : 'menu-item-inactive'">
              <svg :class="(selected === 'Forms') || (page === 'formElements' || page === 'formLayout' || page === 'proFormElements' || page === 'proFormLayout') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M5.5 3.25C4.25736 3.25 3.25 4.25736 3.25 5.5V18.5C3.25 19.7426 4.25736 20.75 5.5 20.75H18.5001C19.7427 20.75 20.7501 19.7426 20.7501 18.5V5.5C20.7501 4.25736 19.7427 3.25 18.5001 3.25H5.5ZM4.75 5.5C4.75 5.08579 5.08579 4.75 5.5 4.75H18.5001C18.9143 4.75 19.2501 5.08579 19.2501 5.5V18.5C19.2501 18.9142 18.9143 19.25 18.5001 19.25H5.5C5.08579 19.25 4.75 18.9142 4.75 18.5V5.5ZM6.25005 9.7143C6.25005 9.30008 6.58583 8.9643 7.00005 8.9643L17 8.96429C17.4143 8.96429 17.75 9.30008 17.75 9.71429C17.75 10.1285 17.4143 10.4643 17 10.4643L7.00005 10.4643C6.58583 10.4643 6.25005 10.1285 6.25005 9.7143ZM6.25005 14.2857C6.25005 13.8715 6.58583 13.5357 7.00005 13.5357H17C17.4143 13.5357 17.75 13.8715 17.75 14.2857C17.75 14.6999 17.4143 15.0357 17 15.0357H7.00005C6.58583 15.0357 6.25005 14.6999 6.25005 14.2857Z" fill="" />
              </svg>
              <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Forms</span>
              <svg class="absolute stroke-current menu-item-arrow right-2.5 top-1/2 -translate-y-1/2" :class="[(selected === 'Forms') ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'lg:hidden' : '' ]" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </a>
            <div class="overflow-hidden transform translate" :class="(selected === 'Forms') ? 'block' : 'hidden'">
              <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'" class="flex flex-col gap-1 mt-2 menu-dropdown pl-9 cyber-dropdown">
                <li>
                  <a href="{{ route('admin.ujian-countdown.index') }}" class="menu-dropdown-item group cyber-dropdown-item" :class="page === 'formElements' ? 'menu-dropdown-item-active cyber-dropdown-active' : 'menu-dropdown-item-inactive'">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Ujian
                  </a>
                </li>
                 <li>
                  <a href="{{ route('admin.bni.index') }}" class="menu-dropdown-item group cyber-dropdown-item" :class="page === 'formLayout' ? 'menu-dropdown-item-active cyber-dropdown-active' : 'menu-dropdown-item-inactive'">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 5H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    Sync Gelombang 1 PAID
                  </a>
                </li>
              </ul>
            </div>
          </li>
          @endif
          <!-- Menu Multi Settings - HIDDEN untuk admin_akademik dan admin_farmasi -->
          @if(!in_array(auth()->user()->role, ['admin_akademik', 'admin_farmasi']))
          <li>
            <a href="#" @click.prevent="selected = (selected === 'Settings' ? '' : 'Settings')" class="menu-item group cyber-menu-item" :class="(selected === 'Settings') || (page === 'akun' || page === 'target' || page === 'va' || page === 'nilai') ? 'menu-item-active cyber-menu-active' : 'menu-item-inactive'">
              <svg :class="(selected === 'Settings') || (page === 'akun' || page === 'target' || page === 'va' || page === 'nilai') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M8.50391 4.25C8.50391 3.83579 8.83969 3.5 9.25391 3.5H15.2777C15.4766 3.5 15.6674 3.57902 15.8081 3.71967L18.2807 6.19234C18.4214 6.333 18.5004 6.52376 18.5004 6.72268V16.75C18.5004 17.1642 18.1646 17.5 17.7504 17.5H16.248V17.4993H14.748V17.5H9.25391C8.83969 17.5 8.50391 17.1642 8.50391 16.75V4.25ZM14.748 19H9.25391C8.01126 19 7.00391 17.9926 7.00391 16.75V6.49854H6.24805C5.83383 6.49854 5.49805 6.83432 5.49805 7.24854V19.75C5.49805 20.1642 5.83383 20.5 6.24805 20.5H13.998C14.4123 20.5 14.748 20.1642 14.748 19.75L14.748 19ZM7.00391 4.99854V4.25C7.00391 3.00736 8.01127 2 9.25391 2H15.2777C15.8745 2 16.4468 2.23705 16.8687 2.659L19.3414 5.13168C19.7634 5.55364 20.0004 6.12594 20.0004 6.72268V16.75C20.0004 17.9926 18.9931 19 17.7504 19H16.248L16.248 19.75C16.248 20.9926 15.2407 22 13.998 22H6.24805C5.00541 22 3.99805 20.9926 3.99805 19.75V7.24854C3.99805 6.00589 5.00541 4.99854 6.24805 4.99854H7.00391Z" fill="" />
              </svg>
              <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Multi Settings</span>
              <svg class="absolute stroke-current menu-item-arrow right-2.5 top-1/2 -translate-y-1/2" :class="[(selected === 'Settings') ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'lg:hidden' : '' ]" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </a>
            <div class="overflow-hidden transform translate" :class="(selected === 'Settings') ? 'block' : 'hidden'">
              <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'" class="flex flex-col gap-1 mt-2 menu-dropdown pl-9 cyber-dropdown">
                <li>
                  <a href="{{ route('admin.akun.index') }}" class="menu-dropdown-item group cyber-dropdown-item" :class="page === 'akun' ? 'menu-dropdown-item-active cyber-dropdown-active' : 'menu-dropdown-item-inactive'">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Semua Akun
                  </a>
                </li>
                
                {{-- Pengaturan Jadwal Farmasi --}}
                <li class="relative my-1">
                    @php
                        $isJadwalActive = request()->routeIs('admin.jadwal.*');
                    @endphp
                
                    <a href="{{ route('admin.jadwal.create') }}" 
                       class="group flex w-full items-center justify-between px-4 py-3 transition-all duration-300 rounded-lg mx-auto"
                       style="width: 95%;"
                       class="{{ $isJadwalActive 
                           ? 'bg-blue-900/10 text-blue-500' 
                           : 'text-gray-400 hover:bg-gray-800 hover:text-white' 
                       }}">
                       
                        <div class="flex items-center gap-3 overflow-hidden">
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5 transition-transform group-hover:scale-110 {{ $isJadwalActive ? 'text-blue-500' : 'text-inherit' }}" 
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                            </div>
                
                            <div class="flex flex-col text-left overflow-hidden">
                                <span class="text-sm font-medium truncate">Jadwal Saringan</span>
                                <span class="text-[10px] opacity-80 truncate w-full font-mono {{ $isJadwalActive ? 'text-blue-400' : 'text-gray-500 group-hover:text-gray-300' }}">
                                    Prodi Farmasi
                                </span>
                            </div>
                        </div>
                
                        <div class="flex-shrink-0 ml-2 relative flex items-center justify-center">
                            @if($isJadwalActive)
                                <div class="relative flex h-3 w-3">
                                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                                  <span class="relative inline-flex rounded-full h-3 w-3 bg-blue-500"></span>
                                </div>
                            @else
                                <div class="h-2 w-2 rounded-full bg-gray-600 group-hover:bg-gray-400 transition-colors"></div>
                            @endif
                        </div>
                    </a>
                </li>
                
                
                
                
                {{-- Pengaturan Sistem --}}
                <li>
                    <a href="{{ route('admin.settings.index') }}" class="menu-dropdown-item group cyber-dropdown-item" :class="page === 'settings' ? 'menu-dropdown-item-active cyber-dropdown-active' : 'menu-dropdown-item-inactive'">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Pengaturan Sistem
                    </a>
                </li>
                
                {{-- GIS --}}
                <li class="relative my-1">
                    <a href="{{ route('admin.gis.index') }}" 
                       class="group flex w-full items-center justify-between px-4 py-3 transition-all duration-300 rounded-lg mx-auto"
                       style="width: 95%;"
                       class="{{ request()->routeIs('admin.gis.*') 
                            ? 'bg-blue-900/10 text-blue-500' 
                            : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        
                        <div class="flex items-center gap-3 overflow-hidden">
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5 transition-transform group-hover:scale-110" 
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 7m0 13V7"></path>
                                </svg>
                            </div>
                
                            <div class="flex flex-col text-left overflow-hidden">
                                <span class="text-sm font-medium truncate">Peta Sebaran (GIS)</span>
                                <span class="text-[10px] opacity-60 truncate">Visualisasi Data Wilayah</span>
                            </div>
                        </div>
                
                        <div class="flex-shrink-0 ml-2">
                            <svg class="w-4 h-4 opacity-50 group-hover:opacity-100 transition-opacity" 
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </a>
                </li>
              </ul>
            </div>
          </li>
          @endif

          <!-- Menu Halaman - HIDDEN untuk admin_akademik dan admin_farmasi -->
          @if(!in_array(auth()->user()->role, ['admin_akademik', 'admin_farmasi']))
          <li>
            <a href="#" @click.prevent="selected = (selected === 'Pages' ? '' : 'Pages')" class="menu-item group cyber-menu-item" :class="(selected === 'Pages') || (page === 'fileManager' || page === 'pricingTables' || page === 'blank' || page === 'page404' || page === 'page500' || page === 'page503' || page === 'success' || page === 'faq' || page === 'comingSoon' || page === 'maintenance') ? 'menu-item-active cyber-menu-active' : 'menu-item-inactive'">
              <svg :class="(selected === 'Pages') || (page === 'fileManager' || page === 'pricingTables' || page === 'blank' || page === 'page404' || page === 'page500' || page === 'page503' || page === 'success' || page === 'faq' || page === 'comingSoon' || page === 'maintenance') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M8.50391 4.25C8.50391 3.83579 8.83969 3.5 9.25391 3.5H15.2777C15.4766 3.5 15.6674 3.57902 15.8081 3.71967L18.2807 6.19234C18.4214 6.333 18.5004 6.52376 18.5004 6.72268V16.75C18.5004 17.1642 18.1646 17.5 17.7504 17.5H16.248V17.4993H14.748V17.5H9.25391C8.83969 17.5 8.50391 17.1642 8.50391 16.75V4.25ZM14.748 19H9.25391C8.01126 19 7.00391 17.9926 7.00391 16.75V6.49854H6.24805C5.83383 6.49854 5.49805 6.83432 5.49805 7.24854V19.75C5.49805 20.1642 5.83383 20.5 6.24805 20.5H13.998C14.4123 20.5 14.748 20.1642 14.748 19.75L14.748 19ZM7.00391 4.99854V4.25C7.00391 3.00736 8.01127 2 9.25391 2H15.2777C15.8745 2 16.4468 2.23705 16.8687 2.659L19.3414 5.13168C19.7634 5.55364 20.0004 6.12594 20.0004 6.72268V16.75C20.0004 17.9926 18.9931 19 17.7504 19H16.248L16.248 19.75C16.248 20.9926 15.2407 22 13.998 22H6.24805C5.00541 22 3.99805 20.9926 3.99805 19.75V7.24854C3.99805 6.00589 5.00541 4.99854 6.24805 4.99854H7.00391Z" fill="" />
              </svg>
              <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Halaman</span>
              <svg class="absolute stroke-current menu-item-arrow right-2.5 top-1/2 -translate-y-1/2" :class="[(selected === 'Pages') ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'lg:hidden' : '' ]" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </a>
            <div class="overflow-hidden transform translate" :class="(selected === 'Pages') ? 'block' : 'hidden'">
              <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'" class="flex flex-col gap-1 mt-2 menu-dropdown pl-9 cyber-dropdown">
                <li>
                  <a href="{{ route('admin.posts.index') }}" class="menu-dropdown-item group cyber-dropdown-item" :class="page === 'blank' ? 'menu-dropdown-item-active cyber-dropdown-active' : 'menu-dropdown-item-inactive'">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Postingan
                  </a>
                </li>
                <li>
                  <a href="404.html" class="menu-dropdown-item group cyber-dropdown-item" :class="page === 'page404' ? 'menu-dropdown-item-active cyber-dropdown-active' : 'menu-dropdown-item-inactive'">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                    404 Error
                  </a>
                </li>
                <li>
                  <a href="" class="menu-dropdown-item group cyber-dropdown-item" :class="page === 'calendar' ? 'menu-dropdown-item-active cyber-dropdown-active' : 'menu-dropdown-item-inactive'">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Kalender
                  </a>
                </li>
              </ul>
            </div>
          </li>
          @endif
        </ul>
      </div>
    </nav>

         @endif
   @if(!in_array(auth()->user()->role, ['admin_akademik', 'admin_farmasi', 'admin_keuangan',  'admin_teknik_mesin', 'admin_teknik_industri', 'admin_teknik_informatika', 'admin_sistem_informasi', 'admin_manajemen_s2', 'admin_ilmu_hukum', 'admin_psikologi', 'admin_akuntansi', 'admin_pendidikan_guru_sekolah_dasar', 'admin_pendidikan_pancasila_dan_kewarganegaraan', 'admin_pendidikan_agama_islam', 'admin_manajemen', 'admin_profesi']))
<!-- Promo Code Section -->
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-theme-sm border border-gray-200 dark:border-gray-700 p-6">
    <div class="flex items-start justify-between mb-4">
        <div class="flex-1">
            <h3 class="text-theme-xl font-semibold text-gray-900 dark:text-white mb-2">
                Buat Code OTP Promo
            </h3>
            <p class="text-gray-500 dark:text-gray-400 text-theme-sm leading-relaxed">
                Gunakan page ini untuk membuat kode promo untuk CAMABA.
            </p>
        </div>
        <div class="flex-shrink-0 ml-4">
            <div class="w-12 h-12 bg-brand-50 dark:bg-brand-500/15 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-brand-500 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>
    
    <a href="{{ route('admin.promocode.index') }}" target="_blank" rel="nofollow" 
       class="inline-flex items-center justify-center gap-2 bg-brand-500 hover:bg-brand-600 text-white font-medium py-2.5 px-4 rounded-lg transition-colors duration-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        Buat Code Promo
    </a>
</div>
@endif
  </div>
</aside>

<style>
  /* Cyberpunk Sidebar Effects */
  .cyber-sidebar {
    position: relative;
    overflow: hidden;
  }

  .cyber-sidebar::before {
    content: '';
    position: absolute;
    top: 0;
    right: -2px;
    width: 3px;
    height: 100%;
    background: linear-gradient(180deg,
        #4f46e5 0%,
        #818cf8 25%,
        #a5b4fc 50%,
        #818cf8 75%,
        #4f46e5 100%);
    border-radius: 2px;
    opacity: 0.7;
    box-shadow: 0 0 10px rgba(79, 70, 229, 0.5);
    animation: cyber-pulse 3s ease-in-out infinite;
  }

  .dark .cyber-sidebar::before {
    background: linear-gradient(180deg,
        #6366f1 0%,
        #818cf8 25%,
        #a5b4fc 50%,
        #818cf8 75%,
        #6366f1 100%);
    box-shadow: 0 0 15px rgba(99, 102, 241, 0.7);
    opacity: 0.8;
  }

  @keyframes cyber-pulse {
    0%, 100% {
      opacity: 0.5;
      box-shadow: 0 0 10px rgba(79, 70, 229, 0.3);
    }
    50% {
      opacity: 0.8;
      box-shadow: 0 0 15px rgba(79, 70, 229, 0.6);
    }
  }

  /* Cyberpunk Menu Items */
  .cyber-menu-item {
    position: relative;
    transition: all 0.3s ease;
    border-radius: 0.5rem;
    overflow: hidden;
  }

  .cyber-menu-item::before {
    content: '';
    position: absolute;
    left: -2px;
    top: 50%;
    transform: translateY(-50%);
    width: 3px;
    height: 0;
    background: linear-gradient(180deg, #4f46e5, #818cf8);
    border-radius: 2px;
    transition: height 0.3s ease;
    opacity: 0;
  }

  .cyber-menu-item:hover::before,
  .cyber-menu-active::before {
    height: 60%;
    opacity: 1;
  }

  .dark .cyber-menu-item::before {
    background: linear-gradient(180deg, #818cf8, #a5b4fc);
  }

  .cyber-menu-active {
    background: linear-gradient(135deg, rgba(79, 70, 229, 0.1), rgba(129, 140, 248, 0.05));
    border: 1px solid rgba(79, 70, 229, 0.2);
  }

  .dark .cyber-menu-active {
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.15), rgba(129, 140, 248, 0.1));
    border: 1px solid rgba(99, 102, 241, 0.3);
  }

  /* Cyberpunk Dropdown */
  .cyber-dropdown {
    position: relative;
  }

  .cyber-dropdown::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    width: 2px;
    height: 100%;
    background: linear-gradient(180deg,
        transparent 0%,
        #4f46e5 20%,
        #818cf8 50%,
        #4f46e5 80%,
        transparent 100%);
    opacity: 0.3;
  }

  .cyber-dropdown-item {
    position: relative;
    transition: all 0.2s ease;
    border-radius: 0.375rem;
  }

  .cyber-dropdown-item:hover,
  .cyber-dropdown-active {
    background: rgba(79, 70, 229, 0.08);
    transform: translateX(4px);
  }

  .dark .cyber-dropdown-item:hover,
  .dark .cyber-dropdown-active {
    background: rgba(99, 102, 241, 0.15);
  }

  /* Cyberpunk Text Effects */
  .cyber-menu-title {
    position: relative;
    letter-spacing: 0.1em;
  }

  .cyber-subtitle {
    position: relative;
    letter-spacing: 0.05em;
  }

  .cyber-text {
    background: linear-gradient(135deg, #4f46e5 0%, #818cf8 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .dark .cyber-text {
    background: linear-gradient(135deg, #818cf8 0%, #a5b4fc 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  /* Cyberpunk Promo Section */
  .cyber-promo {
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(79, 70, 229, 0.2);
  }

  .cyber-promo::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg,
        transparent,
        #4f46e5,
        #818cf8,
        #4f46e5,
        transparent);
    animation: cyber-scan 3s linear infinite;
  }

  @keyframes cyber-scan {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
  }

  .cyber-promo-btn {
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
  }

  .cyber-promo-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg,
        transparent,
        rgba(255, 255, 255, 0.2),
        transparent);
    transition: left 0.5s ease;
  }

  .cyber-promo-btn:hover::before {
    left: 100%;
  }

  /* Logo Cyber Effect */
  .sidebar-header {
    position: relative;
  }

  .sidebar-header::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg,
        transparent,
        #4f46e5,
        #818cf8,
        #4f46e5,
        transparent);
    opacity: 0.5;
  }

  /* Active State Enhancements */
  .menu-item-icon-active {
    filter: drop-shadow(0 0 8px rgba(79, 70, 229, 0.4));
  }

  .dark .menu-item-icon-active {
    filter: drop-shadow(0 0 10px rgba(99, 102, 241, 0.6));
  }

  .menu-item-arrow-active {
    filter: drop-shadow(0 0 4px rgba(79, 70, 229, 0.3));
  }
</style>
@endif