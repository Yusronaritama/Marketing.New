@extends('layouts.admin.app')
@section('title', 'Edit Nilai Quiz')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.moodle.quiz-complete.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Daftar Attempt
        </a>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-dark">
        <div class="px-6 py-6 border-b border-gray-200 dark:border-gray-800">
            <h4 class="text-xl font-semibold text-gray-900 dark:text-white">
                Edit Data Quiz Attempt
            </h4>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Ubah nilai dan status attempt peserta.
            </p>
        </div>

        <div class="p-6">
            <div class="mb-8 grid grid-cols-1 md:grid-cols-2 gap-6 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-100 dark:border-gray-700">
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Nama Peserta</label>
                    <div class="text-sm font-semibold text-gray-900 dark:text-white">
                        {{ $attempt->moodleUser->masterUser->user->name ?? 'N/A' }}
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Username / VA</label>
                    <div class="text-sm font-semibold text-gray-900 dark:text-white">
                        {{ $attempt->moodleUser->username ?? 'N/A' }}
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Quiz / Course</label>
                    <div class="text-sm font-semibold text-gray-900 dark:text-white">
                        {{ $attempt->course->fullname ?? 'Unknown Quiz' }}
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Attempt ID</label>
                    <div class="text-sm font-mono text-gray-600 dark:text-gray-400">
                        #{{ $attempt->attempt_id }}
                    </div>
                </div>
            </div>

            <form action="{{ route('admin.moodle.quiz-complete.update', $attempt->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <div>
                        <label for="score" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Score (Nilai Perolehan)
                        </label>
                        <div class="relative">
                            <input type="number" id="score" name="score" step="0.01" value="{{ old('score', $attempt->score ?? $attempt->grade) }}"
                                class="w-full rounded-lg border border-gray-200 bg-white px-4 py-3 text-gray-900 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 transition-colors dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:border-brand-800 @error('score') border-red-500 @enderror">
                            @error('score')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Nilai murni yang didapat peserta.</p>
                    </div>

                    <div>
                        <label for="grade" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Max Grade (Nilai Maksimal Soal)
                        </label>
                        <input type="number" id="grade" name="grade" step="0.01" value="{{ old('grade', $attempt->max_grade ?? $attempt->grade) }}"
                            class="w-full rounded-lg border border-gray-200 bg-white px-4 py-3 text-gray-900 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 transition-colors dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:border-brand-800">
                        <p class="mt-1 text-xs text-gray-500">Total nilai maksimal jika semua jawaban benar (biasanya 10 atau 100).</p>
                    </div>

                    <div>
                        <label for="state" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Status Pengerjaan
                        </label>
                        <select id="state" name="state" class="w-full rounded-lg border border-gray-200 bg-white px-4 py-3 text-gray-900 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 transition-colors dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:border-brand-800">
                            <option value="finished" {{ $attempt->state == 'finished' ? 'selected' : '' }}>Finished (Selesai)</option>
                            <option value="inprogress" {{ $attempt->state == 'inprogress' ? 'selected' : '' }}>In Progress (Sedang Mengerjakan)</option>
                            <option value="overdue" {{ $attempt->state == 'overdue' ? 'selected' : '' }}>Overdue (Terlambat)</option>
                        </select>
                    </div>
                </div>

                <div class="mt-8 flex items-center justify-end gap-3 pt-6 border-t border-gray-100 dark:border-gray-700">
                    <a href="{{ route('admin.moodle.quiz-complete.index') }}"
                        class="px-5 py-2.5 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 font-medium transition-colors dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-5 py-2.5 rounded-lg bg-blue-600 text-white hover:bg-blue-700 font-medium transition-colors shadow-lg shadow-blue-500/30">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection