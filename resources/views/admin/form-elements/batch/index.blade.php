@extends('layouts.admin.app')

@section('title', 'Daftar Batch Soal')

@section('content')
<div class="container mx-auto px-4 py-6">

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold">Daftar Batch Soal</h2>
        <a href="{{ route('admin.form-elements.batch.create') }}" 
           class="bg-blue-600 hover:bg-blue-700 text-black px-5 py-2 rounded-lg shadow">
            + Tambah Batch
        </a>
    </div>

    {{-- Alert sukses/error --}}
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    {{-- List Batch --}}
    @if ($batches->isEmpty())
        <div class="text-center py-10 text-gray-600">
            <p>Belum ada batch soal yang dibuat.</p>
        </div>
    @else
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($batches as $batch)
                <div class="bg-white rounded-xl shadow-md p-5 hover:shadow-lg transition">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="text-xl font-semibold text-gray-800">{{ $batch->batch_name }}</h3>
                        <span class="text-sm text-gray-500">{{ $batch->uuid }}</span>
                    </div>
                    <div class="text-gray-700 space-y-1 text-sm">
                        <p><strong>Jumlah Soal:</strong> {{ $batch->total_questions }}</p>
                        <p><strong>Jawaban per Soal:</strong> {{ $batch->answers_per_question }}</p>
                        <p><strong>Durasi:</strong> {{ $batch->duration }} menit</p>
                        <p><strong>Expired:</strong> {{ \Carbon\Carbon::parse($batch->expired_at)->format('d M Y H:i') }}</p>
                    </div>

                    <div class="mt-5 flex justify-between items-center">
                        <a href="{{ route('admin.form-elements.questions.create', ['batch' => $batch->id]) }}" 
                           class="bg-green-600 hover:bg-green-700 text-black px-4 py-2 rounded text-sm">
                            + Buat Soal
                        </a>
                        <a href="#" 
                           class="text-blue-600 hover:underline text-sm font-medium">
                            Lihat Soal
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
<script src="{{ asset('assets/js/bundle.js') }}"></script>
@endsection
