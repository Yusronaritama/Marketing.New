<?php
use App\Http\Controllers\Admin\WhatsAppChatController;
use App\Http\Controllers\Admin\WhatsAppController;
use App\Http\Controllers\Admin\Admin_MoodleController;
use App\Http\Controllers\Admin\Admin_MoodleTestController;
use App\Http\Controllers\Api\BNIRegenerateVAController;
use App\Http\Controllers\Api\RegistrasiUktController;
use App\Http\Controllers\Mahasiswa\PembayaranController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PerbaikanController;
use App\Http\Controllers\Api\BniCallbackController;

use App\Http\Controllers\Admin\BniCallBackkController;
use App\Http\Controllers\Api\RegistrasiPendaftarController;
use App\Http\Controllers\Api\MandiriCallbackController;
use App\Http\Controllers\Admin\ApiPendaftarController;
use App\Http\Controllers\Admin\GenerateNimController;

Route::prefix('admin')->group(function () {
    Route::get('/pendaftar', [ApiPendaftarController::class, 'getPendaftar']);
});

// Endpoint Publik / Webhook untuk Sinkronisasi SLIK (dapat diakses n8n / scheduler)
Route::match(['get', 'post'], '/slik/sync-bulk', [GenerateNimController::class, 'sendBulkToSlik'])->name('api.slik.sync-bulk');
Route::match(['get', 'post'], '/sync-slik-bulk', [GenerateNimController::class, 'sendBulkToSlik']);

Route::post('/registrasi_ukt', [RegistrasiUktController::class, 'handleRegistrasiUkt']);
Route::post('/registrasi_pendaftaran', [RegistrasiPendaftarController::class, 'handleRegistrasiPendaftar']);
Route::get('/payment-status/{order_id}', [PembayaranController::class, 'getPaymentStatus']);




Route::post('/callback', [BniCallbackController::class, 'handle']);

Route::get('/health', function () {
    return response()->json([
        'status' => 'OK',
        'service' => 'Laravel Marketing API',
        'timestamp' => now()->toIso8601String(),
        'environment' => app()->environment(),
        'version' => '1.0.0'
    ]);
});
Route::get('/bni/check-status', [BNIRegenerateVAController::class, 'checkBNIStatus']);
Route::post('/bni/regenerate-va-1min', [BNIRegenerateVAController::class, 'regenerateVAOneMinute']);
Route::prefix('bni')->group(function () {
    // Health check endpoint
    Route::get('/health', [BNIRegenerateVAController::class, 'healthCheck'])
        ->withoutMiddleware(['api.token']);
    
    // Regenerate VA endpoint
    Route::post('/regenerate-va', [BNIRegenerateVAController::class, 'regenerateVA'])
        ->middleware('api.token');
    
    // Get pending users
    Route::get('/pending-users', [BNIRegenerateVAController::class, 'getPendingUsers'])
        ->middleware('api.token');
    
    // Get user detail (untuk debugging)
    Route::get('/user/{userId}', [BNIRegenerateVAController::class, 'getUserDetail'])
        ->middleware('api.token');
            Route::post('/check-va', [App\Http\Controllers\Api\BNIRegenerateVAController::class, 'checkVAStatus']);
});

// routes/api.php
// Route::get('admin/moodle/pad-grade-report', [MoodleCompleteSyncController::class, 'getPadGradeReport']);
// Route::get('admin/moodle/statistics', [MoodleCompleteSyncController::class, 'getSyncStatistics']);
// Route::prefix('admin/moodle-grades')->group(function () {
//     // Grade sync operations
//     Route::post('sync-grades-existing', [MoodleGradeSyncController::class, 'syncGradesForExistingAttempts']);
//     Route::post('update-single-grade/{attemptId}', [MoodleGradeSyncController::class, 'updateSingleAttemptGrade']);
//     Route::post('force-calculate-grades', [MoodleGradeSyncController::class, 'forceCalculateGrades']);
    
//     // Debug and info
//     Route::get('debug-attempt/{attemptId}', [MoodleGradeSyncController::class, 'debugAttemptData']);
//     Route::get('grade-statistics', [MoodleGradeSyncController::class, 'getGradeStatistics']);
//     Route::get('missing-grades', [MoodleGradeSyncController::class, 'getAttemptsWithMissingGrades']);
// });


Route::prefix('admin/moodle-test')->group(function () {
    // Test koneksi dan informasi dasar
    Route::get('test-connection', [Admin_MoodleTestController::class, 'testConnection']);
    Route::get('test-quiz-functions', [Admin_MoodleTestController::class, 'testMoodleQuizFunctions']);
    
    // Courses
    Route::get('courses', [Admin_MoodleTestController::class, 'getAllCourses']);
    
    // Quizzes
    Route::get('quizzes', [Admin_MoodleTestController::class, 'getAllQuizzes']);
    Route::get('quizzes/search', [Admin_MoodleTestController::class, 'searchQuizzes']);
    Route::get('quizzes/course/{courseId}', [Admin_MoodleTestController::class, 'getQuizzesByCourseId']);
    Route::get('quizzes/{quizId}', [Admin_MoodleTestController::class, 'getQuizDetails']);
    Route::get('quizzes/{quizId}/stats', [Admin_MoodleTestController::class, 'getQuizAttemptsStats']);
    
    // User-specific tests
    Route::get('users/{moodleUserId}/courses-with-quizzes', [Admin_MoodleTestController::class, 'getUserCoursesWithQuizzes']);
    Route::get('users/{moodleUserId}/test-attempts', [Admin_MoodleTestController::class, 'testQuizAttempts']);
    Route::get('users/{moodleUserId}/test-attempts/{quizId}', [Admin_MoodleTestController::class, 'testQuizAttempts']);
});


// ============================================
// MOODLE ROUTES
// ============================================
Route::prefix('moodle')->name('moodle.')->group(function () {
    Route::post('upload-to-database', [Admin_MoodleController::class, 'uploadToDatabase'])->name('upload-to-database');
    Route::post('upload-user-to-database', [Admin_MoodleController::class, 'uploadUserToDatabase'])->name('upload-user-to-database');
    Route::post('force-upload-user-to-database', [Admin_MoodleController::class, 'forceUploadUserToDatabase'])->name('force-upload-user-to-database');
    
    Route::get('users/all', [Admin_MoodleController::class, 'getAllMoodleUsers'])->name('users.all');
    Route::get('users/complete', [Admin_MoodleController::class, 'getCompleteMoodleUsers'])->name('users.complete');
    Route::get('users/{moodleUserId}/complete', [Admin_MoodleController::class, 'getCompleteUserData'])->name('users.complete-single');
    Route::get('users/stats', [Admin_MoodleController::class, 'getMoodleUsersStats'])->name('users.stats');
});

Route::prefix('whatsapp')->group(function () {
    Route::post('/test-notification', [BniCallBackkController::class, 'testWhatsAppNotification'])
        ->name('whatsapp.test.notification');
    
    Route::post('/send-custom', [BniCallBackkController::class, 'sendCustomWhatsApp'])
        ->name('whatsapp.send.custom');
    
    Route::get('/status', function () {
        $whatsappService = app(\App\Services\WhatsAppService::class);
        return response()->json([
            'status' => $whatsappService->checkStatus(),
            'is_ready' => $whatsappService->isReady(),
            'server_url' => env('WHATSAPP_SERVER_URL')
        ]);
    });
});

// ============================================
// PRESENSI PKKMB API
// ============================================
Route::prefix('presensi')->group(function () {
    Route::match(['POST', 'OPTIONS'], '/presensi', [\App\Http\Controllers\PresensiController::class, 'presensi']);
    Route::match(['POST', 'OPTIONS'], '/daftar_presensi_by_email', [\App\Http\Controllers\PresensiController::class, 'daftar_presensi_by_email']);
    Route::match(['POST', 'OPTIONS'], '/daftar_presensi_by_email_pulang', [\App\Http\Controllers\PresensiController::class, 'daftar_presensi_by_email_pulang']);
});



