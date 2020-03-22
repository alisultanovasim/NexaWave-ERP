<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepartmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('short_name');
            $table->char('code', 50);
            $table->unsignedBigInteger('country_id');
            $table->unsignedBigInteger('city_id');
            $table->unsignedBigInteger('region_id')->nullable();
            $table->boolean('is_parent_department')->default(false);
            $table->unsignedBigInteger('department_id')->nullable();
            $table->boolean('is_closed')->default(false);
            $table->date('closing_date')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('fax')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('email')->nullable();
            $table->string('web_site')->nullable();
            $table->bigInteger('position');
            $table->unsignedBigInteger('company_id');
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

            $table->foreign('department_id')
                ->references('id')
                ->on('departments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('departments');
    }
}
