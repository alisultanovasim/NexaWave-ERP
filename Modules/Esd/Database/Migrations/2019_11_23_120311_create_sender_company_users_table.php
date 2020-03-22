<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSenderCompanyUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sender_company_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('sender_company_id');
            $table->unsignedBigInteger('sender_company_role_id');
            $table->string('name');
            $table->foreign('sender_company_id')->references('id')->on('sender_companies');
            $table->foreign('sender_company_role_id')->references('id')->on('sender_company_roles');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sender_company_users');
    }
}
