@extends('layouts.admin.app')

@section('title', 'Edit Akun')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Edit Akun</h1>
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
                            <span class="text-gray-700 dark:text-gray-300">Edit Akun</span>
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

    <!-- Edit Form -->
    <div class="bg-white rounded-lg shadow-theme-md dark:bg-gray-800">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h5 class="text-xl font-semibold text-gray-900 dark:text-white">Edit Data Akun</h5>
        </div>
        <div class="p-6">
            <form action="{{ route('admin.akun.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <!-- Nama -->
                    <div>
                        <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Nama Lengkap <span class="text-error-600">*</span>
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" 
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
                        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
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
                            <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="mahasiswa_baru" {{ old('role', $user->role) == 'mahasiswa_baru' ? 'selected' : '' }}>Mahasiswa</option>
                        </select>
                        @error('role')
                            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Telepon -->
                    <div>
                        <label for="phone" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Nomor Telepon
                        </label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}"
                            class="w-full px-4 py-2 text-gray-700 bg-gray-50 border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-brand-500 dark:focus:border-brand-500 @error('phone') border-error-500 @enderror"
                            placeholder="Masukkan nomor telepon">
                        @error('phone')
                            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Gelombang -->
                    <div>
                        <label for="gelombang" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Gelombang
                        </label>
                        <input type="number" name="gelombang" id="gelombang" 
                            value="{{ old('gelombang', $user->gelombang) }}"
                            min="1" max="12"
                            class="w-full px-4 py-2 text-gray-700 bg-gray-50 border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-brand-500 dark:focus:border-brand-500 @error('gelombang') border-error-500 @enderror"
                            placeholder="Masukkan angka gelombang (1-12)">
                        <p class="mt-1 text-xs text-gray-500">Angka 1-12 (1: Gelombang 1, 2: Gelombang 2, dst.)</p>
                        @error('gelombang')
                            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status VA -->
                    <div>
                        <label for="va_status" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Status VA
                        </label>
                        <select name="va_status" id="va_status" 
                            class="w-full px-4 py-2 text-gray-700 bg-gray-50 border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-brand-500 dark:focus:border-brand-500 @error('va_status') border-error-500 @enderror">
                            <option value="">Pilih Status VA</option>
                            <option value="pending" {{ old('va_status', $user->va_status) == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="active" {{ old('va_status', $user->va_status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="expired" {{ old('va_status', $user->va_status) == 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="paid" {{ old('va_status', $user->va_status) == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="failed" {{ old('va_status', $user->va_status) == 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                        @error('va_status')
                            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- VA Number -->
                    <div>
                        <label for="va_number" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Nomor VA
                        </label>
                        <input type="text" name="va_number" id="va_number" value="{{ old('va_number', $user->va_number) }}"
                            class="w-full px-4 py-2 text-gray-700 bg-gray-50 border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-brand-500 dark:focus:border-brand-500 @error('va_number') border-error-500 @enderror"
                            placeholder="Masukkan nomor VA">
                        @error('va_number')
                            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- VA Amount -->
                    <div>
                        <label for="va_amount" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Amount VA
                        </label>
                        <input type="number" name="va_amount" id="va_amount" value="{{ old('va_amount', $user->va_amount) }}"
                            class="w-full px-4 py-2 text-gray-700 bg-gray-50 border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-brand-500 dark:focus:border-brand-500 @error('va_amount') border-error-500 @enderror"
                            placeholder="Masukkan amount VA" step="0.01" min="0">
                        @error('va_amount')
                            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- VA Expired At -->
                    <div>
                        <label for="va_expired_at" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            VA Expired At
                        </label>
                        <input type="datetime-local" name="va_expired_at" id="va_expired_at" 
                            value="{{ old('va_expired_at', $user->va_expired_at ? \Carbon\Carbon::parse($user->va_expired_at)->format('Y-m-d\TH:i') : '') }}"
                            class="w-full px-4 py-2 text-gray-700 bg-gray-50 border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-brand-500 dark:focus:border-brand-500 @error('va_expired_at') border-error-500 @enderror">
                        @error('va_expired_at')
                            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Promo Code Used -->
                    <div>
                        <label for="promo_code_used" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Kode Promo Digunakan
                        </label>
                        <input type="text" name="promo_code_used" id="promo_code_used" value="{{ old('promo_code_used', $user->promo_code_used) }}"
                            class="w-full px-4 py-2 text-gray-700 bg-gray-50 border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-brand-500 dark:focus:border-brand-500 @error('promo_code_used') border-error-500 @enderror"
                            placeholder="Masukkan kode promo">
                        @error('promo_code_used')
                            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Master User Data Section -->
                    @php
                        $masterUser = \App\Models\MasterUser::where('user_id', $user->id)->first();
                    @endphp

                    @if($masterUser)
                    <!-- TRX ID -->
                    <div>
                        <label for="trx_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            TRX ID
                        </label>
                        <input type="text" name="trx_id" id="trx_id" value="{{ old('trx_id', $masterUser->trx_id) }}"
                            class="w-full px-4 py-2 text-gray-700 bg-gray-50 border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-brand-500 dark:focus:border-brand-500 @error('trx_id') border-error-500 @enderror"
                            placeholder="TRX ID dari BNI">
                        @error('trx_id')
                            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Plain Password -->
                    <div>
                        <label for="plain_password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Plain Password
                        </label>
                        <div class="flex gap-2">
                            <input type="text" name="plain_password" id="plain_password" value="{{ old('plain_password', $masterUser->plain_password) }}"
                                class="flex-1 px-4 py-2 text-gray-700 bg-gray-50 border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-brand-500 dark:focus:border-brand-500 @error('plain_password') border-error-500 @enderror"
                                placeholder="Password plain text">
                            <button type="button" id="generatePasswordBtn" class="px-4 py-2 text-sm font-medium text-white bg-blue-500 rounded-lg hover:bg-blue-600 focus:ring-4 focus:ring-blue-300">
                                Generate
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Password yang akan ditampilkan ke user</p>
                        @error('plain_password')
                            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password (Hashed) -->
                    <div>
                        <label for="master_password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Password (Hashed)
                        </label>
                        <input type="text" name="master_password" id="master_password" value="{{ old('master_password', $masterUser->password) }}"
                            class="w-full px-4 py-2 text-gray-700 bg-gray-50 border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-brand-500 dark:focus:border-brand-500 @error('master_password') border-error-500 @enderror"
                            placeholder="Password hashed" readonly>
                        <p class="mt-1 text-xs text-gray-500">Password terhash (auto update dari plain password)</p>
                        @error('master_password')
                            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>
                    @endif

                    <!-- Bio -->
                    <div class="md:col-span-2">
                        <label for="bio" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Bio
                        </label>
                        <textarea name="bio" id="bio" rows="3"
                            class="w-full px-4 py-2 text-gray-700 bg-gray-50 border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-brand-500 dark:focus:border-brand-500 @error('bio') border-error-500 @enderror"
                            placeholder="Masukkan bio pengguna">{{ old('bio', $user->bio) }}</textarea>
                        @error('bio')
                            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password (Optional) untuk User -->
                    <div class="md:col-span-2">
                        <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Password Baru User (Opsional)
                        </label>
                        <div class="flex gap-2">
                            <input type="password" name="password" id="password"
                                class="flex-1 px-4 py-2 text-gray-700 bg-gray-50 border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-brand-500 dark:focus:border-brand-500 @error('password') border-error-500 @enderror"
                                placeholder="Masukkan password baru (kosongkan jika tidak ingin mengubah)">
                            <button type="button" id="showPasswordBtn" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 border border-gray-300 rounded-lg hover:bg-gray-300 dark:bg-gray-600 dark:text-gray-300 dark:border-gray-500 dark:hover:bg-gray-500">
                                👁️
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Jika diisi, akan mengupdate password user dan plain password di master user</p>
                        @error('password')
                            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Confirmation -->
                    <div class="md:col-span-2">
                        <label for="password_confirmation" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Konfirmasi Password Baru
                        </label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            class="w-full px-4 py-2 text-gray-700 bg-gray-50 border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-brand-500 dark:focus:border-brand-500 @error('password_confirmation') border-error-500 @enderror"
                            placeholder="Konfirmasi password baru">
                        @error('password_confirmation')
                            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-3 pt-6 mt-6 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('admin.akun.index') }}" class="px-6 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-brand-500 rounded-lg hover:bg-brand-600 focus:ring-4 focus:ring-brand-300 dark:focus:ring-brand-800">
                        Simpan Perubahan
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
    const plainPasswordInput = document.getElementById('plain_password');
    const masterPasswordInput = document.getElementById('master_password');
    const generatePasswordBtn = document.getElementById('generatePasswordBtn');
    const passwordInput = document.getElementById('password');
    const showPasswordBtn = document.getElementById('showPasswordBtn');
    const passwordConfirmationInput = document.getElementById('password_confirmation');

    // Generate random password
    if (generatePasswordBtn && plainPasswordInput) {
        generatePasswordBtn.addEventListener('click', function() {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            let password = '';
            for (let i = 0; i < 8; i++) {
                password += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            plainPasswordInput.value = password;
            
            // Trigger input event to update hashed password
            plainPasswordInput.dispatchEvent(new Event('input'));
        });
    }

    // Update hashed password when plain password changes
    if (plainPasswordInput && masterPasswordInput) {
        plainPasswordInput.addEventListener('input', function() {
            // In a real scenario, you might want to hash this on the server side
            // This is just for demonstration
            masterPasswordInput.value = '[Hashed: ' + plainPasswordInput.value + ']';
        });
    }

    // Show/hide password
    if (showPasswordBtn && passwordInput) {
        let isPasswordVisible = false;
        showPasswordBtn.addEventListener('click', function() {
            isPasswordVisible = !isPasswordVisible;
            passwordInput.type = isPasswordVisible ? 'text' : 'password';
            showPasswordBtn.textContent = isPasswordVisible ? '🙈' : '👁️';
        });
    }

    // Password confirmation validation
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

    // Auto-fill plain password when user password is entered
    if (passwordInput && plainPasswordInput) {
        passwordInput.addEventListener('input', function() {
            if (passwordInput.value && !plainPasswordInput.value) {
                plainPasswordInput.value = passwordInput.value;
                plainPasswordInput.dispatchEvent(new Event('input'));
            }
        });
    }
});
</script>
@endsection
<script src="{{ asset('assets/js/bundle.js') }}"></script>