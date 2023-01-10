<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoragePurchaseItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('storage_purchase_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('storage_purchase_id');
            $table->unsignedBigInteger('storage_id');
            $table->unsignedBigInteger('purchase_product_id');
            $table->unsignedBigInteger('product_id');
            $table->string('situation',55);
            $table->string('measure',55);
            $table->double('amount');
            $table->softDeletes();
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
        Schema::dropIfExists('storage_purchase_items');
    }
}
