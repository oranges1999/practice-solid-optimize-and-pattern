<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\MarkOnlineStatusService;
use Illuminate\Http\Request;

class MarkOnlineStatusController extends Controller
{
    private $service;

    public function __construct(MarkOnlineStatusService $service)
    {
        $this->service = $service;
    }

    public function markStatusOnline(User $user)
    {
        $this->service->markOnline($user);
        return response()->json([], 200);
    }

    public function markStatusOffline(User $user)
    {
        $this->service->markOffline($user);
        return response()->json([], 200);
    }
}
