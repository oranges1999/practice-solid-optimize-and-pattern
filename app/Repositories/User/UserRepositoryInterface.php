<?php

namespace App\Repositories\User;

use App\Models\User;

interface UserRepositoryInterface
{
    public function getAllUser($perPage, $page);
    public function massEditUser($data);
}
