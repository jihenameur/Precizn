<?php

namespace App\Events\Supplier;

use App\Models\Command;
use App\Models\Supplier;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class notifySupplierNewCommandEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    private $supplier;
    private $command;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Supplier $supplier, Command $command)
    {
        $this->supplier = $supplier;
        $this->command = $command;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('.supplier.'.$this->supplier->id);
    }

    public function broadcastAs()
    {
        return 'action';
    }

    public function broadcastWith()
    {
        return [
            "type_event" => "NEW_COMMAND",
            "command" => $this->command->id,
        ];
    }
}
