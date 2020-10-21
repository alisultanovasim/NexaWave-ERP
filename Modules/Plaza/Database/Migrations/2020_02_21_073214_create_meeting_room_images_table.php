<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMeetingRoomImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meeting_room_images', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('url');
            $table->unsignedInteger('meeting_room_id');
            $table->foreign('meeting_room_id')->references('id')->on('meeting_rooms')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('meeting_room_images');
    }
}
