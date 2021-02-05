<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStructurePositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('structure_positions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('structure_id');
            $table->enum('structure_type', [
                'company',
                'department',
                'section',
                'sector'
            ]);
            $table->unsignedBigInteger('position_id');
            $table->integer('quantity');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('position_id')->references('id')->on('positions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('structure_positions');
    }
}
