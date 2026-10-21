<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

// ── Model Imports ──────────────────────────────────────────────────────────────
use App\Models\Biodata;
use App\Models\MasterEreg;
use App\Models\MasterUser;
use App\Models\MasterMoodle;
use App\Models\MasterMoodleLulus;
use App\Models\SlikBilling;
use App\Models\UjianCountdown;
use App\Models\GroupGelombang;
use App\Models\DataMahasiswaFinal;
use App\Models\PendaftarGetNim;

// ── Model (Tidak Aktif — Cadangan Ujian Farmasi Tahap 2) ──────────────────────
// use App\Models\FarmasiVerification;
// use App\Models\JadwalUjianFarmasi;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // ======================================================================
        // INISIALISASI VARIABEL DEFAULT
        // ======================================================================
        $currentStep      = 1;
        $biodataSelesai   = false;
        $moodleUsername   = '-';
        $ujianCountdown   = null;
        $ujianOpen        = false;
        $pesanUjian       = "Jadwal ujian belum tersedia.";
        $isUjianSelesai   = false;
        $statusKelulusan  = null;
        $jurusanDiterima  = '-';
        $stuckFarmasi     = false;
        $stuckAkademik    = false;
        $isBayarLunas     = false;
        $teksInfoAkademik = null;
        $activeBill       = null;
        
        
       
        
        
        

        // ======================================================================
        // STEP 0 — PROTEKSI DASHBOARD: CEK STATUS PEMBAYARAN AWAL
        // ======================================================================

        $masterUser = MasterUser::where('user_id', $user->id)->first();

        // Redirect khusus untuk profesi Apoteker Farmasi
        if ($user->profesi === 'Apoteker Farmasi') {
            return redirect()->route('profesi.dashboard');
        }

        // Tolak akses jika data master belum ada atau VA masih pending
        if (!$masterUser || $masterUser->va_status === 'pending') {
            return redirect()->route('mahasiswa.va.dashboard')
                ->with('warning', 'Silahkan selesaikan pembayaran pendaftaran terlebih dahulu untuk mengakses Dashboard Mahasiswa.');
        }

        // ======================================================================
        // STEP 1 — CEK KELENGKAPAN BIODATA
        // ======================================================================

        $biodata = Biodata::where('user_id', $user->id)->latest()->first();

        if ($biodata && !empty($biodata->nik)) {
            $biodataSelesai = true;
        }

        // ======================================================================
        // STEP 2 — CEK DATA REGISTRASI / E-REG
        // ======================================================================

        $ereg = MasterEreg::where('user_id', $user->id)->latest()->first();

        // ======================================================================
        // STEP 3 — AMBIL JADWAL UJIAN AKTIF
        // ======================================================================

        $jadwal = UjianCountdown::where('is_active', 1)->latest()->first();
        // $jadwalUjianFarmasi = JadwalUjianFarmasi::where('is_active', true)->latest()->first();

        if ($jadwal) {
            $now   = Carbon::now();
            $start = Carbon::parse($jadwal->start_date);
            $end   = Carbon::parse($jadwal->end_date);

            $ujianCountdown = $jadwal;

            if ($now->between($start, $end)) {
                $ujianOpen  = true;
                $pesanUjian = "Ujian Seleksi Online Sedang Berlangsung!";
            } elseif ($now->lt($start)) {
                $ujianOpen  = false;
                $pesanUjian = "Ujian akan segera dimulai.";
            } else {
                $ujianOpen  = false;
                $pesanUjian = "Jadwal ujian telah berakhir.";
            }
        }

        // ======================================================================
        // STEP 4 — SIAPKAN KREDENSIAL MOODLE (Username & Password dari No. HP)
        // ======================================================================

        $rawPhone      = $user->phone ?? $user->no_hp ?? '-';
        $ujianPassword = preg_replace('/[^0-9]/', '', $rawPhone);

        // Ubah awalan +62 / 62 menjadi 0
        if (substr($ujianPassword, 0, 2) === '62') {
            $ujianPassword = '0' . substr($ujianPassword, 2);
        }

        // ======================================================================
        // STEP 5 — DATA KELULUSAN (LOGIKA INTI: JALUR KONVERSI vs. REGULER)
        // ======================================================================

        $vaKonversi      = null;
        $isJalurKonversi = false; // True jika pendaftar masuk via jalur pindahan/RPL/alih kredit
        $isKonversiLunas = true;  // Default true untuk jalur Reguler (tidak perlu cek VA konversi)

        if ($masterUser) {

            // ── A. Ambil Username Moodle ──────────────────────────────────────
            $akunMoodle = MasterMoodle::where('master_user_id', $masterUser->id)->first();
            if ($akunMoodle) {
                $moodleUsername = $akunMoodle->username;
            }

            // ── B. Ambil Hasil Ujian Moodle (Sumber Utama Kelulusan Reguler) ─
            $hasilUjian = MasterMoodleLulus::where('master_user_id', $masterUser->id)->first();

            // ── C. Identifikasi Jalur Konversi ────────────────────────────────
            // Keyword yang termasuk jalur konversi: pindahan, ekstensi, RPL, lanjutan,
            // konversi, alih kredit, transfer
            $userJalur       = strtolower($ereg->jalur_pendaftaran ?? '-');
            $isJalurKonversi = Str::contains($userJalur, [
                'pindahan', 'ekstensi', 'rpl', 'lanjutan',
                'konversi', 'alih', 'transfer',
            ]);

            // ──────────────────────────────────────────────────────────────────
            // JALUR KONVERSI (Bypass Moodle — Wajib Validasi Akademik + VA)
            // ──────────────────────────────────────────────────────────────────
            if ($isJalurKonversi) {

                // Anak konversi wajib bayar tagihan konversi terlebih dahulu
                $isKonversiLunas = false;
                $isUjianSelesai  = true; // Tidak perlu mengikuti ujian Moodle

                // Cek status VA Konversi melalui API SLIK
                if ($masterUser && $masterUser->trx_id) {
                    $vaKonversi = app(\App\Services\UktMandiriApi::class)
                        ->getConversionVaStatus($masterUser->trx_id);
                }

                if ($vaKonversi) {
                    $isKonversiLunas = (
                        isset($vaKonversi['status_pembayaran']) &&
                        strtolower($vaKonversi['status_pembayaran']) === 'sudah'
                    );
                }
                $isKonversiLunas = true;

                // Nama prodi fallback (dari hasil moodle atau pilihan ereg awal)
                $fallbackProdi = $hasilUjian->nama_prodi_lulus ?? $ereg->jurusan1 ?? 'Program Studi Pilihan';

                // Ambil data verifikasi akademik dari tabel pengelolaan_jalur
                $verifAkademik = \App\Models\PengelolaanJalur::where('user_id', $user->id)->first();

                if ($verifAkademik) {
                    // Admin sudah membuatkan data verifikasi
                    $teksInfoAkademik = $verifAkademik->teks_informasi
                        ?? 'Silakan lengkapi pemberkasan konversi SKS Anda ke bagian Akademik.';

                    if ($verifAkademik->status_verifikasi == 1) {
                        // Verifikasi disetujui
                        $statusKelulusan = 'LULUS';
                        $jurusanDiterima = $verifAkademik->prodi_diterima ?? $fallbackProdi;
                    } else {
                        // Masih menunggu persetujuan akademik
                        $statusKelulusan = 'LULUS_TAHAP_1';
                        $jurusanDiterima = $fallbackProdi . ' (Menunggu Verifikasi Akademik)';
                        $stuckAkademik   = true;
                    }
                } else {
                    // Admin belum membuat data verifikasi sama sekali
                    $teksInfoAkademik = 'Berkas pendaftaran Anda sedang dalam antrean pengecekan awal oleh Admin. Mohon cek kembali halaman ini secara berkala.';
                    $statusKelulusan  = 'LULUS_TAHAP_1';
                    $jurusanDiterima  = $fallbackProdi . ' (Menunggu Pendataan Admin)';
                    $stuckAkademik    = true;
                }

            // ──────────────────────────────────────────────────────────────────
            // JALUR REGULER (Kelulusan Ditentukan oleh Nilai Moodle)
            // ──────────────────────────────────────────────────────────────────
            } else {

                if ($hasilUjian) {
                    $isUjianSelesai = true;

                    if ($hasilUjian->status_kelulusan === 'LULUS') {
                        $statusKelulusan = 'LULUS';
                        $jurusanDiterima = $hasilUjian->nama_prodi_lulus;
                    } elseif ($hasilUjian->status_kelulusan === 'TIDAK LULUS') {
                        $statusKelulusan = 'TIDAK_LULUS';
                        $jurusanDiterima = 'TIDAK LULUS';
                    } else {
                        $statusKelulusan = 'MENUNGGU';
                    }
                } else {
                    // Belum ada data nilai di Moodle
                    $isUjianSelesai = false;
                }
            }
        }

        // ======================================================================
        // STEP 6 — STATUS PEMBAYARAN UKT
        // ======================================================================

        // ── A. Cek Riwayat Pelunasan UKT Utama ───────────────────────────────
        $historyLunas = SlikBilling::where('user_id', $user->id)
            ->where('order_id', 'like', 'UKT-%')
            ->where('order_id', 'not like', '%UKT-SELISIH%')
            ->where(function ($q) {
                $q->whereRaw('LOWER(status) = ?', ['paid'])
                  ->orWhereRaw('LOWER(status) = ?', ['lunas']);
            })
            ->first();

        if ($historyLunas) {
            $isBayarLunas = true;
        }

        // ── B. Cek Tagihan UKT Aktif (Belum Dibayar) ─────────────────────────
        $activeBill = SlikBilling::where('user_id', $user->id)
            ->where('order_id', 'like', 'UKT-%')
            ->where('order_id', 'not like', '%UKT-SELISIH%')
            ->where('is_active', 1)
            ->first();

        // Tandai tagihan aktif sebagai expired jika sudah melewati batas waktu
        if ($activeBill && $activeBill->isExpired() && $activeBill->status !== 'paid') {
            if ($activeBill->status !== 'expired') {
                $activeBill->update(['status' => 'expired']);
            }
        }

        // ── C. Cek Tagihan Selisih UKT (Akibat Pindah Program Studi) ─────────
        $tagihanSelisihAktif = SlikBilling::where('user_id', $user->id)
            ->where('order_id', 'like', '%UKT-SELISIH%')
            ->where('is_active', 1)
            ->whereRaw('LOWER(status) != ?', ['paid'])
            ->latest()
            ->first();

        $lunasSelisih = SlikBilling::where('user_id', $user->id)
            ->where('order_id', 'like', '%UKT-SELISIH%')
            ->whereRaw('LOWER(status) IN (?, ?)', ['paid', 'lunas'])
            ->latest()
            ->first();

        // ======================================================================
        // STEP 7 — EKSEKUSI PINDAH PRODI & SINKRONISASI KUOTA
        // (Dipicu setelah tagihan selisih lunas)
        // ======================================================================

        if ($lunasSelisih) {
            $isBayarLunas   = true;
            $prodiBaruLunas = \App\Models\ProgramStudi::where('kode_prodi', $lunasSelisih->kode_prodi)->first();

            // Perbarui nama prodi yang ditampilkan ke mahasiswa
            if ($prodiBaruLunas) {
                $jurusanDiterima = ucwords(strtolower($prodiBaruLunas->nama_prodi));
            }

            // Jalankan proses pindah prodi & sync kuota jika belum dilakukan
            if ($ereg && $ereg->jurusan_lulusan !== $lunasSelisih->kode_prodi) {
                $kodeJurusanLama = $ereg->jurusan_lulusan;
                $kodeJurusanBaru = $lunasSelisih->kode_prodi;

                // Update kode prodi di tabel master_eregs
                $ereg->update(['jurusan_lulusan' => $kodeJurusanBaru]);

                // Selaraskan nama prodi di tagihan UKT asal
                SlikBilling::where('user_id', $ereg->user_id)
                    ->where('order_id', 'like', '%UKT%')
                    ->where('order_id', 'not like', '%UKT-SELISIH%')
                    ->whereIn('status', ['paid', 'lunas'])
                    ->update([
                        'nama_jurusan' => $prodiBaruLunas->nama_prodi ?? $kodeJurusanBaru,
                    ]);

                // Kembalikan kuota prodi lama
                if ($kodeJurusanLama) {
                    $prodiLama = \App\Models\ProgramStudi::where('kode_prodi', $kodeJurusanLama)->first();
                    if ($prodiLama) $prodiLama->syncDayaTampung();
                }

                // Kurangi kuota prodi baru
                if ($kodeJurusanBaru) {
                    $prodiBaru = \App\Models\ProgramStudi::where('kode_prodi', $kodeJurusanBaru)->first();
                    if ($prodiBaru) $prodiBaru->syncDayaTampung();
                }
            }
        }

        // Override status pembayaran jika masih ada tagihan selisih yang pending
        if ($tagihanSelisihAktif) {
            $isBayarLunas = false;
            $activeBill   = $tagihanSelisihAktif;

            $prodiTujuan = \App\Models\ProgramStudi::where('kode_prodi', $tagihanSelisihAktif->kode_prodi)->first();
            if ($prodiTujuan) {
                $jurusanDiterima = ucwords(strtolower($prodiTujuan->nama_prodi)) . ' (Menunggu Pelunasan Selisih)';
            }
        }

        // ======================================================================
        // STEP 8 — CEK KEBUTUHAN POPUP UKURAN ALMAMATER
        // ======================================================================

        $needsAlmamater = false;

        // Tampilkan popup hanya jika sudah lunas & ukuran almamater belum diisi
        if ($isBayarLunas && $ereg && empty($ereg->ukuran_almamater)) {
            $needsAlmamater = true;
        }

        // ======================================================================
        // STEP 9 — AMBIL DAFTAR GROUP WHATSAPP GELOMBANG
        // ======================================================================

        $listGroupWA = collect([]);

        // Hanya ambil list grup jika proses pendaftaran sudah selesai / final
        if ($ereg && ($ereg->status == 'completed' || $ereg->status == 'final')) {
            try {
                $listGroupWA = GroupGelombang::active()->ordered()->get();
            } catch (\Exception $e) {
                // Abaikan error agar dashboard tetap bisa ditampilkan
            }
        }

        // ======================================================================
        // STEP 10 — CEK DATA FINAL MAHASISWA (PEMBERKASAN)
        // ======================================================================

        $dataFinalRecord    = DataMahasiswaFinal::where('user_id', $user->id)->first();
        $isDataFinalAdaDiDB = ($dataFinalRecord && $dataFinalRecord->status_data == 'completed');

        // ======================================================================
        // STEP 11 — CEK SISA KUOTA PROGRAM STUDI (GEMBOK ANTI-OVERLOAD)
        // ======================================================================

        $kuotaHabis     = false;
        $pesanKuotaHabis = "Mohon Maaf, kuota untuk Program Studi {$jurusanDiterima} sudah terisi penuh. Silakan hubungi Admin PMB untuk proses pindah Program Studi.";

        // Cek kuota hanya jika status LULUS murni, belum bayar, & bukan jalur konversi stuck
        if (
            $statusKelulusan == 'LULUS' &&
            !$isBayarLunas &&
            !$stuckAkademik &&
            $jurusanDiterima !== '-' &&
            $jurusanDiterima !== 'TIDAK LULUS'
        ) {
            $prodiCek = \App\Models\ProgramStudi::where('nama_prodi', $jurusanDiterima)->first();

            if ($prodiCek && $prodiCek->kuota <= 0) {
                $kuotaHabis = true;
            }
        }

        // ======================================================================
        // STEP 12 — PENENTUAN FLOW STEP (PROGRESS PENDAFTARAN MAHASISWA)
        // ======================================================================

        $currentStep = 1;

        // Step 2: Registrasi Prodi — jika biodata sudah lengkap
        if ($biodataSelesai) {
            $currentStep = 2;
        }

        // Step 3: Ujian Seleksi — jika sudah memilih prodi (ereg selesai)
        if ($currentStep == 2 && $ereg && ($ereg->status == 'completed' || $ereg->status == 'final')) {
            $currentStep = 3;
        }

        // Step 4: Pembayaran — jika lulus (dan tidak stuck), sudah bayar, atau jalur konversi
        if ($currentStep == 3 && (
            ($statusKelulusan == 'LULUS' && !$stuckAkademik && !$kuotaHabis) ||
            $isBayarLunas ||
            $isJalurKonversi
        )) {
            $currentStep = 4;
        }

        // Step 5: Data Final (Pemberkasan) — jika lunas & tidak ada hambatan konversi/akademik
        if ($currentStep == 4 && $isBayarLunas && $isKonversiLunas && !$stuckAkademik) {
            $currentStep = 5;
        }

        // Step 6: Selesai / Dashboard Maba — jika data final sudah lengkap
        if ($currentStep == 5 && $isDataFinalAdaDiDB) {
            $currentStep = 6;
        }

        // ======================================================================
        // STEP 13 — AMBIL DATA AKUN MAHASISWA (NIM) & AKUN E-LEARNING PKKMB
        // ======================================================================

        $dataAkun = DB::table('pendaftar_get_nim')
            ->where('user_id', $user->id)
            ->first();

        $akunSudahAda = ($dataAkun && !empty($dataAkun->nim));

        // Kode Daftar / Nomor Pendaftaran Mahasiswa
        $kodeDaftar = $masterUser->trx_id ?? ($ereg->trx_id ?? ($dataAkun->nim ?? '-'));

        // Cek status pembuatan akun E-Learning PKKMB
        $isPkkmbReady = ($dataAkun && $dataAkun->message_elearning === 'Success');
        $pkkmbAccount = null;

        if ($isPkkmbReady) {
            $trxIdRaw      = !empty($masterUser?->trx_id) ? $masterUser->trx_id : ($dataAkun->nim ?? $user->id);
            $cleanTrxId    = preg_replace('/[^0-9]/', '', (string)$trxIdRaw);
            $pkkmbUsername = strtolower('pkkmb-26' . $cleanTrxId);

            $rawPhone      = preg_replace('/[^0-9]/', '', (string)($user->phone ?? ($biodata->handphone ?? '')));
            $passwordBase  = !empty($rawPhone) ? $rawPhone : (string)$cleanTrxId;
            $pkkmbPassword = $passwordBase . 'Ubp@26';

            $rawEmail      = strtolower(trim($dataAkun->email_mhs ?? ($user->email ?? '')));
            $pkkmbEmail    = !empty($rawEmail)
                ? (str_starts_with($rawEmail, 'pkkmb.') ? $rawEmail : 'pkkmb.' . $rawEmail)
                : strtolower('pkkmb.' . $pkkmbUsername . '@mhs.ubpkarawang.ac.id');

            $pkkmbAccount = [
                'username'  => $pkkmbUsername,
                'password'  => $pkkmbPassword,
                'email'     => $pkkmbEmail,
                'course_id' => 1595,
                'login_url' => 'https://elearning.ubpkarawang.ac.id/course/view.php?id=1595',
            ];
        }
        
        // ======================================================================
        // 13.1 Modular untuk akun SIPT, akun BOC & Barcode Presensi
        // ======================================================================
        
        // --- 1. LOGIKA AKUN SIPT ---
        $siptReady = $akunSudahAda;

        // --- 2. LOGIKA AKUN BOC / PKKMB ---
        $trxIdMaster = $masterUser ? trim($masterUser->trx_id) : null;
        $noHp        = $user->phone ? trim($user->phone) : null;

        $bocReady = ($trxIdMaster && $noHp);
        $bocUser  = $trxIdMaster ? 'pkkmb-26' . $trxIdMaster : '-';
        $bocPass  = $noHp ? $noHp . 'Ubp@26' : '-';

        // --- 3. LOGIKA BARCODE PRESENSI ---
        $billingLunas = SlikBilling::where('user_id', $user->id)
            ->where('order_id', 'like', 'UKT-%')
            ->whereIn(DB::raw('LOWER(status)'), ['paid', 'lunas'])
            ->first();

        $trxId            = $billingLunas ? trim($billingLunas->trx_id) : null;
        $fileName         = 'qrcode-id-' . $trxId . '.png';
        $filePhysicalPath = public_path('assets/QrCodePresensi/' . $fileName);
        
        $isFileReady = $trxId && file_exists($filePhysicalPath);
        $fileUrl     = asset('assets/QrCodePresensi/' . $fileName);
        
        

        // ======================================================================
        // STEP 14 — CEK POPUP VALIDASI ULANG DATA FINAL
        // Hapus blok ini setelah masa validasi ulang selesai
        // ======================================================================

        $showResetPopup  = false;
        $dataFinalRecord = DataMahasiswaFinal::where('user_id', $user->id)->first();

        // Tampilkan popup jika status_reset kosong atau belum dikonfirmasi
        if ($dataFinalRecord && ($dataFinalRecord->status_reset == 0 || is_null($dataFinalRecord->status_reset))) {
            $showResetPopup = true;
        }

        // ======================================================================
        // RENDER VIEW DASHBOARD
        // ======================================================================

        return view('mahasiswa.dashboard', compact(
            'user',
            'currentStep',
            'biodata',
            'ereg',
            'ujianCountdown',
            'ujianOpen',
            'pesanUjian',
            'moodleUsername',
            'ujianPassword',
            'isUjianSelesai',
            'jurusanDiterima',
            'statusKelulusan',
            'isBayarLunas',
            'activeBill',
            'listGroupWA',
            'biodataSelesai',
            'isDataFinalAdaDiDB',
            'needsAlmamater',
            'dataAkun',
            'akunSudahAda',
            'kodeDaftar',
            'isPkkmbReady',
            'pkkmbAccount',
            'stuckAkademik',
            'teksInfoAkademik',
            'showResetPopup',
            'tagihanSelisihAktif',
            'lunasSelisih',
            'vaKonversi',
            'kuotaHabis',
            'pesanKuotaHabis',
            'isKonversiLunas',
            'isJalurKonversi',
            // --- VARIABEL BARU UNTUK SIPT, BOC & BARCODE ---
            'siptReady',
            'bocReady',
            'bocUser',
            'bocPass',
            'trxId',
            'fileName',
            'isFileReady',
            'fileUrl'
        ));
    }
}