<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('inventory_type_id');
            $table->timestamp('presenting_time');
            $table->timestamp('delivery_time')->nullable()->default(null);
            $table->char('name', 100);
            $table->char('number', 50);
            $table->char('note')->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('inventory_type_id')->references('id')->on('inventory_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventors');
    }
}
