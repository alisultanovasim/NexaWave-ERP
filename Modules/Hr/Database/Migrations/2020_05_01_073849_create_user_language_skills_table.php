<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserLanguageSkillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_language_skills', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('language_id')->nullable();
            $table->unsignedBigInteger('listening')->nullable();
            $table->unsignedBigInteger('reading')->nullable();
            $table->unsignedBigInteger('writing')->nullable();
            $table->unsignedBigInteger('comprehension')->nullable();

            $table->foreign('language_id')
                ->references('id')
                ->on('languages')
                ->onDelete('set null');

            $table->foreign('listening')
                ->references('id')
                ->on('language_levels')
                ->onDelete('set null');
            $table->foreign('reading')
                ->references('id')
                ->on('language_levels')
                ->onDelete('set null');
            $table->foreign('comprehension')
                ->references('id')
                ->on('language_levels')
                ->onDelete('set null');
            $table->foreign('writing')
                ->references('id')
                ->on('language_levels')
                ->onDelete('set null');


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
        Schema::dropIfExists('user_language_skills');
    }
}
