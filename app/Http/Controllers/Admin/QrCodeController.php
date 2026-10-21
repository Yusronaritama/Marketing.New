<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class QrCodeController extends Controller
{
    public function generate_qrCode()
    {
        Log::info('--- MEMULAI PROSES GENERATE QR CODE ---'); 

        try {
            // Join tabel slik_billings dengan master_users berdasarkan user_id
            $data = DB::table('slik_billings')
                ->join('master_users', 'slik_billings.user_id', '=', 'master_users.user_id')
                ->where('slik_billings.status', 'paid')
                ->select(
                    'slik_billings.id',
                    'slik_billings.user_id',
                    'slik_billings.trx_id as billing_trx_id',
                    'slik_billings.customer_name',
                    'master_users.trx_id as master_trx_id' // Ambil trx_id dari master_users
                ) 
                ->get();

            Log::info("Ditemukan {$data->count()} data slik_billings (paid) yang terhubung ke master_users.");

            $tempDir = public_path('assets/QrCodePresensi');

            if (!File::exists($tempDir)) {
                File::makeDirectory($tempDir, 0755, true);
                Log::info("Folder QR Code berhasil dibuat di: {$tempDir}");
            }

            $generatedCount = 0;
            $skippedCount = 0;

            foreach ($data as $value) {
                // Nama file tetap menggunakan trx_id billing agar sinkron dengan sistem view/list
                $filename = 'qrcode-id-' . $value->billing_trx_id . '.png'; 
                $absolutePath = $tempDir . '/' . $filename;

                if (!File::exists($absolutePath)) {
                    // Isi QR Code hanya trx_id milik master_users
                    $qrContent = (string) $value->master_trx_id;

                    // Lewati jika trx_id master kosong
                    if (empty($qrContent)) {
                        Log::warning("Skipped user_id: {$value->user_id} karena master_trx_id kosong.");
                        continue;
                    }

                    \QrCode::format('png')
                        ->size(300)
                        ->margin(1)
                        ->generate($qrContent, $absolutePath);

                    $generatedCount++;
                    Log::info("Berhasil generate QR Code berisi [{$qrContent}] untuk: {$value->customer_name} (Billing TRX: {$value->billing_trx_id})");
                } else {
                    $skippedCount++;
                }
            }
            
            Log::info("--- PROSES SELESAI ---");
            Log::info("Total Baru Dibuat: {$generatedCount} | Total Di-skip (Sudah Ada): {$skippedCount}");

            return redirect('/admin/qrcode-presensi')->with('success', "Berhasil melakukan generate {$generatedCount} QR Code baru.");

        } catch (\Exception $e) {
            Log::error("ERROR saat generate QR Code: " . $e->getMessage());
            Log::error($e->getTraceAsString()); 

            return redirect('/admin/dashboard')->with('error', 'Terjadi kesalahan saat membuat QR Code. Silakan cek log.');
        }
    }

    public function index(Request $request)
    {
        $statusFilter = $request->get('status');
        $tempDir = public_path('assets/QrCodePresensi');

        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        $files = glob($tempDir . '/qrcode-id-*.png');
        $existingTrxIds = array_map(function ($path) {
            $filename = basename($path, '.png');
            return str_replace('qrcode-id-', '', $filename);
        }, $files);

        $query = DB::table('slik_billings')
            ->where('status', 'paid')
            ->select('id', 'user_id', 'trx_id', 'customer_name as nama')
            ->orderBy('created_at', 'desc');

        if ($statusFilter === 'generated') {
            $query->whereIn('trx_id', $existingTrxIds);
        } elseif ($statusFilter === 'pending') {
            $query->whereNotIn('trx_id', $existingTrxIds);
        }

        $mahasiswa = $query->paginate(25)->appends($request->query());

        $mahasiswa->getCollection()->transform(function ($item) use ($existingTrxIds) {
            $item->has_qr = in_array($item->trx_id, $existingTrxIds);
            return $item;
        });

        $totalLunas = DB::table('slik_billings')->where('status', 'paid')->count();
        $totalSiapGlobal = count($existingTrxIds);

        return view('admin.qrcode.index', compact('mahasiswa', 'totalLunas', 'totalSiapGlobal', 'statusFilter'));
    }
}