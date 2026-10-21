<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\SlikBillingExport;
use Maatwebsite\Excel\Facades\Excel;
class SlikBillingController extends Controller
{
    

// Tambahkan method ini di dalam class SlikBillingController
public function export(Request $request) 
{
    $fileName = 'rekap_pembayaran_ukt_' . date('Ymd_His') . '.xlsx';
    return Excel::download(new SlikBillingExport($request), $fileName);
}
public function index(Request $request)
{
    $query = DB::table('slik_billings')
        ->join('users', 'slik_billings.user_id', '=', 'users.id')
        ->select('slik_billings.*', 'users.name as nama_lengkap_user');

    $isDuplicateFilterActive = $request->filled('filter_duplicate');

    // Default sembunyikan 'cancel', KECUALI jika sedang memfilter data duplikat 
    // atau jika user secara eksplisit memilih filter status 'cancel'
    if (!$isDuplicateFilterActive && $request->status !== 'cancel') {
        $query->where('slik_billings.status', '!=', 'cancel');
    }

    // Filter berdasarkan Teks (Nama, VA, atau Order ID)
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('users.name', 'like', "%{$search}%")
              ->orWhere('slik_billings.virtual_account', 'like', "%{$search}%")
              ->orWhere('slik_billings.order_id', 'like', "%{$search}%");
        });
    }

    // Filter berdasarkan Status
    if ($request->filled('status')) {
        $query->where('slik_billings.status', $request->status);
    }

    // Filter berdasarkan Duplikasi (Data Double)
    if ($isDuplicateFilterActive) {
        if ($request->filter_duplicate === 'duplicate_user') {
            // Cari Mahasiswa (user_id) yang punya tagihan lebih dari 1
            $duplicateUsers = DB::table('slik_billings')
                ->select('user_id')
                ->groupBy('user_id')
                ->havingRaw('COUNT(id) > 1')
                ->pluck('user_id');
                
            $query->whereIn('slik_billings.user_id', $duplicateUsers);
            $query->orderBy('slik_billings.user_id', 'desc'); // Urutkan by user agar berdekatan

        } elseif ($request->filter_duplicate === 'duplicate_va') {
            // Cari nomor Virtual Account yang tercatat ganda di tabel
            $duplicateVAs = DB::table('slik_billings')
                ->select('virtual_account')
                ->whereNotNull('virtual_account')
                ->where('virtual_account', '!=', '')
                ->groupBy('virtual_account')
                ->havingRaw('COUNT(id) > 1')
                ->pluck('virtual_account');
                
            $query->whereIn('slik_billings.virtual_account', $duplicateVAs);
            $query->orderBy('slik_billings.virtual_account', 'desc'); // Urutkan by VA agar berdekatan
        }
    } else {
        // Default order
        $query->orderBy('slik_billings.created_at', 'desc');
    }

    // Fitur Per Page
    $perPage = $request->get('per_page', 25);
    if ($perPage === 'all') {
        $perPage = 1000000; 
    } else {
        $perPage = (int)$perPage;
    }

    $billings = $query->paginate($perPage);

    return view('admin.mandiri.ukt.index', compact('billings'));
}
public function updateStatus(Request $request, $id)
{
    $request->validate([
        'status' => 'required|in:paid,cancel,pending,expired',
    ]);

    $data = [
        'status' => $request->status,
        'updated_at' => now(),
    ];

    // Jika diubah ke paid, set waktu pembayaran ke sekarang
    if ($request->status === 'paid') {
        $data['datetime_payment'] = now();
        $data['is_active'] = 0; // Biasanya nonaktif setelah lunas
    }

    DB::table('slik_billings')->where('id', $id)->update($data);

    return redirect()->back()->with('success', 'Status pembayaran berhasil diperbarui ke ' . strtoupper($request->status));
}
    public function show($id)
    {
        $billing = DB::table('slik_billings')->where('id', $id)->first();

        if (!$billing) {
            abort(404);
        }

        return view('admin.mandiri.ukt.show', compact('billing'));
    }
}