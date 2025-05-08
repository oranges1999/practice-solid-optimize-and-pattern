<?php

namespace App\Repositories\User;

use App\Models\User;

interface UserRepositoryInterface
{
    public function getAllUser($perPage, $page, $user);
    public function massEditUser($data);
    public function massDeleteUser($data);
    public function updateSpecificUser($user, $data);
    public function deleteSpecificUser($user);
}
