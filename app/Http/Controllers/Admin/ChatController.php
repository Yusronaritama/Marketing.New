<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chat_Users;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
 public function index()
    {
        $user = auth()->user();
        $selectedChatId = request()->get('chat_id');
        $mahasiswa = collect();

        if ($user->role == 'admin') {
            // Ambil semua ID chat yang unik
            $existingChatIds = Chat_Users::distinct('chat_id')->pluck('chat_id');
            $userIds = [];

            foreach ($existingChatIds as $cid) {
                $parts = explode('_', $cid);
                if (count($parts) == 3) {
                    $id1 = (int)$parts[1];
                    $id2 = (int)$parts[2];
                    // Ambil ID lawan bicara
                    if ($id1 != $user->id) $userIds[] = $id1;
                    elseif ($id2 != $user->id) $userIds[] = $id2;
                }
            }

            $mahasiswa = User::whereIn('id', array_unique($userIds))->get();

            // Loop untuk attach data tambahan (pesan terakhir & unread count)
            foreach ($mahasiswa as $mhs) {
                $chatId = 'chat_' . min($user->id, $mhs->id) . '_' . max($user->id, $mhs->id);
                
                // Ambil pesan terakhir
                $lastMessage = Chat_Users::where('chat_id', $chatId)->latest('created_at')->first();
                $mhs->last_message = $lastMessage ? $lastMessage->chat_text : '';
                $mhs->last_time = $lastMessage ? $lastMessage->created_at : null;

                // HITUNG UNREAD: Pesan di chat ini, BUKAN dari saya, dan status is_read 0
                $mhs->unread_count = Chat_Users::where('chat_id', $chatId)
                    ->where('email', '!=', $user->email) 
                    ->where('is_read', 0)
                    ->count();
            }

            // Urutkan: Yang ada unread di atas, lalu berdasarkan waktu terbaru
            $mahasiswa = $mahasiswa->sort(function ($a, $b) {
                if ($a->unread_count != $b->unread_count) {
                    return $b->unread_count <=> $a->unread_count; // Unread banyak di atas
                }
                return $b->last_time <=> $a->last_time; // Waktu terbaru di atas
            })->values();
        }

        return view('admin.chat.index', compact('user', 'mahasiswa', 'selectedChatId'));
    }
    public function store(Request $request)
    {
      try {
            $request->validate(['chat_text' => 'required|string', 'chat_id' => 'required|string']);
            $user = auth()->user();

            Chat_Users::create([
                'name'      => $user->name,
                'email'     => $user->email,
                'chat_id'   => $request->chat_id,
                'chat_text' => $request->chat_text,
                'role'      => $user->role ?? 'user',
                'is_read'   => false, // Default belum dibaca
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

  public function fetch(Request $request)
    {
        try {
            $chatId = $request->get('chat_id');
            if (!$chatId) return response()->json([]);

            // MARK AS READ: Update pesan dari lawan bicara menjadi sudah dibaca
            Chat_Users::where('chat_id', $chatId)
                ->where('email', '!=', auth()->user()->email)
                ->update(['is_read' => 1]);

            $chats = Chat_Users::where('chat_id', $chatId)
                ->orderBy('created_at', 'asc')
                ->get();
            
            return response()->json($chats);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getOrCreateChatId($userId)
    {
        try {
            $user = auth()->user();
            $targetUser = User::findOrFail($userId);
            
            $chatId = 'chat_' . min($user->id, $targetUser->id) . '_' . max($user->id, $targetUser->id);
            
            return response()->json([
                'chat_id' => $chatId,
                'success' => true
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}