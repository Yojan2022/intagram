<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Friendship;
use App\Models\User;

class FriendshipController extends Controller
{
     public function send(Request $request, User $user)
    {
        if ($user->id == $request->user()->id) {
            return response()->json(['message'=>'No puedes agregarte'], 400);
        }

        $friendship = Friendship::firstOrCreate([
            'user_id' => $request->user()->id,
            'friend_id' => $user->id,
        ], [
            'status' => 'pending'
        ]);

        return $friendship;
    }

    public function accept(Request $request, Friendship $friendship)
    {
        // solo el receptor puede aceptar
        if ($friendship->friend_id !== $request->user()->id) {
            return response()->json(['message'=>'No autorizado'], 403);
        }

        $friendship->update(['status'=>'accepted']);

        return $friendship;
    }

    public function myFriends(Request $request)
{
    $userId = $request->user()->id;

    $friendships = Friendship::with(['user.profile', 'friend.profile'])
        ->where(function($q) use ($userId) {

            $q->where('user_id', $userId)
              ->orWhere('friend_id', $userId);

        })
        ->where('status', 'accepted')
        ->get();

    $friends = $friendships->map(function($f) use ($userId) {

        return $f->user_id == $userId
            ? $f->friend
            : $f->user;

    });

    return response()->json($friends->values());
}

    public function pending(Request $request)
    {
        return \App\Models\Friendship::with('user')
            ->where('friend_id', $request->user()->id)
            ->where('status', 'pending')
            ->get();
    }
}
