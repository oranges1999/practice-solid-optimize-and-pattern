<?php

namespace App\Broadcasting;

use App\Models\User;

class RetrieveMessages
{
    /**
     * Create a new channel instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Authenticate the user's access to the channel.
     */
    public function join(User $user, $id): array|bool
    {
        $userHasConvo = User::where('id', $user->id)->with(['conversation' => function ($q) use ($id) {
            $q->where('id', $id);
        }])->exists();
        return $userHasConvo ?
            ['id' => $user->id, 'name' => $user->name, 'avatar' => $user->avatar] :
            [] ;
    }
}
