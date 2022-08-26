<?php

namespace App\Jobs\Admin;

use App\Events\Admin\AdminCommandNotAssignedEvent;
use App\Events\Admin\AdminNewPreAssignCommandEvent;
use App\Models\Admin;
use App\Models\Command;
use App\Models\Delivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AdminNewPreAssignCommandJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $command;
    private $delivery;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Command $command, Delivery $delivery)
    {
        $this->command = $command;
        $this->delivery = $delivery;
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
            broadcast(new AdminNewPreAssignCommandEvent($admin, $this->command, $this->delivery));
        }
    }
}
