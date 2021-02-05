<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfficesLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offices_locations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger("office_id");
            $table->decimal("size");
            $table->unsignedBigInteger("floor_id");
            $table->unsignedInteger("number")->nullable();
            $table->string('schema')->nullable();
            $table->foreign("office_id")->references("id")->on("offices");
            $table->foreign("floor_id")->references("id")->on("floors");
            $table->timestamps();
            $table->unique(['office_id' , 'floor_id']);
//            $table->unique(['number' , 'floor_id'] );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('offices_locations');
    }
}
