<?php

namespace App\Events;

use App\Models\Delivery;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeliveryPosition implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    public $position;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($position)
    {
        $this->position = $position;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('.positionDelivery');
    }
    public function broadcastAs(): string
    {
        return 'my-positionDelivery';
    }
}
