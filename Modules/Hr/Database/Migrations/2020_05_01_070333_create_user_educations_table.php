<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserEducationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_educations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('education_specialty_id')->nullable();
            $table->foreign('education_specialty_id')
                ->references('id')
                ->on('education_specialties')
                ->onDelete('set null');

            $table->unsignedBigInteger('education_place_id')->nullable();
            $table->foreign('education_place_id')
                ->references('id')
                ->on('education_places')
                ->onDelete('set null');

            $table->unsignedBigInteger('faculty_id')->nullable();
            $table->foreign('faculty_id')
                ->references('id')
                ->on('faculties')
                ->onDelete('set null');

            $table->unsignedBigInteger('education_level_id')->nullable();
            $table->foreign('education_level_id')
                ->references('id')
                ->on('education_levels')
                ->onDelete('set null');

            $table->unsignedBigInteger('education_state_id')->nullable();
            $table->foreign('education_state_id')
                ->references('id')
                ->on('education_states')
                ->onDelete('set null');

            $table->unsignedBigInteger('language_id')->nullable();
            $table->foreign('language_id')
                ->references('id')
                ->on('languages')
                ->onDelete('set null');

//            $table->unsignedBigInteger('language_level_id')->nullable();
//            $table->foreign('language_level_id')
//                ->references('id')
//                ->on('language_levels')
//                ->onDelete('set null');

            $table->date('entrance_date')->nullable();
            $table->date('graduation_date')->nullable();

            $table->text('description')->nullable();

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

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
        Schema::dropIfExists('user_educations');
    }
}
