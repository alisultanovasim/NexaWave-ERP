<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDemandAssignmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('demand_assignments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->unsignedBigInteger('demand_id')->nullable();
            $table->foreign('demand_id')
                ->references('id')
                ->on('demands')
                ->onDelete('cascade');
            $table->timestamp('expiry_time')->nullable();
            $table->unsignedTinyInteger('status')
                ->default(\Modules\Storage\Entities\DemandAssignment::STATUS_WAIT);

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
        Schema::dropIfExists('demand_assignments');
    }
}
