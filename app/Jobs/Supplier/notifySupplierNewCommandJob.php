<?php

namespace App\Jobs\Supplier;

use App\Events\Supplier\notifySupplierNewCommandEvent;
use App\Models\Command;
use App\Models\Supplier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class notifySupplierNewCommandJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $supplier;
    private $command;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Supplier $supplier, Command $command)
    {
        $this->supplier = $supplier;
        $this->command = $command;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        broadcast(new notifySupplierNewCommandEvent($this->supplier, $this->command));
    }
}
