<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassUserUpdateRequest;
use App\Http\Requests\UpdateSpecificUserRequest;
use App\Http\Requests\UserCreateRequest;
use App\Models\User;
use App\Repositories\User\UserRepository;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

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
        $users = $this->userService->getUser($request->all(), $currentUser);
        return response()->json($users, 200);
    }

    public function massUpdate(MassUserUpdateRequest $request)
    {
        $this->userService->massUpdateUser($request->validated());
        return response()->json([], 200);
    }

    public function massDelete(Request $request)
    {
        $this->userService->massDeleteUser($request->all());
        return response()->json([], 200);
    }

    public function getUserData(User $user)
    {
        return response()->json($user, 200);
    }

    public function updateSpecificUser(User $user, UpdateSpecificUserRequest $request)
    {
        $this->userService->updateSpecificUser($user, $request->validated());
        return response()->json([], 200);
    }

    public function deleteSpecificUser(User $user)
    {
        $this->userService->deleteSpecificUser($user);
        return response()->json([], 200);
    }

    public function createUser(UserCreateRequest $request)
    {
        $user = $this->userService->createNewUser($request->validated());
        return response()->json(['user' => $user]);
    }

    public function importData(Request $request)
    {
        $file = $request->validate([
            'file' => 'mimes:xlsx|required'
        ]);
        $path = $this->userService->import($file);
        return $path
            ? response()->download($path, 'import_error.xlsx')->deleteFileAfterSend()
            : response()->json([], 200);
    }

    public function exportUsers(Request $request)
    {
        $this->userService->exportUsers(
            $request->input('user_type'),
            $request->input('export_option'),
            $request->input('user_ids')
        );
        return response()->json([], 200);
    }
}
