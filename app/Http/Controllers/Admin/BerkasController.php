<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterEreg;
use App\Models\Biodata;
use App\Models\User;
use App\Models\MasterUser;
use Illuminate\Http\Request;
use ZipArchive;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class BerkasController extends Controller
{
    public function index()
    {
        $pendaftaran = MasterEreg::with(['user', 'biodata'])->orderBy('created_at', 'desc')->get();

        $stats = [
            'total' => $pendaftaran->count(),
            'completed' => $pendaftaran->where('status', 'completed')->count(),
            'in_progress' => $pendaftaran->where('status', 'in_progress')->count(),
            'rejected' => $pendaftaran->where('status', 'rejected')->count(),
        ];

        // Ambil daftar gelombang untuk dropdown
        $gelombangList = MasterUser::whereNotNull('gelombang')
            ->distinct()
            ->pluck('gelombang')
            ->filter()
            ->values();

        return view('admin.berkas.index', compact('pendaftaran', 'stats', 'gelombangList'));
    }

    public function show($id)
    {
        $pendaftaran = MasterEreg::with(['user', 'biodata'])->findOrFail($id);
        
        return view('admin.berkas.show', compact('pendaftaran'));
    }

    public function download($id, $type)
    {
        $pendaftaran = MasterEreg::findOrFail($id);
        
        $filePath = null;
        $fileName = '';

        if ($type === 'kk' && $pendaftaran->file_kk) {
            $filePath = storage_path('app/public/' . $pendaftaran->file_kk);
            $fileName = 'Kartu_Keluarga_' . $pendaftaran->nama . '.pdf';
        } elseif ($type === 'ijazah' && $pendaftaran->file_ijazah) {
            $filePath = storage_path('app/public/' . $pendaftaran->file_ijazah);
            $fileName = 'Ijazah_' . $pendaftaran->nama . '.pdf';
        }

        if ($filePath && file_exists($filePath)) {
            return response()->download($filePath, $fileName);
        }

        return back()->with('error', 'File tidak ditemukan.');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:in_progress,completed,rejected'
        ]);

        $pendaftaran = MasterEreg::findOrFail($id);
        $pendaftaran->update([
            'status' => $request->status
        ]);

        return back()->with('success', 'Status berhasil diperbarui.');
    }

    /**
     * Download semua users
     */
    public function downloadAllUsers()
    {
        $users = User::with(['biodata', 'masterEreg'])->get();

        if ($users->isEmpty()) {
            return back()->with('error', 'Tidak ada data users yang dapat didownload.');
        }

        $fileName = 'semua_users_' . date('Y-m-d_H-i-s') . '.xlsx';
        $filePath = storage_path('app/temp/' . $fileName);

        // Pastikan directory temp exists
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Semua Users');

        // Header
        $headers = [
            'ID', 'Nama', 'Email', 'No. Telepon', 'Role', 'Gelombang',
            'Kode Promo Digunakan', 'VA Number', 'VA Amount', 'Original Amount',
            'Discount Amount', 'VA Expired At', 'VA Status', 'Tanggal Daftar', 'Tanggal Update'
        ];

        $sheet->fromArray($headers, null, 'A1');

        // Style untuk header
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E86C1']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ];
        $sheet->getStyle('A1:O1')->applyFromArray($headerStyle);

        // Data rows
        $rowNumber = 2;
        foreach ($users as $user) {
            $sheet->fromArray([
                $user->id,
                $user->name ?? 'N/A',
                $user->email ?? 'N/A',
                $user->phone ?? 'N/A',
                $user->role ?? 'N/A',
                $user->gelombang ?? 'N/A',
                $user->promo_code_used ?? 'N/A',
                $user->va_number ?? 'N/A',
                $user->va_amount ?? '0',
                $user->original_amount ?? '0',
                $user->discount_amount ?? '0',
                $user->va_expired_at ? Carbon::parse($user->va_expired_at)->format('Y-m-d H:i:s') : 'N/A',
                $user->va_status ?? 'N/A',
                $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : 'N/A',
                $user->updated_at ? $user->updated_at->format('Y-m-d H:i:s') : 'N/A'
            ], null, "A{$rowNumber}");
            $rowNumber++;
        }

        // Auto size columns
        foreach (range('A', 'O') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        $response = response()->download($filePath, $fileName);
        $response->deleteFileAfterSend(true);

        return $response;
    }

    /**
     * Download users yang sudah mengisi biodata
     */
    /**
 * Download users yang sudah mengisi biodata
 */
public function downloadUsersWithBiodata()
{
    $users = User::with(['biodata', 'masterEreg'])
        ->whereHas('biodata')
        ->get();

    if ($users->isEmpty()) {
        return back()->with('error', 'Tidak ada users dengan biodata yang dapat didownload.');
    }

    $fileName = 'users_dengan_biodata_' . date('Y-m-d_H-i-s') . '.xlsx';
    $filePath = storage_path('app/temp/' . $fileName);

    if (!file_exists(storage_path('app/temp'))) {
        mkdir(storage_path('app/temp'), 0755, true);
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Users dengan Biodata');

    // Header lengkap dengan data biodata sesuai struktur tabel
    $headers = [
        'ID User', 
        'Nama User', 
        'Email', 
        'No. Telepon', 
        'Gelombang',
        'Nama Lengkap', 
        'Tempat Lahir',
        'Tanggal Lahir', 
        'Nama Ibu', 
        'Nama Ayah',
        'Alamat',
        'Jenjang Pendidikan',
        'Asal Sekolah', 
        'Jurusan Sekolah',
        'Agama',
        'Tempat Tinggal',
        'Status Verifikasi',
        'Tanggal Daftar User',
        'Tanggal Update Biodata'
    ];

    $sheet->fromArray($headers, null, 'A1');

    // Style header
    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E86C1']],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
    ];
    $sheet->getStyle('A1:S1')->applyFromArray($headerStyle);

    // Data rows
    $rowNumber = 2;
    foreach ($users as $user) {
        $sheet->fromArray([
            $user->id,
            $user->name ?? 'N/A',
            $user->email ?? 'N/A',
            $user->phone ?? 'N/A',
            $user->gelombang ?? 'N/A',
            // Data dari tabel biodatas
            $user->biodata->nama_lengkap ?? 'N/A',
            $user->biodata->tempat_lahir ?? 'N/A',
            $user->biodata->tanggal_lahir ?? 'N/A',
            $user->biodata->nama_ibu ?? 'N/A',
            $user->biodata->nama_ayah ?? 'N/A',
            $user->biodata->alamat ?? 'N/A',
            $user->biodata->jenjang_pendidikan ?? 'N/A',
            $user->biodata->asal_sekolah ?? 'N/A',
            $user->biodata->jurusan_sekolah ?? 'N/A',
            $user->biodata->agama ?? 'N/A',
            $user->biodata->tempat_tinggal ?? 'N/A',
            $user->biodata->is_verified ? 'Terverifikasi' : 'Belum Terverifikasi',
            $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : 'N/A',
            $user->biodata->updated_at ? $user->biodata->updated_at->format('Y-m-d H:i:s') : 'N/A'
        ], null, "A{$rowNumber}");
        $rowNumber++;
    }

    // Auto size columns
    foreach (range('A', 'S') as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }

    $writer = new Xlsx($spreadsheet);
    $writer->save($filePath);

    $response = response()->download($filePath, $fileName);
    $response->deleteFileAfterSend(true);

    return $response;
}

/**
 * Download users dengan biodata berdasarkan jenjang pendidikan
 */
public function downloadUsersByJenjangPendidikan($jenjang)
{
    $users = User::with(['biodata', 'masterEreg'])
        ->whereHas('biodata', function($query) use ($jenjang) {
            $query->where('jenjang_pendidikan', $jenjang);
        })
        ->get();

    if ($users->isEmpty()) {
        return back()->with('error', 'Tidak ada users dengan jenjang pendidikan ' . $jenjang);
    }

    $fileName = 'users_jenjang_' . $jenjang . '_' . date('Y-m-d_H-i-s') . '.xlsx';
    $filePath = storage_path('app/temp/' . $fileName);

    if (!file_exists(storage_path('app/temp'))) {
        mkdir(storage_path('app/temp'), 0755, true);
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Jenjang ' . $jenjang);

    // Header untuk filter jenjang
    $headers = [
        'ID User', 
        'Nama User', 
        'Email', 
        'No. Telepon', 
        'Gelombang',
        'Nama Lengkap', 
        'Tempat Lahir',
        'Tanggal Lahir', 
        'Jenjang Pendidikan',
        'Asal Sekolah', 
        'Jurusan Sekolah',
        'Status Verifikasi',
        'Tanggal Daftar'
    ];

    $sheet->fromArray($headers, null, 'A1');

    // Style header
    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E86C1']],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
    ];
    $sheet->getStyle('A1:M1')->applyFromArray($headerStyle);

    // Data rows
    $rowNumber = 2;
    foreach ($users as $user) {
        $sheet->fromArray([
            $user->id,
            $user->name ?? 'N/A',
            $user->email ?? 'N/A',
            $user->phone ?? 'N/A',
            $user->gelombang ?? 'N/A',
            $user->biodata->nama_lengkap ?? 'N/A',
            $user->biodata->tempat_lahir ?? 'N/A',
            $user->biodata->tanggal_lahir ?? 'N/A',
            $user->biodata->jenjang_pendidikan ?? 'N/A',
            $user->biodata->asal_sekolah ?? 'N/A',
            $user->biodata->jurusan_sekolah ?? 'N/A',
            $user->biodata->is_verified ? 'Terverifikasi' : 'Belum Terverifikasi',
            $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : 'N/A'
        ], null, "A{$rowNumber}");
        $rowNumber++;
    }

    // Auto size columns
    foreach (range('A', 'M') as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }

    $writer = new Xlsx($spreadsheet);
    $writer->save($filePath);

    $response = response()->download($filePath, $fileName);
    $response->deleteFileAfterSend(true);

    return $response;
}

/**
 * Download users dengan biodata yang sudah terverifikasi
 */
public function downloadVerifiedBiodataUsers()
{
    $users = User::with(['biodata', 'masterEreg'])
        ->whereHas('biodata', function($query) {
            $query->where('is_verified', true);
        })
        ->get();

    if ($users->isEmpty()) {
        return back()->with('error', 'Tidak ada users dengan biodata terverifikasi.');
    }

    $fileName = 'users_biodata_terverifikasi_' . date('Y-m-d_H-i-s') . '.xlsx';
    $filePath = storage_path('app/temp/' . $fileName);

    if (!file_exists(storage_path('app/temp'))) {
        mkdir(storage_path('app/temp'), 0755, true);
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Biodata Terverifikasi');

    // Header untuk biodata terverifikasi
    $headers = [
        'ID User', 
        'Nama User', 
        'Email', 
        'Gelombang',
        'Nama Lengkap', 
        'Tempat Lahir',
        'Tanggal Lahir', 
        'Jenjang Pendidikan',
        'Asal Sekolah', 
        'Jurusan Sekolah',
        'Tanggal Verifikasi Biodata',
        'Tanggal Daftar User'
    ];

    $sheet->fromArray($headers, null, 'A1');

    // Style header
    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E86C1']],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
    ];
    $sheet->getStyle('A1:L1')->applyFromArray($headerStyle);

    // Data rows
    $rowNumber = 2;
    foreach ($users as $user) {
        $sheet->fromArray([
            $user->id,
            $user->name ?? 'N/A',
            $user->email ?? 'N/A',
            $user->gelombang ?? 'N/A',
            $user->biodata->nama_lengkap ?? 'N/A',
            $user->biodata->tempat_lahir ?? 'N/A',
            $user->biodata->tanggal_lahir ?? 'N/A',
            $user->biodata->jenjang_pendidikan ?? 'N/A',
            $user->biodata->asal_sekolah ?? 'N/A',
            $user->biodata->jurusan_sekolah ?? 'N/A',
            $user->biodata->updated_at ? $user->biodata->updated_at->format('Y-m-d H:i:s') : 'N/A',
            $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : 'N/A'
        ], null, "A{$rowNumber}");
        $rowNumber++;
    }

    // Auto size columns
    foreach (range('A', 'L') as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }

    $writer = new Xlsx($spreadsheet);
    $writer->save($filePath);

    $response = response()->download($filePath, $fileName);
    $response->deleteFileAfterSend(true);

    return $response;
}
 /**
 * Download users yang sudah lengkap (biodata + registrasi ulang)
 */
public function downloadCompleteUsers()
{
    $users = User::with(['biodata', 'masterEreg'])
        ->whereHas('biodata')
        ->whereHas('masterEreg')
        ->get();

    if ($users->isEmpty()) {
        return back()->with('error', 'Tidak ada users yang sudah lengkap (biodata + registrasi ulang).');
    }

    $fileName = 'users_lengkap_' . date('Y-m-d_H-i-s') . '.xlsx';
    $filePath = storage_path('app/temp/' . $fileName);

    if (!file_exists(storage_path('app/temp'))) {
        mkdir(storage_path('app/temp'), 0755, true);
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Users Lengkap');

    // Header lengkap semua data dari users, biodata, dan masterEreg
    $headers = [
        // Data User (13 kolom)
        'ID User', 
        'Nama User', 
        'Email', 
        'No. Telepon', 
        'Role',
        'Gelombang',
        'Kode Promo Digunakan',
        'VA Number',
        'VA Amount',
        'Original Amount',
        'Discount Amount',
        'VA Status',
        'VA Expired At',
        
        // Data Biodata (13 kolom)
        'Nama Lengkap', 
        'Tempat Lahir',
        'Tanggal Lahir', 
        'Nama Ibu', 
        'Nama Ayah',
        'Alamat',
        'Jenjang Pendidikan',
        'Asal Sekolah', 
        'Jurusan Sekolah',
        'Agama',
        'Tempat Tinggal',
        'Status Verifikasi Biodata',
        'Tanggal Update Biodata',
        
        // Data MasterEreg (7 kolom)
        'Status Pendaftaran', 
        'Jurusan 1', 
        'Jurusan 2',
        'File KK',
        'File Ijazah',
        'Tanggal Registrasi Ulang',
        'Tanggal Update Registrasi',
        
        // Timestamps (2 kolom)
        'Tanggal Daftar User',
        'Tanggal Update User'
    ];

    $sheet->fromArray($headers, null, 'A1');

    // Style header
    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E86C1']],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
    ];
    
    // Total kolom: 13 (User) + 13 (Biodata) + 7 (MasterEreg) + 2 (Timestamps) = 35 kolom
    $totalColumns = 35;
    $sheet->getStyle('A1:' . $this->getExcelColumnName($totalColumns) . '1')->applyFromArray($headerStyle);

    // Data rows
    $rowNumber = 2;
    foreach ($users as $user) {
        $sheet->fromArray([
            // Data User (13 kolom)
            $user->id,
            $user->name ?? 'N/A',
            $user->email ?? 'N/A',
            $user->phone ?? 'N/A',
            $user->role ?? 'N/A',
            $user->gelombang ?? 'N/A',
            $user->promo_code_used ?? 'N/A',
            $user->va_number ?? 'N/A',
            $user->va_amount ?? '0',
            $user->original_amount ?? '0',
            $user->discount_amount ?? '0',
            $user->va_status ?? 'N/A',
            $user->va_expired_at ? Carbon::parse($user->va_expired_at)->format('Y-m-d H:i:s') : 'N/A',
            
            // Data Biodata (13 kolom)
            $user->biodata->nama_lengkap ?? 'N/A',
            $user->biodata->tempat_lahir ?? 'N/A',
            $user->biodata->tanggal_lahir ?? 'N/A',
            $user->biodata->nama_ibu ?? 'N/A',
            $user->biodata->nama_ayah ?? 'N/A',
            $user->biodata->alamat ?? 'N/A',
            $user->biodata->jenjang_pendidikan ?? 'N/A',
            $user->biodata->asal_sekolah ?? 'N/A',
            $user->biodata->jurusan_sekolah ?? 'N/A',
            $user->biodata->agama ?? 'N/A',
            $user->biodata->tempat_tinggal ?? 'N/A',
            $user->biodata->is_verified ? 'Terverifikasi' : 'Belum Terverifikasi',
            $user->biodata->updated_at ? $user->biodata->updated_at->format('Y-m-d H:i:s') : 'N/A',
            
            // Data MasterEreg (7 kolom)
            $user->masterEreg->status ?? 'N/A',
            $user->masterEreg->jurusan1 ?? 'N/A',
            $user->masterEreg->jurusan2 ?? 'N/A',
            $user->masterEreg->file_kk ? 'Ya' : 'Tidak',
            $user->masterEreg->file_ijazah ? 'Ya' : 'Tidak',
            $user->masterEreg->created_at ? $user->masterEreg->created_at->format('Y-m-d H:i:s') : 'N/A',
            $user->masterEreg->updated_at ? $user->masterEreg->updated_at->format('Y-m-d H:i:s') : 'N/A',
            
            // Timestamps User (2 kolom)
            $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : 'N/A',
            $user->updated_at ? $user->updated_at->format('Y-m-d H:i:s') : 'N/A'
        ], null, "A{$rowNumber}");
        $rowNumber++;
    }

    // Auto size columns menggunakan pendekatan yang kompatibel
    for ($i = 1; $i <= $totalColumns; $i++) {
        $columnName = $this->getExcelColumnName($i);
        $sheet->getColumnDimension($columnName)->setAutoSize(true);
    }

    // Tambahkan style untuk memisahkan section dengan warna berbeda
    // Section User (A-M) - 13 kolom
    $sheet->getStyle('A1:' . $this->getExcelColumnName(13) . '1')->applyFromArray([
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E86C1']] // Biru
    ]);
    
    // Section Biodata (N-Z) - 13 kolom (kolom 14-26)
    $sheet->getStyle($this->getExcelColumnName(14) . '1:' . $this->getExcelColumnName(26) . '1')->applyFromArray([
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '27AE60']] // Hijau
    ]);
    
    // Section MasterEreg (AA-AI) - 7 kolom (kolom 27-33)
    $sheet->getStyle($this->getExcelColumnName(27) . '1:' . $this->getExcelColumnName(33) . '1')->applyFromArray([
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E74C3C']] // Merah
    ]);
    
    // Section Timestamps (AJ-AK) - 2 kolom (kolom 34-35)
    $sheet->getStyle($this->getExcelColumnName(34) . '1:' . $this->getExcelColumnName(35) . '1')->applyFromArray([
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F39C12']] // Orange
    ]);

    $writer = new Xlsx($spreadsheet);
    $writer->save($filePath);

    $response = response()->download($filePath, $fileName);
    $response->deleteFileAfterSend(true);

    return $response;
}

/**
 * Helper function untuk mendapatkan nama kolom Excel berdasarkan angka
 * 1 = A, 2 = B, ..., 26 = Z, 27 = AA, 28 = AB, dst.
 */
private function getExcelColumnName($columnNumber)
{
    $columnName = '';
    while ($columnNumber > 0) {
        $modulo = ($columnNumber - 1) % 26;
        $columnName = chr(65 + $modulo) . $columnName;
        $columnNumber = (int)(($columnNumber - $modulo) / 26);
    }
    return $columnName;
}
/**
 * Download users per gelombang dengan data lengkap
 */
public function downloadByGelombang(Request $request)
{
    $request->validate([
        'gelombang' => 'required|string'
    ]);

    $gelombang = $request->gelombang;

    $users = User::with(['biodata', 'masterEreg'])
        ->where('gelombang', $gelombang)
        ->get();

    if ($users->isEmpty()) {
        return back()->with('error', 'Tidak ada users pada gelombang ' . $gelombang);
    }

    $fileName = 'users_gelombang_' . $gelombang . '_' . date('Y-m-d_H-i-s') . '.xlsx';
    $filePath = storage_path('app/temp/' . $fileName);

    if (!file_exists(storage_path('app/temp'))) {
        mkdir(storage_path('app/temp'), 0755, true);
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Gelombang ' . $gelombang);

    // Header lengkap untuk gelombang
    $headers = [
        // Data User
        'ID User', 
        'Nama User', 
        'Email', 
        'No. Telepon', 
        'Role',
        'Gelombang',
        
        // Data Biodata
        'Nama Lengkap', 
        'Tempat Lahir',
        'Tanggal Lahir', 
        'Nama Ibu', 
        'Nama Ayah',
        'Alamat',
        'Jenjang Pendidikan',
        'Asal Sekolah', 
        'Jurusan Sekolah',
        'Agama',
        'Tempat Tinggal',
        'Status Verifikasi Biodata',
        
        // Data MasterEreg
        'Status Pendaftaran', 
        'Jurusan 1', 
        'Jurusan 2',
        'File KK',
        'File Ijazah',
        'Jalur Pendaftaran',
        'Status Pekerjaan',
        
        // Timestamps
        'Tanggal Daftar User',
        'Tanggal Update Biodata',
        'Tanggal Registrasi Ulang'
    ];

    $sheet->fromArray($headers, null, 'A1');

    // Style header
    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E86C1']],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
    ];
    
    // Total 25 kolom (A-Y)
    $sheet->getStyle('A1:Y1')->applyFromArray($headerStyle);

    // Data rows
    $rowNumber = 2;
    foreach ($users as $user) {
        // Data Biodata dengan null safety
        $biodataNamaLengkap = $user->biodata->nama_lengkap ?? 'N/A';
        $biodataTempatLahir = $user->biodata->tempat_lahir ?? 'N/A';
        $biodataTanggalLahir = $user->biodata->tanggal_lahir ?? 'N/A';
        $biodataNamaIbu = $user->biodata->nama_ibu ?? 'N/A';
        $biodataNamaAyah = $user->biodata->nama_ayah ?? 'N/A';
        $biodataAlamat = $user->biodata->alamat ?? 'N/A';
        $biodataJenjang = $user->biodata->jenjang_pendidikan ?? 'N/A';
        $biodataAsalSekolah = $user->biodata->asal_sekolah ?? 'N/A';
        $biodataJurusanSekolah = $user->biodata->jurusan_sekolah ?? 'N/A';
        $biodataAgama = $user->biodata->agama ?? 'N/A';
        $biodataTempatTinggal = $user->biodata->tempat_tinggal ?? 'N/A';
        $biodataStatusVerifikasi = $user->biodata ? ($user->biodata->is_verified ? 'Terverifikasi' : 'Belum Terverifikasi') : 'Tidak Ada Biodata';
        $biodataUpdatedAt = $user->biodata ? ($user->biodata->updated_at ? $user->biodata->updated_at->format('Y-m-d H:i:s') : 'N/A') : 'N/A';

        // Data MasterEreg dengan null safety
        $masterEregStatus = $user->masterEreg->status ?? 'Belum Registrasi Ulang';
        $masterEregJurusan1 = $user->masterEreg->jurusan1 ?? 'N/A';
        $masterEregJurusan2 = $user->masterEreg->jurusan2 ?? 'N/A';
        $masterEregFileKK = $user->masterEreg ? ($user->masterEreg->file_kk ? 'Ya' : 'Tidak') : 'Tidak';
        $masterEregFileIjazah = $user->masterEreg ? ($user->masterEreg->file_ijazah ? 'Ya' : 'Tidak') : 'Tidak';
        $masterEregJalur = $user->masterEreg->jalur_pendaftaran ?? 'N/A';
        $masterEregPekerjaan = $user->masterEreg->status_pekerjaan ?? 'N/A';
        $masterEregCreatedAt = $user->masterEreg ? ($user->masterEreg->created_at ? $user->masterEreg->created_at->format('Y-m-d H:i:s') : 'N/A') : 'N/A';

        $sheet->fromArray([
            // Data User (6 kolom)
            $user->id,
            $user->name ?? 'N/A',
            $user->email ?? 'N/A',
            $user->phone ?? 'N/A',
            $user->role ?? 'N/A',
            $user->gelombang ?? 'N/A',
            
            // Data Biodata (12 kolom)
            $biodataNamaLengkap,
            $biodataTempatLahir,
            $biodataTanggalLahir,
            $biodataNamaIbu,
            $biodataNamaAyah,
            $biodataAlamat,
            $biodataJenjang,
            $biodataAsalSekolah,
            $biodataJurusanSekolah,
            $biodataAgama,
            $biodataTempatTinggal,
            $biodataStatusVerifikasi,
            
            // Data MasterEreg (7 kolom)
            $masterEregStatus,
            $masterEregJurusan1,
            $masterEregJurusan2,
            $masterEregFileKK,
            $masterEregFileIjazah,
            $masterEregJalur,
            $masterEregPekerjaan,
            
            // Timestamps (3 kolom)
            $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : 'N/A',
            $biodataUpdatedAt,
            $masterEregCreatedAt
        ], null, "A{$rowNumber}");
        $rowNumber++;
    }

    // Auto size columns untuk 25 kolom (A-Y)
    foreach (range('A', 'Y') as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }

    $sheet->getStyle('A1:F1')->applyFromArray([
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E86C1']] // Biru
    ]);
    
    $sheet->getStyle('G1:R1')->applyFromArray([
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '27AE60']] // Hijau
    ]);
    
    $sheet->getStyle('S1:Y1')->applyFromArray([
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E74C3C']] // Merah
    ]);

    $writer = new Xlsx($spreadsheet);
    $writer->save($filePath);

    $response = response()->download($filePath, $fileName);
    $response->deleteFileAfterSend(true);

    return $response;
}
   public function downloadAll()
{
    // Ambil semua data pendaftaran yang memiliki file
    $pendaftaran = MasterEreg::with(['user', 'biodata'])
        ->where(function($query) {
            $query->whereNotNull('file_kk')
                  ->orWhereNotNull('file_ijazah');
        })->get();

    // Ambil semua data users untuk CSV dan XLSX
    $users = User::with(['masterEreg', 'biodata'])->get();

    if ($pendaftaran->isEmpty() && $users->isEmpty()) {
        return back()->with('error', 'Tidak ada data yang dapat didownload.');
    }

    // Buat nama file ZIP
    $zipFileName = 'semua_data_' . date('Y-m-d_H-i-s') . '.zip';
    $zipPath = storage_path('app/temp/' . $zipFileName);

    // Pastikan directory temp exists
    if (!file_exists(storage_path('app/temp'))) {
        mkdir(storage_path('app/temp'), 0755, true);
    }

    // Buat ZIP archive
    $zip = new ZipArchive;
    
    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        
        // Tambahkan file CSV users jika ada data
        if ($users->isNotEmpty()) {
            $csvContent = $this->createUsersCSV($users);
            $zip->addFromString('data_users.csv', $csvContent);
            
            // Tambahkan file XLSX users
            $xlsxPath = $this->createUsersXLSX($users);
            $zip->addFile($xlsxPath, 'data_users.xlsx');
        }

        // Tambahkan berkas untuk setiap peserta
        foreach ($pendaftaran as $item) {
            // Buat folder untuk setiap peserta
            $folderName = $this->sanitizeFileName($item->nama) . '_' . $item->id;
            
            // Tambahkan file KK jika ada
            if ($item->file_kk && Storage::disk('public')->exists($item->file_kk)) {
                $kkPath = storage_path('app/public/' . $item->file_kk);
                $kkFileName = 'Kartu_Keluarga.pdf';
                $zip->addFile($kkPath, $folderName . '/' . $kkFileName);
            }
            
            // Tambahkan file Ijazah jika ada
            if ($item->file_ijazah && Storage::disk('public')->exists($item->file_ijazah)) {
                $ijazahPath = storage_path('app/public/' . $item->file_ijazah);
                $ijazahFileName = 'Ijazah.pdf';
                $zip->addFile($ijazahPath, $folderName . '/' . $ijazahFileName);
            }

            // Tambahkan file info peserta dalam format XLSX
            $infoXlsxPath = $this->createInfoXLSX($item);
            if ($infoXlsxPath) {
                $zip->addFile($infoXlsxPath, $folderName . '/info_peserta.xlsx');
            }
        }
        
        $zip->close();
        
        // Hapus file temporary setelah ditambahkan ke ZIP
        if (isset($xlsxPath) && file_exists($xlsxPath)) {
            unlink($xlsxPath);
        }
        
        // Hapus file info XLSX temporary
        foreach ($pendaftaran as $item) {
            $tempInfoPath = storage_path('app/temp/info_peserta_' . $item->id . '.xlsx');
            if (file_exists($tempInfoPath)) {
                unlink($tempInfoPath);
            }
        }
    } else {
        return back()->with('error', 'Gagal membuat file ZIP.');
    }

    // Download file ZIP
    $response = response()->download($zipPath, $zipFileName);

    // Hapus file ZIP setelah didownload
    register_shutdown_function(function() use ($zipPath) {
        if (file_exists($zipPath)) {
            unlink($zipPath);
        }
    });

    return $response;
}
    /**
     * Download berkas berdasarkan status tertentu
     */
    public function downloadByStatus($status)
    {
        $validStatuses = ['completed', 'in_progress', 'rejected'];
        
        if (!in_array($status, $validStatuses)) {
            return back()->with('error', 'Status tidak valid.');
        }

        // Ambil data berdasarkan status dengan relasi biodata
        $pendaftaran = MasterEreg::with(['user', 'biodata'])
            ->where('status', $status)
            ->where(function($query) {
                $query->whereNotNull('file_kk')
                      ->orWhereNotNull('file_ijazah');
            })->get();

        if ($pendaftaran->isEmpty()) {
            return back()->with('error', 'Tidak ada berkas dengan status ' . $status . ' yang dapat didownload.');
        }

        // Buat nama file ZIP
        $statusLabels = [
            'completed' => 'Completed',
            'in_progress' => 'In_Progress',
            'rejected' => 'Rejected'
        ];
        
        $zipFileName = 'berkas_' . $statusLabels[$status] . '_' . date('Y-m-d_H-i-s') . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);

        // Pastikan directory temp exists
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        // Buat ZIP archive
        $zip = new ZipArchive;
        
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach ($pendaftaran as $item) {
                // Buat folder untuk setiap peserta
                $folderName = $this->sanitizeFileName($item->nama) . '_' . $item->id;
                
                // Tambahkan file KK jika ada
                if ($item->file_kk && Storage::disk('public')->exists($item->file_kk)) {
                    $kkPath = storage_path('app/public/' . $item->file_kk);
                    $kkFileName = 'Kartu_Keluarga.pdf';
                    $zip->addFile($kkPath, $folderName . '/' . $kkFileName);
                }
                
                // Tambahkan file Ijazah jika ada
                if ($item->file_ijazah && Storage::disk('public')->exists($item->file_ijazah)) {
                    $ijazahPath = storage_path('app/public/' . $item->file_ijazah);
                    $ijazahFileName = 'Ijazah.pdf';
                    $zip->addFile($ijazahPath, $folderName . '/' . $ijazahFileName);
                }

                // Tambahkan file info peserta dalam format XLSX
                $infoXlsxPath = $this->createInfoXLSX($item);
                if ($infoXlsxPath) {
                    $zip->addFile($infoXlsxPath, $folderName . '/info_peserta.xlsx');
                }
            }
            
            $zip->close();
            
            // Hapus file info XLSX temporary
            foreach ($pendaftaran as $item) {
                $tempInfoPath = storage_path('app/temp/info_peserta_' . $item->id . '.xlsx');
                if (file_exists($tempInfoPath)) {
                    unlink($tempInfoPath);
                }
            }
        } else {
            return back()->with('error', 'Gagal membuat file ZIP.');
        }

        // Download file ZIP
        $response = response()->download($zipPath, $zipFileName);

        // Hapus file ZIP setelah didownload
        register_shutdown_function(function() use ($zipPath) {
            if (file_exists($zipPath)) {
                unlink($zipPath);
            }
        });

        return $response;
    }

    /**
     * Helper function untuk membuat file info peserta dalam format XLSX
     */
    private function createInfoXLSX($item)
{
    try {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set judul worksheet
        $sheet->setTitle('Info Peserta');

        // Header style
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2E86C1']
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ];

        // Data style
        $dataStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'DDDDDD']
                ]
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ];

        // Judul
        $sheet->setCellValue('A1', 'INFORMASI PESERTA');
        $sheet->mergeCells('A1:B1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Data peserta dengan informasi lengkap dari biodata
        $data = [
            ['DATA UMUM', ''],
            ['Nama', $item->nama],
            ['Email', $item->user->email ?? 'N/A'],
            ['Status Pendaftaran', $item->status],
            ['Tanggal Daftar', $item->created_at->format('d M Y H:i')],
            ['Terakhir Update', $item->updated_at->format('d M Y H:i')],
            ['', ''], // Empty row untuk pemisah
            
            ['DATA BIODATA LENGKAP', ''],
            ['Nama Lengkap', $item->biodata->nama_lengkap ?? 'N/A'],
            ['Tempat Lahir', $item->biodata->tempat_lahir ?? 'N/A'],
            ['Tanggal Lahir', $item->biodata->tanggal_lahir ?? 'N/A'],
            ['Nama Ibu', $item->biodata->nama_ibu ?? 'N/A'],
            ['Nama Ayah', $item->biodata->nama_ayah ?? 'N/A'],
            ['Alamat', $item->biodata->alamat ?? 'N/A'],
            ['Jenjang Pendidikan', $item->biodata->jenjang_pendidikan ?? 'N/A'],
            ['Asal Sekolah', $item->biodata->asal_sekolah ?? 'N/A'],
            ['Jurusan Sekolah', $item->biodata->jurusan_sekolah ?? 'N/A'],
            ['Agama', $item->biodata->agama ?? 'N/A'],
            ['Tempat Tinggal', $item->biodata->tempat_tinggal ?? 'N/A'],
            ['Status Verifikasi', $item->biodata->is_verified ? 'Terverifikasi' : 'Belum Terverifikasi'],
            ['', ''], // Empty row untuk pemisah
            
            ['FILE YANG TERSEDIA', ''],
            ['Kartu Keluarga', $item->file_kk ? 'Ya' : 'Tidak'],
            ['Ijazah', $item->file_ijazah ? 'Ya' : 'Tidak'],
        ];

        // Mulai dari row 3
        $row = 3;
        foreach ($data as $itemData) {
            $sheet->setCellValue('A' . $row, $itemData[0]);
            $sheet->setCellValue('B' . $row, $itemData[1]);
            
            // Apply header style untuk judul section
            if (in_array($itemData[0], ['DATA UMUM', 'DATA BIODATA LENGKAP', 'FILE YANG TERSEDIA'])) {
                $sheet->getStyle('A' . $row . ':B' . $row)->applyFromArray($headerStyle);
                $sheet->mergeCells('A' . $row . ':B' . $row);
            } else {
                $sheet->getStyle('A' . $row . ':B' . $row)->applyFromArray($dataStyle);
            }
            
            $row++;
        }

        // Set column width
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(40);

        // Set row height untuk judul section
        foreach (range(3, $row) as $rowNum) {
            $sheet->getRowDimension($rowNum)->setRowHeight(25);
        }

        // Simpan file temporary
        $fileName = 'info_peserta_' . $item->id . '.xlsx';
        $filePath = storage_path('app/temp/' . $fileName);
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return $filePath;

    } catch (\Exception $e) {
        Log::error('Error creating info XLSX for participant ' . $item->id . ': ' . $e->getMessage());
        return null;
    }
}

    /**
     * Helper function untuk membuat CSV users
     */
    private function createUsersCSV($users)
    {
        // Header CSV
        $headers = [
            'ID',
            'Nama',
            'Email', 
            'No. Telepon',
            'Role',
            'Gelombang',
            'Kode Promo Digunakan',
            'VA Number',
            'VA Amount',
            'Original Amount',
            'Discount Amount',
            'VA Expired At',
            'VA Status',
            'Tanggal Daftar',
            'Tanggal Update',
            'Nama Lengkap (Biodata)',
            'Alamat (Biodata)',
            'Tanggal Lahir (Biodata)',
            'Nama Ibu (Biodata)',
            'Nama Ayah (Biodata)',
            'Asal Sekolah (Biodata)',
            'Jurusan Sekolah (Biodata)',
            'Status Pendaftaran',
            'Jurusan 1',
            'Jurusan 2'
        ];

        // Buat CSV content
        $csvContent = '';
        
        // Tambahkan header
        $csvContent .= $this->formatCsvRow($headers);
        
        // Data rows
        foreach ($users as $user) {
            $row = $this->prepareUserData($user);
            $csvContent .= $this->formatCsvRow($row);
        }
        
        return $csvContent;
    }

    /**
     * Helper function untuk membuat XLSX users
     */
    private function createUsersXLSX($users)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set judul
        $sheet->setTitle('Data Users');

        // Header
        $headers = [
            'ID',
            'Nama',
            'Email', 
            'No. Telepon',
            'Role',
            'Gelombang',
            'Kode Promo Digunakan',
            'VA Number',
            'VA Amount',
            'Original Amount',
            'Discount Amount',
            'VA Expired At',
            'VA Status',
            'Tanggal Daftar',
            'Tanggal Update',
            'Nama Lengkap (Biodata)',
            'Alamat (Biodata)',
            'Tanggal Lahir (Biodata)',
            'Nama Ibu (Biodata)',
            'Nama Ayah (Biodata)',
            'Asal Sekolah (Biodata)',
            'Jurusan Sekolah (Biodata)',
            'Status Pendaftaran',
            'Jurusan 1',
            'Jurusan 2'
        ];

        // Set header dengan styling
        $sheet->fromArray($headers, null, 'A1');
        
        // Style untuk header
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2E86C1']
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ];
        
        $sheet->getStyle('A1:Y1')->applyFromArray($headerStyle);

        // Data rows
        $rowNumber = 2;
        foreach ($users as $user) {
            $rowData = $this->prepareUserData($user);
            $sheet->fromArray($rowData, null, "A{$rowNumber}");
            $rowNumber++;
        }

        // Auto size columns
        foreach (range('A', 'Y') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Style untuk data rows
        $dataStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'DDDDDD']
                ]
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ];
        
        $lastRow = count($users) + 1;
        $sheet->getStyle("A2:Y{$lastRow}")->applyFromArray($dataStyle);

        // Simpan file temporary
        $xlsxPath = storage_path('app/temp/data_users_' . date('Y-m-d_H-i-s') . '.xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($xlsxPath);

        return $xlsxPath;
    }

    /**
     * Helper function untuk mempersiapkan data user
     */
    private function prepareUserData($user)
    {
        // Handle va_expired_at - bisa berupa string atau Carbon instance
        $vaExpiredAt = 'N/A';
        if ($user->va_expired_at) {
            if ($user->va_expired_at instanceof \Carbon\Carbon) {
                $vaExpiredAt = $user->va_expired_at->format('Y-m-d H:i:s');
            } else {
                // Jika berupa string, coba parse dulu
                try {
                    $vaExpiredAt = Carbon::parse($user->va_expired_at)->format('Y-m-d H:i:s');
                } catch (\Exception $e) {
                    $vaExpiredAt = $user->va_expired_at; // Gunakan string asli jika gagal parse
                }
            }
        }

        return [
            $user->id,
            $user->name ?? 'N/A',
            $user->email ?? 'N/A',
            $user->phone ?? 'N/A',
            $user->role ?? 'N/A',
            $user->gelombang ?? 'N/A',
            $user->promo_code_used ?? 'N/A',
            $user->va_number ?? 'N/A',
            $user->va_amount ?? '0',
            $user->original_amount ?? '0',
            $user->discount_amount ?? '0',
            $vaExpiredAt,
            $user->va_status ?? 'N/A',
            $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : 'N/A',
            $user->updated_at ? $user->updated_at->format('Y-m-d H:i:s') : 'N/A',
            $user->biodata->nama_lengkap ?? 'N/A',
            $user->biodata->alamat ?? 'N/A',
            $user->biodata->tanggal_lahir ?? 'N/A',
            $user->biodata->nama_ibu ?? 'N/A',
            $user->biodata->nama_ayah ?? 'N/A',
            $user->biodata->asal_sekolah ?? 'N/A',
            $user->biodata->jurusan_sekolah ?? 'N/A',
            $user->masterEreg->status ?? 'N/A',
            $user->masterEreg->jurusan1 ?? 'N/A',
            $user->masterEreg->jurusan2 ?? 'N/A'
        ];
    }

    private function formatCsvRow($data)
    {
        $output = '';
        
        foreach ($data as $key => $value) {
            // Escape value jika mengandung koma, quote, atau newline
            if (is_string($value)) {
                $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                
                // Jika value mengandung koma, quote, atau newline, wrap dengan quotes
                if (strpos($value, ',') !== false || 
                    strpos($value, '"') !== false || 
                    strpos($value, "\n") !== false || 
                    strpos($value, "\r") !== false) {
                    $value = '"' . str_replace('"', '""', $value) . '"';
                }
            } elseif ($value === null) {
                $value = 'N/A';
            }
            
            $output .= $value;
            
            if ($key < count($data) - 1) {
                $output .= ',';
            }
        }
        
        $output .= "\n";
        return $output;
    }

    /**
     * Helper function untuk convert array ke CSV
     */
    private function arrayToCsv($data)
    {
        return $this->formatCsvRow($data);
    }

    /**
     * Helper function untuk sanitize nama file
     */
    private function sanitizeFileName($name)
    {
        // Hapus karakter yang tidak diinginkan dari nama folder
        $name = preg_replace('/[^\w\s-]/', '', $name);
        $name = preg_replace('/[-\s]+/', '_', $name);
        return trim($name, '_-');
    }

    /**
     * Helper function untuk membuat file info peserta dengan data dari biodata (format lama TXT - untuk backup)
     */
    private function createInfoFile($item)
    {
        $info = "INFORMASI PESERTA\n";
        $info .= "================\n";
        $info .= "Nama: " . $item->nama . "\n";
        $info .= "Email: " . ($item->user->email ?? 'N/A') . "\n";
        $info .= "Status: " . $item->status . "\n";
        $info .= "Tanggal Daftar: " . $item->created_at->format('d M Y H:i') . "\n";
        $info .= "Terakhir Update: " . $item->updated_at->format('d M Y H:i') . "\n";
        
        // Data dari tabel biodatas
        if ($item->biodata) {
            $info .= "\nDATA BIODATA:\n";
            $info .= "Nama Lengkap: " . ($item->biodata->nama_lengkap ?? 'N/A') . "\n";
            $info .= "Alamat: " . ($item->biodata->alamat ?? 'N/A') . "\n";
            $info .= "Tanggal Lahir: " . ($item->biodata->tanggal_lahir ?? 'N/A') . "\n";
            $info .= "Nama Ibu: " . ($item->biodata->nama_ibu ?? 'N/A') . "\n";
            $info .= "Nama Ayah: " . ($item->biodata->nama_ayah ?? 'N/A') . "\n";
            $info .= "Asal Sekolah: " . ($item->biodata->asal_sekolah ?? 'N/A') . "\n";
            $info .= "Jurusan Sekolah: " . ($item->biodata->jurusan_sekolah ?? 'N/A') . "\n";
        } else {
            $info .= "\nDATA BIODATA: Tidak tersedia\n";
        }
        
        $info .= "\nFILE YANG TERSEDIA:\n";
        $info .= "- Kartu Keluarga: " . ($item->file_kk ? 'Ya' : 'Tidak') . "\n";
        $info .= "- Ijazah: " . ($item->file_ijazah ? 'Ya' : 'Tidak') . "\n";
        
        return $info;
    }
}