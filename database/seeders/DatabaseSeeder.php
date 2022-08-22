<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $this->call(RoleSeeder::class);
        $this->call(SeederCategorie::class);
        $this->call(SeederSousCategorie::class);
        $this->call(StatusSeeder::class);
        $this->call(SeederClient::class);
        $this->call(SeederAdmin::class);
        $this->call(SuperAdminSeeder::class);
        $this->call(SeederSupplier::class);
        $this->call(SeederDelivery::class);
        $this->call(SeederAdress::class);
        $this->call(SeederSetting::class);
        $this->call(SeederOption::class);
        $this->call(SeederTypeProduct::class);
        $this->call(SeederTags::class);
        $this->call(SeederProduct::class);
        //$this->call(SeederPanier::class);
        $this->call(CouponSeeder::class);
        $this->call(SeederCommand::class);
        $this->call(SeederWriting::class);
        $this->call(SeederAnnonce::class);
        $this->call(AdsAreaSeeder::class);
        $this->call(AdsSeeder::class);


    }
}
