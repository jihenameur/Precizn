<?php

namespace App\Jobs;

use App\Events\Admin\ClientVerifyCommandEvent;
use App\Models\Client;
use App\Models\Command;
use App\Models\Supplier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendCommandClientNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $command;
    private $from;
    private $status;
    private $client;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Command $command, Supplier $from,Client $client,$status)
    {
        $this->command = $command;
        $this->from = $from;
        $this->status = $status;
        $this->client = $client;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        broadcast(new ClientVerifyCommandEvent($this->client));
        $this->client->notify(new \App\Notifications\CommandClientNotification($this->command,$this->from,$this->status));
    }
}
