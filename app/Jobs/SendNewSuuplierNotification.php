<?php

namespace App\Jobs;

use App\Models\Admin;
use App\Models\Supplier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNewSuuplierNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $supplier;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Supplier $supplier)
    {
        $this->supplier = $supplier;
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
            $admin->user->notify(new \App\Notifications\NewSupplierNotification($this->supplier,'mail'));
        }
    }
}
