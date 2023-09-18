<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
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
                'password' => Hash::make($this->password),
                'email' => 'amjihen21@gmail.com',
                'firstName'=>'superadmin',
                'lastName'=>'superadmin',
                'phone' => '+21696006433',
                'social_reason'=>'precizn',
                'address'=>'sousse'
        
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
                'social_reason'
           ]));
            $adm =  Admin::create(Arr::except($SuperAdmin, ['firstName',
            'lastName',
            'email',
            'password',
            'phone','address']));
            $adm->user()->save($user);
            $adm->save();
            $role= Role::find(1);
            $user->roles()->attach($role);

        }
    }

}
