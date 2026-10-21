@extends('layouts.admin.app')

@section('title', 'Target Pendaftar')

@section('content')
  <main class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
        <!-- Page Title -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Target Pendaftar</h1>
            <p class="text-gray-600 mt-2">Kelola target jumlah pendaftar untuk periode ini</p>
        </div>

        <!-- Current Target Display -->
        <div class="card bg-white p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-medium text-gray-700">Target Pendaftar Saat Ini</h2>
                    <p class="text-gray-500 text-sm mt-1">Jumlah target yang telah ditetapkan</p>
                </div>
                <div class="text-right">
                    <div class="target-display">{{ $target ? $target->target : '0' }}</div>
                    <p class="text-gray-500 text-sm mt-1">Pendaftar</p>
                </div>
            </div>
        </div>

        <!-- Update Target Form -->
        <div class="card bg-white p-6">
            <h2 class="text-lg font-medium text-gray-700 mb-4">Perbarui Target Pendaftar</h2>
            
            <form action="{{ route('admin.dashboard.target.update') }}" method="post">
                @csrf
                <div class="mb-6">
                    <label for="target" class="block text-sm font-medium text-gray-700 mb-2">
                        Masukkan Jumlah Target Pendaftaran
                    </label>
                    <div class="relative rounded-md shadow-sm">
                        <input 
                            type="number" 
                            name="target" 
                            id="target"
                            max="100000" 
                            class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Masukkan jumlah target"
                            required
                        >
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-gray-500">orang</span>
                        </div>
                    </div>
                    <p class="text-gray-500 text-xs mt-2">Maksimal 100.000 pendaftar</p>
                </div>
                
                <div class="flex justify-end">
                    <button 
                        type="submit" 
                        class="btn-primary px-6 py-3 rounded-lg font-medium flex items-center"
                    >
                        <i class="fas fa-save mr-2"></i>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Success Message -->
        @if (session('success'))
        <div class="mt-6 alert-success border-l-4 p-4 rounded">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-2"></i>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        </div>
        @endif
    </main>
@endsection
<script src="{{ asset('assets/js/bundle.js') }}"></script>