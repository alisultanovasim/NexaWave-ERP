<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBankInformationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_information', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string("name");
            $table->string("short_name");
            $table->date("registration_date");
            $table->string("licence");
            $table->string("auditor");
            $table->string("address");
            $table->string("phone");
            $table->string("correspondent");
            $table->string("swift");
            $table->string("code");
            $table->string("teleks")->nullable();
            $table->integer("fax")->nullable();
            $table->string("email");
            $table->string("site")->nullable();
            $table->string("voen", 30);
            $table->bigInteger("index");
            $table->unsignedBigInteger('company_id');
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
        Schema::dropIfExists('bank_information');
    }
}
