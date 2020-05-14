<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductKindsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_kinds', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
//
//            $table->unsignedInteger('unit_id')->nullable();
//            $table->foreign('unit_id')
//                ->references('id')
//                ->on('units')
//                ->onDelete('cascade');

            $table->unsignedBigInteger('title_id');
            $table->foreign('title_id')
                ->references('id')
                ->on('product_titles')
                ->onDelete('cascade');

            $table->unsignedBigInteger('company_id');
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
        Schema::dropIfExists('product_kinds');
    }
}
