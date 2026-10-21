<?php

namespace App\Http\Controllers\Mahasiswa\Profesi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SuratProfesiController extends Controller
{
    public function pkms()
    {
        return view('mahasiswa.profesi.surat.PKMS');
    }

    public function pkmu()
    {
        return view('mahasiswa.profesi.surat.PKMU');
    }

    public function pkpa()
    {
        return view('mahasiswa.profesi.surat.PKPA');
    }
}