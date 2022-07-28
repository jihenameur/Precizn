<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupplierMenuProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supplier_menu_product', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('supplier_id')->unsigned();
            $table->bigInteger('menu_id')->unsigned();
            $table->bigInteger('product_id')->unsigned();

            $table->foreign('supplier_id')
                ->references('id')
                ->on('suppliers')
                ->onDelete('cascade');

            $table->foreign('menu_id')
                ->references('id')
                ->on('menu')
                ->onDelete('cascade');

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
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
        Schema::dropIfExists('supplier_menu_product');
    }
}
