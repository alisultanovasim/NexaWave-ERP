<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkCalendarDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_calendar_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('work_calendar_id');
            $table->unsignedBigInteger('employee_id')->nullable()->default(null);
            $table->char('event');
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
        Schema::dropIfExists('work_calendar_details');
    }
}
