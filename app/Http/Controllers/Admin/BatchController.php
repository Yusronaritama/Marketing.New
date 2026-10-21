<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Batch;
use Carbon\Carbon;

class BatchController extends Controller
{
    /**
     * Menampilkan form tambah batch baru
     */
    public function create()
    {
        return view('admin.form-elements.batch.create');
    }

    /**
     * Menampilkan daftar semua batch
     */
    public function index()
    {
        $batches = Batch::latest()->get();
        return view('admin.form-elements.batch.index', compact('batches'));
    }

    /**
     * Simpan data batch baru ke database
     */
    public function store(Request $request)
    {
        $request->validate([
            'batch_name' => 'required|string|max:255',
            'total_questions' => 'required|integer|min:1',
            'answers_per_question' => 'required|integer|min:2|max:10',
            'duration' => 'required|integer|min:1',
            'expired_at' => 'required|date|after:now',
        ]);

        try {
            Batch::create([
                'uuid' => Str::uuid(),
                'batch_name' => $request->batch_name,
                'total_questions' => $request->total_questions,
                'answers_per_question' => $request->answers_per_question,
                'duration' => $request->duration,
                'expired_at' => Carbon::parse($request->expired_at),
            ]);

            return redirect()->route('admin.form-elements.batch.index')->with('success', 'Batch berhasil dibuat!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
