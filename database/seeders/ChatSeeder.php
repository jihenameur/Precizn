<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Message;
use Illuminate\Database\Seeder;

class ChatSeeder extends Seeder
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
        foreach ($clients as $client){
            for($i = 0 ; $i < 50 ; $i++){
                $message = new Message();
                $message->client_id = $client->id;
                $message->date = now();
                $message->send = 0;
                $message->message = $faker->text(20);
                $message->save();

                $message = new Message();
                $message->client_id = $client->id;
                $message->date = now();
                $message->send = 1;
                $message->message = $faker->text(20);
                $message->save();
            }
        }
    }
}
