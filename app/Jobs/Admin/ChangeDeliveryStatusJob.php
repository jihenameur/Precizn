<?php

namespace App\Jobs\Admin;

use App\Models\Admin;
use App\Models\Delivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ChangeDeliveryStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $delivery;
    private $status;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Delivery $delivery, $status)
    {

        $this->delivery = $delivery;
        $this->status = $status;
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
            broadcast(new \App\Events\Admin\DeleviryStatusChangedEvent($admin, $this->delivery, $this->status));
        }

    }
}
