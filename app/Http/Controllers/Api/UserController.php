<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\User\UserRepository;
use App\Services\UserService;
use Illuminate\Http\Request;
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
        return $this->userService->getUser($request->all());
    }

    public function massUpdate(Request $request)
    {
        try {
            $this->userService->massUpdateUser($request->all());
            return response()->json([], 200);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function massDelete(Request $request)
    {
        DB::beginTransaction();
        try{
            $this->userService->massDeleteUser($request->all());
            DB::commit();
        } catch (\Throwable $th){
            DB::rollback();
        }
    }
}
