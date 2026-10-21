<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\DataMahasiswaFinal;
use App\Models\MasterUsers;
use App\Models\MasterEreg;
use Illuminate\Http\Request;

class DataS2Controller extends Controller
{
    private const PRODI_S2_MANAJEMEN_KODE = '61114';
    private function baseQuery()
    {
        return DataMahasiswaFinal::with('user', 'masterUser', 'masterEreg')
            ->whereHas('masterEreg', function ($q) {
                $q->where('jurusan1', self::PRODI_S2_MANAJEMEN_KODE)
                  ->orWhere('jurusan2', self::PRODI_S2_MANAJEMEN_KODE);
            });
    }

    /**
     * Display a listing — hanya S2 Manajemen (pilihan pertama)
     */
    public function index(Request $request)
    {
        $query = $this->baseQuery();

        // Filter berdasarkan status data
        if ($request->filled('status_data')) {
            $query->where('status_data', $request->status_data);
        }

        // Search berdasarkan nama / HP / email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_mahasiswa', 'like', "%{$search}%")
                  ->orWhere('handphone', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($q2) => $q2->where('email', 'like', "%{$search}%"));
            });
        }

        // Sorting
        $sortBy    = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage     = $request->get('per_page', 15);
        $dataS2List  = $query->paginate($perPage)->withQueryString();

        // Statistik
        $totalS2     = $this->baseQuery()->count();
        $completedS2 = $this->baseQuery()->where('status_data', 'completed')->count();
        $pendingS2   = $this->baseQuery()->where('status_data', 'pending')->count();
        $rejectedS2  = $this->baseQuery()->where('status_data', 'rejected')->count();

        return view('admin.data-s2.index', [
            'dataS2List'       => $dataS2List,
            'statusList'       => ['completed' => 'Selesai', 'pending' => 'Pending', 'rejected' => 'Ditolak'],
            'totalRecords'     => $totalS2,
            'completedRecords' => $completedS2,
            'pendingRecords'   => $pendingS2,
            'rejectedRecords'  => $rejectedS2,
        ]);
    }

    public function create()
    {
        return view('admin.data-s2.create');
    }

    public function show($id)
    {
        $dataS2    = DataMahasiswaFinal::findOrFail($id);
        $user      = $dataS2->user;
        $masterUser = $dataS2->masterUser;
        $masterEreg = $dataS2->masterEreg;

        return view('admin.data-s2.show', compact('dataS2', 'user', 'masterUser', 'masterEreg'));
    }

    public function edit($id)
    {
        $dataS2 = DataMahasiswaFinal::findOrFail($id);
        return view('admin.data-s2.edit', compact('dataS2'));
    }

    public function update(Request $request, $id)
    {
        $dataS2 = DataMahasiswaFinal::findOrFail($id);

        $validated = $request->validate([
            'nama_mahasiswa' => 'required|string|max:255',
            'jenis_kelamin'  => 'required|in:L,P',
            'tempat_lahir'   => 'required|string|max:255',
            'tanggal_lahir'  => 'required|date',
            'nik'            => 'required|string|max:16',
            'nisn'           => 'required|string|max:10',
            'email'          => 'required|email',
            'handphone'      => 'required|string|max:15',
        ]);

        $dataS2->update($validated);

        return redirect()->route('admin.data-s2.show', $dataS2->id)
                         ->with('success', 'Data S2 Manajemen berhasil diperbarui');
    }

    public function destroy($id)
    {
        $dataS2 = DataMahasiswaFinal::findOrFail($id);
        $dataS2->delete();

        return redirect()->route('admin.data-s2.index')
                         ->with('success', 'Data S2 Manajemen berhasil dihapus');
    }

    /**
     * Export CSV — hanya S2 Manajemen
     */
    public function export(Request $request)
    {
        $query = $this->baseQuery();

        if ($request->filled('status_data')) {
            $query->where('status_data', $request->status_data);
        }

        $data     = $query->get();
        $filename = 'data_s2_manajemen_' . date('Y-m-d_H-i-s') . '.csv';
        $handle   = fopen('php://memory', 'r+');

        fputcsv($handle, [
            'ID', 'User ID', 'Nama Mahasiswa', 'Email', 'HP', 'Jenis Kelamin',
            'NIK', 'NISN', 'Tempat Lahir', 'Tanggal Lahir', 'Alamat',
            'Program Studi (Pilihan 1)', 'Jalur Pendaftaran',
            'Status Pembayaran', 'Status E-Reg', 'Status Data', 'Dibuat'
        ]);

        foreach ($data as $row) {
            fputcsv($handle, [
                $row->id,
                $row->user_id,
                $row->nama_mahasiswa,
                $row->user->email ?? '-',
                $row->handphone,
                $row->jenis_kelamin,
                $row->nik,
                $row->nisn,
                $row->tempat_lahir,
                $row->tanggal_lahir,
                trim($row->jalan . ', ' . $row->dusun . ', ' . $row->kelurahkan, ', '),
                $row->masterEreg->jurusan1_label ?? 'Manajemen S2', // sesuaikan jika ada relasi
                $row->masterEreg->jalur_pendaftaran ?? '-',
                $row->masterUser->va_status ?? '-',
                $row->masterEreg->status ?? '-',
                $row->status_data,
                $row->created_at->format('d-m-Y H:i'),
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $dataS2    = DataMahasiswaFinal::findOrFail($id);
        $validated = $request->validate([
            'status_data' => 'required|in:completed,pending,rejected',
        ]);
        $dataS2->update($validated);

        return back()->with('success', 'Status data berhasil diperbarui');
    }
}