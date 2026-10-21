@extends('layouts.admin.app')

@section('title', 'Tambah Soal')

@section('content')
<div class="container mx-auto px-4 py-6">

    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-2xl font-semibold mb-4">
            Tambah Soal untuk Batch: <span class="text-blue-600">{{ $batch->batch_name }}</span>
        </h2>

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

        {{-- Form Soal --}}
        <div id="questionForms"></div>
    </div>
</div>

{{-- Script interaktif --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('questionForms');

    // Data dari controller Laravel
    const batchId = "{{ $batch->id }}";
    const totalQuestions = "{{ $batch->total_questions }}";
    const totalAnswers = "{{ $batch->answers_per_question }}";

    container.innerHTML = '';

    for (let q = 1; q <= totalQuestions; q++) {
        const form = document.createElement('form');
        form.classList.add('bg-gray-50', 'p-4', 'rounded-lg', 'shadow', 'mb-6');
        form.action = "{{ route('admin.form-elements.batch.store') }}";
        form.method = "POST";
        form.enctype = "multipart/form-data";

        form.innerHTML = `
            @csrf
            <input type="hidden" name="batch_id" value="${batchId}">

            <h3 class="text-lg font-semibold mb-3">Soal ${q}</h3>

            <div class="mb-4">
                <label class="block font-semibold mb-2">Teks Soal:</label>
                <textarea name="text" rows="3" class="border rounded-lg w-full p-2" placeholder="Masukkan teks soal ke-${q}" required></textarea>
            </div>

            <div class="mb-4">
                <label class="block font-semibold mb-2">Upload Gambar (opsional):</label>
                <input type="file" name="image" accept="image/*" class="border rounded-lg w-full p-2">
            </div>

            <div class="mb-4">
                <label class="block font-semibold mb-2">Pilihan Jawaban:</label>
                <div class="space-y-2">
                    ${[...Array(parseInt(totalAnswers))].map((_, i) => `
                        <div class="flex items-center gap-2">
                            <input type="text" name="options[]" placeholder="Jawaban ${i+1}" class="border rounded-lg w-full p-2" required>
                            <input type="radio" name="correct_answer" value="${i+1}" required>
                            <label>Pilih jawaban benar</label>
                        </div>
                    `).join('')}
                </div>
            </div>

            <div class="mt-4 text-right">
                <button type="submit" class="bg-blue-600 text-Black px-6 py-2 rounded hover:bg-blue-700">
                    Simpan Soal ${q}
                </button>
            </div>
        `;

        container.appendChild(form);
    }
});
</script>

<script src="{{ asset('assets/js/bundle.js') }}"></script>
@endsection
