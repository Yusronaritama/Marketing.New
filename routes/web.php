<?php

use Illuminate\Support\Facades\Route;


// ============================================================
// AUTH
// ============================================================
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\RegisteredMandiriController;
use App\Http\Controllers\Auth\ForgotAccountController;

// ============================================================
// CORE / SHARED CONTROLLERS
// ============================================================
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\KtpOcrController;
use App\Http\Controllers\BocController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\MasterMoodleLulusController;
use App\Http\Controllers\MoodleEnrollmentController;
use App\Http\Controllers\WilayahController;
use App\Http\Controllers\AdminController;

Route::get('/admin/cek-quiz', [AdminController::class, 'index']);

// ============================================================
// API
// ============================================================
use App\Http\Controllers\Api\BniCallbackController;

// ============================================================
// ADMIN CONTROLLERS
// ============================================================
use App\Http\Controllers\Admin\DashboardController           as AdminDashboardController;
use App\Http\Controllers\Admin\ProfileController             as AdminProfileController;
use App\Http\Controllers\Admin\BiodataController             as AdminBiodataController;
use App\Http\Controllers\Admin\ChatController                as AdminChatController;
use App\Http\Controllers\Admin\MoodleController              as AdminMoodleController;
use App\Http\Controllers\Admin\MoodleController;
use App\Http\Controllers\Admin\CekController;
use App\Http\Controllers\Admin\LocalMoodleController;
use App\Http\Controllers\Admin\AdminUbahProdiController;
use App\Http\Controllers\Admin\PengelolaanController;
use App\Http\Controllers\Admin\MoodleCompleteController;
use App\Http\Controllers\Admin\AdminProdiController;
use App\Http\Controllers\Admin\MoodleCompleteSyncController;
use App\Http\Controllers\Admin\GenerateNimController;
use App\Http\Controllers\Admin\MoodleQuizAttemptController;
use App\Http\Controllers\Admin\MoodleQuizSyncController;
use App\Http\Controllers\Admin\MoodleUserCourseController;
use App\Http\Controllers\Admin\MoodleGradeController;
use App\Http\Controllers\Admin\MoodleNilaiController;
use App\Http\Controllers\Admin\MoodleRetryController;
use App\Http\Controllers\Admin\AkunController;
use App\Http\Controllers\Admin\BerkasController;
use App\Http\Controllers\Admin\QrCodeController;
use App\Http\Controllers\Admin\BniCallBackkController;
use App\Http\Controllers\Admin\BNIController;
use App\Http\Controllers\Admin\BatchController;
use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\Admin\DownloadController;
use App\Http\Controllers\Admin\DatabaseImportController;
use App\Http\Controllers\Admin\DownloadDataController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\FarmasiController;
use App\Http\Controllers\Admin\FormElementsController;
use App\Http\Controllers\Admin\GelombangController;
use App\Http\Controllers\Admin\MasterGelombangController;
use App\Http\Controllers\Admin\AdminProfesiController;
use App\Http\Controllers\Admin\GenerateVAController;
use App\Http\Controllers\Admin\GisController;
use App\Http\Controllers\Admin\GroupGelombangController;
use App\Http\Controllers\Admin\JadwalUjianController;
use App\Http\Controllers\Admin\MandiriRegisterController;
use App\Http\Controllers\Admin\MasterEregController;
use App\Http\Controllers\Admin\MasterLulusController;
use App\Http\Controllers\Admin\PerbaikanController;
use App\Http\Controllers\Admin\PengaturanGelombangController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\ProdiController;
use App\Http\Controllers\Admin\QrAttendanceController;
use App\Http\Controllers\Admin\ProdiPageController;
use App\Http\Controllers\Admin\ProfileLengkapController;
use App\Http\Controllers\Admin\PromoCodeController;
use App\Http\Controllers\Admin\PromoController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\ServerStatusController;
use App\Http\Controllers\Admin\ShowAllController;
use App\Http\Controllers\Admin\SlikBillingController;
use App\Http\Controllers\Admin\TestController;
use App\Http\Controllers\Admin\UjianCountdownController;
use App\Http\Controllers\Admin\UserBiodataController;
use App\Http\Controllers\Admin\UserProfileController;
use App\Http\Controllers\Admin\WhatsAppController;
use App\Http\Controllers\Admin\WhatsAppChatController;
use App\Http\Controllers\Admin\DataS2Controller;
use App\Http\Controllers\Api\PresensiController;

// ============================================================
// MAHASISWA CONTROLLERS
// ============================================================
use Illuminate\Support\Facades\Schedule;
use App\Http\Controllers\Mahasiswa\DashboardController      as MahasiswaDashboardController;
use App\Http\Controllers\Mahasiswa\BiodataController        as MahasiswaBiodataController;
use App\Http\Controllers\Mahasiswa\ChatController;
use App\Http\Controllers\Mahasiswa\DataFinalController;
use App\Http\Controllers\Mahasiswa\MahasiswaDataController;
use App\Http\Controllers\Mahasiswa\OCRScannerController;
use App\Http\Controllers\Mahasiswa\PembayaranController;
use App\Http\Controllers\Mahasiswa\PostRegisterController;
use App\Http\Controllers\Mahasiswa\ProgressController;
use App\Http\Controllers\Mahasiswa\RegisterStepController;
use App\Http\Controllers\Mahasiswa\RingkasanController;
use App\Http\Controllers\Mahasiswa\VirtualAccountController;

// ============================================================
// PROFESI CONTROLLERS
// ============================================================
use App\Http\Controllers\Mahasiswa\DashboardProfesiController;
use App\Http\Controllers\Mahasiswa\Profesi\BiodataProfesiController;
use App\Http\Controllers\Mahasiswa\profesi\DataFinalProfesiController;
// ============================================================
// MOODLE CONTROLLERS
// ============================================================
use App\Http\Controllers\Moodle\MoodleQuizController;
use App\Http\Controllers\Moodle\MoodleSyncController;

// ============================================================
// SERVICES / LIBRARIES
// ============================================================
use App\Services\BNIApiService;
use App\Libraries\Bnienc;


// ============================================================
// :)
// ============================================================
use App\Services\WhatsAppRekapService;

Route::get('/penmabar/whatsapprekap', function (WhatsAppRekapService $waService) {
    try {
        // Eksekusi logic kirim WA
        $waService->kirimLaporanHarian();
        
        // Tampilkan pesan di browser kalau sukses
        return response('<h2 style="color: green; font-family: sans-serif;">✅ BOOM! Laporan PMB berhasil dikirim ke WA lu dawg!</h2>', 200);
        
    } catch (\Exception $e) {
        // Tampilkan pesan error kalau gagal
        return response('<h2 style="color: red; font-family: sans-serif;">❌ Gagal ngirim WA: ' . $e->getMessage() . '</h2>', 500);
    }
});
/*
|--------------------------------------------------------------------------
| API – Wilayah (publik, tanpa auth)
|--------------------------------------------------------------------------
*/
Route::get('/api/wilayah/provinces',            [WilayahController::class, 'getProvinces']);
Route::get('/api/wilayah/cities/{province_id}', [WilayahController::class, 'getCities']);
Route::get('/api/wilayah/districts/{city_id}',  [WilayahController::class, 'getDistricts']);
Route::delete('/admin/cek/destroy/{id}', [CekController::class, 'destroy'])->name('admin.cek.destroy');
Route::match(['get', 'post'], '/admin/generate-nim/siptbulkall/{jenjangOrLimit?}/{limit?}', [GenerateNimController::class, 'sendBulkToSipt'])
    ->name('admin.generate-nim.send-bulk-sipt');
// Route::get('/admin/generate-nim/siptbulkall/{jenjangOrLimit?}/{limit?}', [GenerateNimController::class, 'sendBulkToSipt'])->name('admin.generate-nim.send-bulk-sipt');
Route::prefix('/admin/generate-nim')->name('admin.generate-nim.')->group(function () {
    // List E-Learning
    Route::get('/elearning-list', [GenerateNimController::class, 'listElearning'])->name('elearning-list');
    
    // Kirim E-Learning Satuan (Mendukung URL /send-elearning/{id} dan /elearning/{id})
    Route::match(['get', 'post'], '/send-elearning/{nimId}', [GenerateNimController::class, 'sendToElearning'])->name('send-elearning');
    Route::match(['get', 'post'], '/elearning/{nimId}', [GenerateNimController::class, 'sendToElearning']);
    
    // Kirim E-Learning Massal
    Route::match(['get', 'post'], '/send-bulk-elearning/{jenjangOrLimit?}/{limit?}', [GenerateNimController::class, 'sendBulkToElearning'])->name('send-bulk-elearning');
    Route::match(['get', 'post'], '/elearningbulkall/{jenjangOrLimit?}/{limit?}', [GenerateNimController::class, 'sendBulkToElearning']);
});
Route::get('/admin/generate-nim/bulkall',  [GenerateNimController::class, 'generateAll'])->name('admin.generate-nim.bulkall');
Route::get('/admin/generate-nim/elearning/{id}', [GenerateNimController::class, 'sendToElearning'])->name('admin.generate-nim.send-elearning');
// Route::get('/admin/generate-nim/elearningbulkall/{jenjangOrLimit?}/{limit?}', [GenerateNimController::class, 'sendBulkToElearning'])->name('admin.generate-nim.send-bulk-elearning');
Route::get('/admin/generate-qrcode/all', [QrCodeController::class, 'generate_qrCode'])->name('admin.generate-qrcode');
Route::get('/admin/promocode/export', [App\Http\Controllers\Admin\PromoCodeController::class, 'exportExcel'])
    ->name('admin.promocode.export');
Route::get('/admin/send-elearning/{nimId}', [GenerateNimController::class, 'sendToElearning'])->name('admin.send-elearning');
Route::get('/admin/send-bulk-elearning/{jenjangOrLimit?}/{limit?}', [GenerateNimController::class, 'sendBulkToElearning'])->name('admin.send-bulk-elearning');
Route::match(['get', 'post'], '/admin/send-bulk-slik/{jenjangOrLimit?}/{limit?}', [GenerateNimController::class, 'sendBulkToSlik'])->name('admin.send-bulk-slik');
Route::match(['get', 'post'], '/send-bulk-slik/{jenjangOrLimit?}/{limit?}', [GenerateNimController::class, 'sendBulkToSlik'])->name('send-bulk-slik');
/*
|--------------------------------------------------------------------------
| Misc – Standalone routes
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Admin\PromoReportController;

Route::prefix('admin')->group(function () {
    Route::get('/promo-report', [PromoReportController::class, 'index'])->name('admin.promo.report');
    Route::get('/show-all/export/{namaProdi}', [ShowAllController::class, 'exportExcelProdi'])
    ->name('admin.show-all.export-prodi');
});
Route::get('admin/promo-report/export/{code}', [PromoReportController::class, 'exportByCode'])
    ->name('admin.promo-report.export');
Route::patch('/admin/profesi/cbt/batal/{id}', [AdminProfesiController::class, 'cbtBatal'])->name('admin.profesi.cbt.batal');
Route::get('/admin/promo-usage', [\App\Http\Controllers\Admin\PromoController::class, 'promoUsage'])->name('admin.promo.usage');
Route::get('/qr/show/{userId}', [QrAttendanceController::class, 'showQr'])
     ->name('qr.show');
 
// Endpoint yang di-encode di dalam QR Code — saat scan otomatis buka ini
Route::get('/qr/scan/{token}', [QrAttendanceController::class, 'scan'])
     ->name('qr.scan');
 Route::put('/admin/profile-lengkap/{id}/update-pddikti', [\App\Http\Controllers\Admin\ProfileLengkapController::class, 'updateDataPddikti'])
    ->name('admin.profile-lengkap.update-pddikti');
 
// ----------------------------------------------------------------
// ADMIN — harus login & role admin
// ----------------------------------------------------------------
 
Route::middleware(['auth', 'role:admin,super_admin'])->prefix('admin')->name('admin.')->group(function () {
	Route::get('/cek-prodi-unknown', [CekController::class, 'index'])->name('cek.unknown');
    Route::get('/cek-filter-spesifik', [CekController::class, 'filterSpesifik'])->name('cek.filter');
 Route::get('/qr/scanner', [QrAttendanceController::class, 'scannerPage'])->name('qr.scanner');
    // Halaman utama manajemen QR
    Route::get('/qr-attendance', [QrAttendanceController::class, 'index'])
         ->name('qr-attendance.index');
 
    // Generate QR untuk satu mahasiswa
    Route::post('/qr-attendance/generate', [QrAttendanceController::class, 'generate'])
         ->name('qr-attendance.generate');
 
    // Generate QR massal untuk semua mahasiswa yang sudah isi finals
    Route::post('/qr-attendance/generate-bulk', [QrAttendanceController::class, 'generateBulk'])
         ->name('qr-attendance.generate-bulk');
 
    // Daftar hadir (yang sudah scan)
    Route::get('/qr-attendance/list', [QrAttendanceController::class, 'attendanceList'])
         ->name('qr-attendance.list');
 
    // Expire manual satu QR
    Route::post('/qr-attendance/{id}/expire', [QrAttendanceController::class, 'expireQr'])
         ->name('qr-attendance.expire');
});
Route::get('/debug-apoteker', [ShowAllController::class, 'debugApoteker']);
/*
|--------------------------------------------------------------------------
| Misc – Standalone routes
|--------------------------------------------------------------------------
*/
// ----------------------------------------------------------------
// PRESENSI PKKMB (PUBLIC ENDPOINTS)
// URL: /presensi/presensi, /presensi/daftar_presensi_by_email, /presensi/daftar_presensi_by_email_pulang
// ----------------------------------------------------------------
Route::prefix('presensi')->group(function () {
    Route::match(['POST', 'OPTIONS'], '/presensi', [\App\Http\Controllers\PresensiController::class, 'presensi']);
    Route::match(['POST', 'OPTIONS'], '/daftar_presensi_by_email', [\App\Http\Controllers\PresensiController::class, 'daftar_presensi_by_email']);
    Route::match(['POST', 'OPTIONS'], '/daftar_presensi_by_email_pulang', [\App\Http\Controllers\PresensiController::class, 'daftar_presensi_by_email_pulang']);
});

Route::get('/admin/download/s2-excel', [DownloadController::class, 'downloadExcelS2'])->name('admin.download.s2-excel');

Route::get('/moodle/enrollment',  [MoodleEnrollmentController::class, 'index'])->name('moodle.enroll.index');
Route::post('/moodle/enrollment', [MoodleEnrollmentController::class, 'store'])->name('moodle.enroll.store');

Route::post('/admin/moodle/bulk-register', [MoodleSyncController::class, 'bulkRegisterUsers'])->name('admin.moodle.bulk-register');

Route::get('/debug-user', function () {
    $user = \App\Models\User::find(1247);
    if (!$user) return 'User tidak ditemukan';

    return [
        'id'              => $user->id,
        'name'            => $user->name,
        'role'            => $user->role,
        'has_master_ereg' => $user->masterEreg ? 'Ya' : 'Tidak',
        'ereg_status'     => $user->masterEreg ? $user->masterEreg->status : '-',
    ];
});

/*
|--------------------------------------------------------------------------
| Moodle – Quiz Complete (prefix tanpa auth)
|--------------------------------------------------------------------------
*/
Route::prefix('admin/moodle')->group(function () {
    Route::prefix('quiz-complete')->name('quiz-complete.')->group(function () {
        Route::get('/',          [MoodleCompleteController::class, 'index'])->name('index');
        Route::post('/sync-all', [MoodleQuizSyncController::class, 'syncAllQuizAttempts'])->name('sync-all');
    });
});

/*
|--------------------------------------------------------------------------
| Auth – guard & status (memerlukan auth)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::put('/admin/profile-lengkap/{id}/update-akun', [ProfileLengkapController::class, 'updateAkun'])->name('admin.profile-lengkap.update-akun');
    Route::get('/data-s2/nim/upload',   [DataS2Controller::class, 'uploadNimForm'])->name('admin.data-s2.upload-nim');
    Route::post('/data-s2/nim/preview', [DataS2Controller::class, 'previewNim'])->name('admin.data-s2.nim-preview');
    Route::post('/data-s2/nim/import',  [DataS2Controller::class, 'importNim'])->name('admin.data-s2.nim-import');
    Route::get('/data-s2/nim/template', [DataS2Controller::class, 'downloadTemplate'])->name('admin.data-s2.nim-template');
    Route::get('/admin/server-status',              [ServerStatusController::class, 'index'])->name('admin.server.status');
    Route::post('/admin/user-profiles/{id}/update', [UserProfileController::class, 'updateProfile'])->name('admin.userprofile.update');
    Route::get('/cek-moodle',                       [MoodleCompleteController::class, 'index']);

    Route::get('/admin/perbaikan/status', [PerbaikanController::class,       'getStatus'])->name('admin.perbaikan.status');
    Route::get('/admin/gelombang/status', [PengaturanGelombangController::class, 'getStatus'])->name('admin.gelombang.status');
});

/*
|--------------------------------------------------------------------------
| Profile Lengkap – helper routes
|--------------------------------------------------------------------------
*/
Route::get('/admin/profile-lengkap/reset-page',    [ProfileLengkapController::class, 'resetPage'])->name('admin.profile-lengkap.reset-page');
Route::get('/admin/profile-lengkap/clear-filters', [ProfileLengkapController::class, 'clearFilters'])->name('admin.profile-lengkap.clear-filters');

/*
|--------------------------------------------------------------------------
| Root – redirect berdasarkan role
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();

        if ($user->role === 'admin' || $user->role === 'superadmin') {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('mahasiswa.dashboard');
    }

    return redirect()->route('login');
})->name('home');

/*
|--------------------------------------------------------------------------
| 1. Public Auth Routes (guest only)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    Route::get('/register',  [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    Route::get('/registration/success', [RegisteredUserController::class, 'showSuccessPage'])
        ->name('registration.success')
        ->middleware('auth');

    Route::get('/forgot-account',  [ForgotAccountController::class, 'showForm'])->name('forgot.account');
    Route::post('/forgot-account', [ForgotAccountController::class, 'submitForm'])->name('forgot.account.submit');

    Route::get('/find-account',  [ForgotAccountController::class, 'showFindAccountForm'])->name('find.account');
    Route::post('/find-account', [ForgotAccountController::class, 'submitFindAccount'])->name('find.account.submit');

    Route::get('/account-reset/{token}', [ForgotAccountController::class, 'showResetForm'])->name('password.reset.master');
    Route::post('/account-reset',        [ForgotAccountController::class, 'resetPassword'])->name('password.update.master');
});

/*
|--------------------------------------------------------------------------
| 2. Testing & Debug Routes
|--------------------------------------------------------------------------
*/
Route::get('/admin/bni-logs',                 [BniCallbackController::class, 'showLogs'])->name('bni.logs');
Route::get('/admin/bni-simulate/{va_number}', [BniCallbackController::class, 'simulate'])->name('bni.simulate');
Route::get('/admin/bni-sync-all',             [BniCallbackController::class, 'syncAll'])->name('bni.sync_all');
Route::get('/admin/pembayaran',               [PaymentController::class, 'index'])->name('admin.payments');
Route::get('/pembayaran/status',              [PaymentController::class, 'checkStatus'])->name('payment.status');

Route::get('/admin/moodle/debug-data', [MoodleCompleteSyncController::class, 'debugMoodleData']);

Route::get('/test-ssl', function () {
    $url      = 'https://api.bni-ecollection.com/';
    $ch       = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $error    = curl_error($ch);
    $info     = curl_getinfo($ch);
    curl_close($ch);

    return response()->json([
        'success'          => $response !== false,
        'error'            => $error,
        'info'             => $info,
        'response_preview' => substr($response, 0, 200),
    ]);
});

Route::prefix('test-bni')->group(function () {
    if (app()->environment('production')) {
        Route::get('/{any}', function () {
            return response()->json(['error' => 'Not found', 'message' => 'Test endpoints are disabled in production'], 404);
        })->where('any', '.*');
        return;
    }

    Route::get('/config',     fn (BNIApiService $service) => response()->json($service->testBniConfig()));
    Route::get('/payload',    fn (BNIApiService $service) => response()->json($service->testPayloadWithLogging()));
    Route::get('/encryption', fn (BNIApiService $service) => response()->json($service->getEncryptionInfo()));
    Route::get('/quick', function (BNIApiService $service) {
        $service->quickTest();
        return response()->json(['message' => 'Quick test executed', 'check' => 'See laravel.log for details']);
    });
});

Route::get('/test-bni-debug', function () {
    try {
        $service        = new BNIApiService();
        $connectionTest = $service->validateCredentials();

        return response()->json([
            'status'     => 'success',
            'connection' => $connectionTest ? 'SUCCESS' : 'FAILED - ' . $service->getLastError(),
        ]);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
    }
});

Route::get('/env-debug', function () {
    return response()->json([
        'bni_vars' => [
            'BNI_CLIENT_ID_env' => env('BNI_CLIENT_ID', 'NOT_FOUND'),
            'BNI_API_URL_env'   => env('BNI_API_URL', 'NOT_FOUND'),
        ],
        'app_env' => env('APP_ENV'),
    ]);
});

Route::post('/check-duplicate-registration',      [RegisteredUserController::class,   'checkDuplicate'])->name('check.duplicate.registration');
Route::post('/check-duplicate-registration-test', [RegisteredMandiriController::class, 'checkDuplicate'])->name('check.duplicate.registration-test');

Route::get('/debug-user/{id}', function ($id) {
    $user   = \App\Models\User::find($id);
    $master = \App\Models\MasterUser::where('user_id', $id)->first();

    return response()->json([
        'user' => [
            'id'        => $user->id,
            'email'     => $user->email,
            'role'      => $user->role,
            'va_status' => $user->va_status,
        ],
        'master' => $master ? [
            'id'            => $master->id,
            'va_status'     => $master->va_status,
            'va_number'     => $master->va_number,
            'va_amount'     => $master->va_amount,
            'va_expired_at' => $master->va_expired_at,
            'payment_date'  => $master->payment_date,
        ] : null,
    ]);
})->middleware('auth');

/*
|--------------------------------------------------------------------------
| 3. Public Tools
|--------------------------------------------------------------------------
*/
Route::prefix('public_/report')->name('show-all.')->group(function () {
    Route::get('/', [ShowAllController::class, 'index'])->name('index');
});

Route::prefix('test')->group(function () {
    Route::get('/whatsapp-registration',          [TestController::class, 'testRegistrationWhatsApp']);
    Route::get('/whatsapp-registration/{userId}', [TestController::class, 'testRegistrationWhatsApp']);
    Route::get('/username-password',              [TestController::class, 'testUsernamePasswordOnly']);
    Route::get('/direct-endpoint',                [TestController::class, 'testDirectEndpoint']);
});

Route::get('/ktp/upload',   [KtpOcrController::class, 'showUploadForm'])->name('ktp.upload');
Route::post('/ktp/process', [KtpOcrController::class, 'processKtp'])->name('ktp.process');

Route::get('/site-info', [BocController::class, 'getSiteInfo']);

// BNI Callback
Route::post('/bni/callback', [BniCallBackkController::class, 'handleCallback'])->name('bni.callback.post');
Route::get('/bni/callback',  [BniCallBackkController::class, 'index'])->name('bni.callback');

Route::domain('103.23.198.160')->group(function () {
    Route::post('/bni-callback-direct', [BniCallBackkController::class, 'handleCallback'])->name('bni.callback.direct');
});

/*
|--------------------------------------------------------------------------
| 4. Authenticated User Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard',  fn () => view('dashboard'))->name('dashboard');

    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('admin')->group(function () {
        Route::get('/moodle/nilai',       [MoodleNilaiController::class, 'index'])->name('moodle.index');
        Route::post('/moodle/sync-nilai', [MoodleNilaiController::class, 'syncGrades'])->name('moodle.sync');
    });

    Route::get('/admin/mandiri/ukt/export', [SlikBillingController::class, 'export'])->name('admin.mandiri.ukt.export');
});

/*
|--------------------------------------------------------------------------
| 5. Admin Routes
|--------------------------------------------------------------------------
*/

// ROUTE KHUSUS ADMIN PROFESI
Route::middleware(['auth', 'role:admin_profesi'])->prefix('admin/profesi')->name('admin.profesi.')->group(function () {
    Route::get('/export-excel', [AdminProfesiController::class, 'exportExcel'])->name('export');
    Route::get('/dashboard', [AdminProfesiController::class, 'index'])->name('dashboard');
    Route::get('/cbt', [AdminProfesiController::class, 'cbtIndex'])->name('cbt.index');
    Route::get('/download-semua', [AdminProfesiController::class, 'downloadSemua'])->name('download.semua');
    Route::delete('/users/{id}', [AdminProfesiController::class, 'destroy'])->name('users.destroy');
    Route::post('/cbt/{id}/update', [AdminProfesiController::class, 'cbtUpdate'])->name('cbt.update');
    Route::get('/users/{id}/berkas', [AdminProfesiController::class, 'showBerkas'])->name('berkas.show');
    Route::patch('/users/{id}/verifikasi', [AdminProfesiController::class, 'verifikasiBerkas'])->name('berkas.verifikasi');
    Route::patch('/users/{id}/batal-verifikasi', [AdminProfesiController::class, 'batalVerifikasi'])->name('berkas.batal');
    Route::get('/setting-gelombang', [AdminProfesiController::class, 'settingGelombang'])->name('setting.gelombang');
	Route::post('/update-gelombang', [AdminProfesiController::class, 'updateJadwalGelombang'])->name('update.gelombang');
	Route::post('/wawancara/{id}/update', [AdminProfesiController::class, 'wawancaraUpdate'])->name('wawancara.update');
	Route::get('/export-kelulusan', [AdminProfesiController::class, 'exportKelulusan'])
    ->name('export.kelulusan');
});
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
	Route::get('/generate-qrcode', [QrCodeController::class, 'generate_qrCode'])->name('generate-qrcode');
	Route::get('/qrcode-presensi', [QrCodeController::class, 'index'])->name('admin.qrcode.index');
	Route::prefix('ubah-prodi')->name('ubah-prodi.')->group(function () {
        Route::get('/export', [AdminProdiController::class, 'export'])->name('export');
        Route::get('/', [AdminProdiController::class, 'index'])->name('index');
        Route::post('/{userId}/proses', [AdminProdiController::class, 'updateProdi'])->name('proses');
        Route::delete('/batal-va/{id}', [AdminProdiController::class, 'batalVaSelisih'])->name('batal-va');
    });
Route::prefix('/database')->name('database.')->group(function () {
	Route::post('/sync-legacy', [\App\Http\Controllers\Admin\DatabaseImportController::class, 'syncDirectFromLegacy'])->name('sync-legacy');
   Route::get('/preview', [\App\Http\Controllers\Admin\DatabaseImportController::class, 'index'])->name('import');
   Route::post('/preview', [\App\Http\Controllers\Admin\DatabaseImportController::class, 'previewSql'])->name('process');
	Route::post('/store', [\App\Http\Controllers\Admin\DatabaseImportController::class, 'storeToDatabase'])->name('store');
});

Route::prefix('kelola-gelombang')->name('mastergelombang.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\MasterGelombangController::class, 'index'])->name('index');
    Route::post('/', [\App\Http\Controllers\Admin\MasterGelombangController::class, 'store'])->name('store');
    Route::put('/{id}', [\App\Http\Controllers\Admin\MasterGelombangController::class, 'update'])->name('update');
    Route::delete('/{id}', [\App\Http\Controllers\Admin\MasterGelombangController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/set-active', [\App\Http\Controllers\Admin\MasterGelombangController::class, 'setActive'])->name('set-active');
    Route::post('/{id}/set-inactive', [\App\Http\Controllers\Admin\MasterGelombangController::class, 'setInactive'])->name('set-inactive');
});
Route::get('/pengelolaan-jalur/export-berkas', [\App\Http\Controllers\Admin\PengelolaanController::class, 'exportBerkas'])
    ->name('pengelolaan.export-berkas');
Route::get('/pengelolaan-jalur/export-data', [\App\Http\Controllers\Admin\PengelolaanController::class, 'exportData'])
    ->name('pengelolaan.export-data');
Route::get('/pengelolaan-jalur', [\App\Http\Controllers\Admin\PengelolaanController::class, 'index'])
    ->name('pengelolaan.index');
Route::post('/pengelolaan-jalur/{id}/status', [\App\Http\Controllers\Admin\PengelolaanController::class, 'updateStatus'])
    ->name('pengelolaan.update-status');
Route::post('/pengelolaan-jalur/bulk-verify', [\App\Http\Controllers\Admin\PengelolaanController::class, 'bulkVerify'])
    ->name('pengelolaan.bulk-verify');
Route::post('/pengelolaan-jalur/bulk-info', [\App\Http\Controllers\Admin\PengelolaanController::class, 'bulkUpdateInformasi'])
    ->name('pengelolaan.bulk-info');
Route::post('/pengelolaan-jalur/{id}/informasi', [\App\Http\Controllers\Admin\PengelolaanController::class, 'updateInformasi'])
    ->name('pengelolaan.update-informasi');
    
    // ── NIM S2 ───────────────────────────────────────────
   Route::prefix('generate-nim')->name('generate-nim.')->group(function () {
        Route::get('/', [GenerateNimController::class, 'index'])->name('index');
        Route::post('/generate/{id}', [GenerateNimController::class, 'generate'])->name('generate');
        Route::post('/bulk-generate', [GenerateNimController::class, 'generateBulk'])->name('bulk-generate');
        Route::get('/list', [GenerateNimController::class, 'list'])->name('list');
        Route::post('/send-sipt/{id}', [GenerateNimController::class, 'sendToSipt'])->name('send-sipt');
        Route::post('/send-sipt-bulk', [GenerateNimController::class, 'sendBulkToSipt'])->name('send-sipt-bulk');
        Route::post('/send-slik/{id}', [GenerateNimController::class, 'sendToSlikManual'])->name('send-slik');
        Route::post('/send-slik-bulk', [GenerateNimController::class, 'sendBulkToSlik'])->name('send-slik-bulk');
        
    });
    // ── NIM S2 ───────────────────────────────────────────
    Route::prefix('data-s2')->name('data-s2.')->group(function () {
        Route::get('/', [DataS2Controller::class, 'index'])->name('index');
        Route::get('create', [DataS2Controller::class, 'create'])->name('create');
        Route::post('/', [DataS2Controller::class, 'store'])->name('store');
        Route::get('{id}', [DataS2Controller::class, 'show'])->name('show');
        Route::get('{id}/edit', [DataS2Controller::class, 'edit'])->name('edit');
        Route::put('{id}', [DataS2Controller::class, 'update'])->name('update');
        Route::delete('{id}', [DataS2Controller::class, 'destroy'])->name('destroy');
        Route::post('{id}/status', [DataS2Controller::class, 'updateStatus'])->name('update-status');
        Route::get('export/csv', [DataS2Controller::class, 'export'])->name('export');
    });
    // ── NIM S2 ───────────────────────────────────────────
    Route::prefix('data-s2')->name('data-s2.')->group(function () {
        Route::get('/', [DataS2Controller::class, 'index'])->name('index');
        Route::get('create', [DataS2Controller::class, 'create'])->name('create');
        Route::post('/', [DataS2Controller::class, 'store'])->name('store');
        Route::get('{id}', [DataS2Controller::class, 'show'])->name('show');
        Route::get('{id}/edit', [DataS2Controller::class, 'edit'])->name('edit');
        Route::put('{id}', [DataS2Controller::class, 'update'])->name('update');
        Route::delete('{id}', [DataS2Controller::class, 'destroy'])->name('destroy');
        Route::post('{id}/status', [DataS2Controller::class, 'updateStatus'])->name('update-status');
        Route::get('export/csv', [DataS2Controller::class, 'export'])->name('export');
    });
    // ── Dashboard ───────────────────────────────────────────
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/',               [AdminDashboardController::class, 'index'])->name('index');
        Route::get('/filter-history', [AdminDashboardController::class, 'filterHistory'])->name('filterHistory');
        Route::get('/target',         [AdminDashboardController::class, 'target'])->name('target');
        Route::post('/update-target', [AdminDashboardController::class, 'targetUpdate'])->name('target.update');
        Route::get('/chart-data-gelombang', [AdminDashboardController::class, 'getChartDataByGelombang'])->name('chart.gelombang');
    });

    Route::get('chart-data-gelombang', [AdminDashboardController::class, 'getChartDataByGelombang']);

    // ── Profile ─────────────────────────────────────────────
    Route::get('/profile',          [AdminProfileController::class, 'index'])->name('profile');
    Route::patch('/profile/update', [AdminProfileController::class, 'update'])->name('profile.update');

    // ── Maintenance / Sistem ────────────────────────────────
    Route::post('/perbaikan/toggle', [PerbaikanController::class,          'toggle'])->name('perbaikan.toggle');
    Route::post('/gelombang/toggle', [PengaturanGelombangController::class, 'toggle'])->name('gelombang.toggle');

    // ── ProdiPage ───────────────────────────────────────────
    Route::get('/prodipage',               [ProdiPageController::class, 'index'])->name('prodipage.index');
    Route::post('/prodipage/{id}/validate',[ProdiPageController::class, 'validateData'])->name('prodipage.validate');

    // ── Mandiri ─────────────────────────────────────────────
    Route::get('/mandiri/register/export', [MandiriRegisterController::class, 'export'])->name('mandiri.register.export');

    Route::prefix('mandiri')->name('mandiri.')->group(function () {
        Route::prefix('register')->name('register.')->group(function () {
            Route::get('/',                     [MandiriRegisterController::class, 'index'])->name('index');
            Route::get('/{id}',                 [MandiriRegisterController::class, 'show'])->name('show');
            Route::patch('/{id}/update-status', [MandiriRegisterController::class, 'updateStatus'])->name('update_status');
            Route::post('/{id}/mark-paid',      [MandiriRegisterController::class, 'markAsPaid'])->name('mark_paid');
        });

        Route::prefix('ukt')->name('ukt.')->group(function () {
            Route::get('/',                     [SlikBillingController::class, 'index'])->name('index');
            Route::get('/{id}',                 [SlikBillingController::class, 'show'])->name('show');
            Route::patch('/{id}/update-status', [SlikBillingController::class, 'updateStatus'])->name('update_status');
        });
    });

    // ── User & Biodata ──────────────────────────────────────
    Route::get('/users-biodata', [UserBiodataController::class, 'index'])->name('users.biodata');

    Route::prefix('biodata')->name('biodata.')->group(function () {
        Route::get('/',              [AdminBiodataController::class, 'index'])->name('index');
        Route::get('/export',        [AdminBiodataController::class, 'export'])->name('export');
        Route::get('/search',        [AdminBiodataController::class, 'search'])->name('search');
        Route::post('/{id}/status',  [AdminBiodataController::class, 'updateStatus'])->name('updateStatus');
        Route::post('/{id}/verify',  [AdminBiodataController::class, 'verify'])->name('verify');
        Route::post('/{id}/unverify',[AdminBiodataController::class, 'unverify'])->name('unverify');
        Route::get('/{id}',          [AdminBiodataController::class, 'show'])->name('show');
        Route::get('/{id}/edit',     [AdminBiodataController::class, 'edit'])->name('edit');
        Route::put('/{id}',          [AdminBiodataController::class, 'update'])->name('update');
        Route::delete('/{id}',       [AdminBiodataController::class, 'destroy'])->name('destroy');
    });

    // ── User Profiles ───────────────────────────────────────
    Route::get('/user-profiles',               [UserProfileController::class, 'index'])->name('userprofile.index');
    Route::get('/user-profiles/{id}',          [UserProfileController::class, 'show'])->name('userprofile.show');
    Route::get('/userprofile/show-ajax/{id}',  [UserProfileController::class, 'showAjax'])->name('userprofile.showAjax');
    Route::post('/user-profiles/{id}/delete',  [UserProfileController::class, 'destroy'])->name('userprofile.destroy');
    Route::post('/user-profiles/bulk-delete',  [UserProfileController::class, 'bulkDestroy'])->name('userprofile.bulkDestroy');

    Route::get('/userprofile/pending-moodle',  [MoodleSyncController::class, 'getPendingUsers'])->name('userprofile.pendingMoodle');
    Route::post('/userprofile/register-moodle',[MoodleSyncController::class, 'registerUsersToMoodle'])->name('userprofile.registerMoodle');

    // ── Profile Lengkap ─────────────────────────────────────
    Route::prefix('profile-lengkap')->name('profile-lengkap.')->group(function () {
        Route::post('/{id}/beasiswa',[ProfileLengkapController::class, 'toggleBeasiswa'])->name('beasiswa');
        Route::get('/export-slik', [ProfileLengkapController::class, 'exportSlikBilling'])->name('export-slik');

    	Route::post('/{id}/kirim-pengelolaan', [ProfileLengkapController::class, 'kirimKePengelolaan'])
    ->name('kirim-pengelolaan');
        Route::get('/',                    [ProfileLengkapController::class, 'index'])->name('index');
        Route::get('/{id}',                [ProfileLengkapController::class, 'show'])->name('show');
        Route::put('/{id}/update-prodi',   [ProfileLengkapController::class, 'updateProdi'])->name('update-prodi');
        Route::post('/{id}/verify',        [ProfileLengkapController::class, 'verify'])->name('verify');
        Route::delete('/{id}/unverify',    [ProfileLengkapController::class, 'unverify'])->name('unverify');
        Route::delete('/{id}',             [ProfileLengkapController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/luluskan',      [ProfileLengkapController::class, 'luluskanUser'])->name('luluskan');
        Route::post('/bulk-delete',        [ProfileLengkapController::class, 'bulkDestroy'])->name('bulk-destroy');
        Route::delete('/{user}/delete-ereg-files', [ProfileLengkapController::class, 'deleteEregFiles'])->name('delete-ereg-files');

        // VA Status
        Route::post('/{id}/update-va-status',     [ProfileLengkapController::class, 'updateVaStatus'])->name('update-va-status');
        Route::post('/{id}/auto-update-va-status', [ProfileLengkapController::class, 'autoUpdateVaStatus'])->name('auto-update-va-status');

        // PDF
        Route::get('/{id}/export-pdf',  [ProfileLengkapController::class, 'exportPdf'])->name('export-pdf');
        Route::get('/{id}/preview-pdf', [ProfileLengkapController::class, 'previewPdf'])->name('preview-pdf');

        // Farmasi
        Route::post('/{id}/farmasi',            [ProfileLengkapController::class, 'verifyFarmasi'])->name('farmasi');
        Route::get('/{id}/farmasi/create',      [ProfileLengkapController::class, 'createFarmasiVerification'])->name('create-farmasi-verification');
        Route::post('/{id}/farmasi/store',      [ProfileLengkapController::class, 'storeFarmasiVerification'])->name('store-farmasi-verification');
        Route::delete('/{id}/farmasi/destroy',  [ProfileLengkapController::class, 'destroyFarmasiVerification'])->name('destroy-farmasi-verification');
    });

    // ── Master Ereg ─────────────────────────────────────────
    Route::delete('/master-ereg/{id}/delete-files', [MasterEregController::class, 'deleteFiles'])->name('admin.master-ereg.delete-files');

    // ── Master Moodle Lulus ──────────────────────────────────
    Route::prefix('master-moodle-lulus')->name('master-moodle-lulus.')->group(function () {
        Route::get('/',                     [MasterLulusController::class, 'index'])->name('index');
        Route::post('/luluskan/{id}',       [MasterLulusController::class, 'luluskanUser'])->name('luluskan');
        Route::post('/luluskan-multiple',   [MasterLulusController::class, 'luluskanMultiple'])->name('luluskan-multiple');
        Route::post('/batalkan-lulus/{id}', [MasterLulusController::class, 'batalkanLulus'])->name('batalkan-lulus');
        Route::get('/statistics',           [MasterLulusController::class, 'statistics'])->name('statistics');
        Route::get('/user-detail/{id}',     [MasterLulusController::class, 'userDetail'])->name('user-detail');
        Route::get('/search-ajax',          [MasterLulusController::class, 'searchAjax'])->name('search-ajax');
    });

    // ── Farmasi ──────────────────────────────────────────────
    Route::get('/farmasi/export', [FarmasiController::class, 'export'])->name('farmasi.export');

    Route::prefix('farmasi')->name('farmasi.')->group(function () {
        Route::get('/',                               [FarmasiController::class, 'index'])->name('index');
        Route::post('/validate/{id}',                 [FarmasiController::class, 'validateStep'])->name('validate');
        Route::get('/auto-fill/{masterMoodleLulus}',  [FarmasiController::class, 'autoFill'])->name('auto-fill');
        Route::get('/master-ereg/{userId}',           [FarmasiController::class, 'getMasterEregData'])->name('master-ereg');
        Route::get('/create/{masterMoodleLulus}',     [FarmasiController::class, 'create'])->name('create');
        Route::post('/store/{masterMoodleLulus}',     [FarmasiController::class, 'store'])->name('store');
        Route::get('/statistics',                     [FarmasiController::class, 'statistics'])->name('statistics');
        Route::get('/import',                         [FarmasiController::class, 'import'])->name('import');
        Route::post('/process-import',                [FarmasiController::class, 'processImport'])->name('process-import');
        Route::post('/bulk-verify',                   [FarmasiController::class, 'bulkVerify'])->name('bulk-verify');
        Route::get('/{id}',                           [FarmasiController::class, 'show'])->name('show');
        Route::get('/{id}/edit',                      [FarmasiController::class, 'edit'])->name('edit');
        Route::put('/{id}',                           [FarmasiController::class, 'update'])->name('update');
        Route::delete('/{id}',                        [FarmasiController::class, 'destroy'])->name('destroy');
        Route::post('/quick-verify/{masterMoodleLulus}', [FarmasiController::class, 'quickVerify'])->name('quick-verify');
    });

    // ── Jadwal Ujian Farmasi ─────────────────────────────────
    Route::prefix('jadwal-farmasi')->name('jadwal.')->group(function () {
        Route::get('/setting',   [JadwalUjianController::class, 'create'])->name('create');
        Route::post('/store',    [JadwalUjianController::class, 'store'])->name('store');
        Route::delete('/destroy',[JadwalUjianController::class, 'destroy'])->name('destroy');
    });
    
    // ── konversi jalur RPL, Ekstensi, Pindahan ─────────────────────────────────
    Route::prefix('info-konversi')->name('konversi.')->group(function () {
        Route::get('/setting',    [JadwalUjianController::class, 'createKonversi'])->name('create');
        Route::post('/store',     [JadwalUjianController::class, 'storeKonversi'])->name('store');
        Route::delete('/destroy', [JadwalUjianController::class, 'destroyKonversi'])->name('destroy');
    });

    // ── GIS ──────────────────────────────────────────────────
    Route::prefix('gis')->name('gis.')->group(function () {
        Route::get('/',                  [GisController::class, 'index'])->name('index');
        Route::get('/data',              [GisController::class, 'getData'])->name('data');
        Route::get('/kelulusan',         [GisController::class, 'getKelulusan'])->name('kelulusan');
        Route::get('/rekap',             [GisController::class, 'getRekap'])->name('rekap');
        Route::get('/get-payment-stats', [GisController::class, 'getPaymentStats'])->name('getPaymentStats');
        Route::post('/sync',             [GisController::class, 'syncData'])->name('sync');
        Route::get('/capacity',          [GisController::class, 'capacityPage'])->name('capacity');
    });

    // ── Show All ─────────────────────────────────────────────
	Route::prefix('show-all')->name('show-all.')->group(function () {
        Route::get('/', [ShowAllController::class, 'index'])->name('index');
        Route::get('/export', [ShowAllController::class, 'exportExcel'])->name('export');
        Route::get('/tren-tahun-ke-tahun', [ShowAllController::class, 'trenMultiTahun'])->name('tren');
        Route::get('/get-jumlah-gelombang', [ShowAllController::class, 'getJumlahPerGelombang'])->name('get-jumlah-gelombang');
        Route::post('/tren-tahun-ke-tahun/ai-analyze', [ShowAllController::class, 'aiAnalyze'])->name('ai-analyze');
    });
	Route::prefix('database')->name('database.')->group(function () {
        Route::get('/preview', [DatabaseImportController::class, 'index'])->name('import');
        Route::post('/preview', [DatabaseImportController::class, 'previewSql'])->name('process');
        Route::post('/store', [DatabaseImportController::class, 'storeToDatabase'])->name('store');
        Route::post('/sync-legacy', [DatabaseImportController::class, 'syncDirectFromLegacy'])->name('sync-legacy');
        Route::post('/sync-current', [\App\Http\Controllers\Admin\DatabaseImportController::class, 'syncCurrentUsersToRiwayat'])->name('sync-current');
        Route::post('/sync-all-legacy', [\App\Http\Controllers\Admin\DatabaseImportController::class, 'syncAllLegacyDatabases'])->name('sync-all-legacy');
    });
    // ── BNI ──────────────────────────────────────────────────
    Route::prefix('bni')->name('bni.')->group(function () {
        Route::get('/',                     [BNIController::class, 'index'])->name('index');
        Route::post('/bulk-paid',           [BNIController::class, 'bulkPaid'])->name('bulk-paid');
        Route::post('/force-paid',          [BNIController::class, 'forcePaid'])->name('force-paid');
        Route::post('/sync-payment',        [BNIController::class, 'syncPayment'])->name('sync-payment');
        Route::post('/test-whatsapp',       [BNIController::class, 'testWhatsApp'])->name('test-whatsapp');
        Route::get('/get-callback-details', [BNIController::class, 'getCallbackDetails'])->name('get-callback-details');
        Route::get('/get-user-payments',    [BNIController::class, 'getUserPayments'])->name('get-user-payments');
        Route::post('/send-whatsapp',       [BNIController::class, 'sendWhatsApp'])->name('send-whatsapp');
        Route::post('/delete-user',         [BNIController::class, 'deleteUser'])->name('delete-user');
    });

    // ── Generate VA ──────────────────────────────────────────
    Route::prefix('generate-va')->name('generate-va.')->group(function () {
        Route::get('/',                               [GenerateVAController::class, 'index'])->name('index');
        Route::get('/create/{id}',                    [GenerateVAController::class, 'create'])->name('create');
        Route::post('/store/{id}',                    [GenerateVAController::class, 'store'])->name('store');
        Route::delete('/destroy/{id}',                [GenerateVAController::class, 'destroy'])->name('destroy');
        Route::get('/check-status/{id}',              [GenerateVAController::class, 'checkStatus'])->name('check-status');
        Route::get('/get-harga/{kodeJurusan}/{tenor}',[GenerateVAController::class, 'getHarga'])->name('get-harga');
    });

    // ── Gelombang ────────────────────────────────────────────
    Route::prefix('gelombang')->name('gelombang.')->group(function () {
        Route::get('/',            [GelombangController::class, 'index'])->name('index');
        Route::put('/update/{id}', [GelombangController::class, 'updateGelombang'])->name('update');
    });

    // ── Prodi ────────────────────────────────────────────────
    Route::prefix('prodi')->name('prodi.')->group(function () {
        Route::get('/',                                     [ProdiController::class, 'index'])->name('index');
        Route::get('/create',                               [ProdiController::class, 'create'])->name('create');
        Route::post('/',                                    [ProdiController::class, 'store'])->name('store');
        Route::get('/{id}/edit',                            [ProdiController::class, 'edit'])->name('edit');
        Route::put('/{id}',                                 [ProdiController::class, 'update'])->name('update');
        Route::delete('/{id}',                              [ProdiController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/status',                         [ProdiController::class, 'updateStatus'])->name('update-status');
        Route::post('/{id}/send-kuota',                     [ProdiController::class, 'sendKuotaInfo'])->name('sendKuota');
        Route::post('/{prodiId}/send-to-user/{userId}',     [ProdiController::class, 'sendKuotaToUser'])->name('sendKuotaToUser');
        Route::get('/users-with-phone',                     [ProdiController::class, 'getUsersWithPhone'])->name('usersWithPhone');
    });

    // ── Moodle ───────────────────────────────────────────────
    Route::get('/api/moodle/users', [AdminMoodleController::class, 'apiIndex'])->name('moodle.api.index');

    Route::post('/moodle/retry-ajax',          [MoodleRetryController::class, 'retryBatchAjax'])->name('moodle.retry.ajax');
    Route::get('/moodle/api/users/{courseId}', [MoodleGradeController::class, 'getEnrolledUsers'])->name('moodle.api.users');
    Route::post('/moodle/api/check-grade',     [MoodleGradeController::class, 'checkUserGrade'])->name('moodle.api.check_grade');

    Route::prefix('moodle')->name('moodle.')->group(function () {
        Route::get('/',         [AdminMoodleController::class, 'index'])->name('index');
        Route::get('/list',     [AdminMoodleController::class, 'list'])->name('list');
        Route::get('/{id}',     [AdminMoodleController::class, 'show'])->name('show');

        Route::post('/sync',          [MoodleController::class, 'syncData'])->name('sync');
        Route::get('/sync-stats',     [MoodleController::class, 'getSyncStats'])->name('sync-stats');
        Route::post('/sync-detailed', [MoodleController::class, 'syncDetailedData'])->name('sync-detailed');
        Route::get('/detailed-stats', [MoodleController::class, 'getDetailedStats'])->name('detailed-stats');

        Route::get('/grades', [MoodleGradeController::class, 'index'])->name('grades');

        Route::get('/failed',  [MoodleRetryController::class, 'index'])->name('failed');
        Route::post('/retry',  [MoodleRetryController::class, 'retryFailedUsers'])->name('retry');

        Route::get('/test-connection',       [MoodleSyncController::class, 'testMoodleConnection'])->name('test-connection');
        Route::post('/sync-failed',          [MoodleSyncController::class, 'syncFailedAndNewUsers'])->name('sync-failed');
        Route::post('/enrol-user',           [MoodleSyncController::class, 'enrolUserToCourse'])->name('enrol-user');
        Route::post('/bulk-enrol',           [MoodleSyncController::class, 'bulkEnrolUsers'])->name('bulk-enrol');
        Route::post('/enrol-all',            [MoodleSyncController::class, 'enrolAllSuccessfulUsers'])->name('enrol-all');
        Route::post('/manual-enrol',         [MoodleSyncController::class, 'manualEnrolUsers'])->name('manual-enrol');
        Route::get('/fix-token-permissions', [MoodleSyncController::class, 'fixTokenPermissions'])->name('fix-token-permissions');
        Route::get('/check-enrollment',      [MoodleSyncController::class, 'checkUserEnrollment'])->name('check-enrollment');
        Route::get('/statistics',            [MoodleSyncController::class, 'getStatistics'])->name('statistics');
        Route::get('/debug-enrollment',      [MoodleSyncController::class, 'debugEnrollment'])->name('debug-enrollment');
        Route::post('/batch-register',       [MoodleSyncController::class, 'registerUsersToMoodle'])->name('batch-register');
        Route::get('/failed-users',          [MoodleSyncController::class, 'getFailedOrUnregisteredUsers'])->name('failed-users');

        // Quiz Complete
        Route::prefix('quiz-complete')->name('quiz-complete.')->group(function () {
            Route::get('/',                           [MoodleCompleteController::class, 'index'])->name('index');
            Route::post('/sync-all',                  [MoodleCompleteSyncController::class, 'syncAllQuizAttempts'])->name('sync-all');
            Route::post('/sync-user/{moodleUserId}',  [MoodleCompleteSyncController::class, 'syncUserQuizAttemptsData'])->name('sync-user');
            Route::post('/force-sync',                [MoodleCompleteSyncController::class, 'forceSyncQuizAttempts'])->name('force-sync');
            Route::get('/statistics',                 [MoodleCompleteSyncController::class, 'getSyncStatistics'])->name('statistics');
            Route::post('/clean-duplicates',          [MoodleCompleteSyncController::class, 'cleanDuplicateAttempts'])->name('clean-duplicates');
            Route::get('/quiz-info',                  [MoodleCompleteSyncController::class, 'getQuizInfo'])->name('quiz-info');
            Route::post('/sync-lolos',                [MoodleCompleteController::class, 'syncLolos'])->name('sync-lolos');
            Route::get('/ranking',                    [MoodleCompleteController::class, 'ranking'])->name('ranking');
            Route::get('/program-recommendations',    [MoodleCompleteController::class, 'programRecommendations'])->name('program-recommendations');
            Route::get('/user/{moodleUserId}',        [MoodleCompleteController::class, 'byUser'])->name('by-user');
            Route::get('/quiz/{quizId}',              [MoodleCompleteController::class, 'byQuiz'])->name('by-quiz');
            Route::get('/{id}/edit',                  [MoodleCompleteController::class, 'edit'])->name('edit');
            Route::put('/{id}',                       [MoodleCompleteController::class, 'update'])->name('update');
            Route::post('/{id}',                      [MoodleCompleteController::class, 'destroy'])->name('destroy');
            Route::get('/{id}',                       [MoodleCompleteController::class, 'show'])->name('show');
        });

        // Courses
        Route::prefix('courses')->name('courses.')->group(function () {
            Route::get('/',                    [MoodleUserCourseController::class, 'index'])->name('index');
            Route::get('/user/{moodleUserId}', [MoodleUserCourseController::class, 'byUser'])->name('by-user');
            Route::get('/{id}',                [MoodleUserCourseController::class, 'show'])->name('show');
            Route::delete('/{id}',             [MoodleUserCourseController::class, 'destroy'])->name('destroy');
        });

        // Local Moodle
        Route::prefix('local')->name('local.')->group(function () {
            Route::get('/',          [LocalMoodleController::class, 'index'])->name('index');
            Route::get('/create',    [LocalMoodleController::class, 'create'])->name('create');
            Route::post('/',         [LocalMoodleController::class, 'store'])->name('store');
            Route::get('/{id}',      [LocalMoodleController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [LocalMoodleController::class, 'edit'])->name('edit');
            Route::put('/{id}',      [LocalMoodleController::class, 'update'])->name('update');
            Route::delete('/{id}',   [LocalMoodleController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/sync',[LocalMoodleController::class, 'sync'])->name('sync');
        });
    });

    // ── Chat ─────────────────────────────────────────────────
    Route::prefix('chat')->name('chat.')->group(function () {
        Route::get('/',              [AdminChatController::class, 'index'])->name('index');
        Route::post('/send',         [AdminChatController::class, 'store'])->name('chat.store');
        Route::get('/fetch',         [AdminChatController::class, 'fetch'])->name('chat.fetch');
        Route::get('/user/{userId}', [AdminChatController::class, 'getOrCreateChatId'])->name('chat.user');
    });

    // ── Test / Debug ─────────────────────────────────────────
    Route::get('/test-prodi',          [TestController::class, 'testProdi'])->name('test.prodi');
    Route::post('/get-prodi-data',     [TestController::class, 'getProdiData'])->name('get.prodi.data');
    Route::get('/test-moodle',         [TestController::class, 'testMoodleConnection'])->name('test.moodle');
    Route::get('/prodi-codes',         [TestController::class, 'getProdiCodes'])->name('prodi.codes');
    Route::get('/search-prodi/{kode}', [TestController::class, 'searchProdi'])->name('search.prodi');

    // ── Download ─────────────────────────────────────────────
    Route::get('/download',              [DownloadController::class, 'index'])->name('download.index');
    Route::get('/download/moodle-lulus', [DownloadController::class, 'downloadMoodleLulus'])->name('download.moodle-lulus');
    Route::post('/download/custom-mhs',  [DownloadController::class, 'downloadCustomMhs'])->name('download.custom-mhs');

    Route::get('/download-data',                  [DownloadDataController::class, 'index'])->name('download-data.index');
    Route::post('/download/custom-mhs',           [DownloadDataController::class, 'downloadCustomMhs'])->name('download-data.custom-mhs');
    Route::get('/download/data-mahasiswa-finals', [DownloadDataController::class, 'downloadDataMahasiswaFinals'])->name('download.data-mahasiswa-finals');
    Route::get('/download-data/biodatas',         [DownloadDataController::class, 'downloadBiodatas'])->name('download.biodatas');
    Route::get('/download-data/master-ereg',      [DownloadDataController::class, 'downloadMasterEreg'])->name('download.master-ereg');
    Route::get('/download-data/all',              [DownloadDataController::class, 'downloadAll'])->name('download.all');
    Route::get('/download-data/complete-all',     [DownloadDataController::class, 'downloadAllComplete'])->name('download.complete-all');
    Route::get('/download-data/with-pdf',         [DownloadDataController::class, 'downloadAllWithPDF'])->name('download.with-pdf');

    Route::get('/unduhan', [DownloadController::class, 'index'])->name('unduhan.index');

    // ── Ujian Countdown ──────────────────────────────────────
    Route::resource('ujian-countdown', UjianCountdownController::class);
    Route::post('ujian-countdown/{ujianCountdown}/toggle-active', [UjianCountdownController::class, 'toggleActive'])->name('ujian-countdown.toggle-active');
    Route::post('ujian-countdown/{ujianCountdown}/activate',      [UjianCountdownController::class, 'activate'])->name('ujian-countdown.activate');
    Route::post('ujian-countdown/{ujianCountdown}/deactivate',    [UjianCountdownController::class, 'deactivate'])->name('ujian-countdown.deactivate');

    // ── Group Gelombang ──────────────────────────────────────
    Route::resource('group-gelombang', GroupGelombangController::class);
    Route::post('group-gelombang/{groupGelombang}/toggle-active', [GroupGelombangController::class, 'toggleActive'])->name('group-gelombang.toggle-active');
    Route::post('group-gelombang/update-order',                   [GroupGelombangController::class, 'updateOrder'])->name('group-gelombang.update-order');
    Route::post('group-gelombang/bulk-create',                    [GroupGelombangController::class, 'bulkCreate'])->name('group-gelombang.bulk-create');

    // ── Berkas ───────────────────────────────────────────────
    Route::prefix('berkas')->name('berkas.')->group(function () {
        Route::get('/',                     [BerkasController::class, 'index'])->name('index');
        Route::get('/{id}',                 [BerkasController::class, 'show'])->name('show');
        Route::get('/{id}/download/{type}', [BerkasController::class, 'download'])->name('download');
        Route::patch('/{id}/status',        [BerkasController::class, 'updateStatus'])->name('updateStatus');

        Route::prefix('download')->name('download.')->group(function () {
            Route::get('/all-users',          [BerkasController::class, 'downloadAllUsers'])->name('all-users');
            Route::get('/users-biodata',      [BerkasController::class, 'downloadUsersWithBiodata'])->name('users-biodata');
            Route::get('/complete-users',     [BerkasController::class, 'downloadCompleteUsers'])->name('complete-users');
            Route::post('/by-gelombang',      [BerkasController::class, 'downloadByGelombang'])->name('by-gelombang');
            Route::get('/all',                [BerkasController::class, 'downloadAll'])->name('all');
            Route::get('/by-status/{status}', [BerkasController::class, 'downloadByStatus'])->name('by-status');
        });
    });

    // ── Promo Code ───────────────────────────────────────────
    Route::prefix('promocode')->name('promocode.')->group(function () {
        Route::get('/',                    [PromoCodeController::class, 'index'])->name('index');
        Route::post('/',                   [PromoCodeController::class, 'store'])->name('store');
        Route::delete('/{id}',             [PromoCodeController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/toggle-status',[PromoCodeController::class, 'toggleStatus'])->name('toggleStatus');
        Route::post('/validate-code',      [PromoCodeController::class, 'validateCode'])->name('validateCode');
    });

    // ── Calendar ─────────────────────────────────────────────
    Route::get('/calendar',              [CalendarController::class, 'index'])->name('calendar');
    Route::get('/calendar/events',       [CalendarController::class, 'getEvents']);
    Route::post('/calendar/store',       [CalendarController::class, 'store']);
    Route::put('/calendar/update/{id}',  [CalendarController::class, 'update']);
    Route::delete('/calendar/delete/{id}',[CalendarController::class, 'destroy']);

    // ── Events ───────────────────────────────────────────────
    Route::get('/events',         [EventController::class, 'index'])->name('events.index');
    Route::post('/events',        [EventController::class, 'store'])->name('events.store');
    Route::put('/events/{id}',    [EventController::class, 'update'])->name('events.update');
    Route::delete('/events/{id}', [EventController::class, 'destroy'])->name('events.destroy');

    // ── Form Elements ────────────────────────────────────────
    Route::get('/form-elements', [FormElementsController::class, 'index'])->name('form-elements');

    Route::prefix('form-elements/questions')->name('form-elements.questions.')->group(function () {
        Route::get('/create', [QuestionController::class, 'create'])->name('create');
        Route::post('/',      [QuestionController::class, 'store'])->name('store');
    });

    Route::prefix('form-elements/batch')->name('form-elements.batch.')->group(function () {
        Route::get('/index',  [BatchController::class, 'index'])->name('index');
        Route::get('/create', [BatchController::class, 'create'])->name('create');
        Route::post('/store', [BatchController::class, 'store'])->name('store');
    });

    // ── Posts ────────────────────────────────────────────────
    Route::resource('posts', PostController::class);
    Route::post('posts/{post}/publish', [PostController::class, 'publish'])->name('posts.publish');

    // ── Akun ─────────────────────────────────────────────────
    Route::prefix('akun')->name('akun.')->group(function () {
        Route::get('/',               [AkunController::class, 'index'])->name('index');
        Route::get('/create',         [AkunController::class, 'create'])->name('create');
        Route::post('/',              [AkunController::class, 'store'])->name('store');
        Route::get('/{id}/edit',      [AkunController::class, 'edit'])->name('edit');
        Route::put('/{id}',           [AkunController::class, 'update'])->name('update');
        Route::delete('/{id}',        [AkunController::class, 'destroy'])->name('destroy');
        Route::post('/validate-promo',[AkunController::class, 'validatePromoCode'])->name('validate-promo');
    });
});


/*
|--------------------------------------------------------------------------
| 6. Profesi Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'role:mahasiswa_baru'])
    ->prefix('profesi')
    ->name('profesi.')
    ->group(function () {

        // 1. Dashboard Khusus Profesi
        Route::get('/dashboard', [\App\Http\Controllers\Mahasiswa\DashboardProfesiController::class, 'index'])->name('dashboard');
        
        // 2. Resume Data
        Route::get('/resume-data', [\App\Http\Controllers\Mahasiswa\DashboardProfesiController::class, 'resumeData'])->name('resume');   
        
        // 3. Biodata Controller
        Route::get('/biodata', [\App\Http\Controllers\Mahasiswa\profesi\BiodataProfesiController::class, 'create'])->name('biodata.create');
        Route::post('/biodata', [\App\Http\Controllers\Mahasiswa\profesi\BiodataProfesiController::class, 'store'])->name('biodata.store');

        // 4. Berkas Controller & AJAX Cek Status
        Route::get('/upload-berkas', [\App\Http\Controllers\Mahasiswa\ProfesiBerkasController::class, 'create'])->name('berkas.create');
        Route::post('/upload-berkas', [\App\Http\Controllers\Mahasiswa\ProfesiBerkasController::class, 'store'])->name('berkas.store');
        Route::get('/berkas/cek-status', [\App\Http\Controllers\Mahasiswa\DashboardProfesiController::class, 'checkStatusBerkas'])->name('berkas.checkStatus');
        
        // 5. Surat Pernyataan Profesi (Group di dalam Group)
        Route::prefix('surat')
            ->name('surat.')
            ->controller(\App\Http\Controllers\Mahasiswa\profesi\SuratProfesiController::class)
            ->group(function () {
                Route::get('/pkms', 'pkms')->name('pkms');
                Route::get('/pkmu', 'pkmu')->name('pkmu');
                Route::get('/pkpa', 'pkpa')->name('pkpa');
            }); // <--- PENUTUP GROUP SURAT ADA DI SINI

        // 6. Data Final PDDIKTI (DI LUAR GROUP SURAT, TAPI MASIH DI DALAM PROFESI)
        Route::get('/data-final', [\App\Http\Controllers\Mahasiswa\profesi\PDDIKTI\DataFinalProfesiController::class, 'create'])->name('data-final.create');
        Route::post('/data-final', [\App\Http\Controllers\Mahasiswa\profesi\PDDIKTI\DataFinalProfesiController::class, 'store'])->name('data-final.store');

    
        // 6. Review & Pembuatan VA Pembayaran UKT Profesi
       // ==========================================================
Route::get('/reviewpayment', [\App\Http\Controllers\Mahasiswa\profesi\payment\ReviewPaymentProfesiController::class, 'index'])->name('reviewpayment');
Route::post('/reviewpayment/create', [\App\Http\Controllers\Mahasiswa\profesi\payment\ReviewPaymentProfesiController::class, 'createVa'])->name('reviewpayment.create');
Route::get('/reviewpayment/{orderId}', [\App\Http\Controllers\Mahasiswa\profesi\payment\ReviewPaymentProfesiController::class, 'showVa'])->name('reviewpayment.show');
Route::get('/reviewpayment/{orderId}/status', [\App\Http\Controllers\Mahasiswa\profesi\payment\ReviewPaymentProfesiController::class, 'checkStatus'])->name('reviewpayment.checkStatus');
Route::delete('/reviewpayment/{orderId}/cancel', [\App\Http\Controllers\Mahasiswa\profesi\payment\ReviewPaymentProfesiController::class, 'cancelVa'])->name('reviewpayment.cancel');
    });
/*
|--------------------------------------------------------------------------
| 7. Mahasiswa Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'role:mahasiswa_baru'])
    ->prefix('mahasiswa')
    ->name('mahasiswa.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [MahasiswaDashboardController::class, 'index'])->name('dashboard');
        
        // Chatbot (AI Assistant)
        Route::get('/chatbot', [\App\Http\Controllers\Mahasiswa\ChatbotController::class, 'index'])->name('chatbot.index');
        
        // Progress & Status
        Route::get('/progress/check', [ProgressController::class, 'apiProgress'])->name('progress.check');
        Route::get('/biodata/status', [ProgressController::class, 'checkBiodataStatus'])->name('biodata.status');
        Route::get('/progress',       [ProgressController::class, 'getProgressData'])->name('progress');

        // Chat
        Route::get('/chat',       [ChatController::class, 'index'])->name('chat.index');
        Route::post('/chat/send', [ChatController::class, 'store'])->name('chat.store');
        Route::get('/chat/fetch', [ChatController::class, 'fetch'])->name('chat.fetch');

        // Biodata (Step 1)
        Route::get('/biodata',        [MahasiswaBiodataController::class, 'create'])->name('biodata');
        Route::get('/biodata/create', [MahasiswaBiodataController::class, 'create'])->name('biodata.create');
        Route::post('/biodata',       [MahasiswaBiodataController::class, 'store'])->name('biodata.store');
        Route::post('/biodata/draft', [MahasiswaBiodataController::class, 'saveDraft'])->name('biodata.draft');
        Route::get('/biodata/edit',   [MahasiswaBiodataController::class, 'edit'])->name('biodata.edit');

        // Ringkasan
        Route::get('/ringkasan',               [RingkasanController::class, 'index'])->name('ringkasan');
        Route::post('/ringkasan/simpan-almet', [RingkasanController::class, 'simpanAlmamater'])->name('ringkasan.simpan_almet');
        Route::get('/ringkasan/print',         [RingkasanController::class, 'printManifest'])->name('print');

        // Virtual Account
        Route::middleware(['check.va.status'])->prefix('va')->name('va.')->group(function () {
            Route::get('/dashboard',     [VirtualAccountController::class, 'dashboard'])->name('dashboard');
            Route::post('/regenerate',   [VirtualAccountController::class, 'regenerateVA'])->name('regenerate');
            Route::get('/check-status',  [VirtualAccountController::class, 'checkStatus'])->name('check.status');
            Route::get('/auto-login',    [VirtualAccountController::class, 'autoLogin'])->name('auto.login');
            Route::post('/upload-proof', [VirtualAccountController::class, 'uploadProof'])->name('uploadProof');
        });

        // Step 2 – Memerlukan biodata selesai
        Route::middleware(['progress_pendaftaran:biodata_completed'])->group(function () {
            Route::prefix('scanner')->name('scanner.')->group(function () {
                Route::post('/scan-temp',         [OCRScannerController::class, 'scanTempFiles'])->name('scan-temp');
                Route::get('/test-connection',    [OCRScannerController::class, 'testConnection'])->name('test');
                Route::post('/trigger',           [OCRScannerController::class, 'triggerScan'])->name('trigger');
                Route::get('/status',             [OCRScannerController::class, 'getScanStatus'])->name('status');
                Route::post('/single/{fileType}', [OCRScannerController::class, 'scanSingleFileByType'])->name('single');
                Route::post('/scan-all',          [OCRScannerController::class, 'scanAllFiles'])->name('scan-all');
            });

            Route::get('/registration',           [RegisterStepController::class, 'index'])->name('registration');
            Route::post('/registration/step1',    [RegisterStepController::class, 'processStep1'])->name('registration.step1');
            Route::post('/registration/step2',    [RegisterStepController::class, 'processStep2'])->name('registration.step2');
            Route::post('/registration/step3',    [RegisterStepController::class, 'processStep3'])->name('registration.step3');
            Route::post('/registration/complete', [RegisterStepController::class, 'completeRegistration'])->name('registration.complete');
        });

        // Step 3 & 4 – Memerlukan master ereg selesai
        Route::middleware(['progress_pendaftaran:ereg_completed'])->group(function () {
            Route::get('/ujian',           [MoodleQuizController::class, 'index'])->name('ujian.index');
            Route::get('/quiz/my-results', [MoodleQuizController::class, 'getMyQuizResults'])->name('quiz.my-results');
        });

        // Step 5 – Memerlukan pembayaran lunas
        Route::middleware(['progress_pendaftaran:paid'])->group(function () {
            Route::get('/data-final',  [DataFinalController::class, 'create'])->name('data-final.create');
            Route::post('/data-final', [DataFinalController::class, 'store'])->name('data-final.store');

            Route::get('/data-mahasiswa',  [MahasiswaDataController::class, 'index'])->name('data.index');
            Route::post('/data-mahasiswa', [MahasiswaDataController::class, 'store'])->name('data.store');
        });

        // Pembayaran
        Route::get('/pembayaran/status/{order_id}', [PembayaranController::class, 'checkStatus'])->name('pembayaran.check_status');
        Route::post('/pembayaran/reset/{order_id}', [PembayaranController::class, 'resetTagihan'])->name('pembayaran.reset');
        Route::get('/pembayaran/cetak/{order_id}',  [PembayaranController::class, 'printReceipt'])->name('pembayaran.receipt');

        // Register Ulang
        Route::get('/register-ulang',  [RegisterStepController::class, 'index'])->name('register.index');
        Route::post('/register-ulang', [RegisterStepController::class, 'store'])->name('register.store');
    });

/*
|--------------------------------------------------------------------------
| 8. Other Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/pembayaran-ukt',              [PembayaranController::class, 'index'])->name('pembayaran.ukt');
Route::post('/pembayaran/create-va',       [PembayaranController::class, 'createVA'])->name('pembayaran.createVA');
Route::get('/pembayaran/show/{order_id}',  [PembayaranController::class, 'showInstruksi'])->name('pembayaran.show');
Route::post('/pembayaran/save-almamater',  [PembayaranController::class, 'saveAlmamater'])->name('pembayaran.saveAlmamater');
Route::post('/payment/callback',           [PembayaranController::class, 'paymentCallback'])->name('payment.callback');
Route::get('/pembayaran/status/{order_id}',[PembayaranController::class, 'getPaymentStatus'])->name('pembayaran.status');

Route::post('/slik/callback/payment', [GenerateVAController::class, 'callbackPayment']);

Route::get('/ringkasan-debug-bypass', [RingkasanController::class, 'index'])->name('ringkasan.debug.bypass');

Route::post('/promocode/validate', [RegisteredUserController::class, 'validatePromoCode'])->name('promocode.validate');

// Download Surat (testing – auth required)
Route::middleware(['auth'])->get(
    '/mahasiswa/download-surat',
    [App\Http\Controllers\Mahasiswa\SuratKelulusanController::class, 'downloadSurat']
)->name('mahasiswa.surat.download');


/*
|--------------------------------------------------------------------------
| 9. Includes
|--------------------------------------------------------------------------
*/
require __DIR__ . '/whatsapp.php';
require __DIR__ . '/auth.php';







// Debug kuota prodi
Route::get('/cuci-database', function() {
    // 1. MATIKAN SEMUA KUOTA (KEMBALIKAN KE NOL)
    // Kita hapus semua stempel lunas & jurusan lulusan yang berantakan
    \App\Models\MasterEreg::query()->update([
        'is_quota_taken' => 0,
        'jurusan_lulusan' => null
    ]);

    // 2. CARI YANG BENAR-BENAR VALID & LUNAS
    // Hanya ambil yang status SLIK-nya strictly "paid" atau "lunas"
    $paidUsers = \App\Models\SlikBilling::whereRaw('LOWER(status) IN (?, ?)', ['paid', 'lunas'])
        ->distinct('user_id')
        ->pluck('user_id');

    $diperbaiki = 0;

    // 3. PROSES SATU PER SATU ANAK YANG LUNAS
    foreach($paidUsers as $uid) {
        $ereg = \App\Models\MasterEreg::where('user_id', $uid)->first();
        if(!$ereg) continue;

        // Berikan stempel kuota HANYA untuk mereka yang beneran lunas
        $ereg->is_quota_taken = 1;

        // Tentukan jurusan (Default: Pilihan 1)
        $kodeFinal = $ereg->jurusan1;

        // LOGIKA KHUSUS FARMASI (Kode Prodi: 48201)
        if ($kodeFinal == '48201') { 
            $verif = \App\Models\FarmasiVerification::where('user_id', $uid)->first();
            $statusTahap2 = $verif ? strtolower($verif->status_verifikasi) : 'pending';
            
            // Jika ditolak/gagal tes kesehatan, lempar ke Pilihan 2
            if (in_array($statusTahap2, ['rejected', 'ditolak', 'tidak lulus', 'gagal'])) {
                $kodeFinal = $verif->prodi_pilihan2 ?? $ereg->jurusan2;
            }
        }

        // Simpan keputusan final
        $ereg->jurusan_lulusan = $kodeFinal;
        $ereg->save();
        $diperbaiki++;
    }

    // 4. HITUNG ULANG SELURUH PRODI
    \App\Models\ProgramStudi::all()->each(function($p) {
        $p->syncDayaTampung();
    });

    return "<h1>CUCI GUDANG BERHASIL! 🚀</h1>
            <p>Total <b>{$diperbaiki}</b> mahasiswa lunas telah dipetakan ulang dengan sempurna.</p>
            <p>Silakan cek Dashboard Admin, angkanya dijamin kembali cantik dan sinkron!</p>";
});

// Admin Settings
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/settings', function () {
        return view('admin.settings.index');
    })->name('settings.index');
});

// Admin Settings S2
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/settings/s2/status', function () {
        $data = ['active' => true];
        if (\Illuminate\Support\Facades\Storage::disk('local')->exists('app_s2_status.json')) {
            $data = json_decode(\Illuminate\Support\Facades\Storage::disk('local')->get('app_s2_status.json'), true);
        }
        return response()->json($data);
    })->name('settings.s2.status');
    
    Route::post('/settings/s2/toggle', function (\Illuminate\Http\Request $request) {
        $data = ['active' => (bool) $request->active];
        \Illuminate\Support\Facades\Storage::disk('local')->put('app_s2_status.json', json_encode($data));
        return response()->json(['status' => 'success']);
    })->name('settings.s2.toggle');
});


// Admin Settings Apoteker
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/settings/apoteker/status', function () {
        $data = ['active' => false]; // Default false sesuai dengan request saat ini yg mati
        if (\Illuminate\Support\Facades\Storage::disk('local')->exists('app_apoteker_status.json')) {
            $data = json_decode(\Illuminate\Support\Facades\Storage::disk('local')->get('app_apoteker_status.json'), true);
        }
        return response()->json($data);
    })->name('settings.apoteker.status');
    
    Route::post('/settings/apoteker/toggle', function (\Illuminate\Http\Request $request) {
        $data = ['active' => (bool) $request->active];
        \Illuminate\Support\Facades\Storage::disk('local')->put('app_apoteker_status.json', json_encode($data));
        return response()->json(['status' => 'success']);
    })->name('settings.apoteker.toggle');
});
