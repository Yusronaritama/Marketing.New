<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\PembayaranBerhasil;
use Exception;

class RegistrasiPendaftarController extends Controller
{
    public function handleRegistrasiPendaftar(Request $request)
    {
        // 1. PENGAMBILAN DATA
        $data = $request->all();

        Log::info('--- CALLBACK MANDIRI DEBUG ---');
        Log::info('Headers: ', $request->headers->all());
        Log::info('Payload: ', $data);

        // 2. NORMALISASI VARIABEL
        $no_va = $data['virtual_account'] ?? $data['no_va'] ?? $data['va_number'] ?? null;
        Log::info("Mencari VA: " . $no_va);

        if (!$no_va) {
            Log::error("Format Payload Mandiri Salah: VA tidak ditemukan.");
            return response()->json(['status' => 'error', 'message' => 'Invalid Request'], 400);
        }

        // 3. LOGIKA IDENTIFIKASI DENGAN JOIN
        // Mencoba ambil dari master_users dan join ke users untuk mendapatkan email & nama
        $record = DB::table('master_users')
            ->leftJoin('users', 'master_users.user_id', '=', 'users.id')
            ->select('master_users.id', 'master_users.va_status', 'users.email', 'users.name')
            ->where('master_users.va_number', $no_va)
            ->first();
        
        $targetTable = 'master_users';

        // Jika tidak ketemu di master_users, cek di tabel users secara langsung
        if (!$record) {
            $record = DB::table('users')
                ->select('id', 'va_status', 'email', 'name')
                ->where('va_number', $no_va)
                ->first();
            $targetTable = 'users';
        }

        if (!$record) {
            Log::error("VA Tidak Dikenali di database: $no_va");
            return response()->json(['status' => 'error', 'message' => 'VA Not Found'], 404);
        }

        Log::info("Data User Ditemukan (ID: {$record->id}, Email: {$record->email})");

        // 4. EKSEKUSI DENGAN TRANSACTION & LOCKING
        try {
            return DB::transaction(function () use ($record, $targetTable, $no_va) {
                
                // Lock baris untuk mencegah race condition (double hit)
                $current = DB::table($targetTable)->where('id', $record->id)->lockForUpdate()->first();

                if ($current->va_status === 'paid') {
                    Log::info("⚠️ VA: $no_va sudah PAID sebelumnya.");
                    return response()->json(['status' => 'success', 'message' => 'Already processed'], 200);
                }

                // Update status di database
                DB::table($targetTable)->where('id', $record->id)->update([
                    'va_status'      => 'paid',
                    'paid_at'        => now(),
                    'payment_method' => 'MANDIRI_VA',
                    'updated_at'     => now(),
                ]);

                // Pengiriman Email
                try {
                    if (!empty($record->email)) {
                        Mail::to($record->email)->send(new PembayaranBerhasil($record->name ?? 'User'));
                        Log::info("✅ Email berhasil dikirim ke: " . $record->email);
                    } else {
                        Log::warning("⚠️ Email tidak terkirim: Data email kosong untuk record ID: " . $record->id);
                    }
                } catch (Exception $mailError) {
                    Log::error("💥 Gagal kirim email: " . $mailError->getMessage());
                }

                Log::info("✅ Pembayaran SUKSES & status terupdate untuk VA: $no_va");

                return response()->json(['status' => 'success', 'message' => 'OK'], 200);
            });

        } catch (Exception $e) {
            Log::error("💥 Gagal proses callback Mandiri: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Internal Server Error'], 500);
        }
    }
}