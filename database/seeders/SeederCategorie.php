<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class SeederCategorie extends Seeder
{
 /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = $this->getCategoriesList();
        $this->createCategories($categories);
    }

    /**
     * Get available application roles list
     *
     * @return string[][]
     */
    private function getCategoriesList(): array
    {
        $categories = [
            ['name' => 'Restaurant','parent_id'=>0,'order_id'=>1,'description'=>'Restaurant'],
            ['name' => 'Épicerie','parent_id'=>0,'order_id'=>1,'description'=>'Epicerie'],
            ['name' => 'Supermarché','parent_id'=>0,'order_id'=>1,'description'=>'Supermarché'],
            ['name' => 'Commerce de bouche','parent_id'=>0,'order_id'=>1,'description'=>'Commerce de bouche'],
            ['name' => 'Magasin','parent_id'=>0,'order_id'=>1,'description'=>'Magasin'],
            ['name' => 'Fleuriste','parent_id'=>0,'order_id'=>1,'description'=>'Fleuriste'],
            ['name' => 'Boutique','parent_id'=>0,'order_id'=>1,'description'=>'Boutique'],
            ['name' => 'Café resto','parent_id'=>0,'order_id'=>1,'description'=>'Café resto'],
            ['name' => ' Pâtisserie & boulangerie','parent_id'=>0,'order_id'=>1,'description'=>' Pâtisserie & boulangerie'],
            ['name' => 'Animalerie','parent_id'=>0,'order_id'=>1,'description'=>'Animalerie'],
            ['name' => 'Parfumerie','parent_id'=>0,'order_id'=>1,'description'=>'Parfumerie'],
            ['name' => 'Traiteur','parent_id'=>0,'order_id'=>1,'description'=>'Traiteur'],
            ['name' => 'Volaillerie','parent_id'=>0,'order_id'=>1,'description'=>'Volaillerie'],
            ['name' => 'Boucherie','parent_id'=>0,'order_id'=>1,'description'=>'Boucherie'],
            ['name' => 'Fromagerie','parent_id'=>0,'order_id'=>1,'description'=>'Fromagerie'],
            ['name' => 'Poissonnerie','parent_id'=>0,'order_id'=>1,'description'=>'Poissonnerie'],
            ['name' => 'Fruits/Légumes','parent_id'=>0,'order_id'=>1,'description'=>'Fruits/Légumes'],
           
          







            



        
            
           
            
            
            
            
            
            
            
            
            
            
            
            





        ];
        return $categories;
    }

    /**
     * Create categories
     *
     * @param array $categories
     */
    private function createCategories(array $categories): void
    {
        foreach ($categories as $categorie) {
            Category::create($categorie);
        }
    }

}