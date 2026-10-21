<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PerbaikanController extends Controller
{
    // Nama file penyimpanan status (disimpan di storage/app/)
    private $filename = 'app_maintenance_mode.json';

    /**
     * Mengambil status maintenance saat ini.
     * Digunakan oleh Admin Sidebar & Polling Mahasiswa.
     */
    public function getStatus()
    {
        // Default Data
        $data = [
            'active'        => false, 
            'scheduled_at'  => null,
            'estimated_end' => null, // [BARU] Default null
            'updated_by'    => 'System',
            'updated_at'    => null
        ];
        
        // Cek apakah file setting ada
        if (Storage::disk('local')->exists($this->filename)) {
            $json = Storage::disk('local')->get($this->filename);
            $data = json_decode($json, true) ?? $data;
        }

        // --- LOGIKA PENENTUAN STATUS ---
        $manualActive = $data['active'] ?? false;
        $scheduledAt  = $data['scheduled_at'] ?? null;
        $estimatedEnd = $data['estimated_end'] ?? null; // [BARU] Ambil data estimasi
        
        $isMaintenanceNow = false;

        // 1. Jika Manual ON -> Pasti Maintenance
        if ($manualActive) {
            $isMaintenanceNow = true;
        } 
        // 2. Jika ada Jadwal -> Cek apakah waktu sudah tiba
        elseif ($scheduledAt) {
            $now = Carbon::now();
            $startTime = Carbon::parse($scheduledAt);
            
            if ($now->greaterThanOrEqualTo($startTime)) {
                $isMaintenanceNow = true;
            }
        }

        return response()->json([
            'status'         => 'success',
            'is_maintenance' => $isMaintenanceNow, // Status Real-time
            'manual_active'  => $manualActive,     // Status Tombol Merah
            'scheduled_at'   => $scheduledAt,      // Waktu Mulai
            'estimated_end'  => $estimatedEnd,     // [BARU] Waktu Selesai (Dikirim ke Frontend)
            'server_time'    => now()->toDateTimeString()
        ]);
    }

    /**
     * Mengubah status maintenance (Toggle / Set Jadwal).
     */
    public function toggle(Request $request)
    {
        // Validasi Input
        $request->validate([
            'active'        => 'required|boolean',
            'scheduled_at'  => 'nullable|date',
            'estimated_end' => 'nullable|date', // [BARU] Validasi input tanggal selesai
        ]);

        try {
            // Siapkan data untuk disimpan
            $data = [
                'active'        => (bool) $request->active,
                'scheduled_at'  => $request->scheduled_at,
                'estimated_end' => $request->estimated_end, // [BARU] Simpan ke JSON
                'updated_at'    => now()->toDateTimeString(),
                'updated_by'    => auth()->user()->name ?? 'Admin'
            ];

            // Simpan ke file JSON
            Storage::disk('local')->put($this->filename, json_encode($data));

            // Buat pesan feedback
            $msg = "Status diperbarui.";
            
            if ($request->active) {
                $msg = "Mode Maintenance diaktifkan SEKARANG (Manual).";
            } elseif ($request->scheduled_at) {
                $dateIndo = Carbon::parse($request->scheduled_at)
                                  ->locale('id')
                                  ->isoFormat('dddd, D MMMM Y HH:mm');
                $msg = "Maintenance dijadwalkan pada: $dateIndo";
            } else {
                $msg = "Sistem kembali normal (Maintenance dimatikan).";
            }

            return response()->json([
                'status'  => 'success',
                'message' => $msg,
                'data'    => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error', 
                'message' => 'Gagal menyimpan status: ' . $e->getMessage()
            ], 500);
        }
    }
}