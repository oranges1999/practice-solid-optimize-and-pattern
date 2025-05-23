<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Scout\Searchable;

class User extends Authenticatable
{
    /**
 * @use HasFactory<\Database\Factories\UserFactory>
*/
    use HasFactory;
    use Notifiable;
    use Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'type',
        'description',
        'avatar',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function toSearchableArray()
    {
        $array = $this->toArray();

        $array['created_at'] = strtotime($this->created_at); // convert thành Unix timestamp

        return $array;
    }

    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'user_conversation', 'user_id', 'conversation_id')
            ->withPivot('user_id');
    }

    public function mesasges()
    {
        return $this->hasMany(Message::class, 'user_id', 'id');
    }
}
