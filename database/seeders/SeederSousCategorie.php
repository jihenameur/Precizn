<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class SeederSousCategorie extends Seeder
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
            ['name' => 'Africaine : éthiopienne','parent_id'=>1,'order_id'=>2,'description'=>'Africaine : éthiopienne'],
            ['name' => 'Africaine : autre','parent_id'=>1,'order_id'=>2,'description'=>'Africaine : autre'],
            ['name' => 'Américaine','parent_id'=>1,'order_id'=>2,'description'=>'Américaine'],
            ['name' => 'Argentine','parent_id'=>1,'order_id'=>2,'description'=>'Argentine'],
            ['name' => 'Asiatique','parent_id'=>1,'order_id'=>2,'description'=>'Asiatique'],
            ['name' => 'Asiatique : autre','parent_id'=>1,'order_id'=>2,'description'=>'Asiatique : autre'],
            ['name' => 'Asiatique (fusion) ','parent_id'=>1,'order_id'=>2,'description'=>'Asiatique : autre'],
            ['name' => 'Boulangerie ','parent_id'=>1,'order_id'=>2,'description'=>'Boulangerie'],
            ['name' => 'Boulangerie et pâtisserie ','parent_id'=>1,'order_id'=>2,'description'=>'Boulangerie et pâtisserie'],
            ['name' => 'Bangladaise ','parent_id'=>1,'order_id'=>2,'description'=>'Bangladaise'],
            ['name' => 'Cuisine de bar/pub ','parent_id'=>1,'order_id'=>2,'description'=>'Cuisine de bar/pub'],
            ['name' => 'Barbecue','parent_id'=>1,'order_id'=>2,'description'=>'Barbecue'],
            ['name' => 'Brésilienne','parent_id'=>1,'order_id'=>2,'description'=>'Brésilienne'],
            ['name' => 'Petit-déjeuner et brunch','parent_id'=>2,'order_id'=>1,'description'=>'Petit-déjeuner et brunch'],
            ['name' => 'Bubble Tea','parent_id'=>1,'order_id'=>2,'description'=>'Bubble Tea'],
            ['name' => 'Burgers','parent_id'=>1,'order_id'=>2,'description'=>'Burgers'],
            ['name' => 'Birmane','parent_id'=>1,'order_id'=>2,'description'=>'Birmane'],
            ['name' => 'Burrito','parent_id'=>1,'order_id'=>2,'description'=>'Burrito'],
            ['name' => 'Cadienne/Créole','parent_id'=>1,'order_id'=>2,'description'=>'Cadienne/Créole'],
            ['name' => 'Gâteau','parent_id'=>1,'order_id'=>2,'description'=>'Gâteau'],
            ['name' => 'Caribéenne','parent_id'=>1,'order_id'=>2,'description'=>'Caribéenne'],
            ['name' => 'Poulet','parent_id'=>1,'order_id'=>2,'description'=>'Poulet'],
            ['name' => 'Chilienne','parent_id'=>1,'order_id'=>2,'description'=>'Chilienne'],
            ['name' => 'Chinoise : cantonaise','parent_id'=>1,'order_id'=>2,'description'=>'Chinoise : cantonaise'],
            ['name' => 'Chinoise : fondue chinoise','parent_id'=>1,'order_id'=>2,'description'=>'Chinoise : fondue chinoise'],
            ['name' => 'Chinoise : nouilles et raviolis','parent_id'=>1,'order_id'=>2,'description'=>'Chinoise : nouilles et raviolis'],
            ['name' => 'Chinoise : autre','parent_id'=>1,'order_id'=>2,'description'=>'Chinoise : autre'],
            ['name' => 'Chinoise : sichuanaise','parent_id'=>1,'order_id'=>2,'description'=>'Chinoise : sichuanaise'],
            ['name' => 'Chinoise : taïwanaise','parent_id'=>1,'order_id'=>2,'description'=>'Chinoise : taïwanaise'],
            ['name' => 'Café et thé','parent_id'=>1,'order_id'=>2,'description'=>'Café et thé'],
            ['name' => 'Colombienne','parent_id'=>1,'order_id'=>2,'description'=>'Colombienne'],
            ['name' => 'Crêpes ou crêperie','parent_id'=>1,'order_id'=>2,'description'=>'Crêpes ou crêperie'],
            ['name' => 'Plats réconfortants','parent_id'=>1,'order_id'=>2,'description'=>'Plats réconfortants'],
            ['name' => 'Desserts','parent_id'=>1,'order_id'=>2,'description'=>'Desserts'],
            ['name' => 'Desserts : autre','parent_id'=>1,'order_id'=>2,'description'=>'Desserts :autre'],
            ['name' => 'Équatorienne','parent_id'=>1,'order_id'=>2,'description'=>'Équatorienne'],
            ['name' => 'Égyptienne','parent_id'=>1,'order_id'=>2,'description'=>'Égyptienne'],
            ['name' => 'Empanadas','parent_id'=>1,'order_id'=>2,'description'=>'Empanadas'],
            ['name' => 'Européenne','parent_id'=>1,'order_id'=>2,'description'=>'Européenne'],
            ['name' => 'Européenne : autre','parent_id'=>1,'order_id'=>2,'description'=>'Européenne : autre'],
            ['name' => 'Philippine','parent_id'=>1,'order_id'=>2,'description'=>'Philippine'],
            ['name' => 'Fish & chips','parent_id'=>1,'order_id'=>2,'description'=>'Fish & chips'],
            ['name' => 'Poisson et fruits de mer','parent_id'=>1,'order_id'=>2,'description'=>'Poisson et fruits de mer'],
            ['name' => 'Française','parent_id'=>1,'order_id'=>2,'description'=>'Française'],
            ['name' => 'Géorgienne','parent_id'=>1,'order_id'=>2,'description'=>'Géorgienne'],
            ['name' => 'Allemande','parent_id'=>1,'order_id'=>2,'description'=>'Allemande'],
            ['name' => 'Gastronomique','parent_id'=>1,'order_id'=>2,'description'=>'Gastronomique'],
            ['name' => 'Grecque','parent_id'=>1,'order_id'=>2,'description'=>'Grecque'],
            ['name' => 'Guatémaltèque','parent_id'=>1,'order_id'=>2,'description'=>'Guatémaltèque'],
            ['name' => 'Halal','parent_id'=>1,'order_id'=>2,'description'=>'Halal'],
            ['name' => 'Hawaïenne','parent_id'=>1,'order_id'=>2,'description'=>'Hawaïenne'],
            ['name' => 'Saine','parent_id'=>1,'order_id'=>2,'description'=>'Saine'],
            ['name' => 'Glaces et yaourts glacés','parent_id'=>1,'order_id'=>2,'description'=>'Glaces et yaourts glacés'],
            ['name' => 'Indienne','parent_id'=>1,'order_id'=>2,'description'=>'Indienne'],
            ['name' => 'Indonésienne','parent_id'=>1,'order_id'=>2,'description'=>'Indonésienne'],
            ['name' => 'Italienne','parent_id'=>1,'order_id'=>2,'description'=>'Italienne'],
            ['name' => 'Japonaise : autre','parent_id'=>1,'order_id'=>2,'description'=>'Japonaise : autre'],
            ['name' => 'Japonaise : ramens','parent_id'=>1,'order_id'=>2,'description'=>'Japonaise : ramens'],
            ['name' => 'Japonaise : sushis','parent_id'=>1,'order_id'=>2,'description'=>'Japonaise : sushis'],
            ['name' => 'Jus et smoothies','parent_id'=>1,'order_id'=>2,'description'=>'Jus et smoothies'],
            ['name' => 'Kebab','parent_id'=>1,'order_id'=>2,'description'=>'Kebab'],
            ['name' => 'Coréenne','parent_id'=>1,'order_id'=>2,'description'=>'Coréenne'],
            ['name' => 'Casher','parent_id'=>1,'order_id'=>2,'description'=>'Casher'],
            ['name' => 'Latino-américaine : autre','parent_id'=>1,'order_id'=>2,'description'=>'Latino-américaine : autre'],
            ['name' => 'Libanaise','parent_id'=>1,'order_id'=>2,'description'=>'Libanaise'],
            ['name' => 'Malaisienne','parent_id'=>1,'order_id'=>2,'description'=>'Malaisienne'],
            ['name' => 'Méditerranéenne','parent_id'=>1,'order_id'=>2,'description'=>'Méditerranéenne'],
            ['name' => 'Mexicaine','parent_id'=>1,'order_id'=>2,'description'=>'Mexicaine'],
            ['name' => 'Moyen-orientale','parent_id'=>1,'order_id'=>2,'description'=>'Moyen-orientale'],
            ['name' => 'Australienne (nouvelle cuisine)','parent_id'=>1,'order_id'=>2,'description'=>'Australienne (nouvelle cuisine)'],
            ['name' => 'Marocaine','parent_id'=>1,'order_id'=>2,'description'=>'Marocaine'],
            ['name' => 'Pakistanaise','parent_id'=>1,'order_id'=>2,'description'=>'Pakistanaise'],
            ['name' => 'Péruvienne','parent_id'=>1,'order_id'=>2,'description'=>'Péruvienne'],
            ['name' => 'Pizzas','parent_id'=>1,'order_id'=>2,'description'=>'Pizzas'],
            ['name' => 'Poke (poisson cru)','parent_id'=>1,'order_id'=>2,'description'=>'Poke (poisson cru)'],
            ['name' => 'Portugaise','parent_id'=>1,'order_id'=>2,'description'=>'Portugaise'],
            ['name' => 'Russe','parent_id'=>1,'order_id'=>2,'description'=>'Russe'],
            ['name' => 'Salades/Sandwichs','parent_id'=>1,'order_id'=>2,'description'=>'Salades/Sandwichs'],
            ['name' => 'Fruits de mer','parent_id'=>1,'order_id'=>1,'description'=>'Fruits de mer'],
            ['name' => 'Snacks','parent_id'=>1,'order_id'=>1,'description'=>'Snacks'],
            ['name' => 'Afro-américaine','parent_id'=>1,'order_id'=>2,'description'=>'Afro-américaine'],
            ['name' => 'Plats du sud des États-Unis','parent_id'=>1,'order_id'=>2,'description'=>'Plats du sud des États-Unis'],
            ['name' => 'Espagnole','parent_id'=>1,'order_id'=>2,'description'=>'Espagnole'],
            ['name' => 'Grill','parent_id'=>1,'order_id'=>2,'description'=>'Grill'],
            ['name' => 'Sushis','parent_id'=>1,'order_id'=>2,'description'=>'Sushis'],
            ['name' => 'Tacos','parent_id'=>1,'order_id'=>2,'description'=>'Tacos'],
            ['name' => 'Tex Mex','parent_id'=>1,'order_id'=>2,'description'=>'Tex Mex'],
            ['name' => 'Thaï','parent_id'=>1,'order_id'=>2,'description'=>'Thaï'],
            ['name' => 'Turque','parent_id'=>1,'order_id'=>2,'description'=>'Turque'],
            ['name' => 'Végétarienne/Végétalienne','parent_id'=>1,'order_id'=>2,'description'=>'TurVégétarienne/Végétalienneque'],
            ['name' => 'Vénézuélienne','parent_id'=>1,'order_id'=>2,'description'=>'Vénézuélienne'],
            ['name' => 'Vietnamienne','parent_id'=>1,'order_id'=>2,'description'=>'Vietnamienne'],
            ['name' => 'Ailes de poulet','parent_id'=>1,'order_id'=>2,'description'=>'Ailes de poulet'],
            ['name' => 'Autre','parent_id'=>1,'order_id'=>2,'description'=>'Autre'],

















































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
