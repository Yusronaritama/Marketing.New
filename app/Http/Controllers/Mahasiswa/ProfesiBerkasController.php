<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log; // <-- [BARU] Import Log biar bisa nyatet error
use App\Models\BerkasProfesi;
use Exception; // <-- [BARU] Import Exception

class ProfesiBerkasController extends Controller
{
    public function create()
    {
        $user = Auth::user();
        $berkas = $user->berkasProfesi;
        
        if ($berkas && in_array($berkas->status_verifikasi, ['lulus', 'proses', 'tolak'])) { // <-- Tambahin 'tolak' di sini
            return redirect()->route('profesi.dashboard')
                ->with('error', 'Akses ditutup. Status berkas Anda saat ini: ' . strtoupper($berkas->status_verifikasi));
        }

        if ($berkas && in_array($berkas->status_verifikasi, ['lulus', 'proses'])) {
            return redirect()->route('profesi.dashboard')
                ->with('error', 'Berkas Anda sedang diproses atau sudah lulus. Anda tidak bisa mengunggah ulang saat ini.');
        }

        return view('mahasiswa.profesi.berkas.upload-berkas', compact('berkas')); 
    }
    

    public function store(Request $request)
    {
        // 1. Validasi Super Ketat
        $request->validate([
            'file_akreditasi'   => 'required|mimes:pdf|max:2048',
            'file_ijazah'       => 'required|mimes:pdf|max:2048',
            'file_transkrip'    => 'required|mimes:pdf|max:2048',
            'file_pasfoto'      => 'required|mimes:jpg|max:2048', 
            'file_ktp'          => 'required|mimes:pdf|max:2048',
            'file_kk'           => 'required|mimes:pdf|max:2048',
            'file_asuransi'     => 'required|mimes:pdf|max:2048',
            'file_surat_sehat'  => 'required|mimes:pdf|max:2048',
        ], [
            'max' => 'Ukuran file :attribute maksimal 2MB dawg!',
            'file_pasfoto.mimes' => 'Pas foto wajib berformat JPG/JPEG.',
            'mimes' => 'Berkas dokumen wajib berformat PDF.',
        ]);

        $user = Auth::user();
        
        // [BARU] Bungkus dengan try-catch
        try {
            // 2. Siapkan array untuk nyimpen path lokasi file
            $folderPath = 'berkas_profesi/' . $user->id;
            $filePaths = [];

            $fileInputs = [
                'file_akreditasi', 'file_ijazah', 'file_transkrip', 'file_pasfoto', 
                'file_ktp', 'file_kk', 'file_asuransi', 'file_surat_sehat'
            ];

            // 3. Proses Upload Setiap File
            foreach ($fileInputs as $input) {
                if ($request->hasFile($input)) {
                    $file = $request->file($input);
                    $fileName = str_replace('file_', '', $input) . '_' . time() . '.' . $file->getClientOriginalExtension();
                    
                    $path = $file->storeAs($folderPath, $fileName, 'public');
                    $filePaths[$input] = $path;
                }
            }

            // 4. Simpan ke Database
            BerkasProfesi::updateOrCreate(
                ['user_id' => $user->id],
                [
                	'status_berkas'     => 'dikirim',
                    'file_akreditasi'   => $filePaths['file_akreditasi'],
                    'file_ijazah'       => $filePaths['file_ijazah'],
                    'file_transkrip'    => $filePaths['file_transkrip'],
                    'file_pasfoto'      => $filePaths['file_pasfoto'],
                    'file_ktp'          => $filePaths['file_ktp'],
                    'file_kk'           => $filePaths['file_kk'],
                    'file_asuransi'     => $filePaths['file_asuransi'],
                    'file_surat_sehat'  => $filePaths['file_surat_sehat'],
                    'status_verifikasi' => 'belum',
                    'catatan_verifikasi'=> null 
                ]
            );

            return redirect()->route('profesi.dashboard')
                ->with('success', 'Semua berkas persyaratan berhasil diunggah! Silakan tunggu proses verifikasi.');

        } catch (Exception $e) {
            // [BARU] Kalau error, catat ke file storage/logs/laravel.log
            Log::error('GAGAL UPLOAD BERKAS PROFESI | User ID: ' . $user->id . ' | Error: ' . $e->getMessage());

            // [BARU] Lempar balik ke halaman form upload dengan notif error
            return back()->withInput()->with('error', 'Terjadi kesalahan sistem saat menyimpan berkas. Pastikan format file sesuai atau hubungi admin.');
        }
    }
}