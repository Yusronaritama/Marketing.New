<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class MandiriRegisterExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = DB::table('master_users as mu')
            ->join('users as u', 'mu.user_id', '=', 'u.id')
            // Tambahkan LEFT JOIN ke tabel promo_codes
            ->leftJoin('promo_codes as pc', 'mu.promo_code_used', '=', 'pc.code')
            // Tambahkan pc.description sebagai school_name ke dalam select
            ->select('mu.*', 'u.name', 'u.email', 'pc.description as school_name')
            ->where('mu.va_number', 'like', '896%');

        // Filter Default: PAID
        if ($this->request->filled('status')) {
            $query->where('mu.va_status', $this->request->status);
        } else {
            $query->where('mu.va_status', 'paid');
        }

        // Filter Nominal
        if ($this->request->get('amount') == '285000') {
            $query->where('mu.va_amount', 285000);
        }

        // Filter Pencarian
        if ($this->request->filled('search')) {
            $search = $this->request->search;
            $query->where(function($q) use ($search) {
                $q->where('u.name', 'like', "%{$search}%")
                  ->orWhere('mu.va_number', 'like', "%{$search}%")
                  ->orWhere('mu.trx_id', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('mu.created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'Nama Lengkap',
            'Email',
            'TRX ID',
            'VA Number',
            'Gelombang',
            'Nominal (Rp)',
            'Kode Promo',
            'Asal Sekolah', // Tambahkan kolom header baru
            'Status',
            'Tanggal Dibuat',
            'Tanggal Bayar'
        ];
    }

    public function map($item): array
    {
        return [
            $item->name,
            $item->email,
            "'" . $item->trx_id, 
            "'" . $item->va_number, 
            $item->gelombang,
            number_format($item->va_amount, 0, ',', '.'),
            $item->promo_code_used ?? '-',
            $item->school_name ?? '-', // Panggil data alias school_name dari hasil Join
            strtoupper($item->va_status),
            $item->created_at,
            $item->paid_at ?? '-'
        ];
    }
}