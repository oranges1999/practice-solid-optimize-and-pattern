<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
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
        return inertia('User/SpecificEdit', [
            'user' => $user,
        ]);
    }

    public function create()
    {
        return inertia('User/Create');
    }

    public function import()
    {
        return inertia('User/Import');
    }
}
