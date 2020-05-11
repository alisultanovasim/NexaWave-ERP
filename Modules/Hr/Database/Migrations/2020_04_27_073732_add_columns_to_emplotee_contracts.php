<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class AddColumnsToEmploteeContracts
 */
class AddColumnsToEmploteeContracts extends Migration
{
    /**
     * Run the migrations.
     * @additions
     * @return void
     */
    public function up()
    {
        Schema::table('employee_contracts', function (Blueprint $table) {
            $table->string('contract_no')->nullable();//muqavile nomresi

            $table->unsignedBigInteger('acceptor_id')->nullable(); //ishe alan


            $table->unsignedBigInteger('duration_type_id')->nullable(); // muqavile muddeti
            $table->foreign('duration_type_id')
                ->references('id')
                ->on('duration_types')
                ->onDelete('cascade');

            $table->text('description')->nullable(); //qeyd
            $table->text('labor_protection_addition')->nullable(); //Verilən mühafizə vasitələri
            $table->text('labor_meal_addition')->nullable(); //Verilən qida məhsulları
            $table->text('labor_sport_addition')->nullable();//Bədən tərbiyəsi və idman üzrə əlavə şərtlər



            $table->unsignedInteger('vacation_main')->default(0);
            $table->unsignedInteger('vacation_work_insurance')->default(0);
            $table->unsignedInteger('vacation_work_envs')->default(0);
            $table->unsignedInteger('vacation_for_child')->default(0);
            $table->unsignedInteger('vacation_collective_contract')->default(0);
            $table->unsignedInteger('vacation_total')->default(0);
            $table->decimal('vacation_social_benefits')->nullable();


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
            $table->dropColumn('contract_no');

            $table->dropColumn('acceptor_id');

            $table->dropColumn('work_place_id');
            $table->dropColumn('work_place_id');

            $table->dropColumn('duration_type_id');
            $table->dropColumn('duration_type_id');

            $table->dropColumn('description');
            $table->dropColumn('labor_protection_addition');
            $table->dropColumn('labor_meal_addition');
            $table->dropColumn('labor_sport_addition');



            $table->dropColumn('main');
            $table->dropColumn('work_insurance');
            $table->dropColumn('work_envs');
            $table->dropColumn('for_child');
            $table->dropColumn('collective_contract');
            $table->dropColumn('total');
            $table->dropColumn('social_benefits');
        });
    }
}
