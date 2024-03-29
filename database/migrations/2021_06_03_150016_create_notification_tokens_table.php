<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_tokens', function (Blueprint $table) {
            $table->uuid("id")->primary()->unique();
            $table->foreignId("user_id")->constrained("users");
            $table->string("fcm_token", 300);
            $table->smallInteger("application_id")->nullable();
            $table->ipAddress("user_ip");
            $table->text("user_agent")->nullable();
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
        Schema::dropIfExists('notification_tokens');
    }
}
