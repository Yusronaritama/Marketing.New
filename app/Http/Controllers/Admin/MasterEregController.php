<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterEreg;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class MasterEregController extends Controller
{
    /**
     * Hapus semua file berkas pada master_ereg
     */
    public function deleteFiles($id)
    {
        try {
            $masterEreg = MasterEreg::findOrFail($id);
            
            // Daftar kolom file yang akan dihapus
            $fileColumns = [
                'file_kk',
                'file_ijazah',
                'file_ktp',
                'file_ijazah_s1',
                'file_transkrip_s1',
                'file_surat_pindah',
                'file_ktm',
                'file_surat_akademik_pindahan',
                'file_surat_alih_jenjang',
                'file_surat_akademik_ekstensi',
                'file_surat_tugas_kerja',
                'file_transkrip_nilai_rpl',
                'file_surat_akademik_rpl',
                'file_surat_buta_warna'
            ];
            
            // Hapus file fisik dari storage
            foreach ($fileColumns as $column) {
                if ($masterEreg->$column && Storage::disk('public')->exists($masterEreg->$column)) {
                    Storage::disk('public')->delete($masterEreg->$column);
                }
            }
            
            // Set semua kolom file menjadi NULL
            foreach ($fileColumns as $column) {
                $masterEreg->$column = null;
            }
            
            // Reset status menjadi in_progress jika belum completed
            if ($masterEreg->status !== 'completed') {
                $masterEreg->status = 'in_progress';
                $masterEreg->step_completed = 1; // Kembali ke step 1
            }
            
            $masterEreg->save();
            
            return redirect()->back()
                ->with('success', 'Semua berkas berhasil dihapus dan status direset.');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus berkas: ' . $e->getMessage());
        }
    }
}