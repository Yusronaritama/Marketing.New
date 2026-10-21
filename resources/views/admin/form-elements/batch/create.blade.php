@extends('layouts.admin.app')

@section('title', 'Tambah Batch Soal')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-2xl font-semibold mb-4">Tambah Batch Soal Baru</h2>

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

        {{-- Form tambah batch --}}
        <form action="{{ route('admin.form-elements.batch.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block font-semibold mb-2">Nama Batch</label>
                <input type="text" name="batch_name" class="w-full border rounded-lg p-2" required>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold mb-2">Jumlah Soal</label>
                    <input type="number" name="total_questions" class="w-full border rounded-lg p-2" min="1" required>
                </div>
                <div>
                    <label class="block font-semibold mb-2">Jawaban per Soal</label>
                    <input type="number" name="answers_per_question" class="w-full border rounded-lg p-2" min="2" max="10" required>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block font-semibold mb-2">Durasi (menit)</label>
                    <input type="number" name="duration" class="w-full border rounded-lg p-2" min="1" required>
                </div>
                <div>
                    <label class="block font-semibold mb-2">Tanggal Kedaluwarsa</label>
                    <input type="datetime-local" name="expired_at" class="w-full border rounded-lg p-2" required>
                </div>
            </div>

            <div class="mt-6 text-right">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                    Simpan Batch
                </button>
            </div>
        </form>
    </div>
</div>


<script src="{{ asset('assets/js/bundle.js') }}"></script>
@endsection
