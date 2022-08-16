<?php

namespace Database\Seeders;

use App\Models\Option;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class SeederOption extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $options = $this->getOptionList();
        $this->createProduct($options);
    }

    /**
     * Get available application options list
     *
     * @return string[][]
     */
    private function getOptionList(): array
    {
        $options = [

            [
                'name' => 'Hrissa',
                'description' => 'hrissa',
                'price' => 0,
                'default' => 0
            ],
            [
                'name' => 'Mayonnaise',
                'description' => 'Mayonnaise',
                'price' => 0,
                'default' => 0
            ],
            [
                'name' => 'Oignons',
                'description' => 'Oignons',
                'price' => 0,
                'default' => 0
            ],
            [
                'name' => 'Fromage',
                'description' => 'fromage.',
                'price' => 1,
                'default' => 1
            ],
            [
                'name' => 'Jambon',
                'description' => 'Jambon.',
                'price' => 1,
                'default' => 1
            ]
        ];
        return $options;
    }

    /**
     * Create options
     *
     * @param array $options
     */
    private function createProduct(array $options): void
    {
        foreach ($options as $option) {
            $op = new Option(Arr::except($option, []));
            $op->save();
        }
    }
}
