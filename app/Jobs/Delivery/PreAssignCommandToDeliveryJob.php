<?php

namespace App\Jobs\Delivery;

use App\Events\Delivery\PreAssignCommandToDelivery;
use App\Models\Command;
use App\Models\Delivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PreAssignCommandToDeliveryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $delivery;
    protected $command;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Delivery $delivery, Command $command)
    {
        $this->delivery = $delivery;
        $this->command = $command;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        broadcast(new PreAssignCommandToDelivery($this->delivery, $this->command));
    }
}
