<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExpiredAtAndEmployerIdToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger("employer_id")->nullable()->after("id");
            $table->date("password_expired_at")->nullable();
            $table->dateTime("password_last_change_date")->nullable();
            $table->string("phone", 15)->nullable();
            $table->dateTime("last_login_date")->nullable();
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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn("employer_id");
            $table->dropColumn("password_expired_at");
            $table->dropColumn("password_last_change_date");
            $table->dropColumn("phone");
            $table->dropColumn("last_login_date");
        });
    }
}
