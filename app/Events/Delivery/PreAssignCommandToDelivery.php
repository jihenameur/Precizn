<?php

namespace App\Events\Delivery;

use App\Models\Command;
use App\Models\Delivery;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PreAssignCommandToDelivery implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    protected $delivery;
    protected $command;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Delivery $delivery, Command $command)
    {
        $this->delivery = $delivery;
        $this->command = $command;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('.delivery.'.$this->delivery->id);
    }

    public function broadcastAs()
    {
        return 'action';
    }

    public function broadcastWith()
    {
        return [
            "type_event" => "NEW_PRE_ASSIGN_COMMAND",
            "command" => $this->command->id,
        ];
    }
}
