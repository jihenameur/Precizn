<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class SeederTags extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tags = $this->getTagsList();
        $this->createTags($tags);
    }

    /**
     * Get available application roles list
     *
     * @return string[][]
     */
    private function getTagsList(): array
    {
        $tags = [
            ['name' => 'Thon '],
            ['name' => 'Sardines'],
            ['name' => 'Tomates '],
            ['name' => 'Maies'],
            ['name' => 'Harissa…'],
            ['name' => 'Chips '],
            ['name' => 'Pringles  '],
            ['name' => 'Tucs '],
            ['name' => 'Fruits sec '],
            ['name' => 'Bâtonnets '],
            ['name' => 'Huile d’olive'],
            ['name' => 'Vinaigre '],
            ['name' => 'Sauces : Moutarde, Ketchup, Mayonnaise… '],
            ['name' => 'Condiments : Cornichons et olive…  '],
            ['name' => 'Salades cuites '],
            ['name' => 'Soupes '],
            ['name' => 'Brouillons '],
            ['name' => 'Epices et herbes'],
            ['name' => 'Sels et poivre '],
            ['name' => 'Spaghetti '],
            ['name' => 'Chorba  '],
            ['name' => 'Mhamsas '],
            ['name' => 'Malsouka… '],
            ['name' => 'Semoule '],
            ['name' => 'riz'],
            ['name' => 'Couscous  '],
            ['name' => 'Orge… '],
            ['name' => 'Lentilles   '],
            ['name' => 'Maïs '],
            ['name' => 'Haricot '],
            ['name' => 'Corète (Mloukhia) '],
            ['name' => 'Biscuits (Gaufrette…) '],
            ['name' => 'Gâteaux (Brownies et cakes…)'],
            ['name' => 'Chocolat (Chocolat poudre, barre de désert…) '],
            ['name' => 'Bonbons et sucreries (Chewing-gum, Bonbons…)    '],
            ['name' => 'Sucres vanille et poudre '],
            ['name' => 'Sucre roux de canne '],
            ['name' => 'Bicarbonate'],
            ['name' => 'Farines '],
            ['name' => 'Levure'],
            ['name' => 'Crème dessert'],
            ['name' => 'Amidon'],
            ['name' => 'Poudre de cacao '],
            ['name' => 'Les crèmes'],
            ['name' => 'Préparation flan, glace, crêpes, crème chantilly, cake, cookies…'],
            ['name' => 'Mesfouf, bsissa, Pois chiche, Droo, Zrir '],
            ['name' => 'Préparation de break '],
            ['name' => 'Préparation de Pizza'],
            ['name' => 'Préparation de Fricassé'],
            ['name' => 'Préparation de Batbout'],
            ['name' => 'Arômes naturels (Eau de fleur orange et de rose…)'],
            ['name' => 'Sirops'],
            ['name' => 'Sauces de dessert'],
            ['name' => 'Flocons d’avoines, barres et autres types de céréales.'],
            ['name' => 'Chamia, miel, confiture, beurre de cacahuète, et d’autres pâtes à tartiner…'],
            ['name' => 'Croissant, cake et brioche'],
            ['name' => 'Les pains et les biscottes.'],
            ['name' => 'Lait entier et demi écrémé'],
            ['name' => 'Lben, Raieb'],
            ['name' => 'Ricotta'],
            ['name' => 'Beurre'],
            ['name' => 'Yaourt à boire'],
            ['name' => 'Yaourt blancs'],
            ['name' => 'Yaourt dessert'],
            ['name' => 'Glaces'],
            ['name' => 'Crème fraîche'],
            ['name' => 'Crème chantier'],
            ['name' => 'Fromages râpés, carrés et en morceaux '],
            ['name' => 'Thé '],
            ['name' => 'Cafés'],
            ['name' => 'Chocolat en poudre'],
            ['name' => 'Eau minérales tous les tailles'],
            ['name' => 'Boissons gazeuses cannet et autres tailles'],
            ['name' => 'Boissons énergétiques en cannet et autres'],
            ['name' => 'Jus '],
            ['name' => 'Boissons concentrées  '],
            ['name' => 'Gâteaux, glaces et dessert '],
            ['name' => 'Viande, poissons, plats préparer, fruits et légumese'],
            ['name' => 'Glaçons alimentaires'],
            ['name' => 'Glaçons non alimentaires'],
            ['name' => 'Oeufs'],
            ['name' => 'Viande blancs '],
            ['name' => 'Salami et Jambon  '],
            ['name' => 'Saucisse'],
            ['name' => 'Viande rouge'],
            ['name' => 'Poissons'],
            ['name' => 'Fruit de mer'],
            ['name' => 'Frais fruits de saison'],
            ['name' => 'Frais legumes'],
            ['name' => 'Plats cuisinés  '],
            ['name' => 'Des minis salées '],
            ['name' => 'Sandwich et Pizzas'],
            ['name' => 'Rôtisserie et grillade'],
            ['name' => 'Mini gâteaux, cookies et d’autre sucrées '],
            ['name' => 'Aliments pour chats, chiens et autres animaux'],
            ['name' => 'Accessoires pour chats chiens et autres animaux'],
            ['name' => 'Parfum, déodorant, roulants…'],
            ['name' => 'Dentifrice, brosse à dents '],
            ['name' => 'Savons, shampoing, et gel à douche…'],
            ['name' => 'Omo, Dettol, Javel, Ajax.. '],
            ['name' => 'Couches '],
            ['name' => 'Nourritures'],
            ['name' => "Produits d'hygiène & et nettoyage"]
        ];
        return $tags;
    }

    /**
     * Create tags
     *
     * @param array $tags
     */
    private function createTags(array $tags): void
    {
        foreach ($tags as $tag) {
            Tag::create($tag);
        }
    }
}
