<?php

namespace App\Repositories\User;

interface UserRepositoryInterface
{
    public function getAllUser($perPage, $page);
}
