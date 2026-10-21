<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PostRegisterController extends Controller
{
    /**
     * Menampilkan halaman setelah registrasi selesai.
     */
    public function index()
    {
        return view('mahasiswa.post-register');
    }
}
