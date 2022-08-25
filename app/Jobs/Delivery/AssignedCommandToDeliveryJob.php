<?php

namespace App\Jobs\Delivery;

use App\Events\Delivery\AssignedCommandToDelivery;
use App\Models\Command;
use App\Models\Delivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AssignedCommandToDeliveryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $command;

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
        $deliveries = Delivery::all()->each(function ($delivery){
            broadcast(new AssignedCommandToDelivery($delivery, $this->command));
        });

    }
}
