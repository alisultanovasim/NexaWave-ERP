<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('unit_id');

            $table->unsignedTinyInteger("less_value");
            $table->unsignedTinyInteger("quickly_old");

            $table->unsignedBigInteger('title_id');
            $table->foreign('title_id')->references('id')->on('product_titles')->onDelete('cascade');

            $table->unsignedBigInteger('kind_id')->nullable();
            $table->foreign('kind_id')->references('id')->on('product_kinds')->onDelete('cascade');

            $table->unsignedBigInteger('state_id');
            $table->foreign('state_id')->references('id')->on('product_states')->onDelete('cascade');

            $table->text('description')->nullable();
            $table->double('amount');
            $table->unsignedBigInteger('storage_id');
            $table->timestamps();
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');
            $table->foreign('storage_id')
                ->references('id')
                ->on('storages')
                ->onDelete('cascade');
            $table->softDeletes();

            $table->unsignedBigInteger('company_id');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
