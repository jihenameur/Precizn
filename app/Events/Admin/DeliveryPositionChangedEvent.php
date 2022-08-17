<?php

namespace App\Events\Admin;

use App\Models\Admin;
use App\Models\Delivery;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeliveryPositionChangedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $admin;
    public $delivery;
    public $position;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Admin $admin, Delivery $delivery, $position)
    {
        $this->admin = $admin;
        $this->delivery = $delivery;
        $this->position = $position;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('.admin.'.$this->admin->id); //channel global
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'action';
    }

    public function broadcastWith()
    {
        return [
            'type_event' => "delivery_position_changed",
            "delivery" => $this->delivery,
            "position" => $this->position
        ];
    }
}
