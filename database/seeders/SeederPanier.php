<?php

namespace Database\Seeders;

use App\Models\Panier;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class SeederPanier extends Seeder
{
   /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $paniers = $this->getPanierList();
        $this->createPanier($paniers);
    }

    /**
     * Get available application products list
     *
     * @return string[][]
     */
    private function getPanierList(): array
    {
        $paniers = [
           [
            'price'=>0
           ]
        ];
        return $paniers;
    }

    /**
     * Create paniers
     *
     * @param array $paniers
     */
    private function createPanier(array $paniers): void
    {
        foreach ($paniers as $panier) {
            $pan = new Panier(Arr::except($panier, ['price']));
            $pan->save();
            $product=Product::find(1);
            $pan->products()->attach($product, [
                'quantity' => 2
             
              ]);
        }
    }
}
