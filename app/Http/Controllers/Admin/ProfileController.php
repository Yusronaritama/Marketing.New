<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    // Menampilkan halaman profile admin
    public function index()
    {
        $user = Auth::user(); // ambil data user yang login
        return view('admin.profile', compact('user'));
    }

    // Update profile admin
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:20',
            // HAPUS validasi untuk bio
            // 'bio' => 'nullable|string|max:1000', // DIHAPUS
            'profile_photo' => 'nullable|image|max:2048', // opsional
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        // HAPUS penyimpanan bio
        // $user->bio = $request->bio; // DIHAPUS

        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');
            $filename = time().'_'.$file->getClientOriginalName();
            $file->storeAs('public/profile', $filename);
            $user->profile_photo = $filename;
        }

        $user->save();

        return redirect()->back()->with('success', 'Profile berhasil diperbarui!');
    }
}