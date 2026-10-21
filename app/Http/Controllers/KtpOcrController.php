<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class KtpOcrController extends Controller
{
    public function showUploadForm()
    {
        return view('ktp-upload');
    }

    public function processKtp(Request $request)
    {
        $request->validate([
            'fotoKtp' => 'required|image|mimes:jpeg,png,jpg|max:5120'
        ]);

        try {
            // Simpan file sementara
            $filename = 'ktp_scan_' . now()->timestamp . '.' . $request->file('fotoKtp')->getClientOriginalExtension();
            $path = $request->file('fotoKtp')->storeAs('ktp_temp', $filename, 'public');
            $filePath = storage_path("app/public/{$path}");

            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => '❌ File tidak ditemukan di server.'
                ], 400);
            }

            // Kirim ke server OCR (n8n)
            $response = Http::timeout(60)
                ->attach('file', fopen($filePath, 'r'), basename($filePath))
                ->post('https://n8n-r3dgg4qq.n8x.biz.id/webhook/1c9e436a-fe25-45d9-96d6-840dd343ee78');

            if ($response->failed()) {
                // Hapus file temporary
                Storage::disk('public')->delete($path);
                
                return response()->json([
                    'success' => false,
                    'message' => '❌ Gagal menghubungi server OCR (status ' . $response->status() . ').'
                ], 500);
            }

            $result = $response->json();
            $data = $result['output'] ?? $result;
            
            Log::info('OCR Response', $data);

            // Validasi NIK hasil OCR
            $nikValid = false;
            $nikMessage = '';
            
            if (!empty($data['nik']) && preg_match('/^\d{16}$/', $data['nik'])) {
                $nikValid = true;
                $nikMessage = '✅ NIK berhasil dibaca dari foto: ' . $data['nik'];
                
                // Check NIK (anda bisa menambahkan logika checkNIK disini)
                // $this->checkNIK($data['nik']);
            } else {
                $nikMessage = '⚠ Tidak ditemukan NIK valid dalam gambar.';
            }

            // Hapus file setelah diproses
            Storage::disk('public')->delete($path);

            return response()->json([
                'success' => true,
                'data' => [
                    'nik' => $data['nik'] ?? '',
                    'nama_lengkap' => $data['nama'] ?? '',
                    'jenis_kelamin' => $data['jenis_kelamin'] ?? '',
                    'agama' => $data['agama'] ?? '',
                    'status_perkawinan' => $data['status_perkawinan'] ?? ''
                ],
                'nik_valid' => $nikValid,
                'message' => $nikMessage
            ]);

        } catch (\Throwable $th) {
            Log::error('OCR Error', ['message' => $th->getMessage()]);
            
            // Hapus file jika ada error
            if (isset($path)) {
                Storage::disk('public')->delete($path);
            }

            return response()->json([
                'success' => false,
                'message' => '❌ Gagal OCR: ' . $th->getMessage()
            ], 500);
        }
    }

    // Method untuk mengecek NIK (optional)
    private function checkNIK($nik)
    {
        // Implementasi logika pengecekan NIK
        // Contoh: validasi format, cek di database, dll.
        
        return true;
    }
}