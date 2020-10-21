<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSellActDemandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sell_act_demands', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('sell_act_id');
            $table->foreign('sell_act_id')
                ->references('id')
                ->on('sell_acts')
                ->onDelete('cascade');

            $table->unsignedBigInteger('demand_id');
            $table->foreign('demand_id')
                ->references('id')
                ->on('demands')
                ->onDelete('cascade');

            $table->float('amount')->nullable();
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
        Schema::dropIfExists('sell_act_demands');
    }
}
