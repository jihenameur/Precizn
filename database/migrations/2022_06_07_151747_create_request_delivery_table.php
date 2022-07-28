<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestDeliveryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('requestDelivery', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('delivery_id')->unsigned();
            $table->bigInteger('command_id')->unsigned();
            $table->dateTime('date');
            $table->boolean('accept')->nullable();

            $table->foreign('delivery_id')->references('id')->on('deliverys')
            ->onDelete('cascade');
            $table->foreign('command_id')->references('id')->on('commands')
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
        Schema::dropIfExists('request_delivery');
    }
}
