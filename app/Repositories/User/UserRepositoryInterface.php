<?php

namespace App\Repositories\User;

use App\Models\User;

interface UserRepositoryInterface
{
    public function getAllUser($data, $user);
    public function massEditUser($data);
    public function massDeleteUser($data);
    public function updateSpecificUser($user, $data);
    public function deleteSpecificUser($user);
    public function createUSer($data);
}
