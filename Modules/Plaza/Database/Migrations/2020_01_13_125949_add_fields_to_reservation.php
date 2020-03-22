<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToReservation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meeting_room_reservations', function (Blueprint $table) {
            /**
             * 1 - wait
             * 2 - active
             * 3 - denied
             */
            $table->unsignedTinyInteger('status')->default(0);
            $table->text('plaza_note')->nullable();
            $table->text('office_note')->nullable();

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
