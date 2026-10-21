<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Biodata;
use App\Models\User;
use App\Models\MasterUser; 
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class BiodataController extends Controller
{
    /**
     * Display a listing of the biodata.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        
        // Query untuk mengambil SEMUA data biodata dengan relasi user
        $biodatas = Biodata::with(['user' => function($query) {
                $query->select('id', 'name', 'email'); // Hanya ambil kolom yang diperlukan
            }])
            ->when($search, function($query) use ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('nama_lengkap', 'like', "%{$search}%")
                      ->orWhere('asal_sekolah', 'like', "%{$search}%")
                      ->orWhere('jurusan_sekolah', 'like', "%{$search}%")
                      ->orWhere('tempat_lahir', 'like', "%{$search}%")
                      ->orWhere('nama_ibu', 'like', "%{$search}%")
                      ->orWhere('nama_ayah', 'like', "%{$search}%")
                      ->orWhere('agama', 'like', "%{$search}%")
                      ->orWhereHas('user', function($userQuery) use ($search) {
                          $userQuery->where('email', 'like', "%{$search}%")
                                   ->orWhere('name', 'like', "%{$search}%");
                      });
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $totalBiodata = Biodata::count();
        
        // Ambil total mahasiswa dari master_users dengan va_status = 'paid'
        $totalUsers = MasterUser::where('va_status', 'paid')->count();
        
        $completionPercentage = $totalUsers > 0 ? round(($totalBiodata / $totalUsers) * 100, 1) : 0;

        return view('admin.biodata.index', compact(
            'biodatas',
            'totalBiodata',
            'totalUsers',
            'completionPercentage',
            'search'
        ));
    }

    /**
     * Display the specified biodata.
     */
    public function show(string $id): View
    {
        $biodata = Biodata::with(['user' => function($query) {
                $query->select('id', 'name', 'email', 'created_at');
            }])
            ->findOrFail($id);
        
        return view('admin.biodata.show', compact('biodata'));
    }

    /**
     * Show the form for editing the specified biodata.
     */
    public function edit(string $id): View
    {
        $biodata = Biodata::with(['user' => function($query) {
                $query->select('id', 'name', 'email');
            }])
            ->findOrFail($id);
        
        // Ambil semua jenjang pendidikan yang ada di database
        $jenjangPendidikan = $this->getJenjangPendidikanOptions();
        
        return view('admin.biodata.edit', compact('biodata', 'jenjangPendidikan'));
    }

    /**
     * Update the specified biodata in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $biodata = Biodata::findOrFail($id);

        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'tempat_lahir' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'nama_ibu' => 'required|string|max:255',
            'nama_ayah' => 'required|string|max:255',
            'alamat' => 'required|string',
            'jenjang_pendidikan' => 'required|string|max:255',
            'asal_sekolah' => 'required|string|max:255',
            'jurusan_sekolah' => 'required|string|max:255',
            'agama' => 'required|string|max:255',
            'tempat_tinggal' => 'required|string|max:255',
            'is_verified' => 'sometimes|boolean',
        ]);

        $biodata->update($validated);

        return redirect()->route('admin.biodata.index')
            ->with('success', 'Biodata berhasil diperbarui.');
    }

    /**
     * Remove the specified biodata from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $biodata = Biodata::findOrFail($id);
        $biodata->delete();

        return redirect()->route('admin.biodata.index')
            ->with('success', 'Biodata berhasil dihapus.');
    }

    /**
     * Export biodata to Excel
     */
    public function export(Request $request)
    {
        $search = $request->input('search');
        
        $biodatas = Biodata::with(['user' => function($query) {
                $query->select('id', 'name', 'email');
            }])
            ->when($search, function($query) use ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('nama_lengkap', 'like', "%{$search}%")
                      ->orWhere('asal_sekolah', 'like', "%{$search}%")
                      ->orWhere('jurusan_sekolah', 'like', "%{$search}%")
                      ->orWhereHas('user', function($userQuery) use ($search) {
                          $userQuery->where('email', 'like', "%{$search}%");
                      });
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Implementasi export Excel di sini
        // Anda bisa menggunakan package seperti Maatwebsite/Laravel-Excel
    }

    /**
     * Verify biodata
     */
    public function verify(string $id): RedirectResponse
    {
        $biodata = Biodata::findOrFail($id);
        $biodata->update(['is_verified' => true]);

        return redirect()->back()
            ->with('success', 'Biodata berhasil diverifikasi.');
    }

    /**
     * Unverify biodata
     */
    public function unverify(string $id): RedirectResponse
    {
        $biodata = Biodata::findOrFail($id);
        $biodata->update(['is_verified' => false]);

        return redirect()->back()
            ->with('success', 'Biodata berhasil dibatalkan verifikasinya.');
    }

    /**
     * Quick update status verification
     */
    public function updateStatus(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        try {
            $biodata = Biodata::findOrFail($id);
            
            $request->validate([
                'is_verified' => 'required|boolean'
            ]);

            $biodata->update([
                'is_verified' => $request->is_verified
            ]);

            return response()->json([
                'success' => true,
                'message' => $request->is_verified ? 'Biodata berhasil diverifikasi' : 'Status biodata diubah menjadi pending',
                'is_verified' => $biodata->is_verified
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get unique jenjang pendidikan options from database
     */
    private function getJenjangPendidikanOptions(): array
    {
        // Ambil semua jenjang pendidikan unik dari database
        $jenjangFromDB = Biodata::whereNotNull('jenjang_pendidikan')
            ->where('jenjang_pendidikan', '!=', '')
            ->distinct()
            ->pluck('jenjang_pendidikan')
            ->toArray();

        // Default options jika tidak ada data di database
        $defaultOptions = ['SMA', 'SMK', 'MA', 'Paket C'];
        
        // Gabungkan dan hapus duplikat
        $allOptions = array_unique(array_merge($defaultOptions, $jenjangFromDB));
        
        // Urutkan secara alfabet
        sort($allOptions);
        
        return $allOptions;
    }

    /**
     * Get dynamic options for select fields from database
     */
    private function getDynamicOptions(string $column): array
    {
        return Biodata::whereNotNull($column)
            ->where($column, '!=', '')
            ->distinct()
            ->pluck($column)
            ->sort()
            ->toArray();
    }
}