<?php

namespace App\Services;

use App\Models\PendaftarGetNim;
use App\Models\ProgramStudi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NimGeneratorService
{
    private const KODE_UNIV = '4162';
    private const S2_BULAN_AKTIF = [4, 9];

    public function generateNim(
        string  $kodeProdi,
        string  $jenjang  = 'S1',
        ?Carbon $paidAt   = null
    ): string {
        $jenjang = strtoupper(trim($jenjang));

        if ($jenjang === 'S2' && !$this->isS2PeriodeAktif()) {
            throw new \RuntimeException(
                'NIM S2 hanya dapat digenerate di bulan April (4) atau September (9). ' .
                'Bulan sekarang: ' . Carbon::now()->month
            );
        }

        $prodi = ProgramStudi::where('kode_prodi', $kodeProdi)->first();
        if (!$prodi) {
            throw new \RuntimeException("Kode prodi '{$kodeProdi}' tidak ditemukan di tabel program_studi.");
        }

        $tahunAngkatan = $this->getTahunAngkatan();
        $digitTahun    = substr($tahunAngkatan, 2, 2);
        $prefix        = $digitTahun . self::KODE_UNIV . $kodeProdi;
        $nomorUrut     = $this->getNextNomorUrut($prefix, $kodeProdi, $paidAt);

        return $digitTahun
            . self::KODE_UNIV
            . $kodeProdi
            . str_pad($nomorUrut, 3, '0', STR_PAD_LEFT);
    }

    public function generateEmail(
        string $nama,
        string $kodeProdi,
        string $nim,
        string $jenjang = 'S1'
    ): string {
        $singkatan  = strtolower(ProgramStudi::getSingkatan($kodeProdi));
        $digitTahun = substr($this->getTahunAngkatan(), 2, 2);
        $prefix     = $singkatan . $digitTahun;
        $bagianNama = $this->buildNamaBagian($nama);
        $localPart  = $prefix . '.' . $bagianNama;

        if ($this->emailExists($localPart)) {
            $localPart = $prefix . '.' . $bagianNama . substr($nim, -3);
        }

        return $localPart . '@mhs.ubpkarawang.ac.id';
    }

    public function isS2PeriodeAktif(): bool
    {
        return in_array(Carbon::now()->month, self::S2_BULAN_AKTIF, true);
    }

    public function sanitizeAscii(string $teks): string
    {
        if (function_exists('transliterator_transliterate')) {
            $teks = transliterator_transliterate('Any-Latin; Latin-ASCII', $teks);
        } elseif (function_exists('iconv')) {
            $teks = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $teks);
        }
        return preg_replace('/[^\x20-\x7E]/', '', $teks);
    }

    private function getTahunAngkatan(): string
    {
        return (string) Carbon::now()->year;
    }

    private function getKodeSemester(): string
    {
        return Carbon::now()->month >= 8 ? '2' : '1';
    }

    private function getNextNomorUrut(
        string  $prefix,
        string  $kodeProdi,
        ?Carbon $paidAt
    ): int {
        return DB::transaction(function () use ($prefix, $kodeProdi, $paidAt) {
            $lastNim = PendaftarGetNim::where('nim', 'like', $prefix . '%')
                ->where('kode_prodi', $kodeProdi)
                ->orderByDesc('nim')
                ->lockForUpdate()
                ->value('nim');

            if (!$lastNim) {
                return 1;
            }

            return (int) substr($lastNim, -3) + 1;
        });
    }

    private function buildNamaBagian(string $nama): string
    {
        $nama = $this->sanitizeAscii($nama);
        $nama = strtolower(trim($nama));
        $nama = preg_replace('/[^a-z\s]/', '', $nama);
        $nama = preg_replace('/\s+/', ' ', $nama);
        $kata = array_values(array_filter(explode(' ', $nama)));

        return match (true) {
            count($kata) === 0 => 'mahasiswa',
            count($kata) === 1 => $kata[0],
            default            => $kata[0] . $kata[count($kata) - 1],
        };
    }

    private function emailExists(string $localPart): bool
    {
        return PendaftarGetNim::where('email_mhs', $localPart . '@mhs.ubpkarawang.ac.id')->exists();
    }
}