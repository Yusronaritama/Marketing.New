<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Biodata;
use App\Models\MasterEreg;
use App\Models\SlikBilling; // Jangan lupa import ini

class ProgressPendaftaran
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $syarat  (Parameter: biodata_completed, ereg_completed, paid)
     */
    public function handle(Request $request, Closure $next, string $syarat): Response
    {
        $user = Auth::user();

        // ----------------------------------------------------------------
        // SYARAT 1: BIODATA HARUS COMPLETED
        // (Digunakan untuk memproteksi akses ke Registrasi/Step 2)
        // ----------------------------------------------------------------
        if ($syarat === 'biodata_completed') {
            $biodata = Biodata::where('user_id', $user->id)->first();

            // Cek ada data & status_bio completed
            if (!$biodata || $biodata->status_bio != 'completed') {
                return redirect()->route('mahasiswa.biodata.create') // Redirect langsung ke form biodata
                    ->with('error', 'Akses ditolak. Mohon lengkapi dan simpan Biodata Anda hingga status Selesai.');
            }
        }

        // ----------------------------------------------------------------
        // SYARAT 2: EREG (PENDAFTARAN) HARUS COMPLETED
        // (Digunakan untuk memproteksi akses ke Ujian/Step 3 & Pembayaran/Step 4)
        // ----------------------------------------------------------------
        if ($syarat === 'ereg_completed') {
            $ereg = MasterEreg::where('user_id', $user->id)->first();

            // Cek apakah data ereg ada DAN statusnya completed atau final
            if (!$ereg || !in_array($ereg->status, ['completed', 'final'])) {
                // Redirect ke Step 2 jika Step 1 sudah selesai tapi Step 2 belum
                return redirect()->route('mahasiswa.registration')
                    ->with('error', 'Akses ditolak. Silakan selesaikan Pendaftaran Dasar dan Upload Berkas terlebih dahulu.');
            }
        }

        // ----------------------------------------------------------------
        // SYARAT 3: PEMBAYARAN HARUS LUNAS (PAID)
        // (Digunakan untuk memproteksi akses ke Data Final/Step 5)
        // ----------------------------------------------------------------
        if ($syarat === 'paid') {
            $paidBill = SlikBilling::where('user_id', $user->id)
                ->where(function($query) {
                    // Kriteria Lunas:
                    $query->where('status', 'paid')       
                          ->orWhere('status', 'settled')  
                          ->orWhereNotNull('datetime_payment'); 
                })
                ->first(); // Ambil satu saja jika ketemu

            
            if (!$paidBill) {
                return redirect()->route('pembayaran.ukt')
                    ->with('error', 'Akses ditolak. Mohon lunasi pembayaran UKT terlebih dahulu.');
            }
        }

        return $next($request);
    }
}