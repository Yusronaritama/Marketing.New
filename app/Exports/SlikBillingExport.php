<?php

namespace App\Exports;

use App\Models\User;
use App\Models\ProgramStudi; // Tambahkan model ProgramStudi untuk mengambil nama prodi
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SlikBillingExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    protected $request;

    public function __construct($request)
    {
        // Tangkap request untuk memfilter data yang di-export
        $this->request = $request;
    }

    public function collection()
    {
        // 1. Ambil semua data prodi untuk mapping kode => nama (menghindari N+1 query dalam loop)
        $prodiMap = ProgramStudi::pluck('nama_prodi', 'kode_prodi')->toArray();

        // 2. Ambil data User beserta relasi terkait billing
        $query = User::with(['slikBilling', 'masterUser', 'biodata', 'masterEreg'])
                     ->where('role', 'mahasiswa_baru');

        // 3. Filter Pencarian Global (berlaku untuk Registrasi dan Pembayaran Tahap Awal)
        if ($this->request->filled('search')) {
            $search = $this->request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('biodata', function($qBio) use ($search) {
                      $qBio->where('nama_lengkap', 'like', "%{$search}%");
                  })
                  ->orWhereHas('slikBilling', function($qSlik) use ($search) {
                      $qSlik->where('virtual_account', 'like', "%{$search}%")
                            ->orWhere('order_id', 'like', "%{$search}%");
                  })
                  ->orWhereHas('masterUser', function($qMaster) use ($search) {
                      $qMaster->where('va_number', 'like', "%{$search}%")
                              ->orWhere('trx_id', 'like', "%{$search}%");
                  });
            });
        }

        $users = $query->get();
        $exportData = collect();
        $statusFilter = $this->request->filled('status') ? strtolower($this->request->status) : null;

        foreach ($users as $user) {
            // Ambil nama lengkap mahasiswa
            $nama = $user->biodata->nama_lengkap ?? $user->name;
            
            // Konversi kode prodi dari masterEreg menjadi nama prodi menggunakan array map
            $kodeProdi = $user->masterEreg->jurusan1 ?? null;
            $namaProdiEreg = $prodiMap[$kodeProdi] ?? '-';

            // --- A. DATA DARI SLIK BILLING (PEMBAYARAN TAHAP AWAL) ---
            if ($user->slikBilling) {
                $slikStatus = strtolower($user->slikBilling->status ?? '');
                // Filter status & kecualikan "cancel"
                if ((!$statusFilter || $slikStatus === $statusFilter) && $slikStatus !== 'cancel') {
                    
                    // Gunakan nama_jurusan bawaan tabel Slik, jika kosong/null gunakan hasil konversi Ereg
                    $prodiSlik = $user->slikBilling->nama_jurusan ?? $namaProdiEreg;

                    $exportData->push((object)[
                        'source'     => 'PEMBAYARAN TAHAP AWAL',
                        'nama'       => $nama,
                        'prodi'      => $prodiSlik, // Menampilkan Nama Prodi
                        'va'         => $user->slikBilling->virtual_account,
                        'order_id'   => $user->slikBilling->order_id,
                        'amount'     => $user->slikBilling->amount,
                        'status'     => $user->slikBilling->status,
                        'created_at' => $user->slikBilling->created_at,
                        'paid_at'    => $user->slikBilling->datetime_payment
                    ]);
                }
            }

            // --- B. DATA DARI MASTER USER (REGISTRASI) ---
            if ($user->masterUser && $user->masterUser->va_number) {
                $masterStatus = strtolower($user->masterUser->va_status ?? '');
                // Filter status & kecualikan "cancel"
                if ((!$statusFilter || $masterStatus === $statusFilter) && $masterStatus !== 'cancel') {
                    $exportData->push((object)[
                        'source'     => 'REGISTRASI',
                        'nama'       => $nama,
                        'prodi'      => $namaProdiEreg, // Menampilkan Nama Prodi (bukan kode prodi lagi)
                        'va'         => $user->masterUser->va_number,
                        'order_id'   => $user->masterUser->trx_id,
                        'amount'     => $user->masterUser->va_amount,
                        'status'     => $user->masterUser->va_status,
                        'created_at' => $user->masterUser->created_at,
                        'paid_at'    => $user->masterUser->paid_at
                    ]);
                }
            }
        }

        // Urutkan data gabungan berdasarkan tanggal dibuat terbaru
        return $exportData->sortByDesc('created_at');
    }

    public function headings(): array
    {
        return [
            'Sumber Data',
            'Nama Mahasiswa',
            'Prodi',
            'Virtual Account',
            'Order ID',
            'Nominal',
            'Status',
            'Tanggal Dibuat',
            'Tanggal Bayar'
        ];
    }

    public function map($item): array
    {
        return [
            $item->source,
            $item->nama,
            $item->prodi, // String nama prodi hasil konversi map
            "'" . $item->va, 
            $item->order_id,
            $item->amount,
            strtoupper($item->status ?? ''),
            $item->created_at,
            $item->paid_at ?? '-'
        ];
    }
}