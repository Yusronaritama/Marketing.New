<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterEreg;
use App\Models\User;
use App\Models\ProgramStudi; // Pastikan model ini di-import jika digunakan di index
use Illuminate\Http\Request;
use ZipArchive;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class DownloadController extends Controller
{
    private $kodeProdiS2 = '61114'; // Kode Khusus S2 Manajemen

    /**
     * Menampilkan halaman utama download data
     * Method ini wajib ada jika route mengarah ke DownloadController@index
     */
    public function index()
    {
        // Ambil data Program Studi untuk dropdown filter jika diperlukan di view
        $programStudi = ProgramStudi::where('status_prodi', 1)
                        ->orderBy('nama_prodi', 'asc')
                        ->get();

        // Pastikan path view sesuai dengan lokasi file blade Anda
        return view('admin.download-data.index', compact('programStudi'));
    }

    /**
     * KHUSUS DOWNLOAD EXCEL DATA S2 (MANAJEMEN)
     */
    public function downloadExcelS2()
    {
        $dataS2 = MasterEreg::with(['user', 'biodata', 'prodi'])
            ->where('jurusan1', $this->kodeProdiS2)
            ->get();

        if ($dataS2->isEmpty()) {
            return back()->with('error', 'Data pendaftar S2 tidak ditemukan.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'No', 'Nama Lengkap', 'Email', 'Nomor Telepon', 'Gelombang', 
            'Jalur Pendaftaran', 'Status Pekerjaan', 'Jurusan Lulusan', 'Kode Promo', 
            'Kode Jurusan 1', 'Jurusan 1', 'Alasan Jurusan 1', 'Kode Jurusan 2', 
            'Jurusan 2', 'Alasan Jurusan 2', 'Status Pendaftaran', 'Step Completed', 
            'Scan Status', 'Dibuat Pada', 'Diupdate Pada'
        ];
        $sheet->fromArray($headers, NULL, 'A1');

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '465FFF']],
        ];
        $sheet->getStyle('A1:T1')->applyFromArray($headerStyle);

        $rows = [];
        foreach ($dataS2 as $index => $item) {
            $rows[] = [
                $index + 1,
                $item->nama ?? ($item->biodata->nama_lengkap ?? '-'),
                $item->gmail ?? ($item->user->email ?? '-'),
                $item->phone ?? ($item->user->phone ?? '-'),
                $item->user->gelombang ?? '1',
                $item->jalur_pendaftaran,
                $item->status_pekerjaan ?? 'N/A',
                $item->jurusan_lulusan ?? 'N/A',
                $item->kode_promo ?? 'N/A',
                $item->jurusan1,
                $item->prodi->nama_prodi ?? 'Manajemen S2',
                $item->alasan_jurusan_1 ?? '-',
                $item->jurusan2 ?? '-',
                '-', 
                $item->alasan_jurusan_2 ?? '-',
                $item->status,
                $item->step_completed,
                $item->scan_status ?? 'pending',
                $item->created_at->format('d/m/Y H:i'),
                $item->updated_at->format('d/m/Y H:i'),
            ];
        }

        $sheet->fromArray($rows, NULL, 'A2');

        foreach (range('A', 'T') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Data_Pendaftar_S2_Manajemen_' . now()->format('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. $fileName .'"');
        $writer->save('php://output');
        exit;
    }

    /**
     * DOWNLOAD ZIP BERKAS S2
     */
    public function downloadZipS2()
    {
        $pendaftaran = MasterEreg::where('jurusan1', $this->kodeProdiS2)->get();

        if ($pendaftaran->isEmpty()) {
            return back()->with('error', 'Berkas S2 tidak ditemukan.');
        }

        $zip = new ZipArchive;
        $fileName = 'Berkas_S2_Manajemen_' . now()->format('YmdHis') . '.zip';
        $zipPath = storage_path('app/public/' . $fileName);

        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            foreach ($pendaftaran as $row) {
                $folderName = str_replace(' ', '_', $row->nama) . '/';

                $files = [
                    'file_kk' => 'Kartu_Keluarga',
                    'file_ktp' => 'KTP',
                    'file_ijazah' => 'Ijazah_Terakhir',
                    'file_ijazah_s1' => 'Ijazah_S1_Asli',
                    'file_transkrip_s1' => 'Transkrip_S1_Asli',
                    'file_foto' => 'Pas_Foto'
                ];

                foreach ($files as $column => $label) {
                    if ($row->$column && Storage::disk('public')->exists($row->$column)) {
                        $filePath = Storage::disk('public')->path($row->$column);
                        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
                        $zip->addFile($filePath, $folderName . $label . '.' . $extension);
                    }
                }
            }
            $zip->close();
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }
}