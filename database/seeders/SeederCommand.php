<?php

namespace Database\Seeders;

use App\Models\Command;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class SeederCommand extends Seeder
{
     /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $commands = $this->getCommandList();
        $this->createCommand($commands);
    }

    /**
     * Get available application commands list
     *
     * @return string[][]
     */
    private function getCommandList(): array
    {
        $commands = [

            [
                'client_id'=>1,
                'delivery_id'=>1,
                'date'=>'2022-06-17 11:50',
                'mode_pay'=>1,
                'delivery_price'=>4,
                'total_price'=>4,
                'tip'=>1,
                'status'=>1,
                'codepromo'=>123456,
                'supplier_id'=>1,
                'lat'=>35.886399,
                'long'=>10.5915072,
                'addresse_id'=>1,
                'panier_id'=>1
            ],
            [
                'client_id'=>1,
                'delivery_id'=>1,
                'date'=>'2022-06-17 11:50',
                'mode_pay'=>1,
                'delivery_price'=>4,
                'total_price'=>4,
                'tip'=>1,
                'status'=>1,
                'codepromo'=>123456,
                'supplier_id'=>1,
                'lat'=>35.886399,
                'long'=>10.5915072,
                'addresse_id'=>1,
                'panier_id'=>1
            ],
        ];
        return $commands;
    }

    /**
     * Create commands
     *
     * @param array $commands
     */
    private function createCommand(array $commands): void
    {
        foreach ($commands as $command) {
            $comm = new Command(Arr::except($command, []));
            $comm->save();
            $prod=Product::find(1);
            $comm->products()->attach($prod, [
                'quantity' => 1

              ]);

        }
    }
}
