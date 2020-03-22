<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToMeetings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meeting_room_reservations', function (Blueprint $table) {
            $table->unsignedInteger('meeting_room');
            $table->foreign('meeting_room')->references('id')->on('meeting_rooms');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('meeting_room_reservations', function (Blueprint $table) {
            //
        });
    }
}
