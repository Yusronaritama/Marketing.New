<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use App\Models\MasterUser;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PromoReportController extends Controller
{
    public function index()
    {
        // 1. Ambil daftar sekolah/kode promo unik yang memiliki kuota aktif
        $promoCodes = PromoCode::select('code', 'description', 'discount')
            ->groupBy('code', 'description', 'discount')
            ->get();

        // 2. Tempelkan data user VA (baik yang paid maupun status lainnya agar laporan lengkap)
        // Catatan: master_users.* sudah otomatis membawa kolom trx_id,
        // yang dipakai di view sebagai pengganti va_number.
        foreach ($promoCodes as $promo) {
            $promo->va_users = MasterUser::select('master_users.*', 'users.name as user_name')
                ->join('users', 'master_users.user_id', '=', 'users.id')
                ->where('master_users.promo_code_used', 'LIKE', '%' . $promo->code . '%')
                ->orderBy('master_users.va_status', 'asc') // Mengelompokkan status pembayaran
                ->get();
        }

        return view('admin.promo_report', compact('promoCodes'));
    }

    /**
     * Export daftar user yang menggunakan satu kode promo tertentu ke CSV.
     *
     * Tambahkan route berikut di routes/web.php (dalam grup admin/auth):
     *   Route::get('admin/promo-report/export/{code}', [PromoReportController::class, 'exportByCode'])
     *       ->name('admin.promo-report.export');
     *
     * Lalu di Blade bisa dipakai sebagai link biasa, contoh:
     *   <a href="{{ route('admin.promo-report.export', $promo->code) }}">Export</a>
     */
    public function exportByCode(string $code): StreamedResponse
    {
        $promo = PromoCode::where('code', $code)->firstOrFail();

        $vaUsers = MasterUser::select('master_users.*', 'users.name as user_name')
            ->join('users', 'master_users.user_id', '=', 'users.id')
            ->where('master_users.promo_code_used', 'LIKE', '%' . $promo->code . '%')
            ->orderBy('master_users.va_status', 'asc')
            ->get();

        $filename = 'Peserta_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $promo->code) . '_' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->streamDownload(function () use ($vaUsers) {
            $handle = fopen('php://output', 'w');

            // BOM UTF-8 supaya karakter non-ASCII terbaca benar di Excel
            fwrite($handle, "\xEF\xBB\xBF");

            // Header kolom
            fputcsv($handle, [
                'Nama Lengkap',
                'User ID',
                'Gelombang',
                'Trx ID',
                'Nominal',
                'Status VA',
                'Tanggal Bayar',
            ]);

            // Data baris per user
            foreach ($vaUsers as $user) {
                fputcsv($handle, [
                    $user->user_name,
                    $user->user_id,
                    $user->gelombang,
                    $user->trx_id ?? '-',
                    number_format((float) $user->va_amount, 0, ',', '.'),
                    strtoupper($user->va_status),
                    $user->paid_at ?? $user->payment_date ?? '-',
                ]);
            }

            fclose($handle);
        }, $filename, $headers);
    }
}