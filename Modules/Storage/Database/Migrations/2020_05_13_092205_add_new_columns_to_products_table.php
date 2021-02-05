<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewColumnsToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('inv_no')->nullable();
            $table->date('make_date')->nullable();
            $table->unsignedBigInteger('made_in_country')->nullable();
            $table->unsignedBigInteger('buy_from_country')->nullable();
            $table->foreign('made_in_country')
                ->references('id')
                ->on('countries')
                ->onDelete('set null');

            $table->foreign('buy_from_country')
                ->references('id')
                ->on('countries')
                ->onDelete('set null');


            $table->string('product_model')->nullable();
            $table->string('product_mark')->nullable();
            $table->string('product_no')->nullable();

            $table->boolean('main_funds')->default(0);

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
