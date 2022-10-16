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
            $table->unsignedBigInteger('mark_id');
            $table->unsignedBigInteger('model_id');
            $table->unsignedBigInteger('made_in');
            $table->string('color',55);
            $table->decimal('price');
            $table->decimal('custom_fee');
            $table->decimal('transport_fee');
            $table->tinyInteger('measure');
            $table->integer('amount');
            $table->float('discount');
            $table->float('edv_percent');
//            $table->integer('edv_tax');
            $table->float('excise_percent');
//            $table->integer('excise_tax');
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
