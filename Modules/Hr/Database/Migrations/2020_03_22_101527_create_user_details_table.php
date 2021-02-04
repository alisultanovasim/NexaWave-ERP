<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class CreateUserDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('fin');

            $table->date('birthday')->nullable();
            $table->string('father_name')->nullable();
            $table->enum('gender' ,['m','f']);

            $table->unsignedBigInteger('nationality_id')->nullable();
            $table->unsignedBigInteger('citizen_id')->nullable();
            $table->unsignedBigInteger('birthday_country_id')->nullable()->nullable();
            $table->unsignedBigInteger('birthday_city_id')->nullable()->nullable();
            $table->unsignedBigInteger('birthday_region_id')->nullable()->nullable();
            $table->unsignedBigInteger('blood_id')->nullable();
            $table->unsignedBigInteger('eye_color_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();


            $table->foreign('nationality_id')->references('id')->on('nationalities');
            $table->foreign('citizen_id')->references('id')->on('countries');
            $table->foreign('birthday_country_id')->references('id')->on('countries');
            $table->foreign('birthday_city_id')->references('id')->on('cities');
            $table->foreign('birthday_region_id')->references('id')->on('regions');
            $table->foreign('blood_id')->references('id')->on('blood_groups');
            $table->foreign('eye_color_id')->references('id')->on('colors');
            $table->foreign('user_id')->references('id')->on('users');


            $table->string('passport_seria')->nullable();
            $table->string('passport_number')->nullable();
            $table->string('passport_from_organ')->nullable();
            $table->string('passport_get_date')->nullable();
            $table->string('passport_expire_date')->nullable();


            $table->boolean('military_status')->nullable();
            $table->date('military_start_date')->nullable();
            $table->date('military_end_date')->nullable();
            $table->unsignedBigInteger('military_state_id')->nullable();
            $table->string('military_passport_number')->nullable();
            $table->string('military_place')->nullable();
            $table->foreign('military_state_id')->references('id')->on('military_states');

            $table->string('driving_license_number')->nullable();
//            $table->set('driving_license_categories',UserDetail::DRIVING_CATEGORIES)->nullable();
            $table->string('driving_license_categories')->nullable();
            $table->string('driving_license_organ')->nullable();
            $table->date('driving_license_get_date')->nullable();
            $table->date('driving_license_expire_date')->nullable();



            $table->string('foreign_passport_number')->nullable();
            $table->string('foreign_passport_organ')->nullable();
            $table->date('foreign_passport_get_date')->nullable();
            $table->date('foreign_passport_expire_date')->nullable();

//            Marital status
            $table->string('family_status_document_number')->nullable();
            $table->string('family_status_state')->nullable();
            $table->date('family_status_register_date')->nullable();

            $table->string('avatar')->nullable();


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
        Schema::dropIfExists('user_details');
    }
}
