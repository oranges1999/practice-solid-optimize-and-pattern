<?php

namespace App\Repositories\Message;

use App\Models\Message;

class MessageRepository implements MessageRepositoryInterface
{
    public function storeMessage($data)
    {
        return Message::create($data);
    }
}
