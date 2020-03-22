<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHumansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('humans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('fin');
            $table->string('name');
            $table->string('surname');
            $table->string('father_name')->nullable();
            $table->date('birthday')->nullable();
            $table->enum('gender' ,['m','f']);

            $table->unsignedBigInteger('nationality_id')->nullable();
            $table->unsignedBigInteger('citizen_id')->nullable();
            $table->unsignedBigInteger('birthday_country_id')->nullable()->nullable();
            $table->unsignedBigInteger('birthday_city_id')->nullable()->nullable();
            $table->unsignedBigInteger('birthday_region_id')->nullable()->nullable();
            $table->unsignedBigInteger('blood_id')->nullable();
            $table->unsignedBigInteger('eye_color_id')->nullable();



            $table->foreign('nationality_id')->references('id')->on('nationalities');
            $table->foreign('citizen_id')->references('id')->on('countries');
            $table->foreign('birthday_country_id')->references('id')->on('countries');
            $table->foreign('birthday_city_id')->references('id')->on('cities');
            $table->foreign('birthday_region_id')->references('id')->on('regions');
            $table->foreign('blood_id')->references('id')->on('blood_groups');
            $table->foreign('eye_color_id')->references('id')->on('colors');


            $table->string('passport_seria')->nullable();
            $table->string('passport_number')->nullable();
            $table->string('passport_from_organ')->nullable();
            $table->string('passport_get_at')->nullable();
            $table->string('passport_expire_at')->nullable();


            $table->string('email')->nullable();

            $table->unique('fin');

            $table->softDeletes();
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
        Schema::dropIfExists('humans');
    }
}
