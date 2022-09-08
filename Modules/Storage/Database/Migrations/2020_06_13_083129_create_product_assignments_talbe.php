<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductAssignmentsTalbe extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_assignments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedTinyInteger('assignment_type');
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('section_id')->nullable();
            $table->unsignedBigInteger('sector_id')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->integer('room')->nullable();
            $table->integer('floor')->nullable();
            $table->float('amount');
            $table->unsignedBigInteger('company_id');
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
        Schema::dropIfExists('product_assignments');
    }
}
