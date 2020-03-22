<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupplementSalaryTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supplement_salary_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->char('code', 50)->nullable();
            $table->bigInteger('position');
            $table->unsignedBigInteger('company_id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('supplement_salary_types');
    }
}
