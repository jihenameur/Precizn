<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class SeederAdmin extends Seeder
{
    protected $password = '12345678';
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = Role::where('name',config('roles.backadmin.admin'))->first();

        $admins = $this->getAdminList();
        $this->createAdmin($admins);
    }

    /**
     * Get clients list
     *
     * @param $role
     * @return array[]
     */
    private function getAdminList(): array
    {
        $admins = [
            [
                'gender' => '1',
                'password' => Hash::make($this->password),
                'email' => 'dahman@gmail.com',
                'firstName'=>'mohammed',
                'lastName'=>'salah'
            ]
        ];
        return $admins;
    }

    /**
     * Create suppliers list into the DB
     *
     * @param array $admins
     */
    private function createAdmin(array $admins): void
    {
        foreach ($admins as $admin) {
            /** @var User $user */
            $user = new  User(Arr::except($admin, [
           'firstName',
           'gender',
           'lastName']));
            /** @var supplier $cli */
            $adm =  Admin::create(Arr::except($admin, ['email', 'password']));
            $adm->user()->save($user);
            $adm->save();
            $role= Role::find(1);
            $user->roles()->attach($role);

        }
    }


}
