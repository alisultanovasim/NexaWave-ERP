<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkShiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_shifts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('contract_id')->nullable();
            $table->smallInteger('shift_number');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->bigInteger('position');
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('company_id');

            $table->foreign('contract_id')
                ->references('id')
                ->on('contracts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('work_shifts');
    }
}
