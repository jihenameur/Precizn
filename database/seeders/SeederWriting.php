<?php

namespace Database\Seeders;

use App\Models\Writing;
use Illuminate\Database\Seeder;

class SeederWriting extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $writing = $this->getWritingList();
        $this->createWriting($writing);
    }

    /**
     * Get available application writing list
     *
     * @return string[][]
     */
    private function getWritingList(): array
    {
        $writings = [

            ['comment' => 'Un excellent restaurant, Les pizzas sont au top.
            Je recommande ce restaurant', 'client_id' => 1, 'supplier_id' => 1, 'note' => 4],
            ['comment' => 'Staff accueillant.  Service rapide. Goût excellent de la pizza.', 'client_id' => 1, 'supplier_id' => 1, 'note' => 4],
        ];
        return $writings;
    }

    /**
     * Create writings
     *
     * @param array $writings
     */
    private function createWriting(array $writings): void
    {
        foreach ($writings as $writing) {
            Writing::create($writing);
        }
    }
}
