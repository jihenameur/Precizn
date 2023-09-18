<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
  /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = $this->getRolesList();
        $this->createRoles($roles);
    }

    /**
     * Get available application roles list
     *
     * @return string[][]
     */
    private function getRolesList(): array
    {
        $roles = [
            ['name' => 'superadmin', 'short_name' => 'sa', 'description' => 'SuperAdmin , has all rights in the application'],
            ['name' => 'admin', 'short_name' => 'ad', 'description' => 'Admin , has global limited rights in the application'],
            ['name' => 'employee', 'short_name' => 'emp', 'description' => 'Employee , has global limited rights in the application']

        ];
        return $roles;
    }

    /**
     * Create roles
     *
     * @param array $roles
     */
    private function createRoles(array $roles): void
    {
        foreach ($roles as $role) {
            Role::create($role);
        }
    }

}
