<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tm_tasks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId("list_id")->constrained("tm_lists");
            $table->foreignId("parent_id")->constrained("tm_tasks");
            $table->foreignId("assigned_id")->constrained("users");
            $table->foreignId("created_id")->constrained("users");
            $table->string("name", 300);
            $table->dateTime("deadline")->nullable();
            $table->text("description")->nullable();
            $table->float("budget")->nullable();
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
        Schema::dropIfExists('tm_tasks');
    }
}
