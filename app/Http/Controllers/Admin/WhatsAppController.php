<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Validator;

class WhatsAppController extends Controller
{
    protected $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Tampilkan status WhatsApp
     */
    public function status()
    {
        $status = $this->whatsappService->checkStatus();
        $qrCode = $this->whatsappService->getQrCode();
        $isReady = $this->whatsappService->isReady();

        return view('admin.whatsapp.status', compact('status', 'qrCode', 'isReady'));
    }

    /**
     * Kirim pesan WhatsApp
     */
    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|min:10|max:15',
            'message' => 'required|string|min:1|max:1000',
            'template' => 'sometimes|string',
            'use_queue' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $useQueue = $request->get('use_queue', true);
            
            if ($request->has('template')) {
                $result = $this->whatsappService->sendTemplateMessage(
                    $request->phone,
                    $request->template,
                    $request->except(['phone', 'template', 'use_queue']),
                    $useQueue
                );
            } else {
                if ($useQueue) {
                    $result = $this->whatsappService->sendMessageViaQueue(
                        $request->phone,
                        $request->message,
                        $request->except(['phone', 'message', 'use_queue'])
                    );
                } else {
                    $result = $this->whatsappService->sendMessage(
                        $request->phone,
                        $request->message,
                        $request->except(['phone', 'message', 'use_queue'])
                    );
                }
            }

            if ($result) {
                return back()->with('success', 'Pesan berhasil dikirim ke ' . $request->phone);
            } else {
                return back()->with('error', 'Gagal mengirim pesan ke ' . $request->phone);
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Kirim broadcast ke banyak nomor
     */
    public function broadcast(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phones' => 'required|string',
            'message' => 'required|string|min:1|max:1000',
            'template' => 'sometimes|string'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Parse phones dari string ke array
            $phones = array_map('trim', explode(',', $request->phones));
            
            $successCount = 0;
            $failedCount = 0;
            
            foreach ($phones as $phone) {
                if (empty(trim($phone))) continue;
                
                try {
                    if ($request->has('template')) {
                        $result = $this->whatsappService->sendTemplateMessage(
                            trim($phone),
                            $request->template,
                            $request->except(['phones', 'template']),
                            true
                        );
                    } else {
                        $result = $this->whatsappService->sendMessageViaQueue(
                            trim($phone),
                            $request->message,
                            $request->except(['phones', 'message'])
                        );
                    }
                    
                    if ($result) {
                        $successCount++;
                    } else {
                        $failedCount++;
                    }
                } catch (\Exception $e) {
                    $failedCount++;
                }
            }

            return back()->with('success', "Broadcast selesai. Berhasil: {$successCount}, Gagal: {$failedCount}");

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Restart WhatsApp client
     */
    public function restart(Request $request)
    {
        try {
            $result = $this->whatsappService->restartClient();
            
            if ($result) {
                return back()->with('success', 'WhatsApp client berhasil direstart');
            } else {
                return back()->with('error', 'Gagal restart WhatsApp client');
            }
            
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Delete WhatsApp session
     */
    public function deleteSession(Request $request)
    {
        try {
            $result = $this->whatsappService->deleteSession();
            
            if ($result) {
                return back()->with('success', 'Session WhatsApp berhasil dihapus');
            } else {
                return back()->with('error', 'Gagal menghapus session WhatsApp');
            }
            
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Get QR code
     */
    public function getQr()
{
    try {
        // Ambil data dari WhatsAppService
        $qrData = $this->whatsappService->getQrCode();
        $status = $this->whatsappService->checkStatus();
        $isReady = $this->whatsappService->isReady();
        
        // Jika $qrData adalah array/object JSON
        if (is_array($qrData) || is_object($qrData)) {
            // Cek jika ada data URL QR
            if (isset($qrData['dataUrl']) && $qrData['dataUrl']) {
                $qr = $qrData['dataUrl'];
                $message = null;
                $success = true;
            } 
            // Cek jika ada struktur "data" -> "dataUrl"
            elseif (isset($qrData['data']['dataUrl']) && $qrData['data']['dataUrl']) {
                $qr = $qrData['data']['dataUrl'];
                $message = $qrData['data']['message'] ?? null;
                $success = $qrData['success'] ?? true;
            }
            // Jika tidak ada QR
            else {
                $qr = null;
                $message = isset($qrData['data']['message']) ? $qrData['data']['message'] : 
                          (isset($qrData['message']) ? $qrData['message'] : 'Tidak ada QR code tersedia');
                $success = false;
            }
        } 
        // Jika $qrData langsung string (URL QR)
        elseif (is_string($qrData) && !empty($qrData)) {
            $qr = $qrData;
            $message = null;
            $success = true;
        }
        // Jika kosong/null
        else {
            $qr = null;
            $message = 'Tidak ada QR code tersedia';
            $success = false;
        }
        
        // Kirim data ke view
        return view('admin.whatsapp.qr', [
            'qr' => $qr,
            'message' => $message,
            'status' => $status,
            'isReady' => $isReady,
            'success' => $success,
            'error' => null
        ]);
        
    } catch (\Exception $e) {
        return view('admin.whatsapp.qr', [
            'qr' => null,
            'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            'status' => null,
            'isReady' => false,
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

    /**
     * Test koneksi WhatsApp
     */
    public function testConnection(Request $request)
    {
        try {
            $isReady = $this->whatsappService->isReady();
            $status = $this->whatsappService->checkStatus();
            
            return response()->json([
                'success' => true,
                'is_ready' => $isReady,
                'status' => $status
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Tampilkan form test WhatsApp
     */
    public function showTestForm()
    {
        return view('admin.whatsapp.test');
    }
}