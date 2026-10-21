<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Question;
use App\Models\Batch;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class QuestionController extends Controller
{
    public function create(Request $request)
    {
        // Ambil ID batch dari query ?batch=ID (jika ada)
        $batchId = $request->query('batch');

        // Ambil semua batch untuk dropdown
        $batches = Batch::orderBy('created_at', 'desc')->get();

        // Jika ada batch yang dipilih, ambil datanya
        $batch = $batchId ? Batch::find($batchId) : null;

        // Ambil semua soal berdasarkan batch yang dipilih (jika ada)
        $questions = $batch ? Question::where('batch_id', $batch->id)->latest()->get() : collect();

        // Kirim ke view
        return view('admin.form-elements.create', compact('batches', 'batch', 'questions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'batch_id' => 'required|exists:batches,id',
            'text' => 'required|string',
            'options' => 'required|array|min:2',
            'correct_answer' => 'required|string',
            'image' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'document' => 'nullable|file|mimes:pdf,docx|max:5120',
        ]);

        try {
            $batch = Batch::findOrFail($validated['batch_id']);

            // Upload file jika ada
            $imagePath = $request->hasFile('image')
                ? $request->file('image')->store('questions/images', 'public')
                : null;

            $docPath = $request->hasFile('document')
                ? $request->file('document')->store('questions/docs', 'public')
                : null;

            // Simpan ke DB
            Question::create([
                'uuid' => Str::uuid(),
                'batch_id' => $batch->id,
                'text' => $validated['text'],
                'options' => json_encode($validated['options']),
                'correct_answer' => $validated['correct_answer'],
                'image_path' => $imagePath,
                'document_path' => $docPath,
                'expired_time' => $batch->expired_at,
            ]);

            return redirect()->back()->with('success', '✅ Soal berhasil disimpan untuk batch ' . $batch->batch_name . '!');
        } catch (\Exception $e) {
            Log::error('❌ Gagal menyimpan soal: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menyimpan soal.');
        }
    }
}
