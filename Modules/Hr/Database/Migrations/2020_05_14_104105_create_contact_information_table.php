<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContactInformationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contact_information', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('country_id');
            $table->unsignedBigInteger('city_id');
            $table->unsignedBigInteger('region_id');
            $table->unsignedBigInteger('address_type_id');
            $table->char('address');
            $table->date('expire_date')->nullable()->default(null);
            $table->char('post_index')->nullable()->default(null);
            $table->char('number');
            $table->char('fax');
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
        Schema::dropIfExists('contact_information');
    }
}
