<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToEmployeeContracts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employee_contracts', function (Blueprint $table) {
            $table->unsignedBigInteger('personal_category_id')->nullable(); // professiya cateqoriyasi
            $table->foreign('personal_category_id')
                ->references('id')
                ->on('personal_categories')
                ->onDelete('cascade');

            $table->unsignedBigInteger('specialization_degree_id')->nullable(); // ixtisas derecesi
            $table->foreign('specialization_degree_id')
                ->references('id')
                ->on('specialization_degrees')
                ->onDelete('cascade');


            $table->unsignedBigInteger('work_environment_id')->nullable(); //is yeri
            $table->foreign('work_environment_id')
                ->references('id')
                ->on('work_environments')
                ->onDelete('cascade');

            $table->float('state_value')->nullable();//stat deyeri

            $table->date('intern_start_date')->nullable();
            $table->date('intern_end_date')->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();

            $table->foreign('currency_id')
                ->references('id')
                ->on('currency')
                ->onDelete('cascade');

            $table->unsignedBigInteger('contract_type_id')->nullable();
            $table->foreign('contract_type_id')
                ->references('id')
                ->on('contract_types')
                ->onDelete('cascade');

            $table->date('work_start_date')->nullable();
            $table->date('work_end_date')->nullable();

            $table->time('work_time_start_at')->nullable();
            $table->time('work_time_end_at')->nullable();
            $table->time('break_time_start')->nullable();
            $table->time('break_time_end')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_contracts', function (Blueprint $table) {

        });
    }
}
