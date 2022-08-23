<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_id');
            $table->unsignedBigInteger('title_id');
            $table->unsignedBigInteger('kind_id');
            $table->unsignedBigInteger('model_id');
            $table->string('mark',50);
            $table->unsignedBigInteger('color_id');
            $table->string('made_in',50);
            $table->decimal('custom_tax');
            $table->decimal('price');
            $table->integer('amount');
            $table->integer('discount');
            $table->integer('edv_percent');
            $table->integer('edv_tax');
            $table->integer('excise_percent');
            $table->integer('excise_tax');
            $table->tinyInteger('status')->default(1);
            $table->integer('total_price');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_products');
    }
}
