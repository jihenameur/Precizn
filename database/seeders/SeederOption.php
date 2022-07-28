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

            ['name' => 'hrissa',
             'description' => 'hrissa',
             'price'=>0,
             'default'=>1],
            ['name' => 'fromage',
              'description' => 'fromage.',
              'price'=>1,
              'default'=>1]
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
            $op= new Option(Arr::except($option,[]));
            $op->save();
            $product= Product::find(1);
            $op->products()->attach($product, [
                'supplier_id' => 1

              ]);


        }
    }
}
