<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
                $table->unsignedBigInteger('model_id')->nullable();
                $table->unsignedBigInteger('color_id')->nullable();

                $table->foreign('model_id')
                    ->references('id')
                    ->on('product_models');

                $table->foreign('color_id')
                    ->references('id')
                    ->on('product_colors');

                $table->float('size')->nullable();

                $table->date('exploitation_date')->nullable();


            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {

        });
    }
}
