<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSofteDeletesAdminTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

            Schema::table('admins', function (Blueprint $table) {
                $table->softDeletes();
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
