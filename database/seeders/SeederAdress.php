<?php

namespace Database\Seeders;

use App\Models\Address;
use Illuminate\Database\Seeder;

class SeederAdress extends Seeder
{ /**
    * Run the database seeds.
    *
    * @return void
    */
   public function run()
   {
       $addresses = $this->getAddressList();
       $this->createAddress($addresses);
   }

   /**
    * Get available application addresses list
    *
    * @return string[][]
    */
   private function getAddressList(): array
   {
       $addresses = [
       [ "street"=>"hammam sousse",
        "postcode"=>"4000",
        "city"=>"Sousse",
       "region"=>"tunisie",
       "lat"=>35.886399,
       "long"=>10.5915072,
       'status'=>1,
       "user_id"=> 1
       ]
       ];
       return $addresses;
   }

   /**
    * Create addresse
    *
    * @param array $addresses
    */
   private function createAddress(array $addresses): void
   {
       foreach ($addresses as $addresse) {
           Address::create($addresse);
       }
   }
}
