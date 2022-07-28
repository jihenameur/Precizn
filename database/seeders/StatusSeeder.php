<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $status = $this->getStatusList();
        $this->createStatus($status);
    }

    private function getStatusList(): array
    {
        $status = [
            ['name' => 'actif'],
            ['name' => 'disabled'],
            ['name' => 'blocked'],
            ['name' => 'in_progress']
        ];
        return $status;
    }

    private function createStatus(array $status): void
    {
        foreach ($status as $stat) {
            Status::create($stat);
        }
    }
}
