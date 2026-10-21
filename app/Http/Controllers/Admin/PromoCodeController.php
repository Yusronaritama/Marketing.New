<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PromoCode;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PromoCodeController extends Controller
{
    private function getPromoDataWithCounts()
    {
        return PromoCode::latest()
            ->get()
            ->map(function ($promo) {
                $userIdsFromUsers = DB::table('users')
                    ->where('promo_code_used', $promo->code)
                    ->pluck('id')
                    ->toArray();

                $userIdsFromMaster = DB::table('master_users')
                    ->where('promo_code_used', $promo->code)
                    ->pluck('user_id')
                    ->toArray();

                $allUserIds = array_unique(array_merge($userIdsFromUsers, $userIdsFromMaster));

                if (empty($allUserIds)) {
                    $promo->total_count = 0;
                    $promo->paid_count = 0;
                    $promo->unpaid_count = 0;
                    return $promo;
                }

                $promo->total_count = count($allUserIds);

                $promo->paid_count = DB::table('master_users')
                    ->whereIn('user_id', $allUserIds)
                    ->where('va_status', 'paid')
                    ->count();

                $promo->unpaid_count = $promo->total_count - $promo->paid_count;

                return $promo;
            });
    }

    public function index()
    {
        $promos = $this->getPromoDataWithCounts();

        $grandTotalPakai = $promos->sum('total_count');
        $grandTotalPaid  = $promos->sum('paid_count');
        $grandTotalUnpaid = $promos->sum('unpaid_count');

        return view('admin.promocode.index', compact(
            'promos', 
            'grandTotalPakai', 
            'grandTotalPaid', 
            'grandTotalUnpaid'
        ));
    }

    /**
     * Export Ringkasan Promo & Daftar Lengkap User Pengguna Promo ke Excel
     */
public function exportExcel(): StreamedResponse
{
    // 1. Data Ringkasan Promo
    $promos = $this->getPromoDataWithCounts();
    $grandTotalPakai = $promos->sum('total_count');
    $grandTotalPaid  = $promos->sum('paid_count');
    $grandTotalUnpaid = $promos->sum('unpaid_count');

    // 2. Data Rincian Peserta / User
    $detailUsers = DB::table('master_users')
        ->join('users', 'users.id', '=', 'master_users.user_id')
        ->leftJoin('promo_codes', 'promo_codes.code', '=', 'master_users.promo_code_used')
        ->leftJoin('master_ereg', 'master_ereg.user_id', '=', 'master_users.user_id')
        ->leftJoin('program_studi', 'program_studi.kode_prodi', '=', 'master_ereg.jurusan1')
        ->whereNotNull('master_users.promo_code_used')
        ->where('master_users.promo_code_used', '!=', '')
        ->select(
            DB::raw('COALESCE(master_ereg.nama, users.name) as nama_lengkap'),
            DB::raw('COALESCE(master_ereg.gmail, users.email) as email'),
            'master_users.promo_code_used as kode_promo',
            'promo_codes.description as deskripsi_promo',
            'promo_codes.discount as diskon_persen',
            'program_studi.nama_prodi',
            'program_studi.jenjang',
            'master_ereg.jalur_pendaftaran',
            'master_users.va_status as status_bayar',
            'master_users.original_amount as tagihan_asli',
            'master_users.discount_amount as potongan_diskon',
            'master_users.va_amount as total_bayar',
            'master_users.paid_at as tanggal_bayar'
        )
        ->orderBy('master_users.promo_code_used', 'asc')
        ->orderBy('master_users.created_at', 'desc')
        ->get();

    // 3. Mapping baris target di Sheet 'Daftar Peserta' untuk Hyperlink
    $promoRowMap = [];
    $currentRow = 2; // Data baris pertama pada sheet peserta dimulai di baris ke-2
    foreach ($detailUsers as $u) {
        $code = (string) $u->kode_promo;
        if (!isset($promoRowMap[$code])) {
            $promoRowMap[$code] = $currentRow;
        }
        $currentRow++;
    }

    // Ubah ekstensi menjadi .xls agar kompatibel langsung dengan format XML Spreadsheet
    $filename = 'Laporan_Kode_Promo_' . date('Y-m-d_His') . '.xls';

    $headers = [
        "Content-Type"        => "application/vnd.ms-excel; charset=UTF-8",
        "Content-Disposition" => "attachment; filename=\"{$filename}\"",
        "Pragma"              => "no-cache",
        "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
        "Expires"             => "0"
    ];

    $callback = function () use ($promos, $grandTotalPakai, $grandTotalPaid, $grandTotalUnpaid, $detailUsers, $promoRowMap) {
        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        $out = fopen('php://output', 'w');

        fwrite($out, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
        fwrite($out, "<?mso-application progid=\"Excel.Sheet\"?>\n");
        fwrite($out, '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
            xmlns:o="urn:schemas-microsoft-com:office:office"
            xmlns:x="urn:schemas-microsoft-com:office:excel"
            xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
            xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n");

        fwrite($out, '<Styles>
            <Style ss:ID="Header">
                <Font ss:Bold="1" ss:Color="#FFFFFF"/>
                <Interior ss:Color="#2563EB" ss:Pattern="Solid"/>
                <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
            </Style>
            <Style ss:ID="HeaderDark">
                <Font ss:Bold="1" ss:Color="#FFFFFF"/>
                <Interior ss:Color="#1E293B" ss:Pattern="Solid"/>
                <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
            </Style>
            <Style ss:ID="Total">
                <Font ss:Bold="1" ss:Color="#000000"/>
                <Interior ss:Color="#E2E8F0" ss:Pattern="Solid"/>
            </Style>
            <Style ss:ID="Link">
                <Font ss:Color="#2563EB" ss:Underline="Single"/>
            </Style>
            <Style ss:ID="Center">
                <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
            </Style>
        </Styles>' . "\n");

        // SHEET 1: RINGKASAN PROMO
        fwrite($out, '<Worksheet ss:Name="Ringkasan Promo">' . "\n");
        fwrite($out, '<Table>' . "\n");
        fwrite($out, '<Column ss:Width="110"/>' . "\n");
        fwrite($out, '<Column ss:Width="250"/>' . "\n");
        fwrite($out, '<Column ss:Width="80"/>' . "\n");
        fwrite($out, '<Column ss:Width="90"/>' . "\n");
        fwrite($out, '<Column ss:Width="100"/>' . "\n");
        fwrite($out, '<Column ss:Width="100"/>' . "\n");
        fwrite($out, '<Column ss:Width="100"/>' . "\n");
        fwrite($out, '<Column ss:Width="90"/>' . "\n");
        fwrite($out, '<Column ss:Width="110"/>' . "\n");

        fwrite($out, '<Row ss:StyleID="Header">' . "\n");
        fwrite($out, '<Cell><Data ss:Type="String">Kode Promo</Data></Cell>' . "\n");
        fwrite($out, '<Cell><Data ss:Type="String">Asal Sekolah (Klik Buka Peserta)</Data></Cell>' . "\n");
        fwrite($out, '<Cell><Data ss:Type="String">Diskon (%)</Data></Cell>' . "\n");
        fwrite($out, '<Cell><Data ss:Type="String">Status</Data></Cell>' . "\n");
        fwrite($out, '<Cell><Data ss:Type="String">Total Pakai</Data></Cell>' . "\n");
        fwrite($out, '<Cell><Data ss:Type="String">Sudah Bayar</Data></Cell>' . "\n");
        fwrite($out, '<Cell><Data ss:Type="String">Belum Bayar</Data></Cell>' . "\n");
        fwrite($out, '<Cell><Data ss:Type="String">Kuota</Data></Cell>' . "\n");
        fwrite($out, '<Cell><Data ss:Type="String">Kadaluarsa</Data></Cell>' . "\n");
        fwrite($out, '</Row>' . "\n");

        foreach ($promos as $promo) {
            $desc = htmlspecialchars($promo->description ?? '-', ENT_QUOTES, 'UTF-8');
            
            fwrite($out, '<Row>' . "\n");
            fwrite($out, '<Cell><Data ss:Type="String">' . $promo->code . '</Data></Cell>' . "\n");

            if (isset($promoRowMap[$promo->code])) {
                $targetRow = $promoRowMap[$promo->code];
                $targetHref = "#'Daftar Peserta'!B" . $targetRow;
                fwrite($out, '<Cell ss:StyleID="Link" ss:HRef="' . $targetHref . '"><Data ss:Type="String">' . $desc . ' 🔗</Data></Cell>' . "\n");
            } else {
                fwrite($out, '<Cell><Data ss:Type="String">' . $desc . '</Data></Cell>' . "\n");
            }

            fwrite($out, '<Cell ss:StyleID="Center"><Data ss:Type="String">' . $promo->discount . '%</Data></Cell>' . "\n");
            fwrite($out, '<Cell ss:StyleID="Center"><Data ss:Type="String">' . ($promo->is_active ? 'Aktif' : 'Nonaktif') . '</Data></Cell>' . "\n");
            fwrite($out, '<Cell ss:StyleID="Center"><Data ss:Type="Number">' . $promo->total_count . '</Data></Cell>' . "\n");
            fwrite($out, '<Cell ss:StyleID="Center"><Data ss:Type="Number">' . $promo->paid_count . '</Data></Cell>' . "\n");
            fwrite($out, '<Cell ss:StyleID="Center"><Data ss:Type="Number">' . $promo->unpaid_count . '</Data></Cell>' . "\n");
            fwrite($out, '<Cell ss:StyleID="Center"><Data ss:Type="String">' . ($promo->usage_limit ?: 'Unlimited') . '</Data></Cell>' . "\n");
            fwrite($out, '<Cell ss:StyleID="Center"><Data ss:Type="String">' . ($promo->expired_at ? Carbon::parse($promo->expired_at)->format('d-m-Y') : '-') . '</Data></Cell>' . "\n");
            fwrite($out, '</Row>' . "\n");
        }

        fwrite($out, '<Row ss:StyleID="Total">' . "\n");
        fwrite($out, '<Cell><Data ss:Type="String">TOTAL AKUMULASI</Data></Cell>' . "\n");
        fwrite($out, '<Cell><Data ss:Type="String"></Data></Cell>' . "\n");
        fwrite($out, '<Cell><Data ss:Type="String"></Data></Cell>' . "\n");
        fwrite($out, '<Cell><Data ss:Type="String"></Data></Cell>' . "\n");
        fwrite($out, '<Cell ss:StyleID="Center"><Data ss:Type="Number">' . $grandTotalPakai . '</Data></Cell>' . "\n");
        fwrite($out, '<Cell ss:StyleID="Center"><Data ss:Type="Number">' . $grandTotalPaid . '</Data></Cell>' . "\n");
        fwrite($out, '<Cell ss:StyleID="Center"><Data ss:Type="Number">' . $grandTotalUnpaid . '</Data></Cell>' . "\n");
        fwrite($out, '<Cell><Data ss:Type="String"></Data></Cell>' . "\n");
        fwrite($out, '<Cell><Data ss:Type="String"></Data></Cell>' . "\n");
        fwrite($out, '</Row>' . "\n");

        fwrite($out, '</Table>' . "\n");
        fwrite($out, '</Worksheet>' . "\n");

        // SHEET 2: DAFTAR PESERTA
        fwrite($out, '<Worksheet ss:Name="Daftar Peserta">' . "\n");
        fwrite($out, '<Table>' . "\n");
        fwrite($out, '<Column ss:Width="40"/>' . "\n");
        fwrite($out, '<Column ss:Width="180"/>' . "\n");
        fwrite($out, '<Column ss:Width="180"/>' . "\n");
        fwrite($out, '<Column ss:Width="100"/>' . "\n");
        fwrite($out, '<Column ss:Width="200"/>' . "\n");
        fwrite($out, '<Column ss:Width="70"/>' . "\n");
        fwrite($out, '<Column ss:Width="150"/>' . "\n");
        fwrite($out, '<Column ss:Width="60"/>' . "\n");
        fwrite($out, '<Column ss:Width="120"/>' . "\n");
        fwrite($out, '<Column ss:Width="90"/>' . "\n");
        fwrite($out, '<Column ss:Width="100"/>' . "\n");
        fwrite($out, '<Column ss:Width="100"/>' . "\n");
        fwrite($out, '<Column ss:Width="100"/>' . "\n");
        fwrite($out, '<Column ss:Width="120"/>' . "\n");

        fwrite($out, '<Row ss:StyleID="HeaderDark">' . "\n");
        fwrite($out, '<Cell><Data ss:Type="String">No</Data></Cell>' . "\n");
        fwrite($out, '<Cell><Data ss:Type="String">Nama Mahasiswa</Data></Cell>' . "\n");
        fwrite($out, '<Cell><Data ss:Type="String">Email</Data></Cell>' . "\n");
        fwrite($out, '<Cell><Data ss:Type="String">Kode Promo</Data></Cell>' . "\n");
        fwrite($out, '<Cell><Data ss:Type="String">Asal Sekolah</Data></Cell>' . "\n");
        fwrite($out, '<Cell><Data ss:Type="String">Diskon</Data></Cell>' . "\n");
        fwrite($out, '<Cell><Data ss:Type="String">Program Studi</Data></Cell>' . "\n");
        fwrite($out, '<Cell><Data ss:Type="String">Jenjang</Data></Cell>' . "\n");
        fwrite($out, '<Cell><Data ss:Type="String">Jalur Pendaftaran</Data></Cell>' . "\n");
        fwrite($out, '<Cell><Data ss:Type="String">Status Bayar</Data></Cell>' . "\n");
        fwrite($out, '<Cell><Data ss:Type="String">Tagihan Asli</Data></Cell>' . "\n");
        fwrite($out, '<Cell><Data ss:Type="String">Diskon</Data></Cell>' . "\n");
        fwrite($out, '<Cell><Data ss:Type="String">Total Bayar</Data></Cell>' . "\n");
        fwrite($out, '<Cell><Data ss:Type="String">Tanggal Bayar</Data></Cell>' . "\n");
        fwrite($out, '</Row>' . "\n");

        $no = 1;
        foreach ($detailUsers as $u) {
            fwrite($out, '<Row>' . "\n");
            fwrite($out, '<Cell ss:StyleID="Center"><Data ss:Type="Number">' . $no++ . '</Data></Cell>' . "\n");
            fwrite($out, '<Cell><Data ss:Type="String">' . htmlspecialchars($u->nama_lengkap ?? '-', ENT_QUOTES, 'UTF-8') . '</Data></Cell>' . "\n");
            fwrite($out, '<Cell><Data ss:Type="String">' . htmlspecialchars($u->email ?? '-', ENT_QUOTES, 'UTF-8') . '</Data></Cell>' . "\n");
            fwrite($out, '<Cell ss:StyleID="Center"><Data ss:Type="String">' . $u->kode_promo . '</Data></Cell>' . "\n");
            fwrite($out, '<Cell><Data ss:Type="String">' . htmlspecialchars($u->deskripsi_promo ?? '-', ENT_QUOTES, 'UTF-8') . '</Data></Cell>' . "\n");
            fwrite($out, '<Cell ss:StyleID="Center"><Data ss:Type="String">' . ($u->diskon_persen ?? 0) . '%</Data></Cell>' . "\n");
            fwrite($out, '<Cell><Data ss:Type="String">' . htmlspecialchars($u->nama_prodi ?? '-', ENT_QUOTES, 'UTF-8') . '</Data></Cell>' . "\n");
            fwrite($out, '<Cell ss:StyleID="Center"><Data ss:Type="String">' . ($u->jenjang ?? '-') . '</Data></Cell>' . "\n");
            fwrite($out, '<Cell><Data ss:Type="String">' . htmlspecialchars($u->jalur_pendaftaran ?? '-', ENT_QUOTES, 'UTF-8') . '</Data></Cell>' . "\n");
            fwrite($out, '<Cell ss:StyleID="Center"><Data ss:Type="String">' . strtoupper($u->status_bayar ?? 'UNPAID') . '</Data></Cell>' . "\n");
            fwrite($out, '<Cell><Data ss:Type="Number">' . (float)($u->tagihan_asli ?? 0) . '</Data></Cell>' . "\n");
            fwrite($out, '<Cell><Data ss:Type="Number">' . (float)($u->potongan_diskon ?? 0) . '</Data></Cell>' . "\n");
            fwrite($out, '<Cell><Data ss:Type="Number">' . (float)($u->total_bayar ?? 0) . '</Data></Cell>' . "\n");
            fwrite($out, '<Cell ss:StyleID="Center"><Data ss:Type="String">' . ($u->tanggal_bayar ? Carbon::parse($u->tanggal_bayar)->format('d-m-Y H:i') : '-') . '</Data></Cell>' . "\n");
            fwrite($out, '</Row>' . "\n");
        }

        fwrite($out, '</Table>' . "\n");
        fwrite($out, '</Worksheet>' . "\n");

        fwrite($out, '</Workbook>' . "\n");
        fclose($out);
    };

    return response()->stream($callback, 200, $headers);
}

    public function store(Request $request)
    {
        $request->validate([
            'description' => 'nullable|string|max:255',
            'discount' => 'required|integer|min:1|max:100',
            'expired_at' => 'nullable|date|after:today',
            'usage_limit' => 'nullable|integer|min:1',
        ]);

        try {
            $promoCode = PromoCode::create([
                'code' => PromoCode::generateUniqueCode(),
                'description' => $request->description,
                'discount' => $request->discount,
                'expired_at' => $request->expired_at ? Carbon::parse($request->expired_at) : null,
                'usage_limit' => $request->usage_limit,
                'is_active' => true,
                'used_count' => 0,
            ]);

            return redirect()
                ->route('admin.promocode.index')
                ->with('success', 'Kode promo ' . $promoCode->code . ' berhasil dibuat!');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal membuat kode promo: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $promoCode = PromoCode::findOrFail($id);
            $code = $promoCode->code;
            $promoCode->delete();

            return redirect()
                ->route('admin.promocode.index')
                ->with('success', 'Kode promo ' . $code . ' berhasil dihapus.');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus kode promo: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        try {
            $promoCode = PromoCode::findOrFail($id);
            $promoCode->update([
                'is_active' => !$promoCode->is_active
            ]);

            $status = $promoCode->is_active ? 'diaktifkan' : 'dinonaktifkan';

            return redirect()
                ->back()
                ->with('success', 'Kode promo ' . $promoCode->code . ' berhasil ' . $status);

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal mengubah status: ' . $e->getMessage());
        }
    }

    public function validateCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string'
        ]);

        $promoCode = PromoCode::where('code', $request->code)->first();

        if (!$promoCode) {
            return response()->json([
                'valid' => false,
                'message' => 'Kode promo tidak ditemukan'
            ], 404);
        }

        if (!$promoCode->isValid()) {
            return response()->json([
                'valid' => false,
                'message' => 'Kode promo tidak valid atau sudah kadaluarsa'
            ], 400);
        }

        return response()->json([
            'valid' => true,
            'promo_code' => [
                'id' => $promoCode->id,
                'code' => $promoCode->code,
                'discount' => $promoCode->discount,
                'description' => $promoCode->description
            ]
        ]);
    }
}