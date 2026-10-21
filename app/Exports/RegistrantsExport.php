<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Models\AkunPendaftarDefault;

class RegistrantsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return AkunPendaftarDefault::all();
    }

    public function headings(): array
    {
        return ['ID', 'Nama', 'Email', 'No. HP', 'VA Number', 'Password Awal', 'Tgl Daftar'];
    }

    public function map($registrant): array
    {
        return [
            $registrant->id,
            $registrant->name,
            $registrant->email,
            $registrant->phone,
            $registrant->va_number,
            $registrant->password_plain,
            $registrant->created_at->format('Y-m-d H:i:s'),
        ];
    }
}