<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProposeDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('propose_details', function (Blueprint $table) {
            $table->id();
            $table->double('amount');
            $table->decimal('price');
            $table->unsignedBigInteger('demand_item_id');
            $table->unsignedBigInteger('propose_id');
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
        Schema::dropIfExists('propose_details');
    }
}
