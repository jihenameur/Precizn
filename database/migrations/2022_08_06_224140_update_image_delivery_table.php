<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateImageDeliveryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('deliverys', function (Blueprint $table) {

            try {

                DB::statement('ALTER TABLE deliverys DROP  image ;');
            } catch (Exception $e) {
                // do nothing
            }
            $table->foreignId('file_id')->nullable()->constrained('files')->onDelete('cascade');
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
