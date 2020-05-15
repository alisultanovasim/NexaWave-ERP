<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salaries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('position_id');
            $table->unsignedBigInteger('salary_type_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->float('quantity');
            $table->unsignedBigInteger('currency_id');
            $table->boolean('with_percentage')->default(0);
            $table->char('note')->nullable()->default(null);
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('position_id')->references('id')->on('positions');
            $table->foreign('salary_type_id')->references('id')->on('supplement_salary_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('salaries');
    }
}
