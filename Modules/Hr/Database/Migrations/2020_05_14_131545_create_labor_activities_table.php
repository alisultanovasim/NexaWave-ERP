<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLaborActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('labor_activities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('country_id');
            $table->unsignedBigInteger('city_id');
            $table->unsignedBigInteger('region_id');
            $table->char('company_name');
            $table->char('structure')->nullable()->default(null);
            $table->char('sector')->nullable()->default(null);
            $table->char('position');
            $table->timestamp('start_date')->nullable()->default(null);
            $table->timestamp('end_date')->nullable()->default(null);
            $table->char('labor_book_number');
            $table->timestamp('labor_book_filling_date')->nullable()->default(null);
            $table->char('labor_bool_stuffing_number');
            $table->unsignedBigInteger('company_id')->nullable()->default(null)->comment('describes is this company field');
            $table->boolean('is_civil_service')->default(0);
            $table->char('termination_reason')->nullable()->default(null);
            $table->char('note')->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('country_id')->references('id')->on('countries');
            $table->foreign('city_id')->references('id')->on('cities');
            $table->foreign('region_id')->references('id')->on('regions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('labor_activities');
    }
}
