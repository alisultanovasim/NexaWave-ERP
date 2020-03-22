<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedTinyInteger('is_read')->default(0);
            $table->unsignedTinyInteger('from_office');
            $table->unsignedBigInteger('dialog_id');
            $table->mediumText('body')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('dialog_id')->references('id')->on('dialogs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('messages');
    }
}
