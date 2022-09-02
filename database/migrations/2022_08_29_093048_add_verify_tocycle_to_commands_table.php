<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVerifyTocycleToCommandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('commands', function (Blueprint $table) {
            $table->enum('cycle',[
                'PENDING',
                'VERIFY',
                'AUTHORIZED',
                'PRE_ASSIGN',
                'PRE_ASSIGN_ADMIN',
                'ASSIGNED',
                'INPROGRESS',
                'SUCCESS',
                'REJECTED',
            ])->default('PENDING');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('commands', function (Blueprint $table) {
            //
        });
    }
}
