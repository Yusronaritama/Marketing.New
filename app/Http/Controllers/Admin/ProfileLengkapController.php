<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Biodata;
use App\Models\MasterEreg;
use App\Models\MasterMoodle;
use App\Models\MasterMoodleLulus;
use App\Models\MasterUser;
use App\Models\MasterUsersVerified;
use App\Models\FarmasiVerification;
use Illuminate\Http\Request;
use App\Models\ProgramStudi;
use Illuminate\View\View;
use App\Exports\SlikBillingExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\WhatsAppService;
use Imagick; 

class ProfileLengkapController extends Controller
{
    protected $whatsAppService;

    // Inject service melalui constructor
public function __construct(WhatsAppService $whatsAppService)
{
    $this->whatsAppService = $whatsAppService;
}
public function index(Request $request): View
{
    // 1. Logika Auto-Reset Page saat Filter Berubah
    // Jika admin melakukan pencarian atau mengubah filter, paksa kembali ke halaman 1
    if ($request->filled('search') || $request->filled('status_isian') || $request->filled('kelulusan') || $request->filled('jenjang')) {
        if (!$request->has('page')) {
            $request->merge(['page' => 1]);
        }
    }
	
    // 2. Inisialisasi Query Dasar
    // 2. Inisialisasi Query Dasar
    $query = User::with([
    'biodata', 'masterUser', 'masterEreg', 'masterUsersVerified',
    'masterUser.masterMoodleLulus', 'masterUser.masterMoodle.quizAttempts', 
    
    // PERBAIKAN DI SINI: Tambahkan pengecekan is_active = 1
    'slikBilling' => function ($q) {
        $q->whereIn(DB::raw('LOWER(status)'), ['paid', 'settlement', 'success', '00', 'capture', 'lunas'])
          ->where('is_active', 1) 
          ->orderBy('updated_at', 'desc'); 
    },
    
    'dataMahasiswaFinal'
])->where('role', 'mahasiswa_baru');


    // Filter Role Admin (Khusus Akademik/Farmasi hanya melihat yang sudah completed)
    if (in_array(Auth::user()->role, ['admin_akademik', 'admin_farmasi'])) {
        $query->whereHas('masterEreg', function($q) {
            $q->where('status', 'completed');
        });
    }
    if ($request->filled('jalur_pendaftaran')) {
         $jalur = $request->jalur_pendaftaran;
         $query->whereHas('masterEreg', function($q) use ($jalur) {
            $q->where('jalur_pendaftaran', $jalur);
        });
    }
    // Filter Kelulusan
    if ($request->filled('kelulusan')) {
        if ($request->kelulusan == '1') {
            $query->whereHas('masterUser.masterMoodleLulus');
        } elseif ($request->kelulusan == '0') {
            $query->whereDoesntHave('masterUser.masterMoodleLulus');
        }
    }

    // Filter Verifikasi Admin
    if ($request->filled('verifikasi')) {
        if ($request->verifikasi == 'verified') {
            $query->whereHas('masterUsersVerified');
        } elseif ($request->verifikasi == 'unverified') {
            $query->whereDoesntHave('masterUsersVerified');
        }
    }

    // Filter Jenjang (S1/S2)
    if ($request->filled('jenjang')) {
        $jenjang = $request->jenjang;
        $kodeProdiList = ProgramStudi::where('jenjang', $jenjang)->pluck('kode_prodi')->toArray();
        $query->whereHas('masterEreg', function($q) use ($kodeProdiList) {
            $q->whereIn('jurusan1', $kodeProdiList)->orWhereIn('jurusan2', $kodeProdiList);
        });
    }

    // Logika Filter Status Isian
    if ($request->filled('status_isian')) {
        switch ($request->status_isian) {
            case 'empty':
$query->whereHas('masterUser')->doesntHave('biodata');
        break;
            case 'only_biodata':
                $query->has('biodata')->doesntHave('masterEreg');
                break;
            case 'filled':
                $query->where(function($q) {
                    $q->has('biodata')->orHas('masterEreg');
                });
                break;
            case 'already_ereg':
                $query->whereHas('masterEreg', function($q) {
                    $q->where('status', 'completed');
                });
                break;
            case 'already_paid':
                $query->whereHas('slikBilling', function($q) {
                    $q->whereIn('status', ['paid', 'settlement', 'success'])->where('is_active', 1);
                });
                break;
            case 'already_final':
                $query->has('dataMahasiswaFinal');
                break;
        }
    }
if ($request->filled('status_slik')) {
    $statusSlik = strtolower($request->status_slik); // paid, cancel, expired
    $query->whereHas('slikBilling', function($q) use ($statusSlik) {
        if ($statusSlik === 'paid') {
            // Menangkap semua variasi status lunas sesuai standarisasi sistem Anda
            $q->whereIn(DB::raw('LOWER(status)'), ['paid', 'settlement', 'success', '00', 'capture', 'lunas']);
        } else {
            $q->where(DB::raw('LOWER(status)'), $statusSlik);
        }
    });
}
    // Logic Pencarian (Search)
    if ($request->filled('search')) {
        $rawSearch = trim($request->search);
        $search = str_replace(' ', '%', $rawSearch); 
        $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhereHas('masterEreg', function($qEreg) use ($search) {
                  $qEreg->where('nama', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('gmail', 'like', "%{$search}%");
              })
              ->orWhereHas('masterUser', function($qMaster) use ($search) {
              $qMaster->where('va_number', 'like', "%{$search}%")
                      ->orWhere('va_status', 'like', "%{$search}%");
          });
        });
    }
    $baseStatQuery = User::where('role', 'mahasiswa_baru');
    $statEmptyCount = (clone $baseStatQuery)
    ->has('masterUser') 
    ->doesntHave('biodata')
    ->count();
    $statOnlyBioCount  = (clone $baseStatQuery)->has('biodata')->doesntHave('masterEreg')->count();
$belumbayarregist = (clone $baseStatQuery)
    ->whereHas('masterUser', function($q) {
        $q->where('va_status', '!=', 'paid');
    })
    ->count();
    $statEregCount     = (clone $baseStatQuery)->whereHas('masterEreg', fn($q) => $q->where('status', 'completed'))->count();
    $statPaidCount     = (clone $baseStatQuery)->whereHas('slikBilling', fn($q) => $q->whereIn('status', ['paid', 'settlement', 'success'])->where('is_active', 1))->count();
    $statLulusCount    = (clone $baseStatQuery)->whereHas('masterUser.masterMoodleLulus')->count();
    $statFinalCount    = (clone $baseStatQuery)->has('dataMahasiswaFinal')->count();
    $statTotalAll      = (clone $baseStatQuery)->count();

    $statistikDeskriptif = "Yang belum mengisi apapun ($statEmptyCount), belum bayar registrasi ($belumbayarregist) mengisi biodata saja ($statOnlyBioCount), sudah mengisi ereg ($statEregCount), sudah bayar ($statPaidCount), sudah ujian ($statLulusCount), dan sudah final PDDIKTI ($statFinalCount). Total keseluruhan: $statTotalAll mahasiswa.";
    $perPage = $request->input('per_page', 20);
    if ($perPage === 'all') {
        $totalItems = $query->count();
        $perPage = $totalItems > 0 ? $totalItems : 1;
    }
	if ($request->filled('kelola_khusus')) {
	        $kelola = strtolower($request->kelola_khusus);
	        $query->whereHas('masterEreg', function($q) use ($kelola) {
	            if ($kelola === 'ekstensi') {
	                $q->where('jalur_pendaftaran', 'LIKE', '%Ekstensi%');
	            } elseif ($kelola === 'pindahan') {
	                $q->where('jalur_pendaftaran', 'LIKE', '%Pindahan%');
	            } elseif ($kelola === 'rpl') {
	                $q->where('jalur_pendaftaran', 'LIKE', '%RPL%');
	            }
	        });
	    }
    $users = $query->orderBy('created_at', 'desc')->paginate($perPage);
    $users->appends($request->all());
    $users->getCollection()->transform(function ($user) {
        $biodataComplete = false;
        if ($user->biodata) {
            $wajibBiodata = ['nik', 'tempat_lahir', 'tanggal_lahir', 'nama_ibu', 'agama', 'alamat'];
            $filled = collect($wajibBiodata)->filter(fn($col) => !empty($user->biodata->$col))->count();
            if (!empty($user->phone) || !empty($user->biodata->no_telepon)) $filled++;
            $biodataComplete = ($filled >= count($wajibBiodata) + 1);
        }
        $eregComplete = ($user->masterEreg && $user->masterEreg->status === 'completed');
        
        // --- PENGECEKAN KETAT MURNI DARI TABEL SLIK BILLINGS ---
        $isSlikPaid = false;

        if ($user->slikBilling) {
            $validStatuses = ['paid', 'settlement', 'success', '00', 'capture', 'lunas'];
            
            // Mengecek apakah relasi mengembalikan Collection (kasus hasMany)
            if ($user->slikBilling instanceof \Illuminate\Support\Collection) {
                // Filter ulang secara ketat di memori
                $isSlikPaid = $user->slikBilling->contains(function ($item) use ($validStatuses) {
                    return $item->is_active == 1 && in_array(strtolower($item->status ?? ''), $validStatuses);
                });
            } 
            // Mengecek jika relasi mengembalikan 1 objek (kasus hasOne)
            else {
                // Pastikan objek memiliki ID (bukan objek withDefault kosong) dan statusnya valid
                if (!empty($user->slikBilling->id) && 
                    $user->slikBilling->is_active == 1 && 
                    in_array(strtolower($user->slikBilling->status ?? ''), $validStatuses)) {
                    $isSlikPaid = true; 
                }
            }
        }
        
        // 3. Gabungkan hasil -> KITA HANYA MENGGUNAKAN TABEL SLIK (Abaikan data dari Master User)
        $billingComplete = $isSlikPaid;

        $user->progress_details = [
            'Biodata'          => ['status' => $biodataComplete],
            'Registrasi Ulang' => ['status' => $eregComplete],
            'Ujian'            => ['status' => (bool)($user->masterUser && $user->masterUser->masterMoodleLulus)],
            'Pembayaran'       => ['status' => $billingComplete], // Kini murni membaca tabel SLIK
            'PDDIKTI'          => ['status' => (bool)$user->dataMahasiswaFinal],
        ];
        
        // Jangan lupa tambahkan ini agar badge tulisan "Belum Registrasi" dll tampil benar
        $user->ereg_status = $user->masterEreg ? $user->masterEreg->status : null;
        
        return $user;
    });

    // 5. Data Pendukung Dashboard
    $totalUsers = $statTotalAll;
    $verifiedCount = User::where('role', 'mahasiswa_baru')->whereHas('masterUsersVerified')->count();
    $unverifiedCount = $totalUsers - $verifiedCount;
    $lulusCount = $statLulusCount;
    $unfilledCount = $statEmptyCount;
    $onlyBiodataCount = $statOnlyBioCount;
    $belumbayarregist = $belumbayarregist;

    $programStudi = ProgramStudi::where('status_prodi', 1)->orderBy('urutan_tampil', 'asc')->get();
    $prodiList = $programStudi->pluck('nama_prodi', 'kode_prodi');
    $kelulusanData = MasterMoodleLulus::all()->keyBy('master_user_id');
    $farmasiVerifications = FarmasiVerification::all()->keyBy('user_id');

    $eregStatusLabels = ['pending' => 'Pending', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'rejected' => 'Ditolak', 'incomplete' => 'Tidak Lengkap', null => 'Belum Registrasi'];
    $eregStatusColors = ['pending' => 'cyberpunk-badge-yellow', 'in_progress' => 'cyberpunk-badge-blue', 'completed' => 'cyberpunk-badge-green', 'rejected' => 'cyberpunk-badge-red', 'incomplete' => 'cyberpunk-badge-orange', null => 'cyberpunk-badge-gray'];
    $pengelolaanStatus = DB::table('pengelolaan_jalur')
        ->pluck('status_verifikasi', 'user_id')
        ->toArray();
    $pageData = [
        'current_page' => $users->currentPage(),
        'total_pages' => $users->lastPage(),
        'total_items' => $users->total(),
        'per_page' => $perPage,
        'filters' => $request->all(),
        'stat_text' => $statistikDeskriptif 
    ];
    $jalurPendaftaranList = MasterEreg::whereNotNull('jalur_pendaftaran')
    ->distinct()
    ->pluck('jalur_pendaftaran');
    return view('admin.profile-lengkap.index', compact(
        'users', 'kelulusanData', 'farmasiVerifications',
        'totalUsers', 'verifiedCount', 'unverifiedCount', 'lulusCount','unfilledCount', 'onlyBiodataCount',
        'programStudi', 'prodiList', 'eregStatusLabels', 'eregStatusColors', 'pageData','belumbayarregist','jalurPendaftaranList'
    ,'pengelolaanStatus'
    ));
}
public function luluskanUser(Request $request, $id)
{
    $request->validate([
        'nilai_manual' => 'required|numeric|min:0|max:100',
    ]);
 
    try {
        DB::beginTransaction();
 
        // 1. Load user beserta relasi yang dibutuhkan
        $user = User::with([
            'masterUser',
            'masterEreg',
            'biodata'
        ])->findOrFail($id);
 
        // 2. Validasi: user harus punya masterUser
        if (!$user->masterUser) {
            throw new \Exception('User belum memiliki data Master User (VA Number belum digenerate).');
        }
 
        // 3. Validasi: user harus sudah mengisi E-Reg
        if (!$user->masterEreg) {
            throw new \Exception('User belum mengisi data Registrasi Ulang (E-Reg)!');
        }
 
        $masterUserId  = $user->masterUser->id;
        $nilaiManual   = $request->nilai_manual;
        $namaLengkap   = $user->biodata->nama_lengkap ?? $user->name;
        $prodiLulus    = $user->masterEreg->jurusan1;
        $namaProdi     = $this->getNamaProdi($prodiLulus);
 
        // 4. Cari data Moodle jika ada (tidak wajib ada)
        $masterMoodle = MasterMoodle::where('master_user_id', $masterUserId)->first();
 
        // 5. Jika ada data Moodle, update sync_notes-nya
        if ($masterMoodle) {
            $masterMoodle->update([
                'sync_notes' => 'LULUS - Manual (Nilai: ' . $nilaiManual . ') oleh admin pada ' . now(),
                'updated_at' => now(),
            ]);
        }
 
        // 6. Simpan/update ke master_moodle_lulus
        //    moodle_user_id bisa null jika user belum terdaftar di Moodle
        MasterMoodleLulus::updateOrCreate(
            [
                'master_user_id' => $masterUserId,
            ],
            [
                'moodle_user_id'   => $masterMoodle->moodle_user_id ?? null,
                'username'         => $masterMoodle->username ?? $user->email,
                'email'            => $user->email,
                'nama_lengkap'     => $namaLengkap,
                'prodi_lulus'      => $prodiLulus,
                'nama_prodi_lulus' => $namaProdi,
                'pilihan_lulus'    => 1,
                'quiz_id'          => $masterMoodle->quiz_id ?? 1,   // default 1 jika tidak ada
                'nilai_quiz'       => $nilaiManual,
                'attempt_count'    => 1,
                'status_kelulusan' => 'LULUS',
                'wa_sent'          => 0,
                'keterangan'       => 'Diluluskan secara manual oleh admin dengan nilai ' . $nilaiManual,
                'tanggal_lulus'    => now(),
            ]
        );
 
        DB::commit();
 
        return redirect()->back()->with('success', "User {$namaLengkap} berhasil diluluskan dengan nilai {$nilaiManual}!");
 
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Gagal luluskan manual: ' . $e->getMessage(), [
            'user_id' => $id,
            'trace'   => $e->getTraceAsString(),
        ]);
        return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
    }
}

public function toggleBeasiswa($id)
{
    try {
        $user = User::findOrFail($id);

        // Toggle nilai
        $user->kel_beasiswa = ($user->kel_beasiswa == 1) ? 0 : 1;
        $user->save();

        if ($user->kel_beasiswa == 1) {
            return redirect()->back()
                ->with('success', 'Mahasiswa berhasil ditetapkan sebagai penerima beasiswa.');
        }

        return redirect()->back()
            ->with('success', 'Status beasiswa berhasil dibatalkan.');

    } catch (\Exception $e) {
        return redirect()->back()
            ->with('error', $e->getMessage());
    }
}

private function translateColumnName($column)
{
    $translations = [
        'nik' => 'NIK',
        'tempat_lahir' => 'Tempat Lahir',
        'tanggal_lahir' => 'Tanggal Lahir',
        'nama_ibu' => 'Nama Ibu',
        'agama' => 'Agama',
        'alamat' => 'Alamat',
        'no_telepon' => 'Nomor Telepon',
    ];
    
    return $translations[$column] ?? $column;
}

/**
 * Helper method untuk menerjemahkan nama file
 */
private function translateFileName($file)
{
    $translations = [
        'file_kk' => 'Kartu Keluarga',
        'file_ijazah' => 'Ijazah',
        'file_ktp' => 'KTP',
        'file_ijazah_s1' => 'Ijazah S1',
        'file_transkrip_s1' => 'Transkrip S1',
    ];
    
    return $translations[$file] ?? $file;
}
public function resetPage(Request $request)
{
    // Hapus session halaman
    session()->forget('profile_lengkap_page');
    
    // Redirect kembali ke index dengan parameter reset
    return redirect()->route('admin.profile-lengkap.index', [
        'page' => 1,
        'reset' => 'true'
    ]);
}

/**
 * Clear all filters dan kembali ke halaman 1
 */
public function clearFilters(Request $request)
{
    // Hapus semua session yang terkait
    session()->forget('profile_lengkap_page');
    session()->forget('profile_lengkap_filters');
    
    // Redirect ke halaman 1 tanpa filter
    return redirect()->route('admin.profile-lengkap.index', ['page' => 1]);
}
    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $user = User::with([
            'biodata',
            'masterUser',
            'masterEreg',
            'masterUsersVerified',
            'farmasiVerifications'
        ])->where('role', 'mahasiswa_baru')
          ->findOrFail($id);

        // Filter khusus untuk admin_akademik dan admin_farmasi
        if (in_array(Auth::user()->role, ['admin_akademik', 'admin_farmasi'])) {
            if (!$user->masterEreg || $user->masterEreg->status !== 'completed') {
                abort(403, 'Anda tidak memiliki akses ke data ini.');
            }
        }

        // Ambil data moodle dan kelulusan
        $moodleUser = null;
        $kelulusan = null;

        if ($user->masterUser) {
            $moodleUser = MasterMoodle::where('master_user_id', $user->masterUser->id)->first();
            $kelulusan = MasterMoodleLulus::where('master_user_id', $user->masterUser->id)
                ->orWhere('moodle_user_id', $moodleUser->moodle_user_id ?? null)
                ->first();
        }

        $biodata = $user->biodata;
        $ereg = $user->masterEreg;

        // PERBAIKAN: Gunakan scope 'urutan()' bukan 'urutTampil()'
        $programStudi = ProgramStudi::where('status_prodi', 1) // Filter aktif
            ->orderBy('urutan_tampil', 'asc')
            ->orderBy('nama_prodi', 'asc')
            ->get(['kode_prodi', 'nama_prodi', 'jenjang']);

        // Tentukan status verifikasi
        $verifikasi = $user->masterUsersVerified && $user->masterUsersVerified->count() > 0;

        \Log::info("User verification status in show method:", [
            'user_id' => $user->id,
            'verifikasi' => $verifikasi,
            'masterUsersVerified_count' => $user->masterUsersVerified ? $user->masterUsersVerified->count() : 0,
            'program_studi_count' => $programStudi->count()
        ]);

        return view('admin.profile-lengkap.show', compact(
            'user',
            'kelulusan',
            'moodleUser',
            'biodata',
            'ereg',
            'verifikasi',
            'programStudi'
        ));
    }
    
    /**
     * Verifikasi khusus untuk prodi Farmasi
     */
    public function verifyFarmasi($id)
    {
        $user = User::with(['masterEreg', 'biodata'])
                    ->where('role', 'mahasiswa_baru')
                    ->findOrFail($id);
        
        $allowedRoles = ['admin', 'admin_farmasi'];
        if (!in_array(Auth::user()->role, $allowedRoles)) {
            abort(403, 'Hanya admin dan admin farmasi yang dapat melakukan verifikasi farmasi.');
        }
        
        // Cek apakah memilih prodi farmasi
        $isFarmasi = $this->isProdiFarmasi($user->masterEreg);
        if (!$isFarmasi) {
            return redirect()->back()
                ->with('error', 'User tidak memilih prodi Farmasi');
        }
        
        // Simpan ke farmasi_verifications
        FarmasiVerification::updateOrCreate(
            ['user_id' => $user->id],
            [
                'master_ereg_id' => $user->masterEreg->id,
                'nama_lengkap' => $user->biodata->nama_lengkap ?? $user->name,
                'email' => $user->email,
                'nomer_hp' => $user->biodata->no_telepon ?? $user->phone ?? null,
                'phone' => $user->biodata->phone ?? $user->phone ?? null,
                'prodi_pilihan1' => $user->masterEreg->jurusan1 ?? null,
                'prodi_pilihan2' => $user->masterEreg->jurusan2 ?? null,
                'status_verifikasi' => 'verified',
                'verified_by' => Auth::id(),
                'nama_verifikator' => Auth::user()->name,
                'tanggal_verifikasi' => now()
            ]
        );
        $phone = $user->biodata->no_telepon ?? $user->phone;
    if ($phone) {
        $nama = $user->biodata->nama_lengkap ?? $user->name;
        $message = "Halo, Kak *{$nama}* 🎉\n\nSelamat! Berkas pendaftaran Anda untuk Program Studi *FARMASI* telah diverifikasi dan dinyatakan *LULUS SELEKSI BERKAS*.\n\nSilakan cek dashboard pendaftaran Anda secara berkala untuk tahapan selanjutnya.\n\nTerima kasih.";
        
        try {
            $this->whatsAppService->sendMessage($phone, $message);
        } catch (\Exception $e) { \Log::error("WA Farmasi Quick Verify Error: " . $e->getMessage()); }
    }
        return redirect()->back()->with('success', 'Verifikasi kelulusan farmasi berhasil disimpan.');
    }
    
    /**
     * Form untuk verifikasi farmasi lanjutan
     */
    public function createFarmasiVerification($id): View
    {
        $user = User::with(['masterEreg', 'biodata'])
                    ->where('role', 'mahasiswa_baru')
                    ->findOrFail($id);
        
        $allowedRoles = ['admin', 'admin_farmasi'];
        if (!in_array(Auth::user()->role, $allowedRoles)) {
            abort(403, 'Hanya admin dan admin farmasi yang dapat mengakses halaman ini.');
        }
        
        $isFarmasi = $this->isProdiFarmasi($user->masterEreg);
        if (!$isFarmasi) {
            abort(403, 'User tidak memilih prodi Farmasi.');
        }
        
        $existingVerification = FarmasiVerification::where('user_id', $user->id)->first();
        
        return view('admin.profile-lengkap.farmasi-verification', compact(
            'user',
            'existingVerification'
        ));
    }
    
    /**
     * Store farmasi verification
     */
    public function storeFarmasiVerification(Request $request, $id)
{
    $user = User::with(['masterEreg', 'biodata'])
                ->where('role', 'mahasiswa_baru')
                ->findOrFail($id);
    
    $allowedRoles = ['admin', 'admin_farmasi'];
    if (!in_array(Auth::user()->role, $allowedRoles)) {
        abort(403, 'Hanya admin dan admin farmasi yang dapat melakukan verifikasi.');
    }
    
    $request->validate([
        'status_verifikasi' => 'required|in:pending,verified,rejected',
        'keterangan' => 'nullable|string|max:500'
    ]);
    
    // 1. Simpan/Update ke Database
    $verification = FarmasiVerification::updateOrCreate(
        ['user_id' => $user->id],
        [
            'master_ereg_id' => $user->masterEreg->id,
            'nama_lengkap' => $user->biodata->nama_lengkap ?? $user->name,
            'email' => $user->email,
            'nomer_hp' => $user->biodata->no_telepon ?? $user->phone ?? null,
            'phone' => $user->biodata->phone ?? $user->phone ?? null,
            'prodi_pilihan1' => $user->masterEreg->jurusan1 ?? null,
            'prodi_pilihan2' => $user->masterEreg->jurusan2 ?? null,
            'status_verifikasi' => $request->status_verifikasi,
            'verified_by' => Auth::id(),
            'nama_verifikator' => Auth::user()->name,
            'tanggal_verifikasi' => $request->status_verifikasi === 'verified' ? now() : null,
            'keterangan' => $request->keterangan
        ]
    );

    // 2. Kirim Notifikasi WhatsApp jika status bukan 'pending'
    if (in_array($request->status_verifikasi, ['verified', 'rejected'])) {
        $phone = $user->biodata->no_telepon ?? $user->phone;
        $nama = $user->biodata->nama_lengkap ?? $user->name;
        
        if ($phone) {
            $statusText = $request->status_verifikasi === 'verified' ? "*LULUS SELEKSI*" : "*TIDAK LULUS SELEKSI*";
            $emoji = $request->status_verifikasi === 'verified' ? "🎉" : "✉️";

            $message = "Halo, Kak *{$nama}* {$emoji}\n\n";
            $message .= "Kami menginformasikan hasil ujian pada prodi *FARMASI*:\n\n";
            $message .= "Status: {$statusText}\n";
            
            if ($request->keterangan) {
                $message .= "Selamat Atas Lulusnya kak ^_^\n\n";
            }

            if ($request->status_verifikasi === 'verified') {
                $message .= "Silakan melanjutkan ke tahapan berikutnya sesuai panduan di dashboard PMB Anda.\n\n";
            } else {
                $message .= "Mohon maaf, Anda belum dapat melanjutkan ke prodi Farmasi. Silakan periksa kembali berkas atau hubungi admin jika ada pertanyaan.\n\n";
            }

            $message .= "Terima kasih.";

            try {
                $this->whatsAppService->sendMessage($phone, $message);
                \Log::info("WA Farmasi Notification sent: User ID " . $id . " Status: " . $request->status_verifikasi);
            } catch (\Exception $e) {
                \Log::error("Failed to send Farmasi WA Notification: " . $e->getMessage());
            }
        }
    }
    
    return redirect()->back()->with('success', 'Verifikasi farmasi berhasil disimpan dan notifikasi WA telah diproses.');
}

    /**
     * Update Program Studi
     */
 public function updateProdi(Request $request, $id)
    {
        $request->validate([
            'jurusan1' => 'required',
            'jurusan2' => 'nullable',
        ]);

        try {
            // 1. Tambahkan relasi masterUser dan masterMoodleLulus agar bisa diakses
            $user = User::with(['masterEreg', 'masterUser.masterMoodleLulus'])->findOrFail($id);

            if (!$user->masterEreg) {
                return redirect()->back()->with('error', 'Data Master Ereg belum ada untuk user ini.');
            }

            // Validasi apakah kode prodi valid
            $kodeProdi1 = $request->jurusan1;
            $kodeProdi2 = $request->jurusan2;
            
            // 2. Cek kode prodi 1 dan ambil objek datanya (bukan cuma exists) untuk mendapatkan nama_prodi
            $prodi1 = ProgramStudi::where('kode_prodi', $kodeProdi1)
                ->where('status_prodi', 1)
                ->first();
                
            if (!$prodi1) {
                return redirect()->back()->with('error', 'Kode Program Studi 1 tidak valid atau tidak aktif.');
            }
            
            // Cek prodi 2 jika diisi
            if ($kodeProdi2) {
                $prodi2Exists = ProgramStudi::where('kode_prodi', $kodeProdi2)
                    ->where('status_prodi', 1)
                    ->exists();
                    
                if (!$prodi2Exists) {
                    return redirect()->back()->with('error', 'Kode Program Studi 2 tidak valid atau tidak aktif.');
                }
            }

            // Mulai Transaksi Database
            DB::beginTransaction();

            // 3. Update data jurusan di Master Ereg
            $user->masterEreg->update([
                'jurusan1' => $kodeProdi1,
                'jurusan2' => $kodeProdi2,
            ]);

            // 4. Update data di master_moodle_lulus JIKA user tersebut sudah memiliki data kelulusan
            if ($user->masterUser && $user->masterUser->masterMoodleLulus) {
                $user->masterUser->masterMoodleLulus->update([
                    'prodi_lulus' => $kodeProdi1,
                    'nama_prodi_lulus' => $prodi1->nama_prodi,
                    // Opsional: Sesuaikan pilihan_lulus jika memang secara default diset ke pilihan 1 oleh admin
                    'pilihan_lulus' => 1, 
                    'updated_at' => now(),
                ]);
            }

            // Simpan semua perubahan ke database
            DB::commit();

            // Log perubahan
            \Log::info('Program studi dan data kelulusan berhasil diupdate:', [
                'user_id' => $user->id,
                'jurusan1' => $kodeProdi1,
                'jurusan2' => $kodeProdi2,
                'updated_by' => \Illuminate\Support\Facades\Auth::id(),
            ]);

            return redirect()->back()->with('success', 'Program Studi dan Data Kelulusan (jika ada) berhasil diperbarui.');

        } catch (\Exception $e) {
            // Batalkan semua perubahan database jika terjadi error
            DB::rollBack();
            
            \Log::error('Error updating program studi:', [
                'error' => $e->getMessage(),
                'user_id' => $id,
                'data' => $request->all()
            ]);
            
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    /**
     * Helper method untuk mendapatkan nama prodi dari kode
     */
    private function getNamaProdi($kodeProdi)
    {
        if (!$kodeProdi) return null;
        
        $prodi = ProgramStudi::where('kode_prodi', $kodeProdi)->first();
        return $prodi ? $prodi->nama_prodi : null;
    }
    public function bulkDestroy(Request $request)
{
    try {
        // Decode string JSON IDs dari hidden input
        $ids = json_decode($request->user_ids);
        
        if (!$ids || count($ids) == 0) {
            return redirect()->back()->with('error', 'Pilih minimal satu user untuk dihapus.');
        }

        DB::beginTransaction();
        
        // 1. Hapus file fisik di storage agar tidak memenuhi server
        $eregs = MasterEreg::whereIn('user_id', $ids)->get();
        foreach ($eregs as $ereg) {
            $fileColumns = [
                'file_kk', 'file_ijazah', 'file_ktp', 'file_surat_keterangan_sehat', 
                'file_ijazah_s1', 'file_transkrip_s1', 'file_surat_buta_warna'
            ];
            foreach ($fileColumns as $col) {
                if ($ereg->$col) {
                    Storage::disk('public')->delete($ereg->$col);
                }
            }
        }

        // 2. Hapus User (Relasi master_users, master_ereg, dll akan ikut terhapus jika menggunakan Cascade)
        User::whereIn('id', $ids)->delete();

        DB::commit();
        return redirect()->back()->with('success', count($ids) . ' data mahasiswa berhasil dihapus permanen.');

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'Gagal hapus massal: ' . $e->getMessage());
    }
}
    public function destroy($id)
{
    try {
        DB::beginTransaction();

        $user = User::findOrFail($id);

        // Opsional: Hapus file fisik di storage jika ada masterEreg
        if ($user->masterEreg) {
            $fileColumns = ['file_kk', 'file_ijazah', 'file_ktp', 'file_surat_keterangan_sehat', 'file_ijazah_s1', 'file_transkrip_s1', 'file_surat_buta_warna'];
            foreach ($fileColumns as $col) {
                if ($user->masterEreg->$col) {
                    Storage::disk('public')->delete($user->masterEreg->$col);
                }
            }
        }

        // Hapus user (Relasi akan ikut terhapus jika Anda menggunakan ON DELETE CASCADE di database)
        // Jika tidak, hapus manual relasinya sebelum baris ini
        $user->delete();

        DB::commit();
       return redirect()->back()->with('success', 'User dan seluruh data terkait berhasil dihapus permanen.');
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'Gagal menghapus user: ' . $e->getMessage());
    }
}
    /**
     * Batalkan verifikasi farmasi
     */
    public function destroyFarmasiVerification($id)
    {
        $user = User::findOrFail($id);
        
        $allowedRoles = ['admin', 'admin_farmasi'];
        if (!in_array(Auth::user()->role, $allowedRoles)) {
            abort(403, 'Hanya admin dan admin farmasi yang dapat menghapus verifikasi farmasi.');
        }
        
        $deleted = FarmasiVerification::where('user_id', $user->id)->delete();
        
        if ($deleted) {
		    return redirect()->back()->with('success', 'Verifikasi farmasi berhasil dibatalkan.');
		}
        
        return redirect()->back()->with('error', 'Gagal membatalkan verifikasi farmasi.');
    }
public function updateDataPddikti(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $user = User::findOrFail($id);
            
            // 1. Ambil data yang sudah ada di database (jika ada)
            $existingData = DB::table('data_mahasiswa_finals')->where('user_id', $id)->first();

            $updateData = [];
            
            // 2. Loop semua request kecuali token dan method
            foreach ($request->except(['_token', '_method']) as $key => $value) {
                
                // HANYA proses jika input form memiliki isi (tidak kosong dan tidak null)
                if ($value !== null && $value !== '') {
                    
                    // Jika belum ada data lama, ATAU jika ada perubahan nilai dari data lama
                    if (!$existingData || $existingData->$key != $value) {
                        $updateData[$key] = $value;
                    }
                    
                }
            }

            // 3. Eksekusi query HANYA jika ada data baru yang valid untuk diupdate
            if (!empty($updateData)) {
                $updateData['updated_at'] = now();

                // Jika user ini belum punya record sama sekali di tabel pddikti
                if (!$existingData) {
                    $updateData['created_at'] = now();
                    $updateData['status_data'] = 'completed'; 
                }

                DB::table('data_mahasiswa_finals')->updateOrInsert(
                    ['user_id' => $user->id],
                    $updateData
                );
            }

            DB::commit();

            return redirect()->back()->with('success', 'Data PDDIKTI Mahasiswa berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating Data PDDIKTI: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }
    private function isProdiFarmasi($masterEreg)
    {
        if (!$masterEreg) {
            return false;
        }
        
        $jurusan1 = strtolower($masterEreg->jurusan1 ?? '');
        $jurusan2 = strtolower($masterEreg->jurusan2 ?? '');
        
        $isFarmasi = str_contains($jurusan1, 'farmasi') || 
                     str_contains($jurusan2, 'farmasi') ||
                     str_contains($jurusan1, '48201') ||
                     str_contains($jurusan2, '48201');
        
        return $isFarmasi;
    }
    
    /**
     * Verify user profile dan simpan ke master_users_verified
     */
    public function verify($id)
    {
        // Load relasi biodata untuk mendapatkan nomor HP
        $user = User::with(['masterUser', 'biodata', 'masterEreg'])
                    ->where('role', 'mahasiswa_baru')
                    ->findOrFail($id);
        
        if (in_array(Auth::user()->role, ['admin_akademik', 'admin_farmasi'])) {
            if (!$user->masterEreg || $user->masterEreg->status !== 'completed') {
                abort(403, 'Anda tidak memiliki akses ke data ini.');
            }
        }
        
        if ($user->masterUser) {
            $existingVerification = MasterUsersVerified::where('user_id', $user->id)
                ->where('master_user_id', $user->masterUser->id)
                ->first();

            if (!$existingVerification) {
                $verification = MasterUsersVerified::create([
                    'user_id' => $user->id,
                    'master_user_id' => $user->masterUser->id,
                    'verified_at' => now(),
                    'verified_by' => Auth::user()->name ?? 'System',
                    'notes' => 'Profile verified by admin',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // --- BAGIAN NOTIFIKASI WHATSAPP VERIFIKASI BERHASIL ---
                $phone = $user->biodata->no_telepon ?? $user->phone;
                $nama = $user->biodata->nama_lengkap ?? $user->name;

                if ($phone) {
                    $message = "Halo, Kak *{$nama}*.\n\n";
                    $message .= "Selamat! Berkas pendaftaran Anda telah *BERHASIL DIVERIFIKASI* oleh Admin Akademik.\n\n";
                    $message .= "Silakan login ke portal PMB untuk memantau tahapan seleksi selanjutnya. Pastikan Anda memeriksa jadwal ujian atau pengumuman terbaru pada dashboard pendaftaran.\n\n";
                    $message .= "*Informasi Penting Untuk yang mendaftar S1 (Sarjana)!*\n\n";
                    $message .= "Jika ingin mengganti Jurusan/Prodi, Segera hubungi admin. Sebelum *Ujian* dimulai!\n\n";
                    $message .= "Terima kasih dan semoga sukses!";

                    try {
                        $this->whatsAppService->sendMessage($phone, $message);
                        \Log::info("WA Notification sent for successful verification: User ID " . $id);
                    } catch (\Exception $waEx) {
                        \Log::error("Failed to send Verification WA Notification: " . $waEx->getMessage());
                    }
                }
                // -----------------------------------------------------

                \Log::info("Verification created:", [
                    'user_id' => $user->id,
                    'master_user_id' => $user->masterUser->id,
                    'verification_id' => $verification->id
                ]);
            } else {
                \Log::info("Verification already exists for user:", [
                    'user_id' => $user->id,
                    'existing_verification_id' => $existingVerification->id
                ]);
            }
        } else {
            \Log::warning("User has no masterUser, cannot create verification:", [
                'user_id' => $user->id
            ]);
        }

        return redirect()->back()->with('success', 'Profile mahasiswa baru berhasil diverifikasi dan notifikasi WA telah dikirim.');
    }

    public function exportPdf($id)
    {
        $user = User::with([
            'biodata',
            'masterUser',
            'masterEreg',
            'masterUsersVerified',
            'farmasiVerifications'
        ])->where('role', 'mahasiswa_baru')
          ->findOrFail($id);

        // Filter khusus untuk admin_akademik dan admin_farmasi
        if (in_array(Auth::user()->role, ['admin_akademik', 'admin_farmasi'])) {
            if (!$user->masterEreg || $user->masterEreg->status !== 'completed') {
                abort(403, 'Anda tidak memiliki akses ke data ini.');
            }
        }

        // Ambil data moodle dan kelulusan
        $moodleUser = null;
        $kelulusan = null;

        if ($user->masterUser) {
            $moodleUser = MasterMoodle::where('master_user_id', $user->masterUser->id)->first();
            $kelulusan = MasterMoodleLulus::where('master_user_id', $user->masterUser->id)
                ->orWhere('moodle_user_id', $moodleUser->moodle_user_id ?? null)
                ->first();
        }

        $biodata = $user->biodata;
        $ereg = $user->masterEreg;

        // Daftar lengkap semua file di master_ereg
        $allFileColumns = [
            'file_kk' => 'Kartu Keluarga',
            'file_ijazah' => 'Ijazah', 
            'file_ktp' => 'KTP',
            'file_ijazah_s1' => 'Ijazah S1',
            'file_transkrip_s1' => 'Transkrip S1',
            'file_surat_pindah' => 'Surat Pindah',
            'file_ktm' => 'Kartu Tanda Mahasiswa',
            'file_surat_akademik_pindahan' => 'Surat Akademik Pindahan',
            'file_surat_alih_jenjang' => 'Surat Alih Jenjang',
            'file_surat_akademik_ekstensi' => 'Surat Akademik Ekstensi',
            'file_surat_tugas_kerja' => 'Surat Tugas Kerja',
            'file_transkrip_nilai_rpl' => 'Transkrip Nilai RPL',
            'file_surat_akademik_rpl' => 'Surat Akademik RPL',
            'file_surat_buta_warna' => 'Surat Buta Warna'
        ];

        // Filter hanya file yang ada
        $berkasList = [];
        if ($ereg) {
            foreach ($allFileColumns as $field => $label) {
                if ($ereg->$field) {
                    $filePath = $ereg->$field; // PERBAIKAN: Definisikan variabel
                    $fileExists = Storage::disk('public')->exists($filePath);
                    
                    $berkasList[$field] = [
                        'label' => $label,
                        'path' => $filePath,
                        'url' => asset('storage/' . $filePath),
                        'exists' => $fileExists,
                        'base64' => $fileExists ? $this->embedFileToPdf($filePath) : null,
                        'extension' => pathinfo($filePath, PATHINFO_EXTENSION),
                    ];
                }
            }
        }

        // Tentukan status verifikasi
        $verifikasi = $user->masterUsersVerified && $user->masterUsersVerified->count() > 0;

        // Data untuk PDF
        $data = [
            'user' => $user,
            'kelulusan' => $kelulusan,
            'moodleUser' => $moodleUser,
            'biodata' => $biodata,
            'ereg' => $ereg,
            'verifikasi' => $verifikasi,
            'berkasList' => $berkasList,
            'today' => now()->format('d F Y'),
            'totalBerkas' => count($berkasList)
        ];

        // Generate PDF
        $pdf = Pdf::loadView('admin.profile-lengkap.pdf', $data);
        
        // Set nama file PDF (perbaiki syntax)
        $filename = 'profile-lengkap-' . $user->id . '-' . ($user->biodata->nama_lengkap ?? $user->name) . '.pdf';
        $filename = preg_replace('/[^a-zA-Z0-9-.]/', '-', $filename);
        
        return $pdf->download($filename);
    }

    public function previewThumbnail($id, $field)
    {
        try {
            $user = User::with('masterEreg')
                        ->where('role', 'mahasiswa_baru')
                        ->findOrFail($id);
            
            if (!$user->masterEreg || !$user->masterEreg->$field) {
                abort(404, 'File not found');
            }
            
            $filePath = $user->masterEreg->$field;
            $fullPath = Storage::disk('public')->path($filePath);
            
            // Check if file exists
            if (!Storage::disk('public')->exists($filePath)) {
                abort(404, 'File not found on storage');
            }
            
            // Check if file is PDF
            $mimeType = mime_content_type($fullPath);
            if ($mimeType !== 'application/pdf') {
                // For non-PDF files, serve the original file
                return response()->file($fullPath);
            }
            
            // Check if Imagick is available
            if (!extension_loaded('imagick')) {
                // Fallback: return a placeholder image
                return $this->generatePlaceholderImage();
            }
            
            // Generate thumbnail using Imagick
            $imagick = new \Imagick();
            $imagick->setResolution(150, 150);
            $imagick->readImage($fullPath . '[0]'); // First page
            $imagick->setImageFormat('jpeg');
            $imagick->setImageBackgroundColor('white');
            $imagick->setImageAlphaChannel(\Imagick::ALPHACHANNEL_REMOVE);
            $imagick->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
            $imagick->thumbnailImage(300, 200, true, true);
            
            return response($imagick->getImageBlob())
                ->header('Content-Type', 'image/jpeg')
                ->header('Cache-Control', 'public, max-age=3600');
                
        } catch (\Exception $e) {
            \Log::error('Error generating PDF thumbnail:', [
                'user_id' => $id,
                'field' => $field,
                'error' => $e->getMessage()
            ]);
            
            return $this->generatePlaceholderImage();
        }
    }

    /**
     * Generate placeholder image when PDF preview fails
     */
    private function generatePlaceholderImage()
    {
        $image = imagecreatetruecolor(300, 200);
        $bgColor = imagecolorallocate($image, 42, 42, 42);
        $textColor = imagecolorallocate($image, 136, 136, 136);
        
        imagefill($image, 0, 0, $bgColor);
        imagestring($image, 5, 100, 90, 'No Preview', $textColor);
        
        ob_start();
        imagejpeg($image);
        $imageData = ob_get_clean();
        imagedestroy($image);
        
        return response($imageData)
            ->header('Content-Type', 'image/jpeg');
    }

    /**
     * Direct PDF view endpoint
     */
    public function viewPdf($id, $field)
    {
        $user = User::with('masterEreg')
                    ->where('role', 'mahasiswa_baru')
                    ->findOrFail($id);
        
        if (!$user->masterEreg || !$user->masterEreg->$field) {
            abort(404, 'File not found');
        }
        
        $filePath = $user->masterEreg->$field;
        $fullPath = Storage::disk('public')->path($filePath);
        
        if (!Storage::disk('public')->exists($filePath)) {
            abort(404, 'File not found on storage');
        }
        
        // Set proper headers for PDF viewing
        return response()->file($fullPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"'
        ]);
    }

    public function unverify($id)
    {
        $user = User::with('masterUser')
                    ->where('role', 'mahasiswa_baru')
                    ->findOrFail($id);
        
        if (in_array(Auth::user()->role, ['admin_akademik', 'admin_farmasi'])) {
            if (!$user->masterEreg || $user->masterEreg->status !== 'completed') {
                abort(403, 'Anda tidak memiliki akses ke data ini.');
            }
        }
        
        if ($user->masterUser) {
            $deletedCount = MasterUsersVerified::where('user_id', $user->id)
                ->where('master_user_id', $user->masterUser->id)
                ->delete();

            \Log::info("Verification deleted:", [
                'user_id' => $user->id,
                'deleted_count' => $deletedCount
            ]);
        }

        return redirect()->back()->with('success', 'Verifikasi profile mahasiswa baru berhasil dibatalkan.');
    }
    
    /**
     * Delete ereg files and reset status
     */
    public function deleteEregFiles($userId)
    {
        try {
            // Load relasi biodata untuk mendapatkan nomor HP
            $user = User::with(['masterEreg', 'biodata'])->findOrFail($userId);
            
            if (!$user->masterEreg) {
                return redirect()->back()
                    ->with('error', 'Data master_ereg tidak ditemukan untuk user ini.');
            }
            
            $masterEreg = $user->masterEreg;
            
            $fileColumns = [
                'file_kk', 'file_ijazah', 'file_ktp', 'file_ijazah_s1',
                'file_transkrip_s1', 'file_surat_pindah', 'file_ktm',
                'file_surat_akademik_pindahan', 'file_surat_alih_jenjang',
                'file_surat_akademik_ekstensi', 'file_surat_tugas_kerja',
                'file_transkrip_nilai_rpl', 'file_surat_akademik_rpl',
                'file_surat_buta_warna'
            ];
            
            // Proses penghapusan file fisik
            foreach ($fileColumns as $column) {
                if ($masterEreg->$column && Storage::disk('public')->exists($masterEreg->$column)) {
                    Storage::disk('public')->delete($masterEreg->$column);
                }
            }
            
            // Reset kolom di database
            foreach ($fileColumns as $column) {
                $masterEreg->$column = null;
            }
            
            $masterEreg->status = 'in_progress';
            $masterEreg->step_completed = 1;
            $masterEreg->save();

            // --- BAGIAN NOTIFIKASI WHATSAPP ---
            $phone = $user->biodata->no_telepon ?? $user->phone;
            $nama = $user->biodata->nama_lengkap ?? $user->name;

            if ($phone) {
                $message = "Halo, Kak *{$nama}*.\n\n";
                $message .= "Kami menginformasikan bahwa berkas pendaftaran Anda telah *direset/dihapus* oleh Admin Akademik karena adanya ketidaksesuaian data.\n\n";
                $message .= "*Note:* Gunakan aplikasi Camscanner untuk scanning dalam bentuk pdf.\n\n";
                $message .= "Silakan login kembali ke portal PMB dan unggah ulang berkas Anda sesuai dengan ketentuan yang berlaku agar proses verifikasi dapat dilanjutkan.\n\n";
                $message .= "Terima kasih.";

                try {
                    $this->whatsAppService->sendMessage($phone, $message);
                    \Log::info("WA Notification sent for file deletion: User ID " . $userId);
                } catch (\Exception $waEx) {
                    \Log::error("Failed to send WA Notification: " . $waEx->getMessage());
                    // Kita tidak menghentikan proses redirect sukses meskipun WA gagal
                }
            }
            // ----------------------------------
            
            return redirect()->back()
                ->with('success', 'Semua berkas master_ereg berhasil dihapus, status direset, dan notifikasi WA telah dikirim.');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus berkas: ' . $e->getMessage());
        }
    }

    private function embedFileToPdf($filePath)
    {
        if (!$filePath || !Storage::disk('public')->exists($filePath)) {
            return null;
        }
        
        $fullPath = Storage::disk('public')->path($filePath);
        $mimeType = mime_content_type($fullPath);
        
        // Hanya proses tipe file gambar dan PDF
        if (str_starts_with($mimeType, 'image/') || $mimeType == 'application/pdf') {
            $fileContent = Storage::disk('public')->get($filePath);
            $base64 = base64_encode($fileContent);
            return "data:{$mimeType};base64,{$base64}";
        }
        
        return null;
    }
    public function kirimKePengelolaan($id)
    {
        try {
            $user = User::with(['biodata', 'masterEreg'])->findOrFail($id);

            if (!$user->masterEreg || !$user->masterEreg->jalur_pendaftaran) {
                return redirect()->back()->with('error', 'Gagal: Mahasiswa belum memiliki data E-Reg atau jalur pendaftaran.');
            }

            // Cek apakah data sudah ada di tabel pengelolaan_jalur
            $existing = DB::table('pengelolaan_jalur')->where('user_id', $user->id)->first();

            if ($existing && $existing->status_verifikasi == 1) {
                // JIKA SUDAH VERIFIKASI = BATALKAN (Ubah status jadi 0)
                DB::table('pengelolaan_jalur')
                    ->where('user_id', $user->id)
                    ->update([
                        'status_verifikasi' => 0, 
                        'updated_at' => now()
                    ]);
                
                return redirect()->back()->with('success', 'Verifikasi jalur khusus berhasil DIBATALKAN.');
            } else {
                // JIKA BELUM ADA / STATUS 0 = KIRIM & VERIFIKASI (Otomatis jadi 1)
                DB::table('pengelolaan_jalur')->updateOrInsert(
                    ['user_id' => $user->id],
                    [
                        'nama_lengkap' => $user->biodata->nama_lengkap ?? $user->name,
                        'jalur_pendaftaran' => $user->masterEreg->jalur_pendaftaran,
                        'status_verifikasi' => 1, // OTOMATIS 1
                        'updated_at' => now(),
                        // Jangan ubah created_at jika data sudah pernah ada sebelumnya
                        'created_at' => $existing ? $existing->created_at : now(), 
                    ]
                );

                return redirect()->back()->with('success', 'Data berhasil dikirim dan DIVERIFIKASI pada Pengelolaan Jalur.');
            }

        } catch (\Exception $e) {
            \Log::error('Error kirim pengelolaan jalur: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
    public function updateAkun(Request $request, $id)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $id,
    ]);

    try {
        DB::beginTransaction();

        $user = User::findOrFail($id);
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        DB::commit();
        return redirect()->back()->with('success', 'Nama dan Email Akun Utama berhasil diperbarui.');

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'Gagal memperbarui akun: ' . $e->getMessage());
    }
}
public function exportSlikBilling(Request $request)
{
    try {
        $namaFile = 'Laporan_Pembayaran_SLIK_' . now()->format('Ymd_His') . '.xlsx';
        
        // Panggil class export dan langsung download sebagai .xlsx
        return Excel::download(new SlikBillingExport($request), $namaFile);

    } catch (\Exception $e) {
        \Log::error('Gagal Ekspor XLSX Slik: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Gagal mengekspor data Excel: ' . $e->getMessage());
    }
}
}