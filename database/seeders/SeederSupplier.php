<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Category;
use App\Models\Delivery;
use App\Models\Product;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class SeederSupplier extends Seeder
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
            $user->email = 'supplier'.($i+1).'@thunder-express.com';
            $user->tel = $faker->numerify('+216########');
            $user->password = Hash::make($this->password);
            $user->status_id = 1;
            $supplier = new Supplier();
            $supplier->name = $faker->company();
            $supplier->qantityVente = 0;
            $supplier->starttime = date('H:i:s', rand(1, 14000));
            $supplier->closetime = date('H:i:s', rand(1, 54000));
            $supplier->delivery = 1;
            $supplier->take_away = 1;
            $supplier->on_site = 1;
            $supplier->firstName = $faker->firstName();
            $supplier->lastName = $faker->lastName();
            $supplier->street = $faker->streetName();
            $supplier->region = $faker->country();
            $supplier->postcode = $faker->postcode();
            $supplier->city = $faker->city();
            $supplier->lat = $this->postion[$i]['lat'];
            $supplier->long = $this->postion[$i]['long'];
            $supplier->commission = rand(0, 10);
            $supplier->star = rand(0, 100);

            $supplier->save();
            $supplier->user()->save($user);
            $role = Role::find(3);
            $user->roles()->attach($role);
            $category = Category::all()->random(1);
            $supplier->categorys()->attach($category);
        }
    }


}
