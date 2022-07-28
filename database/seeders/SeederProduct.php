<?php

namespace Database\Seeders;

use App\Models\Panier;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class SeederProduct extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $products = $this->getProductList();
        $this->createProduct($products);
    }

    /**
     * Get available application products list
     *
     * @return string[][]
     */
    private function getProductList(): array
    {
        $products = [

            ['name' => 'pizza',
            'image' => 'C:/Users/windows/Downloads/coca.png',
             'description' => 'Pizza 4fromages on dirait une soupe tellement pâte trop fine. Cadre agréable mais la pizza non.',
             'private'=>0,
             'default_price'=>7],
            ['name' => 'Spaghetti',
             'image' => 'C:/Users/windows/Downloads/coca.png',
              'description' => 'Spaghetti à l \'amatriciana, spaghetti aux boulettes de thon, spaghetti au saumon et épinards, spaghetti au poulet et pesto, spaghetti aux fruits de mer... Il y en a pour tous les goûts.',
              'private'=>0,
              'default_price'=>10]
        ];
        return $products;
    }

    /**
     * Create products
     *
     * @param array $products
     */
    private function createProduct(array $products): void
    {
        foreach ($products as $product) {
            $prod= new Product(Arr::except($product,[]));
            $prod->save();
            $supp=Supplier::find(1);
            $prod->suppliers()->attach($supp, ['price' => 9]);

        }
    }
}
