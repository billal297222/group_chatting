<?php

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ChatMessage $chatMessage
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel("chat.room.{$this->chatMessage->chat_room_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'chat.message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id' => $this->chatMessage->id,
                'message' => $this->chatMessage->message,
                'user' => [
                    'id' => $this->chatMessage->user->id,
                    'name' => $this->chatMessage->user->name,
                ],
                'created_at' => $this->chatMessage->created_at->toDateTimeString(),
            ],
        ];
    }
}
