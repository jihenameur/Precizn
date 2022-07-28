<?php


namespace App\Helpers;


use App\Models\Command;

class CommandHelper
{

    public function CalculateTotale(Command $command)
    {
        $total = 0;
        $products = $command->products;
        $tip = $command->tip;
        $delivery = $command->delivery_price;

        foreach ($products as $product){
           // dd($product->supplier_price($command->supplier_id));
            $total += ($products[0]->pivot->quantity * $product->supplier_price($command->supplier_id ));
        }

        // to do discount & promo code
        return $total;

    }
}
