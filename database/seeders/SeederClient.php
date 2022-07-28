<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class SeederClient extends Seeder
{protected $password = '12345678';
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       // $role = Role::where('name',config('roles.backadmin.client'))->first();

        $clients = $this->getClientsList();
        $this->createClients($clients);
    }

    /**
     * Get clients list
     *
     * @param $role
     * @return array[]
     */
    private function getClientsList(): array
    {
        $clients = [
            [
                'password' => Hash::make($this->password),
                'email' => 'webifyTechnology@gmail.com',
                'firstname' => 'webify',
                'lastname' => 'webify technology',        
                'gender'=>1,
               // 'role_id' => $role->id
            
            ],
            [
                'email' => 'maldiv@gmail.com',
                'gender'=>1,
                'firstname' => 'Maldiv',
                'lastname' => 'Quest',
                'password' => Hash::make($this->password),
           
               // 'role_id' => $role->id
       


            ]
        ];
        return $clients;
    }

    /**
     * Create suppliers list into the DB
     *
     * @param array $suppliers
     */
    private function createClients(array $clients): void
    {
        foreach ($clients as $client) {
            /** @var User $user */
            $user = User::create(Arr::except($client, ['firstname', 'lastname', 'gender']));
            /** @var supplier $cli */
            $cli = new Client(Arr::except($client, ['email', 'password']));
            $cli->save();
            $cli->user()->save($user);
            $role= Role::find(5);
            $user->roles()->attach($role);
        }
    }

}
