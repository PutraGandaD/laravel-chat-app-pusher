<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'receiver_id',
        'sender_id'
    ];

    // this conversation has many messages. One conversation = between user A and B
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    // get receiver message in this user conversations
    public function getReceiver()
    {
        if ($this->sender_id === auth()->id()) {
            return User::firstWhere('id', $this->receiver_id); // if this user is the sender, than get the receiver
        } else {
            return User::firstWhere('id', $this->sender_id); // otherwise this is user is the receiver
        }
    }

    public function unreadMessagesCount(): int
    {
        return $unreadMessages = Message::where('conversation_id', '=', $this->id)
            ->where('receiver_id', auth()->user()->id)
            ->whereNull('read_at')
            ->count();
    }
}
