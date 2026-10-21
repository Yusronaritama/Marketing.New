<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Biodata;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BiodataController extends Controller
{
    /**
     * Menampilkan form biodata
     */
    public function create()
    {
        // ===============================================
        // CATATAN: DEBUGGING LOGIN OTOMATIS (Hapus di Production!)
        // ===============================================
        if (!Auth::check()) {
            $testUser = User::find(6);
            if ($testUser) {
                // Auth::login($testUser); // Hapus atau Komen baris ini di Production
            }
        }

        $biodata = Auth::user()->biodata;
        return view('mahasiswa.biodata.create', compact('biodata'));
    }

    /**
     * Menyimpan biodata (dan set status_bio 'completed')
     */
    public function store(Request $request)
    {
        // dd($request->all());
        Log::info('=== BIODATA STORE START ===');
        Log::info('Request Data:', $request->all());
        Log::info('User ID:', ['user_id' => Auth::id()]);

        $user = Auth::user();

        $validated = $request->validate([
            'nik' => [
                'required', 
                'numeric', 
                'digits:16', 
                // Abaikan NIK user saat ini dalam validasi unik
                Rule::unique('biodatas')->ignore($user->id, 'user_id')
            ],
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'tempat_lahir' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'nama_ibu' => 'required|string|max:255',
            'nama_ayah' => 'required|string|max:255',
            'alamat' => 'required|string|max:1000',
            'tahun_lulus' => 'required|numeric|digits:4', 
            
            // PERBAIKAN: Menggunakan underscore untuk konsisten dengan rule IN
            'daerah_asal' => 'required|string|in:luar_karawang,dalam_karawang',
            'kota_kabupaten_asal' => 'required|string',
            'provinsi_asal' => 'required|string',
            
            'jenjang_pendidikan' => 'required|string|in:pkbm,smk,sma,d3,s1,s2',
            'asal_sekolah' => 'required|string|max:255',
            'jurusan_sekolah' => 'required|string|max:255',
            'agama' => 'required|string|in:Islam,Kristen,Katolik,Hindu,Buddha,Konghucu',
            'tempat_tinggal' => 'required|string|max:1000',
        ]);

        Log::info('Validated Data:', $validated);

        // PERBAIKAN: Mengubah spasi di data input menjadi underscore sebelum disimpan
        $validated['daerah_asal'] = str_replace(' ', '_', strtolower($validated['daerah_asal']));


        try {
            DB::beginTransaction();
            
            $existingBiodata = Biodata::where('user_id', $user->id)->first();
            
            $dataToSave = array_merge($validated, [
                'user_id' => $user->id,
                'is_verified' => false,
                'status_bio' => 'completed', // Finalisasi biodata
            ]);

            Log::info('Data to Save:', $dataToSave);

            if ($existingBiodata) {
                $existingBiodata->update($dataToSave);
                $biodata = $existingBiodata;
                Log::info('Updated Existing Biodata');
            } else {
                $biodata = Biodata::create($dataToSave);
                Log::info('Created New Biodata');
            }

            $biodata->refresh();
            Log::info('Biodata After Save:', $biodata->toArray());

            DB::commit();

            Log::info('=== BIODATA STORE SUCCESS: Redirecting to Dashboard ===');

            $message = $existingBiodata ? 'Biodata berhasil diperbarui dan diselesaikan!' : 'Biodata berhasil disimpan dan diselesaikan!';
            
            // REDIRECT SUDAH BENAR: Ke halaman tujuan akhir (Dashboard), bukan ke halaman yang sama
            return redirect()->route('mahasiswa.dashboard')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Biodata Store Error: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menyimpan biodata: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Menyimpan sebagai draft (status_bio tetap 'draft')
     */
    public function saveDraft(Request $request)
    {
        Log::info('=== BIODATA DRAFT START ===');
        Log::info('Draft Request Data:', $request->all());
        
        // dd($request->all()); // <-- PERBAIKAN: Hapus/Komen baris dd() untuk mencegah looping/hang
        
        $user = Auth::user();

        // Validasi Draft: Sebagian besar kolom diizinkan nullable
        $validated = $request->validate([
            'nik' => [
                'nullable', 
                'numeric', 
                'digits:16',
                Rule::unique('biodatas')->ignore($user->id, 'user_id')
            ],
            // Mengubah semua rules wajib ('required') menjadi 'nullable'
            'nama_lengkap' => 'nullable|string|max:255',
            'jenis_kelamin' => 'nullable|in:Laki-laki,Perempuan',
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'nama_ibu' => 'nullable|string|max:255',
            'nama_ayah' => 'nullable|string|max:255',
            'alamat' => 'nullable|string|max:1000',
            'tahun_lulus' => 'nullable|numeric|digits:4', 
            // PERBAIKAN: Menggunakan underscore untuk konsisten dengan rule IN
            'daerah_asal' => 'nullable|string|in:luar_karawang,dalam_karawang',
            'provinsi_asal' => 'nullable|string',
            'kota_kabupaten_asal' => 'nullable|string',
            
            'jenjang_pendidikan' => 'nullable|string|in:pkbm,smk,sma,d3,s1,s2',
            'asal_sekolah' => 'nullable|string|max:255',
            'jurusan_sekolah' => 'nullable|string|max:255',
            'agama' => 'nullable|string|in:Islam,Kristen,Katolik,Hindu,Buddha,Konghucu',
            'tempat_tinggal' => 'nullable|string|max:1000',
        ]);
        
        // PERBAIKAN: Mengubah spasi di data input menjadi underscore sebelum disimpan (jika ada)
        if (isset($validated['daerah_asal'])) {
             $validated['daerah_asal'] = str_replace(' ', '_', strtolower($validated['daerah_asal']));
        }


        // Filter null values (untuk draft)
        $validated = array_filter($validated, function($value) {
            return $value !== null && $value !== '';
        });

        Log::info('Filtered Draft Data:', $validated);

        try {
            DB::beginTransaction();

            if (!empty($validated)) {
                $dataToSave = array_merge([
                    'user_id' => $user->id,
                    'is_verified' => false,
                    'status_bio' => 'draft' // Disimpan sebagai draft
                ], $validated);
                
                $biodata = Biodata::updateOrCreate(
                    ['user_id' => $user->id],
                    $dataToSave
                );
                
                Log::info('Draft After Save:', $biodata->toArray());
            }

            DB::commit();
            Log::info('=== BIODATA DRAFT SUCCESS: Redirecting to Dashboard ===');

            // REDIRECT SUDAH BENAR: Ke halaman tujuan akhir (Dashboard), bukan ke halaman yang sama
            return redirect()->route('mahasiswa.dashboard')
                ->with('success', 'Draft biodata berhasil disimpan!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Biodata Draft Error: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menyimpan draft: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Menampilkan form edit biodata
     */
    public function edit()
    {
        $biodata = Auth::user()->biodata;

        if (!$biodata) {
            return redirect()->route('mahasiswa.biodata.create')
                ->with('error', 'Biodata tidak ditemukan. Silakan isi biodata terlebih dahulu.');
        }

        // PERBAIKAN: Asumsi route 'mahasiswa.biodata.create' dan 'mahasiswa.biodata.edit' menggunakan view yang sama
        return view('mahasiswa.biodata.create', compact('biodata'));
    }

    /**
     * Debug method untuk melihat struktur database
     */
    public function debug()
    {
        $user = Auth::user();
        $biodata = Biodata::where('user_id', $user->id)->first();
        $tableColumns = DB::getSchemaBuilder()->getColumnListing('biodatas');

        return response()->json([
            'user_id' => $user->id,
            'table_columns' => $tableColumns,
            'biodata_exists' => $biodata ? true : false,
            'biodata_data' => $biodata,
            'missing_columns' => array_diff([
                'alamat', 'jenjang_pendidikan', 'is_verified', 'status_bio'
            ], $tableColumns)
        ]);
    }
}