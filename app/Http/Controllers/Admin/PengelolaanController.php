<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use App\Exports\PengelolaanExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PengelolaanController extends Controller
{
    public function index(Request $request): View
    {
        // Mengambil seluruh data master_ereg tanpa membatasi tipe jalur pendaftaran
        $query = DB::table('master_ereg')
            ->leftJoin('users', 'master_ereg.user_id', '=', 'users.id')
            ->leftJoin('pengelolaan_jalur', 'master_ereg.user_id', '=', 'pengelolaan_jalur.user_id')
            ->where('master_ereg.status', 'completed') 
            ->where('users.role', 'mahasiswa_baru') 
            ->select(
                'master_ereg.user_id as user_id',
                'master_ereg.nama as nama_lengkap', 
                'master_ereg.jalur_pendaftaran',   
                DB::raw('COALESCE(pengelolaan_jalur.status_verifikasi, 0) as status_verifikasi'),
                'pengelolaan_jalur.teks_informasi'
            );

        // Fitur filter berdasarkan jalur (jika admin memilih lewat dropdown filter)
        if ($request->filled('jalur')) {
            $query->where('master_ereg.jalur_pendaftaran', 'LIKE', '%' . $request->jalur . '%');
        }

        // Pencarian nama
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('master_ereg.nama', 'LIKE', "%{$search}%");
        }

        // Pagination
        $pengelolaan = $query->orderBy('master_ereg.created_at', 'desc')->paginate(20);
        $pengelolaan->appends($request->all());

        // Hitung statistik secara global sinkron dengan query di atas
        $baseStatQuery = DB::table('master_ereg')
            ->leftJoin('users', 'master_ereg.user_id', '=', 'users.id')
            ->leftJoin('pengelolaan_jalur', 'master_ereg.user_id', '=', 'pengelolaan_jalur.user_id')
            ->where('master_ereg.status', 'completed')
            ->where('users.role', 'mahasiswa_baru');

        $statTotal = (clone $baseStatQuery)->count();
        $statVerified = (clone $baseStatQuery)->where('pengelolaan_jalur.status_verifikasi', 1)->count();
        $statUnverified = $statTotal - $statVerified;

        return view('admin.pengelolaan.index', compact('pengelolaan', 'statTotal', 'statVerified', 'statUnverified'));
    }

    public function updateStatus(Request $request, $userId)
    {
        $request->validate([
            'status_verifikasi' => 'required|in:0,1'
        ]);

        try {
            $user = DB::table('master_ereg')
                ->where('user_id', $userId)
                ->select('nama', 'jalur_pendaftaran')
                ->first();

            if (!$user) {
                return redirect()->back()->with('error', 'User tidak ditemukan atau belum mengisi E-Reg.');
            }

            DB::table('pengelolaan_jalur')->updateOrInsert(
                ['user_id' => $userId],
                [
                    'nama_lengkap' => $user->nama, 
                    'jalur_pendaftaran' => $user->jalur_pendaftaran,
                    'status_verifikasi' => $request->status_verifikasi,
                    'updated_at' => now()
                ]
            );

            $statusText = $request->status_verifikasi == 1 ? 'Diverifikasi' : 'Belum Verifikasi';
            return redirect()->back()->with('success', "Status berhasil diubah menjadi: $statusText");

        } catch (\Exception $e) {
            \Log::error('Error update status pengelolaan: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengupdate status.');
        }
    }

    public function exportData(Request $request)
    {
        try {
            $namaJalur = $request->filled('jalur') ? str_replace(' ', '_', $request->jalur) : 'Semua_Jalur';
            $fileName = 'Data_Mahasiswa_' . $namaJalur . '_' . date('Ymd_His') . '.xlsx';

            return Excel::download(new PengelolaanExport($request), $fileName);

        } catch (\Exception $e) {
            \Log::error('Error export XLSX pengelolaan: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal meng-export data Excel: ' . $e->getMessage());
        }
    }

    public function bulkVerify(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|string'
        ]);

        try {
            DB::beginTransaction();

            $userIds = json_decode($request->user_ids, true);

            if (empty($userIds)) {
                return redirect()->back()->with('error', 'Tidak ada data yang dipilih.');
            }

            $usersToVerify = DB::table('master_ereg')
                ->whereIn('user_id', $userIds)
                ->select('user_id', 'nama as nama_lengkap', 'jalur_pendaftaran')
                ->get();

            $existingRecords = DB::table('pengelolaan_jalur')
                ->whereIn('user_id', $userIds)
                ->pluck('user_id')
                ->toArray();

            if (count($existingRecords) > 0) {
                DB::table('pengelolaan_jalur')
                    ->whereIn('user_id', $existingRecords)
                    ->update([
                        'status_verifikasi' => 1,
                        'updated_at' => now()
                    ]);
            }

            $newUsers = $usersToVerify->whereNotIn('user_id', $existingRecords);
            $insertData = [];

            foreach ($newUsers as $u) {
                $insertData[] = [
                    'user_id' => $u->user_id,
                    'nama_lengkap' => $u->nama_lengkap,
                    'jalur_pendaftaran' => $u->jalur_pendaftaran,
                    'status_verifikasi' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            if (count($insertData) > 0) {
                DB::table('pengelolaan_jalur')->insert($insertData);
            }

            DB::commit();

            $count = count($userIds);
            return redirect()->back()->with('success', "$count data mahasiswa terpilih berhasil diverifikasi!");

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error bulk verify pengelolaan: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal melakukan verifikasi massal: ' . $e->getMessage());
        }
    }

    public function bulkUpdateInformasi(Request $request)
    {
        $request->validate([
            'teks_informasi' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $query = DB::table('master_ereg')
                ->leftJoin('users', 'master_ereg.user_id', '=', 'users.id')
                ->where('master_ereg.status', 'completed')
                ->where('users.role', 'mahasiswa_baru');

            if ($request->filled('jalur')) {
                $query->where('master_ereg.jalur_pendaftaran', 'LIKE', '%' . $request->jalur . '%');
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where('master_ereg.nama', 'LIKE', "%{$search}%");
            }

            $usersToUpdate = $query->select(
                'master_ereg.user_id as user_id',
                'master_ereg.nama as nama_lengkap',
                'master_ereg.jalur_pendaftaran'
            )->get();

            if ($usersToUpdate->isEmpty()) {
                return redirect()->back()->with('error', 'Tidak ada data mahasiswa yang sesuai dengan filter.');
            }

            $userIds = $usersToUpdate->pluck('user_id')->toArray();

            $existingRecords = DB::table('pengelolaan_jalur')
                ->whereIn('user_id', $userIds)
                ->pluck('user_id')
                ->toArray();

            if (count($existingRecords) > 0) {
                DB::table('pengelolaan_jalur')
                    ->whereIn('user_id', $existingRecords)
                    ->update([
                        'teks_informasi' => $request->teks_informasi,
                        'updated_at' => now()
                    ]);
            }

            $newUsers = $usersToUpdate->whereNotIn('user_id', $existingRecords);
            $insertData = [];

            foreach ($newUsers as $u) {
                $insertData[] = [
                    'user_id' => $u->user_id,
                    'nama_lengkap' => $u->nama_lengkap,
                    'jalur_pendaftaran' => $u->jalur_pendaftaran,
                    'status_verifikasi' => 0,
                    'teks_informasi' => $request->teks_informasi,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            if (count($insertData) > 0) {
                DB::table('pengelolaan_jalur')->insert($insertData);
            }

            DB::commit();

            $count = count($userIds);
            return redirect()->back()->with('success', "Pesan informasi berhasil diterapkan ke $count mahasiswa!");

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error bulk update info pengelolaan: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengatur informasi massal: ' . $e->getMessage());
        }
    }

    public function updateInformasi(Request $request, $userId)
    {
        $request->validate([
            'teks_informasi' => 'nullable|string'
        ]);

        try {
            $existing = DB::table('pengelolaan_jalur')->where('user_id', $userId)->first();

            if ($existing) {
                DB::table('pengelolaan_jalur')
                    ->where('user_id', $userId)
                    ->update([
                        'teks_informasi' => $request->teks_informasi,
                        'updated_at' => now()
                    ]);
            } else {
                $user = DB::table('master_ereg')
                    ->where('user_id', $userId)
                    ->select('nama', 'jalur_pendaftaran')
                    ->first();

                if ($user) {
                    DB::table('pengelolaan_jalur')->insert([
                        'user_id' => $userId,
                        'nama_lengkap' => $user->nama,
                        'jalur_pendaftaran' => $user->jalur_pendaftaran,
                        'status_verifikasi' => 0,
                        'teks_informasi' => $request->teks_informasi,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            return redirect()->back()->with('success', 'Teks informasi berhasil disimpan.');
        } catch (\Exception $e) {
            \Log::error('Error update informasi pengelolaan: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyimpan informasi.');
        }
    }
}