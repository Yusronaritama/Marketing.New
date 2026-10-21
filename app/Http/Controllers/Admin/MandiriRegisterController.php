<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\MandiriRegisterExport;
use Maatwebsite\Excel\Facades\Excel;

class MandiriRegisterController extends Controller
{
    public function index(Request $request)
    {
        // 1. Query Dasar
        $baseQuery = DB::table('master_users as mu')
            ->join('users as u', 'mu.user_id', '=', 'u.id')
            ->leftJoin('promo_codes as pc', 'mu.promo_code_used', '=', 'pc.code')
            ->where('mu.va_number', 'like', '896%');

        // 2. Hitung Total Statistik (Hanya untuk 285.000 dan status PAID)
        $totalSpecificAmount = (clone $baseQuery)
            ->where('mu.va_amount', 285000)
            ->where('mu.va_status', 'paid')
            ->count();

        // 3. Query Utama untuk Tabel Data
        $query = (clone $baseQuery)
            ->select('mu.*', 'u.name', 'u.email', 'pc.description as school_name');

        // --- PERBAIKAN FILTER STATUS ---
        // Jika ada parameter 'status' (berarti form filter baru saja di-submit)
        if ($request->has('status')) {
            // Jika status tidak kosong (misal dipilih 'paid' atau 'pending')
            if ($request->filled('status')) {
                $query->where('mu.va_status', $request->status);
            }
            // Jika kosong ("Semua Status"), biarkan saja agar semua data muncul
        } else {
            // Jika halaman baru dibuka dari menu (belum ada filter yang disubmit), default PAID
            $query->where('mu.va_status', 'paid');
        }

        // --- FILTER NOMINAL (Jika diklik dari dashboard) ---
        if ($request->get('amount') == '285000') {
            $query->where('mu.va_amount', 285000);
        }

        // --- FILTER PENCARIAN ---
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('u.name', 'like', "%{$search}%")
                  ->orWhere('mu.va_number', 'like', "%{$search}%")
                  ->orWhere('mu.trx_id', 'like', "%{$search}%");
            });
        }

        // --- FITUR PER PAGE ---
        $perPage = $request->get('per_page', 25);
        if ($perPage === 'all') {
            $perPage = 1000000; 
        } else {
            $perPage = (int)$perPage;
        }

        $registers = $query->orderBy('mu.created_at', 'desc')->paginate($perPage);

        return view('admin.mandiri.register.index', compact('registers', 'totalSpecificAmount'));
    }

    public function markAsPaid($id)
    {
        $current = DB::table('master_users')->where('id', $id)->first();
        if (!$current) abort(404);

        DB::table('master_users')->where('id', $id)->update([
            'va_status' => 'paid',
            'paid_at' => now(),
            'payment_date' => now(),
            'payment_amount' => $current->va_amount,
            'updated_at' => now()
        ]);

        return back()->with('success', 'Pembayaran berhasil dikonfirmasi secara manual.');
    }

    public function export(Request $request) 
    {
        // Memaksa parameter amount menjadi 285000 secara otomatis untuk di Excel
        $request->merge(['amount' => '285000']);

        $fileName = 'Data_Pendaftar_Promo_285K_'.now()->format('Y-m-d_His').'.xlsx';
        return Excel::download(new MandiriRegisterExport($request), $fileName);
    }

    public function show($id)
    {
        $data = DB::table('master_users as mu')
            ->join('users as u', 'mu.user_id', '=', 'u.id')
            ->leftJoin('promo_codes as pc', 'mu.promo_code_used', '=', 'pc.code')
            ->select('mu.*', 'u.name', 'u.email', 'pc.description as school_name')
            ->where('mu.id', $id)
            ->first();

        if (!$data) abort(404);

        return view('admin.mandiri.register.show', compact('data'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:paid,pending,cancel,expired']);

        $updateData = [
            'va_status' => $request->status,
            'updated_at' => now()
        ];

        if ($request->status === 'paid') {
            $updateData['paid_at'] = now();
            $updateData['payment_date'] = now();
            
            $current = DB::table('master_users')->where('id', $id)->first();
            if ($current) {
                $updateData['payment_amount'] = $current->va_amount;
            }
        } else {
            $updateData['paid_at'] = null;
            $updateData['payment_date'] = null;
        }

        DB::table('master_users')->where('id', $id)->update($updateData);

        return redirect()->route('admin.mandiri.register.index')->with('success', 'Status registrasi berhasil diperbarui.');
    }
}