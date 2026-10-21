<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PendaftaranController extends Controller
{
    public function daftar(Request $request)
    {
        // 1. Tangkap Data Form
        $data = [
            'nama' => $request->nama,
            'email' => $request->email,
            'nomer_hp' => $request->nomer_hp,
            'tanggal_daftar' => now()
        ];

        try {
            // 2. Simpan ke Database
            DB::table('akun_pendaftar_default')->insert($data);

            // 3. Simpan ke File Excel
            $this->appendToExcel($data);

            return "Pendaftaran berhasil, data telah masuk ke Database dan Excel!";
        } catch (\Exception $e) {
            return "Terjadi kesalahan: " . $e->getMessage();
        }
    }

    private function appendToExcel($data)
    {
        $filePath = storage_path('app/public/data_pendaftar.xlsx');
        
        // Cek apakah file sudah ada
        if (file_exists($filePath)) {
            // Load file yang sudah ada
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $lastRow = $sheet->getHighestRow() + 1; // Cari baris kosong berikutnya
        } else {
            // Buat file baru jika belum ada
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Buat Header
            $sheet->setCellValue('A1', 'No');
            $sheet->setCellValue('B1', 'Nama');
            $sheet->setCellValue('C1', 'Email');
            $sheet->setCellValue('D1', 'No HP');
            $sheet->setCellValue('E1', 'Tanggal Daftar');
            $lastRow = 2;
        }

        // Masukkan data ke baris terbaru
        $sheet->setCellValue('A' . $lastRow, $lastRow - 1);
        $sheet->setCellValue('B' . $lastRow, $data['nama']);
        $sheet->setCellValue('C' . $lastRow, $data['email']);
        $sheet->setCellValue('D' . $lastRow, $data['nomer_hp']);
        $sheet->setCellValue('E' . $lastRow, $data['tanggal_daftar']);

        // Simpan file
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);
    }
}