<?php

namespace Database\Seeders;

use App\Models\Ads;
use App\Models\Adsarea;
use App\Models\File;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class AdsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $faker = \Faker\Factory::create();
        $suppliers = Supplier::all();
        foreach ($suppliers as $supplier){
            $file = new File();
            $adsarea = Adsarea::all()->random(1);
            $file->name = $faker->text(20);
            $file->path = $faker->imageUrl();
            $file->user_id = 1;
            $file->save();
            $file->refresh();

            $ads = new Ads();
            $ads->adsarea_id = $adsarea[0]->id;
            $ads->file_id = $file->id;
            $ads->supplier_id = $supplier->id;
            $ads->start_date = $faker->dateTimeInInterval('-17 day','+20 day');
            $ads->end_date = $faker->dateTimeInInterval('+29 day','+20 day');
            $ads->price = 20;
            $ads->save();
        }
    }
}
