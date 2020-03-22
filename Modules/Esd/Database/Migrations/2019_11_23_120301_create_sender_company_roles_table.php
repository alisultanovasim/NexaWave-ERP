<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSenderCompanyRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sender_company_roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('sender_company_id');
            $table->string('name');
            $table->foreign('sender_company_id')->references('id')->on('sender_companies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sender_company_roles');
    }
}
