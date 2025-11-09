<?php

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;

// Authentication Routes
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Chat Routes (Protected by auth)
Route::middleware(['auth'])->group(function () {
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat/rooms', [ChatController::class, 'createRoom'])->name('chat.rooms.create');
    Route::get('/chat/rooms/{room}', [ChatController::class, 'show'])->name('chat.room.show');
    Route::post('/chat/rooms/{room}/join', [ChatController::class, 'joinRoom'])->name('chat.room.join');
    Route::post('/chat/rooms/{room}/leave', [ChatController::class, 'leaveRoom'])->name('chat.room.leave');
    Route::post('/chat/rooms/{room}/messages', [ChatController::class, 'sendMessage'])->name('chat.message.send');
});

Route::get('/', function () {
    return redirect('/chat');
});
