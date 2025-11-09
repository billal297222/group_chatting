<?php

namespace App\Http\Controllers;

use App\Events\ChatMessageSent;
use App\Models\ChatMessage;
use App\Models\ChatRoom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index()
    {
        $rooms = ChatRoom::with(['users', 'messages'])->get();
        $userRooms = Auth::user()->chatRooms;

        return view('chat.index', compact('rooms', 'userRooms'));
    }

    public function show(ChatRoom $room)
    {
        if (!$room->isMember(Auth::user())) {
            return redirect()->route('chat.index')->with('error', 'You are not a member of this room.');
        }

        $messages = $room->messages()->with('user')->orderBy('created_at', 'asc')->get();
        $rooms = Auth::user()->chatRooms;

        return view('chat.room', compact('room', 'messages', 'rooms'));
    }

    public function createRoom(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $room = ChatRoom::create([
            'name' => $request->name,
            'description' => $request->description,
            'is_private' => $request->boolean('is_private', false)
        ]);

        $room->addUser(Auth::user());

        return redirect()->route('chat.room.show', $room)->with('success', 'Room created successfully!');
    }

    public function joinRoom(ChatRoom $room)
    {
        $room->addUser(Auth::user());

        return redirect()->route('chat.room.show', $room)->with('success', 'Joined room successfully!');
    }

    public function leaveRoom(ChatRoom $room)
    {
        $room->removeUser(Auth::user());

        return redirect()->route('chat.index')->with('success', 'Left room successfully!');
    }

    public function sendMessage(Request $request, ChatRoom $room)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        if (!$room->isMember(Auth::user())) {
            return back()->with('error', 'You are not a member of this room.');
        }

        $chatMessage = ChatMessage::create([
            'chat_room_id' => $room->id,
            'user_id' => Auth::id(),
            'message' => $request->message
        ]);

        $chatMessage->load('user');

        broadcast(new ChatMessageSent($chatMessage));

        return back()->with('success', 'Message sent!');
    }
}
