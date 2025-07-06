<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index()
    {
        return view('pages.v_chat', [
            'page' => 'Messages'
        ]);
    }

    public function getUsers()
    {
        $currentUserId = Auth::id();

        $users = User::where('id', '!=', $currentUserId)
            ->with(['userLog' => function ($query) {
                $query->latest('logged_at')->limit(1);
            }])
            ->get()
            ->map(function ($user) use ($currentUserId) {
                // Get last user log entry
                $lastLog = $user->userLog->first();

                $isOnline = $lastLog && $lastLog->event === 'login';

                $lastMessage = Message::where(function ($query) use ($currentUserId, $user) {
                    $query->where('sender_id', $currentUserId)
                        ->where('recipient_id', $user->id);
                })
                    ->orWhere(function ($query) use ($currentUserId, $user) {
                        $query->where('sender_id', $user->id)
                            ->where('recipient_id', $currentUserId);
                    })
                    ->latest()
                    ->first();

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'profile' => $user->profile,
                    'online' => $isOnline,
                    'last_message' => $lastMessage ? [
                        'text' => $lastMessage->text,
                        'created_at' => $lastMessage->created_at->toDateTimeString()
                    ] : null
                ];
            });

        return response()->json($users);
    }

    public function getMessages($recipientId)
    {
        $messages = Message::where(function ($q) use ($recipientId) {
            $q->where('sender_id', Auth::id())
                ->where('recipient_id', $recipientId);
        })->orWhere(function ($q) use ($recipientId) {
            $q->where('sender_id', $recipientId)
                ->where('recipient_id', Auth::id());
        })->orderBy('created_at')->get();

        return response()->json($messages);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'recipient_id' => 'required|integer|exists:users,id',
            'text' => 'nullable|string',
        ]);

        $message = Message::create([
            'sender_id' => Auth::id(),
            'recipient_id' => $request->recipient_id,
            'text' => $request->text,
            'is_file' => null, // handle file later
        ]);

        return response()->json($message);
    }
}
