<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\MasterEreg;
use Illuminate\Support\Facades\Log;

class OCRScannerController extends Controller
{
    private $nodeScannerUrl;

    public function __construct()
    {
        $this->nodeScannerUrl = env('NODE_SCANNER_URL', 'http://103.23.198.160:3009');
        
        Log::info('OCR Scanner initialized', [
            'scanner_url' => $this->nodeScannerUrl
        ]);
    }

    public function triggerScan(Request $request)
    {
        try {
            $userId = auth()->id();
            $registration = MasterEreg::where('user_id', $userId)->first();

            if (!$registration) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pendaftaran tidak ditemukan'
                ], 404);
            }

            // PERUBAHAN: Hanya ambil file KK dan KTP
            $filesToScan = $this->getFilesToScan($registration);
            
            if (empty($filesToScan)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada berkas KK atau KTP yang perlu di-scan. Pastikan Anda sudah mengupload KK dan KTP.'
                ], 400);
            }

            Log::info('Starting OCR scan for user: ' . $userId, [
                'files_to_scan' => array_keys($filesToScan),
                'jalur' => $registration->jalur_pendaftaran,
                'total_files' => count($filesToScan),
                'scanner_url' => $this->nodeScannerUrl
            ]);

            $scanResults = [];
            $allSuccessful = true;
            $hasValidFiles = false;

            foreach ($filesToScan as $fileType => $filePath) {
                Log::info('Scanning file: ' . $fileType, ['path' => $filePath]);
                
                $scanResult = $this->scanSingleFile($fileType, $filePath, $registration->jalur_pendaftaran);
                
                $scanResults[$fileType] = $scanResult;
                
                if (!$scanResult['success']) {
                    $allSuccessful = false;
                    Log::error('Scan failed for file: ' . $fileType, $scanResult);
                } else {
                    if ($scanResult['is_valid'] ?? false) {
                        $hasValidFiles = true;
                    }
                    Log::info('Scan successful for file: ' . $fileType, [
                        'confidence' => $scanResult['confidence'] ?? 0,
                        'is_valid' => $scanResult['is_valid'] ?? false
                    ]);
                }
            }

            // Determine scan status
            $scanStatus = 'completed';
            if (!$allSuccessful) {
                $scanStatus = $hasValidFiles ? 'partial_failed' : 'failed';
            }

            // Update scan status in database
            $registration->update([
                'scan_results' => $scanResults,
                'scan_status' => $scanStatus,
                'last_scan_at' => now()
            ]);

            return response()->json([
                'success' => $allSuccessful,
                'message' => $allSuccessful ? 
                    'KK dan KTP berhasil di-scan dan valid!' : 
                    ($hasValidFiles ? 
                        'Beberapa berkas perlu perbaikan' : 
                        'Scan gagal untuk semua berkas'),
                'scan_results' => $scanResults,
                'scan_status' => $scanStatus,
                'jalur_pendaftaran' => $registration->jalur_pendaftaran,
                'scan_timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('OCR Scan Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'scanner_url' => $this->nodeScannerUrl
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses scan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * PERUBAHAN: Get files to scan - HANYA KK dan KTP
     */
    private function getFilesToScan($registration)
    {
        $files = [];
        
        // PERUBAHAN: Hanya scan KK dan KTP, abaikan file lainnya
        $requiredFiles = ['file_kk', 'file_ktp'];

        foreach ($requiredFiles as $fileType) {
            if ($this->fileExists($registration, $fileType)) {
                $files[$fileType] = $registration->$fileType;
            }
        }

        Log::info('Files to scan found (KK & KTP only):', [
            'total' => count($files),
            'files' => array_keys($files),
            'jalur' => $registration->jalur_pendaftaran
        ]);

        return $files;
    }

    /**
     * Check if file exists and uploaded
     */
    private function fileExists($registration, $fileType)
    {
        $exists = $registration->$fileType && Storage::disk('public')->exists($registration->$fileType);
        
        if (!$exists) {
            Log::warning('File not found or not uploaded: ' . $fileType, [
                'file_path' => $registration->$fileType,
                'exists' => $registration->$fileType ? Storage::disk('public')->exists($registration->$fileType) : false
            ]);
        }
        
        return $exists;
    }

    /**
     * TIDAK DIGUNAKAN LAGI - Kept for backward compatibility
     */
    private function getSpecificFilesByJalur($jalur)
    {
        // Return empty array karena kita hanya scan KK dan KTP
        return [];
    }

    /**
     * Test connection to scanner server
     */
    private function testScannerConnection()
    {
        try {
            Log::info('Testing scanner connection', [
                'url' => $this->nodeScannerUrl . '/api/health'
            ]);

            $response = Http::timeout(10)
                ->get($this->nodeScannerUrl . '/api/health');

            if ($response->successful()) {
                Log::info('Scanner connection test successful', [
                    'status' => $response->json()
                ]);
                return true;
            }

            Log::warning('Scanner connection test failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('Scanner connection test error', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Scan single file to Node.js scanner
     */
    private function scanSingleFile($fileType, $filePath, $jalurPendaftaran)
    {
        try {
            // Validate file exists
            if (!Storage::disk('public')->exists($filePath)) {
                Log::error('File not found in storage: ' . $filePath);
                return [
                    'success' => false,
                    'file_type' => $fileType,
                    'is_valid' => false,
                    'error' => 'File tidak ditemukan di storage: ' . $filePath,
                    'scan_timestamp' => now()->toISOString()
                ];
            }

            $fileContent = Storage::disk('public')->get($filePath);
            $fileName = basename($filePath);
            $fileSize = Storage::disk('public')->size($filePath);
            $fileMimeType = Storage::disk('public')->mimeType($filePath);

            Log::info('Preparing to scan file', [
                'file_type' => $fileType,
                'file_name' => $fileName,
                'file_path' => $filePath,
                'file_size' => $fileSize,
                'file_mime_type' => $fileMimeType,
                'jalur' => $jalurPendaftaran,
                'scanner_url' => $this->nodeScannerUrl
            ]);

            // Validasi file content
            if (empty($fileContent)) {
                Log::error('File content is empty: ' . $filePath);
                return [
                    'success' => false,
                    'file_type' => $fileType,
                    'is_valid' => false,
                    'error' => 'File content kosong',
                    'scan_timestamp' => now()->toISOString()
                ];
            }

            $payload = [
                'file_type' => $fileType,
                'file_name' => $fileName,
                'jalur_pendaftaran' => $jalurPendaftaran,
                'file_content' => base64_encode($fileContent),
                'file_size' => $fileSize,
                'file_mime_type' => $fileMimeType
            ];

            $scanUrl = $this->nodeScannerUrl . '/api/scan';

            Log::info('Sending request to Node.js scanner', [
                'url' => $scanUrl,
                'file_type' => $fileType,
                'payload_size' => strlen(json_encode($payload)),
                'base64_content_length' => strlen(base64_encode($fileContent))
            ]);

            $response = Http::timeout(180)
                ->retry(3, 2000, function ($exception, $request) {
                    Log::warning('Retrying request to Node.js scanner', [
                        'exception' => $exception->getMessage()
                    ]);
                    return $exception instanceof \Illuminate\Http\Client\ConnectionException;
                })
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'User-Agent' => 'Laravel-OCR-Scanner/1.0',
                    'X-Request-ID' => uniqid('scan_', true)
                ])
                ->post($scanUrl, $payload);

            Log::info('Node.js scanner response received', [
                'status' => $response->status(),
                'success' => $response->successful(),
                'file_type' => $fileType,
                'response_size' => strlen($response->body())
            ]);

            if ($response->successful()) {
                $result = $response->json();
                
                Log::info('Scan successful', [
                    'file_type' => $fileType,
                    'confidence' => $result['confidence'] ?? 0,
                    'is_valid' => $result['is_valid'] ?? false,
                    'message' => $result['message'] ?? 'No message'
                ]);
                
                return [
                    'success' => true,
                    'file_type' => $fileType,
                    'is_valid' => $result['is_valid'] ?? false,
                    'confidence' => $result['confidence'] ?? 0,
                    'extracted_text' => $result['extracted_text'] ?? '',
                    'validation_details' => $result['validation_details'] ?? [],
                    'scan_data' => $result['data'] ?? [],
                    'message' => $result['message'] ?? ($result['is_valid'] ? 'File valid' : 'File tidak valid'),
                    'scan_timestamp' => now()->toISOString()
                ];
            } else {
                $errorBody = $response->body();
                $errorStatus = $response->status();
                
                Log::error('Node Scanner API Error', [
                    'status' => $errorStatus,
                    'error' => $errorBody,
                    'file_type' => $fileType,
                    'file_path' => $filePath,
                    'node_url' => $this->nodeScannerUrl
                ]);
                
                $errorMessage = 'Scanner API returned error: ' . $errorStatus;
                if (strlen($errorBody) < 1000) {
                    $errorMessage .= ' - ' . $errorBody;
                }
                
                return [
                    'success' => false,
                    'file_type' => $fileType,
                    'is_valid' => false,
                    'error' => $errorMessage,
                    'error_details' => $errorBody,
                    'message' => 'Gagal memproses file',
                    'scan_timestamp' => now()->toISOString()
                ];
            }

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Node.js Connection Error', [
                'file_type' => $fileType,
                'error' => $e->getMessage(),
                'node_url' => $this->nodeScannerUrl
            ]);
            
            return [
                'success' => false,
                'file_type' => $fileType,
                'is_valid' => false,
                'error' => 'Tidak dapat terhubung ke server scanner: ' . $e->getMessage(),
                'message' => 'Server scanner tidak dapat dihubungi. Pastikan server scanner berjalan di ' . $this->nodeScannerUrl,
                'scan_timestamp' => now()->toISOString()
            ];
        } catch (\Exception $e) {
            Log::error('Scan Single File Error', [
                'file_type' => $fileType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file_path' => $filePath ?? 'unknown'
            ]);
            
            return [
                'success' => false,
                'file_type' => $fileType,
                'is_valid' => false,
                'error' => $e->getMessage(),
                'message' => 'Terjadi kesalahan saat scan file',
                'scan_timestamp' => now()->toISOString()
            ];
        }
    }

    /**
     * Get scan status and results
     */
    public function getScanStatus(Request $request)
    {
        $userId = auth()->id();
        $registration = MasterEreg::where('user_id', $userId)->first();

        if (!$registration) {
            return response()->json([
                'success' => false,
                'message' => 'Data pendaftaran tidak ditemukan'
            ], 404);
        }

        $scanResults = $registration->scan_results ?? [];

        return response()->json([
            'success' => true,
            'scan_status' => $registration->scan_status ?? 'not_scanned',
            'scan_results' => $scanResults,
            'jalur_pendaftaran' => $registration->jalur_pendaftaran,
            'scan_timestamp' => $registration->last_scan_at?->toISOString(),
            'last_updated' => $registration->updated_at->toISOString(),
            'scanner_url' => $this->nodeScannerUrl
        ]);
    }

    /**
     * Test scanner connection endpoint
     */
    public function testConnection(Request $request)
    {
        try {
            Log::info('Testing scanner connection from endpoint');

            $isConnected = $this->testScannerConnection();

            if ($isConnected) {
                return response()->json([
                    'success' => true,
                    'message' => 'Koneksi ke scanner server berhasil',
                    'scanner_url' => $this->nodeScannerUrl,
                    'timestamp' => now()->toISOString()
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal terhubung ke scanner server',
                'scanner_url' => $this->nodeScannerUrl,
                'timestamp' => now()->toISOString()
            ], 503);

        } catch (\Exception $e) {
            Log::error('Test connection error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error testing connection',
                'error' => $e->getMessage(),
                'scanner_url' => $this->nodeScannerUrl
            ], 500);
        }
    }

    /**
     * Scan single file by type - HANYA UNTUK KK dan KTP
     */
    public function scanSingleFileByType(Request $request, $fileType)
    {
        try {
            // PERUBAHAN: Validasi hanya KK dan KTP yang boleh di-scan
            $allowedTypes = ['file_kk', 'file_ktp'];
            
            if (!in_array($fileType, $allowedTypes)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya file KK dan KTP yang dapat di-scan. File type: ' . $fileType . ' tidak diizinkan.'
                ], 400);
            }

            $userId = auth()->id();
            $registration = MasterEreg::where('user_id', $userId)->first();

            if (!$registration) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pendaftaran tidak ditemukan'
                ], 404);
            }

            // Check if file exists
            if (!$this->fileExists($registration, $fileType)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File ' . $fileType . ' tidak ditemukan atau belum diupload'
                ], 404);
            }

            Log::info('Scanning single file by type', [
                'user_id' => $userId,
                'file_type' => $fileType,
                'jalur' => $registration->jalur_pendaftaran
            ]);

            $scanResult = $this->scanSingleFile($fileType, $registration->$fileType, $registration->jalur_pendaftaran);

            // Update scan results
            $scanResults = $registration->scan_results ?? [];
            $scanResults[$fileType] = $scanResult;
            
            // Update overall status - hanya cek KK dan KTP
            $allValid = true;
            foreach (['file_kk', 'file_ktp'] as $requiredFile) {
                if (isset($scanResults[$requiredFile])) {
                    if (!$scanResults[$requiredFile]['success'] || !$scanResults[$requiredFile]['is_valid']) {
                        $allValid = false;
                        break;
                    }
                }
            }
            
            $scanStatus = $allValid ? 'completed' : 'partial_failed';

            $registration->update([
                'scan_results' =>$scanResults,
                'scan_status' => $scanStatus,
                'last_scan_at' => now()
            ]);

            return response()->json([
                'success' => $scanResult['success'],
                'message' => $scanResult['success'] ? 
                    ($scanResult['is_valid'] ? 'File valid' : 'File perlu perbaikan') : 
                    'Scan file gagal',
                'scan_result' => $scanResult,
                'file_type' => $fileType
            ]);

        } catch (\Exception $e) {
            Log::error('Single File Scan Error: ' . $e->getMessage(), [
                'file_type' => $fileType,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat scan file',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * PERUBAHAN: Get validation rules - HANYA KK dan KTP
     */
    private function getValidationRules($jalurPendaftaran = null)
    {
        // PERUBAHAN: Hanya return rules untuk KK dan KTP
        return [
            'file_ktp' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'file_kk' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ];
    }

    /**
     * PERUBAHAN: Scan temporary files - HANYA KK dan KTP
     */
    public function scanTempFiles(Request $request)
    {
        try {
            $userId = auth()->id();
            $registration = MasterEreg::where('user_id', $userId)->first();

            if (!$registration) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pendaftaran tidak ditemukan'
                ], 404);
            }

            $jalurPendaftaran = $registration->jalur_pendaftaran;

            // PERUBAHAN: Validasi hanya KK dan KTP
            $rules = $this->getValidationRules($jalurPendaftaran);
            $request->validate($rules);

            $scanResults = [];
            $allValid = true;
            $hasFiles = false;

            Log::info('Starting temp scan for user: ' . $userId, [
                'jalur' => $jalurPendaftaran,
                'files_to_validate' => array_keys($rules),
                'scanner_url' => $this->nodeScannerUrl
            ]);

            // Scan hanya KK dan KTP
            foreach ($rules as $fileType => $rule) {
                if ($request->hasFile($fileType)) {
                    $hasFiles = true;
                    $file = $request->file($fileType);
                    
                    // Simpan file temporary
                    $filename = time() . '_' . $fileType . '.' . $file->getClientOriginalExtension();
                    $tempPath = $file->storeAs('temp_scans', $filename, 'public');
                    
                    Log::info('Scanning temp file:', [
                        'file_type' => $fileType,
                        'temp_path' => $tempPath,
                        'file_size' => $file->getSize(),
                        'original_name' => $file->getClientOriginalName()
                    ]);

                    // Scan file
                    $scanResult = $this->scanSingleFile($fileType, $tempPath, $jalurPendaftaran);
                    
                    $scanResults[$fileType] = $scanResult;
                    
                    if (!$scanResult['is_valid']) {
                        $allValid = false;
                        Log::warning('File validation failed:', [
                            'file_type' => $fileType,
                            'result' => $scanResult
                        ]);
                    } else {
                        Log::info('File validation passed:', [
                            'file_type' => $fileType,
                            'confidence' => $scanResult['confidence'] ?? 0
                        ]);
                    }
                    
                    // Hapus file temporary
                    Storage::disk('public')->delete($tempPath);
                    Log::info('Temp file deleted:', ['file_type' => $fileType]);
                } else {
                    Log::warning('Required file not uploaded:', ['file_type' => $fileType]);
                }
            }

            if (!$hasFiles) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada file KK atau KTP yang diupload untuk divalidasi'
                ], 400);
            }

            $scanStatus = $allValid ? 'completed' : 'partial_failed';

            Log::info('Temp scan completed:', [
                'user_id' => $userId,
                'scan_status' => $scanStatus,
                'all_valid' => $allValid,
                'total_files_scanned' => count($scanResults)
            ]);

            return response()->json([
                'success' => true,
                'message' => $allValid ? 
                    'KK dan KTP valid dan siap diupload!' : 
                    'KK atau KTP perlu perbaikan sebelum diupload',
                'scan_status' => $scanStatus,
                'scan_results' => $scanResults,
                'jalur_pendaftaran' => $jalurPendaftaran,
                'scan_timestamp' => now()->toISOString(),
                'scanned_files' => array_keys($scanResults)
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Temp scan validation failed:', [
                'errors' => $e->errors(),
                'jalur' => $jalurPendaftaran ?? 'unknown'
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validasi file gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Temp Scan Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => $userId ?? 'unknown'
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses validasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * PERUBAHAN: Get file requirements - HANYA KK dan KTP
     */
    public function getFileRequirements(Request $request)
    {
        try {
            $userId = auth()->id();
            $registration = MasterEreg::where('user_id', $userId)->first();

            if (!$registration) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pendaftaran tidak ditemukan'
                ], 404);
            }

            $jalurPendaftaran = $registration->jalur_pendaftaran;
            $rules = $this->getValidationRules($jalurPendaftaran);

            // Format requirements untuk frontend - HANYA KK dan KTP
            $requirements = [];
            foreach ($rules as $fileType => $rule) {
                $requirements[] = [
                    'file_type' => $fileType,
                    'display_name' => $this->getFileDisplayName($fileType),
                    'required' => str_contains($rule, 'required'),
                    'allowed_formats' => $this->extractAllowedFormats($rule),
                    'max_size' => $this->extractMaxSize($rule),
                    'description' => $this->getFileDescription($fileType, $jalurPendaftaran)
                ];
            }

            return response()->json([
                'success' => true,
                'jalur_pendaftaran' => $jalurPendaftaran,
                'requirements' => $requirements,
                'total_files_required' => count($requirements),
                'scanner_url' => $this->nodeScannerUrl,
                'note' => 'Saat ini hanya KK dan KTP yang di-scan'
            ]);

        } catch (\Exception $e) {
            Log::error('Get requirements error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil requirements file'
            ], 500);
        }
    }

    /**
     * Helper methods untuk requirements
     */
    private function getFileDisplayName($fileType)
    {
        $names = [
            'file_ktp' => 'KTP',
            'file_kk' => 'Kartu Keluarga (KK)',
        ];

        return $names[$fileType] ?? str_replace('file_', '', $fileType);
    }

    private function extractAllowedFormats($rule)
    {
        if (preg_match('/mimes:([^|]+)/', $rule, $matches)) {
            return explode(',', $matches[1]);
        }
        return ['pdf', 'jpg', 'jpeg', 'png'];
    }

    private function extractMaxSize($rule)
    {
        if (preg_match('/max:(\d+)/', $rule, $matches)) {
            return (int)$matches[1] / 1024;
        }
        return 2048;
    }

    private function getFileDescription($fileType, $jalurPendaftaran)
    {
        $descriptions = [
            'file_ktp' => 'Kartu Tanda Penduduk yang masih berlaku',
            'file_kk' => 'Kartu Keluarga terbaru',
        ];

        return $descriptions[$fileType] ?? 'Dokumen persyaratan pendaftaran';
    }
}