<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BerkasProfesi;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use ZipArchive;
use Illuminate\Support\Facades\Storage;
use App\Exports\KelulusanExport;
use Maatwebsite\Excel\Facades\Excel;

class AdminProfesiController extends Controller
{
    // =========================================================================
    // DASHBOARD UTAMA — Daftar Pendaftar Apoteker (Disinkronkan dengan tabel Gelombang)
    // =========================================================================
    // =========================================================================
    // DASHBOARD UTAMA — Daftar Pendaftar Apoteker
    // =========================================================================
    public function index(Request $request)
    {
        // 1. Query dasar - Mengambil user mahasiswa baru jalur apoteker dengan relasi masterUser
        $query = User::with('masterUser')
                     ->where('role', 'mahasiswa_baru')
                     ->where('profesi', 'Apoteker Farmasi')
                     ->orderBy('created_at', 'desc');

        // NEW: Filter Pencarian Nama
        $searchName = $request->input('search_name');
        if (!empty($searchName)) {
            $query->where('name', 'like', '%' . $searchName . '%');
        }

        // 2. Filter alumni / umum
        $filterAlumni = $request->input('filter_alumni');
        if ($filterAlumni === 'alumni') {
            $query->whereNotNull('nim');
        } elseif ($filterAlumni === 'non_alumni') {
            $query->whereNull('nim');
        }

        // 3. Filter Gelombang Aktif (Mendukung ID 1 & Penyelamat Data ID 5)
        $filterGelombang = $request->input('filter_gelombang');
        if ($filterGelombang !== null && $filterGelombang !== '') {
            $query->whereHas('masterUser', function($q) use ($filterGelombang) {
                if ($filterGelombang == 1) {
                    $q->whereIn('gelombang', [1, 5, '1', '5'])->orWhere('gelombang', 'Gelombang 1');
                } else {
                    $q->where('gelombang', $filterGelombang)->orWhere('gelombang', 'Gelombang ' . $filterGelombang);
                }
            });
        }

        // 4. Per-page
        $perPage = $request->input('per_page', 10);
        $pendaftarApoteker = ($perPage === 'all')
            ? $query->paginate(999999)
            : $query->paginate((int) $perPage);

        // 5. Statistik header (Ikut terpengaruh pencarian & gelombang agar akurat)
        $baseQuery = User::where('role', 'mahasiswa_baru')
                         ->where('profesi', 'Apoteker Farmasi');
        
        if (!empty($searchName)) {
            $baseQuery->where('name', 'like', '%' . $searchName . '%');
        }
                         
        if ($filterGelombang !== null && $filterGelombang !== '') {
            $baseQuery->whereHas('masterUser', function($q) use ($filterGelombang) {
                if ($filterGelombang == 1) {
                    $q->whereIn('gelombang', [1, 5, '1', '5'])->orWhere('gelombang', 'Gelombang 1');
                } else {
                    $q->where('gelombang', $filterGelombang)->orWhere('gelombang', 'Gelombang ' . $filterGelombang);
                }
            });
        }

        $totalPendaftar = $baseQuery->count();
        $totalLunas = $baseQuery->whereHas('masterUser', fn($q) => $q->where('va_status', 'paid'))->count();
        $totalPending = $totalPendaftar - $totalLunas;

        // 6. Ambil berkas
        $userIds     = $pendaftarApoteker->pluck('id');
        $semuaBerkas = DB::table('berkas_profesi')->whereIn('user_id', $userIds)->get()->keyBy('user_id');

        // 7. Ambil opsi gelombang
        $listGelombang = DB::table('gelombang')->orderBy('id', 'asc')->get();

        return view('admin.profesi.dashboard', compact(
            'pendaftarApoteker', 'totalPendaftar', 'totalLunas', 'totalPending',
            'filterAlumni', 'filterGelombang', 'listGelombang', 'perPage', 'semuaBerkas', 'searchName'
        ));
    }

    // =========================================================================
    // HALAMAN NILAI CBT
    // =========================================================================
    public function cbtIndex(Request $request)
    {
        $query = User::with('masterUser')
                     ->where('role', 'mahasiswa_baru')
                     ->where('profesi', 'Apoteker Farmasi')
                     ->whereHas('masterUser', fn($q) => $q->where('va_status', 'paid'))
                     ->orderBy('name', 'asc');

        // NEW: Filter Pencarian Nama di CBT
        $searchName = $request->input('search_name');
        if (!empty($searchName)) {
            $query->where('name', 'like', '%' . $searchName . '%');
        }

        // Filter gelombang
        $filterGelombang = $request->input('filter_gelombang');
        if ($filterGelombang !== null && $filterGelombang !== '') {
            $query->whereHas('masterUser', function($q) use ($filterGelombang) {
                if ($filterGelombang == 1) {
                    $q->whereIn('gelombang', [1, 5, '1', '5'])->orWhere('gelombang', 'Gelombang 1');
                } else {
                    $q->where('gelombang', $filterGelombang)->orWhere('gelombang', 'Gelombang ' . $filterGelombang);
                }
            });
        }

        $perPage      = $request->input('per_page', 15);
        $pendaftarCbt = ($perPage === 'all')
            ? $query->paginate(999999)
            : $query->paginate((int) $perPage);

        $listGelombang = DB::table('gelombang')->orderBy('id', 'asc')->get();

        return view('admin.profesi.cbt', compact('pendaftarCbt', 'perPage', 'listGelombang', 'filterGelombang', 'searchName'));
    }
// =========================================================================
    // BATALKAN STATUS KELULUSAN CBT & HAPUS NILAI
    // =========================================================================
    public function cbtBatal($id)
    {
        $user = User::findOrFail($id);

        $user->update([
            'nilai_cbt'  => null, // Menghapus nilai menjadi kosong kembali
            'status_cbt' => null, // Mengembalikan status ujian menjadi kosong/belum diuji
        ]);

        return redirect()->back()->with(
            'success',
            "Status ujian dan nilai CBT untuk {$user->name} berhasil dibatalkan dan dikosongkan."
        );
    }
    public function downloadSemua(Request $request)
    {
        $zip = new \ZipArchive();
        $zipFileName = 'Data_Lengkap_Apoteker_' . date('Y-m-d_H-i') . '.zip';
        $zipPath = tempnam(sys_get_temp_dir(), 'zip');

        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return back()->with('error', 'Gagal membuat file ZIP.');
        }

        // 1. Generate & Tambahkan Excel
        $excelContent = $this->generateExcelContent($request);
        $zip->addFromString('Data_Pendaftar.xlsx', $excelContent);

        // 2. Ambil data berkas dengan relasi user
        $dataBerkas = \App\Models\BerkasProfesi::with('user')->get();

        foreach ($dataBerkas as $berkas) {
            $namaUser = $berkas->user ? str_replace([' ', '/', '\\'], '_', $berkas->user->name) : 'User_' . $berkas->user_id;
            $folderUser = $namaUser . '_' . $berkas->user_id;

            $kolomFile = [
                'file_akreditasi' => 'Akreditasi',
                'file_ijazah'     => 'Ijazah',
                'file_transkrip'  => 'Transkrip',
                'file_pasfoto'    => 'Pasfoto',
                'file_ktpa'       => 'KTP',
                'file_kk'         => 'KK',
                'file_asuransi'   => 'Asuransi',
                'file_surat_sehat'=> 'Surat_Sehat'
            ];

            foreach ($kolomFile as $dbColumn => $prefix) {
                $pathFile = $berkas->$dbColumn;
                if ($pathFile && \Illuminate\Support\Facades\Storage::disk('public')->exists($pathFile)) {
                    $ext = pathinfo($pathFile, PATHINFO_EXTENSION);
                    $zip->addFile(storage_path('app/public/' . $pathFile), $folderUser . '/' . $prefix . '_' . $namaUser . '.' . $ext);
                }
            }
        }

        $zip->close();
        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }

    private function generateExcelContent(Request $request) 
    {
        $query = User::with(['masterUser'])
                     ->where('role', 'mahasiswa_baru')
                     ->where('profesi', 'Apoteker Farmasi');

        if ($request->filter_alumni === 'alumni') $query->whereNotNull('nim');
        elseif ($request->filter_alumni === 'non_alumni') $query->whereNull('nim');

        // PERBAIKAN: Filter gelombang di excel zip generator
        if ($request->filled('filter_gelombang')) {
            $filterGelombang = $request->filter_gelombang;
            $query->whereHas('masterUser', function($q) use ($filterGelombang) {
                if ($filterGelombang == 1) {
                    $q->whereIn('gelombang', [1, 5, '1', '5'])->orWhere('gelombang', 'Gelombang 1');
                } else {
                    $q->where('gelombang', $filterGelombang);
                }
            });
        }

        $pendaftar = $query->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $headers = [
            'No', 'Nama Lengkap', 'Email', 'No. HP', 'Status Alumni', 
            'NIM', 'Status Pembayaran', 'Status CBT', 'Status Wawancara', 'Status Verifikasi Berkas'
        ];
        
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }

        $row = 2;
        foreach ($pendaftar as $index => $user) {
            $berkasRow = DB::table('berkas_profesi')->where('user_id', $user->id)->first();
            $statusVerif = ($berkasRow && $berkasRow->status_verifikasi === 'lulus') 
                           ? 'Terverifikasi' 
                           : 'Belum Diverifikasi';

            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $user->name);
            $sheet->setCellValue('C' . $row, $user->email);
            $sheet->setCellValue('D' . $row, $user->phone ?? '-');
            $sheet->setCellValue('E' . $row, $user->nim ? 'Alumni' : 'Umum');
            $sheet->setCellValue('F' . $row, $user->nim ?? '-');
            $sheet->setCellValue('G' . $row, $user->masterUser->va_status ?? 'Pending');
            $sheet->setCellValue('H' . $row, $user->status_cbt ?? 'Belum Ujian');
            $sheet->setCellValue('I' . $row, $user->status_wawancara ?? 'Belum Diuji');
            $sheet->setCellValue('J' . $row, $statusVerif);
            $row++;
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tempExcel = tempnam(sys_get_temp_dir(), 'excel');
        $writer->save($tempExcel);
        
        $content = file_get_contents($tempExcel);
        unlink($tempExcel);
        
        return $content;
    }

    // =========================================================================
    // EXPORT XLSX MAIN DASHBOARD
    // =========================================================================
    public function exportExcel(Request $request)
    {
        $query = User::with('masterUser')
                     ->where('role', 'mahasiswa_baru')
                     ->where('profesi', 'Apoteker Farmasi')
                     ->orderBy('created_at', 'desc');

        $filterAlumni = $request->input('filter_alumni');
        if ($filterAlumni === 'alumni') {
            $query->whereNotNull('nim');
        } elseif ($filterAlumni === 'non_alumni') {
            $query->whereNull('nim');
        }

        // PERBAIKAN: Filter gelombang di export xlsx utama
        if ($request->filled('filter_gelombang')) {
            $filterGelombang = $request->filter_gelombang;
            $query->whereHas('masterUser', function($q) use ($filterGelombang) {
                if ($filterGelombang == 1) {
                    $q->whereIn('gelombang', [1, 5, '1', '5'])->orWhere('gelombang', 'Gelombang 1');
                } else {
                    $q->where('gelombang', $filterGelombang);
                }
            });
        }

        $pendaftar = $query->get();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Pendaftar Apoteker');

        $headers = [
            'No', 'Nama Lengkap', 'Email', 'No. Handphone (WA)',
            'Status Alumni', 'NIM (Jika Alumni)', 'Nomor Virtual Account',
            'Status Pembayaran', 'Nilai CBT', 'Status Ujian',
            'Status Verifikasi Berkas', 'Tanggal Mendaftar',
        ];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }

        $sheet->getStyle('A1:L1')->getFont()->setBold(true);
        $sheet->getStyle('A1:L1')->getFill()
              ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
              ->getStartColor()->setARGB('FFF0F0F0');

        $row = 2;
        foreach ($pendaftar as $i => $user) {
            $berkasRow    = DB::table('berkas_profesi')->where('user_id', $user->id)->first();
            
            $statusVerif = ($berkasRow && $berkasRow->status_verifikasi === 'lulus') 
                           ? 'Terverifikasi' 
                           : 'Belum Diverifikasi';

            $sheet->setCellValue('A' . $row, $i + 1);
            $sheet->setCellValue('B' . $row, $user->name);
            $sheet->setCellValue('C' . $row, $user->email);
            $sheet->setCellValueExplicit('D' . $row, $user->phone ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('E' . $row, $user->nim ? 'Alumni UBP' : 'Umum / Non-Alumni');
            $sheet->setCellValue('F' . $row, $user->nim ?? '-');
            $sheet->setCellValueExplicit('G' . $row, $user->masterUser?->va_number ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('H' . $row, $user->masterUser ? ucfirst($user->masterUser->va_status) : 'Pending');
            $sheet->setCellValue('I' . $row, $user->nilai_cbt ?? 'Belum Ujian');
            $sheet->setCellValue('J' . $row, $user->status_cbt ?? '-');
            $sheet->setCellValue('K' . $row, $statusVerif); 
            $sheet->setCellValue('L' . $row, \Carbon\Carbon::parse($user->created_at)->format('d-m-Y H:i:s'));
            $row++;
        }

        $writer   = new Xlsx($spreadsheet);
        $fileName = 'Data_Pendaftar_Apoteker_' . date('Y-m-d_H-i') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // =========================================================================
    // HAPUS USER
    // =========================================================================
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        DB::beginTransaction();
        try {
            $email = $user->email;

            DB::table('master_users')->where('user_id', $user->id)->delete();
            DB::table('master_ereg')->where('user_id', $user->id)->delete();
            DB::table('berkas_profesi')->where('user_id', $user->id)->delete();

            $defaultTable = DB::getSchemaBuilder()->hasTable('akun_pendaftar_defaults')
                ? 'akun_pendaftar_defaults'
                : 'akun_pendaftar_default';
            DB::table($defaultTable)->where('email', $email)->delete();

            $user->delete();
            DB::commit();

            return redirect()->back()->with(
                'success',
                "Akun pendaftar {$user->name} beserta seluruh data terkait berhasil dihapus."
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal menghapus user ID {$id}: " . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem. Gagal menghapus data.');
        }
    }

    // =========================================================================
    // UPDATE NILAI / STATUS CBT
    // =========================================================================
    public function cbtUpdate(Request $request, $id)
    {
        $request->validate([
            'status_cbt' => ['required', 'in:Lulus,Tidak Lulus'],
        ]);

        $user   = User::findOrFail($id);
        $status = $request->input('status_cbt');
        
        if (!empty($user->nilai_cbt) && is_numeric($user->nilai_cbt)) {
            $nilai = $user->nilai_cbt;
        } else {
            $nilai = ($status === 'Lulus') ? 80 : 50;
        }

        $user->update([
            'nilai_cbt'  => $nilai,
            'status_cbt' => $status,
        ]);

        return redirect()->back()->with(
            'success',
            "Status kelulusan {$user->name} diperbarui menjadi {$status} dengan nilai CBT: {$nilai}"
        );
    }

    // =========================================================================
    // TAMPILKAN BERKAS
    // =========================================================================
    public function showBerkas($id)
    {
        $user   = User::findOrFail($id);
        $berkas = DB::table('berkas_profesi')->where('user_id', $id)->first();

        return view('admin.profesi.berkas', compact('user', 'berkas'));
    }

    public function batalVerifikasi(Request $request, $id)
    {
        $user = User::findOrFail($id);
    
        DB::table('berkas_profesi')
            ->where('user_id', $id)
            ->update([
                'status_verifikasi' => 'belum',
                'updated_at'        => now(),
            ]);
    
        return redirect()->back()->with(
            'success',
            "Status verifikasi {$user->name} telah dibatalkan (dikembalikan ke 'belum')."
        );
    }

    // =========================================================================
    // EXPORT KELULUSAN CBT & WAWANCARA
    // =========================================================================
    public function exportKelulusan(Request $request)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Kelulusan Apoteker');

        $headers = ['No', 'Nama Lengkap', 'Email', 'No. HP', 'Status Berkas', 'Status CBT', 'Status Wawancara'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }

        $query = User::where('role', 'mahasiswa_baru')
                     ->where('profesi', 'Apoteker Farmasi');

        // PERBAIKAN: Filter gelombang di export kelulusan
        if ($request->filled('filter_gelombang')) {
            $filterGelombang = $request->filter_gelombang;
            $query->whereHas('masterUser', function($q) use ($filterGelombang) {
                if ($filterGelombang == 1) {
                    $q->whereIn('gelombang', [1, 5, '1', '5'])->orWhere('gelombang', 'Gelombang 1');
                } else {
                    $q->where('gelombang', $filterGelombang);
                }
            });
        }

        $pendaftar = $query->get();

        $row = 2;
        foreach ($pendaftar as $i => $user) {
            $berkas = DB::table('berkas_profesi')->where('user_id', $user->id)->first();
            $statusVerifikasi = $berkas ? $berkas->status_verifikasi : 'Belum Ada';

            $sheet->setCellValue('A' . $row, $i + 1);
            $sheet->setCellValue('B' . $row, $user->name);
            $sheet->setCellValue('C' . $row, $user->email);
            $sheet->setCellValue('D' . $row, $user->phone);
            $sheet->setCellValue('E' . $row, ucfirst($statusVerifikasi)); 
            $sheet->setCellValue('F' . $row, $user->status_cbt ?? 'Belum Diuji');
            $sheet->setCellValue('G' . $row, $user->status_wawancara ?? 'Belum Diuji');
            $row++;
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'Data_Kelulusan_Apoteker_' . date('Y-m-d_H-i') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function wawancaraUpdate(Request $request, $id)
    {
        $request->validate([
            'status_wawancara' => ['required', 'in:Lulus,Tidak Lulus'],
        ]);
    
        $user = User::findOrFail($id);
        $user->update([
            'status_wawancara' => $request->status_wawancara
        ]);
    
        return redirect()->back()->with('success', 'Status Wawancara ' . $user->name . ' berhasil diupdate.');
    }

    public function verifikasiBerkas(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $exists = DB::table('berkas_profesi')->where('user_id', $id)->exists();

        if (! $exists) {
            return redirect()->back()->with('error', "Tidak ditemukan berkas untuk pendaftar {$user->name}.");
        }

        DB::table('berkas_profesi')
            ->where('user_id', $id)
            ->update([
                'status_verifikasi' => 'lulus',
                'updated_at'        => now(),
            ]);

        return redirect()->back()->with('success', "Berkas pendaftaran atas nama {$user->name} berhasil diverifikasi!");
    }

    public function settingGelombang()
    {
        $gelombang = DB::table('gelombang')->orderBy('id', 'asc')->get();
        return view('admin.profesi.setting-gelombang', compact('gelombang'));
    }
    
    public function updateJadwalGelombang(Request $request)
    {
        $request->validate([
            'gelombang.*.tanggal_mulai' => 'required|date',
            'gelombang.*.tanggal_selesai' => 'required|date|after_or_equal:gelombang.*.tanggal_mulai',
        ]);
    
        foreach ($request->gelombang as $id => $data) {
            DB::table('gelombang')->where('id', $id)->update([
                'tanggal_mulai' => $data['tanggal_mulai'],
                'tanggal_selesai' => $data['tanggal_selesai'],
                'updated_at' => now()
            ]);
        }
    
        return redirect()->back()->with('success', 'Jadwal semua gelombang berhasil diperbarui.');
    }
}