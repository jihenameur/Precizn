<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sub_tasks', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('subtask_name');
            $table->string('description');
            $table->string('status');
            $table->string('priority');
            $table->string('claim');
            $table->foreignId('user_id')->nullable()->constrained('users');;
            $table->foreignId('task_id')->constrained('tasks');
            $table->string('start_date');
            $table->string('end_date');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sub_tasks');
    }
};
