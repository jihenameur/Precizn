<?php

namespace Database\Seeders;

use App\Models\Annonces;
use Illuminate\Database\Seeder;

class SeederAnnonce extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $annonces = $this->getAnnonceList();
        $this->createAnnonce($annonces);
    }

    /**
     * Get available application annonces list
     *
     * @return string[][]
     */
    private function getAnnonceList(): array
    {
        $annonces = [
            
            ['description' => 'N\'oubliez pas votre offre thunder express', 'supplier_id'=>1]
        ];
        return $annonces;
    }

    /**
     * Create annonces
     *
     * @param array $annonces
     */
    private function createAnnonce(array $annonces): void
    {
        foreach ($annonces as $annonce) {
            Annonces::create($annonce);
           
        }
    }
}
