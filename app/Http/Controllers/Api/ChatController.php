<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    private $service;
    private $user;

    public function __construct(ChatService $service)
    {
        $this->service = $service;
        $this->user = Auth::user();
    }

    public function storeMessage(Request $request)
    {
        return $this->service
            ->storeMessage($request->all(), $this->user);
    }

    public function getConversation(Conversation $conversation)
    {
        $user = $this->user;
        return $conversation->load([
            'users' => function ($query) use ($user) {
                $query->where('user_id', '!=', $user->id);
            },
            'messages'
        ]);
    }
}
