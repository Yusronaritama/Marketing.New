<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterMoodleLulus;
use App\Models\MasterEreg;
use App\Models\MasterUser;
use App\Models\User;
use App\Models\Biodata;
use App\Models\ProgramStudi; 
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;
use ZipArchive;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class DownloadDataController extends Controller
{
    // Map: ['code' => 'Name']
    private $kodeToNamaMap = [];
    
    // Map: ['name (lowercase)' => 'code']
    private $namaToKodeMap = [];

    // Map: ['code' => 'Jenjang'] (S1/S2)
    private $kodeToJenjangMap = [];

    public function index()
    {
        $programStudi = ProgramStudi::where('status_prodi', 1)
                        ->orderBy('nama_prodi', 'asc')
                        ->get();

        return view('admin.download-data.index', compact('programStudi'));
    }

    /**
     * Helper: Auto-size semua kolom menggunakan PhpSpreadsheet iterator.
     */
    private function autoSizeAllColumns(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): void
    {
        foreach ($sheet->getColumnIterator() as $column) {
            $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
        }
    }

public function downloadCustomMhs(Request $request)
    {
        // 1. Ambil Input
        $selectedColumns = $request->input('columns', []);
        $statusIsian     = $request->input('status_isian');
        $prodiFilter     = $request->input('prodi_id');

        // 2. Validasi Minimal Kolom
        if (empty($selectedColumns)) {
            return back()->with('error', 'Silahkan pilih minimal satu kriteria kolom excel untuk diunduh.');
        }

        // Ambil Data User ID yang sudah bayar SLIK
        $paidSlikUserIds = DB::table('slik_billings')
            ->where('status', 'paid')
            ->pluck('user_id')
            ->toArray();

        // 3. Mapping Nama Prodi untuk Notifikasi
        $namaProdiDipilih = "Program Studi Terkait";
        if ($prodiFilter) {
            $prodiObj         = ProgramStudi::where('kode_prodi', $prodiFilter)->first();
            $namaProdiDipilih = $prodiObj ? $prodiObj->nama_prodi : "Prodi tersebut";
        }

        // 4. Query Dasar User
        $query = User::with([
            'biodata', 
            'masterUser', 
            'masterEreg', 
            'masterUser.masterMoodleLulus'
        ])
        ->whereHas('masterUser')
        ->where('role', 'mahasiswa_baru'); 

        // 5. Filter Status Isian
        if ($statusIsian) {
            switch ($statusIsian) {
                case 'empty':
                    $query->doesntHave('biodata');
                    break;
                case 'only_biodata':
                    $query->has('biodata')->doesntHave('masterEreg');
                    break;
                case 'already_ereg':
                    $query->whereHas('masterEreg', fn($q) => $q->where('status', 'completed'));
                    break;
                case 'paid_slik':
                    $query->whereIn('id', $paidSlikUserIds);
                    break;
                case 'paid_300k':
                    $query->whereHas('masterUser', function($q) {
                        $q->where('va_status', 'paid')
                          ->where('va_amount', 300000);
                    });
                    break;
            }
        }

        // 6. Filter Prodi
        if ($prodiFilter) {
            $moodleProdiUserIds = DB::table('master_moodle_lulus')
                ->join('master_users', 'master_users.id', '=', 'master_moodle_lulus.master_user_id')
                ->where('master_moodle_lulus.prodi_lulus', $prodiFilter)
                ->pluck('master_users.user_id')
                ->toArray();

            $query->where(function ($mainQuery) use ($prodiFilter, $moodleProdiUserIds) {
                $mainQuery
                    ->whereHas('masterEreg', function ($q) use ($prodiFilter) {
                        $q->where('jurusan1', $prodiFilter)
                          ->orWhere('jurusan2', $prodiFilter)
                          ->orWhere('jurusan_lulusan', $prodiFilter);
                    })
                    ->orWhereIn('id', $moodleProdiUserIds)
                    ->orWhere(function ($q) use ($prodiFilter) {
                        if ($prodiFilter === '00000') {
                            $q->whereNotNull('profesi')
                              ->whereHas('masterEreg', function ($q2) {
                                  $q2->whereNull('jurusan1')->whereNull('jurusan2');
                              });
                        }
                    });
            });
        }

        // 7. Eksekusi Query
        $users = $query->get();

        if ($users->isEmpty()) {
            $msg = "Maaf, tidak ditemukan data pendaftar";
            if ($statusIsian) $msg .= " dengan kriteria " . str_replace('_', ' ', $statusIsian);
            if ($prodiFilter)  $msg .= " pada program studi " . $namaProdiDipilih;
            return back()->with('info', $msg . ". Silahkan sesuaikan kembali filter Anda.");
        }

        $userIds = $users->pluck('id')->toArray();

        // Mapping Data Mahasiswa Finals secara cepat
        $finalsMap = DB::table('data_mahasiswa_finals')
            ->whereIn('user_id', $userIds)
            ->get()
            ->keyBy('user_id');

        $dataFinalUserIds = $finalsMap->keys()->toArray();

        // ===== Mapping Data Pendaftar Get NIM =====
        $nimMap = DB::table('pendaftar_get_nim')
            ->whereIn('user_id', $userIds)
            ->get()
            ->keyBy('user_id');
        
        $moodleLulusUserIds = DB::table('master_moodle_lulus')
            ->join('master_users', 'master_users.id', '=', 'master_moodle_lulus.master_user_id')
            ->pluck('master_users.user_id')
            ->toArray();

        // Mapping Master Data: Prodi, Provinces, Cities, Districts
        $prodiMap    = ProgramStudi::pluck('nama_prodi', 'kode_prodi')->toArray();
        $provinceMap = DB::table('provinces')->pluck('name', 'id')->toArray();
        $cityMap     = DB::table('cities')->pluck('name', 'id')->toArray();
        $districtMap = DB::table('districts')->pluck('name', 'id')->toArray();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        // 8. Mapping Header Label
        $headersMap = [
            'nim'                           => 'NIM',
            'email_mhs'                     => 'Email Mahasiswa (Kampus)',
            'nama'                          => 'Nama Lengkap',
            'email'                         => 'Email Pribadi',
            'phone'                         => 'No Telepon',
            'handphone'                     => 'Handphone',
            'telepon'                       => 'Telepon',
            'gelombang'                     => 'Gelombang',
            'jalur'                         => 'Jalur Pendaftaran',
            'prodi_1'                       => 'Nama Prodi 1',
            'prodi_2'                       => 'Nama Prodi 2',
            'va'                            => 'Username / VA',
            'status_bayar'                  => 'Bayar Daftar (300k)',
            'bayar_slik'                    => 'Bayar Tahap 1',
            'diterima'                      => 'Diterima',
            'data_final'                    => 'Mahasiswa Data Final',
            'status_data'                   => 'Status Data Final',
            'status_reset'                  => 'Status Reset Final',
            'ujian'                         => 'Hasil Ujian',
            'quiz'                          => 'Status Ujian',
            'nilai'                         => 'Nilai Ujian',
            'lulus'                         => 'Status Lulus',
            'ukuran_almamater'              => 'Ukuran Almamater',
            'beasiswa'                      => 'Status Beasiswa',

            // Data Pribadi
            'jenis_kelamin'                 => 'Jenis Kelamin',
            'nik'                           => 'NIK',
            'nisn'                          => 'NISN',
            'tempat_lahir'                  => 'Tempat Lahir',
            'tanggal_lahir'                 => 'Tanggal Lahir',
            'asal_sekolah'                  => 'Asal Sekolah',
            'penerima_kps'                  => 'Penerima KPS',
            'no_kps'                        => 'No KPS',
            'npwp'                          => 'NPWP',
            'kewarganegaraan'               => 'Kewarganegaraan',
            'id_agama'                      => 'Agama',

            // Wilayah
            'id_provinsi'                   => 'Provinsi',
            'id_kabupaten'                  => 'Kabupaten / Kota',
            'id_kecamatan'                  => 'Kecamatan',

            // Kebutuhan Khusus
            'id_kebutuhan_khusus_mahasiswa' => 'Kebutuhan Khusus Mahasiswa',
            'id_kebutuhan_khusus_ayah'      => 'Kebutuhan Khusus Ayah',
            'id_kebutuhan_khusus_ibu'       => 'Kebutuhan Khusus Ibu',

            // Data Orang Tua / Wali
            'nama_ayah'                     => 'Nama Ayah',
            'tanggal_lahir_ayah'            => 'Tanggal Lahir Ayah',
            'nik_ayah'                      => 'NIK Ayah',
            'id_jenjang_pendidikan_ayah'    => 'Jenjang Pend. Ayah',
            'id_pekerjaan_ayah'             => 'Pekerjaan Ayah',
            'id_penghasilan_ayah'           => 'Penghasilan Ayah',

            'nama_ibu_kandung'              => 'Nama Ibu Kandung',
            'tanggal_lahir_ibu'             => 'Tanggal Lahir Ibu',
            'nik_ibu'                       => 'NIK Ibu',
            'id_jenjang_pendidikan_ibu'     => 'Jenjang Pend. Ibu',
            'id_pekerjaan_ibu'              => 'Pekerjaan Ibu',
            'id_penghasilan_ibu'            => 'Penghasilan Ibu',

            'nama_wali'                     => 'Nama Wali',
            'tanggal_lahir_wali'            => 'Tanggal Lahir Wali',
            'nik_wali'                      => 'NIK Wali',
            'id_jenjang_pendidikan_wali'    => 'Jenjang Pend. Wali',
            'id_pekerjaan_wali'             => 'Pekerjaan Wali',
            'id_penghasilan_wali'           => 'Penghasilan Wali',
        ];

        $currentHeader = ['No'];
        foreach ($selectedColumns as $col) {
            $currentHeader[] = $headersMap[$col] ?? strtoupper($col);
        }
        $sheet->fromArray($currentHeader, NULL, 'A1');

        // 9. Isi Baris Data
        $rows = [];
        foreach ($users as $index => $u) {
            $rowData        = [$index + 1];
            $ereg           = $u->masterEreg;
            $bio            = $u->biodata;
            $final          = $finalsMap[$u->id] ?? null;
            $getNim         = $nimMap[$u->id] ?? null;
            $hasUjianRecord = in_array($u->id, $moodleLulusUserIds);

            foreach ($selectedColumns as $col) {
                switch ($col) {
                    // ===== NIM & EMAIL KAMPUS DARI pendaftar_get_nim =====
                    case 'nim':
                        $nimVal = $getNim->nim ?? null;
                        $rowData[] = $nimVal ? "'" . $nimVal : '-';
                        break;
                    case 'email_mhs':
                        $rowData[] = $getNim->email_mhs ?? '-';
                        break;

                    case 'nama':
                        $rowData[] = $getNim->nama_mahasiswa ?? $final->nama_mahasiswa ?? $bio->nama_lengkap ?? $u->name;
                        break;
                    case 'email':
                        $rowData[] = $final->email ?? $u->email;
                        break;
                    case 'phone':
                        $rowData[] = $u->phone ? "'" . $u->phone : '-';
                        break;
                    case 'handphone':
                        $hp = $final->handphone ?? $u->phone ?? null;
                        $rowData[] = $hp ? "'" . $hp : '-';
                        break;
                    case 'telepon':
                        $telp = $final->telepon ?? null;
                        $rowData[] = $telp ? "'" . $telp : '-';
                        break;
                    case 'gelombang':
                        $rowData[] = $u->masterUser->gelombang ?? '-';
                        break;
                    case 'jalur':
                        $rowData[] = $ereg->jalur_pendaftaran ?? '-';
                        break;
                    case 'prodi_1':
                        $rowData[] = $prodiMap[$ereg->jurusan1 ?? ''] ?? '-';
                        break;
                    case 'prodi_2':
                        $rowData[] = $prodiMap[$ereg->jurusan2 ?? ''] ?? '-';
                        break;
                    case 'va':
                        $rowData[] = $u->masterUser->trx_id ?? '-';
                        break;
                    case 'status_bayar':
                        $mUser = $u->masterUser;
                        if ($mUser && $mUser->va_status === 'paid' && (int)$mUser->va_amount === 300000) {
                            $rowData[] = 'Paid (300k)';
                        } elseif ($mUser && $mUser->va_status === 'paid') {
                            $rowData[] = 'Paid (' . number_format($mUser->va_amount, 0, ',', '.') . ')';
                        } else {
                            $rowData[] = 'Unpaid';
                        }
                        break;
                    case 'bayar_slik':
                        $rowData[] = in_array($u->id, $paidSlikUserIds) ? 'Lunas' : 'Belum';
                        break;
                    case 'diterima':
                        if (!empty($u->profesi)) {
                            $rowData[] = 'Sudah';
                        } else {
                            $rowData[] = ($ereg && !empty($ereg->jurusan1)) ? 'Sudah' : 'Belum';
                        }
                        break;
                    case 'data_final':
                        $rowData[] = in_array($u->id, $dataFinalUserIds) ? 'Sudah' : 'Belum';
                        break;
                    case 'status_data':
                        $rowData[] = $final->status_data ?? '-';
                        break;
                    case 'status_reset':
                        $rowData[] = $final->status_reset ?? '-';
                        break;
                    case 'ukuran_almamater':
                        $rowData[] = $ereg->ukuran_almamater ?? '-';
                        break;
                    case 'ujian':
                        $rowData[] = $hasUjianRecord ? 'Lulus' : 'Tidak Lulus';
                        break;
                    case 'quiz':
                        $rowData[] = $hasUjianRecord ? 'Sudah' : 'Belum';
                        break;
                    case 'nilai':
                        $rowData[] = $u->masterUser->masterMoodleLulus->nilai_quiz ?? '0';
                        break;
                    case 'lulus':
                        $rowData[] = $hasUjianRecord ? 'LULUS' : 'BELUM';
                        break;
                    case 'beasiswa':
                        $rowData[] = ($u->kel_beasiswa == 1) ? 'Penerima Beasiswa' : 'Bukan Penerima Beasiswa';
                        break;
                    case 'jenis_kelamin':
                        $rowData[] = $final->jenis_kelamin ?? $bio->jenis_kelamin ?? '-';
                        break;
                    case 'nik':
                        $nik = $final->nik ?? $bio->nik ?? null;
                        $rowData[] = $nik ? "'" . $nik : '-';
                        break;
                    case 'nisn':
                        $nisn = $final->nisn ?? $bio->nisn ?? null;
                        $rowData[] = $nisn ? "'" . $nisn : '-';
                        break;
                    case 'tempat_lahir':
                        $rowData[] = $final->tempat_lahir ?? $bio->tempat_lahir ?? '-';
                        break;
                    case 'tanggal_lahir':
                        $tgl = $final->tanggal_lahir ?? $bio->tanggal_lahir ?? null;
                        $rowData[] = $tgl ? Carbon::parse($tgl)->format('d/m/Y') : '-';
                        break;
                    case 'asal_sekolah':
                        $rowData[] = $final->asal_sekolah ?? $bio->asal_sekolah ?? '-';
                        break;
                    case 'id_agama':
                        $rowData[] = $final->id_agama ?? $bio->agama ?? '-';
                        break;
                    case 'kewarganegaraan':
                        $rowData[] = $final->kewarganegaraan ?? '-';
                        break;
                    case 'penerima_kps':
                        $rowData[] = $final->penerima_kps ?? '-';
                        break;
                    case 'no_kps':
                        $rowData[] = $final->no_kps ?? '-';
                        break;
                    case 'npwp':
                        $npwp = $final->npwp ?? null;
                        $rowData[] = $npwp ? "'" . $npwp : '-';
                        break;

                    // Wilayah (Nama Provinsi, Kab/Kota, Kecamatan)
                    case 'id_provinsi':
                        $provKey = $final->id_provinsi ?? null;
                        if (!empty($provKey) && isset($provinceMap[$provKey])) {
                            $rowData[] = $provinceMap[$provKey];
                        } else {
                            $rowData[] = $bio->provinsi ?? ($provKey ?: '-');
                        }
                        break;
                    case 'id_kabupaten':
                        $cityKey = $final->id_kabupaten ?? null;
                        if (!empty($cityKey) && isset($cityMap[$cityKey])) {
                            $rowData[] = $cityMap[$cityKey];
                        } else {
                            $rowData[] = $bio->kota ?? ($cityKey ?: '-');
                        }
                        break;
                    case 'id_kecamatan':
                        $distKey = $final->id_kecamatan ?? null;
                        if (!empty($distKey) && isset($districtMap[$distKey])) {
                            $rowData[] = $districtMap[$distKey];
                        } else {
                            $rowData[] = $bio->kecamatan ?? ($distKey ?: '-');
                        }
                        break;

                    // Kebutuhan Khusus
                    case 'id_kebutuhan_khusus_mahasiswa':
                        $rowData[] = $final->id_kebutuhan_khusus_mahasiswa ?? '-';
                        break;
                    case 'id_kebutuhan_khusus_ayah':
                        $rowData[] = $final->id_kebutuhan_khusus_ayah ?? '-';
                        break;
                    case 'id_kebutuhan_khusus_ibu':
                        $rowData[] = $final->id_kebutuhan_khusus_ibu ?? '-';
                        break;

                    // Data Ayah
                    case 'nama_ayah':
                        $rowData[] = $final->nama_ayah ?? $bio->nama_ayah ?? '-';
                        break;
                    case 'tanggal_lahir_ayah':
                        $tgl = $final->tanggal_lahir_ayah ?? null;
                        $rowData[] = $tgl ? Carbon::parse($tgl)->format('d/m/Y') : '-';
                        break;
                    case 'nik_ayah':
                        $nikAyah = $final->nik_ayah ?? null;
                        $rowData[] = $nikAyah ? "'" . $nikAyah : '-';
                        break;
                    case 'id_jenjang_pendidikan_ayah':
                        $rowData[] = $final->id_jenjang_pendidikan_ayah ?? '-';
                        break;
                    case 'id_pekerjaan_ayah':
                        $rowData[] = $final->id_pekerjaan_ayah ?? '-';
                        break;
                    case 'id_penghasilan_ayah':
                        $rowData[] = $final->id_penghasilan_ayah ?? '-';
                        break;

                    // Data Ibu
                    case 'nama_ibu_kandung':
                        $rowData[] = $final->nama_ibu_kandung ?? $bio->nama_ibu ?? '-';
                        break;
                    case 'tanggal_lahir_ibu':
                        $tgl = $final->tanggal_lahir_ibu ?? null;
                        $rowData[] = $tgl ? Carbon::parse($tgl)->format('d/m/Y') : '-';
                        break;
                    case 'nik_ibu':
                        $nikIbu = $final->nik_ibu ?? null;
                        $rowData[] = $nikIbu ? "'" . $nikIbu : '-';
                        break;
                    case 'id_jenjang_pendidikan_ibu':
                        $rowData[] = $final->id_jenjang_pendidikan_ibu ?? '-';
                        break;
                    case 'id_pekerjaan_ibu':
                        $rowData[] = $final->id_pekerjaan_ibu ?? '-';
                        break;
                    case 'id_penghasilan_ibu':
                        $rowData[] = $final->id_penghasilan_ibu ?? '-';
                        break;

                    // Data Wali
                    case 'nama_wali':
                        $rowData[] = $final->nama_wali ?? '-';
                        break;
                    case 'tanggal_lahir_wali':
                        $tgl = $final->tanggal_lahir_wali ?? null;
                        $rowData[] = $tgl ? Carbon::parse($tgl)->format('d/m/Y') : '-';
                        break;
                    case 'nik_wali':
                        $nikWali = $final->nik_wali ?? null;
                        $rowData[] = $nikWali ? "'" . $nikWali : '-';
                        break;
                    case 'id_jenjang_pendidikan_wali':
                        $rowData[] = $final->id_jenjang_pendidikan_wali ?? '-';
                        break;
                    case 'id_pekerjaan_wali':
                        $rowData[] = $final->id_pekerjaan_wali ?? '-';
                        break;
                    case 'id_penghasilan_wali':
                        $rowData[] = $final->id_penghasilan_wali ?? '-';
                        break;

                    default:
                        $rowData[] = '-';
                }
            }
            $rows[] = $rowData;
        }

        $sheet->fromArray($rows, NULL, 'A2');

        // 10. Styling & AutoSize
        $highestColumn = $sheet->getHighestColumn();
        $sheet->getStyle('A1:' . $highestColumn . '1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '6D28D9']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $this->autoSizeAllColumns($sheet);

        // 11. Download File
        $writer   = new Xlsx($spreadsheet);
        $fileName = 'Smart_Export_Mhs_' . now()->format('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        $writer->save('php://output');
        exit;
    }

    public function downloadDataMahasiswaFinals(Request $request)
    {
        try {
            $this->loadProdiMap();

            $jenjangFilter = $request->get('jenjang');
            $prodiFilter   = $request->get('prodi');

            $query = DB::table('data_mahasiswa_finals')
                ->leftJoin('users', 'data_mahasiswa_finals.user_id', '=', 'users.id')
                ->leftJoin('master_ereg', 'master_ereg.user_id', '=', 'data_mahasiswa_finals.user_id')
                ->leftJoin('master_users', 'master_users.user_id', '=', 'data_mahasiswa_finals.user_id')
                ->leftJoin('master_moodle_lulus', 'master_moodle_lulus.master_user_id', '=', 'master_users.id')
                ->select(
                    'data_mahasiswa_finals.*',
                    'users.email as user_email',
                    'users.phone as user_phone',
                    'users.name as user_name',
                    'master_ereg.jurusan1',
                    'master_ereg.jurusan2',
                    'master_moodle_lulus.prodi_lulus'
                );

            if ($jenjangFilter && in_array(strtoupper($jenjangFilter), ['S1', 'S2'])) {
                $jenjangFilter       = strtoupper($jenjangFilter);
                $kodeProdiByJenjang  = DB::table('program_studi')
                    ->where('jenjang', $jenjangFilter)
                    ->pluck('kode_prodi')
                    ->toArray();

                $query->where(function ($q) use ($kodeProdiByJenjang) {
                    $q->whereIn('master_ereg.jurusan1', $kodeProdiByJenjang)
                      ->orWhereIn('master_ereg.jurusan2', $kodeProdiByJenjang);
                });
            }

            if ($prodiFilter && $prodiFilter !== 'all') {
                $query->where(function ($q) use ($prodiFilter) {
                    $q->where('master_ereg.jurusan1', $prodiFilter)
                      ->orWhere('master_ereg.jurusan2', $prodiFilter);
                });
            }

            $data = $query->orderBy('data_mahasiswa_finals.id', 'desc')->get();

            if ($data->isEmpty()) {
                $msg = 'Tidak ada data mahasiswa finals';
                if ($jenjangFilter) $msg .= " jenjang {$jenjangFilter}";
                if ($prodiFilter && $prodiFilter !== 'all') {
                    $namaProdi = $this->getNamaProdiFromKode($prodiFilter);
                    $msg      .= " prodi {$namaProdi}";
                }
                return back()->with('info', $msg . ' yang ditemukan.');
            }

            $spreadsheet = new Spreadsheet();
            $sheet       = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Data Mahasiswa Finals');

            $headers = [
                'No', 'ID', 'User ID', 'Status Data', 'Status Reset', 'Nama Mahasiswa',
                'Jenis Kelamin', 'Jalan/RT RW', 'Dusun', 'Kelurahan', 'Kode Pos',
                'NISN', 'NIK', 'Tempat Lahir', 'Tanggal Lahir',
                'Nama Ayah', 'Tanggal Lahir Ayah', 'NIK Ayah',
                'Jenjang Pend. Ayah', 'Pekerjaan Ayah', 'Penghasilan Ayah',
                'Kebutuhan Khusus Ayah',
                'Nama Ibu Kandung', 'Tanggal Lahir Ibu', 'NIK Ibu',
                'Jenjang Pend. Ibu', 'Pekerjaan Ibu', 'Penghasilan Ibu',
                'Kebutuhan Khusus Ibu',
                'Kebutuhan Khusus Mahasiswa',
                'Nama Wali', 'Tanggal Lahir Wali', 'NIK Wali',
                'Jenjang Pend. Wali', 'Pekerjaan Wali', 'Penghasilan Wali',
                'Telepon', 'Handphone', 'Email',
                'Penerima KPS', 'No KPS', 'NPWP',
                'ID Provinsi', 'ID Kabupaten', 'ID Kecamatan',
                'Jenis Tinggal', 'Agama', 'Alat Transportasi', 'Kewarganegaraan',
                'Prodi Pilihan 1', 'Prodi Pilihan 2',
                'Dibuat Pada', 'Diupdate Pada',
            ];

            $colIndex = 1;
            foreach ($headers as $header) {
                $sheet->setCellValueByColumnAndRow($colIndex, 1, $header);
                $colIndex++;
            }

            $lastHeaderCol = $sheet->getHighestColumn();
            $sheet->getStyle('A1:' . $lastHeaderCol . '1')->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2D5B8A']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);

            $row = 2;
            foreach ($data as $index => $item) {
                $namaJurusan1 = $this->getNamaProdiFromKode($item->jurusan1 ?? '');
                $namaJurusan2 = $this->getNamaProdiFromKode($item->jurusan2 ?? '');

                $col = 1;
                $sheet->setCellValueByColumnAndRow($col++, $row, $index + 1);
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->id ?? 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->user_id ?? 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->status_data ?? 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->status_reset ?? 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->nama_mahasiswa ?? 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->jenis_kelamin ?? 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->jalanrtrw ?? 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->dusun ?? 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->kelurahan ?? 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->kode_pos ?? 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->nisn ?? 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->nik ? "'" . $item->nik : 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->tempat_lahir ?? 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->tanggal_lahir ? Carbon::parse($item->tanggal_lahir)->format('d/m/Y') : 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->nama_ayah ?? 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->tanggal_lahir_ayah ? Carbon::parse($item->tanggal_lahir_ayah)->format('d/m/Y') : 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->nik_ayah ? "'" . $item->nik_ayah : 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->id_jenjang_pendidikan_ayah ?? 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->id_pekerjaan_ayah ?? 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->id_penghasilan_ayah ?? 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->id_kebutuhan_khusus_ayah ?? 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->nama_ibu_kandung ?? 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->tanggal_lahir_ibu ? Carbon::parse($item->tanggal_lahir_ibu)->format('d/m/Y') : 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->nik_ibu ? "'" . $item->nik_ibu : 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->id_jenjang_pendidikan_ibu ?? 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->id_pekerjaan_ibu ?? 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->id_penghasilan_ibu ?? 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->id_kebutuhan_khusus_ibu ?? 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->id_kebutuhan_khusus_mahasiswa ?? 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->nama_wali ?? 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->tanggal_lahir_wali ? Carbon::parse($item->tanggal_lahir_wali)->format('d/m/Y') : 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->nik_wali ? "'" . $item->nik_wali : 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->id_jenjang_pendidikan_wali ?? 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->id_pekerjaan_wali ?? 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->id_penghasilan_wali ?? 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->telepon ?? 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->handphone ?? 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->email ?? ($item->user_email ?? 'N/A'));
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->penerima_kps ?? 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->no_kps ?? 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->npwp ? "'" . $item->npwp : 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->id_provinsi ?? 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->id_kabupaten ?? 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->id_kecamatan ?? 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->id_jenis_tinggal ?? 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->id_agama ?? 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->id_alat_transportasi ?? 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->kewarganegaraan ?? 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $namaJurusan1 !== 'N/A' ? $namaJurusan1 : ($item->jurusan1 ?? 'N/A'));
                $sheet->setCellValueByColumnAndRow($col++, $row, $namaJurusan2 !== 'N/A' ? $namaJurusan2 : ($item->jurusan2 ?? 'N/A'));
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->created_at ? Carbon::parse($item->created_at)->format('d/m/Y H:i') : 'N/A');
                $sheet->setCellValueByColumnAndRow($col++, $row, $item->updated_at ? Carbon::parse($item->updated_at)->format('d/m/Y H:i') : 'N/A');
                $row++;
            }

            $lastRow = $row - 1;
            $lastCol = $sheet->getHighestColumn();
            $sheet->getStyle("A1:{$lastCol}{$lastRow}")->applyFromArray([
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);

            $this->autoSizeAllColumns($sheet);

            $fileSuffix = '';
            if ($jenjangFilter) $fileSuffix .= '_' . $jenjangFilter;
            if ($prodiFilter && $prodiFilter !== 'all') $fileSuffix .= '_' . $prodiFilter;

            $writer   = new Xlsx($spreadsheet);
            $filename = 'data_mahasiswa_finals' . $fileSuffix . '_' . Carbon::now()->format('Y_m_d_His') . '.xlsx';
            $filePath = storage_path('app/public/' . $filename);
            $writer->save($filePath);

            return response()->download($filePath)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('Error downloading data mahasiswa finals: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function loadProdiMap()
    {
        if (empty($this->kodeToNamaMap) || empty($this->namaToKodeMap) || empty($this->kodeToJenjangMap)) {
            $prodis = ProgramStudi::select('kode_prodi', 'nama_prodi', 'jenjang')->get();

            foreach ($prodis as $prodi) {
                $this->kodeToNamaMap[$prodi->kode_prodi] = $prodi->nama_prodi;
                $cleanName                               = strtolower(trim($prodi->nama_prodi));
                $this->namaToKodeMap[$cleanName]         = $prodi->kode_prodi;
                $this->kodeToJenjangMap[$prodi->kode_prodi] = strtoupper(trim($prodi->jenjang ?? ''));
            }
        }
    }

    private function getKodeJurusanFromNamaProdi($namaProdi)
    {
        if (empty($namaProdi)) return 'N/A';
        if (empty($this->namaToKodeMap)) $this->loadProdiMap();

        $namaClean = strtolower(trim($namaProdi));
        if (isset($this->namaToKodeMap[$namaClean])) {
            return $this->namaToKodeMap[$namaClean];
        }

        foreach ($this->namaToKodeMap as $namaDb => $kodeDb) {
            if (strpos($namaClean, $namaDb) !== false || strpos($namaDb, $namaClean) !== false) {
                return $kodeDb;
            }
        }
        return 'N/A';
    }

    private function getNamaProdiFromKode($kodeProdi)
    {
        if (empty($kodeProdi)) return 'N/A';
        if (empty($this->kodeToNamaMap)) $this->loadProdiMap();
        return $this->kodeToNamaMap[$kodeProdi] ?? 'N/A';
    }

    private function getJenjangFromKode($kodeProdi)
    {
        if (empty($kodeProdi)) return '';
        if (empty($this->kodeToJenjangMap)) $this->loadProdiMap();
        return $this->kodeToJenjangMap[$kodeProdi] ?? '';
    }

    public function downloadBiodatas(Request $request)
    {
        try {
            $data        = Biodata::with(['user.masterUser', 'user.masterEreg'])->get();
            $spreadsheet = new Spreadsheet();
            $sheet       = $spreadsheet->getActiveSheet();

            $headers = [
                'No', 'Nama Lengkap', 'Email', 'Nomor Telepon', 'Gelombang',
                'Tempat Lahir', 'Tanggal Lahir', 'Nama Ibu', 'Nama Ayah', 'Alamat',
                'Jenjang Pendidikan', 'Asal Sekolah', 'Jurusan Sekolah', 'Agama',
                'Tempat Tinggal', 'Status Verifikasi', 'Dibuat Pada', 'Diupdate Pada',
            ];

            $column = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($column . '1', $header);
                $column++;
            }

            $sheet->getStyle('A1:R1')->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2D5B8A']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);

            $row = 2;
            foreach ($data as $index => $item) {
                $user       = $item->user ?? null;
                $masterUser = $user->masterUser ?? null;

                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, $item->nama_lengkap ?? 'N/A');
                $sheet->setCellValue('C' . $row, $user->email ?? 'N/A');
                $sheet->setCellValue('D' . $row, $user->phone ?? 'N/A');
                $sheet->setCellValue('E' . $row, $masterUser->gelombang ?? 'N/A');
                $sheet->setCellValue('F' . $row, $item->tempat_lahir ?? 'N/A');
                $sheet->setCellValue('G' . $row, $item->tanggal_lahir ? Carbon::parse($item->tanggal_lahir)->format('d/m/Y') : 'N/A');
                $sheet->setCellValue('H' . $row, $item->nama_ibu ?? 'N/A');
                $sheet->setCellValue('I' . $row, $item->nama_ayah ?? 'N/A');
                $sheet->setCellValue('J' . $row, $item->alamat ?? 'N/A');
                $sheet->setCellValue('K' . $row, $item->jenjang_pendidikan ?? 'N/A');
                $sheet->setCellValue('L' . $row, $item->asal_sekolah ?? 'N/A');
                $sheet->setCellValue('M' . $row, $item->jurusan_sekolah ?? 'N/A');
                $sheet->setCellValue('N' . $row, $item->agama ?? 'N/A');
                $sheet->setCellValue('O' . $row, $item->tempat_tinggal ?? 'N/A');
                $sheet->setCellValue('P' . $row, $item->is_verified ? 'Terverifikasi' : 'Belum Terverifikasi');
                $sheet->setCellValue('Q' . $row, $item->created_at ? $item->created_at->format('d/m/Y H:i') : 'N/A');
                $sheet->setCellValue('R' . $row, $item->updated_at ? $item->updated_at->format('d/m/Y H:i') : 'N/A');
                $row++;
            }

            $lastRow = $row - 1;
            $sheet->getStyle("A1:R{$lastRow}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
            ]);

            $this->autoSizeAllColumns($sheet);

            $writer   = new Xlsx($spreadsheet);
            $filename = 'data_biodata_mahasiswa_' . Carbon::now()->format('Y_m_d_His') . '.xlsx';
            $filePath = storage_path('app/public/' . $filename);
            $writer->save($filePath);

            return response()->download($filePath)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('Error downloading biodatas data: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function downloadMoodleLulus(Request $request)
    {
        try {
            $this->loadProdiMap();

            $data = MasterMoodleLulus::with([
                'masterUser.user.biodata',
                'masterUser.user.masterEreg',
            ])->get();

            $spreadsheet = new Spreadsheet();
            $sheet       = $spreadsheet->getActiveSheet();

            $headers = [
                'No', 'Nama Lengkap', 'Email', 'Nomor Telepon', 'Gelombang',
                'Kode Jurusan Lulus', 'Nama Prodi Lulus', 'Nilai Quiz',
                'Kode Jurusan 1', 'Jurusan 1',
                'Kode Jurusan 2', 'Jurusan 2',
                'Jalur Pendaftaran', 'Status Pekerjaan', 'Tanggal Kelulusan',
                'Dibuat Pada', 'Diupdate Pada',
            ];

            $column = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($column . '1', $header);
                $column++;
            }

            $sheet->getStyle('A1:Q1')->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2D5B8A']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);

            $row = 2;
            foreach ($data as $index => $item) {
                $user    = $item->masterUser->user ?? null;
                $biodata = $user->biodata ?? null;
                $ereg    = $user->masterEreg ?? null;

                $kodeJurusanLulus  = $this->getKodeJurusanFromNamaProdi($item->nama_prodi_lulus);
                $kodeJurusan1      = $ereg->jurusan1 ?? 'N/A';
                $namaJurusan1      = $this->getNamaProdiFromKode($kodeJurusan1);
                $jenjangJurusan1   = $this->getJenjangFromKode($kodeJurusan1);

                if ($jenjangJurusan1 === 'S2') {
                    $kodeJurusan2 = '-';
                    $namaJurusan2 = 'Pendaftar Program Pascasarjana (S2)';
                } else {
                    $rawKode2 = $ereg->jurusan2;
                    if (empty($rawKode2) || $rawKode2 === 'N/A' || $rawKode2 === '-') {
                        $kodeJurusan2 = '-';
                        $namaJurusan2 = 'Hanya Memilih 1 Program Studi';
                    } else {
                        $kodeJurusan2 = $rawKode2;
                        $namaJurusan2 = $this->getNamaProdiFromKode($kodeJurusan2);
                    }
                }

                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, $biodata->nama_lengkap ?? $user->name ?? 'N/A');
                $sheet->setCellValue('C' . $row, $user->email ?? 'N/A');
                $sheet->setCellValue('D' . $row, $user->phone ?? 'N/A');
                $sheet->setCellValue('E' . $row, $item->masterUser->gelombang ?? 'N/A');
                $sheet->setCellValue('F' . $row, $kodeJurusanLulus);
                $sheet->setCellValue('G' . $row, $item->nama_prodi_lulus ?? 'N/A');
                $sheet->setCellValue('H' . $row, $item->nilai_quiz ?? 'N/A');
                $sheet->setCellValue('I' . $row, $kodeJurusan1);
                $sheet->setCellValue('J' . $row, $namaJurusan1);
                $sheet->setCellValue('K' . $row, $kodeJurusan2);
                $sheet->setCellValue('L' . $row, $namaJurusan2);
                $sheet->setCellValue('M' . $row, $ereg->jalur_pendaftaran ?? 'N/A');
                $sheet->setCellValue('N' . $row, $ereg->status_pekerjaan ?? 'N/A');
                $sheet->setCellValue('O' . $row, $item->tanggal_lulus ? Carbon::parse($item->tanggal_lulus)->format('d/m/Y H:i') : 'N/A');
                $sheet->setCellValue('P' . $row, $item->created_at ? $item->created_at->format('d/m/Y H:i') : 'N/A');
                $sheet->setCellValue('Q' . $row, $item->updated_at ? $item->updated_at->format('d/m/Y H:i') : 'N/A');
                $row++;
            }

            $lastRow = $row - 1;
            $sheet->getStyle("A1:Q{$lastRow}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
            ]);

            $this->autoSizeAllColumns($sheet);

            $writer   = new Xlsx($spreadsheet);
            $filename = 'data_kelulusan_mahasiswa_' . Carbon::now()->format('Y_m_d_His') . '.xlsx';
            $filePath = storage_path('app/public/' . $filename);
            $writer->save($filePath);

            return response()->download($filePath)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('Error downloading moodle lulus data: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function downloadMasterEreg(Request $request)
    {
        try {
            $this->loadProdiMap();

            $status = $request->get('status', 'all');
            $query  = MasterEreg::with(['user.biodata', 'user.masterUser']);

            if ($status !== 'all') {
                $query->where('status', $status);
            }

            $data        = $query->get();
            $spreadsheet = new Spreadsheet();
            $sheet       = $spreadsheet->getActiveSheet();

            $headers = [
                'No', 'Nama Lengkap', 'Email', 'Nomor Telepon', 'Gelombang',
                'Jalur Pendaftaran', 'Status Pekerjaan', 'Jurusan Lulusan', 'Kode Promo',
                'Kode Jurusan 1', 'Jurusan 1', 'Alasan Jurusan 1',
                'Kode Jurusan 2', 'Jurusan 2', 'Alasan Jurusan 2',
                'Status Pendaftaran', 'Step Completed', 'Scan Status',
                'Dibuat Pada', 'Diupdate Pada',
            ];

            $column = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($column . '1', $header);
                $column++;
            }

            $sheet->getStyle('A1:T1')->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2D5B8A']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);

            $row = 2;
            foreach ($data as $index => $item) {
                $user       = $item->user ?? null;
                $biodata    = $user->biodata ?? null;
                $masterUser = $user->masterUser ?? null;

                $kodeJurusan1    = $item->jurusan1 ?? 'N/A';
                $namaJurusan1    = $this->getNamaProdiFromKode($kodeJurusan1);
                $jenjangJurusan1 = $this->getJenjangFromKode($kodeJurusan1);

                if ($jenjangJurusan1 === 'S2') {
                    $kodeJurusan2 = '-';
                    $namaJurusan2 = 'Pendaftar Program Pascasarjana (S2)';
                } else {
                    $rawKode2 = $item->jurusan2;
                    if (empty($rawKode2) || $rawKode2 === 'N/A' || $rawKode2 === '-') {
                        $kodeJurusan2 = '-';
                        $namaJurusan2 = 'Hanya Memilih 1 Program Studi';
                    } else {
                        $kodeJurusan2 = $rawKode2;
                        $namaJurusan2 = $this->getNamaProdiFromKode($kodeJurusan2);
                    }
                }

                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, $biodata->nama_lengkap ?? $user->name ?? 'N/A');
                $sheet->setCellValue('C' . $row, $user->email ?? 'N/A');
                $sheet->setCellValue('D' . $row, $item->phone ?? $user->phone ?? 'N/A');
                $sheet->setCellValue('E' . $row, $masterUser->gelombang ?? 'N/A');
                $sheet->setCellValue('F' . $row, $item->jalur_pendaftaran ?? 'N/A');
                $sheet->setCellValue('G' . $row, $item->status_pekerjaan ?? 'N/A');
                $sheet->setCellValue('H' . $row, $item->jurusan_lulusan ?? 'N/A');
                $sheet->setCellValue('I' . $row, $item->kode_promo ?? 'N/A');
                $sheet->setCellValue('J' . $row, $kodeJurusan1);
                $sheet->setCellValue('K' . $row, $namaJurusan1);
                $sheet->setCellValue('L' . $row, $item->alasan_jurusan_1 ?? 'N/A');
                $sheet->setCellValue('M' . $row, $kodeJurusan2);
                $sheet->setCellValue('N' . $row, $namaJurusan2);
                $sheet->setCellValue('O' . $row, $item->alasan_jurusan_2 ?? 'N/A');
                $sheet->setCellValue('P' . $row, $item->status ?? 'N/A');
                $sheet->setCellValue('Q' . $row, $item->step_completed ?? 'N/A');
                $sheet->setCellValue('R' . $row, $item->scan_status ?? 'N/A');
                $sheet->setCellValue('S' . $row, $item->created_at ? $item->created_at->format('d/m/Y H:i') : 'N/A');
                $sheet->setCellValue('T' . $row, $item->updated_at ? $item->updated_at->format('d/m/Y H:i') : 'N/A');
                $row++;
            }

            $lastRow = $row - 1;
            $sheet->getStyle("A1:T{$lastRow}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
            ]);

            $this->autoSizeAllColumns($sheet);

            $statusLabel = $this->getStatusLabel($status);
            $filename    = 'data_pendaftaran_mahasiswa_' . $statusLabel . '_' . Carbon::now()->format('Y_m_d_His') . '.xlsx';
            $filePath    = storage_path('app/public/' . $filename);

            $writer = new Xlsx($spreadsheet);
            $writer->save($filePath);

            return response()->download($filePath)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('Error downloading master ereg data: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function getStatusLabel($status)
    {
        $labels = [
            'all'         => 'semua',
            'completed'   => 'completed',
            'in_progress' => 'in_progress',
            'rejected'    => 'rejected',
        ];
        return $labels[$status] ?? 'semua';
    }

    public function downloadAllComplete(Request $request)
    {
        try {
            $this->loadProdiMap();
            $filePath = $this->createGabunganAllDataFile();
            $filename = 'semua_data_lengkap_mahasiswa_' . Carbon::now()->format('Y_m_d_His') . '.xlsx';
            return response()->download($filePath, $filename)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Error downloading all complete data: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function downloadAllWithPDF(Request $request)
    {
        try {
            $this->loadProdiMap();
            $zip         = new ZipArchive();
            $zipFileName = 'semua_data_dan_file_mahasiswa_' . Carbon::now()->format('Y_m_d_His') . '.zip';
            $zipPath     = storage_path('app/public/' . $zipFileName);

            if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
                $excelPath = $this->createGabunganAllDataFile();
                $zip->addFile($excelPath, 'data_gabungan_semua_mahasiswa.xlsx');
                $this->addPDFFilesToZip($zip);
                $zip->close();

                if (file_exists($excelPath)) unlink($excelPath);

                return response()->download($zipPath)->deleteFileAfterSend(true);
            } else {
                throw new \Exception('Gagal membuat file ZIP');
            }
        } catch (\Exception $e) {
            Log::error('Error downloading all data with PDF: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function addPDFFilesToZip(ZipArchive $zip)
    {
        try {
            $data      = MasterEreg::with(['user.biodata'])->get();
            $fileCount = 0;

            foreach ($data as $index => $item) {
                $user           = $item->user ?? null;
                $biodata        = $user->biodata ?? null;
                $namaMahasiswa  = $biodata->nama_lengkap ?? $user->name ?? 'Unknown_' . $item->id;
                $folderName     = $this->sanitizeFileName($namaMahasiswa . '_' . $item->id);

                $pdfFiles = [
                    'file_kk'                    => 'Kartu_Keluarga',
                    'file_ijazah'                => 'Ijazah',
                    'file_ktp'                   => 'KTP',
                    'file_ijazah_s1'             => 'Ijazah_S1',
                    'file_transkrip_s1'          => 'Transkrip_S1',
                    'file_surat_pindah'          => 'Surat_Pindah',
                    'file_surat_akademik_pindahan'  => 'Surat_Akademik_Pindahan',
                    'file_surat_alih_jenjang'    => 'Surat_Alih_Jenjang',
                    'file_surat_akademik_ekstensi'  => 'Surat_Akademik_Ekstensi',
                    'file_surat_tugas_kerja'     => 'Surat_Tugas_Kerja',
                    'file_transkrip_nilai_rpl'   => 'Transkrip_Nilai_RPL',
                    'file_surat_akademik_rpl'    => 'Surat_Akademik_RPL',
                ];

                foreach ($pdfFiles as $field => $label) {
                    if (!empty($item->$field) && $item->$field !== 'NULL') {
                        $originalPath  = $item->$field;
                        $possiblePaths = [
                            storage_path('app/public/' . $originalPath),
                            storage_path('app/public/ereg_files/' . basename($originalPath)),
                        ];

                        foreach ($possiblePaths as $filePath) {
                            if (file_exists($filePath)) {
                                $extension = pathinfo($filePath, PATHINFO_EXTENSION);
                                $fileName  = $label . '.' . $extension;
                                $zipEntry  = $folderName . '/' . $fileName;
                                $zip->addFile($filePath, $zipEntry);
                                $fileCount++;
                                break;
                            }
                        }
                    }
                }
            }
            Log::info("Added {$fileCount} PDF files to ZIP");
        } catch (\Exception $e) {
            Log::error('Error adding PDF files to ZIP: ' . $e->getMessage());
            throw $e;
        }
    }

    private function sanitizeFileName($name)
    {
        $name = preg_replace('/[^\w\s-]/', '', $name);
        $name = preg_replace('/\s+/', '_', $name);
        $name = preg_replace('/_+/', '_', $name);
        $name = trim($name, '_');
        return $name;
    }

    private function createGabunganAllDataFile()
    {
        try {
            $dataFinalUserIds = DB::table('data_mahasiswa_finals')->pluck('user_id')->toArray();

            // Ambil data kelulusan terpusat (murni dari master_moodle_lulus)
            $moodleLulusUserIds = DB::table('master_moodle_lulus')
                ->join('master_users', 'master_users.id', '=', 'master_moodle_lulus.master_user_id')
                ->pluck('master_users.user_id')
                ->toArray();

            $spreadsheet = new Spreadsheet();
            $data        = MasterEreg::with([
                'user.biodata',
                'user.masterUser.masterMoodleLulus',
            ])->get();

            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Data Gabungan Lengkap');

            $headers = [
                'No', 'ID User', 'Nama Lengkap', 'Email', 'Nomor Telepon', 'Gelombang',
                'Tempat Lahir', 'Tanggal Lahir', 'Jenis Kelamin', 'NIK', 'NISN',
                'Asal Sekolah', 'Jurusan Sekolah', 'Nama Ibu', 'Nama Ayah', 'Alamat',
                'Provinsi', 'Kota', 'Kecamatan', 'Jenjang Pendidikan', 'Tahun Lulus',
                'Agama', 'Tempat Tinggal', 'Status Verifikasi Biodata',
                'Jalur Pendaftaran', 'Status Pekerjaan', 'Jurusan Lulusan', 'Kode Promo',
                'Kode Jurusan 1', 'Jurusan 1', 'Alasan Jurusan 1',
                'Kode Jurusan 2', 'Jurusan 2', 'Alasan Jurusan 2',
                'Status Pendaftaran', 'Step Completed', 'Scan Status',
                'Prodi Lulus', 'Nilai Quiz', 'Status Kelulusan', 'Tanggal Kelulusan',
                'File KK', 'File Ijazah', 'File KTP', 'File Ijazah S1', 'File Transkrip S1',
                'Tanggal Daftar', 'Tanggal Update Biodata', 'Tanggal Update Pendaftaran',
            ];

            $column = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($column . '1', $header);
                $column++;
            }

            $sheet->getStyle('A1:AW1')->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2D5B8A']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            $row = 2;
            foreach ($data as $index => $item) {
                $user        = $item->user ?? null;
                $biodata     = $user->biodata ?? null;
                $masterUser  = $user->masterUser ?? null;
                $moodleLulus = $masterUser->masterMoodleLulus ?? null;

                $kodeJurusan1    = $item->jurusan1 ?? 'N/A';
                $namaJurusan1    = $this->getNamaProdiFromKode($kodeJurusan1);
                $jenjangJurusan1 = $this->getJenjangFromKode($kodeJurusan1);

                if ($jenjangJurusan1 === 'S2') {
                    $kodeJurusan2 = '-';
                    $namaJurusan2 = 'Pendaftar Program Pascasarjana (S2)';
                } else {
                    $rawKode2 = $item->jurusan2;
                    if (empty($rawKode2) || $rawKode2 === 'N/A' || $rawKode2 === '-') {
                        $kodeJurusan2 = '-';
                        $namaJurusan2 = 'Hanya Memilih 1 Program Studi';
                    } else {
                        $kodeJurusan2 = $rawKode2;
                        $namaJurusan2 = $this->getNamaProdiFromKode($kodeJurusan2);
                    }
                }

                $isUserUjian = $user ? in_array($user->id, $moodleLulusUserIds) : false;

                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, $user->id ?? 'N/A');
                $sheet->setCellValue('C' . $row, $biodata->nama_lengkap ?? ($user->name ?? 'N/A'));
                $sheet->setCellValue('D' . $row, $user->email ?? 'N/A');
                $sheet->setCellValue('E' . $row, $item->phone ?? ($user->phone ?? 'N/A'));
                $sheet->setCellValue('F' . $row, $masterUser->gelombang ?? 'N/A');
                $sheet->setCellValue('G' . $row, $biodata->tempat_lahir ?? 'N/A');

                $tglLahir = ($biodata && $biodata->tanggal_lahir) ? Carbon::parse($biodata->tanggal_lahir)->format('d/m/Y') : 'N/A';
                $sheet->setCellValue('H' . $row, $tglLahir);

                $sheet->setCellValue('I' . $row, $biodata->jenis_kelamin ?? 'N/A');
                $sheet->setCellValue('J' . $row, ($biodata && $biodata->nik) ? "'" . $biodata->nik : 'N/A');
                $sheet->setCellValue('K' . $row, $biodata->nisn ?? 'N/A');
                $sheet->setCellValue('L' . $row, $biodata->asal_sekolah ?? 'N/A');
                $sheet->setCellValue('M' . $row, $biodata->jurusan_sekolah ?? 'N/A');
                $sheet->setCellValue('N' . $row, $biodata->nama_ibu ?? 'N/A');
                $sheet->setCellValue('O' . $row, $biodata->nama_ayah ?? 'N/A');
                $sheet->setCellValue('P' . $row, $biodata->alamat ?? 'N/A');
                $sheet->setCellValue('Q' . $row, $biodata->provinsi ?? 'N/A');
                $sheet->setCellValue('R' . $row, $biodata->kota ?? 'N/A');
                $sheet->setCellValue('S' . $row, $biodata->kecamatan ?? 'N/A');
                $sheet->setCellValue('T' . $row, $biodata->jenjang_pendidikan ?? 'N/A');
                $sheet->setCellValue('U' . $row, $biodata->tahun_lulus ?? 'N/A');
                $sheet->setCellValue('V' . $row, $biodata->agama ?? 'N/A');
                $sheet->setCellValue('W' . $row, $biodata->tempat_tinggal ?? 'N/A');
                $sheet->setCellValue('X' . $row, ($biodata && $biodata->is_verified) ? 'Terverifikasi' : 'Belum');
                $sheet->setCellValue('Y' . $row, $item->jalur_pendaftaran ?? 'N/A');
                $sheet->setCellValue('Z' . $row, $item->status_pekerjaan ?? 'N/A');
                $sheet->setCellValue('AA' . $row, $item->jurusan_lulusan ?? 'N/A');
                $sheet->setCellValue('AB' . $row, $item->kode_promo ?? 'N/A');
                $sheet->setCellValue('AC' . $row, $kodeJurusan1);
                $sheet->setCellValue('AD' . $row, $namaJurusan1);
                $sheet->setCellValue('AE' . $row, $item->alasan_jurusan_1 ?? 'N/A');
                $sheet->setCellValue('AF' . $row, $kodeJurusan2);
                $sheet->setCellValue('AG' . $row, $namaJurusan2);
                $sheet->setCellValue('AH' . $row, $item->alasan_jurusan_2 ?? 'N/A');
                $sheet->setCellValue('AI' . $row, $item->status ?? 'N/A');
                $sheet->setCellValue('AJ' . $row, $item->step_completed ?? 'N/A');
                $sheet->setCellValue('AK' . $row, $item->scan_status ?? 'N/A');
                $sheet->setCellValue('AL' . $row, $moodleLulus->nama_prodi_lulus ?? 'N/A');
                $sheet->setCellValue('AM' . $row, $moodleLulus->nilai_quiz ?? 'N/A');
                $sheet->setCellValue('AN' . $row, $isUserUjian ? 'Lulus' : 'Belum');

                $tglLulus = ($moodleLulus && $moodleLulus->tanggal_lulus) ? Carbon::parse($moodleLulus->tanggal_lulus)->format('d/m/Y H:i') : 'N/A';
                $sheet->setCellValue('AO' . $row, $tglLulus);

                $sheet->setCellValue('AP' . $row, (!empty($item->file_kk) && $item->file_kk !== 'NULL') ? 'Ya' : 'Tidak');
                $sheet->setCellValue('AQ' . $row, (!empty($item->file_ijazah) && $item->file_ijazah !== 'NULL') ? 'Ya' : 'Tidak');
                $sheet->setCellValue('AR' . $row, (!empty($item->file_ktp) && $item->file_ktp !== 'NULL') ? 'Ya' : 'Tidak');
                $sheet->setCellValue('AS' . $row, (!empty($item->file_ijazah_s1) && $item->file_ijazah_s1 !== 'NULL') ? 'Ya' : 'Tidak');
                $sheet->setCellValue('AT' . $row, (!empty($item->file_transkrip_s1) && $item->file_transkrip_s1 !== 'NULL') ? 'Ya' : 'Tidak');
                $sheet->setCellValue('AU' . $row, $item->created_at ? $item->created_at->format('d/m/Y H:i') : 'N/A');

                $tglUpdateBio = ($biodata && $biodata->updated_at) ? $biodata->updated_at->format('d/m/Y H:i') : 'N/A';
                $sheet->setCellValue('AV' . $row, $tglUpdateBio);
                $sheet->setCellValue('AW' . $row, $item->updated_at ? $item->updated_at->format('d/m/Y H:i') : 'N/A');

                $row++;
            }

            $lastRow = $row - 1;
            $sheet->getStyle("A1:AW{$lastRow}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);

            $this->autoSizeAllColumns($sheet);

            $writer   = new Xlsx($spreadsheet);
            $filename = 'temp_gabungan_all_' . Carbon::now()->format('Y_m_d_His') . '.xlsx';
            $filePath = storage_path('app/public/' . $filename);
            $writer->save($filePath);

            return $filePath;

        } catch (\Exception $e) {
            Log::error('Error creating gabungan all data file: ' . $e->getMessage());
            throw $e;
        }
    }
}