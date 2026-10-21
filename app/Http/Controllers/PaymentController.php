<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    // Untuk Admin: Melihat semua yang sudah bayar
    public function index()
    {
        $users = User::whereNotNull('va_number')
                     ->orderBy('updated_at', 'desc')
                     ->paginate(10);

        return view('admin.payments.index', compact('users'));
    }

    // Untuk Mahasiswa: Melihat status miliknya sendiri
    public function checkStatus()
    {
        $user = Auth::user(); // Mengambil data user yang sedang login
        return view('pembayaran.status', compact('user'));
    }
}