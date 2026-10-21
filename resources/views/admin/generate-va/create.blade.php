@extends('layouts.admin.app')

@section('title', 'Generate Virtual Account')

@section('content')
<div class="p-4 sm:p-6">
    <!-- Page Header -->
    <div class="mb-6">
        <h4 class="text-title-md font-semibold text-gray-900 dark:text-white mb-2">
            Generate Virtual Account
        </h4>
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                <li>
                    <a href="{{ route('admin.dashboard.index') }}" class="hover:text-brand-500 dark:hover:text-brand-400 transition-colors">
                        Dashboard
                    </a>
                </li>
                <li>
                    <span class="mx-2">/</span>
                </li>
                <li>
                    <a href="{{ route('admin.generate-va.index') }}" class="hover:text-brand-500 dark:hover:text-brand-400 transition-colors">
                        Generate VA
                    </a>
                </li>
                <li>
                    <span class="mx-2">/</span>
                </li>
                <li class="text-gray-900 dark:text-white font-medium">
                    Create
                </li>
            </ol>
        </nav>
    </div>

    <!-- Main Card -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-theme-sm border border-gray-200 dark:border-gray-700">
        <div class="p-4 sm:p-6">
            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="mb-4 p-4 rounded-lg bg-success-50 dark:bg-success-500/10 border border-success-200 dark:border-success-500/20">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-success-600 dark:text-success-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-sm font-medium text-success-800 dark:text-success-400">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 rounded-lg bg-error-50 dark:bg-error-500/10 border border-error-200 dark:border-error-500/20">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-error-600 dark:text-error-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-sm font-medium text-error-800 dark:text-error-400">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            <!-- Student Info Card -->
            <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-gray-200 dark:border-gray-700">
                <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Informasi Mahasiswa</h5>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Nama Mahasiswa</label>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $masterEreg->nama ?? ($user->biodata->nama_lengkap ?? $user->name ?? 'N/A') }}
                        </p>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Email</label>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $masterEreg->gmail ?? $user->email ?? 'N/A' }}
                        </p>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Nomor HP</label>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $masterEreg->phone ?? ($user->biodata->no_telepon ?? $user->phone ?? '-') }}
                        </p>
                    </div>
                    <div>
    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">TRX ID</label>
    <p class="text-sm font-medium text-gray-900 dark:text-white">
        {{ $masterUserVerified->trx_id ?? ($user->trx_id ?? '-') }}
    </p>
</div>
                </div>
            </div>

            <!-- Prodi Pilihan Info -->
            <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-500/10 rounded-lg border border-blue-200 dark:border-blue-500/20">
                <h5 class="text-sm font-medium text-blue-700 dark:text-blue-300 mb-3">Program Studi Pilihan</h5>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-blue-600 dark:text-blue-400 mb-1">Pilihan 1</label>
                        @if($prodiPilihan['pilihan1'])
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $prodiPilihan['pilihan1']['kode'] }} - {{ $prodiPilihan['pilihan1']['nama'] }}
                            </p>
                        @else
                            <p class="text-sm text-gray-400 dark:text-gray-500">-</p>
                        @endif
                    </div>
                    <div>
                        <label class="block text-xs text-blue-600 dark:text-blue-400 mb-1">Pilihan 2</label>
                        @if($prodiPilihan['pilihan2'])
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $prodiPilihan['pilihan2']['kode'] }} - {{ $prodiPilihan['pilihan2']['nama'] }}
                            </p>
                        @else
                            <p class="text-sm text-gray-400 dark:text-gray-500">-</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Status Kelulusan -->
            <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-gray-200 dark:border-gray-700">
                <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Status Kelulusan</h5>
                <div class="flex items-center gap-2">
                    @if($user->masterMoodleLulus)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500">
                            LULUS
                        </span>
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                            Prodi Lulus: {{ $user->masterMoodleLulus->prodi_lulus ?? '-' }}
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-500">
                            BELUM LULUS
                        </span>
                    @endif
                </div>
            </div>

            <!-- Existing VA Warning -->
            @if($existingVA)
                <div class="mb-6 p-4 bg-warning-50 dark:bg-warning-500/10 rounded-lg border border-warning-200 dark:border-warning-500/20">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-warning-600 dark:text-warning-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <h6 class="text-sm font-medium text-warning-800 dark:text-warning-400 mb-1">
                                Virtual Account Sudah Ada
                            </h6>
                            <p class="text-sm text-warning-700 dark:text-warning-500/80">
                                Mahasiswa ini sudah memiliki Virtual Account yang aktif. Silakan batalkan VA sebelumnya untuk membuat yang baru.
                            </p>
                            <div class="mt-2">
                                <code class="text-sm font-mono text-gray-900 dark:text-white px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded">
                                    {{ $existingVA->virtual_account }}
                                </code>
                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                                    Status: {{ strtoupper($existingVA->status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- Form Generate VA -->
                <form action="{{ route('admin.generate-va.store', $masterEreg->id) }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Left Column -->
                        <div class="space-y-6">
                            <!-- Program Studi -->
                            <div>
                                <label for="kode_prodi" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Program Studi <span class="text-error-500">*</span>
                                </label>
                                <select 
                                    name="kode_prodi" 
                                    id="kode_prodi"
                                    required
                                    class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:border-brand-500 dark:focus:border-brand-500 focus:ring-3 focus:ring-brand-500/10 outline-hidden appearance-none">
                                    <option value="">-- Pilih Program Studi --</option>
                                    @foreach($availableProdi as $prodi)
                                        <option value="{{ $prodi->kode_prodi }}">
                                            {{ $prodi->kode_prodi }} - {{ $prodi->nama_prodi }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('kode_prodi')
                                    <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Tenor Pembayaran -->
                            <div>
                                <label for="tenor_pembayaran" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Tenor Pembayaran <span class="text-error-500">*</span>
                                </label>
                                <select 
                                    name="tenor_pembayaran" 
                                    id="tenor_pembayaran"
                                    required
                                    class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:border-brand-500 dark:focus:border-brand-500 focus:ring-3 focus:ring-brand-500/10 outline-hidden appearance-none">
                                    <option value="">-- Pilih Tenor --</option>
                                    <option value="cicilan">Cicilan</option>
                                    <option value="semester">Semester</option>
                                    <option value="tahun">Tahun</option>
                                </select>
                                @error('tenor_pembayaran')
                                    <p class="mt-1 text-sm text-error-600 dark:text-error-error-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-6">
                            <!-- Harga Standar Display -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Harga Standar
                                </label>
                                <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <div id="price-display" class="text-center py-4">
                                        <span class="text-gray-400 dark:text-gray-500 text-sm">
                                            Pilih Program Studi dan Tenor untuk melihat harga
                                        </span>
                                    </div>
                                    <div id="price-details" class="hidden space-y-2">
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-600 dark:text-gray-400">Program Studi:</span>
                                            <span id="prodi-name" class="text-sm font-medium text-gray-900 dark:text-white"></span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-600 dark:text-gray-400">Tenor:</span>
                                            <span id="tenor-name" class="text-sm font-medium text-gray-900 dark:text-white"></span>
                                        </div>
                                        <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                                            <div class="flex justify-between items-center">
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Harga:</span>
                                                <span id="standard-price" class="text-lg font-bold text-gray-900 dark:text-white"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Custom Amount -->
                            <div>
                                <label for="custom_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Custom Amount (Opsional)
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 dark:text-gray-400">Rp</span>
                                    </div>
                                    <input 
                                        type="number" 
                                        name="custom_amount" 
                                        id="custom_amount"
                                        min="0"
                                        placeholder="Masukkan jumlah custom (jika ada)"
                                        class="w-full pl-12 pr-4 py-2.5 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:border-brand-500 dark:focus:border-brand-500 focus:ring-3 focus:ring-brand-500/10 outline-hidden transition-colors"
                                        oninput="calculateDifference()">
                                </div>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Kosongkan jika ingin menggunakan harga standar
                                </p>
                            </div>

                            <!-- Harga Final Display -->
                            <div id="final-price-section" class="hidden">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Harga Final
                                </label>
                                <div class="p-4 bg-brand-50 dark:bg-brand-500/10 rounded-lg border border-brand-200 dark:border-brand-500/20">
                                    <div class="space-y-2">
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-600 dark:text-gray-400">Harga Standar:</span>
                                            <span id="display-standard-price" class="text-sm text-gray-700 dark:text-gray-300"></span>
                                        </div>
                                        <div id="custom-amount-row" class="hidden">
                                            <div class="flex justify-between items-center">
                                                <span class="text-sm text-gray-600 dark:text-gray-400">Custom Amount:</span>
                                                <span id="display-custom-amount" class="text-sm text-gray-700 dark:text-gray-300"></span>
                                            </div>
                                            <div class="flex justify-between items-center pt-2 border-t border-gray-200 dark:border-gray-700">
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Selisih:</span>
                                                <span id="price-difference" class="text-sm font-medium"></span>
                                            </div>
                                        </div>
                                        <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                                            <div class="flex justify-between items-center">
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Total Bayar:</span>
                                                <span id="final-price" class="text-lg font-bold text-brand-600 dark:text-brand-400"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if(!$isVerified)
<div class="p-4 bg-warning-50 dark:bg-warning-500/10 rounded-lg border border-warning-200 dark:border-warning-500/20">
    <div class="flex items-start gap-3">
        <input 
            type="checkbox" 
            name="confirm_unverified" 
            id="confirm_unverified"
            value="1"
            class="mt-1 w-4 h-4 text-warning-600 bg-gray-100 border-gray-300 rounded focus:ring-warning-500 dark:focus:ring-warning-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
        <div>
            <label for="confirm_unverified" class="block text-sm font-medium text-warning-800 dark:text-warning-400 mb-1">
                Konfirmasi Generate VA untuk User Belum Terverifikasi
            </label>
            <p class="text-sm text-warning-700 dark:text-warning-500/80">
                User ini belum terverifikasi. Centang kotak ini jika Anda yakin ingin tetap generate Virtual Account.
            </p>
        </div>
    </div>
</div>
@endif
                    <!-- Form Actions -->
                    <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex flex-col sm:flex-row gap-3 justify-end">
                            <a 
                                href="{{ route('admin.generate-va.index') }}" 
                                class="px-6 py-2.5 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors font-medium inline-flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Batal
                            </a>
                            <button 
                                type="submit" 
                                class="px-6 py-2.5 bg-brand-500 text-white rounded-lg hover:bg-brand-600 focus:ring-3 focus:ring-brand-500/10 transition-colors font-medium inline-flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Generate Virtual Account
                            </button>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <!-- Info Section -->
    <div class="mt-4 bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/20 rounded-lg p-4">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <div>
                <h6 class="text-sm font-semibold text-blue-800 dark:text-blue-300 mb-2">
                    Informasi Generate VA
                </h6>
                <ul class="space-y-1 text-sm text-blue-700 dark:text-blue-400">
                    <li>• Virtual Account akan aktif selama 2 hari setelah digenerate</li>
                    <li>• Pastikan program studi dan tenor pembayaran sudah benar</li>
                    <li>• Custom amount hanya digunakan jika ingin mengubah harga standar</li>
                    <li>• VA yang sudah dibayar tidak dapat dibatalkan</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
    let standardPrice = 0;
    
    document.addEventListener('DOMContentLoaded', function() {
        const prodiSelect = document.getElementById('kode_prodi');
        const tenorSelect = document.getElementById('tenor_pembayaran');
        
        // Event listeners untuk perubahan dropdown
        prodiSelect.addEventListener('change', function() {
            if (prodiSelect.value && tenorSelect.value) {
                fetchPrice(prodiSelect.value, tenorSelect.value);
            }
        });
        
        tenorSelect.addEventListener('change', function() {
            if (prodiSelect.value && tenorSelect.value) {
                fetchPrice(prodiSelect.value, tenorSelect.value);
            }
        });
        
        // Event listener untuk custom amount
        document.getElementById('custom_amount').addEventListener('input', calculateDifference);
    });
    
    function fetchPrice(kodeProdi, tenor) {
        fetch(`/admin/generate-va/get-harga/${kodeProdi}/${tenor}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    standardPrice = data.price;
                    
                    // Update price display
                    const priceDisplay = document.getElementById('price-display');
                    const priceDetails = document.getElementById('price-details');
                    const finalPriceSection = document.getElementById('final-price-section');
                    
                    priceDisplay.classList.add('hidden');
                    priceDetails.classList.remove('hidden');
                    finalPriceSection.classList.remove('hidden');
                    
                    // Update details
                    document.getElementById('prodi-name').textContent = data.prodi_nama;
                    document.getElementById('tenor-name').textContent = tenor.charAt(0).toUpperCase() + tenor.slice(1);
                    document.getElementById('standard-price').textContent = data.price_formatted;
                    
                    // Update final price section
                    updateFinalPrice();
                } else {
                    alert(data.message);
                    resetPriceDisplay();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mengambil harga');
                resetPriceDisplay();
            });
    }
    
    function updateFinalPrice() {
        const customAmountInput = document.getElementById('custom_amount');
        const customAmount = parseFloat(customAmountInput.value) || 0;
        
        // Format standard price
        const standardPriceFormatted = 'Rp ' + standardPrice.toLocaleString('id-ID');
        document.getElementById('display-standard-price').textContent = standardPriceFormatted;
        
        let finalPrice = standardPrice;
        
        // Update custom amount display
        if (customAmount > 0 && customAmount !== standardPrice) {
            document.getElementById('custom-amount-row').classList.remove('hidden');
            const customAmountFormatted = 'Rp ' + customAmount.toLocaleString('id-ID');
            document.getElementById('display-custom-amount').textContent = customAmountFormatted;
            
            // Calculate and display difference
            const difference = customAmount - standardPrice;
            const differenceFormatted = (difference >= 0 ? '+' : '-') + ' Rp ' + Math.abs(difference).toLocaleString('id-ID');
            const differenceElement = document.getElementById('price-difference');
            
            differenceElement.textContent = differenceFormatted;
            differenceElement.className = 'text-sm font-medium ' + (difference >= 0 ? 'text-success-600 dark:text-success-400' : 'text-error-600 dark:text-error-400');
            
            finalPrice = customAmount;
        } else {
            document.getElementById('custom-amount-row').classList.add('hidden');
        }
        
        // Update final price
        const finalPriceFormatted = 'Rp ' + finalPrice.toLocaleString('id-ID');
        document.getElementById('final-price').textContent = finalPriceFormatted;
    }
    
    function calculateDifference() {
        if (standardPrice > 0) {
            updateFinalPrice();
        }
    }
    
    function resetPriceDisplay() {
        const priceDisplay = document.getElementById('price-display');
        const priceDetails = document.getElementById('price-details');
        const finalPriceSection = document.getElementById('final-price-section');
        
        priceDisplay.classList.remove('hidden');
        priceDetails.classList.add('hidden');
        finalPriceSection.classList.add('hidden');
        
        standardPrice = 0;
    }
</script>
@endsection