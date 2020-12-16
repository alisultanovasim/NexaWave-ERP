<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeWorkCalendarDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('work_calendar_details', function (Blueprint $table){
            $table->unsignedBigInteger('event_id')->after('event')->nullable()->default(null);
            $table->string('event')->nullable()->default(null)->change();

            $table->foreign('event_id')->references('id')->on('company_events');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('work_calendar_details', function (Blueprint $table){
            $table->dropColumn('event_id');
        });
    }
}
