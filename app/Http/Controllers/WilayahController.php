<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Wajib dipanggil untuk Query Builder

class WilayahController extends Controller
{
    // 1. Ambil Semua Provinsi
    public function getProvinces()
    {
        // Mengambil semua data provinsi, diurutkan berdasarkan nama (A-Z)
        $provinces = DB::table('provinces')
            ->select('id', 'name')
            ->orderBy('name', 'asc')
            ->get();
            
        return response()->json($provinces);
    }

    // 2. Ambil Kota/Kabupaten berdasarkan ID Provinsi
    public function getCities($province_id)
    {
        // Mengambil kota yang province_id-nya sesuai dengan yang dipilih
        $cities = DB::table('cities')
            ->where('province_id', $province_id)
            ->select('id', 'name')
            ->orderBy('name', 'asc')
            ->get();
            
        return response()->json($cities);
    }

    // 3. Ambil Kecamatan berdasarkan ID Kota
    public function getDistricts($city_id)
    {
        // Mengambil kecamatan yang city_id-nya sesuai dengan yang dipilih
        $districts = DB::table('districts')
            ->where('city_id', $city_id)
            ->select('id', 'name')
            ->orderBy('name', 'asc')
            ->get();
            
        return response()->json($districts);
    }
}