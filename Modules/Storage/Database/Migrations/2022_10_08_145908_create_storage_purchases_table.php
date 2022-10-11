<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoragePurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('storage_purchases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_id');
            $table->string('storage_name',55);
            $table->string('company_name',55);
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('title_id');
            $table->unsignedBigInteger('kind_id');
            $table->unsignedBigInteger('mark_id');
            $table->unsignedBigInteger('model_id');
            $table->unsignedBigInteger('unit_id');
            $table->string('color',55);
            $table->decimal('price');
            $table->double('amount');
            $table->string('situation',55);
            $table->tinyInteger('is_completed')->default(0);
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
        Schema::dropIfExists('storage_purchases');
    }
}
