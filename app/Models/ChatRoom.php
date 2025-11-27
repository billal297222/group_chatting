<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_private',
        'created_by',
    ];

    protected $casts = [
    'is_private' => 'boolean',
    ];


    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'chat_room_user');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class)->with('user')->latest();
    }

    public function addUser(User $user): void
    {
        $this->users()->syncWithoutDetaching([$user->id]);
    }

    public function removeUser(User $user): void
    {
        $this->users()->detach($user->id);
    }

    public function isMember(User $user): bool
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }
}
