<?php

namespace App\Events\Client;

use App\Models\Client;
use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewMessageFromAdminEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $client;
    private $message;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Client $client, Message $message)
    {
        $this->client = $client;
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('.client.'.$this->client->id);
    }

    public function broadcastAs()
    {
        return 'action';
    }

    public function broadcastWith()
    {
        return [
            "type_event" => "NEW_ADMIN_MESSAGE",
            "message" => $this->message,
        ];
    }
}
