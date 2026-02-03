<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $messages = Message::with(['sender:id,username,avatar,role,created_at', 'purchase.gameKey.game'])
            ->where(function ($query) use ($userId) {
                $query->where('sender_id', $userId)
                      ->orWhere('receiver_id', $userId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $unreadCount = Message::where('receiver_id', $userId)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'messages' => $messages,
            'unread_count' => $unreadCount
        ]);
    }

    public function show($id)
    {
        $message = Message::with(['sender:id,username,avatar,role,created_at', 'purchase.gameKey.game'])
            ->findOrFail($id);

        $userId = auth()->id();

        if ($message->sender_id !== $userId && $message->receiver_id !== $userId) {
             return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($message->receiver_id == $userId && !$message->is_read) {
            $message->markAsRead();
        }

        return response()->json($message);
    }

    public function markAsRead($id)
    {
        $message = Message::findOrFail($id);
        
        if ($message->receiver_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $message->markAsRead();

        return response()->json(['success' => true]);
    }

    public function unreadCount()
    {
        $count = Message::where('receiver_id', auth()->id())
            ->where('is_read', false)
            ->count();
            
        return response()->json(['count' => $count]);
    }
}