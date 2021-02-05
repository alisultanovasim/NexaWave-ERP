<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->bigIncrements('id');

            /** Table =  document_users  Column = id */
            $table->unsignedBigInteger("assignment_item_id");


            $table->json("versions")->nullable();

            $table->text("resource")->nullable();

            $table->string('size')->nullable();


            /**
             * 1 - file
             * 2 - editor
             */
            $table->unsignedTinyInteger("type");

            $table->timestamps();

            $table->foreign("assignment_item_id")->references("id")->on("assignment_items")->onDelete("cascade");

        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notes');
    }
}
