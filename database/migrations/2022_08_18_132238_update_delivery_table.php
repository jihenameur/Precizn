<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateDeliveryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('deliverys', function (Blueprint $table) {
            try{
                Db::statement('ALTER TABLE deliverys DROP vehicle;');
            }catch(Exception $e){
                // do nothing
            }
            $table->enum('vehicle', [
                'Scooter',
                'Voiture',
                'Velo'
            ])->default('Scooter');
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
