<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = [
        'name',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_conversation', 'conversation_id', 'user_id')
            ->withPivot('user_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'conversation_id', 'id');
    }
}
