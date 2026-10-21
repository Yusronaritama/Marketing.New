<?php

namespace App\Exports;

use App\Models\MasterEreg;
use App\Models\ProgramStudi;
use App\Models\SlikBilling;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UbahProdiExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $search = $this->request->search;

        $query = MasterEreg::with('user')
            ->when($search, function ($query) use ($search) {
                $query->where('nama', 'like', "%{$search}%")
                      ->orWhere('user_id', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($q) use ($search) {
                          $q->where('email', 'like', "%{$search}%")
                            ->orWhereHas('slikBilling', function($qSlik) use ($search) {
                                $qSlik->where('order_id', 'like', "%{$search}%")
                                      ->orWhere('virtual_account', 'like', "%{$search}%");
                            });
                      });
            });

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID Registrasi',
            'Nama Mahasiswa',
            'Email',
            'No. HP',
            'Prodi Sebelum Pindah',
            'Prodi Saat Ini / Tujuan',
            'Total UKT Terbayar',
            'Status Pindah Prodi (Tagihan Selisih)',
            'Nominal VA Selisih',
            'Nomor VA Selisih'
        ];
    }

    public function map($ereg): array
    {
        // Get Prodi Name
        $prodi = ProgramStudi::where('kode_prodi', $ereg->jurusan_lulusan)->first();
        $namaProdi = $prodi ? $prodi->nama_prodi : ($ereg->jurusan_lulusan ?? 'Belum Ditentukan');

        // Nominal Lunas Keseluruhan (Hanya UKT Normal)
        $pembayaranLunas = SlikBilling::where('user_id', $ereg->user_id)
            ->where('order_id', 'not like', '%SELISIH%')
            ->whereIn('status', ['paid', 'settlement', 'success'])
            ->sum('amount');

        // Tagihan UKT Normal pertama untuk melihat Prodi Awal
        $normalUKT = SlikBilling::where('user_id', $ereg->user_id)
            ->where('order_id', 'not like', '%SELISIH%')
            ->orderBy('id', 'asc')
            ->first();
            
        $prodiSebelumPindah = $normalUKT ? $normalUKT->nama_jurusan : '-';

        // VA Selisih (Ambil yang terbaru)
        $tagihanSelisih = SlikBilling::where('user_id', $ereg->user_id)
            ->where('order_id', 'like', '%SELISIH%')
            ->orderBy('id', 'desc')
            ->first();

        if ($tagihanSelisih) {
            if (in_array($tagihanSelisih->status, ['paid', 'settlement', 'success'])) {
                $statusSelisih = 'Selisih Lunas';
                // Jika lunas, prodi saat ini adalah prodi di master_eregs
                $prodiTujuan = $namaProdi;
            } else {
                $statusSelisih = 'Menunggu Pembayaran';
                // Jika masih pending, prodi tujuannya ada di dalam record selisih
                $prodiTujuan = $tagihanSelisih->nama_jurusan;
            }
        } else {
            $statusSelisih = 'Tidak Ada Tagihan Pindah';
            $prodiTujuan = $namaProdi;
        }

        $nominalSelisih = $tagihanSelisih ? $tagihanSelisih->amount : 0;
        $noVaSelisih = $tagihanSelisih ? $tagihanSelisih->virtual_account : '-';

        return [
            $ereg->user_id,
            $ereg->nama ?? ($ereg->user->name ?? '-'),
            $ereg->gmail ?? ($ereg->user->email ?? '-'),
            $ereg->phone ?? '-',
            $prodiSebelumPindah,
            $prodiTujuan,
            $pembayaranLunas,
            $statusSelisih,
            $nominalSelisih,
            $noVaSelisih
        ];
    }
}
