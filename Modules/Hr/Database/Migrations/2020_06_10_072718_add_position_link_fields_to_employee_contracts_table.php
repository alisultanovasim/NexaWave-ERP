<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPositionLinkFieldsToEmployeeContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employee_contracts', function (Blueprint $table) {
            $table->unsignedBigInteger('structure_id')->nullable()->default(null)->after('position_id');
            $table->enum('structure_type', [
                'department',
                'section',
                'sector'
            ])
            ->nullable()->default(null)->after('structure_id');
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
            $table->dropColumn('structure_id');
            $table->dropColumn('structure_type');
        });
    }
}
