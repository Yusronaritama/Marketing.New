<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserBiodataController extends Controller
{
    public function index()
    {
        // 1. Ambil SEMUA user (tanpa filter has('biodata') agar yang belum isi juga kena)
        // 2. Filter: Harus BELUM mengisi master_ereg (doesntHave)
        $users = User::doesntHave('masterEreg') 
                    ->with('biodata') // Tetap eager load untuk mengecek status di Blade
                    ->where('role', '!=', 'admin')
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);

        return view('admin.users.only_biodata', compact('users'));
    }
}