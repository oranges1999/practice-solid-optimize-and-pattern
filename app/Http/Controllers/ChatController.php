<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Repositories\Conversation\ConversationRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    private $repository;

    public function __construct(ConversationRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function index()
    {
        $user = Auth::user();
        $joinedConversationId = $user->conversations->pluck('id');
        return inertia('Dashboard', [
            'all_conversations' => $this->repository
                ->getBasicInformation($joinedConversationId, $user),
        ]);
    }
}
