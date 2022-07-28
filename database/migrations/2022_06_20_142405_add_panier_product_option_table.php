<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPanierProductOptionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('option_panier__product', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('panier__product_id')->unsigned();
            $table->bigInteger('option_id')->unsigned();

            $table->foreign('panier__product_id')->references('id')->on('panier_product')
            ->onDelete('cascade');
            $table->foreign('option_id')->references('id')->on('options')
            ->onDelete('cascade');
            $table->timestamps();
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
