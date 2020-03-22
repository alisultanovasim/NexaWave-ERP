<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMeetingRoomReservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meeting_room_reservations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string("event_name");
            $table->unsignedBigInteger("office_id");
            $table->unsignedBigInteger("company_id");
            $table->timestamp("start_at");
            $table->timestamp("finish_at")->nullable()->default(null);
            $table->timestamp("create_at")->useCurrent();
            $table->text('description')->nullable();
            $table->foreign('office_id')->references('id')->on('offices');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('meeting_room_reservations');
    }
}
