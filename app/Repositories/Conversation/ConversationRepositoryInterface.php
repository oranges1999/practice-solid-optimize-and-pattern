<?php

namespace App\Repositories\Conversation;

interface ConversationRepositoryInterface
{
    public function checkConversationExist($participantIds);
    public function createConversation($usersArray);
    public function getBasicInformation($joinedConversationId, $user);
}
