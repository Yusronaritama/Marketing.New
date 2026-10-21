<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // WAJIB ADA
use Carbon\Carbon; // WAJIB ADA

class PengaturanGelombangController extends Controller
{
    private $filename = 'app_gelombang_status.json';

    public function getStatus()
    {
        // 1. Data Default agar tidak null pointer
        $data = [
            'is_closed'       => false,
            'closing_at'      => null,
            'next_opening_at' => null,
        ];

        try {
            // 2. Baca file jika ada
            if (Storage::disk('local')->exists($this->filename)) {
                $json = Storage::disk('local')->get($this->filename);
                $decoded = json_decode($json, true);
                if ($decoded) {
                    $data = array_merge($data, $decoded);
                }
            }

            $isClosedNow = false;
            $manualClosed = $data['is_closed'] ?? false;
            $closingAt = $data['closing_at'] ?? null;

            // 3. Logika Penentuan Status
            if ($manualClosed) {
                $isClosedNow = true;
            } elseif ($closingAt) {
                // Gunakan Carbon untuk membandingkan waktu server vs waktu tutup
                if (Carbon::now()->greaterThanOrEqualTo(Carbon::parse($closingAt))) {
                    $isClosedNow = true;
                }
            }

            return response()->json([
                'status'          => 'success',
                'is_closed'       => $isClosedNow,
                'closing_at'      => $closingAt,
                'next_opening_at' => $data['next_opening_at'] ?? null,
                'server_time'     => Carbon::now()->toDateTimeString()
            ]);

        } catch (\Exception $e) {
            // Jika ada error (misal format tgl salah), kembalikan status aman (tidak tutup)
            return response()->json([
                'status'    => 'error',
                'is_closed' => false,
                'message'   => $e->getMessage()
            ], 500);
        }
    }

    public function toggle(Request $request)
    {
        try {
            $data = [
                'is_closed'       => (bool) $request->is_closed,
                'closing_at'      => $request->closing_at,
                'next_opening_at' => $request->next_opening_at,
                'updated_at'      => Carbon::now()->toDateTimeString(),
                'updated_by'      => auth()->user()->name ?? 'Admin'
            ];

            Storage::disk('local')->put($this->filename, json_encode($data));

            return response()->json(['status' => 'success', 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}