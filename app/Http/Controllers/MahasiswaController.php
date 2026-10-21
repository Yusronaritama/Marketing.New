<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MahasiswaController extends Controller
{
    public function registerStep()
    {
        // Step aktif (sementara 1)
        $steps = ['Data Diri', 'Upload PDF', 'Addons', 'Payment', 'Selesai'];
        $active = 1;

        return view('mahasiswa.register-step', compact('steps', 'active'));
    }
}
