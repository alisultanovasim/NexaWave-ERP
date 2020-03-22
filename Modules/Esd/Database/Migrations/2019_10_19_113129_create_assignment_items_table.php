<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssignmentItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assignment_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger("assignment_id");
            $table->unsignedBigInteger("user_id");
            $table->unsignedTinyInteger("status")->default(config("modules.document.assignment.not_seen"));
            $table->unsignedTinyInteger("is_base")->default(0);
            $table->timestamp("created_at")->useCurrent();
            $table->foreign("assignment_id")->references("id")->on("assignments")->onDelete("cascade");
            $table->unique(["assignment_id" , "user_id"]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('assignment_items');
    }
}
