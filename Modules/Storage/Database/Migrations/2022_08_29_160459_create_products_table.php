<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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

            $table->tinyInteger("less_value");
            $table->tinyInteger("quickly_old");

            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('title_id');
            $table->unsignedBigInteger('kind_id')->nullable();
            $table->unsignedBigInteger('state_id');

            $table->text('description')->nullable();
            $table->double('amount');
            $table->double('initial_amount');
            $table->unsignedBigInteger('storage_id');
            $table->unsignedBigInteger('model_id');
            $table->unsignedBigInteger('color_id');
            $table->double('size');
            $table->date('exploitation_date');
            $table->unsignedBigInteger('supplier_id');
            $table->string('inv_no');
            $table->date('make_date');
            $table->unsignedBigInteger('made_in_country');
            $table->unsignedBigInteger('buy_from_country');
            $table->string('product_model');
            $table->string('product_mark');
            $table->string('product_no');
            $table->tinyInteger('main_funds');
            $table->tinyInteger('status')->default(\Modules\Storage\Entities\Demand::STATUS_ACCEPTED);
            $table->unsignedBigInteger('sell_act_id');
            $table->unsignedBigInteger('room');
            $table->unsignedBigInteger('floor');

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
        Schema::dropIfExists('products');
    }
}
