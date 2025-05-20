<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class MarkOnlineStatusService
{
    public function markOnline($user)
    {
        Log::info($user);
        die;
    }

    public function markOffline($user)
    {
        Log::info($user);
        die;
    }
}
