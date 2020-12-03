<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdditivesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('additives', function (Blueprint $table) {

            $table->bigIncrements('id');

            $table->unsignedBigInteger('office_id');

            $table->unsignedInteger('days');

            $table->unsignedBigInteger('month');

            $table->timestamp('payed_at')->nullable();

            $table->foreign('office_id')->references('id')->on('offices');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('additives');
    }
}
