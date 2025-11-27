@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3">
            <div class="card mb-3">
                <div class="card-header"><h5>My Rooms</h5></div>
                <div class="card-body">
                    @foreach ($rooms as $userRoom)
                        <div class="mb-2">
                            <a href="{{ route('chat.room.show', $userRoom) }}"
                               class="btn btn-sm w-100 text-start {{ $userRoom->id == $room->id ? 'btn-primary' : 'btn-outline-primary' }}">
                                {{ $userRoom->name }}
                            </a>
                        </div>
                    @endforeach
                    <a href="{{ route('chat.index') }}" class="btn btn-sm btn-outline-secondary w-100 mt-2">All Rooms</a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h6>Room Info</h6>
                    <p><strong>{{ $room->name }}</strong></p>
                    <p class="text-muted">{{ $room->description }}</p>
                </div>
            </div>
        </div>

        <!-- Chat Area -->
        <div class="col-md-9">
            <div class="card" style="height: 500px;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $room->name }}</h5>
                    <span class="badge bg-primary">Members: {{ $room->users->count() }}</span>
                </div>

                <div class="card-body d-flex flex-column p-0" style="height: calc(100% - 60px);">
                    <!-- Messages container -->
                    <div id="chat-messages" class="flex-grow-1 overflow-auto p-3">
                        @foreach ($messages as $message)
                            @if ($message->user_id == Auth::id())
                                <div class="text-end my-2" data-id="{{ $message->id }}">
                                    <div class="d-inline-block p-2 rounded bg-primary text-white">
                                        {{ $message->message }}
                                    </div>
                                </div>
                            @else
                                <div class="my-2" data-id="{{ $message->id }}">
                                    <strong>{{ $message->user->name }}</strong><br>
                                    <div class="d-inline-block p-2 rounded bg-light text-dark border">
                                        {{ $message->message }}
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <!-- Input fixed at bottom -->
                    <form action="{{ route('chat.message.send', $room) }}" method="POST" id="message-form" class="p-3 border-top bg-white">
                        @csrf
                        <div class="input-group">
                            <input type="text" name="message" id="message-input" class="form-control" placeholder="Type your message..." required>
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
<script>
const currentUserId = {{ Auth::id() }};
const messagesContainer = document.getElementById('chat-messages');
let lastMessageId = {{ $messages->last()?->id ?? 0 }};

// Append message function (both sender and receiver)
function appendMessage(message) {
    if (document.querySelector(`[data-id="${message.id}"]`)) return;

    let html = '';
    if (message.user.id === currentUserId) {
        html = `<div class="text-end my-2" data-id="${message.id}">
                    <div class="d-inline-block p-2 rounded bg-primary text-white">
                        ${message.message}
                    </div>
                </div>`;
    } else {
        html = `<div class="my-2" data-id="${message.id}">
                    <strong>${message.user.name}</strong><br>
                    <div class="d-inline-block p-2 rounded bg-light text-dark border">
                        ${message.message}
                    </div>
                </div>`;
    }

    messagesContainer.insertAdjacentHTML('beforeend', html);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
    lastMessageId = message.id;
}

window.onload = () => messagesContainer.scrollTop = messagesContainer.scrollHeight;


document.getElementById('message-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const messageInput = document.getElementById('message-input');

    fetch(this.action, {
        method: "POST",
        body: formData,
        headers: { "X-Requested-With": "XMLHttpRequest" }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.message) {
            appendMessage(data.message);
            messageInput.value = "";
        }
    });
});


setInterval(() => {
    fetch(`{{ route('chat.room.show', $room) }}?ajax=1&after_id=${lastMessageId}`)
        .then(res => res.json())
        .then(data => {
            if (data.messages) {
                data.messages.forEach(msg => appendMessage(msg));
            }
        });
}, 2000);
</script>
@endpush
