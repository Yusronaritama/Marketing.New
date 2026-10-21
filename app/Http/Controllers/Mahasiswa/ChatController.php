<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Chat_Users;
use Illuminate\Http\Request;
use App\Models\User;

class ChatController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Generate chat_id untuk mahasiswa dengan admin
        // Asumsi admin memiliki ID 14 (sesuaikan dengan ID admin Anda)
        $adminId = 14; // Ganti dengan ID admin yang sesuai
        $chatId = 'chat_' . min($user->id, $adminId) . '_' . max($user->id, $adminId);
        
        return view('mahasiswa.chat.index', compact('user', 'chatId'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'chat_text' => 'required|string|max:255',
                'chat_id' => 'required|string'
            ]);

            $user = auth()->user();

            Chat_Users::create([
                'name'      => $user->name,
                'email'     => $user->email,
                'chat_id'   => $request->chat_id,
                'chat_text' => $request->chat_text,
                'role'      => $user->role ?? 'mahasiswa_baru',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pesan berhasil dikirim'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function fetch(Request $request)
    {
        try {
            $chatId = $request->get('chat_id');
            
            if (!$chatId) {
                return response()->json([]);
            }

            $chats = Chat_Users::where('chat_id', $chatId)
                ->orderBy('created_at', 'asc')
                ->get();
            
            return response()->json($chats);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch chats: ' . $e->getMessage()
            ], 500);
        }
    }
}