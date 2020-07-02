<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('type');
            $table->char('number');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('labor_code_id');
            $table->date('order_sign_date');
            $table->unsignedBigInteger('created_by');
            $table->timestamp('confirmed_date')->nullable()->default(null);
            $table->unsignedBigInteger('confirmed_by');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('labor_code_id')->references('id')->on('labor_codes');
            $table->foreign('created_by')->references('id')->on('employees');
            $table->foreign('confirmed_by')->references('id')->on('employees');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
