<?php

namespace App\Http\Controllers\Mahasiswa\profesi\PDDIKTI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\DataMahasiswaFinal;
use App\Models\MasterEreg;

class DataFinalProfesiController extends Controller
{
    // ==========================================
    // DEFINISI DATA DUMMY (Sama persis kayak S1)
    // ==========================================
    private $dummy_nik = '0000000000000000';
    private $dummy_str = '-';
    private $dummy_date = '2000-01-01';
    private $dummy_wilayah = '000000';

    /**
     * Menampilkan Form Data Final PDDIKTI (Profesi)
     */
    public function create()
    {
        $user = Auth::user();

        // 1. CEK ATAU BUAT DATA AWAL (Auto-Draft dengan Dummy)
        $data = DataMahasiswaFinal::firstOrCreate(
            ['user_id' => $user->id], 
            [
                'status_data'    => 'pending',
                'nama_mahasiswa' => $user->name,
                'email'          => $user->email,
                'handphone'      => $user->no_hp ?? null,

                // DATA DUMMY 
                'nik'              => $this->dummy_nik, 
                'tempat_lahir'     => $this->dummy_str,
                'kelurahan'        => $this->dummy_str,
                'tanggal_lahir'    => $this->dummy_date,
                'jenis_kelamin'    => '*', 
                'id_agama'         => 1,   
                'kewarganegaraan'  => 'ID',
                'id_kecamatan'     => $this->dummy_wilayah,
                'id_provinsi'      => null,
                'id_kabupaten'     => null,
                'status_reset'     => 0,
                'nama_ibu_kandung' => $this->dummy_str, 
            ]
        );

        $ereg    = MasterEreg::where('user_id', $user->id)->first();
        $biodata = $user->biodata; 
        
        $eregStatus = 'data-final'; 

        // [PENYESUAIAN VIEW] Arahkan ke folder profesi
        return view('mahasiswa.profesi.berkas.PDDIKTI', compact('data', 'ereg', 'biodata', 'user', 'eregStatus'));
    }

    /**
     * Menyimpan Data Final (Permanen)
     */
    public function store(Request $request)
    {
        // =================================================================
        // 1. VALIDASI DATA (ANTI-DUMMY & FORMAT)
        // =================================================================
        
        $rules = [
            // --- IDENTITAS PRIBADI ---
            'nama_mahasiswa' => 'required|string|max:100',
            'nisn' => 'required|string|size:10',
            
            // NIK: Wajib numeric, 16 digit, dan BUKAN dummy
            'nik' => [
                'required', 
                'numeric', 
                'digits:16', 
                'not_in:' . $this->dummy_nik 
            ],

            // Tempat Lahir: Wajib isi, BUKAN strip (-)
            'tempat_lahir'  => ['required', 'string', 'max:32', 'not_in:' . $this->dummy_str],
            
            // TANGGAL LAHIR MAHASISWA
            'tanggal_lahir' => [
                'required', 
                'date', 
                'not_in:' . $this->dummy_date
            ],

            'jenis_kelamin' => 'required|in:L,P',
            'id_agama'      => 'required',
            'kewarganegaraan' => 'required|string',

            // --- DOMISILI ---
            'kelurahan'  => ['required', 'string', 'max:60', 'not_in:' . $this->dummy_str],
            'id_provinsi'  => 'required|exists:provinces,id',
            'id_kabupaten' => 'required|exists:cities,id',
            'id_kecamatan' => ['required', 'exists:districts,id', 'not_in:' . $this->dummy_wilayah],
            
            'jalan'      => 'nullable|string|max:80',
            'rt'         => 'nullable|numeric',
            'rw'         => 'nullable|numeric',
            'dusun'      => 'nullable|string|max:60',
            'kode_pos'   => 'nullable|string|max:5',
            'id_jenis_tinggal'     => 'nullable|numeric',
            'id_alat_transportasi' => 'nullable|numeric',

            // --- KONTAK ---
            'handphone' => 'nullable|numeric', 
            'telepon'   => 'nullable|string|max:20',
            'email'     => 'nullable|email|max:60',

            // --- DATA IBU KANDUNG ---
            'nama_ibu_kandung' => ['required', 'string', 'max:100', 'not_in:' . $this->dummy_str],
            'nik_ibu'          => 'nullable|numeric|digits:16',
            
            // TANGGAL LAHIR IBU
            'tanggal_lahir_ibu' => ['nullable', 'date', 'not_in:' . $this->dummy_date],
            
            'id_jenjang_pendidikan_ibu' => 'nullable',
            'id_pekerjaan_ibu'          => 'nullable',
            'id_penghasilan_ibu'        => 'nullable',

            // --- DATA AYAH ---
            'nama_ayah' => 'nullable|string|max:100',
            'nik_ayah'  => 'nullable|numeric|digits:16',
            
            // TANGGAL LAHIR AYAH
            'tanggal_lahir_ayah' => ['nullable', 'date', 'not_in:' . $this->dummy_date],
            
            'id_jenjang_pendidikan_ayah' => 'nullable',
            'id_pekerjaan_ayah'          => 'nullable',
            'id_penghasilan_ayah'        => 'nullable',

            // --- DATA TAMBAHAN ---
            'penerima_kps' => 'required|in:0,1', 
            'no_kps'       => 'nullable|string|max:80', 
            'npwp'         => 'nullable|string|max:20', 
            'id_kebutuhan_khusus_ayah' => 'nullable|numeric',
            'id_kebutuhan_khusus_ibu'  => 'nullable|numeric',
            'id_kebutuhan_khusus_mahasiswa' => 'nullable|numeric',

            // --- DATA WALI ---
            'nama_wali' => 'nullable|string|max:100',
            'nik_wali'  => 'nullable|string|size:16',
            
            // TANGGAL LAHIR WALI 
            'tanggal_lahir_wali' => [
                'nullable', 
                'date', 
                'required_with:nama_wali', 
                'not_in:' . $this->dummy_date
            ],
            
            'id_jenjang_pendidikan_wali' => 'required_with:nama_wali',
            'id_pekerjaan_wali'          => 'required_with:nama_wali',
            'id_penghasilan_wali'        => 'required_with:nama_wali',
        ];

        // Custom Error Messages
        $messages = [
            'required' => ':attribute wajib diisi.',
            'numeric'  => ':attribute harus berupa angka.',
            'digits'   => ':attribute harus berjumlah :digits digit.',
            'date'     => 'Format tanggal tidak valid.',
            'in'       => 'Pilihan :attribute tidak valid.',
            
            'not_in'   => 'Data ini masih default sistem. Silakan ubah dengan data asli.',
            
            'tanggal_lahir.not_in'      => 'Mohon ubah Tanggal Lahir Mahasiswa sesuai data asli.',
            'tanggal_lahir_ayah.not_in' => 'Mohon ubah Tanggal Lahir Ayah sesuai data asli.',
            'tanggal_lahir_ibu.not_in'  => 'Mohon ubah Tanggal Lahir Ibu sesuai data asli.',
            'tanggal_lahir_wali.not_in' => 'Mohon ubah Tanggal Lahir Wali sesuai data asli.',
            
            'required_with' => 'Mohon lengkapi :attribute karena Anda mengisi data Wali.',
        ];

        $attributes = [
            'nama_mahasiswa' => 'Nama Lengkap',
            'nik'            => 'NIK (KTP)',
            'nisn'           => 'NISN',
            'tempat_lahir'   => 'Tempat Lahir',
            'kelurahan'      => 'Kelurahan/Desa',
            'id_provinsi'  => 'Provinsi',
            'id_kabupaten' => 'Kabupaten/Kota',
            'id_kecamatan' => 'Kecamatan',
            'nama_ibu_kandung' => 'Nama Ibu Kandung',
            'tanggal_lahir'    => 'Tanggal Lahir',
            'tanggal_lahir_ibu' => 'Tanggal Lahir Ibu',
            'tanggal_lahir_ayah' => 'Tanggal Lahir Ayah',
            'tanggal_lahir_wali' => 'Tanggal Lahir Wali',
            'nik_wali'           => 'NIK Wali',
        ];

        $validatedData = $request->validate($rules, $messages, $attributes);

        // =================================================================
        // 2. PROSES SIMPAN DATA
        // =================================================================
        try {
            DB::beginTransaction();

            $user = Auth::user();

            // Tambahkan data sistem manual
            $validatedData['user_id']     = $user->id;
            $validatedData['status_data'] = 'completed'; // Ubah status jadi final
            $validatedData['status_reset'] = 1;

            // Handle default value 0 untuk kebutuhan khusus (hidden inputs)
            $validatedData['id_kebutuhan_khusus_mahasiswa'] = $request->input('id_kebutuhan_khusus_mahasiswa', 0);
            $validatedData['id_kebutuhan_khusus_ayah']      = $request->input('id_kebutuhan_khusus_ayah', 0);
            $validatedData['id_kebutuhan_khusus_ibu']       = $request->input('id_kebutuhan_khusus_ibu', 0);

            // SIMPAN / UPDATE PERMANEN
            DataMahasiswaFinal::updateOrCreate(
                ['user_id' => $user->id], 
                $validatedData            
            );

            // UPDATE PROGRESS di MasterEreg (Bila di profesi tetap menggunakan step_completed = 5)
            $ereg = MasterEreg::where('user_id', $user->id)->first();
            if ($ereg && $ereg->step_completed < 5) {
                $ereg->update(['step_completed' => 5]);
            }

            DB::commit();

            // [PENYESUAIAN REDIRECT] Arahkan ke dashboard profesi
            return redirect()->route('profesi.dashboard')
                ->with('success', 'Data Final PDDIKTI berhasil disimpan permanen! Tahap pemberkasan Anda telah selesai.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal simpan Data Final Profesi User ID ' . Auth::id() . ': ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan sistem saat menyimpan data. Silakan coba lagi.')
                ->withInput(); 
        }
        
    }
    
}