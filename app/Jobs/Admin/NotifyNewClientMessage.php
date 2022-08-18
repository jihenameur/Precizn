<?php

namespace App\Jobs\Admin;

use App\Events\Admin\NewMessageFromClient;
use App\Models\Admin;
use App\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyNewClientMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $client;
    private $message;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Client $client, $message)
    {
        $this->client = $client;
        $this->message = $message;
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
            broadcast(new NewMessageFromClient($admin, $this->client, $this->message));
        }
    }
}
