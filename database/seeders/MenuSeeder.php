<?php

namespace Database\Seeders;

use App\Models\Adsarea;
use App\Models\File;
use App\Models\Menu;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
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
            $file->name = $faker->text(20);
            $file->path = $faker->imageUrl();
            $file->user_id = 1;
            $file->save();
            $file->refresh();

            $menu = new Menu();
            $menu->name = "SUB MENU 1";
            $menu->description = $faker->text(50);
            $menu->file_id = $file->id;
            $menu->supplier_id = $supplier->id;
            $menu->position = 1;
            $menu->save();
            $menu->refresh();

            $products = $supplier->products()->limit(5)->get();
            $i = 1;
            foreach ($products as $product){
                $menu->products()->attach($product,['position' => $i]);
                $i++;
            }
            $menu->save();

            $file = new File();
            $file->name = $faker->text(20);
            $file->path = $faker->imageUrl();
            $file->user_id = 1;
            $file->save();
            $file->refresh();

            $menu = new Menu();
            $menu->name = "SUB MENU 2";
            $menu->description = $faker->text(50);
            $menu->file_id = $file->id;
            $menu->supplier_id = $supplier->id;
            $menu->position = 2;
            $menu->save();
            $menu->refresh();

            $products = $supplier->products()->limit(5)->get();
            $i = 1;
            foreach ($products as $product){
                $menu->products()->attach($product,['position'=> $i]);
                $i++;
            }
            $menu->save();

        }
    }
}
