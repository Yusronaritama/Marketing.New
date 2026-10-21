<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class SlikExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting, ShouldAutoSize
{
    protected $request;

    // Passing request dari controller untuk mempertahankan filter aktif
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Ambil data dari database berdasarkan filter aktif
     */
    public function collection()
    {
        $query = User::with(['slikBilling', 'biodata', 'masterEreg'])
            ->where('role', 'mahasiswa_baru')
            ->whereHas('slikBilling');

        // Terapkan filter Status Slik
        if ($this->request->filled('status_slik')) {
            $statusSlik = strtolower($this->request->status_slik);
            $query->whereHas('slikBilling', function($q) use ($statusSlik) {
                if ($statusSlik === 'paid') {
                    $q->whereIn(DB::raw('LOWER(status)'), ['paid', 'settlement', 'success', '00', 'capture', 'lunas']);
                } else {
                    $q->where(DB::raw('LOWER(status)'), $statusSlik);
                }
            });
        }

        // Terapkan filter Pencarian Global
        if ($this->request->filled('search')) {
            $search = str_replace(' ', '%', trim($this->request->search));
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('slikBilling', function($qSlik) use ($search) {
                      $qSlik->where('virtual_account', 'like', "%{$search}%")
                            ->orWhere('order_id', 'like', "%{$search}%");
                  });
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Judul Header Kolom di Excel
     */
    public function headings(): array
    {
        return [
            'ID Billing', 
            'Order ID', 
            'Virtual Account', 
            'Nama Pendaftar (Slik)', 
            'Email Akun', 
            'No HP', 
            'Program Studi', 
            'Jumlah Tagihan', 
            'Status', 
            'Ref No (NTB)', 
            'Waktu Pembayaran'
        ];
    }

    /**
     * Mapping / Mapping data per baris
     */
    public function map($user): array
    {
        $billing = $user->slikBilling()->orderBy('updated_at', 'desc')->first();

        if (!$billing) {
            return [];
        }

        return [
            $billing->id,
            $billing->order_id,
            $billing->virtual_account, // Format string murni akan dihandle oleh ColumnFormatting
            $billing->customer_name ?? $user->name,
            $user->email,
            $user->phone ?? $user->biodata->no_telepon ?? '',
            $billing->nama_jurusan ?? '',
            $billing->amount, // Biarkan berupa float/numeric agar bisa diformat mata uang di Excel
            strtoupper($billing->status),
            $billing->payment_ntb ?? '-',
            $billing->datetime_payment ?? '-'
        ];
    }

    /**
     * Memastikan nomor VA dan nominal uang terformat dengan benar di Excel (Mencegah E+15)
     */
    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_TEXT,      // Kolom C (Virtual Account) dipaksa menjadi Text murni
            'H' => '#,##0',                        // Kolom H (Jumlah Tagihan) diformat angka ribuan
        ];
    }
}