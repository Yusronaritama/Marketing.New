<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterGelombang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterGelombangController extends Controller
{
    public function index()
    {
        $gelombang = MasterGelombang::orderBy('angka_gelombang', 'asc')->get();
        return view('admin.mastergelombang.index', compact('gelombang'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_gelombang' => 'required|string|max:255',
            'angka_gelombang' => 'required|integer|unique:master_gelombangs,angka_gelombang',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);

        try {
            MasterGelombang::create([
                'nama_gelombang' => $request->nama_gelombang,
                'angka_gelombang' => $request->angka_gelombang,
                'is_active' => 0, // Default tidak aktif
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
            ]);

            return redirect()->back()->with('success', 'Gelombang baru berhasil ditambahkan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_gelombang' => 'required|string|max:255',
            'angka_gelombang' => 'required|integer|unique:master_gelombangs,angka_gelombang,' . $id,
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);

        try {
            MasterGelombang::findOrFail($id)->update([
                'nama_gelombang' => $request->nama_gelombang,
                'angka_gelombang' => $request->angka_gelombang,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
            ]);

            return redirect()->back()->with('success', 'Data Gelombang berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $gelombang = MasterGelombang::findOrFail($id);
            if ($gelombang->is_active) {
                return redirect()->back()->with('error', 'Tidak dapat menghapus gelombang yang sedang aktif!');
            }
            $gelombang->delete();

            return redirect()->back()->with('success', 'Gelombang berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function setActive($id)
    {
        try {
            DB::beginTransaction();

            // 1. Matikan semua gelombang
            MasterGelombang::query()->update(['is_active' => 0]);

            // 2. Aktifkan gelombang yang dipilih
            $gelombang = MasterGelombang::findOrFail($id);
            $gelombang->update(['is_active' => 1]);

            DB::commit();

            return redirect()->back()->with('success', $gelombang->nama_gelombang . ' sekarang AKTIF dan digunakan untuk pendaftar baru!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mengaktifkan gelombang: ' . $e->getMessage());
        }
    }
    public function setInactive($id)
    {
        try {
            $gelombang = MasterGelombang::findOrFail($id);
            $gelombang->update(['is_active' => 0]);

            return redirect()->back()->with('success', $gelombang->nama_gelombang . ' telah dinonaktifkan. Saat ini tidak ada gelombang pendaftaran yang aktif.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menonaktifkan gelombang: ' . $e->getMessage());
        }
    }
}