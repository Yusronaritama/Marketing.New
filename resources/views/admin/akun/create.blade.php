@extends('layouts.admin.app')

@section('title', 'Tambah Akun Baru')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Tambah Akun Baru</h1>
            <nav class="flex mt-2" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 text-sm">
                    <li class="inline-flex items-center">
                        <a href="{{ route('admin.dashboard.index') }}" class="text-gray-500 hover:text-brand-500 dark:text-gray-400">
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mx-1 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <a href="{{ route('admin.akun.index') }}" class="text-gray-500 hover:text-brand-500 dark:text-gray-400">
                                Manajemen Akun
                            </a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mx-1 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">Tambah Akun</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.akun.index') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali
        </a>
    </div>

    <!-- Alert Messages -->
    @if($errors->any())
    <div class="flex items-center p-4 mb-4 text-error-700 bg-error-50 rounded-lg dark:bg-error-900 dark:text-error-300" role="alert">
        <svg class="flex-shrink-0 w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
        </svg>
        <span>Terjadi kesalahan. Silakan periksa form di bawah.</span>
        <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-error-50 text-error-500 rounded-lg focus:ring-2 focus:ring-error-400 p-1.5 hover:bg-error-200 dark:bg-error-900 dark:text-error-300 dark:hover:bg-error-800" data-dismiss-target="#alert-error" aria-label="Close">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
        </button>
    </div>
    @endif

    <!-- Create Form -->
    <div class="bg-white rounded-lg shadow-theme-md dark:bg-gray-800">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h5 class="text-xl font-semibold text-gray-900 dark:text-white">Tambah Data Akun Baru</h5>
        </div>
        <div class="p-6">
            <form action="{{ route('admin.akun.store') }}" method="POST">
                @csrf
                
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <!-- Nama -->
                    <div>
                        <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Nama Lengkap <span class="text-error-600">*</span>
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" 
                            class="w-full px-4 py-2 text-gray-700 bg-gray-50 border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-brand-500 dark:focus:border-brand-500 @error('name') border-error-500 @enderror"
                            placeholder="Masukkan nama lengkap" required>
                        @error('name')
                            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Email <span class="text-error-600">*</span>
                        </label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}"
                            class="w-full px-4 py-2 text-gray-700 bg-gray-50 border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-brand-500 dark:focus:border-brand-500 @error('email') border-error-500 @enderror"
                            placeholder="Masukkan alamat email" required>
                        @error('email')
                            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Role -->
                    <div>
                        <label for="role" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Role <span class="text-error-600">*</span>
                        </label>
                        <select name="role" id="role" 
                            class="w-full px-4 py-2 text-gray-700 bg-gray-50 border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-brand-500 dark:focus:border-brand-500 @error('role') border-error-500 @enderror"
                            required>
                            <option value="">Pilih Role</option>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="mahasiswa_baru" {{ old('role') == 'mahasiswa_baru' ? 'selected' : '' }}>Mahasiswa</option>
                        </select>
                        @error('role')
                            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Telepon -->
                    <div>
                        <label for="phone" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Nomor Telepon <span class="text-error-600">*</span>
                        </label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                            class="w-full px-4 py-2 text-gray-700 bg-gray-50 border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-brand-500 dark:focus:border-brand-500 @error('phone') border-error-500 @enderror"
                            placeholder="08xxxxxxxxxx" required>
                        <p class="mt-1 text-xs text-gray-500">Format: 08xxxxxxxxxx (10-13 digit)</p>
                        @error('phone')
                            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Gelombang - UBAH MENJADI NUMBER INPUT -->
                    <div>
                        <label for="gelombang" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Gelombang
                        </label>
                        <input type="number" name="gelombang" id="gelombang" value="{{ old('gelombang') }}"
                            min="1" max="12"
                            class="w-full px-4 py-2 text-gray-700 bg-gray-50 border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-brand-500 dark:focus:border-brand-500 @error('gelombang') border-error-500 @enderror"
                            placeholder="Masukkan angka gelombang (1-12)">
                        <p class="mt-1 text-xs text-gray-500">Kosongkan untuk menggunakan gelombang otomatis berdasarkan bulan</p>
                        @error('gelombang')
                            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Promo Code -->
                    <div>
                        <label for="promo_code" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Kode Promo
                        </label>
                        <div class="flex gap-2">
                            <input type="text" name="promo_code" id="promo_code" value="{{ old('promo_code') }}"
                                class="flex-1 px-4 py-2 text-gray-700 bg-gray-50 border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-brand-500 dark:focus:border-brand-500 @error('promo_code') border-error-500 @enderror"
                                placeholder="Masukkan kode promo">
                            <button type="button" id="validatePromoBtn" class="px-4 py-2 text-sm font-medium text-white bg-brand-500 rounded-lg hover:bg-brand-600 focus:ring-4 focus:ring-brand-300">
                                Validasi
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Kosongkan jika tidak menggunakan promo</p>
                        @error('promo_code')
                            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Password <span class="text-error-600">*</span>
                        </label>
                        <input type="password" name="password" id="password"
                            class="w-full px-4 py-2 text-gray-700 bg-gray-50 border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-brand-500 dark:focus:border-brand-500 @error('password') border-error-500 @enderror"
                            placeholder="Masukkan password" required>
                        @error('password')
                            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Konfirmasi Password -->
                    <div>
                        <label for="password_confirmation" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Konfirmasi Password <span class="text-error-600">*</span>
                        </label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            class="w-full px-4 py-2 text-gray-700 bg-gray-50 border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-brand-500 dark:focus:border-brand-500 @error('password_confirmation') border-error-500 @enderror"
                            placeholder="Konfirmasi password" required>
                        @error('password_confirmation')
                            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Informasi Promo & VA -->
                <div id="promoInfo" class="hidden mt-6 p-4 bg-success-50 rounded-lg dark:bg-success-900/20">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 mr-2 text-success-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <div class="flex-1">
                            <h6 class="font-medium text-success-800 dark:text-success-400" id="promoTitle">Kode Promo Valid</h6>
                            <div id="promoDetails" class="mt-2 space-y-2 text-sm text-success-700 dark:text-success-300">
                                <!-- Detail promo akan diisi via JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informasi VA untuk Mahasiswa -->
                <div id="vaInfo" class="hidden mt-4 p-4 bg-blue-light-50 rounded-lg dark:bg-blue-light-500/15">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 mr-2 text-blue-light-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                        <div class="flex-1">
                            <h6 class="font-medium text-blue-light-800 dark:text-blue-light-400">Informasi Virtual Account</h6>
                            <div class="mt-2 space-y-1 text-sm text-blue-light-700 dark:text-blue-light-300">
                                <p>• Virtual Account akan dibuat otomatis untuk akun mahasiswa_baru</p>
                                <p>• Status pembayaran akan ditentukan berdasarkan kode promo</p>
                                <p>• Akun akan terdaftar di sistem Moodle setelah dibuat</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informasi Admin -->
                <div id="adminInfo" class="hidden mt-4 p-4 bg-gray-50 rounded-lg dark:bg-gray-700">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 mr-2 text-gray-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                        <div class="flex-1">
                            <h6 class="font-medium text-gray-800 dark:text-gray-400">Informasi Akun Admin</h6>
                            <div class="mt-2 space-y-1 text-sm text-gray-700 dark:text-gray-300">
                                <p>• Akun admin tidak memerlukan Virtual Account</p>
                                <p>• Akses penuh ke sistem administrasi</p>
                                <p>• Dapat mengelola semua data dan pengguna</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-3 pt-6 mt-6 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('admin.akun.index') }}" class="px-6 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-brand-500 rounded-lg hover:bg-brand-600 focus:ring-4 focus:ring-brand-300 dark:focus:ring-brand-800">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Tambah Akun
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('[role="alert"]');
        alerts.forEach(alert => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        });
    }, 5000);

    // Alert dismiss buttons
    document.querySelectorAll('[data-dismiss-target]').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-dismiss-target');
            const target = document.querySelector(targetId) || this.closest('[role="alert"]');
            if (target) {
                target.style.transition = 'opacity 0.3s';
                target.style.opacity = '0';
                setTimeout(() => target.remove(), 300);
            }
        });
    });

    // Elements
    const roleSelect = document.getElementById('role');
    const promoCodeInput = document.getElementById('promo_code');
    const validatePromoBtn = document.getElementById('validatePromoBtn');
    const promoInfo = document.getElementById('promoInfo');
    const promoTitle = document.getElementById('promoTitle');
    const promoDetails = document.getElementById('promoDetails');
    const vaInfo = document.getElementById('vaInfo');
    const adminInfo = document.getElementById('adminInfo');

    // Toggle info berdasarkan role
    function toggleRoleInfo() {
        const role = roleSelect.value;
        
        if (role === 'mahasiswa_baru') {
            vaInfo.classList.remove('hidden');
            adminInfo.classList.add('hidden');
        } else if (role === 'admin') {
            vaInfo.classList.add('hidden');
            adminInfo.classList.remove('hidden');
        } else {
            vaInfo.classList.add('hidden');
            adminInfo.classList.add('hidden');
        }
    }

    // Validasi promo code
    validatePromoBtn.addEventListener('click', function() {
        const promoCode = promoCodeInput.value.trim();
        
        if (!promoCode) {
            showPromoError('Masukkan kode promo terlebih dahulu');
            return;
        }

        // Show loading
        validatePromoBtn.innerHTML = 'Validating...';
        validatePromoBtn.disabled = true;

        fetch('{{ route("admin.akun.validate-promo") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ promo_code: promoCode })
        })
        .then(response => response.json())
        .then(data => {
            if (data.valid) {
                showPromoSuccess(data);
            } else {
                showPromoError(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showPromoError('Terjadi kesalahan saat validasi promo');
        })
        .finally(() => {
            validatePromoBtn.innerHTML = 'Validasi';
            validatePromoBtn.disabled = false;
        });
    });

    function showPromoSuccess(data) {
        const promo = data.promo_code;
        const pricing = data.pricing;
        const status = data.status_info;
        const gelombang = data.gelombang_info;
        
        promoTitle.textContent = `🎉 Kode Promo Valid: ${promo.code}`;
        
        let detailsHTML = `
            <div class="grid grid-cols-2 gap-2 text-xs">
                <div class="font-medium">Diskon:</div>
                <div>${promo.discount}%</div>
                
                <div class="font-medium">Harga Asli:</div>
                <div>${pricing.formatted_original}</div>
                
                <div class="font-medium">Harga Setelah Diskon:</div>
                <div class="font-semibold">${pricing.formatted_discounted}</div>
                
                <div class="font-medium">Anda Hemat:</div>
                <div class="text-success-600 font-semibold">${pricing.formatted_saving}</div>
                
                <div class="font-medium">Status Pembayaran:</div>
                <div class="font-semibold ${status.is_paid ? 'text-success-600' : 'text-warning-600'}">
                    ${status.va_status} ${status.is_paid ? '🎉' : ''}
                </div>
                
                <div class="font-medium">Gelombang:</div>
                <div>${gelombang.gelombang_display}</div>
            </div>
            <p class="mt-2 text-xs">${data.note}</p>
        `;
        
        promoDetails.innerHTML = detailsHTML;
        promoInfo.classList.remove('hidden');
        promoInfo.classList.remove('bg-error-50', 'text-error-700');
        promoInfo.classList.add('bg-success-50', 'text-success-700');
    }

    function showPromoError(message) {
        promoTitle.textContent = '❌ Kode Promo Tidak Valid';
        promoDetails.innerHTML = `<p>${message}</p>`;
        promoInfo.classList.remove('hidden');
        promoInfo.classList.remove('bg-success-50', 'text-success-700');
        promoInfo.classList.add('bg-error-50', 'text-error-700');
    }

    // Password confirmation validation
    const passwordInput = document.getElementById('password');
    const passwordConfirmationInput = document.getElementById('password_confirmation');
    
    if (passwordInput && passwordConfirmationInput) {
        passwordConfirmationInput.addEventListener('input', function() {
            if (passwordInput.value !== passwordConfirmationInput.value) {
                passwordConfirmationInput.classList.add('border-error-500');
                passwordConfirmationInput.classList.remove('border-gray-300');
            } else {
                passwordConfirmationInput.classList.remove('border-error-500');
                passwordConfirmationInput.classList.add('border-gray-300');
            }
        });
    }

    // Event listeners
    roleSelect.addEventListener('change', toggleRoleInfo);
    
    // Initialize
    toggleRoleInfo();
});
</script>
@endsection
<script src="{{ asset('assets/js/bundle.js') }}"></script>