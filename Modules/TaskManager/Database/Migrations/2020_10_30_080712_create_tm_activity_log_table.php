<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTmActivityLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tm_activity_log', function (Blueprint $table) {
            $table->uuid('id')->unique();
            $table->foreignId("user_id")->constrained("users");
            $table->foreignId("task_id")->nullable()->constrained("tm_tasks");
            $table->string("action", 500);
            $table->string("lang")->default("az");
            $table->ipAddress("user_ip");
            $table->string("user_agent", 400)->nullable();
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
        Schema::dropIfExists('tm_activity_log');
    }
}
