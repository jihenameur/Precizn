<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class SeederSupplier extends Seeder
{
    protected $password = '12345678';
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = Role::where('name', config('roles.backadmin.supplier'))->first();

        $suppliers = $this->getSuppliersList();
        $this->createSupplier($suppliers);
    }

    /**
     * Get clients list
     *
     * @param $role
     * @return array[]
     */
    private function getSuppliersList(): array
    {
        $suppliers = [
            [
                'name' => 'thunder',
                // 'min_period_time'=>20,
                // 'max_period_time'=>30,
                'star' => 0,
                'qantityVente' => 0,
                'starttime' => '11:00',
                'closetime' => "00:00",
                'delivery' => 0,
                'take_away' => 1,
                'on_site' => 1,
                'password' => Hash::make($this->password),
                'email' => 'thunder123@gmail.com',
                'street' => 'hammam sousse',
                'postcode' => '4000',
                'city' => 'Sousse',
                'region' => 'tunisie',
                'lat' => 35.886399,
                'long' => 10.5915072,
                'tel' => '+21626333555',
                'firstName' => 'mohammed',
                'lastName' => 'salah'
            ]
        ];
        return $suppliers;
    }

    /**
     * Create suppliers list into the DB
     *
     * @param array $suppliers
     */
    private function createSupplier(array $suppliers): void
    {
        foreach ($suppliers as $supplier) {
            /** @var User $user */
            $user = new  User(Arr::except($supplier, [
                'name',
                'image',
                'star',
                'qantityVente',
                'starttime',
                'closetime',
                'delivery',
                'take_away',
                'on_site',
                'street',
                'postcode',
                'city',
                'region',
                'lat',
                'long',
                'firstName',
                'lastName'
            ]));
            /** @var supplier $cli */
            $supp =  Supplier::create(Arr::except($supplier, ['email', 'password','tel']));
            $supp->user()->save($user);
            $supp->save();
            $category = Category::find(1);
            $supp->categorys()->attach($category);
            $role = Role::find(3);
            $user->roles()->attach($role);
            $product = Product::find(1);
            $supp->products()->attach($product);
        }
    }
}
