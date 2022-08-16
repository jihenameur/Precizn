<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

class SuperAdminSeeder extends Seeder
{
    protected $password = 'password';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $SuperAdmins = $this->getSuperAdminList();
        $this->createSuperAdmin($SuperAdmins);
    }
     /**
     * Get clients list
     *
     * @param $role
     * @return array[]
     */
    private function getSuperAdminList(): array
    {
        $SuperAdmins = [
            [
                'gender' => '1',
                'password' => Hash::make($this->password),
                'email' => 'superadmin@gmail.com',
                'firstName'=>'superadmin',
                'lastName'=>'superadmin',
                'tel' => '+21626000000'
            ]
        ];
        return $SuperAdmins;
    }

    /**
     * Create suppliers list into the DB
     *
     * @param array $admins
     */
    private function createSuperAdmin(array $SuperAdmins): void
    {
        foreach ($SuperAdmins as $SuperAdmin) {
            /** @var User $user */
            $user = new  User(Arr::except($SuperAdmin, [
           'firstName',
           'gender',
           'lastName']));
            /** @var supplier $cli */
            $adm =  Admin::create(Arr::except($SuperAdmin, ['email', 'password','tel']));
            $adm->user()->save($user);
            $adm->save();
            $role= Role::find(1);
            $user->roles()->attach($role);

        }
    }

}
