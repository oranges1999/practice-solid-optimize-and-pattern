<?php

namespace App\Http\Controllers\Api;

use App\Events\CustomUserLeave;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SendNotiWhenLeaveController extends Controller
{
    public function sendNotification()
    {
        $currentUserId = Auth::user()->id;
        CustomUserLeave::broadcast($currentUserId)->toOthers();
    }
}
