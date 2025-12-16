<?php

namespace App\Services;

use App\Enums\ReturnTypeEnum;
use App\Events\UpdateChatRoom;
use App\Events\UpdateMessages;
use App\Models\Conversation;
use App\Models\Message;
use App\Repositories\Conversation\ConversationRepositoryInterface;
use App\Repositories\Message\MessageRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChatService
{
    private $conversationRepository;
    private $messageRepository;

    public function __construct(
        ConversationRepositoryInterface $conversationRepository,
        MessageRepositoryInterface $messageRepository
    ) {
        $this->conversationRepository = $conversationRepository;
        $this->messageRepository = $messageRepository;
    }

    public function storeMessage($data, $user)
    {
        DB::beginTransaction();
        try {
            if (array_key_exists('user_id', $data)) {
                $type = ReturnTypeEnum::CONVERSATION->value;
                $participantIds = [$data['user_id'], $user->id];
                $conversationId = $this->conversationRepository
                    ->checkConversationExist($participantIds);
                if (! $conversationId) {
                    $conversation = $this->conversationRepository
                        ->createConversation($participantIds);
                }
            }
            if (array_key_exists('conversation_id', $data)) {
                $type = ReturnTypeEnum::MESSAGE->value;
                $conversationId = $data['conversation_id'];
            }
            $message = $this->messageRepository
                ->storeMessage([
                    'conversation_id' => $conversation?->id ?? $conversationId,
                    'user_id' => $user->id,
                    'content' => $data['content']
                ]);
            DB::commit();
            if ($type == ReturnTypeEnum::MESSAGE->value) {
                $returnData = ['type' => $type,'message' => $message];
                $otherMember = DB::table('user_conversation')
                        ->where('user_id', '!=', $user->id)
                        ->where('conversation_id', $conversationId)
                        ->get();
                foreach($otherMember as $member){
                    $oldConversation = Conversation::where('id', $conversationId)
                        ->withCount(['messages as unread_messages_count' => function ($query) use ($member) {
                            $query->where('read', false)
                                ->where('user_id', '!=', $member->user_id);
                        }])
                        ->with([
                            'users' => function ($q) use ($user) {
                                $q->where('users.id', $user->id);
                            },
                            'messages' => function ($q) {
                                $q->orderBy('created_at', 'desc')->limit(1);
                            }
                        ])
                        ->first();
                    broadcast(new UpdateChatRoom($member->user_id, $oldConversation))->toOthers();
                }
                broadcast(new UpdateMessages($message))->toOthers();
            } else {
                $targetData = [
                    'users' => function ($q) use ($data) {
                        $q->where('users.id', $data['user_id']);
                    },
                    'messages'
                ];
                $senderData = [
                    'users' => function ($q) use ($user) {
                        $q->where('users.id', $user->id);
                    },
                    'messages'
                ];
                $returnData = [
                    'type' => $type,
                    'conversation' => $this->getConversationData($targetData, $conversation->id, $user->id)
                    ];
                broadcast(
                    new UpdateChatRoom(
                        $data['user_id'],
                        $this->getConversationData($senderData,  $conversation->id, $data['user_id'])
                    )
                )
                ->toOthers();
            }
            return $returnData;
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);
        }
    }

    public function markRead(Conversation $conversation, $currentUser)
    {
        return Message::where('conversation_id', $conversation->id)
            ->where('user_id', '!=', $currentUser->id)
            ->where('read', false)
            ->update(['read' => true]);
    }

    private function getConversationData($relation, $conversationId, $userId)
    {
        return Conversation::where('id', $conversationId)
            ->withCount(['messages as unread_messages_count' => function ($query) use ($userId) {
                $query->where('read', false)
                    ->where('user_id', '!=', $userId);
            }])
            ->with($relation)
            ->first();
    }
}
