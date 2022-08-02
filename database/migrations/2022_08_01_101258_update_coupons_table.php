<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coupons', function (Blueprint $table) {
          //  $table->dropForeign(['command_id']);
          try{

            Db::statement('ALTER TABLE coupons DROP FOREIGN KEY coupons_command_id_foreign;');

        }catch(Exception $e){
                // do nothing
          }

            $table->dropColumn('command_id');
            $table->dropColumn('percentage');
            $table->enum('type',['amount','percentage'])->default('amount');
            $table->float('value');
            $table->string('title');
            $table->date('start_date');
            $table->date('end_date');
            $table->text('description');
            $table->integer('quantity');
            $table->integer('client_quantity');
            $table->boolean('status');

            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('cascade');

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
