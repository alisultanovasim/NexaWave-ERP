<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesOfficeMeetingRoomReservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meeting_room_reservations', function (Blueprint $table) {
            $table->index(['meeting_room' , 'company_id']);
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
            $table->dropIndex(['meeting_room' , 'company_id']);
        });
    }
}
