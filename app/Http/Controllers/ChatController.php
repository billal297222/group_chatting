<?php

namespace App\Http\Controllers;

use App\Events\ChatMessageSent;
use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Models\User;
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


public function show(ChatRoom $room, Request $request)
{
    if (! $room->isMember(Auth::user())) {
        return redirect()->route('chat.index')
            ->with('error', 'You are not a member of this room.');
    }

    $rooms = auth()->user()->chatRooms()->get();

    $messagesQuery = $room->messages()->with('user')->orderBy('created_at', 'asc');

    if ($request->ajax()) {
        $afterId = $request->query('after_id', 0);
        if ($afterId > 0) {
            $messagesQuery->where('id', '>', $afterId);
        }

        $messages = $messagesQuery->get();

        return response()->json([
            'messages' => $messages->map(fn($msg) => [
                'id' => $msg->id,
                'message' => $msg->message,
                'user' => [
                    'id' => $msg->user->id,
                    'name' => $msg->user->name,
                ],
                'created_at' => $msg->created_at->toDateTimeString(),
            ]),
        ]);
    }

    $messages = $messagesQuery->get();

    return view('chat.room', compact('room', 'rooms', 'messages'));
}



    public function createRoom(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_private' => 'boolean',
        ]);

        // dd($request->all());


        $room = ChatRoom::create([
            'name' => $request->name,
            'description' => $request->description,
            'is_private' => $request->boolean('is_private', false),
            'created_by' => Auth::id(),
        ]);


        $room->addUser(Auth::user());

        return redirect()->route('chat.room.show', $room)
            ->with('success', 'Room created successfully!');
    }


    public function joinRoom(ChatRoom $room)
    {
        $user = Auth::user();

        if ($room->is_private) {

            if ($room->created_by !== $user->id && ! $room->isMember($user)) {
                return redirect()->back()
                    ->with('error', 'This is a private room. Only the creator can add members.');
            }
        }

        $room->addUser($user);

        return redirect()->route('chat.room.show', $room);
    }


    public function leaveRoom(ChatRoom $room)
    {
        $room->removeUser(Auth::user());

        return redirect()->route('chat.index')->with('success', 'Left room successfully!');
    }


   public function sendMessage(Request $request, ChatRoom $room)
{
    $request->validate([
        'message' => 'required|string|max:1000',
    ]);

    if (! $room->isMember(Auth::user())) {
        return response()->json([
            'success' => false,
            'error' => 'You are not a member of this room.'
        ], 403);
    }

    $chatMessage = ChatMessage::create([
        'chat_room_id' => $room->id,
        'user_id' => Auth::id(),
        'message' => $request->message,
    ]);

    $chatMessage->load('user');

    broadcast(new ChatMessageSent($chatMessage))->toOthers();

    // AJAX request → return JSON with message data
    if ($request->ajax()) {
        return response()->json([
            'success' => true,
            'message' => [
                'id' => $chatMessage->id,
                'message' => $chatMessage->message,
                'user' => [
                    'id' => $chatMessage->user->id,
                    'name' => $chatMessage->user->name,
                ],
                'created_at' => $chatMessage->created_at->toDateTimeString(),
            ]
        ]);
    }

    // Normal browser submit → redirect back
    return back()->with('success', 'Message sent!');
}




    public function addMember(Request $request, ChatRoom $room)
    {
        $user = Auth::user();

        if ($room->created_by !== $user->id) {
            return redirect()->back()->with('error', 'Only the creator can add members.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $newUser = User::find($request->user_id);

        if ($room->isMember($newUser)) {
            return redirect()->back()->with('error', 'User is already a member of this room.');
        }

        $room->addUser($newUser);

        return redirect()->back()->with('success', 'User added successfully!');
    }
}
