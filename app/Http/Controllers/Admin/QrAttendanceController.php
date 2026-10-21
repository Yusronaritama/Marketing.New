<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QrAttendance;
use App\Models\User;
use App\Models\DataMahasiswaFinal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

// Install via: composer require simplesoftwareio/simple-qrcode
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrAttendanceController extends Controller
{
    // ───────────────────────────────────────────────────────────
    // ADMIN: Halaman daftar mahasiswa + tombol generate QR
    // ───────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $sesi       = $request->get('sesi', 'Daftar Hadir');
        $search     = $request->get('search');
        $filterScan = $request->get('filter'); // all | scanned | pending

        // Mahasiswa yang sudah isi data_mahasiswa_finals
        $query = User::whereHas('dataMahasiswaFinal')
            ->with([
                'dataMahasiswaFinal',
                'masterEreg',
                'qrAttendances' => fn($q) => $q->where('sesi', $sesi)->latest(),
            ]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('dataMahasiswaFinal', fn($q2) =>
                      $q2->where('nama_mahasiswa', 'like', "%{$search}%")
                  );
            });
        }

        $users = $query->paginate(20)->withQueryString();

        // Statistik sesi ini
        $stats = [
            'total'   => QrAttendance::where('sesi', $sesi)->count(),
            'scanned' => QrAttendance::where('sesi', $sesi)->where('status', 'scanned')->count(),
            'active'  => QrAttendance::where('sesi', $sesi)->where('status', 'active')->count(),
            'expired' => QrAttendance::where('sesi', $sesi)->where('status', 'expired')->count(),
        ];

        // Daftar sesi yang pernah ada
        $daftarSesi = QrAttendance::select('sesi')->distinct()->pluck('sesi');

        return view('admin.qr-attendance.index', compact(
            'users', 'sesi', 'search', 'stats', 'daftarSesi'
        ));
    }

    // ───────────────────────────────────────────────────────────
    // ADMIN: Generate QR untuk satu mahasiswa
    // ───────────────────────────────────────────────────────────
    public function generate(Request $request)
    {
        $request->validate([
            'user_id'     => 'required|exists:users,id',
            'sesi'        => 'required|string|max:100',
            'expired_at'  => 'nullable|date|after:now',
        ]);

        $userId = $request->user_id;
        $sesi   = trim($request->sesi);

        // Cek apakah user sudah isi data finals
        $hasFinal = DB::table('data_mahasiswa_finals')
                      ->where('user_id', $userId)
                      ->exists();

        if (!$hasFinal) {
            return back()->with('error', 'Mahasiswa belum mengisi data finals.');
        }

        // Batalkan QR aktif sebelumnya untuk sesi yang sama
        QrAttendance::where('user_id', $userId)
                    ->where('sesi', $sesi)
                    ->where('status', 'active')
                    ->update(['status' => 'expired']);

        // Buat QR baru
        $qr = QrAttendance::create([
            'user_id'    => $userId,
            'token'      => QrAttendance::generateToken(),
            'status'     => 'active',
            'sesi'       => $sesi,
            'expired_at' => $request->expired_at
                            ? Carbon::parse($request->expired_at)
                            : null,
        ]);

        Log::info("QR generated for user_id={$userId} sesi={$sesi} token={$qr->token}");

        return back()->with('success', "QR Code berhasil dibuat untuk sesi \"{$sesi}\".");
    }

    // ───────────────────────────────────────────────────────────
    // ADMIN: Generate QR massal untuk semua mahasiswa
    // ───────────────────────────────────────────────────────────
    public function generateBulk(Request $request)
    {
        $request->validate([
            'sesi'       => 'required|string|max:100',
            'expired_at' => 'nullable|date|after:now',
        ]);

        $sesi      = trim($request->sesi);
        $expiredAt = $request->expired_at ? Carbon::parse($request->expired_at) : null;

        // Semua user yang sudah isi finals
        $userIds = DB::table('data_mahasiswa_finals')->pluck('user_id');

        $generated = 0;
        foreach ($userIds as $userId) {
            // Expire QR aktif sebelumnya
            QrAttendance::where('user_id', $userId)
                        ->where('sesi', $sesi)
                        ->where('status', 'active')
                        ->update(['status' => 'expired']);

            QrAttendance::create([
                'user_id'    => $userId,
                'token'      => QrAttendance::generateToken(),
                'status'     => 'active',
                'sesi'       => $sesi,
                'expired_at' => $expiredAt,
            ]);

            $generated++;
        }

        Log::info("Bulk QR generated: {$generated} QR untuk sesi={$sesi}");

        return back()->with('success', "Berhasil generate {$generated} QR Code untuk sesi \"{$sesi}\".");
    }

    // ───────────────────────────────────────────────────────────
    // PUBLIC: Halaman tampil QR (untuk ditunjukkan ke scanner)
    // ───────────────────────────────────────────────────────────
    public function showQr(Request $request, $userId)
    {
        $qr = QrAttendance::where('user_id', $userId)
                          ->where('status', 'active')
                          ->latest()
                          ->first();

        if (!$qr) {
            return view('admin.qr-attendance.not-found');
        }

        // Generate gambar QR sebagai SVG/PNG inline
        $qrImage = QrCode::format('svg')
                         ->size(300)
                         ->errorCorrection('H')
                         ->generate($qr->getQrContent());

        $mahasiswa = DB::table('data_mahasiswa_finals')
                       ->where('user_id', $userId)
                       ->first();

        return view('admin.qr-attendance.show', compact('qr', 'qrImage', 'mahasiswa'));
    }

    // ───────────────────────────────────────────────────────────
    // ADMIN: Daftar hadir per sesi
    // ───────────────────────────────────────────────────────────
    public function attendanceList(Request $request)
    {
        $sesi = $request->get('sesi', 'Daftar Hadir');

        $list = QrAttendance::with(['user.dataMahasiswaFinal', 'user.masterEreg'])
                            ->where('sesi', $sesi)
                            ->where('status', 'scanned')
                            ->orderBy('scanned_at', 'asc')
                            ->paginate(25)
                            ->withQueryString();

        $daftarSesi = QrAttendance::select('sesi')->distinct()->pluck('sesi');

        return view('admin.qr-attendance.list', compact('list', 'sesi', 'daftarSesi'));
    }

    // ───────────────────────────────────────────────────────────
    // ADMIN: Expire manual satu QR
    // ───────────────────────────────────────────────────────────
    public function expireQr(Request $request, $id)
    {
        $qr = QrAttendance::findOrFail($id);
        $qr->update(['status' => 'expired']);

        return back()->with('info', 'QR Code berhasil di-expire.');
    }

    // ───────────────────────────────────────────────────────────
    // Private helper: ambil biodata lengkap mahasiswa
    // ───────────────────────────────────────────────────────────
    private function getMahasiswaData(int $userId): ?object
    {
        return DB::table('data_mahasiswa_finals')
                 ->leftJoin('users', 'users.id', '=', 'data_mahasiswa_finals.user_id')
                 ->leftJoin('master_ereg', 'master_ereg.user_id', '=', 'data_mahasiswa_finals.user_id')
                 ->leftJoin('master_users', 'master_users.user_id', '=', 'data_mahasiswa_finals.user_id')
                 ->leftJoin('master_moodle_lulus', 'master_moodle_lulus.master_user_id', '=', 'master_users.id')
                 ->leftJoin('program_studi as ps1', 'ps1.kode_prodi', '=', 'master_ereg.jurusan1')
                 ->leftJoin('program_studi as ps2', 'ps2.kode_prodi', '=', 'master_ereg.jurusan2')
                 ->where('data_mahasiswa_finals.user_id', $userId)
                 ->select(
                     'data_mahasiswa_finals.*',
                     'users.email',
                     'users.phone',
                     'master_ereg.jalur_pendaftaran',
                     'master_ereg.jurusan1',
                     'master_ereg.jurusan2',
                     'master_ereg.status as status_pendaftaran',
                     'master_users.gelombang',
                     'master_moodle_lulus.nama_prodi_lulus',
                     'master_moodle_lulus.nilai_quiz',
                     'master_moodle_lulus.status_kelulusan',
                     'ps1.nama_prodi as nama_prodi_1',
                     'ps1.jenjang as jenjang_prodi_1',
                     'ps2.nama_prodi as nama_prodi_2',
                     'ps2.jenjang as jenjang_prodi_2',
                 )
                 ->first();
    }

    /**
     * Tambahkan route ini ke routes.php:
     * Route::get('/qr/scanner', [QrAttendanceController::class, 'scannerPage'])->name('qr.scanner');
     */

    // ───────────────────────────────────────────────────────────
    // PUBLIC: Halaman scanner (untuk petugas)
    // ───────────────────────────────────────────────────────────
    public function scannerPage(Request $request)
    {
        return view('admin.qr-attendance.scanner');
    }

    // ───────────────────────────────────────────────────────────
    // PUBLIC: Endpoint scan QR — support HTML & JSON
    // ───────────────────────────────────────────────────────────
    public function scan(Request $request, string $token)
    {
        $isAjax = $request->wantsJson() || $request->ajax();

        $qr = QrAttendance::where('token', $token)->first();

        // QR tidak ditemukan
        if (!$qr) {
            $payload = ['status' => 'invalid', 'message' => 'QR Code tidak valid atau tidak ditemukan.', 'qr' => null, 'data' => null];
            return $isAjax
                ? response()->json($payload)
                : view('qr-attendance.result', $payload);
        }

        // QR sudah di-scan
        if ($qr->status === 'scanned') {
            $data    = $this->getMahasiswaData($qr->user_id);
            $payload = [
                'status'  => 'already_scanned',
                'message' => 'QR Code ini sudah digunakan pada ' . $qr->scanned_at?->format('d/m/Y H:i:s'),
                'qr'      => $qr,
                'data'    => $data,
            ];
            return $isAjax ? response()->json($payload) : view('qr-attendance.result', $payload);
        }

        // QR expired
        if ($qr->status === 'expired' || ($qr->expired_at && $qr->expired_at->isPast())) {
            // Auto-fix status jika belum tercatat expired di DB
            if ($qr->status !== 'expired') {
                $qr->update(['status' => 'expired']);
            }
            $payload = ['status' => 'expired', 'message' => 'QR Code sudah kadaluarsa.', 'qr' => $qr, 'data' => null];
            return $isAjax ? response()->json($payload) : view('qr-attendance.result', $payload);
        }

        // ✅ Valid — catat hadir & expire QR
        $qr->update([
            'status'           => 'scanned',
            'scanned_at'       => now(),
            'scanned_by'       => $request->ip(),
            'scanned_location' => $request->get('location', '-'),
        ]);

        Log::info("QR scanned: token={$token} user_id={$qr->user_id} ip={$request->ip()}");

        $data    = $this->getMahasiswaData($qr->user_id);
        $payload = [
            'status'  => 'success',
            'message' => 'Daftar hadir berhasil dicatat!',
            'qr'      => $qr,
            'data'    => $data,
        ];

        return $isAjax ? response()->json($payload) : view('qr-attendance.result', $payload);
    }
    
}