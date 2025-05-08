<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\User\UserRepositoryInterface;

class UserService
{
    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getUser($data)
    {  
        $perPage = $data->per_page??20;
        $page = $data->page??0;
        $users = $this->userRepository->getAllUser($perPage, $page);
        return collect($users);
    }

    public function massUpdateUser($data)
    {
        $this->userRepository->massEditUser($data);
        return 1;
    }

    public function massDeleteUser($data)
    {
        $this->userRepository->massDeleteUser($data);
        return 1;
    }
}
