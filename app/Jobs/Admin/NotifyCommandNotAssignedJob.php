<?php

namespace App\Jobs\Admin;

use App\Events\Admin\AdminCommandNotAssignedEvent;
use App\Models\Admin;
use App\Models\Command;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyCommandNotAssignedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $command;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Command $command)
    {
        $this->command = $command;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // modify to selected admin todo
        $admins = Admin::all();
        foreach ($admins as  $admin)
        {
            broadcast(new AdminCommandNotAssignedEvent($admin, $this->command));
        }
    }
}
