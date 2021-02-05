<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCountriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string("name");
            $table->string("short_name",20);
            $table->string("code", 10)->nullable();
            $table->string("iso", 2)->nullable();
            $table->string("iso3", 3)->nullable();
            $table->string("phone_code",5)->nullable();
            $table->string("currency", 4)->nullable();
            $table->bigInteger("index")->default(0);
            $table->boolean("is_active")->default(true);
            $table->unsignedBigInteger('company_id')->nullable();
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
        Schema::dropIfExists('countries');
    }
}
