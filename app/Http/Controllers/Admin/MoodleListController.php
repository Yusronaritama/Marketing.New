<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MoodleListController extends Controller
{
    /**
     * Display admin dashboard page
     */
    public function index()
    {
        return view('admin.moodle.index');
    }
 public function show($id)
    {
        try {
            // Ambil data dari API
            $response = Http::get("http://pmb.ubpkarawang.ac.id/api/moodle/users/{$id}/complete");
            
            if ($response->successful()) {
                $result = $response->json();
                
                // Pastikan struktur data sesuai
                if (isset($result['success']) && $result['success'] === true) {
                    $data = $result['data'];
                    return view('Admin.moodle.show', compact('data'));
                } else {
                    return back()->with('error', 'Format data tidak valid dari API');
                }
            } else {
                return back()->with('error', 'Gagal mengambil data dari API. Status: ' . $response->status());
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}