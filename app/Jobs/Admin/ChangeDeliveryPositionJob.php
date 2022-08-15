<?php

namespace App\Jobs\Admin;

use App\Events\Admin\DeliveryPositionChangedEvent;
use App\Models\Admin;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ChangeDeliveryPositionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $delivery;
    private $position;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($delivery, $position)
    {
        $this->delivery = $delivery;
        $this->position = $position;
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
            broadcast(new DeliveryPositionChangedEvent($admin, $this->delivery, $this->position));
        }
    }
}
