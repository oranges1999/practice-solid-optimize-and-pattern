<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Support\Facades\Request;
use Illuminate\Http\UploadedFile;

class UserService
{
    private $userRepository;

    private $page = 0;

    private $perPage = 20;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getUser($data, $currentUser)
    {  
        if($data){
            $users = User::search($data)
                ->whereNotIn('id', [$currentUser->id])
                ->paginate($this->perPage, 'page', $this->page);
        } else {
            $users = $this->userRepository
                ->getAllUser($this->perPage, $this->page, $currentUser);
        }
        return collect($users);
    }

    public function massUpdateUser($data)
    {
        $this->userRepository
            ->massEditUser($data);
        return 1;
    }

    public function massDeleteUser($data)
    {
        $this->userRepository
            ->massDeleteUser($data);
        return 1;
    }

    public function updateSpecificUser($user,$data)
    {
        try {
            if(array_key_exists('avatar', $data) && $data['avatar'] instanceof UploadedFile){
                $path = storeFile("avatars/$user->id", $data['avatar']);
                $data['avatar'] = getFileUrl($path);
            }
            $this->userRepository->updateSpecificUser($user, $data);
            return 1;
        } catch (\Throwable $th) {
            throw $th;
        }
        
    }

    public function deleteSpecificUser($user)
    {
        $this->userRepository
            ->deleteSpecificUser($user);
    }
}
