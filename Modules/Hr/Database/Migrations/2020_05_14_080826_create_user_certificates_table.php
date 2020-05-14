<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserCertificatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_certificates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->char('name');
            $table->char('speciality');
            $table->string('description')->nullable()->default(null);
            $table->char('training_place');
            $table->timestamp('start_date');
            $table->timestamp('getting_date')->nullable()->default(null);
            $table->timestamp('expire_date')->nullable()->default(null);
            $table->char('number', 50)->nullable()->default(null);
            $table->char('note')->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_certificates');
    }
}
