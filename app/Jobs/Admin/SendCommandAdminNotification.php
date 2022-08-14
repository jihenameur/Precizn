<?php

namespace App\Jobs\Admin;

use App\Events\Admin\AdminVerifyCommandEvent;
use App\Models\Admin;
use App\Models\Client;
use App\Models\Command;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendCommandAdminNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $command;
    private $from;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Command $command, Client $from)
    {
        $this->command = $command;
        $this->from = $from;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $admins = Admin::all();
        foreach ($admins as $admin){
            broadcast(new AdminVerifyCommandEvent($admin,$this->command));
            $admin->notify(new \App\Notifications\CommandAdminNotification($this->command,$this->from));

           // broadcast(new \App\Notifications\CommandAdminNotification($this->command, $this->from,$admin));
        }
    }
}
