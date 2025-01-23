<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PhpParser\Builder\Function_;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'body',
        'sender_id',
        'receiver_id',
        'conversation_id',
        'read_at',
        'receiver_deleted_at',
        'sender_deleted_at'
    ];

    protected $dates = ['read_at', 'receiver_deleted_at', 'sender_deleted_at'];

    // this message belongs to one conversation
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    // helper to see if messages is read or not
    public function isRead(): bool
    {
        return $this->read_at != null;
    }
}
