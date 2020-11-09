<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AdditionFieldsToEmployeeContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employee_contracts', function (Blueprint $table) {
            $table->date('contract_sing_date')->nullable();
            $table->unsignedBigInteger('company_authorized_employee_id')->nullable();
            $table->unsignedBigInteger('contract_id')->nullable();

            $table->decimal('position_salary_praise_about' )->nullable();// Vəzifə maaşı tərif haqqında
            $table->decimal('addition_package_fee' )->nullable();//  Əlavə tarif haqqı
            $table->decimal('award_amount' )->nullable();// Mükafat
            $table->unsignedTinyInteger('award_period' )->nullable();// Mükafat dovru
            $table->decimal('work_environment_addition' )->nullable();//  Əmək şəraitinə əlavə məbləğ
            $table->decimal('overtime_addition' )->nullable();//  Əmək şəraitinə əlavə məbləğ

            $table->unsignedInteger('incomplete_work_hours')->nullable();
            $table->unsignedTinyInteger('work_days_in_week')->nullable();
            $table->unsignedTinyInteger('work_shift_count')->nullable();
            $table->unsignedTinyInteger('first_shift_start_at')->nullable();
            $table->unsignedTinyInteger('first_shift_end_at')->nullable();
            $table->unsignedTinyInteger('second_shift_start_at')->nullable();
            $table->unsignedTinyInteger('second_shift_end_at')->nullable();
            $table->unsignedTinyInteger('third_shift_start_at')->nullable();
            $table->unsignedTinyInteger('third_shift_end_at')->nullable();


            $table->string('provided_transport')->nullable();
            $table->json('res_days')->nullable();


            $table->unsignedTinyInteger('draft')->default(0);

            $table->decimal('social_amount')->nullable();
            $table->decimal('addition_social_amount')->nullable();


            $table->string('company_share')->nullable();
            $table->string('dividend_amount')->nullable();
            $table->string('user_personal_property')->nullable();

            $table->json('versions')->nullable();
            $table->json('additions')->nullable();

            $table->foreign('company_authorized_employee_id')
                ->references('id')

                ->on('company_authorized_employees')
                ->onDelete('set null');
            $table->foreign('contract_id')
                ->references('id')
                ->on('contracts')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}

//todo add is yeri novu
