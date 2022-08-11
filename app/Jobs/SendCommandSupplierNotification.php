<?php

namespace App\Jobs;

use App\Events\SupplierVerifyCommandEvent;
use App\Models\Client;
use App\Models\Command;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendCommandSupplierNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $command;
    private $supplier;
    private $from;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Command $command, Client $from,Supplier $supplier)
    {
        $this->command = $command;
        $this->from = $from;
        $this->supplier = $supplier;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        broadcast(new SupplierVerifyCommandEvent($this->supplier));
        $this->supplier->notify(new \App\Notifications\CommandNotification($this->command,$this->from));
    }
}
