<?php

namespace App\Console\Commands;

use App\Helpers\RedisHelper;
use Illuminate\Console\Command;

class FlushRedis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'redis:flush';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $redis_helper = new RedisHelper();
        $redis_helper->flush();
        return 1;
    }
}
