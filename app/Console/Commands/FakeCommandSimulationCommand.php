<?php

namespace App\Console\Commands;

use App\Models\Address;
use App\Models\Client;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Console\Command;

class FakeCommandSimulationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simulation:commands';

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
        $faker = \Faker\Factory::create();
        $clients = Client::all();
        $supps = Supplier::all();

        for ($i = 0; $i < 100; $i++) {
            $client = $clients[rand(1, $clients->count() - 1)];
            $command = new \App\Models\Command();
            $command->client_id = $client->id;
            $command->date = $faker->dateTimeInInterval('-1 week','+1 day');
            $command->mode_pay = rand(0, 2);
            $command->delivery_price = rand(4, 6);
            $command->total_price = rand(1, 20);
            $command->tip = rand(1, 3);
            $command->status = rand(0, 2);
            $command->codepromo = $faker->bothify('???-###');
            $suppl = $supps[rand(1, $supps->count() - 1)];
            $command->supplier_id = $suppl->id;
            $addr = Address::where('user_id', $client->user->id)->first();
            $command->lat = $addr->lat;
            $command->long = $addr->long;
            $command->save();
            $prods = Product::whereHas('suppliers', function ($q) use ($command) {
                $q->where('supplier_id', $command->supplier_id);
            })->get();
            $prod = $prods[rand(0, $prods->count() - 1)];
            $command->products()->attach($prod, [
                'quantity' => rand(1, 5)

            ]);
            $command->cycle = 'AUTHORIZED';
            $command->save();
        }
        return 1;
    }
}
