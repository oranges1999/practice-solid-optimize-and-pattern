<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassUserUpdateRequest;
use App\Http\Requests\UpdateSpecificUserRequest;
use App\Models\User;
use App\Repositories\User\UserRepository;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(Request $request)
    {
        $currentUser = Auth::user();
        return $this->userService->getUser($request->all(), $currentUser);
    }

    public function massUpdate(MassUserUpdateRequest $request)
    {
        $this->userService->massUpdateUser($request->validated());
        return response()->json([], 200);
    }

    public function massDelete(Request $request)
    {
        $this->userService->massDeleteUser($request->all());
    }

    public function getUserData(User $user)
    {
        return $user; 
    }

    public function updateSpecificUser(User $user, UpdateSpecificUserRequest $request)
    {
        $this->userService->updateSpecificUser($user, $request->validated());
    }

    public function deleteSpecificUser(User $user)
    {
        $this->userService->deleteSpecificUser($user);
    }
}
