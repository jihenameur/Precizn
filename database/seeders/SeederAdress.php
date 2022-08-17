<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Client;
use App\Models\Command;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;

class SeederAdress extends Seeder
{
    public $postion = [
        ['lat' => 35.87114686073671, 'long' => 10.603245858720085],
        ['lat' => 35.8588004603057, 'long' => 10.602687959208176],
        ['lat' => 35.850278583516896, 'long' => 10.61637795400609],
        ['lat' => 35.84318211773907, 'long' => 10.584963922143647],
        ['lat' => 35.83114450339894, 'long' => 10.628308419670157],
        ['lat' => 35.82300242262979, 'long' => 10.636161927690619],
        ['lat' => 35.8356327290493, 'long' => 10.637234811297786],
        ['lat' => 35.84036422713682, 'long' => 10.630582932958056],
        ['lat' => 35.857200504196165, 'long' => 10.615219239758517],
        ['lat' => 35.85956564528116, 'long' => 10.60406125020781],
    ];
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = \Faker\Factory::create();
        $users = User::where('userable_type','App\Models\Client')->get();

        for ($i = 0; $i < 10; $i++) {
            $user = $users[$i];
            $address = new Address();
            $address->street =$faker->streetName();
            $address->region = $faker->country();
            $address->postcode = $faker->postcode();
            $address->city = $faker->city();
            $address->lat = $this->postion[$i]['lat'];
            $address->long = $this->postion[$i]['long'];
            $address->user_id  =  $user->id;
            $address->status = rand(0, 2);
            $address->label = $faker->company();
            $address->type = rand(1, 3);
            $address->save();

        }
    }


}
