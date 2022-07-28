<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWritingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('writings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('comment');
            $table->tinyInteger('note');
            $table->bigInteger('client_id')->unsigned();
            $table->bigInteger('supplier_id')->unsigned();
           
            $table->foreign('client_id')
                ->references('id')
                ->on('clients')
                ->onDelete('cascade');
                
            $table->foreign('supplier_id')
            ->references('id')
            ->on('suppliers')
            ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('writings');
    }
}
