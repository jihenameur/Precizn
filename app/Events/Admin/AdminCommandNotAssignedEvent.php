<?php

namespace App\Events\Admin;

use App\Models\Admin;
use App\Models\Command;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdminCommandNotAssignedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    private $command;
    private $admin;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Admin $admin, Command $command)
    {
        $this->command = $command;
        $this->admin = $admin;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('.admin.'.$this->admin->id);
    }

    public function broadcastAs()
    {
        return 'action';
    }

    public function broadcastWith()
    {
        return [
            "type_event" => "COMMAND_NOT_ASSIGNED",
            "command" => $this->command,
        ];
    }
}
