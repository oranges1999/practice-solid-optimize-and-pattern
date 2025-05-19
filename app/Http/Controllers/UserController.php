<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index()
    {
        return inertia('User/Index');
    }

    public function edit()
    {
        return inertia('User/Edit');
    }

    public function specificEdit(User $user)
    {
        return inertia(
            'User/SpecificEdit',
            [
            'user' => $user,
            ]
        );
    }

    public function create()
    {
        return inertia('User/Create');
    }

    public function import()
    {
        return inertia('User/Import');
    }

    public function downloadSampleFile()
    {
        // dd(public_path('public/sample/sample_file.xlsx'));
        $path = $this->userService->getSampleFilePath();
        if (file_exists($path)) {
            return response()->download($path);
        }
    }
}
