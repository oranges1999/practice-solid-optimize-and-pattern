<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'conversation_id',
        'user_id',
        'content',
        'file_url',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class, 'conversation_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
