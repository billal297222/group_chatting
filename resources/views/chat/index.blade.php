@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Create New Room</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('chat.rooms.create') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Room Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_private" name="is_private">
                            <label class="form-check-label" for="is_private">Private Room</label>
                        </div>
                        <button type="submit" class="btn btn-primary">Create Room</button>
                    </form>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5>My Rooms</h5>
                </div>
                <div class="card-body">
                    @forelse($userRooms as $room)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <strong>{{ $room->name }}</strong>
                                <br>
                                <small class="text-muted">{{ $room->description }}</small>
                            </div>
                            <div>
                                <a href="{{ route('chat.room.show', $room) }}" class="btn btn-sm btn-success">Enter</a>
                                <form action="{{ route('chat.room.leave', $room) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger">Leave</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted">You haven't joined any rooms yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Available Rooms</h5>
                </div>
                <div class="card-body">
                    @forelse($rooms as $room)
                        <div class="d-flex justify-content-between align-items-center mb-3 p-3 border rounded">
                            <div>
                                <h6>{{ $room->name }}</h6>
                                <p class="mb-1 text-muted">{{ $room->description }}</p>
                                <small class="text-muted">
                                    Members: {{ $room->users->count() }} |
                                    Messages: {{ $room->messages->count() }}
                                </small>
                            </div>
                            <div>
                                @if($room->isMember(Auth::user()))
                                    <a href="{{ route('chat.room.show', $room) }}" class="btn btn-sm btn-primary">Enter Room</a>
                                @else
                                    <form action="{{ route('chat.room.join', $room) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">Join Room</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-muted">No rooms available.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
