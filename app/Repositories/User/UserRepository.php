<?php

namespace App\Repositories\User;

use App\Models\User;

class UserRepository implements UserRepositoryInterface
{
    public function getAllUser($perPage, $page)
    {
        return User::paginate($perPage, ['*'], 'page', $page);
    }
}
