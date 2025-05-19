<?php

namespace App\Events;

use App\Enums\ExportTypeEnum;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BeginDeletingUser implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    private $type;
    private $user;
    /**
     * Create a new event instance.
     */
    public function __construct($type, $user)
    {
        $this->user = $user;
        $this->type = $type;
    }
    public function broadcastWith()
    {
        $conditionalPart = $this->type == ExportTypeEnum::ONLY_EXPORT->value ?
            '' :
            'and deleted' ;
        $message = "Selected users is being exported $conditionalPart, please wait a moment";
        return [
            'type' => 'info',
            'is_deleting' => true,
            'message' => $message
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('App.Models.User.' . $this->user->id),
        ];
    }
}
