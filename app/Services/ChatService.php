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
use PhpParser\Node\Stmt\Return_;

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
            if ($type == ReturnTypeEnum::MESSAGE->value) {
                $returnData = ['type' => $type,'message' => $message];
                broadcast(new UpdateMessages($message))->toOthers();
            } else {
                $senderData = [
                    'users' => function ($q) use ($user) {
                        $q->where('users.id', '!=', $user->id);
                    },
                    'messages'
                ];
                $targetData = [
                    'users' => function ($q) use ($data) {
                        $q->where('users.id', '!=', $data['user_id']);
                    },
                    'messages'
                ];
                $returnData = [
                    'type' => $type,
                    'conversation' => $conversation->load($senderData)
                    ];
                broadcast(new UpdateChatRoom($data['user_id'], $conversation->load($targetData)))->toOthers();
            }
            DB::commit();
            return $returnData;
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);
        }
    }
}
