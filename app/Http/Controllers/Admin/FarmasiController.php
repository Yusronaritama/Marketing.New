<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class FarmasiController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $filterStatus = $request->get('status');

        $query = DB::table('master_moodle_lulus')
            ->where('prodi_lulus', '48201')
            ->where('pilihan_lulus', 1);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_lengkap', 'LIKE', "%$search%")
                  ->orWhere('email', 'LIKE', "%$search%");
            });
        }

        if ($filterStatus) {
            if ($filterStatus === 'unprocessed') {
                $processedEmails = DB::table('farmasi_verifications')->pluck('email')->toArray();
                $query->whereNotIn('email', $processedEmails);
            } else {
                $query->whereExists(function($q) use ($filterStatus) {
                    $q->select(DB::raw(1))
                      ->from('farmasi_verifications')
                      ->whereColumn('farmasi_verifications.email', 'master_moodle_lulus.email')
                      ->where('status_verifikasi', $filterStatus);
                });
            }
        }

        $data = $query->get();

        $allEmails = DB::table('master_moodle_lulus')
            ->where('prodi_lulus', '48201')
            ->where('pilihan_lulus', 1)
            ->pluck('email')
            ->toArray();

        $stats = [
            'total'    => count($allEmails),
            'verified' => DB::table('farmasi_verifications')->whereIn('email', $allEmails)->where('status_verifikasi', 'verified')->count(),
            'rejected' => DB::table('farmasi_verifications')->whereIn('email', $allEmails)->where('status_verifikasi', 'rejected')->count(),
            'pending'  => DB::table('farmasi_verifications')->whereIn('email', $allEmails)->where('status_verifikasi', 'pending')->count(),
        ];
        $stats['unprocessed'] = $stats['total'] - ($stats['verified'] + $stats['rejected'] + $stats['pending']);

        return view('admin.farmasi.index', compact('data', 'stats', 'search', 'filterStatus'));
    }

    public function validateStep(Request $request, $id)
    {
        $moodleLulus = DB::table('master_moodle_lulus')->where('id', $id)->first();
        if (!$moodleLulus) {
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        $user = DB::table('users')->where('email', $moodleLulus->email)->first();
        if (!$user) {
            return redirect()->back()->with('error', 'User tidak terdaftar di tabel users.');
        }

        $masterEreg = DB::table('master_ereg')->where('user_id', $user->id)->first();
        if (!$masterEreg) {
            return redirect()->back()->with('error', 'Data master_ereg tidak ditemukan.');
        }

        DB::table('farmasi_verifications')->updateOrInsert(
            [
                'user_id'        => $user->id,
                'master_ereg_id' => $masterEreg->id,
            ],
            [
                'nama_lengkap'       => $moodleLulus->nama_lengkap,
                'email'              => $moodleLulus->email,
                'status_verifikasi'  => $request->status_verifikasi,
                'verified_by'        => Auth::id(),
                'nama_verifikator'   => Auth::user()->name,
                'tanggal_verifikasi' => now(),
                'keterangan'         => $request->keterangan ?? 'Validated from Farmasi Menu',
                'updated_at'         => now(),
                'created_at'         => DB::raw('IFNULL(created_at, NOW())'),
            ]
        );

        return redirect()->back()->with('success', 'Status verifikasi ' . $moodleLulus->nama_lengkap . ' berhasil diperbarui.');
    }

public function export(Request $request)
    {
        $search       = $request->get('search');
        $filterStatus = $request->get('status');

        // ── QUERY ─────────────────────────────────────────
        $query = DB::table('master_moodle_lulus')
            ->where('master_moodle_lulus.prodi_lulus', '48201')
            ->where('master_moodle_lulus.pilihan_lulus', 1)
            ->leftJoin('users', 'users.email', '=', 'master_moodle_lulus.email')
            ->leftJoin('master_users', 'master_users.user_id', '=', 'users.id')
            ->select(
                'master_moodle_lulus.*',
                'master_users.gelombang'
            );

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('master_moodle_lulus.nama_lengkap', 'LIKE', "%$search%")
                  ->orWhere('master_moodle_lulus.email', 'LIKE', "%$search%");
            });
        }

        if ($filterStatus) {
            if ($filterStatus === 'unprocessed') {
                $processedEmails = DB::table('farmasi_verifications')->pluck('email')->toArray();
                $query->whereNotIn('master_moodle_lulus.email', $processedEmails);
            } else {
                $query->whereExists(function($q) use ($filterStatus) {
                    $q->select(DB::raw(1))
                      ->from('farmasi_verifications')
                      ->whereColumn('farmasi_verifications.email', 'master_moodle_lulus.email')
                      ->where('status_verifikasi', $filterStatus);
                });
            }
        }

        $data = $query->get();

        // ── SPREADSHEET ───────────────────────────────────
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Verifikasi Farmasi');

        // Update Last Column ke G karena 2 kolom dihapus (I -> G)
        $lastCol   = 'G'; 
        $headerRow = 3;
        $dataStart = 4;

        // ── BARIS 1 & 2: JUDUL ────────────────────────────
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', 'DATA VERIFIKASI MAHASISWA FARMASI');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E4057']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->setCellValue('A2', 'Diekspor pada: ' . now()->translatedFormat('d F Y, H:i') . ' WIB  |  Total Data: ' . count($data));
        
        // ── BARIS 3: HEADER KOLOM ─────────────────────────
        // Kolom diubah: ID -> Username, Moodle ID dihapus, Tanggal Update dihapus
        $headers    = ['No', 'Username', 'Nama Lengkap', 'Email', 'Prodi', 'Gelombang', 'Status Verifikasi'];
        $headerCols = ['A',  'B',        'C',            'D',     'E',     'F',         'G'];

        foreach ($headers as $i => $header) {
            $sheet->setCellValue($headerCols[$i] . $headerRow, $header);
        }

        $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '5B7FFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']],
            ],
        ]);

        // ── BARIS DATA ────────────────────────────────────
        $statusColorMap = [
            'verified'       => ['bg' => 'D4EDDA', 'fg' => '155724', 'label' => 'Sudah Lulus'],
            'pending'        => ['bg' => 'FFF3CD', 'fg' => '856404', 'label' => 'Menunggu'],
            'rejected'       => ['bg' => 'F8D7DA', 'fg' => '721C24', 'label' => 'Tidak Lulus'],
            'belum_diproses' => ['bg' => 'E9ECEF', 'fg' => '495057', 'label' => 'Belum Diproses'],
        ];

        $rowNum = $dataStart;
        $no     = 1;

        foreach ($data as $row) {
            $verif      = DB::table('farmasi_verifications')->where('email', $row->email)->first();
            $statusRaw  = $verif ? $verif->status_verifikasi : 'belum_diproses';
            $statusInfo = $statusColorMap[$statusRaw] ?? $statusColorMap['belum_diproses'];
            $rowBg      = ($rowNum % 2 === 0) ? 'F8FAFC' : 'FFFFFF';

            $sheet->setCellValue("A{$rowNum}", $no++);
            $sheet->setCellValue("B{$rowNum}", $row->username); // Menggunakan username menggantikan ID
            $sheet->setCellValue("C{$rowNum}", strtoupper($row->nama_lengkap));
            $sheet->setCellValue("D{$rowNum}", $row->email);
            $sheet->setCellValue("E{$rowNum}", $row->nama_prodi_lulus ?? 'Farmasi');
            $sheet->setCellValue("F{$rowNum}", $row->gelombang ?? '-');
            $sheet->setCellValue("G{$rowNum}", $statusInfo['label']);

            // Style baris
            $sheet->getStyle("A{$rowNum}:{$lastCol}{$rowNum}")->applyFromArray([
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $rowBg]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);

            // Style Status (Kolom G)
            $sheet->getStyle("G{$rowNum}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => $statusInfo['fg']]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $statusInfo['bg']]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            // Alignment Center untuk No, Username, Gelombang
            foreach (['A', 'B', 'F'] as $centerCol) {
                $sheet->getStyle("{$centerCol}{$rowNum}")
                      ->getAlignment()
                      ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }

            $rowNum++;
        }

        // ── LEBAR KOLOM ───────────────────────────────────
        $colWidths = [
            'A' => 6, 
            'B' => 15, // Username
            'C' => 35, // Nama
            'D' => 35, // Email
            'E' => 20, // Prodi
            'F' => 12, // Gelombang
            'G' => 18  // Status
        ];
        foreach ($colWidths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        $sheet->freezePane("A{$dataStart}");

        // ── OUTPUT ────────────────────────────────────────
        $filename = 'Data_Verifikasi_Farmasi_' . now()->format('Y-m-d') . '.xlsx';
        $writer   = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control'       => 'max-age=0',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}