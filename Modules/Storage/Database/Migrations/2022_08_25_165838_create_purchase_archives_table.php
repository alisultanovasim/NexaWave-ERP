<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseArchivesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_archives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('company_name',50);
            $table->unsignedBigInteger('supplier_id');
            $table->string('product_name',77);
            $table->date('start_date');
            $table->date('end_date');
            $table->string('product_type',77);
            $table->integer('demand_amount');
            $table->integer('purchase_amount');
            $table->integer('take_over_amount');
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
        Schema::dropIfExists('purchase_archives');
    }
}
