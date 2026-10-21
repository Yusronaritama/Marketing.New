<?php

namespace App\Exports;

use App\Models\User;
use App\Models\ProgramStudi; // Tambahkan pemanggilan model ProgramStudi
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PengelolaanExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $request;
    protected $rowNumber = 0;
    protected $prodiList; // Tambahkan properti untuk menyimpan daftar prodi

    public function __construct(Request $request)
    {
        $this->request = $request;
        
        // Ambil semua data prodi dan jadikan array dengan key kode_prodi dan value nama_prodi
        // Contoh hasil: ['21201' => 'Teknik Mesin', '48201' => 'Farmasi']
        $this->prodiList = ProgramStudi::pluck('nama_prodi', 'kode_prodi')->toArray();
    }

    public function collection()
    {
        // Query persis seperti yang ada di halaman pengelolaan
        $query = User::with(['biodata', 'masterEreg', 'masterUser'])
            ->where('role', 'mahasiswa_baru')
            ->whereHas('masterEreg', function ($q) {
                if ($this->request->filled('jalur')) {
                    $q->where('jalur_pendaftaran', 'LIKE', '%' . $this->request->jalur . '%');
                } else {
                    $q->where(function($q2) {
                        $q2->where('jalur_pendaftaran', 'LIKE', '%Ekstensi%')
                           ->orWhere('jalur_pendaftaran', 'LIKE', '%Pindahan%')
                           ->orWhere('jalur_pendaftaran', 'LIKE', '%RPL%');
                    });
                }
            });

        if ($this->request->filled('search')) {
            $search = $this->request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('biodata', function ($qBio) use ($search) {
                    $qBio->where('nama_lengkap', 'LIKE', "%{$search}%");
                })->orWhere('name', 'LIKE', "%{$search}%");
            });
        }

        return $query->get();
    }

    // Mapping data per baris
    public function map($user): array
    {
        $this->rowNumber++;
        
        // Dapatkan kode prodi dari master_ereg
        $kodeProdi = $user->masterEreg->jurusan1 ?? null;
        
        // Cocokkan kode dengan daftar nama prodi. Jika tidak ketemu, tampilkan kodenya saja atau '-'
        $namaProdi = $kodeProdi ? ($this->prodiList[$kodeProdi] ?? $kodeProdi) : '-';

        return [
            $this->rowNumber,
            $user->name ?? '-',
            $user->biodata->nama_lengkap ?? $user->name ?? '-',
            $user->email ?? '-',
            $namaProdi, // Di sini nama prodi sudah diterjemahkan
            $user->masterUser->gelombang ?? '-',
            $user->masterEreg->jalur_pendaftaran ?? '-',
        ];
    }

    // Judul Kolom (Header)
    public function headings(): array
    {
        return [
            'No',
            'Username',
            'Nama Lengkap',
            'Email',
            'Prodi',
            'Gelombang',
            'Jalur Pendaftaran'
        ];
    }

    // Styling Warna Excel
    public function styles(Worksheet $sheet)
    {
        return [
            // Targetkan Baris 1 (Header)
            1 => [
                'font' => [
                    'bold' => true, 
                    'color' => ['argb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => '5B7FFF']
                ],
            ],
        ];
    }
}