<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProgramStudi; // Model baru untuk tabel program_studi
use App\Models\User;
use App\Models\MasterEreg;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProdiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status', 'all');

        // Ganti query dari JurusanPassingGrade ke ProgramStudi
        $query = ProgramStudi::query();

        // Filter berdasarkan search
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('kode_prodi', 'like', "%{$search}%")
                  ->orWhere('nama_prodi', 'like', "%{$search}%")
                  ->orWhere('fakultas', 'like', "%{$search}%");
            });
        }

        // Filter berdasarkan status - gunakan field yang sesuai
        if ($status === 'active') {
            $query->where('status_prodi', '1'); // 1 untuk aktif
        } elseif ($status === 'inactive') {
            $query->where('status_prodi', '0'); // 0 untuk tidak aktif
        }

        $prodi = $query->orderBy('urutan_tampil')->paginate(20);

        // Hitung jumlah pendaftar untuk setiap prodi
        foreach ($prodi as $item) {
            $item->jumlah_pendaftar = $this->getJumlahPendaftar($item->kode_prodi);
            $item->kuota_tersedia = max(0, $item->kuota - $item->jumlah_pendaftar);
            
            // Tambahkan field status dengan nilai yang mudah dibaca
            $item->status = $item->status_prodi == '1' ? 'active' : 'inactive';
        }

        return view('admin.prodi.index', compact('prodi', 'search', 'status'));
    }

    /**
     * Get jumlah pendaftar berdasarkan kode jurusan
     */
    private function getJumlahPendaftar($kodeJurusan)
    {
        return MasterEreg::where(function($query) use ($kodeJurusan) {
            $query->where('jurusan1', $kodeJurusan)
                  ->orWhere('jurusan2', $kodeJurusan);
        })
        ->where('status', 'completed')
        ->count();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.prodi.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'kode_prodi' => 'required|string|max:20|unique:program_studi,kode_prodi',
            'nama_prodi' => 'required|string|max:255',
            'jenjang' => 'required|string|in:D3,S1,S2,S3',
            'fakultas' => 'nullable|string|max:255',
            'passing_grade' => 'required|numeric|min:0|max:100',
            'kuota' => 'required|integer|min:0',
            'harga_cicilan' => 'required|numeric|min:0',
            'harga_semester' => 'required|numeric|min:0',
            'harga_tahun' => 'required|numeric|min:0',
            'daya_tampung' => 'nullable|integer|min:0',
            'deskripsi' => 'nullable|string',
            'akreditasi' => 'nullable|string|max:50',
            'status_prodi' => 'required|in:0,1',
            'urutan_tampil' => 'nullable|integer|min:0'
        ]);

        try {
            ProgramStudi::create([
                'kode_prodi' => $request->kode_prodi,
                'nama_prodi' => $request->nama_prodi,
                'jenjang' => $request->jenjang,
                'fakultas' => $request->fakultas,
                'passing_grade' => $request->passing_grade,
                'kuota' => $request->kuota,
                'harga_cicilan' => $request->harga_cicilan,
                'harga_semester' => $request->harga_semester,
                'harga_tahun' => $request->harga_tahun,
                'daya_tampung' => $request->daya_tampung ?? 0,
                'deskripsi' => $request->deskripsi,
                'akreditasi' => $request->akreditasi,
                'status_prodi' => $request->status_prodi,
                'urutan_tampil' => $request->urutan_tampil ?? 0
            ]);

            return redirect()->route('admin.prodi.index')
                ->with('success', 'Program studi berhasil ditambahkan!');
                
        } catch (\Exception $e) {
            Log::error('Error creating prodi: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal menambahkan program studi: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $prodi = ProgramStudi::findOrFail($id);
        $jumlah_pendaftar = $this->getJumlahPendaftar($prodi->kode_prodi);
        $kuota_tersedia = max(0, $prodi->kuota - $jumlah_pendaftar);
        
        return view('admin.prodi.show', compact('prodi', 'jumlah_pendaftar', 'kuota_tersedia'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $prodi = ProgramStudi::findOrFail($id);
        $jumlah_pendaftar = $this->getJumlahPendaftar($prodi->kode_prodi);
        $kuota_tersedia = max(0, $prodi->kuota - $jumlah_pendaftar);
        
        return view('admin.prodi.edit', compact('prodi', 'jumlah_pendaftar', 'kuota_tersedia'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $prodi = ProgramStudi::findOrFail($id);

        $request->validate([
            'kode_prodi' => 'required|string|max:20|unique:program_studi,kode_prodi,' . $id,
            'nama_prodi' => 'required|string|max:255',
            'jenjang' => 'required|string|in:D3,S1,S2,S3',
            'fakultas' => 'nullable|string|max:255',
            'passing_grade' => 'required|numeric|min:0|max:100',
            'kuota' => 'required|integer|min:0',
            'harga_cicilan' => 'required|numeric|min:0',
            'harga_semester' => 'required|numeric|min:0',
            'harga_tahun' => 'required|numeric|min:0',
            'daya_tampung' => 'nullable|integer|min:0',
            'deskripsi' => 'nullable|string',
            'akreditasi' => 'nullable|string|max:50',
            'status_prodi' => 'required|in:0,1',
            'urutan_tampil' => 'nullable|integer|min:0'
        ]);

        try {
            $prodi->update([
                'kode_prodi' => $request->kode_prodi,
                'nama_prodi' => $request->nama_prodi,
                'jenjang' => $request->jenjang,
                'fakultas' => $request->fakultas,
                'passing_grade' => $request->passing_grade,
                'kuota' => $request->kuota,
                'harga_cicilan' => $request->harga_cicilan,
                'harga_semester' => $request->harga_semester,
                'harga_tahun' => $request->harga_tahun,
                'daya_tampung' => $request->daya_tampung ?? 0,
                'deskripsi' => $request->deskripsi,
                'akreditasi' => $request->akreditasi,
                'status_prodi' => $request->status_prodi,
                'urutan_tampil' => $request->urutan_tampil ?? 0
            ]);

            return redirect()->route('admin.prodi.index')
                ->with('success', 'Program studi berhasil diperbarui!');
                
        } catch (\Exception $e) {
            Log::error('Error updating prodi: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal memperbarui program studi: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $prodi = ProgramStudi::findOrFail($id);
            $prodi->delete();

            return redirect()->route('admin.prodi.index')
                ->with('success', 'Program studi berhasil dihapus!');
                
        } catch (\Exception $e) {
            Log::error('Error deleting prodi: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal menghapus program studi: ' . $e->getMessage());
        }
    }

    /**
     * Update status program studi
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $prodi = ProgramStudi::findOrFail($id);
            
            $request->validate([
                'status_prodi' => 'required|in:0,1'
            ]);

            $prodi->update([
                'status_prodi' => $request->status_prodi
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status program studi berhasil diperbarui!',
                'data' => [
                    'id' => $prodi->id,
                    'status_prodi' => $prodi->status_prodi
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating prodi status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Kirim informasi kuota prodi ke users via WhatsApp
     */
    public function sendKuotaInfo(Request $request, $id): JsonResponse
    {
        try {
            $prodi = ProgramStudi::findOrFail($id);
            
            // Hitung jumlah pendaftar dan kuota tersedia
            $jumlah_pendaftar = $this->getJumlahPendaftar($prodi->kode_prodi);
            $kuota_tersedia = max(0, $prodi->kuota - $jumlah_pendaftar);
            
            // Validasi request
            $request->validate([
                'message_type' => 'required|in:custom,auto',
                'custom_message' => 'nullable|string|max:1000'
            ]);

            // Ambil users yang memiliki nomor handphone
            $users = User::whereNotNull('phone')
                        ->where('phone', '!=', '')
                        ->get();

            if ($users->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada users dengan nomor handphone yang terdaftar.'
                ], 400);
            }

            $messageType = $request->message_type;
            $customMessage = $request->custom_message;

            // Siapkan pesan dengan informasi kuota terbaru
            $message = $this->prepareKuotaMessage($prodi, $messageType, $customMessage, $jumlah_pendaftar, $kuota_tersedia);

            // Kirim pesan ke setiap user
            $results = $this->sendBulkMessages($users, $message);

            return response()->json([
                'success' => true,
                'message' => 'Pengiriman informasi kuota berhasil diproses.',
                'data' => [
                    'total_users' => $users->count(),
                    'success_count' => $results['success_count'],
                    'failed_count' => $results['failed_count'],
                    'failed_numbers' => $results['failed_numbers'],
                    'prodi' => $prodi->nama_prodi,
                    'message_preview' => substr($message, 0, 100) . '...'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error sending kuota info: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim informasi kuota: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Kirim informasi kuota ke user tertentu
     */
    public function sendKuotaToUser(Request $request, $prodiId, $userId): JsonResponse
    {
        try {
            $prodi = ProgramStudi::findOrFail($prodiId);
            $user = User::findOrFail($userId);

            if (empty($user->phone)) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak memiliki nomor handphone.'
                ], 400);
            }

            $request->validate([
                'message_type' => 'required|in:custom,auto',
                'custom_message' => 'nullable|string|max:1000'
            ]);

            // Hitung jumlah pendaftar dan kuota tersedia
            $jumlah_pendaftar = $this->getJumlahPendaftar($prodi->kode_prodi);
            $kuota_tersedia = max(0, $prodi->kuota - $jumlah_pendaftar);

            $messageType = $request->message_type;
            $customMessage = $request->custom_message;

            $message = $this->prepareKuotaMessage($prodi, $messageType, $customMessage, $jumlah_pendaftar, $kuota_tersedia);

            // Kirim pesan
            $result = $this->sendSingleMessage($user->phone, $message);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Informasi kuota berhasil dikirim ke ' . $user->name,
                    'data' => [
                        'user' => $user->name,
                        'phone' => $user->phone,
                        'prodi' => $prodi->nama_prodi,
                        'message_id' => $result['message_id']
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengirim pesan: ' . $result['error']
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Error sending kuota to user: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim informasi kuota: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Siapkan pesan kuota dengan informasi terbaru
     */
    private function prepareKuotaMessage(ProgramStudi $prodi, string $messageType, ?string $customMessage = null, int $jumlahPendaftar = 0, int $kuotaTersedia = 0): string
    {
        if ($messageType === 'custom' && !empty($customMessage)) {
            return $customMessage;
        }

        // Pesan otomatis dengan informasi terbaru
        $statusProdi = $prodi->status_prodi == '1' ? '✅ AKTIF' : '⏸️ NON-AKTIF';
        
        return "📢 *INFORMASI KUOTA PROGRAM STUDI*\n\n" .
               "🎓 *{$prodi->nama_prodi}* ({$prodi->jenjang})\n" .
               "🔢 *Kode:* {$prodi->kode_prodi}\n" .
               "🏛️ *Fakultas:* " . ($prodi->fakultas ?: 'Tidak tersedia') . "\n\n" .
               "📊 *Passing Grade:* {$prodi->passing_grade}%\n" .
               "👥 *Kuota Total:* " . number_format($prodi->kuota) . " mahasiswa\n" .
               "📈 *Sudah Mendaftar:* " . number_format($jumlahPendaftar) . " mahasiswa\n" .
               "✅ *Kuota Tersedia:* " . number_format($kuotaTersedia) . " mahasiswa\n\n" .
               "💰 *Biaya Pendidikan:*\n" .
               "   - Cicilan: Rp " . number_format($prodi->harga_cicilan, 0, ',', '.') . "\n" .
               "   - Semester: Rp " . number_format($prodi->harga_semester, 0, ',', '.') . "\n" .
               "   - Tahunan: Rp " . number_format($prodi->harga_tahun, 0, ',', '.') . "\n\n" .
               "🏅 *Akreditasi:* " . ($prodi->akreditasi ?: 'Belum terakreditasi') . "\n" .
               "📍 *Status:* {$statusProdi}\n\n" .
               "ℹ️ Informasi lebih lanjut:\n" .
               "🌐 Website: pmb.ubpkarawang.ac.id\n" .
               "📞 Contact: admin@ubpkarawang.ac.id\n\n" .
               "_Pesan ini dikirim otomatis oleh sistem PMB UBP Karawang_";
    }

    /**
     * Kirim pesan bulk ke multiple users
     */
    private function sendBulkMessages($users, string $message): array
    {
        $successCount = 0;
        $failedCount = 0;
        $failedNumbers = [];

        foreach ($users as $user) {
            try {
                $result = $this->sendSingleMessage($user->phone, $message);
                
                if ($result['success']) {
                    $successCount++;
                    
                    // Log keberhasilan
                    Log::info('WhatsApp message sent successfully', [
                        'user_id' => $user->id,
                        'phone' => $user->phone,
                        'message_id' => $result['message_id']
                    ]);
                } else {
                    $failedCount++;
                    $failedNumbers[] = [
                        'phone' => $user->phone,
                        'error' => $result['error']
                    ];
                    
                    Log::error('Failed to send WhatsApp message', [
                        'user_id' => $user->id,
                        'phone' => $user->phone,
                        'error' => $result['error']
                    ]);
                }

                // Delay untuk menghindari rate limit
                usleep(500000); // 0.5 detik

            } catch (\Exception $e) {
                $failedCount++;
                $failedNumbers[] = [
                    'phone' => $user->phone,
                    'error' => $e->getMessage()
                ];
                
                Log::error('Exception sending WhatsApp message', [
                    'user_id' => $user->id,
                    'phone' => $user->phone,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return [
            'success_count' => $successCount,
            'failed_count' => $failedCount,
            'failed_numbers' => $failedNumbers
        ];
    }

    /**
     * Kirim single message via WhatsApp API
     */
    private function sendSingleMessage(string $phone, string $message): array
    {
        try {
            $nodeServerUrl = config('app.node_server_url', 'http://localhost:3000');
            
            // Format nomor telepon (hapus karakter non-digit dan tambahkan 62 jika perlu)
            $formattedPhone = $this->formatPhoneNumber($phone);
            
            $response = Http::timeout(30)->post($nodeServerUrl . '/api/whatsapp/send-message', [
                'number' => $formattedPhone,
                'message' => $message
            ]);

            if ($response->successful()) {
                $result = $response->json();
                return [
                    'success' => true,
                    'message_id' => $result['data']['message_id'] ?? 'unknown'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'HTTP ' . $response->status() . ': ' . $response->body()
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Format nomor telepon untuk WhatsApp
     */
    private function formatPhoneNumber(string $phone): string
    {
        // Hapus semua karakter non-digit
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        
        // Jika diawali dengan 0, ganti dengan 62
        if (substr($cleanPhone, 0, 1) === '0') {
            $cleanPhone = '62' . substr($cleanPhone, 1);
        }
        
        // Jika tidak diawali dengan 62, tambahkan
        if (substr($cleanPhone, 0, 2) !== '62') {
            $cleanPhone = '62' . $cleanPhone;
        }
        
        return $cleanPhone;
    }

    /**
     * Get users list for select options
     */
    public function getUsersWithPhone(): JsonResponse
    {
        try {
            $users = User::whereNotNull('phone')
                        ->where('phone', '!=', '')
                        ->select('id', 'name', 'email', 'phone')
                        ->orderBy('name')
                        ->get();

            return response()->json([
                'success' => true,
                'data' => $users
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting users with phone: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data users: ' . $e->getMessage()
            ], 500);
        }
    }
}