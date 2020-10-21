<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkplacesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workplaces', function (Blueprint $table) {
           $table->bigIncrements('id');
           $table->char('name', 250);
           $table->char('short_name', 50);
           $table->unsignedBigInteger('country_id');
           $table->unsignedBigInteger('city_id');
           $table->unsignedBigInteger('region_id')->nullable();
           $table->string('address')->nullable();
           $table->string('zip_code')->nullable();
           $table->string('phone')->nullable();
           $table->string('fax')->nullable();
           $table->string('email')->nullable();
           $table->string('web_site')->nullable();
           $table->bigInteger('position');
           $table->timestamps();
           $table->softDeletes();

           $table->foreign('country_id')
               ->references('id')
               ->on('countries');

           $table->foreign('city_id')
               ->references('id')
               ->on('cities');

           $table->foreign('region_id')
               ->references('id')
               ->on('regions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
