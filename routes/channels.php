<?php

use App\Broadcasting\NotifyOnlineStatusChannel;
use Illuminate\Support\Facades\Broadcast;

Broadcast::routes(['middleware' => ['web', 'auth']]);

Broadcast::channel(
    'App.Models.User.{id}',
    function ($user, $id) {
        return (int) $user->id === (int) $id;
    }
);

Broadcast::channel('getOnlineUsers', NotifyOnlineStatusChannel::class);
