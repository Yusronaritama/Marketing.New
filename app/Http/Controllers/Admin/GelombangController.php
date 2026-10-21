<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GelombangController extends Controller
{
    public function index()
    {
        $data = DB::table('users')
            ->join('master_users', 'users.id', '=', 'master_users.user_id')
            ->select('users.name', 'master_users.gelombang', 'users.id', 'users.email', 'users.role', 'users.phone')
            ->where('role', 'mahasiswa_baru')
            ->get();
        return view('admin.gelombang.index', compact('data'));
    }

public function updateGelombang(Request $request, $id)
{
    $request->validate([
        'gelombang' => 'required|integer|min:1|max:10',
    ]);

    $user = MasterUser::where('user_id', $id)->firstOrFail();
    $user->update(['gelombang' => $request->gelombang]);

    return redirect()->route('admin.gelombang.index')
                     ->with('success', 'Gelombang berhasil diperbarui.');
}
}