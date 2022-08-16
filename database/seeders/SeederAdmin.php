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
    protected $password = 'password';
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
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
                'email' => 'admin@thunder-express.com',
                'firstName'=>'admin',
                'lastName'=>'admin',
                'tel' => '+21626333445'
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
           $user->status_id = 1;
            /** @var supplier $cli */
            $adm =  Admin::create(Arr::except($admin, ['email', 'password','tel']));
            $adm->user()->save($user);
            $adm->save();
            $role= Role::find(2);
            $user->roles()->attach($role);

        }
    }


}
