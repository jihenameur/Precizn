<?php

namespace Database\Seeders;

use App\Models\Delivery;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class SeederDelivery extends Seeder
{
    protected $password = '12345678';

     /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $deliverys = $this->getDeliveryList();
        $this->createDelivery($deliverys);
    }

    /**
     * Get available application deliverys list
     *
     * @return string[][]
     */
    private function getDeliveryList(): array
    {
        $deliverys = [

            [

             'lat'=>35.886399,
             'long'=>10.5915072,
            'available'=>1,
            'email'=> 'devilry123@gmail.com',
            'password'=>Hash::make($this->password),
            'vehicle'=>1],
            [

                'lat'=>35.816399,
                'long'=>10.5115072,
                'available'=>1,
                'email'=> 'devilry1234@gmail.com',
                'password'=>Hash::make($this->password),
                'vehicle'=>1],
            [

                'lat'=>35.806399,
                'long'=>10.5015072,
                'available'=>1,
                'email'=> 'devilry1235@gmail.com',
                'password'=>Hash::make($this->password),
                'vehicle'=>1],
        ];
        return $deliverys;
    }

    /**
     * Create delivery
     *
     * @param array $deliverys
     */
    private function createDelivery(array $deliverys): void
    {
        foreach ($deliverys as $delivery) {
            $user =  User::create(Arr::except($delivery, [  'available','lat','long', 'vehicle']));
            $deli =  Delivery::create(Arr::except($delivery, ['email', 'password'   ]));
            $deli->user()->save($user);
            $deli->save();
            $role= Role::find(4);
            $user->roles()->attach($role);

        }
    }
}
