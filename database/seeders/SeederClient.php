<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Client;
use App\Models\Role;
use App\Models\Status;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class SeederClient extends Seeder
{
    protected $password = 'password';

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
            $user->email = 'user'.($i+1).'@thunder-express.com';
            $user->tel = $faker->numerify('+216########');
            $user->password = Hash::make($this->password);
            $user->status_id = 1;

            $client = new Client();
            $client->firstname = $faker->firstName();
            $client->lastname = $faker->lastName();
            $client->gender = rand(0, 1);

            $client->save();
            $client->user()->save($user);
            $role = Role::find(5);
            $user->roles()->attach($role);
        }
    }


}
