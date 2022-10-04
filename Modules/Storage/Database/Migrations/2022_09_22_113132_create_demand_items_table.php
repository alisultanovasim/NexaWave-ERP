<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDemandItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('demand_items', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('demand_id');
                $table->string('title',55);
                $table->unsignedBigInteger('title_id');
                $table->string('kind',55);
                $table->unsignedBigInteger('kind_id');
                $table->string('model',55);
                $table->unsignedBigInteger('model_id');
                $table->string('mark',55);
                $table->double('amount');
                $table->softDeletes();
                $table->timestamps();
                $table->foreign('demand_id')
                    ->references('id')
                    ->on('demands')
                    ->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('demand_items');
    }
}
