<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// --- MODEL IMPORTS ---
use App\Models\User;
use App\Models\Biodata;
use App\Models\MasterEreg;
use App\Models\ProgramStudi;
use App\Models\JalurPendaftaran;

class RegisterStepController extends Controller
{
    /**
     * Menampilkan Halaman Wizard Pendaftaran
     */
    public function index()
    {
        $user = Auth::user();
        $nama_lengkap_default = optional($user->biodata)->nama_lengkap ?? $user->name;

        // 1. Ambil atau Buat Data Registrasi
        $registration = MasterEreg::firstOrCreate(
            ['user_id' => $user->id],
            [
                'trx_id'         => Str::random(10),
                'step_completed' => 0,
                'status'         => 'in_progress', 
                'nama'           => $nama_lengkap_default,
                'gmail'          => $user->email,
                'phone'          => $user->phone ?? $user->no_hp,
                'scan_results'   => [],
                'scan_status'    => 'not_scanned'
            ]
        );
        
        $remainingSeconds = 0;
        
        // ==================================================================
        // LOGIKA RESET OTOMATIS (SIAPA CEPAT DIA DAPAT)
        // ==================================================================
        if ($registration->status != 'completed') {
            
            // Hitung Deadline: Waktu Dibuat + 3 Hari
            $deadline = Carbon::parse($registration->created_at)->addDays(3);
            
            // Cek apakah WAKTU SEKARANG sudah melewati DEADLINE?
            if (Carbon::now()->greaterThan($deadline)) {
                
                // Cek: Hanya reset jika user belum finalisasi (masih step 1/2/3)
                if ($registration->step_completed < 4) { 
                    
                    // A. AMBIL DATA JURUSAN LAMA (PENTING: Sebelum di-null-kan)
                    $oldJurusan1 = $registration->jurusan1;
                    $oldJurusan2 = $registration->jurusan2;

                    // B. RESET DATA PRODI & ALASAN (Hapus Pilihan)
                    $registration->forceFill([
                        'jurusan1' => null, 
                        'jurusan2' => null,
                        'alasan_jurusan_1' => null, 
                        'alasan_jurusan_2' => null,
                        'jalur_pendaftaran' => null, 
                        
                        // 'kode_promo' => null, // [MODIFIKASI] Kode Promo JANGAN dihapus (biar tetap awet)
                        
                        // Kembalikan user ke Step 1
                        'step_completed' => 1, 
                        
                        // [PENTING] Set ulang waktu mulai jadi SEKARANG (Reset Timer)
                        'created_at' => Carbon::now(), 
                    ])->save(); 

                    // C. KEMBALIKAN KUOTA / SYNC DAYA TAMPUNG
                    if ($oldJurusan1) {
                        ProgramStudi::withoutGlobalScopes()->where('kode_prodi', $oldJurusan1)->first()?->syncDayaTampung();
                    }
                    if ($oldJurusan2) {
                        ProgramStudi::withoutGlobalScopes()->where('kode_prodi', $oldJurusan2)->first()?->syncDayaTampung();
                    }

                    // Refresh data agar variabel di view terupdate
                    $registration->refresh();
                    
                    // Set sisa waktu jadi full 3 hari lagi (karena baru direset)
                    $remainingSeconds = 3 * 24 * 60 * 60; 
                    
                    session()->flash('error', 'Waktu pendaftaran sebelumnya telah habis. Pilihan Program Studi Anda telah direset. Silakan pilih kembali.');
                } 
            } else {
                // Jika waktu BELUM habis, hitung sisa detiknya
                $remainingSeconds = Carbon::now()->diffInSeconds($deadline, false);
            }
        }
        
        // Redirect jika sudah selesai
        if ($registration->status === 'completed') {
             return redirect()->route('mahasiswa.dashboard')->with('success', 'Pendaftaran telah selesai!');
        }

        // Data View
        $steps = ['Data Pribadi', 'Pilihan Jurusan', 'Upload Berkas', 'Validasi'];
        $icons = ['user', 'graduation-cap', 'file-upload', 'check-circle'];
        $jalurList = MasterEreg::getJalurPendaftaranList();
        $jurusanList = ProgramStudi::withoutGlobalScopes()->orderBy('urutan_tampil')->get();
        $namaJurusan1 = optional($registration->programStudi1)->nama_prodi ?? $registration->jurusan1 ?? '-';
        $namaJurusan2 = optional($registration->programStudi2)->nama_prodi ?? $registration->jurusan2 ?? 'Tidak Memilih';

        return view('mahasiswa.register.index', compact(
            'registration', 'steps', 'icons', 'jalurList', 'jurusanList', 
            'nama_lengkap_default', 'remainingSeconds', 'namaJurusan1', 'namaJurusan2'
        ));
    }

    /**
     * Proses Step 1: Data Diri Dasar
     */
    public function processStep1(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'status_pekerjaan' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        
        $currentEreg = $user->masterEreg()->first();
        $currentStepCompleted = optional($currentEreg)->step_completed ?? 0;

        // ======================================================================
        // LOGIKA NORMALISASI STATUS PEKERJAAN (SUPER SIMPLE: 0 & 1)
        // ======================================================================
        $rawStatus = strtolower(trim($request->status_pekerjaan));

        // Cek langsung: kalau isinya '1' atau 'bekerja', set ke 1. Selain itu pasti 0 (Tidak Bekerja).
        $statusPddikti = in_array($rawStatus, ['1', 'bekerja']) ? 1 : 0;

        MasterEreg::updateOrCreate(
            ['user_id' => $user->id],
            [
                'nama' => $request->nama, 
                'gmail' => $request->email,
                'phone' => $request->phone, 
                'status_pekerjaan' => $statusPddikti,
                'step_completed' => max(1, $currentStepCompleted),
                'status' => 'in_progress'
            ]
        );

        return response()->json(['success' => true, 'message' => 'Data pribadi tersimpan.', 'next_step' => 2]);
    }

    /**
     * Proses Step 2: Pilih Jalur & Jurusan
     */
    public function processStep2(Request $request)
    {
        // 1. Validasi Custom untuk jalur S2 (Manajemen)
        $ruleCekS2 = function ($attribute, $value, $fail) use ($request) {
            if (empty($value)) return; 
            if ($request->jalur_pendaftaran === 'Jalur Reguler') {
                $prodi = ProgramStudi::withoutGlobalScopes()->where('kode_prodi', $value)->first();
                if ($prodi && Str::contains(Str::upper($prodi->nama_prodi), 'S2')) {
                    $fail('Program Studi S2 tidak tersedia untuk Jalur Reguler.');
                }
            }
        };

        // 2. Validasi Custom untuk jalur pendaftaran RPL
        $ruleCekRPL = function ($attribute, $value, $fail) use ($request) {
            if (empty($value)) return; 
            if ($request->jalur_pendaftaran === 'Jalur RPL') {
                $prodi = ProgramStudi::withoutGlobalScopes()->where('kode_prodi', $value)->first();
                if ($prodi && !Str::contains(Str::lower($prodi->nama_prodi), 'teknik industri')) {
                    $fail('Pendaftaran Jalur RPL saat ini hanya tersedia untuk Program Studi Teknik Industri.');
                }
            }
        };

        // Validasi Farmasi sudah dihapus total di sini

        $request->validate([
            'jalur_pendaftaran' => ['required', 'string'], 
            'jurusan1'          => ['required', 'string', $ruleCekS2, $ruleCekRPL],
            'alasan_jurusan_1'  => 'required|string|min:5',
            'jurusan2'          => ['nullable', 'string', $ruleCekS2, $ruleCekRPL],
            'alasan_jurusan_2'  => 'nullable|required_with:jurusan2|string|min:5',
        ]);

        $registration = MasterEreg::where('user_id', auth()->id())->firstOrFail();
        
        // Simpan jurusan lama sebelum di-update untuk sinkronisasi kuota
        $oldJurusan1 = $registration->jurusan1;
        $oldJurusan2 = $registration->jurusan2;

        // Cek Jalur Single Jurusan
        $isSingleJurusan = in_array($request->jalur_pendaftaran, ['Jalur S2 Manajemen', 'Jalur RPL']);
        $jurusan2Input = $isSingleJurusan ? null : $request->jurusan2;
        $alasan2Input  = $isSingleJurusan ? null : $request->alasan_jurusan_2;

        // Simpan Data Baru
        $registration->update([
            'jalur_pendaftaran' => $request->jalur_pendaftaran,
            'jurusan1' => $request->jurusan1, 
            'jurusan2' => $jurusan2Input, 
            'alasan_jurusan_1' => $request->alasan_jurusan_1, 
            'alasan_jurusan_2' => $alasan2Input,
            'step_completed' => 2,
        ]);

        // SINKRONISASI KUOTA (PENTING)
        // 1. Sync Jurusan Lama (Agar kuotanya kembali kosong/bertambah)
        if ($oldJurusan1 && $oldJurusan1 !== $request->jurusan1) {
             ProgramStudi::withoutGlobalScopes()->where('kode_prodi', $oldJurusan1)->first()?->syncDayaTampung();
        }
        if ($oldJurusan2 && $oldJurusan2 !== $jurusan2Input) {
             ProgramStudi::withoutGlobalScopes()->where('kode_prodi', $oldJurusan2)->first()?->syncDayaTampung();
        }

        // 2. Sync Jurusan Baru (Agar terhitung terisi)
        if ($request->jurusan1) {
            ProgramStudi::withoutGlobalScopes()->where('kode_prodi', $request->jurusan1)->first()?->syncDayaTampung();
        }
        if ($jurusan2Input) {
            ProgramStudi::withoutGlobalScopes()->where('kode_prodi', $jurusan2Input)->first()?->syncDayaTampung();
        }
        
        return response()->json(['success' => true, 'message' => 'Pilihan jurusan tersimpan.', 'next_step' => 3]);
    }

    /**
     * Proses Step 3: Upload Berkas
     */
    public function processStep3(Request $request)
    {
        try {
            $registration = MasterEreg::where('user_id', auth()->id())->first();
            
            $rules = $this->getValidationRulesForFiles($registration, $request);
            $request->validate($rules);

            $uploadData = [];
            foreach ($rules as $field => $rule) {
                if ($request->hasFile($field)) {
                    if ($registration->{$field} && Storage::disk('public')->exists($registration->{$field})) {
                        Storage::disk('public')->delete($registration->{$field});
                    }
                    $uploadData[$field] = $request->file($field)->store('ereg_files', 'public');
                }
            }

            $updateData = array_merge($uploadData, [
                'step_completed' => 3,
                'status' => 'upload_completed', 
                'scan_status' => 'validated',   
                'scan_results' => [],
                
            ]);

            $registration->update($updateData);

            return response()->json(['success' => true, 'message' => 'Berkas berhasil diunggah.', 'next_step' => 4]);

        } catch (\Exception $e) {
            Log::error('Error Upload Step 3: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan berkas: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Proses Step 4: Finalisasi / Complete
     */
    public function completeRegistration(Request $request)
    {
        $registration = MasterEreg::where('user_id', auth()->id())->first();
        
        if ($registration->status === 'completed') {
            return response()->json(['success' => true, 'redirect_url' => route('mahasiswa.dashboard')]);
        }

        $registration->update([
            'step_completed' => 4,
            'status' => 'completed', 
            'scan_results' => [], 
            'scan_status' => 'validated',
        ]);
        
        if ($registration->jurusan1) ProgramStudi::withoutGlobalScopes()->where('kode_prodi', $registration->jurusan1)->first()?->syncDayaTampung();
        if ($registration->jurusan2) ProgramStudi::withoutGlobalScopes()->where('kode_prodi', $registration->jurusan2)->first()?->syncDayaTampung();

        return response()->json([
            'success' => true,
            'message' => 'Pendaftaran Selesai!',
            'redirect_url' => route('mahasiswa.dashboard')
        ]);
    }

    /**
     * Helper: Menentukan Validasi File Berdasarkan Jalur
     */
   private function getValidationRulesForFiles($registration, Request $request = null)
    {
        $jalur = $registration->jalur_pendaftaran;
        
        // Helper kecil untuk cek status file (wajib upload ulang atau tidak)
        $ruleType = function($field) use ($registration) {
            // Jika file sudah ada di database, boleh kosong (nullable). Jika belum, wajib (required).
            return !empty($registration->{$field}) ? 'nullable' : 'required';
        };

        // 1. Aturan Dasar (Dokumen Umum untuk semua jalur)
        $rules = [
            'file_kk'  => $ruleType('file_kk') . '|file|mimes:pdf,jpg,jpeg,png|max:5048',
            'file_ktp' => $ruleType('file_ktp') . '|file|mimes:pdf,jpg,jpeg,png|max:5048',
            'file_surat_keterangan_sehat' => $ruleType('file_surat_keterangan_sehat') . '|file|mimes:pdf,jpg,jpeg,png|max:5048',
        ];

        // 2. Logika Khusus Per Jalur
        switch ($jalur) {
            case 'Jalur Reguler':
                // Ambil pilihan dokumen dari frontend (default: file_ijazah)
                $jenisDokumen = $request ? $request->input('jenis_dokumen_reguler', 'file_ijazah') : 'file_ijazah';

                if ($jenisDokumen === 'file_skl') {
                    $rules['file_skl'] = $ruleType('file_skl') . '|file|mimes:pdf,jpg,jpeg,png|max:5048';
                } elseif ($jenisDokumen === 'file_rapor') {
                    $rules['file_rapor'] = $ruleType('file_rapor') . '|file|mimes:pdf,jpg,jpeg,png|max:5048';
                } else {
                    // Default tetap Ijazah
                    $rules['file_ijazah'] = $ruleType('file_ijazah') . '|file|mimes:pdf,jpg,jpeg,png|max:5048';
                }
                break;

            case 'Jalur Ekstensi':
                $rules['file_ijazah'] = $ruleType('file_ijazah') . '|file|mimes:pdf,jpg,jpeg,png|max:5048';
                $rules['file_transkrip_s1'] = $ruleType('file_transkrip_s1') . '|file|mimes:pdf,jpg,jpeg,png|max:5048';
                break;

            case 'Pindahan':
                $rules['file_surat_pindah'] = $ruleType('file_surat_pindah') . '|file|mimes:pdf,jpg,jpeg,png|max:5048';
                $rules['file_transkrip_s1'] = $ruleType('file_transkrip_s1') . '|file|mimes:pdf,jpg,jpeg,png|max:5048';
                $rules['file_ktm'] = $ruleType('file_ktm') . '|file|mimes:pdf,jpg,jpeg,png|max:5048';
                $rules['file_ijazah'] = $ruleType('file_ijazah') . '|file|mimes:pdf,jpg,jpeg,png|max:5048';
                break;

            case 'Jalur S2 Manajemen':
                $rules['file_ijazah_s1'] = $ruleType('file_ijazah_s1') . '|file|mimes:pdf,jpg,jpeg,png|max:5048';
                $rules['file_transkrip_s1'] = $ruleType('file_transkrip_s1') . '|file|mimes:pdf,jpg,jpeg,png|max:5048';
                break;

            case 'Jalur RPL':
                $rules['file_surat_tugas_kerja'] = $ruleType('file_surat_tugas_kerja') . '|file|mimes:pdf,jpg,jpeg,png|max:5048';
                $rules['file_transkrip_nilai_rpl'] = $ruleType('file_transkrip_nilai_rpl') . '|file|mimes:pdf,jpg,jpeg,png|max:5048';
                // $rules['file_surat_akademik_rpl'] = $ruleType('file_surat_akademik_rpl') . '|file|mimes:pdf,jpg,jpeg,png|max:5048';
                $rules['file_ijazah'] = $ruleType('file_ijazah') . '|file|mimes:pdf,jpg,jpeg,png|max:5048';
                break;
        }

        // 3. Logika Khusus Farmasi (Buta Warna)
        $nama1 = $registration->programStudi1->nama_prodi ?? ''; 
        $nama2 = $registration->programStudi2->nama_prodi ?? '';
        
        if (!$nama1 && !$nama2) {
             $prodiCodes = array_filter([$registration->jurusan1, $registration->jurusan2]);
             // Menggunakan 'implode' langsung dari query pluck agar efisien
             $prodiNames = ProgramStudi::withoutGlobalScopes()->whereIn('kode_prodi', $prodiCodes)->pluck('nama_prodi')->implode(' ');
        } else {
             $prodiNames = $nama1 . ' ' . $nama2;
        }
        
        if (Str::contains(Str::lower($prodiNames), 'farmasi')) {
            $rules['file_surat_buta_warna'] = $ruleType('file_surat_buta_warna') . '|file|mimes:pdf,jpg,jpeg,png|max:5048';
        }

        return $rules;
    }
}