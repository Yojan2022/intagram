<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Models\Friendship;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    // obtener conversación
    public function index(User $user)
    {
        $me = auth()->id();

        $messages = Message::where(function($q) use ($me, $user) {

            $q->where('sender_id', $me)
              ->where('receiver_id', $user->id);

        })->orWhere(function($q) use ($me, $user) {

            $q->where('sender_id', $user->id)
              ->where('receiver_id', $me);

        })
        ->with('sender.profile')
        ->orderBy('created_at')
        ->get();

        return response()->json($messages);
    }

    // enviar mensaje
    public function store(Request $request, User $user)
    {
        $request->validate([
            'message' => 'required'
        ]);

        // verificar amistad en ambas direcciones
        $isFriend = Friendship::where(function($q) use ($user) {

            $q->where('user_id', auth()->id())
              ->where('friend_id', $user->id);

        })->orWhere(function($q) use ($user) {

            $q->where('user_id', $user->id)
              ->where('friend_id', auth()->id());

        })
        ->where('status', 'accepted')
        ->exists();

        if (!$isFriend) {

            return response()->json([
                'message' => 'No son amigos'
            ], 403);

        }

        $message = Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $user->id,
            'message' => $request->message
        ]);

        return response()->json(
            $message->load('sender.profile')
        );
    }
}