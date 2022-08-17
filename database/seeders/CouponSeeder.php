<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $coupons = $this->getCouponsList();
        $this->createCoupon($coupons);
    }
    private function getCouponsList(): array
    {
        $coupons = [
            [
                'client_id' => 1,
                'type' => 'amount',
                'value' => 5,
                'title' => 'black friday',
                'start_date' => '2022-06-17',
                'end_date' => '2022-06-30',
                'description' => 'coupon Command',
                'quantity' => '10',
                'client_quantity' => '2',
                'status' => '1',
                'montant_min' => '40',
                'currency' => 'DNT',
                'frais_port' => 0,
                'code_coupon' => 'blkfrd123',
                'taxe' => 'TTC'

            ],
            [
                //'client_id' => 2,
                'type' => 'amount',
                'value' => 10,
                'title' => 'New Year',
                'start_date' => '2022-12-12',
                'end_date' => '2022-12-31',
                'description' => 'coupon Command',
                'quantity' => '10',
                'client_quantity' => '2',
                'status' => '1',
                'montant_min' => '50',
                'currency' => 'DNT',
                'frais_port' => 0,
                'code_coupon' => 'new123',
                'taxe' => 'TTC'
            ],
        ];
        return $coupons;
    }
    private function createCoupon(array $coupons): void
    {
        foreach ($coupons as $coupon) {
            Coupon::create($coupon);

        }
    }
}
