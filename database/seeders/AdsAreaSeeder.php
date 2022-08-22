<?php

namespace Database\Seeders;

use App\Models\Adsarea;
use Illuminate\Database\Seeder;

class AdsAreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       $areas =  ['HOME_1','HOME_2','HOME_3'];
        for($i = 0; $i < count($areas); $i++){
            $adsarea = new Adsarea();
            $adsarea->title = $areas[$i];
            $adsarea->save();
        }
    }
}
