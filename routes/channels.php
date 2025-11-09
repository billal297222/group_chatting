<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat.room.{roomId}', function ($user, $roomId) {
    return $user->chatRooms()->where('chat_room_id', $roomId)->exists();
});
