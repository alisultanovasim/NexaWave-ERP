<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeFieldsNullableAtOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {

            $table->string('number')->nullable()->default(null)->change();
            $table->unsignedBigInteger('labor_code_id')->nullable()->default(null)->change();
            $table->date('order_sign_date')->nullable()->default(null)->change();
            $table->unsignedBigInteger('created_by')->nullable()->default(null)->change();
            $table->unsignedBigInteger('confirmed_by')->nullable()->default(null)->change();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('number')->change();
            $table->unsignedBigInteger('company_id')->change();
            $table->unsignedBigInteger('labor_code_id')->change();
            $table->date('order_sign_date')->change();
            $table->unsignedBigInteger('created_by')->change();
            $table->unsignedBigInteger('confirmed_by')->change();
        });
    }
}
