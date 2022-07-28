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
        $this->call(SeederSetting::class);
        $this->call(SeederCategorie::class);
        $this->call(SeederSousCategorie::class);
        $this->call(SeederSupplier::class);
        $this->call(SeederProduct::class);
        $this->call(SeederPanier::class);
        $this->call(SeederClient::class);
        $this->call(SeederDelivery::class);
        $this->call(SeederAdress::class);
        $this->call(SeederCommand::class);
        $this->call(SeederWriting::class);
        $this->call(SeederAnnonce::class);
        $this->call(SeederOption::class);
        $this->call(SeederOption::class);
        $this->call(StatusSeeder::class);
        $this->call(SeederTypeProduct::class);
        $this->call(SeederTags::class);

        

















    }
}
