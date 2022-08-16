<?php

namespace Database\Seeders;

use App\Models\Panier;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Tag;
use App\Models\TypeProduct;
use Facade\Ignition\Support\FakeComposer;
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
        $faker = \Faker\Factory::create();
        $types = ['Piece', 'Kg', 'L', 'M'];
        $supps = Supplier::all();
        $tags = Tag::all();
        $typeproduct = TypeProduct::all();

        foreach ($supps as $supp){
            for ($i = 0; $i < 10; $i++) {
                $prod = new Product();
                $prod->name = $faker->company();
                $prod->description = $faker->text();
                $prod->private = 1;
                $prod->default_price = rand(1, 20);
                $min = rand(5, 20);
                $prod->min_period_time = $min;
                $prod->max_period_time = rand($min, 60);
                $prod->available = rand(0, 1);
                $prod->unit_type = $types[rand(0,count($types) -1)];
                $prod->unit_limit = 1;
                $prod->weight = 2;
                $prod->dimension = 2;
                $prod->save();
                $prod->suppliers()->attach($supp, ['price' => rand($prod->default_price, 50)]);
                $prod->tag()->attach($tags[rand(0,count($tags) -1)]);
                $prod->typeproduct()->attach($typeproduct[rand(0,count($typeproduct) -1)]);
            }

            for ($i = 0; $i < 10; $i++) {
                $prod = new Product();
                $prod->name = $faker->company();
                $prod->description = $faker->text();
                $prod->private = 0;
                $prod->default_price = rand(1, 20);
                $min = rand(5, 20);
                $prod->min_period_time = $min;
                $prod->max_period_time = rand($min, 60);
                $prod->available = rand(0, 1);
                $prod->unit_type = $types[rand(0,count($types) -1)];
                $prod->unit_limit = 1;
                $prod->weight = 2;
                $prod->dimension = 2;
                $prod->save();
                $prod->tag()->attach($tags[rand(0,count($tags) -1)]);
                $prod->typeproduct()->attach($typeproduct[rand(0,count($typeproduct) -1)]);
            }
        }


    }


}
