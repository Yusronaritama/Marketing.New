<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataMahasiswaFinal;
use App\Models\MasterUser;
use App\Models\MasterMoodleLulus;
use App\Models\SlikBilling;
use App\Models\PendaftarGetNim;
use App\Models\ProgramStudi;
use App\Services\NimGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GenerateNimController extends Controller
{
    /**
     * Kode prodi S2.
     * Dipusatkan di sini agar index(), getStats(), dan query lain konsisten.
     */
    private const KODE_S2 = ['61114'];
    private const KODE_APOTEKER = ['00000', '48901'];
    private const MOODLE_PKKMB_COURSE_ID = 1595;

    public function __construct(private NimGeneratorService $nimService) {}

    // =========================================================================
    // INDEX — daftar pendaftar yang memenuhi syarat, belum punya NIM
    // =========================================================================

    public function index(Request $request)
    {
        $base = fn() => DataMahasiswaFinal::with(['user', 'masterUser', 'masterEreg'])
            ->whereHas('masterUser', fn($q) =>
                $q->where('va_status', 'paid')->orWhere('jenis_beasiswa', 'pegawai')
            )
            ->whereDoesntHave('pendaftarGetNim')
        ->whereNotIn('data_mahasiswa_finals.user_id', function ($sub) {
            $sub->select('user_id')->from('pendaftar_get_nim')->whereNotNull('user_id');
        })
            ->whereNotIn('data_mahasiswa_finals.user_id', function ($sub) {
                $sub->select('user_id')->from('pendaftar_get_nim')->whereNotNull('user_id');
            });

        // — S1 —
        $nonS1Codes = array_merge(self::KODE_S2, self::KODE_APOTEKER);
        $queryS1 = $base()
            ->whereHas('masterEreg', fn($q) =>
                $q->whereRaw("COALESCE(NULLIF(NULLIF(jurusan_lulusan, ''), '00000'), jurusan1) NOT IN ('" . implode("','", $nonS1Codes) . "')")
                  ->whereRaw("COALESCE(NULLIF(NULLIF(jurusan_lulusan, ''), '00000'), jurusan1) IS NOT NULL")
            )
            ->whereDoesntHave('user', fn($q) => $q->where('profesi', 'Apoteker Farmasi'));

        if ($request->filled('search_s1')) {
            $s = $request->search_s1;
            $queryS1->where(fn($q) =>
                $q->where('nama_mahasiswa', 'like', "%$s%")
                  ->orWhere('handphone', 'like', "%$s%")
                  ->orWhereHas('user', fn($q2) => $q2->where('email', 'like', "%$s%"))
            );
        }

        if ($request->filled('prodi_s1')) {
            $queryS1->whereHas('masterEreg', fn($q) =>
                $q->whereRaw("COALESCE(NULLIF(NULLIF(jurusan_lulusan, ''), '00000'), jurusan1) = ?", [$request->prodi_s1])
            );
        }

        if ($request->filled('jalur_s1')) {
            $queryS1->whereHas('masterEreg', fn($q) => $q->where('jalur_pendaftaran', $request->jalur_s1));
        }

        $pendaftarS1 = $queryS1
            ->orderBy(function ($q) {
                // Diurutkan berdasarkan tanggal bayar (paid_at) sesuai aturan
                $q->select('paid_at')
                  ->from('master_users')
                  ->whereColumn('master_users.user_id', 'data_mahasiswa_finals.user_id')
                  ->limit(1);
            })
            ->paginate($request->integer('per_page_s1', 15), ['*'], 'page_s1')
            ->withQueryString();

        // — S2 —
        $s2Codes = self::KODE_S2;
        $queryS2 = $base()->whereHas('masterEreg', fn($q) =>
            $q->whereRaw("COALESCE(NULLIF(NULLIF(jurusan_lulusan, ''), '00000'), jurusan1) IN ('" . implode("','", $s2Codes) . "')")
        );

        if ($request->filled('search_s2')) {
            $s = $request->search_s2;
            $queryS2->where(fn($q) =>
                $q->where('nama_mahasiswa', 'like', "%$s%")
                  ->orWhere('handphone', 'like', "%$s%")
                  ->orWhereHas('user', fn($q2) => $q2->where('email', 'like', "%$s%"))
            );
        }

        if ($request->filled('jalur_s2')) {
            $queryS2->whereHas('masterEreg', fn($q) => $q->where('jalur_pendaftaran', $request->jalur_s2));
        }

        $pendaftarS2 = $queryS2
            ->orderBy(function ($q) {
                $q->select('paid_at')
                  ->from('master_users')
                  ->whereColumn('master_users.user_id', 'data_mahasiswa_finals.user_id')
                  ->limit(1);
            })
            ->paginate($request->integer('per_page_s2', 15), ['*'], 'page_s2')
            ->withQueryString();

        // Dropdown filter prodi S1 — gunakan 'aktif' (string), bukan 1 (integer)
        // ── Apoteker (Profesi) ───────────────────────────────────────────────
        $apotekerCodes = self::KODE_APOTEKER;
        $queryApoteker = $base()->where(function ($q) use ($apotekerCodes) {
            $q->whereHas('user', fn($q2) => $q2->where('profesi', 'Apoteker Farmasi'))
              ->orWhereHas('masterEreg', fn($q3) =>
                  $q3->whereRaw("COALESCE(NULLIF(NULLIF(jurusan_lulusan, ''), '00000'), jurusan1) IN ('" . implode("','", $apotekerCodes) . "')")
              );
        });

        if ($request->filled('search_apoteker')) {
            $s = $request->search_apoteker;
            $queryApoteker->where(fn($q) =>
                $q->where('nama_mahasiswa', 'like', "%" . $s . "%")
                  ->orWhere('handphone', 'like', "%" . $s . "%")
                  ->orWhereHas('user', fn($q2) => $q2->where('email', 'like', "%" . $s . "%"))
            );
        }

        if ($request->filled('jalur_apoteker')) {
            $queryApoteker->whereHas('masterEreg', fn($q) => $q->where('jalur_pendaftaran', $request->jalur_apoteker));
        }

        $pendaftarApoteker = $queryApoteker
            ->latest()
            ->paginate(15, ['*'], 'page_apoteker')
            ->appends($request->except('page_apoteker'));

        $prodiS1List = ProgramStudi::where('jenjang', 'S1')
            ->where('status_prodi', 'aktif')
            ->orderBy('nama_prodi')
            ->get(['kode_prodi', 'nama_prodi']);

        $activeTab = $request->get('tab', 'S1');
        $stats     = $this->getStats();

        return view('admin.generate-nim.index', compact(
            'pendaftarS1', 'pendaftarS2', 'pendaftarApoteker', 'prodiS1List', 'stats', 'activeTab'
        ));
    }

    // =========================================================================
    // GENERATE NIM — 1 per 1 (sesuai ketentuan)
    // =========================================================================

    public function generate(Request $request, int $id)
    {
        $mahasiswa = DataMahasiswaFinal::with(['user', 'masterUser', 'masterEreg'])
            ->findOrFail($id);

        $eligibility = $this->checkEligibility($mahasiswa);
        if (!$eligibility['eligible']) {
            return back()->with('error', $eligibility['reason']);
        }

        $kodeProdi = $eligibility['kode_prodi'];
        $prodi     = ProgramStudi::where('kode_prodi', $kodeProdi)->first();

        if (!$prodi) {
            return back()->with('error', "Program studi tidak ditemukan: {$kodeProdi}");
        }

        $jenjang = $prodi->jenjang; // 'S1' atau 'S2'

        if ($jenjang === 'S2' && !$this->nimService->isS2PeriodeAktif()) {
            return back()->with('error',
                'Periode penerimaan S2 hanya dibuka pada bulan April dan September.'
            );
        }

        DB::beginTransaction();
        try {
            // Teruskan paid_at agar urutan NIM sesuai tanggal bayar
            $paidAt = $mahasiswa->masterUser?->paid_at
                ? \Carbon\Carbon::parse($mahasiswa->masterUser->paid_at)
                : null;

            $nim   = $this->nimService->generateNim($kodeProdi, $jenjang, $paidAt);
            $email = $this->nimService->generateEmail($mahasiswa->nama_mahasiswa, $kodeProdi, $nim, $jenjang);

            PendaftarGetNim::create([
                'data_mahasiswa_final_id' => $mahasiswa->id,
                'user_id'                 => $mahasiswa->user_id,
                'nim'                     => $nim,
                'email_mhs'               => $email,
                'kode_prodi'              => $kodeProdi,
                'jenjang'                 => $jenjang,
                'id_periode_masuk'        => $this->getPeriodeMasuk($jenjang),
                'generated_by'            => auth()->id(),
                'generated_at'            => now(),
                'syarat_type'             => $eligibility['type'],
                'message_sipt'            => null,
                'message_slik'            => null,
            ]);

            DB::commit();

            Log::info("NIM generated: {$nim} — {$mahasiswa->nama_mahasiswa} ({$email})");

            return back()->with('success',
                "NIM berhasil digenerate: <strong>{$nim}</strong> | Email: <strong>{$email}</strong>"
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Generate NIM gagal: ' . $e->getMessage(), ['id' => $id]);
            return back()->with('error', 'Gagal generate NIM: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // GENERATE ALL (Semua data yang belum dapat NIM tanpa perlu kirim IDs)
    // =========================================================================
    public function generateAll(Request $request)
    {
        $tab = $request->get('tab', 'S1'); // Default ke S1

        Log::info("--- MEMULAI GENERATE ALL NIM UNTUK JENJANG/TAB: [{$tab}] ---");

        // Query dasar: Lunas / Beasiswa Pegawai dan Belum Punya NIM
        $base = fn() => DataMahasiswaFinal::with(['user', 'masterUser', 'masterEreg'])
            ->whereHas('masterUser', fn($q) =>
                $q->where('va_status', 'paid')->orWhere('jenis_beasiswa', 'pegawai')
            )
            ->whereDoesntHave('pendaftarGetNim')
        ->whereNotIn('data_mahasiswa_finals.user_id', function ($sub) {
            $sub->select('user_id')->from('pendaftar_get_nim')->whereNotNull('user_id');
        })
            ->whereNotIn('data_mahasiswa_finals.user_id', function ($sub) {
                $sub->select('user_id')->from('pendaftar_get_nim')->whereNotNull('user_id');
            });

        // Filter berdasarkan Tab/Jenjang
        if ($tab === 'S1') {
            $nonS1Codes = array_merge(self::KODE_S2, self::KODE_APOTEKER);
            $query = $base()
                ->whereHas('masterEreg', fn($q) => 
                    $q->whereRaw("COALESCE(NULLIF(NULLIF(jurusan_lulusan, ''), '00000'), jurusan1) NOT IN ('" . implode("','", $nonS1Codes) . "')")
                      ->whereRaw("COALESCE(NULLIF(NULLIF(jurusan_lulusan, ''), '00000'), jurusan1) IS NOT NULL")
                )
                ->whereDoesntHave('user', fn($q) => $q->where('profesi', 'Apoteker Farmasi'));
        } elseif ($tab === 'S2') {
            $s2Codes = self::KODE_S2;
            $query = $base()
                ->whereHas('masterEreg', fn($q) => 
                    $q->whereRaw("COALESCE(NULLIF(NULLIF(jurusan_lulusan, ''), '00000'), jurusan1) IN ('" . implode("','", $s2Codes) . "')")
                );
        } elseif ($tab === 'Apoteker') {
            $apotekerCodes = self::KODE_APOTEKER;
            $query = $base()
                ->where(function ($q) use ($apotekerCodes) {
                    $q->whereHas('user', fn($q2) => $q2->where('profesi', 'Apoteker Farmasi'))
                      ->orWhereHas('masterEreg', fn($q3) => 
                          $q3->whereRaw("COALESCE(NULLIF(NULLIF(jurusan_lulusan, ''), '00000'), jurusan1) IN ('" . implode("','", $apotekerCodes) . "')")
                      );
                });
        } else {
            return back()->with('error', 'Kategori tab tidak valid.');
        }

        // Urutkan berdasarkan tanggal bayar (paid_at) agar urutan NIM konsisten
        $mahasiswaList = $query
            ->join('master_users', 'master_users.user_id', '=', 'data_mahasiswa_finals.user_id')
            ->orderBy('master_users.paid_at', 'asc')
            ->orderBy('data_mahasiswa_finals.id', 'asc')
            ->select('data_mahasiswa_finals.*')
            ->get();

        $totalDitemukan = $mahasiswaList->count();
        Log::info("Ditemukan {$totalDitemukan} pendaftar yang siap digenerate.");

        if ($totalDitemukan === 0) {
            return back()->with('error', 'Tidak ada data pendaftar yang memenuhi syarat untuk digenerate.');
        }

        $generated = [];
        $failed    = [];

        foreach ($mahasiswaList as $mahasiswa) {
            $eligibility = $this->checkEligibility($mahasiswa);
            if (!$eligibility['eligible']) {
                $failed[] = ['nama' => $mahasiswa->nama_mahasiswa, 'alasan' => $eligibility['reason']];
                Log::warning("Generate Dilewati: {$mahasiswa->nama_mahasiswa} (ID: {$mahasiswa->id}) - {$eligibility['reason']}");
                continue;
            }

            $kodeProdi = $eligibility['kode_prodi'];
            $prodi = ProgramStudi::where('kode_prodi', $kodeProdi)->first();

            if (!$prodi) {
                $alasan = "Prodi {$kodeProdi} tidak ditemukan";
                $failed[] = ['nama' => $mahasiswa->nama_mahasiswa, 'alasan' => $alasan];
                Log::warning("Generate Gagal: {$mahasiswa->nama_mahasiswa} - {$alasan}");
                continue;
            }

            $jenjang = $prodi->jenjang;

            if ($jenjang === 'S2' && !$this->nimService->isS2PeriodeAktif()) {
                $alasan = 'Periode S2 belum buka';
                $failed[] = ['nama' => $mahasiswa->nama_mahasiswa, 'alasan' => $alasan];
                Log::warning("Generate Gagal: {$mahasiswa->nama_mahasiswa} - {$alasan}");
                continue;
            }

            DB::beginTransaction();
            try {
                $paidAt = $mahasiswa->masterUser?->paid_at
                    ? \Carbon\Carbon::parse($mahasiswa->masterUser->paid_at)
                    : null;

                $nim   = $this->nimService->generateNim($kodeProdi, $jenjang, $paidAt);
                $email = $this->nimService->generateEmail($mahasiswa->nama_mahasiswa, $kodeProdi, $nim, $jenjang);

                PendaftarGetNim::create([
                    'data_mahasiswa_final_id' => $mahasiswa->id,
                    'user_id'                 => $mahasiswa->user_id,
                    'nim'                     => $nim,
                    'email_mhs'               => $email,
                    'kode_prodi'              => $kodeProdi,
                    'jenjang'                 => $jenjang,
                    'id_periode_masuk'        => $this->getPeriodeMasuk($jenjang),
                    'generated_by'            => auth()->id(),
                    'generated_at'            => now(),
                    'syarat_type'             => $eligibility['type'],
                ]);

                DB::commit();
                $generated[] = ['nama' => $mahasiswa->nama_mahasiswa, 'nim' => $nim, 'email' => $email];
                Log::info("Generate Sukses: [{$nim}] {$mahasiswa->nama_mahasiswa} ({$email})");
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("Generate Gagal untuk {$mahasiswa->nama_mahasiswa}: " . $e->getMessage());
                $failed[] = ['nama' => $mahasiswa->nama_mahasiswa, 'alasan' => $e->getMessage()];
            }
        }

        $countSuccess = count($generated);
        $countFailed  = count($failed);

        Log::info("--- PROSES SELESAI --- Berhasil: {$countSuccess} | Gagal: {$countFailed}");

        $message = "Berhasil men-generate {$countSuccess} NIM untuk jenjang {$tab}.";

        return back()->with([
            'success'        => $countSuccess > 0 ? $message : null,
            'error'          => $countSuccess === 0 ? 'Gagal men-generate NIM.' : null,
            'bulk_generated' => $generated,
            'bulk_failed'    => $failed,
        ]);
    }

    public function generateBulk(Request $request)
    {
        Log::info(">>> REQUEST MASUK KE generateBulk <<<", [
            'all_payload' => $request->all(),
            'auth_user'   => auth()->id(),
        ]);

        // Jika data 'ids' tidak lolos validasi, Laravel otomatis melempar redirect sebelum baris di bawah ini
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:data_mahasiswa_finals,id',
        ]);

        $totalDipilih = count($request->ids);
        Log::info("Validasi lolos. Memproses {$totalDipilih} pendaftar...");

        // Ambil semua mahasiswa sekaligus, diurutkan paid_at agar nomor urut NIM konsisten
        $mahasiswaList = DataMahasiswaFinal::with(['user', 'masterUser', 'masterEreg'])
            ->whereIn('data_mahasiswa_finals.id', $request->ids)
            ->join('master_users', 'master_users.user_id', '=', 'data_mahasiswa_finals.user_id')
            ->orderBy('master_users.paid_at', 'asc')
            ->orderBy('data_mahasiswa_finals.id', 'asc')
            ->select('data_mahasiswa_finals.*')
            ->get();

        $generated = [];
        $failed    = [];

        foreach ($mahasiswaList as $mahasiswa) {
            $eligibility = $this->checkEligibility($mahasiswa);
            if (!$eligibility['eligible']) {
                $failed[] = ['nama' => $mahasiswa->nama_mahasiswa, 'alasan' => $eligibility['reason']];
                Log::warning("Bulk Generate Dilewati: Mahasiswa {$mahasiswa->nama_mahasiswa} (ID: {$mahasiswa->id}) - Alasan: {$eligibility['reason']}");
                continue;
            }

            $kodeProdi = $eligibility['kode_prodi'];
            $prodi = ProgramStudi::where('kode_prodi', $kodeProdi)->first();

            if (!$prodi) {
                $alasan = "Prodi {$kodeProdi} tidak ditemukan";
                $failed[] = ['nama' => $mahasiswa->nama_mahasiswa, 'alasan' => $alasan];
                Log::warning("Bulk Generate Gagal: {$mahasiswa->nama_mahasiswa} (ID: {$mahasiswa->id}) - {$alasan}");
                continue;
            }

            $jenjang = $prodi->jenjang;

            if ($jenjang === 'S2' && !$this->nimService->isS2PeriodeAktif()) {
                $alasan = 'Periode S2 belum buka';
                $failed[] = ['nama' => $mahasiswa->nama_mahasiswa, 'alasan' => $alasan];
                Log::warning("Bulk Generate Gagal: {$mahasiswa->nama_mahasiswa} (ID: {$mahasiswa->id}) - {$alasan}");
                continue;
            }

            DB::beginTransaction();
            try {
                $paidAt = $mahasiswa->masterUser?->paid_at
                    ? \Carbon\Carbon::parse($mahasiswa->masterUser->paid_at)
                    : null;

                $nim   = $this->nimService->generateNim($kodeProdi, $jenjang, $paidAt);
                $email = $this->nimService->generateEmail($mahasiswa->nama_mahasiswa, $kodeProdi, $nim, $jenjang);

                PendaftarGetNim::create([
                    'data_mahasiswa_final_id' => $mahasiswa->id,
                    'user_id'                 => $mahasiswa->user_id,
                    'nim'                     => $nim,
                    'email_mhs'               => $email,
                    'kode_prodi'              => $kodeProdi,
                    'jenjang'                 => $jenjang,
                    'id_periode_masuk'        => $this->getPeriodeMasuk($jenjang),
                    'generated_by'            => auth()->id(),
                    'generated_at'            => now(),
                    'syarat_type'             => $eligibility['type'],
                ]);

                DB::commit();
                $generated[] = ['nama' => $mahasiswa->nama_mahasiswa, 'nim' => $nim, 'email' => $email];

                Log::info("Bulk Generate Berhasil: [{$nim}] {$mahasiswa->nama_mahasiswa} ({$email}) - Prodi: {$kodeProdi}");
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("Bulk Generate Gagal (Exception) untuk {$mahasiswa->nama_mahasiswa} (ID: {$mahasiswa->id}): " . $e->getMessage(), [
                    'trace' => $e->getTraceAsString()
                ]);
                $failed[] = ['nama' => $mahasiswa->nama_mahasiswa, 'alasan' => $e->getMessage()];
            }
        }

        $countSuccess = count($generated);
        $countFailed  = count($failed);

        Log::info("--- BULK GENERATE SELESAI --- Berhasil: {$countSuccess} | Gagal/Dilewati: {$countFailed}");

        $message = "Proses Generate Bulk Selesai. Berhasil: <strong>{$countSuccess}</strong>, Gagal/Dilewati: <strong>{$countFailed}</strong>.";

        return back()->with([
            'success'        => $countSuccess > 0 ? $message : null,
            'error'          => $countSuccess === 0 ? 'Tidak ada NIM yang berhasil digenerate.' : null,
            'bulk_generated' => $generated,
            'bulk_failed'    => $failed,
        ]);
    }

    private function processSendToSipt(PendaftarGetNim $record): array
    {
        $mahasiswa = $record->mahasiswa;
        $ereg      = $mahasiswa?->masterEreg;
        $user      = $record->user ?? $mahasiswa?->user;

        // Resolve id_agama
        $agamaMap = [1 => 'Islam', 2 => 'Kristen', 3 => 'Katholik', 4 => 'Hindu', 5 => 'Budha', 6 => 'Konghucu', 99 => 'Lainnya'];
        $idAgama  = $mahasiswa?->id_agama;
        if (!is_numeric($idAgama)) {
            $idAgama = array_search($idAgama, $agamaMap) ?: 1;
        }

        $safeDate = fn($date): string => (!$date || (string)$date === '0000-00-00') ? '0000-00-00' : (
            ($date instanceof \Carbon\Carbon) ? $date->format('Y-m-d') : (string) $date
        );

        $jenisKelamin = is_numeric($mahasiswa?->jenis_kelamin)
            ? ((int)$mahasiswa->jenis_kelamin === 1 ? 'P' : 'L')
            : ($mahasiswa?->jenis_kelamin ?? 'L');

        $endpointUrl = config('services.sipt.url', 'https://api-gateway.ubpkarawang.ac.id') . '/external/penmabar/create-user';

        $payload = [
            'mahasiswa' => [
                'nim'              => $record->nim,
                'id_periode_masuk' => $record->id_periode_masuk,
                'kode_prodi'       => $record->kode_prodi,
                'email'            => strtolower($record->email_mhs),
                'nama'             => $mahasiswa?->nama_mahasiswa ?? $user?->name ?? 'Mahasiswa',
                'status_bekerja'   => !empty($ereg?->status_pekerjaan) ? substr($ereg->status_pekerjaan, 0, 1) : '0',
                'status_aktif'     => 'aktif',
            ],
            'login' => [
                'email'    => strtolower($record->email_mhs),
                'password' => config('services.sipt.default_password', 'ubpk26'),
            ],
            'data_profil' => [
                'nim'                           => $record->nim,
                'update_biodata_at'             => $record->id_periode_masuk ?? '20262',
                'id_jenis_daftar'               => 1,
                'id_jalur_masuk'                => 6,
                'jenis_kelamin'                 => $jenisKelamin,
                'alamat'                        => $mahasiswa?->jalan ?? '-',
                'kelurahan'                     => $mahasiswa?->kelurahan ?? '-',
                'kode_pos'                      => $mahasiswa?->kode_pos ?? '00000',
                'nik'                           => $mahasiswa?->nik ?? '0000000000000000',
                'tempat_lahir'                  => $mahasiswa?->tempat_lahir ?? '-',
                'tanggal_lahir'                 => $safeDate($mahasiswa?->tanggal_lahir),
                'nama_ayah'                     => $mahasiswa?->nama_ayah ?? '-',
                'tanggal_lahir_ayah'            => $safeDate($mahasiswa?->tanggal_lahir_ayah),
                'nik_ayah'                      => $mahasiswa?->nik_ayah ?? '0000000000000000',
                'id_jenjang_pendidikan_ayah'    => (int) ($mahasiswa?->id_jenjang_pendidikan_ayah ?? 0),
                'id_pekerjaan_ayah'             => (int) ($mahasiswa?->id_pekerjaan_ayah ?? 0),
                'id_penghasilan_ayah'           => (int) ($mahasiswa?->id_penghasilan_ayah ?? 0),
                'id_kebutuhan_khusus_ayah'      => (int) ($mahasiswa?->id_kebutuhan_khusus_ayah ?? 0),
                'nama_ibu'                      => $mahasiswa?->nama_ibu_kandung ?? '-',
                'tanggal_lahir_ibu'             => $safeDate($mahasiswa?->tanggal_lahir_ibu),
                'nik_ibu'                       => $mahasiswa?->nik_ibu ?? '0000000000000000',
                'id_jenjang_pendidikan_ibu'     => (int) ($mahasiswa?->id_jenjang_pendidikan_ibu ?? 0),
                'id_pekerjaan_ibu'              => (int) ($mahasiswa?->id_pekerjaan_ibu ?? 0),
                'id_penghasilan_ibu'            => (int) ($mahasiswa?->id_penghasilan_ibu ?? 0),
                'id_kebutuhan_khusus_ibu'       => (int) ($mahasiswa?->id_kebutuhan_khusus_ibu ?? 0),
                'nama_wali'                     => $mahasiswa?->nama_wali ?? '',
                'tanggal_lahir_wali'            => $safeDate($mahasiswa?->tanggal_lahir_wali),
                'id_jenjang_pendidikan_wali'    => (int) ($mahasiswa?->id_jenjang_pendidikan_wali ?? 0),
                'id_pekerjaan_wali'             => (int) ($mahasiswa?->id_pekerjaan_wali ?? 0),
                'id_penghasilan_wali'           => (int) ($mahasiswa?->id_penghasilan_wali ?? 0),
                'id_kebutuhan_khusus_mahasiswa' => (int) ($mahasiswa?->id_kebutuhan_khusus_mahasiswa ?? 0),
                'telepon'                       => $mahasiswa?->telepon ?? $user?->phone ?? '',
                'handphone'                     => $mahasiswa?->handphone ?? $user?->phone ?? '',
                'email'                         => $record->email_mhs,
                'penerima_kps'                  => $mahasiswa?->penerima_kps ? 1 : 0,
                'no_kps'                        => $mahasiswa?->no_kps ?? '',
                'npwp'                          => substr(preg_replace('/[^0-9]/', '', $mahasiswa?->npwp ?? ''), 0, 15),
                'id_wilayah'                    => $mahasiswa?->id_kecamatan ?? '000000',
                'id_jenis_tinggal'              => (int) ($mahasiswa?->id_jenis_tinggal ?? 0),
                'id_agama'                      => (int) $idAgama,
                'id_provinsi'                   => $mahasiswa?->id_provinsi ?? '',
                'id_kabupaten_kota'             => $mahasiswa?->id_kabupaten ?? '',
                'telepon_ayah'                  => $mahasiswa?->telepon_ayah ?? '',
                'telepon_ibu'                   => $mahasiswa?->telepon_ibu ?? '',
            ],
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => config('services.sipt.token', ''),
                'Accept'        => 'application/json',
            ])->asForm()->post($endpointUrl, $payload);

            $resJson = $response->json();
            $msgText = is_array($resJson)
                ? ($resJson['messages'] ?? $resJson['message'] ?? ($response->successful() ? 'Success' : 'Gagal SIPT'))
                : ($response->successful() ? 'Success' : strip_tags($response->body()));

            $isSuccess = $response->successful() || str_contains(strtolower((string)$msgText), 'berhasil');

            // FIX #2: SIPT menolak dengan "NIM sudah digunakan" ketika NIM ini SUDAH PERNAH
            // berhasil terdaftar sebelumnya (mis. dari request lama/duplikat pemanggilan bulk).
            // Sebelumnya kasus ini tetap dicatat sebagai gagal (message_sipt != 'Success'),
            // sehingga record ini masuk lagi ke antrean sendBulkToSipt() setiap kali dijalankan
            // dan gagal berulang-ulang tanpa henti. Perlakukan sebagai sukses (idempotent)
            // supaya message_sipt diset 'Success' dan record keluar dari antrean bulk,
            // sekaligus tetap lanjut ke proses kirim SLIK seperti alur normal.
            if (!$isSuccess && str_contains(strtolower((string)$msgText), 'sudah digunakan')) {
                $isSuccess = true;
                $msgText   = 'Success (NIM sudah terdaftar sebelumnya di SIPT)';
            }

            if ($isSuccess) {
                $record->update(['message_sipt' => 'Success']);
                Log::info("SIPT: NIM {$record->nim} HTTP {$response->status()} - {$msgText}");

                // Otomatis kirim ke SLIK jika SIPT sukses
                $slikResponse = $this->processSendToSlik($record);

                return [
                    'success'      => true,
                    'message'      => $msgText,
                    'slikResponse' => $slikResponse,
                ];
            } else {
                $record->update(['message_sipt' => $msgText]);
                Log::warning("SIPT Gagal: NIM {$record->nim} HTTP {$response->status()} - {$msgText}");
                return [
                    'success' => false,
                    'message' => "SIPT [{$response->status()}]: {$msgText}",
                ];
            }
        } catch (\Throwable $e) {
            $record->update(['message_sipt' => $e->getMessage()]);
            Log::error('Send SIPT gagal: ' . $e->getMessage(), ['nim_id' => $record->id, 'nim' => $record->nim]);
            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage(),
            ];
        }
    }

    // =========================================================================
    // DAFTAR NIM yang sudah digenerate
    // =========================================================================

    public function list(Request $request)
    {
        $query = PendaftarGetNim::with(['mahasiswa', 'generatedBy'])
            ->orderByDesc('generated_at');

        if ($request->filled('jenjang')) {
            $query->where('jenjang', $request->jenjang);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) =>
                $q->where('nim', 'like', "%$s%")
                  ->orWhere('email_mhs', 'like', "%$s%")
                  ->orWhereHas('mahasiswa', fn($q2) => $q2->where('nama_mahasiswa', 'like', "%$s%"))
            );
        }

        $list = $query->paginate(20)->withQueryString();

        $pendingSlikCount = PendaftarGetNim::where('nim', 'like', '264162%')
            ->where('message_sipt', 'Success')
            ->where(function ($q) {
                $q->whereNull('message_slik')
                  ->orWhere('message_slik', '!=', 'Success');
            })
            ->when($request->filled('jenjang'), fn($q) => $q->where('jenjang', $request->jenjang))
            ->count();

        return view('admin.generate-nim.list', compact('list', 'pendingSlikCount'));
    }

    // =========================================================================
    // KIRIM KE SIPT
    // =========================================================================
    public function sendToSipt(Request $request, int $nimId)
    {
        $record = PendaftarGetNim::with([
            'mahasiswa',
            'mahasiswa.masterEreg',
            'mahasiswa.masterUser',
            'mahasiswa.user',
            'user',
        ])->findOrFail($nimId);

        if ($record->message_sipt === 'Success') {
            return back()->with('error', 'Data sudah terkirim ke SIPT sebelumnya.');
        }

        $res = $this->processSendToSipt($record);

        if ($res['success']) {
            $slik = $res['slikResponse'] ?? null;
            if ($slik && $slik['success']) {
                return back()->with('success', "SIPT Sukses & {$slik['message']} (NIM {$record->nim})");
            }
            return back()->with('warning', "SIPT Sukses, TETAPI " . ($slik['message'] ?? '') . " (NIM {$record->nim})");
        }

        return back()->with('error', "Gagal SIPT: {$res['message']} (NIM {$record->nim})");
    }

    // =========================================================================
    // KIRIM KE SLIK (MANUAL VIA TOMBOL)
    // =========================================================================
    public function sendToSlikManual(Request $request, int $nimId)
    {
        $record = PendaftarGetNim::with(['mahasiswa', 'mahasiswa.masterUser'])->findOrFail($nimId);

        if ($record->message_sipt !== 'Success') {
            return back()->with('error', 'Gagal! Harus terkirim ke SIPT terlebih dahulu.');
        }

        if (strtolower($record->message_slik ?? '') === 'success') {
            return back()->with('error', 'Data sudah pernah sukses terkirim ke SLIK.');
        }

        // Panggil fungsi inti
        $slikResponse = $this->processSendToSlik($record);

        // Debug Log agar Anda bisa mengecek di storage/logs/laravel.log jika UI tidak muncul
        Log::info("Manual SLIK Clicked for NIM {$record->nim}: " . json_encode($slikResponse));

        if ($slikResponse['success']) {
            return back()->with('success', "SUKSES SLIK: {$slikResponse['message']} (NIM {$record->nim})");
        } else {
            return back()->with('error', "GAGAL SLIK: {$slikResponse['message']} (NIM {$record->nim})");
        }
    }

    public function processSendToSlik(PendaftarGetNim $record): array
    {
        try {
            // 1. CEK MASTER USERS (Gunakan data transaksi PMB)
            $masterUser = DB::table('master_users')->where('user_id', $record->user_id)->first();

            if (!$masterUser) {
                return ['success' => false, 'message' => "Data pendaftar tidak ditemukan di tabel master_users."];
            }

            // Validasi status lunas
            if (strtolower($masterUser->va_status ?? '') !== 'paid') {
                return ['success' => false, 'message' => "Status di master_users belum 'paid'."];
            }

            // 2. CEK SLIK BILLINGS (Untuk mengambil VA UKT dan Nominal Pembayaran Kuliah)
            $billings = DB::table('slik_billings')
                ->where('user_id', $record->user_id)
                ->where('status', 'paid')
                ->get();

            if ($billings->isNotEmpty()) {
                $billing         = $billings->first();
                $vaBilling       = $billing->virtual_account;
                $amount          = (int) $billing->amount;
                $datetimePayment = $billing->datetime_payment ?? $masterUser->paid_at ?? date('Y-m-d H:i:s');
                $billingTrxId    = !empty($billing->trx_id) ? $billing->trx_id : $masterUser->trx_id;
                $tenor           = $billing->tenor_pembayaran ?? ($amount > 7000000 ? 'tahunan' : 'cicilan');
            } else {
                // Fallback ke master_users jika data di slik_billings belum terisi
                $vaBilling       = $masterUser->va_number;
                $amount          = (int) ($masterUser->payment_amount ?? $masterUser->va_amount ?? 0);
                $datetimePayment = $masterUser->paid_at ?? date('Y-m-d H:i:s');
                $billingTrxId    = $masterUser->trx_id;
                $tenor           = $amount > 7000000 ? 'tahunan' : 'cicilan';
            }

            // Prioritas ID Pendaftar di SLIK:
            // 1. $billing->trx_id (TRX ID resmi saat generate billing di SLIK)
            // 2. $masterUser->trx_id (Nomor pendaftaran PMB awal)
            // 3. user_id
            $candidateIds = array_values(array_unique(array_filter([
                !empty($billing->trx_id) ? $billing->trx_id : null,
                !empty($masterUser->trx_id) ? $masterUser->trx_id : null,
                (string) $record->user_id,
            ])));

            $idPendaftar = $candidateIds[0] ?? null;
            if (empty($idPendaftar)) {
                return ['success' => false, 'message' => "Parameter ID Pendaftar / trx_id kosong."];
            }

            // Kode UKT untuk API SLIK menggunakan format prefix 'pmb' (contoh: 'pmbcicilan', 'pmbtahunan', 'pmbkip')
            $cleanTenor = str_replace(['ukt_', 'pmb'], '', strtolower($tenor));
            $kodeUkt = 'pmb' . ($cleanTenor === 'tahunan' ? 'tahunan' : ($cleanTenor === 'kip' ? 'kip' : 'cicilan'));

            // 3. SUSUN PARAMETER UANG MASUK (Menyesuaikan parameter SLIK Tagihan.php)
            $uangMasuk = [
                [
                    'id_pendaftar'     => $idPendaftar,
                    'trx_id'           => $billingTrxId,
                    'virtual_account'  => $vaBilling,
                    'status_va'        => 1,
                    'bank'             => 'Mandiri',
                    'nominal'          => $amount,
                    'trx_amount'       => $amount,
                    'kodeukt'          => $kodeUkt,
                    'datetime_payment' => $datetimePayment,
                    'waktu_transaksi'  => $datetimePayment,
                    'keterangan'       => 'Pembayaran PMB',
                ]
            ];

            // 4. PAYLOAD UTAMA UNTUK SLIK
            $payload = [
                'nim'          => $record->nim,
                'uang_masuk'   => $uangMasuk,
                'prodi'        => $record->kode_prodi,
                'id_pendaftar' => $idPendaftar,
                'periode'      => $record->id_periode_masuk ?? '20261',
            ];

            // Endpoint SLIK (Default menggunakan server produksi SLIK UBP)
            $endpointUrl = env('SLIK_ENDPOINT_URL', 'https://slik.ubpkarawang.ac.id/api/tagihan/pembayaran_pmb');

            // 5. KIRIM KE ENDPOINT SLIK
            $response = Http::asForm()->post($endpointUrl, $payload);
            $result = $response->json();

            // 6. EVALUASI RESPON API SLIK
            if (isset($result['status_code']) && $result['status_code'] === '000') {
                $record->update(['message_slik' => 'Success']);
                Log::info("SLIK Sukses (NIM {$record->nim}) pakai ID Pendaftar: {$idPendaftar} & VA: {$vaBilling}");
                return ['success' => true, 'message' => 'Tagihan berhasil didaftarkan ke SLIK.'];
            } else {
                $errorMsg = isset($result['messages']) ? $result['messages'] : $response->body();

                // Coba fallback dengan sisa candidate IDs
                $alternateIds = array_slice($candidateIds, 1);

                foreach ($alternateIds as $altId) {
                    Log::warning("Mencoba fallback kirim alternate ID {$altId} untuk NIM {$record->nim}...");
                    $payload['id_pendaftar'] = $altId;
                    $payload['uang_masuk'][0]['id_pendaftar'] = $altId;
                    $payload['uang_masuk'][0]['trx_id'] = $altId;

                    $retryResponse = Http::asForm()->post($endpointUrl, $payload);
                    $retryResult = $retryResponse->json();

                    if (isset($retryResult['status_code']) && $retryResult['status_code'] === '000') {
                        $record->update(['message_slik' => 'Success']);
                        Log::info("SLIK Sukses (NIM {$record->nim}) setelah fallback pakai ID: {$altId}");
                        return ['success' => true, 'message' => 'Tagihan berhasil didaftarkan ke SLIK (via Fallback).'];
                    }
                }

                $record->update(['message_slik' => $errorMsg]);
                return ['success' => false, 'message' => "API SLIK Menolak: {$errorMsg}"];
            }

        } catch (\Throwable $e) {
            $record->update(['message_slik' => $e->getMessage()]);
            Log::error('Send SLIK Error: ' . $e->getMessage());
            return ['success' => false, 'message' => "System Error: " . $e->getMessage()];
        }
    }

    // =========================================================================
    // KIRIM MASSAL KE SLIK (DILENGKAPI LOCKING WITHOUT OVERLAPPING & DELAY)
    // =========================================================================

    /**
     * Eksekusi inti pengiriman batch data ke SLIK.
     */
    public function executeBulkSendToSlik(int $limit = 50, int $delayMs = 500, ?string $jenjang = null, ?callable $onProgress = null): array
    {
        Log::info("--- MEMULAI BULK SEND TO SLIK " . ($jenjang ? "JENJANG: [{$jenjang}]" : "SEMUA JENJANG") . " (LIMIT: {$limit}, DELAY: {$delayMs}ms) ---");

        $query = PendaftarGetNim::with([
            'mahasiswa',
            'mahasiswa.masterUser',
            'user',
        ])
        ->where('nim', 'like', '264162%') // Hanya NIM angkatan 2026
        ->where('message_sipt', 'Success')
        ->where(function ($q) {
            $q->whereNull('message_slik')
              ->orWhere('message_slik', '!=', 'Success');
        })
        ->orderByRaw("CASE WHEN message_slik IS NULL THEN 0 ELSE 1 END ASC")
        ->orderBy('updated_at', 'asc');

        if (!empty($jenjang)) {
            $query->where('jenjang', $jenjang);
        }

        $totalAntrean = (clone $query)->count();

        if ($limit > 0) {
            $query->limit($limit);
        }

        $records = $query->get();
        $totalDiproses = $records->count();

        if ($totalDiproses === 0) {
            return [
                'status'        => 'info',
                'jenjang'       => $jenjang ?? 'Semua Jenjang',
                'message'       => 'Semua data NIM yang sukses SIPT sudah berhasil terkirim ke SLIK.',
                'total_antrean' => 0,
                'diproses'      => 0,
                'berhasil'      => 0,
                'gagal'         => 0,
                'sisa_antrean'  => 0,
                'data'          => [],
            ];
        }

        $successCount = 0;
        $failedCount  = 0;
        $details      = [];

        foreach ($records as $index => $record) {
            $res = $this->processSendToSlik($record);

            if ($res['success']) {
                $successCount++;
                Log::info("Bulk SLIK Sukses: NIM {$record->nim} - Message: {$res['message']}");
                $itemDetail = [
                    'nim'     => $record->nim,
                    'status'  => 'Sukses',
                    'message' => $res['message'],
                ];
            } else {
                $failedCount++;
                Log::warning("Bulk SLIK Gagal: NIM {$record->nim} - Message: {$res['message']}");
                $itemDetail = [
                    'nim'     => $record->nim,
                    'status'  => 'Gagal',
                    'message' => $res['message'],
                ];
            }

            $details[] = $itemDetail;

            if ($onProgress) {
                $onProgress($record, $itemDetail, $index + 1, $totalDiproses);
            }

            // Delay antar record agar tidak memberatkan server PMB maupun SLIK (kecuali setelah record terakhir)
            if ($index < $totalDiproses - 1 && $delayMs > 0) {
                usleep($delayMs * 1000);
            }
        }

        $sisaAntrean = max(0, $totalAntrean - $successCount);

        Log::info("--- BULK SLIK SELESAI --- Diproses: {$totalDiproses} | Berhasil: {$successCount} | Gagal: {$failedCount} | Sisa Antrean: {$sisaAntrean}");

        return [
            'status'        => $successCount > 0 ? 'success' : 'error',
            'jenjang'       => $jenjang ?? 'Semua Jenjang',
            'batch_limit'   => $limit,
            'delay_ms'      => $delayMs,
            'total_antrean' => $totalAntrean,
            'diproses'      => $totalDiproses,
            'berhasil'      => $successCount,
            'gagal'         => $failedCount,
            'sisa_antrean'  => $sisaAntrean,
            'message'       => "Bulk Kirim SLIK Selesai! Berhasil: {$successCount}, Gagal/Tertunda: {$failedCount}, Sisa antrean: {$sisaAntrean}.",
            'data'          => $details,
        ];
    }

    /**
     * Handler pengiriman massal ke SLIK (bisa diakses via Web Admin, Controller, maupun Public API/n8n)
     * Dilengkapi Concurrency Lock (withoutOverlapping) dan Delay Throttle.
     */
    public function sendBulkToSlik(Request $request, $jenjangOrLimit = null, $limit = null)
    {
        // 1. Opsional Token Check (Jika token disertakan di request, pastikan valid)
        $configuredToken = config('slik.secret_token');
        $providedToken   = $request->get('token') 
            ?: $request->header('X-SLIK-TOKEN') 
            ?: $request->bearerToken();

        if (!empty($configuredToken) && !empty($providedToken) && $providedToken !== $configuredToken) {
            return response()->json([
                'status'  => 'unauthorized',
                'message' => 'Token otentikasi SLIK tidak valid.',
            ], 401);
        }

        // 2. Concurrency Lock: Mencegah request tumpang tindih (withoutOverlapping)
        $lockKey = 'sync_slik_bulk_lock';
        $lock = Cache::lock($lockKey, 900); // Kunci selama 15 menit

        if (!$lock->get()) {
            $busyMsg = 'Proses sinkronisasi ke SLIK sedang berjalan oleh tugas lain (Scheduler / API / Admin). Permintaan diabaikan untuk mencegah duplikasi (withoutOverlapping).';
            Log::warning($busyMsg);

            if ($request->wantsJson() || $request->is('api/*') || !$request->hasHeader('referer')) {
                return response()->json([
                    'status'  => 'busy',
                    'message' => $busyMsg,
                    'locked'  => true,
                ], 429);
            }
            return back()->with('error', $busyMsg);
        }

        try {
            set_time_limit(600); // Set waktu eksekusi aman hingga 10 menit

            // 3. Parsing parameter limit, delay, dan jenjang
            if (is_numeric($jenjangOrLimit)) {
                $limit = (int) $jenjangOrLimit;
                $jenjang = $request->get('jenjang');
            } else {
                $jenjang = $jenjangOrLimit ?: $request->get('jenjang');
                $limit = $limit ? (int) $limit : ($request->filled('limit') ? (int) $request->limit : 50);
            }

            if ($limit <= 0) {
                $limit = 50;
            }

            $delayMs = (int) $request->get('delay', 500); // Default delay 500ms (0.5 detik)
            if ($delayMs < 100)  $delayMs = 100;
            if ($delayMs > 3000) $delayMs = 3000;

            // 4. Eksekusi pengiriman
            $result = $this->executeBulkSendToSlik($limit, $delayMs, $jenjang);

            if ($request->wantsJson() || $request->is('api/*') || !$request->hasHeader('referer')) {
                return response()->json($result, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }

            $flashType = $result['berhasil'] > 0 ? 'success' : ($result['diproses'] === 0 ? 'info' : 'error');
            return back()->with($flashType, $result['message']);

        } catch (\Throwable $e) {
            Log::error('Exception in sendBulkToSlik: ' . $e->getMessage());

            if ($request->wantsJson() || $request->is('api/*') || !$request->hasHeader('referer')) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage(),
                ], 500);
            }
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());

        } finally {
            // Lepas kunci lock
            $lock->release();
        }
    }

    // =========================================================================
    // HELPERS PRIVATE
    // =========================================================================

    public function sendBulkToSipt(Request $request, $jenjangOrLimit = null, $limit = null)
    {
        // 1. Ambil jenjang & limit dari parameter URL atau query string
        if (is_numeric($jenjangOrLimit)) {
            $limit = (int) $jenjangOrLimit;
            $jenjang = $request->get('jenjang');
        } else {
            $jenjang = $jenjangOrLimit ?: $request->get('jenjang');
            $limit = $limit ? (int) $limit : ($request->filled('limit') ? (int) $request->limit : null);
        }

        Log::info("--- MEMULAI BULK SEND TO SIPT " . ($jenjang ? "JENJANG: [{$jenjang}]" : "SEMUA JENJANG") . ($limit ? " (LIMIT: {$limit} DATA)" : "") . " ---");

        // 2. Query data NIM yang belum sukses ke SIPT
        $query = PendaftarGetNim::with([
            'mahasiswa',
            'mahasiswa.masterEreg',
            'mahasiswa.masterUser',
            'mahasiswa.user',
            'user',
        ])
        ->where(function ($q) {
            $q->whereNull('message_sipt')
              ->orWhere('message_sipt', '!=', 'Success');
        });

        if (!empty($jenjang)) {
            $query->where('jenjang', $jenjang);
        }

        // 3. Batasi query sesuai parameter limit
        if ($limit && $limit > 0) {
            $query->limit($limit);
        }

        $records = $query->get();
        $totalAntrean = $records->count();

        Log::info("Ditemukan {$totalAntrean} record NIM yang siap dikirim ke SIPT.");

        if ($totalAntrean === 0) {
            $infoMsg = ($jenjang ? "Jenjang [{$jenjang}]: " : "") . 'Semua data NIM sudah berhasil terkirim ke SIPT sebelumnya.';
            if ($request->wantsJson() || !$request->hasHeader('referer')) {
                return response()->json([
                    'status'        => 'info',
                    'jenjang'       => $jenjang ?? 'Semua Jenjang',
                    'message'       => $infoMsg,
                    'total_antrean' => 0,
                    'berhasil'      => 0,
                    'gagal'         => 0,
                ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }
            return back()->with('info', $infoMsg);
        }

        $successCount = 0;
        $failedCount  = 0;
        $messages     = [];
        $details      = [];

        foreach ($records as $record) {
            $res = $this->processSendToSipt($record);

            if ($res['success']) {
                $successCount++;
                Log::info("Bulk SIPT Sukses: NIM {$record->nim} - Mahasiswa: {$record->mahasiswa?->nama_mahasiswa}");
                $details[] = [
                    'nim'     => $record->nim,
                    'nama'    => $record->mahasiswa?->nama_mahasiswa ?? $record->user?->name,
                    'status'  => 'Sukses',
                    'message' => $res['message'],
                ];
            } else {
                $failedCount++;
                $messages[] = "NIM {$record->nim}: {$res['message']}";
                Log::warning("Bulk SIPT Gagal: NIM {$record->nim} - {$res['message']}");
                $details[] = [
                    'nim'     => $record->nim,
                    'nama'    => $record->mahasiswa?->nama_mahasiswa ?? $record->user?->name,
                    'status'  => 'Gagal',
                    'message' => $res['message'],
                ];
            }
        }

        Log::info("--- BULK SIPT SELESAI --- Berhasil: {$successCount} | Gagal: {$failedCount}");

        $msg = "Bulk Kirim SIPT " . ($jenjang ? "Jenjang [{$jenjang}] " : "") . "Selesai" . ($limit ? " (Batch {$limit} data)" : "") . "! Berhasil: <strong>{$successCount}</strong>, Gagal/Tertunda: <strong>{$failedCount}</strong>.";
        if (!empty($messages)) {
            $msg .= '<br><small class="text-xs">' . implode('<br>', array_slice($messages, 0, 5)) . (count($messages) > 5 ? '<br>...dan lainnya.' : '') . '</small>';
        }

        if ($request->wantsJson() || !$request->hasHeader('referer')) {
            return response()->json([
                'status'        => $successCount > 0 ? 'success' : 'error',
                'jenjang'       => $jenjang ?? 'Semua Jenjang',
                'batch_limit'   => $limit,
                'total_antrean' => $totalAntrean,
                'berhasil'      => $successCount,
                'gagal'         => $failedCount,
                'summary'       => strip_tags(str_replace('<br>', ' | ', $msg)),
                'data'          => $details,
            ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        return back()->with($successCount > 0 ? 'success' : 'error', $msg);
    }

    private function processSendToElearning(PendaftarGetNim $record): array
    {
        $token = '5675caf640a47a04ef039ac2dc68ea1c';
        $serverUrl = 'https://elearning.ubpkarawang.ac.id/webservice/rest/server.php';

        $mahasiswa = $record->mahasiswa;
        $userId    = $record->user_id ?? $mahasiswa?->user_id ?? $record->user?->id;

        // 1. Ambil data master_users & users
        $masterUser = DB::table('master_users')->where('user_id', $userId)->first();
        $user       = DB::table('users')->where('id', $userId)->first();

        // 2. Format Username: Alfanumerik murni (pola baru agar tidak bentrok dengan akun dummy test)
        // Contoh hasil: pkkmb2620269793011 (pkkmb26 + trx_id)
        $trxId          = !empty($masterUser?->trx_id) ? $masterUser->trx_id : $record->nim;
        $cleanTrxId     = preg_replace('/[^0-9]/', '', (string)$trxId);
        $moodleUsername = strtolower('pkkmb-26' . $cleanTrxId);

        // 3. Format Password berbasis nomor HP + Ubp@26
        $rawPhone     = preg_replace('/[^0-9]/', '', (string)($user?->phone ?? $mahasiswa?->handphone ?? ''));
        $passwordBase = !empty($rawPhone) ? $rawPhone : (string)$cleanTrxId;
        $password     = $passwordBase . 'Ubp@26';

        // 4. Nama Depan & Belakang
        $rawNama   = trim($mahasiswa?->nama_mahasiswa ?? ($user?->name ?? 'Mahasiswa'));
        $cleanNama = preg_replace("/[`'’\"\\\]/", '', $rawNama);
        $cleanNama = preg_replace('/[^a-zA-Z0-9\s]/', ' ', $cleanNama);
        $cleanNama = trim(preg_replace('/\s+/', ' ', $cleanNama));

        $namaParts = explode(' ', $cleanNama);
        $firstname = trim(array_shift($namaParts) ?: 'Mahasiswa');
        $lastname  = !empty($namaParts) ? trim(implode(' ', $namaParts)) : $firstname;

        // 5. Email: Awali dengan 'pkkmb.' untuk membedakan dari akun SIAKAD resmi
        $rawEmail   = strtolower(trim($record->email_mhs ?? ($user?->email ?? '')));
        $cleanEmail = filter_var($rawEmail, FILTER_SANITIZE_EMAIL);
        if (!empty($cleanEmail) && filter_var($cleanEmail, FILTER_VALIDATE_EMAIL)) {
            if (!str_starts_with($cleanEmail, 'pkkmb.')) {
                $cleanEmail = 'pkkmb.' . $cleanEmail;
            }
        } else {
            $cleanEmail = strtolower('pkkmb.' . $moodleUsername . '@mhs.ubpkarawang.ac.id');
        }

        // 6. Payload Moodle
        $payload = [
            'wstoken'            => $token,
            'wsfunction'         => 'core_user_create_users',
            'moodlewsrestformat' => 'json',
            'users' => [
                [
                    'username'   => $moodleUsername,
                    'password'   => $password,
                    'firstname'  => $firstname,
                    'lastname'   => $lastname,
                    'email'      => $cleanEmail,
                    'customfields'=>[
                        ['type'=>"nim",'value'=>$cleanTrxId],
                        ['type'=>"klas",'value'=>"PKKMB"],
                        ['type'=>"nomortelpon",'value'=>'08'],
                    ],
                    'idnumber'   => (string) $cleanTrxId,
                    'department' => (string) ($record->kode_prodi ?? '00000'),
                    'city'       => 'Karawang',
                    'country'    => 'ID',
                ]
            ]
        ];

        try {
            $response = Http::asForm()->post($serverUrl, $payload);
            $resJson  = $response->json();

            // 1. Respon Sukses (Kembalian array berisi ID user yang baru dibuat)
            if (is_array($resJson) && isset($resJson[0]['id'])) {
                $moodleUserId = (int) $resJson[0]['id'];

                // Enrol otomatis ke Course PKKMB (ID: 1595)
                $this->enrolUserToMoodleCourse($serverUrl, $token, $moodleUserId, self::MOODLE_PKKMB_COURSE_ID);

                $record->update(['message_elearning' => 'Success']);
                Log::info("E-Learning: Sukses buat akun {$moodleUsername} (Moodle User ID: {$moodleUserId}) & Enrol ke Course " . self::MOODLE_PKKMB_COURSE_ID);

                return [
                    'success' => true,
                    'message' => "Akun {$moodleUsername} berhasil dibuat di E-Learning (ID: {$moodleUserId}) & dimasukkan ke Course " . self::MOODLE_PKKMB_COURSE_ID
                ];
            }

            $errorMsg  = $resJson['message'] ?? ($resJson['errorcode'] ?? $response->body());
            $errorCode = $resJson['errorcode'] ?? null;

            // 2. Deteksi jika akun sudah terdaftar sebelumnya
            $isDuplicate = str_contains(strtolower($errorMsg), 'already') || 
                           str_contains(strtolower($errorMsg), 'exists') || 
                           str_contains(strtolower($errorMsg), 'duplicate');

            $existingUser = null;

            // Jika Moodle mengembalikan 'invalidparameter', verifikasi apakah email/username sudah ada di Moodle
            if (!$isDuplicate && $errorCode === 'invalidparameter') {
                try {
                    $checkRes = Http::asForm()->post($serverUrl, [
                        'wstoken'            => $token,
                        'wsfunction'         => 'core_user_get_users',
                        'moodlewsrestformat' => 'json',
                        'criteria'           => [
                            ['key' => 'email', 'value' => $cleanEmail],
                        ]
                    ]);
                    $checkJson = $checkRes->json();
                    if (!empty($checkJson['users'])) {
                        $isDuplicate = true;
                        $existingUser = $checkJson['users'][0];
                        Log::info("E-Learning: Terverifikasi akun sudah ada di Moodle via email {$cleanEmail} (Moodle User ID: {$existingUser['id']}, Username: {$existingUser['username']})");
                    }
                } catch (\Throwable $ex) {
                    // Ignore check exception and fallback to error logging
                }
            }

            if ($isDuplicate) {
                // Pastikan user ter-enrol ke Course PKKMB (ID: 1595)
                if (!empty($existingUser['id'])) {
                    $this->enrolUserToMoodleCourse($serverUrl, $token, (int) $existingUser['id'], self::MOODLE_PKKMB_COURSE_ID);
                } else {
                    try {
                        $findUser = Http::asForm()->post($serverUrl, [
                            'wstoken'            => $token,
                            'wsfunction'         => 'core_user_get_users',
                            'moodlewsrestformat' => 'json',
                            'criteria'           => [
                                ['key' => 'username', 'value' => $moodleUsername],
                            ]
                        ])->json();
                        if (!empty($findUser['users'][0]['id'])) {
                            $this->enrolUserToMoodleCourse($serverUrl, $token, (int) $findUser['users'][0]['id'], self::MOODLE_PKKMB_COURSE_ID);
                        }
                    } catch (\Throwable $ex) {}
                }

                $record->update(['message_elearning' => 'Success']);
                Log::info("E-Learning: {$moodleUsername} sudah terdaftar sebelumnya & diverifikasi masuk ke Course " . self::MOODLE_PKKMB_COURSE_ID);
                return [
                    'success' => true,
                    'message' => "Akun {$moodleUsername} sudah terdaftar di E-Learning & dimasukkan ke Course " . self::MOODLE_PKKMB_COURSE_ID . "."
                ];
            }

            // 3. Catat Error
            $cleanError = strip_tags($errorMsg);
            if (isset($resJson['debuginfo'])) {
                $cleanError .= ' | ' . strip_tags($resJson['debuginfo']);
            }

            $record->update(['message_elearning' => substr($cleanError, 0, 255)]);

            Log::warning("E-Learning Gagal: {$moodleUsername} - {$cleanError}", [
                'payload_sent'      => $payload['users'][0],
                'http_status'       => $response->status(),
                'moodle_errorcode'  => $errorCode,
                'raw_response'      => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => "Moodle Error: {$cleanError}"
            ];

        } catch (\Throwable $e) {
            $record->update(['message_elearning' => substr($e->getMessage(), 0, 255)]);
            Log::error("E-Learning Exception: {$moodleUsername} - " . $e->getMessage());

            return [
                'success' => false,
                'message' => "Exception: " . $e->getMessage()
            ];
        }
    }

    /**
     * Enrol User ke Course Moodle via WebService.
     */
    private function enrolUserToMoodleCourse(string $serverUrl, string $token, int $moodleUserId, int $courseId = self::MOODLE_PKKMB_COURSE_ID, int $roleId = 5): bool
    {
        try {
            $response = Http::asForm()->post($serverUrl, [
                'wstoken'            => $token,
                'wsfunction'         => 'enrol_manual_enrol_users',
                'moodlewsrestformat' => 'json',
                'enrolments'         => [
                    [
                        'roleid'   => $roleId, // 5 = Student di Moodle
                        'userid'   => $moodleUserId,
                        'courseid' => $courseId,
                    ]
                ]
            ]);

            $json = $response->json();
            // Moodle mengembalikan null jika berhasil
            if ($json === null || (is_array($json) && empty($json['exception']))) {
                Log::info("E-Learning Enrol: Berhasil enrol User ID {$moodleUserId} ke Course ID {$courseId}");
                return true;
            }

            Log::warning("E-Learning Enrol Gagal: User ID {$moodleUserId} ke Course ID {$courseId} - " . ($json['message'] ?? $response->body()));
            return false;
        } catch (\Throwable $e) {
            Log::error("E-Learning Enrol Exception: User ID {$moodleUserId} - " . $e->getMessage());
            return false;
        }
    }
 

    private function diagnoseElearningRequiredField(string $serverUrl, string $token, array $realFields): ?array
    {
        $uniqueTag = substr(md5(uniqid('', true)), 0, 8);
 
        $safeBaseline = [
            'username'  => "diagtest-{$uniqueTag}1",
            'password'  => 'DiagTest@26',
            'firstname' => 'Diag',
            'lastname'  => 'Test',
            'email'     => "diagtest.{$uniqueTag}@mhs.ubpkarawang.ac.id",
        ];
 
        foreach (array_keys($safeBaseline) as $fieldUnderTest) {
            $testUser = $safeBaseline;
 
            if ($fieldUnderTest === 'username') {
                // JANGAN gunakan username asli mahasiswa secara langsung di sini.
                // Cukup uji apakah KARAKTER/FORMAT-nya yang membuat Moodle menolak,
                // dengan menempelkannya ke prefix unik yang mustahil bentrok dengan
                // akun mahasiswa manapun.
                $testUser['username'] = 'diagfmt-' . $uniqueTag . '-' . $realFields['username'];
            } else {
                $testUser[$fieldUnderTest] = $realFields[$fieldUnderTest];
                $testUser['username']      = $safeBaseline['username'] . $fieldUnderTest;
            }
 
            $testPayload = [
                'wstoken'            => $token,
                'wsfunction'         => 'core_user_create_users',
                'moodlewsrestformat' => 'json',
                'users'              => [$testUser],
            ];
 
            $resp = Http::asForm()->post($serverUrl, $testPayload);
            $json = $resp->json();
 
            if (is_array($json) && isset($json[0]['id'])) {
                $delResp = Http::asForm()->post($serverUrl, [
                    'wstoken'            => $token,
                    'wsfunction'         => 'core_user_delete_users',
                    'moodlewsrestformat' => 'json',
                    'userids'            => [$json[0]['id']],
                ]);
                $delJson = $delResp->json();
 
                // core_user_delete_users normalnya mengembalikan array kosong []
                // ketika berhasil. Kalau bukan itu yang didapat, JANGAN diam-diam
                // dilewati — catat sebagai error keras supaya admin tahu ada akun
                // uji-coba yang harus dihapus manual.
                $deleteOk = is_array($delJson) && count($delJson) === 0;
 
                if (!$deleteOk) {
                    Log::error(
                        "E-Learning DIAGNOSTIC: GAGAL menghapus akun uji coba diagnostik "
                        . "(Moodle User ID: {$json[0]['id']}, username: {$testUser['username']}). "
                        . "Akun sampah ini TERTINGGAL di Moodle dan HARUS dihapus manual "
                        . "lewat admin Moodle untuk menghindari kebocoran akun palsu.",
                        ['delete_response' => $delResp->body()]
                    );
                }
 
                continue;
            }
 
            return [
                'field'        => $fieldUnderTest,
                'value'        => $realFields[$fieldUnderTest] ?? null,
                'raw_response' => $resp->body(),
            ];
        }
 
        return null;
    }
    // =========================================================================
    // KIRIM KE E-LEARNING (Satuan)
    // =========================================================================
    public function sendToElearning(Request $request, int $nimId)
    {
        $record = PendaftarGetNim::with(['mahasiswa', 'user'])->findOrFail($nimId);

        if ($record->message_elearning === 'Success') {
            return back()->with('error', 'Data sudah terkirim ke E-Learning sebelumnya.');
        }

        $res = $this->processSendToElearning($record);

        if ($res['success']) {
            return back()->with('success', "E-Learning Sukses: {$res['message']} (NIM {$record->nim})");
        }

        return back()->with('error', "Gagal E-Learning: {$res['message']} (NIM {$record->nim})");
    }

    // =========================================================================
    // KIRIM MASSAL KE E-LEARNING (Bisa per limit / 10 orang)
    // =========================================================================
    public function sendBulkToElearning(Request $request, $jenjangOrLimit = null, $limit = null)
    {
        // 1. Ambil jenjang & limit dari parameter URL atau query string
        // Contoh: /admin/send-bulk-elearning/S1/5 ATAU /admin/send-bulk-elearning/5
        if (is_numeric($jenjangOrLimit)) {
            $limit = (int) $jenjangOrLimit;
            $jenjang = $request->get('jenjang');
        } else {
            $jenjang = $jenjangOrLimit ?: $request->get('jenjang');
            $limit = $limit ? (int) $limit : ($request->filled('limit') ? (int) $request->limit : null);
        }

        Log::info("--- MEMULAI BULK SEND TO E-LEARNING " . ($jenjang ? "JENJANG: [{$jenjang}]" : "SEMUA JENJANG") . ($limit ? " (LIMIT: {$limit} DATA)" : "") . " ---");

        // Ambil data yang BELUM sukses terkirim ke E-Learning
        $query = PendaftarGetNim::with(['mahasiswa', 'user'])
            ->where(function ($q) {
                $q->whereNull('message_elearning')
                  ->orWhere('message_elearning', '!=', 'Success');
            });

        if (!empty($jenjang)) {
            $query->where('jenjang', $jenjang);
        }

        if ($limit && $limit > 0) {
            $query->limit($limit);
        }

        $records = $query->get();
        $totalAntrean = $records->count();

        Log::info("Ditemukan {$totalAntrean} record NIM yang siap dikirim ke E-Learning.");

        if ($totalAntrean === 0) {
            $infoMsg = ($jenjang ? "Jenjang [{$jenjang}]: " : "") . 'Semua akun mahasiswa sudah berhasil dibuat di E-Learning.';
            if ($request->wantsJson() || !$request->hasHeader('referer')) {
                return response()->json([
                    'status'        => 'info',
                    'jenjang'       => $jenjang ?? 'Semua Jenjang',
                    'message'       => $infoMsg,
                    'total_antrean' => 0,
                    'berhasil'      => 0,
                    'gagal'         => 0,
                ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }
            return back()->with('info', $infoMsg);
        }

        $successCount = 0;
        $failedCount  = 0;
        $messages     = [];
        $details      = [];

        foreach ($records as $record) {
            $res = $this->processSendToElearning($record);

            if ($res['success']) {
                $successCount++;
                $details[] = [
                    'nim'     => $record->nim,
                    'nama'    => $record->mahasiswa?->nama_mahasiswa ?? $record->user?->name,
                    'status'  => 'Sukses',
                    'message' => $res['message'],
                ];
            } else {
                $failedCount++;
                $messages[] = "NIM {$record->nim}: {$res['message']}";
                $details[] = [
                    'nim'     => $record->nim,
                    'nama'    => $record->mahasiswa?->nama_mahasiswa ?? $record->user?->name,
                    'status'  => 'Gagal',
                    'message' => $res['message'],
                ];
            }
        }

        Log::info("--- BULK E-LEARNING SELESAI --- Berhasil: {$successCount} | Gagal: {$failedCount}");

        $msg = "Bulk Kirim E-Learning " . ($jenjang ? "Jenjang [{$jenjang}] " : "") . "Selesai" . ($limit ? " (Batch {$limit} data)" : "") . "! Berhasil: <strong>{$successCount}</strong>, Gagal/Tertunda: <strong>{$failedCount}</strong>.";
        if (!empty($messages)) {
            $msg .= '<br><small class="text-xs">' . implode('<br>', array_slice($messages, 0, 5)) . (count($messages) > 5 ? '<br>...dan lainnya.' : '') . '</small>';
        }

        if ($request->wantsJson() || !$request->hasHeader('referer')) {
            return response()->json([
                'status'        => $successCount > 0 ? 'success' : 'error',
                'jenjang'       => $jenjang ?? 'Semua Jenjang',
                'batch_limit'   => $limit,
                'total_antrean' => $totalAntrean,
                'berhasil'      => $successCount,
                'gagal'         => $failedCount,
                'summary'       => strip_tags(str_replace('<br>', ' | ', $msg)),
                'data'          => $details,
            ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        return back()->with($successCount > 0 ? 'success' : 'error', $msg);
    }

    private function checkEligibility(DataMahasiswaFinal $mahasiswa): array
    {
        if (PendaftarGetNim::where('user_id', $mahasiswa->user_id)->exists() || $mahasiswa->pendaftarGetNim()->exists()) {
            return ['eligible' => false, 'reason' => 'Mahasiswa sudah memiliki NIM.', 'type' => null];
        }

        $mu = $mahasiswa->masterUser;

        if (!$mu || ($mu->va_status !== 'paid' && $mu->jenis_beasiswa !== 'pegawai')) {
            return [
                'eligible' => false,
                'reason'   => 'Belum memenuhi syarat: pembayaran pendaftaran belum lunas atau bukan penerima beasiswa pegawai.',
                'type'     => null,
            ];
        }

        $type = ($mu->jenis_beasiswa === 'pegawai') ? 'beasiswa' : 'payment';

        // Validasi kesesuaian prodi kelulusan dengan prodi pembayaran UKT
        $validasiProdi = $this->validateProdiLulusDanBayar($mahasiswa);
        if (!$validasiProdi['valid']) {
            return [
                'eligible' => false,
                'reason'   => $validasiProdi['reason'],
                'type'     => $type,
            ];
        }

        return [
            'eligible'   => true,
            'reason'     => null,
            'type'       => $type,
            'kode_prodi' => $validasiProdi['kode_prodi'],
            'nama_prodi' => $validasiProdi['nama_prodi'],
        ];
    }

    /**
     * Memvalidasi kecocokan prodi kelulusan dengan prodi pembayaran UKT.
     * Mengembalikan prodi final jika lulus dan bayar di prodi yang sama.
     * Jika tidak sama atau belum lunas, tolak generate NIM.
     *
     * @param DataMahasiswaFinal $mahasiswa
     * @return array{valid: bool, reason: ?string, kode_prodi: ?string, nama_prodi: ?string}
     */
    private function validateProdiLulusDanBayar(DataMahasiswaFinal $mahasiswa): array
    {
        $user       = $mahasiswa->user;
        $ereg       = $mahasiswa->masterEreg;
        $masterUser = $mahasiswa->masterUser;

        // 1. TENTUKAN PRODI KELULUSAN
        $prodiLulus = null;

        // A. Profesi Apoteker Farmasi
        if (($user?->profesi === 'Apoteker Farmasi') || in_array($ereg?->jurusan1, self::KODE_APOTEKER) || in_array($ereg?->jurusan_lulusan, self::KODE_APOTEKER)) {
            $prodiLulus = '48901';
        } else {
            // B. Prioritas Utama: kolom jurusan_lulusan di master_ereg
            if (!empty($ereg?->jurusan_lulusan) && $ereg->jurusan_lulusan !== '00000' && $ereg->jurusan_lulusan !== '-') {
                $prodiLulus = $ereg->jurusan_lulusan;
            }

            // C. Fallback: master_moodle_lulus (record kelulusan terbaru)
            if (empty($prodiLulus) && $masterUser) {
                $moodleLulus = MasterMoodleLulus::where('master_user_id', $masterUser->id)
                    ->where('status_kelulusan', 'LULUS')
                    ->whereNotNull('prodi_lulus')
                    ->where('prodi_lulus', '!=', '00000')
                    ->where('prodi_lulus', '!=', '')
                    ->latest('id')
                    ->first();

                if ($moodleLulus) {
                    $prodiLulus = $moodleLulus->prodi_lulus;
                }
            }

            // D. Fallback: Verifikasi Jalur Konversi / Pindahan di pengelolaan_jalur
            if (empty($prodiLulus)) {
                $verifAkademik = \App\Models\PengelolaanJalur::where('user_id', $mahasiswa->user_id)->first();
                if ($verifAkademik && !empty($verifAkademik->prodi_diterima)) {
                    $prodiDb = ProgramStudi::where('kode_prodi', $verifAkademik->prodi_diterima)
                        ->orWhere('nama_prodi', $verifAkademik->prodi_diterima)
                        ->first();
                    if ($prodiDb) {
                        $prodiLulus = $prodiDb->kode_prodi;
                    }
                }
            }
        }

        if (empty($prodiLulus)) {
            return [
                'valid'      => false,
                'reason'     => 'Mahasiswa belum memiliki status kelulusan prodi yang sah (jurusan_lulusan/hasil seleksi belum ditetapkan).',
                'kode_prodi' => null,
                'nama_prodi' => null,
            ];
        }

        // 2. TENTUKAN PRODI PEMBAYARAN UKT
        $prodiBayar = null;

        // A. Pengecualian Penerima Beasiswa Pegawai
        if ($masterUser && $masterUser->jenis_beasiswa === 'pegawai') {
            $prodiBayar = $prodiLulus; // Bebas tagihan UKT mandiri
        } else {
            // B. Cek Tagihan Selisih UKT yang Lunas (jika pernah pindah prodi)
            $lunasSelisih = SlikBilling::where('user_id', $mahasiswa->user_id)
                ->where('order_id', 'like', '%UKT-SELISIH%')
                ->whereRaw('LOWER(status) IN (?, ?)', ['paid', 'lunas'])
                ->latest('id')
                ->first();

            if ($lunasSelisih && !empty($lunasSelisih->kode_prodi)) {
                $prodiBayar = $lunasSelisih->kode_prodi;
            } else {
                // C. Cek Tagihan UKT Utama yang Lunas
                $lunasUtama = SlikBilling::where('user_id', $mahasiswa->user_id)
                    ->where('order_id', 'like', 'UKT-%')
                    ->where('order_id', 'not like', '%UKT-SELISIH%')
                    ->whereRaw('LOWER(status) IN (?, ?)', ['paid', 'lunas'])
                    ->latest('id')
                    ->first();

                if ($lunasUtama && !empty($lunasUtama->kode_prodi)) {
                    $prodiBayar = $lunasUtama->kode_prodi;
                }
            }
        }

        // D. Normalisasi Kode Apoteker jika di UKT tercatat 00000
        if (($prodiBayar === '00000' || in_array($prodiBayar, self::KODE_APOTEKER)) && $prodiLulus === '48901') {
            $prodiBayar = '48901';
        }

        if (empty($prodiBayar)) {
            return [
                'valid'      => false,
                'reason'     => 'Mahasiswa belum melunasi tagihan UKT.',
                'kode_prodi' => null,
                'nama_prodi' => null,
            ];
        }

        // 3. VALIDASI KESAMAAN (PRODI KELULUSAN HARUS SAMA DENGAN PRODI BAYAR)
        if ($prodiLulus !== $prodiBayar) {
            $namaProdiLulus = ProgramStudi::where('kode_prodi', $prodiLulus)->value('nama_prodi') ?? $prodiLulus;
            $namaProdiBayar = ProgramStudi::where('kode_prodi', $prodiBayar)->value('nama_prodi') ?? $prodiBayar;

            return [
                'valid'      => false,
                'reason'     => "Prodi kelulusan ({$namaProdiLulus} [{$prodiLulus}]) tidak sama dengan prodi pembayaran UKT ({$namaProdiBayar} [{$prodiBayar}]). Generate NIM ditolak.",
                'kode_prodi' => null,
                'nama_prodi' => null,
            ];
        }

        $prodiModel = ProgramStudi::where('kode_prodi', $prodiLulus)->first();
        if (!$prodiModel) {
            return [
                'valid'      => false,
                'reason'     => "Kode program studi '{$prodiLulus}' tidak terdaftar di tabel program_studi.",
                'kode_prodi' => null,
                'nama_prodi' => null,
            ];
        }

        return [
            'valid'      => true,
            'reason'     => null,
            'kode_prodi' => $prodiLulus,
            'nama_prodi' => $prodiModel->nama_prodi,
        ];
    }

    /**
     * Format periode masuk: {tahun}{kode_semester}
     *
     * Kode semester:
     *   Bulan >= 7 (Jul–Des) = semester ganjil   kode "2"  (contoh: 20262)
     *   Bulan <  7 (Jan–Jun) = semester genap    kode "1"  (contoh: 20261)
     *
     * Berlaku sama untuk S1 maupun S2.
     * S2 April  (bulan 4, < 7)  → 20261
     * S2 Sep    (bulan 9, >= 7)  → 20262
     * S1 masuk Sep (bulan 9)     → 20262
     */
    private function getPeriodeMasuk(string $jenjang): string
    {
        $year = now()->year;
        return $year . '1'; // 20261 (Semester Ganjil 2026)
    }

    private function getStats(): array
    {
        $baseQuery = fn() => DataMahasiswaFinal::whereHas('masterUser', fn($q) =>
            $q->where('va_status', 'paid')->orWhere('jenis_beasiswa', 'pegawai')
        )->whereDoesntHave('pendaftarGetNim')
        ->whereNotIn('data_mahasiswa_finals.user_id', function ($sub) {
            $sub->select('user_id')->from('pendaftar_get_nim')->whereNotNull('user_id');
        })
            ->whereNotIn('data_mahasiswa_finals.user_id', function ($sub) {
                $sub->select('user_id')->from('pendaftar_get_nim')->whereNotNull('user_id');
            });

        $s2Codes = "'" . implode("','", self::KODE_S2) . "'";
        $apotekerCodes = "'" . implode("','", self::KODE_APOTEKER) . "'";
        $nonS1Codes = "'" . implode("','", array_merge(self::KODE_S2, self::KODE_APOTEKER)) . "'";

        return [
            'belum_nim_s1'  => $baseQuery()
                ->whereHas('masterEreg', fn($q) =>
                    $q->whereRaw("COALESCE(NULLIF(NULLIF(jurusan_lulusan, ''), '00000'), jurusan1) NOT IN ({$nonS1Codes})")
                      ->whereRaw("COALESCE(NULLIF(NULLIF(jurusan_lulusan, ''), '00000'), jurusan1) IS NOT NULL")
                )
                ->whereDoesntHave('user', fn($q) => $q->where('profesi', 'Apoteker Farmasi'))
                ->count(),
            'belum_nim_s2'  => $baseQuery()
                ->whereHas('masterEreg', fn($q) =>
                    $q->whereRaw("COALESCE(NULLIF(NULLIF(jurusan_lulusan, ''), '00000'), jurusan1) IN ({$s2Codes})")
                )
                ->count(),
            'belum_nim_apoteker' => $baseQuery()
                ->where(function ($q) use ($apotekerCodes) {
                    $q->whereHas('user', fn($q2) => $q2->where('profesi', 'Apoteker Farmasi'))
                      ->orWhereHas('masterEreg', fn($q3) => 
                          $q3->whereRaw("COALESCE(NULLIF(NULLIF(jurusan_lulusan, ''), '00000'), jurusan1) IN ({$apotekerCodes})")
                      );
                })
                ->count(),
            'sudah_nim'     => PendaftarGetNim::count(),
            'terkirim_sipt' => PendaftarGetNim::where('message_sipt', 'Success')->count(),
        ];
    }
}