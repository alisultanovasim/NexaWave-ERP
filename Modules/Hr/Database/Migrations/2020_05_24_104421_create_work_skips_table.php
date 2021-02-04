<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkSkipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_skips', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('employee_id');
            $table->integer('reason_type');
            $table->char('document_number')->nullable()->default(null);
            $table->integer('day');
            $table->date('date_of_presentation')->nullable()->default(null);
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_confirmed')->default(0);
            $table->unsignedBigInteger('confirmed_employee_id')->nullable()->default(null);
            $table->date('work_start_date');
            $table->char('note')->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('employee_id')->references('id')->on('employees');
            $table->foreign('confirmed_employee_id')->references('id')->on('employees');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('work_skips');
    }
}
