<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Models\User;
use App\Models\Biodata;
use App\Models\SlikBilling;
use Illuminate\Http\Request;
use App\Models\MasterUser; 
use App\Models\Targets;
use Carbon\Carbon;

class DashboardController extends Controller
{
public function index(Request $request): View
{
    $user = Auth::user();
    
    // 1. Statistik Dasar
    $userCount = User::count();
    $totalUsersMahasiswa = User::where('role', 'mahasiswa_baru')->count();
    $totalUsersWithBiodata = Biodata::count();
    
    $biodataCompletionPercentage = $totalUsersMahasiswa > 0 
        ? round(($totalUsersWithBiodata / $totalUsersMahasiswa) * 100, 1)
        : 0;

    // 2. Ambil Tahun & Data Chart
    $availableYears = MasterUser::select(DB::raw('DISTINCT YEAR(created_at) as year'))
        ->orderBy('year', 'asc')
        ->pluck('year')
        ->toArray();

    $chartData = [];
    $currentYear = !empty($availableYears) ? max($availableYears) : date('Y');
    $targets = Targets::first();
    $targetUsers = $targets ? $targets->target : 0;
    $usersThisYear = MasterUser::whereYear('created_at', $currentYear)->count();
    $userPercentage = $targetUsers > 0 ? ($usersThisYear / $targetUsers) * 100 : 0;

    if (!empty($availableYears)) {
        foreach ($availableYears as $year) {
            $monthlyCounts = [];
            for ($month = 1; $month <= 12; $month++) {
                $count = MasterUser::whereYear('created_at', $year)
                    ->where('gelombang', $month)
                    ->count();
                $monthlyCounts[] = $count;
            }
            $chartData[] = [
                'year' => $year,
                'data' => $monthlyCounts,
            ];
        }
    }

    // 3. Logika Slik Billing
    $paidSlik = DB::table('slik_billings')->where('status', 'paid')->count();
    $totalSlikBillings = DB::table('slik_billings')->count();
    $unpaidSlik = $totalSlikBillings - $paidSlik;
    $totalRevenue = DB::table('slik_billings')->where('status', 'paid')->sum('amount');

    // 4. LOGIKA UTAMA: Filter & Pendapatan Registrasi (Excluding Gelombang 1)
    $selectedGelombang = $request->get('gelombang');

    // Inisialisasi Query Dasar
    $paidQuery = MasterUser::where('va_status', 'paid');
    $totalQuery = MasterUser::query();

    // Terapkan Filter jika ada
    if ($selectedGelombang) {
        $paidQuery->where('gelombang', $selectedGelombang);
        $totalQuery->where('gelombang', $selectedGelombang);
    }

    // Hitung Jumlah Orang
    $paidUsers = $paidQuery->count();
    $totalUsersWithVa = $totalQuery->count();

    // Hitung Nominal dengan IF-ELSE (Kecualikan Gelombang 1)
    $usersForRevenue = $paidQuery->get(); 
    $totalRevenueRegister = 0;

    foreach ($usersForRevenue as $data) {
        // Jika kolom gelombang adalah '1', maka nominal dianggap 0
        if ($data->gelombang == '1') {
            $nominal = 0;
        } else {
            $nominal = $data->va_amount;
        }
        $totalRevenueRegister += $nominal;
    }

    // 5. Statistik Tambahan & Dropdown
    $unpaidUsers = $totalUsersWithVa - $paidUsers;
    $unpaidPercentage = $totalUsersWithVa > 0 ? ($unpaidUsers / $totalUsersWithVa) * 100 : 0;

    $listGelombang = MasterUser::select('gelombang')
        ->distinct()
        ->orderBy('gelombang', 'asc')
        ->pluck('gelombang');

    $totalBiodata = Biodata::count();
    $totalUsersPaid = MasterUser::where('va_status', 'paid')->count();
    $completionPercentage = $totalUsersPaid > 0 ? round(($totalBiodata / $totalUsersPaid) * 100, 1) : 0;

    // 6. Data Recent Activities & Registrations
    $recentRegistrations = User::where('role', 'mahasiswa_baru')
        ->orderBy('created_at', 'desc')
        ->limit(25)
        ->get();

    $recentActivities = [
        (object)['title' => 'Pendaftaran Baru', 'description' => 'Mahasiswa baru telah mendaftar', 'created_at' => Carbon::now()->subMinutes(5)],
        (object)['title' => 'Pembayaran Diterima', 'description' => 'Pembayaran pendaftaran telah diverifikasi', 'created_at' => Carbon::now()->subHours(1)],
    ];

    return view('admin.dashboard.index', compact(
        'user', 'userCount', 'userPercentage', 'usersThisYear', 'targetUsers',
        'chartData', 'availableYears', 'currentYear', 'paidUsers', 'unpaidPercentage',
        'recentRegistrations', 'totalUsersWithBiodata', 'biodataCompletionPercentage',
        'totalUsersMahasiswa', 'completionPercentage', 'recentActivities',
        'paidSlik', 'unpaidSlik', 'totalRevenue', 'totalRevenueRegister',
        'listGelombang', 'selectedGelombang'
    ));
}
    public function target(Request $request)
    {
        $target = Targets::first('target');
        return view('admin.dashboard.target', compact('target'));
    }

    public function targetUpdate(Request $request)
    {
        $request->validate(['target' => 'required|integer|min:0']);
        $nama = auth()->user()->name;
        $target = Targets::first();

        


        if ($target) {
            $target->update([
                'target' => $request->target,
                'nama' => $nama
            ]);
        } else {
            Targets::create([
                'target' => $request->target,
                'nama' => $nama
            ]);
        }
        
        return redirect()->route('admin.dashboard.target')->with('success', 'Target berhasil disimpan!');
    }

    public function filterHistory(Request $request)
    {
        try {
            $query = User::where('role', 'mahasiswa_baru');

            // Filter berdasarkan status pembayaran
            if ($request->filled('payment_status')) {
                if ($request->payment_status === 'paid') {
                    $query->whereHas('masterUser', function($q) {
                        $q->where('va_status', 'paid');
                    });
                } elseif ($request->payment_status === 'unpaid') {
                    $query->where(function($q) {
                        $q->whereHas('masterUser', function($q2) {
                            $q2->where('va_status', '!=', 'paid')
                               ->orWhereNull('va_status');
                        })->orWhereDoesntHave('masterUser');
                    });
                }
            }

            // Filter berdasarkan bulan
            if ($request->filled('month')) {
                $query->whereMonth('created_at', $request->month);
            }

            $recentRegistrations = $query->orderBy('created_at', 'desc')->limit(25)->get();

            $html = '';
            if ($recentRegistrations->count() > 0) {
                foreach ($recentRegistrations as $registration) {
                    $paymentStatus = $registration->masterUser->va_status ?? 'unpaid';
                    $html .= '
                    <tr class="border-b border-indigo-100 dark:border-gray-800 hover:bg-indigo-50/50 dark:hover:bg-white/[0.02] transition-all">
                        <td class="px-4 py-4 xl:pl-6">
                            <p class="font-medium text-gray-900 dark:text-white/90">'.($registration->name ?? 'N/A').'</p>
                        </td>
                        <td class="px-4 py-4">
                            <p class="text-gray-600 dark:text-gray-400">'.($registration->email ?? 'N/A').'</p>
                        </td>
                        <td class="px-4 py-4">';
                    
                    if ($paymentStatus == 'paid') {
                        $html .= '<span class="inline-flex rounded-full bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-500/15 dark:to-emerald-500/15 px-3 py-1 text-xs font-medium text-green-600 dark:text-green-400 cyber-glow">Lunas</span>';
                    } else {
                        $html .= '<span class="inline-flex rounded-full bg-gradient-to-r from-red-50 to-rose-50 dark:from-red-500/15 dark:to-rose-500/15 px-3 py-1 text-xs font-medium text-red-600 dark:text-red-400 cyber-glow">Pending</span>';
                    }
                    
                    $html .= '
                        </td>
                        <td class="px-4 py-4">
                            <p class="text-gray-600 dark:text-gray-400">'.($registration->created_at->format('d M Y') ?? 'N/A').'</p>
                        </td>
                        <td class="px-4 py-4">
                            <a href="'.route('admin.registrations.show', $registration->id ?? '#').'" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 font-medium">Detail</a>
                        </td>
                    </tr>';
                }
            } else {
                $html = '
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                        Tidak ada data pendaftaran
                    </td>
                </tr>';
            }

            return response()->json([
                'success' => true,
                'data' => $recentRegistrations,
                'html' => $html
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Method untuk data chart refresh
    public function getChartData()
    {
        $currentYear = date('Y');
        $availableYears = MasterUser::select(DB::raw('DISTINCT YEAR(created_at) as year'))
            ->orderBy('year', 'asc')
            ->pluck('year')
            ->toArray();

        $chartData = [];

        foreach ($availableYears as $year) {
            $monthlyCounts = [];
            
            for ($month = 1; $month <= 12; $month++) {
                $count = MasterUser::whereYear('created_at', $year)
                    ->where('gelombang', $month)
                    ->count();
                $monthlyCounts[] = $count;
            }

            $chartData[] = [
                'year' => $year,
                'data' => $monthlyCounts,
            ];
        }

        return response()->json([
            'currentYear' => $currentYear,
            'chartData' => $chartData
        ]);
    }

    // Method untuk export data
    public function exportData(Request $request)
    {
        try {
            $query = User::where('role', 'mahasiswa_baru');

            // Filter berdasarkan status pembayaran
            if ($request->filled('payment_status')) {
                if ($request->payment_status === 'paid') {
                    $query->whereHas('masterUser', function($q) {
                        $q->where('va_status', 'paid');
                    });
                } elseif ($request->payment_status === 'unpaid') {
                    $query->where(function($q) {
                        $q->whereHas('masterUser', function($q2) {
                            $q2->where('va_status', '!=', 'paid')
                               ->orWhereNull('va_status');
                        })->orWhereDoesntHave('masterUser');
                    });
                }
            }

            // Filter berdasarkan bulan
            if ($request->filled('month')) {
                $query->whereMonth('created_at', $request->month);
            }

            $data = $query->orderBy('created_at', 'desc')->get();

            // Header untuk CSV/Excel
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="data_pendaftaran_' . date('Y-m-d') . '.csv"',
            ];

            $callback = function() use ($data) {
                $file = fopen('php://output', 'w');
                
                // Header CSV
                fputcsv($file, ['Nama', 'Email', 'Status Pembayaran', 'Tanggal Daftar']);
                
                // Data
                foreach ($data as $item) {
                    $paymentStatus = $item->masterUser->va_status ?? 'unpaid';
                    fputcsv($file, [
                        $item->name ?? 'N/A',
                        $item->email ?? 'N/A',
                        $paymentStatus === 'paid' ? 'Lunas' : 'Pending',
                        $item->created_at->format('d M Y')
                    ]);
                }
                
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}