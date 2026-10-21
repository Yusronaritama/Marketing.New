@extends('layouts.mahasiswa.profesi')

@section('title', 'Review Pembayaran UKT')

@section('content')
<div class="min-h-screen py-10 px-4 sm:px-6 flex justify-center">
    <div class="w-full max-w-4xl space-y-6 pb-24">

        {{-- Tombol Back --}}
        <a href="{{ route('profesi.dashboard') }}"
           class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-blue-700 transition-colors">
            <i class="fas fa-arrow-left text-xs"></i> Kembali ke Dashboard
        </a>

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden"
             x-data="{
                 isSubmitting: false,
                 metode: 'semester',
                 hargaSemester: {{ $hargaSemester }},
                 hargaLunas: {{ $hargaLunas }},
                 get total() {
                     return this.metode === 'lunas' ? this.hargaLunas : this.hargaSemester;
                 },
                 get totalFormatted() {
                     return new Intl.NumberFormat('id-ID').format(this.total);
                 }
             }">

            {{-- Header --}}
            <div class="flex h-1">
                <div class="flex-1 bg-blue-700"></div>
                <div class="flex-1 bg-red-600"></div>
                <div class="flex-1 bg-yellow-400"></div>
            </div>
            <div class="bg-blue-700 px-6 py-7 sm:px-10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-file-invoice-dollar text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-white">Review Pembayaran UKT</h2>
                        <p class="text-blue-200 text-xs mt-0.5">Program Profesi Apoteker — Universitas Buana Perjuangan Karawang</p>
                    </div>
                </div>
            </div>

            <div class="p-6 sm:p-10">

                {{-- Error --}}
                @if(session('error'))
                    <div class="mb-6 flex items-start gap-3 bg-red-50 border border-red-200 rounded-xl p-4">
                        <i class="fas fa-exclamation-triangle text-red-500 mt-0.5 flex-shrink-0"></i>
                        <p class="text-sm text-red-700 font-medium">{{ session('error') }}</p>
                    </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                    {{-- ── KIRI ── --}}
                    <div class="space-y-6">

                        {{-- Info Mahasiswa --}}
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Informasi Calon Mahasiswa</p>
                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-3">
                                <div>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">Nama Lengkap</p>
                                    <p class="font-semibold text-slate-800 text-sm">{{ $user->name }}</p>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">No. HP</p>
                                        <p class="font-semibold text-slate-800 text-sm">{{ $user->phone ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">Email</p>
                                        <p class="font-semibold text-slate-800 text-sm truncate">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Pilihan Metode Pembayaran --}}
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Pilih Metode Pembayaran</p>
                            <div class="space-y-3">

                                {{-- Opsi: Semester --}}
                                <label class="flex items-start gap-4 p-4 rounded-xl border-2 cursor-pointer transition-all"
                                       :class="metode === 'semester'
                                           ? 'border-blue-600 bg-blue-50'
                                           : 'border-slate-200 bg-white hover:border-slate-300'">
                                    <input type="radio" name="_metode" value="semester"
                                           x-model="metode" class="mt-1 accent-blue-600">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-2">
                                            <p class="font-bold text-slate-800 text-sm">Per Semester</p>
                                            <p class="font-black text-blue-700 text-sm whitespace-nowrap">
                                                Rp {{ number_format($hargaSemester, 0, ',', '.') }}
                                            </p>
                                        </div>
                                        <p class="text-xs text-slate-500 mt-0.5">Paket standar — termasuk biaya pendidikan & almamater</p>
                                    </div>
                                </label>

                                {{-- Opsi: Lunas --}}
                                <label class="flex items-start gap-4 p-4 rounded-xl border-2 cursor-pointer transition-all relative overflow-hidden"
                                       :class="metode === 'lunas'
                                           ? 'border-blue-600 bg-blue-50'
                                           : 'border-slate-200 bg-white hover:border-slate-300'">
                                    {{-- Badge --}}
                                    <div class="absolute top-0 right-0 bg-red-600 text-white text-[9px] font-black px-2 py-0.5 rounded-bl-lg tracking-wide">
                                        HEMAT WAKTU
                                    </div>
                                    <input type="radio" name="_metode" value="lunas"
                                           x-model="metode" class="mt-1 accent-blue-600">
                                    <div class="flex-1 min-w-0 pr-12">
                                        <div class="flex items-center justify-between gap-2">
                                            <p class="font-bold text-slate-800 text-sm">Lunas (Full Payment)</p>
                                            <p class="font-black text-blue-700 text-sm whitespace-nowrap">
                                                Rp {{ number_format($hargaLunas, 0, ',', '.') }}
                                            </p>
                                        </div>
                                        <p class="text-xs text-slate-500 mt-0.5">Termasuk biaya semester + tambahan Rp {{ number_format(18700000, 0, ',', '.') }}</p>
                                    </div>
                                </label>

                            </div>
                        </div>

                        {{-- Ringkasan Total --}}
                        <div class="bg-blue-700 rounded-xl p-5 text-white">
                            <div class="flex justify-between items-center mb-3">
                                <p class="text-blue-200 text-xs font-bold uppercase tracking-wider">Metode</p>
                                <p class="text-sm font-bold capitalize" x-text="metode === 'lunas' ? 'Lunas (Full Payment)' : 'Per Semester'"></p>
                            </div>
                            <div class="border-t border-blue-500/50 pt-3 flex justify-between items-center">
                                <p class="text-blue-200 text-xs font-bold uppercase tracking-wider">Total Tagihan</p>
                                <p class="text-2xl font-black" x-text="'Rp ' + totalFormatted"></p>
                            </div>
                        </div>

                    </div>

                    {{-- ── KANAN: Form ── --}}
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Lengkapi Data Pendukung</p>

                        <form action="{{ route('profesi.reviewpayment.create') }}" method="POST"
                              @submit="isSubmitting = true"
                              class="bg-white border border-slate-200 rounded-xl p-6 space-y-5">
                            @csrf

                            {{-- Hidden: kirim metode_pembayaran dari Alpine --}}
                            <input type="hidden" name="metode_pembayaran" :value="metode">

                            {{-- Ukuran Almamater --}}
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1">
                                    Ukuran Jas Almamater <span class="text-red-500">*</span>
                                </label>
                                <p class="text-xs text-slate-400 mb-2">Sudah termasuk dalam total tagihan.</p>
                                <div class="relative">
                                    <select name="ukuran_almamater" id="ukuran_almamater" required
                                            class="block w-full rounded-xl border border-slate-300 text-sm py-3 px-4 bg-slate-50 appearance-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition">
                                        <option value="" disabled selected>-- Pilih Ukuran --</option>
                                        @foreach(['S','M','L','XL','XXL'] as $size)
                                            <option value="{{ $size }}">Ukuran {{ $size }}</option>
                                        @endforeach
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-slate-400">
                                        <i class="fas fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                                @error('ukuran_almamater')
                                    <p class="mt-1.5 text-xs text-red-600"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Info VA --}}
                            <div class="flex items-start gap-3 bg-slate-50 border border-slate-200 rounded-xl p-4">
                                <i class="fas fa-info-circle text-blue-500 mt-0.5 flex-shrink-0"></i>
                                <p class="text-xs text-slate-600 leading-relaxed">
                                    Sistem akan membuatkan <strong class="text-slate-800">Virtual Account (VA) Mandiri</strong> dengan nominal sesuai metode yang dipilih. Anda akan diarahkan ke halaman instruksi pembayaran.
                                </p>
                            </div>

                            {{-- Submit --}}
                            <button type="submit"
                                    :disabled="isSubmitting"
                                    class="w-full flex items-center justify-center gap-2 bg-blue-700 hover:bg-blue-800 text-white font-bold py-3.5 rounded-xl shadow-md shadow-blue-600/20 transition-all active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed">
                                <template x-if="!isSubmitting">
                                    <span class="flex items-center gap-2">
                                        <i class="fas fa-shield-check"></i>
                                        Buat Tagihan VA —
                                        <span x-text="'Rp ' + totalFormatted" class="font-black"></span>
                                    </span>
                                </template>
                                <template x-if="isSubmitting">
                                    <span class="flex items-center gap-2">
                                        <i class="fas fa-circle-notch fa-spin"></i>
                                        Sedang Memproses...
                                    </span>
                                </template>
                            </button>

                        </form>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>
@endsection