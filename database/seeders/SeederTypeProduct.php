<?php

namespace Database\Seeders;

use App\Models\TypeProduct;
use Illuminate\Database\Seeder;

class SeederTypeProduct extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types_product = $this->getTypesProductList();
        $this->createCategories($types_product);
    }

    /**
     * Get available application roles list
     *
     * @return string[][]
     */
    private function getTypesProductList(): array
    {
        $types_product = [
    
            ['name' => 'Poissons','parent_id'=>0,'order_id'=>1,'description'=>'Poissons'],
            ['name' => 'Légumes','parent_id'=>0,'order_id'=>1,'description'=>'Légumes'],
            ['name' => 'Produits salées (apéritifs) ','parent_id'=>0,'order_id'=>1,'description'=>'Produits salées (apéritifs) '],
            ['name' => 'Huile et vinaigre ','parent_id'=>0,'order_id'=>1,'description'=>'Huile et vinaigre '],
            ['name' => 'Sauces et condiments ','parent_id'=>0,'order_id'=>1,'description'=>'Sauces et condiments  '],
            ['name' => 'Plats cuisinés ','parent_id'=>0,'order_id'=>1,'description'=>'Plats cuisinés '],
            ['name' => 'Epices et sels ','parent_id'=>0,'order_id'=>1,'description'=>'Epices et sels '],
            ['name' => 'Pâtes ','parent_id'=>0,'order_id'=>1,'description'=>'Pâtes '],
            ['name' => 'Grains','parent_id'=>0,'order_id'=>1,'description'=>'Grains'],
            ['name' => 'Légumes secs','parent_id'=>0,'order_id'=>1,'description'=>'Légumes secs'],
            
            ['name' => 'Biscuits et gâteaux','parent_id'=>0,'order_id'=>1,'description'=>'1)	Biscuits et gâteaux'],
            ['name' => 'Chocolat et bonbons','parent_id'=>0,'order_id'=>1,'description'=>'Chocolat et bonbons'],
            ['name' => 'Sucres','parent_id'=>0,'order_id'=>1,'description'=>'Sucres'],
            ['name' => 'Farine Amidon et aide à la pâtisserie ','parent_id'=>0,'order_id'=>1,'description'=>'Farine Amidon et aide à la pâtisserie  '],
            ['name' => 'Dessert à préparer et préparation traditionnelle ','parent_id'=>0,'order_id'=>1,'description'=>'Dessert à préparer et préparation traditionnelle '],
            ['name' => 'Préparation salées  ','parent_id'=>0,'order_id'=>1,'description'=>'Préparation salées  '],
            ['name' => 'Les sirop & arômes ','parent_id'=>0,'order_id'=>1,'description'=>'Les sirop & arômes  '],
            ['name' => 'Céréales et barres ','parent_id'=>0,'order_id'=>1,'description'=>'Céréales et barres '],
            ['name' => 'Produits à tartiner','parent_id'=>0,'order_id'=>1,'description'=>'Produits à tartiner'],
            ['name' => 'Boulangerie et viennoiserie','parent_id'=>0,'order_id'=>1,'description'=>'Boulangerie et viennoiserie'],
            
            ['name' => 'Produits laitiers','parent_id'=>0,'order_id'=>1,'description'=>'Produits laitiers'],
            ['name' => 'Yaourt & dessert','parent_id'=>0,'order_id'=>1,'description'=>'Yaourt & dessert'],
            ['name' => 'Crèmes ','parent_id'=>0,'order_id'=>1,'description'=>'Crèmes '],
            ['name' => 'Fromages ','parent_id'=>0,'order_id'=>1,'description'=>'Fromages'],
            ['name' => 'Boissons chaudes','parent_id'=>0,'order_id'=>1,'description'=>'Boissons chaudes'],
            ['name' => 'Eau minérale ','parent_id'=>0,'order_id'=>1,'description'=>'Eau minérale '],
            ['name' => 'Boissons gazeuses','parent_id'=>0,'order_id'=>1,'description'=>'Boissons gazeuses'],
            ['name' => 'Boissons énergétiques ','parent_id'=>0,'order_id'=>1,'description'=>'Boissons énergétiques '],
            ['name' => 'Jus et boissons concentrées','parent_id'=>0,'order_id'=>1,'description'=>'Jus et boissons concentrées'],
            ['name' => 'Produits surgelés sucrés ','parent_id'=>0,'order_id'=>1,'description'=>'Produits surgelés sucrés '],
            
            ['name' => 'Produits surgelés de marché ','parent_id'=>0,'order_id'=>1,'description'=>'Produits surgelés de marché '],
            ['name' => 'Glaçons ','parent_id'=>0,'order_id'=>1,'description'=>'Glaçons '],
            ['name' => 'Volaille et oeufs  ','parent_id'=>0,'order_id'=>1,'description'=>'Volaille et oeufs  '],
            ['name' => 'Boucherie ','parent_id'=>0,'order_id'=>1,'description'=>'Boucherie  '],
            ['name' => 'Poissons et produits de mer  ','parent_id'=>0,'order_id'=>1,'description'=>'Poissons et produits de mer  '],
            ['name' => 'Fruits et légumes ','parent_id'=>0,'order_id'=>1,'description'=>'Fruits et légumes '],
            ['name' => 'Traiteurs de salées  ','parent_id'=>0,'order_id'=>1,'description'=>'Traiteurs de salées  '],
            ['name' => 'Traiteurs de sucrées ','parent_id'=>0,'order_id'=>1,'description'=>'Traiteurs de sucrées '],
            ['name' => 'Nourriture pour les animaux ','parent_id'=>0,'order_id'=>1,'description'=>'Nourriture pour les animaux '],
            
            ['name' => 'Accessoires d’animaux ','parent_id'=>0,'order_id'=>1,'description'=>'Accessoires d’animaux '],
            ['name' => "Produits d'hygiène, beauté et soins ",'parent_id'=>0,'order_id'=>1,'description'=>"Produits d'hygiène, beauté et soins "],
            ['name' => 'Produits de nettoyage  (apéritifs) ','parent_id'=>0,'order_id'=>1,'description'=>'Produits de nettoyage  '],
            ['name' => 'Monde bébé','parent_id'=>0,'order_id'=>1,'description'=>'Monde bébé' ], 
            ['name' => 'Accessoires et matériels de cuisine, de jardin et d’autre produits d’entretien  ','parent_id'=>0,'order_id'=>1,'description'=>'Accessoires et matériels de cuisine, de jardin et d’autre produits d’entretien '],
           
            ['name' => 'Produits conservés','parent_id'=>0,'order_id'=>1,'description'=>'Produits conservés'],
            ['name' => 'Fruits secs, et séchés','parent_id'=>0,'order_id'=>1,'description'=>'Fruits secs, et séchés '],
            ['name' => "Produits d'épicerie",'parent_id'=>0,'order_id'=>1,'description'=>"Produits d'épicerie"],

        ];
        return $types_product;
    }

    /**
     * Create categories
     *
     * @param array $categories
     */
    private function createCategories(array $types_product): void
    {
        foreach ($types_product as $tupe_product) {
            TypeProduct::create($tupe_product);
        }
    }
}
