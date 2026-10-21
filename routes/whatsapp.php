<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\WhatsAppController;

// Route untuk admin
Route::middleware(['web', 'auth'])->prefix('admin/whatsapp')->name('admin.whatsapp.')->group(function () {
    Route::get('/status', [WhatsAppController::class, 'status'])->name('status');
    Route::get('/test', [WhatsAppController::class, 'showTestForm'])->name('test');
    Route::post('/send', [WhatsAppController::class, 'sendMessage'])->name('send');
    Route::post('/broadcast', [WhatsAppController::class, 'broadcast'])->name('broadcast');
    Route::post('/restart', [WhatsAppController::class, 'restart'])->name('restart');
    Route::post('/delete-session', [WhatsAppController::class, 'deleteSession'])->name('delete-session');
    Route::get('/qr', [WhatsAppController::class, 'getQr'])->name('qr');
    Route::get('/test-connection', [WhatsAppController::class, 'testConnection'])->name('test-connection');
});

// API routes untuk integrasi internal
Route::middleware(['api'])->prefix('api')->group(function () {
    Route::get('/whatsapp/status', [WhatsAppController::class, 'testConnection']);
    Route::post('/whatsapp/send', [WhatsAppController::class, 'sendMessage']);
});