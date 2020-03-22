<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
public function up()
{
    Schema::create('users_offers', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->unsignedBigInteger('user_id');
        $table->unsignedBigInteger('office_id');
        $table->unsignedBigInteger('company_id');
        $table->foreign('office_id')->references('id')->on('offices');

        $table->unique(['user_id' , 'office_id' , 'company_id']);

    });
}

/**
 * Reverse the migrations.
 *
 * @return void
 */
public function down()
{
    Schema::dropIfExists('users_offers');
}
}
