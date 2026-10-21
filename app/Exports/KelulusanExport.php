<?php
namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class KelulusanExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        // Menambahkan filter profesi agar hanya Apoteker yang diekspor
        return User::where('role', 'mahasiswa_baru')
            ->where('profesi', 'Apoteker Farmasi')
            ->get([
                'name', 
                'email', 
                'phone', 
                'status_cbt', 
                'status_wawancara'
            ]);
    }

    public function headings(): array
    {
        return ["Nama Lengkap", "Email", "No HP", "Status CBT", "Status Wawancara"];
    }
}