<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Client;
use App\Models\Command;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Supplier;
use DateTime;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class SeederCommand extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = \Faker\Factory::create();
        $clients = Client::all();
        $supps = Supplier::all();

        for ($i = 0; $i < 2; $i++) {
            $client = $clients[rand(1, $clients->count() - 1)];
            $command = new Command();
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
        }

    }

}
