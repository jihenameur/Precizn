<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Delivery;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class SeederDelivery extends Seeder
{
    protected $password = 'password';
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
        $password = 'password';
        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->email = $faker->email();
            $user->tel = $faker->numerify('+216########');
            $user->password = Hash::make($this->password);
            $user->status_id = 1;

            $delivery = new Delivery();
            $delivery->firstName = $faker->firstName();
            $delivery->lastName = $faker->lastName();
            $delivery->available = rand(0, 1);
            $delivery->street = $faker->streetName();
            $delivery->region = $faker->country();
            $delivery->postcode = $faker->postcode();
            $delivery->city = $faker->city();
            $delivery->lat = $this->postion[$i]['lat'];
            $delivery->long = $this->postion[$i]['long'];
            $delivery->Mark_vehicle = $faker->company();

            $delivery->start_worktime = date('H:i:s', rand(28800,54000));
            $delivery->end_worktime = date('H:i:s', rand(1,54000));
            $delivery->salary = rand(0, 500);

            $delivery->save();
            $delivery->user()->save($user);
            $role = Role::find(4);
            $user->roles()->attach($role);
        }
    }


}
