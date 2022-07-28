<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('commands', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('client_id')->unsigned();
            $table->bigInteger('supplier_id')->unsigned();
            $table->bigInteger('delivery_id')->unsigned()->nullable();
            $table->bigInteger('panier_id')->unsigned();
            $table->dateTime('date');
            $table->bigInteger('addresse_id')->unsigned()->nullable();
            $table->decimal('tip')->nullable();
            $table->decimal('delivery_price')->nullable();
            $table->integer('mode_pay');
            $table->decimal('total_price')->nullable();
            $table->double('lat')->unsigned();
            $table->double('long')->unsigned();
            $table->foreign('client_id')->references('id')->on('clients')
                ->onDelete('cascade');
            $table->foreign('delivery_id')->references('id')->on('deliverys')
                ->onDelete('cascade');
            $table->foreign('panier_id')->references('id')->on('paniers')
                ->onDelete('cascade');
            $table->foreign('supplier_id')->references('id')->on('suppliers')
                ->onDelete('cascade');
            $table->foreign('addresse_id')->references('id')->on('addresses')
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
        Schema::dropIfExists('commands');
    }
}
