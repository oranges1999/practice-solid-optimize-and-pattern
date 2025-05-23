<?php

namespace App\Repositories\Conversation;

use App\Models\Conversation;
use Illuminate\Support\Facades\DB;

class ConversationRepository implements ConversationRepositoryInterface
{
    public function checkConversationExist($participantIds)
    {
        return DB::table('user_conversation')
            ->select('conversation_id')
            ->whereIn('user_id', $participantIds)
            ->groupBy('conversation_id')
            ->havingRaw('COUNT(DISTINCT user_id) = ?', [count($participantIds)])
            ->first()?->conversation_id;
    }

    public function createConversation($usersArray)
    {
        $conversation = Conversation::create();
        $conversation->users()->attach($usersArray);
        return $conversation;
    }

    public function getBasicInformation($joinedConversationId, $user)
    {
        return Conversation::whereIn('id', $joinedConversationId)
            ->with([
                'users' => function ($query) use ($user) {
                    $query->where('users.id', '!=', $user->id);
                } ,
                'messages' => function ($query) {
                    $query->orderBy('created_at', 'desc')->limit(1);
                }
            ])
            ->get();
    }
}
