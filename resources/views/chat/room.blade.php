@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h5>My Rooms</h5>
                </div>
                <div class="card-body">
                    @foreach($rooms as $userRoom)
                        <div class="mb-2">
                            <a href="{{ route('chat.room.show', $userRoom) }}"
                               class="btn btn-sm w-100 text-start {{ $userRoom->id == $room->id ? 'btn-primary' : 'btn-outline-primary' }}">
                                {{ $userRoom->name }}
                            </a>
                        </div>
                    @endforeach
                    <a href="{{ route('chat.index') }}" class="btn btn-sm btn-outline-secondary w-100 mt-2">
                        All Rooms
                    </a>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <h6>Room Info</h6>
                    <p><strong>{{ $room->name }}</strong></p>
                    <p class="text-muted">{{ $room->description }}</p>
                    <form action="{{ route('chat.room.leave', $room) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-danger w-100">Leave Room</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $room->name }}</h5>
                    <span class="badge bg-primary">Members: {{ $room->users->count() }}</span>
                </div>
                <div class="card-body">
                    <div id="chat-messages" style="height: 400px; overflow-y: auto;" class="mb-3 p-3 border rounded">
                        @foreach($messages as $message)
                            <div class="message mb-3">
                                <div class="d-flex justify-content-between">
                                    <strong>{{ $message->user->name }}</strong>
                                    <small class="text-muted">{{ $message->created_at->format('M j, H:i') }}</small>
                                </div>
                                <p class="mb-0">{{ $message->message }}</p>
                            </div>
                        @endforeach
                    </div>

                    <form action="{{ route('chat.message.send', $room) }}" method="POST" id="message-form">
                        @csrf
                        <div class="input-group">
                            <input type="text" name="message" id="message-input"
                                   class="form-control" placeholder="Type your message..." required>
                            <button type="submit" class="btn btn-primary">Send</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://js.pusher.com/7.0/pusher.min.js"></script>
<script>
    // Initialize Pusher
    const pusher = new Pusher('{{ config('reverb.app.key') }}', {
        wsHost: '{{ config('reverb.host') }}',
        wsPort: {{ config('reverb.port') }},
        wssPort: {{ config('reverb.port') }},
        forceTLS: false,
        enabledTransports: ['ws', 'wss'],
        cluster: 'mt1'
    });

    // Subscribe to channel
    const channel = pusher.subscribe('chat.room.{{ $room->id }}');

    // Listen for new messages
    channel.bind('chat.message.sent', function(data) {
        const message = data.message;
        const messageHtml = `
            <div class="message mb-3">
                <div class="d-flex justify-content-between">
                    <strong>${message.user.name}</strong>
                    <small class="text-muted">${new Date(message.created_at).toLocaleString()}</small>
                </div>
                <p class="mb-0">${message.message}</p>
            </div>
        `;

        document.getElementById('chat-messages').innerHTML += messageHtml;
        scrollToBottom();
    });

    // Auto-scroll to bottom
    function scrollToBottom() {
        const messagesContainer = document.getElementById('chat-messages');
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    // Handle form submission with AJAX for better UX
    document.getElementById('message-form').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const messageInput = document.getElementById('message-input');

        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                messageInput.value = '';
            }
        })
        .catch(error => console.error('Error:', error));
    });

    // Scroll to bottom on page load
    document.addEventListener('DOMContentLoaded', function() {
        scrollToBottom();
    });
</script>
@endpush
