<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDemandAssignmentIdToDemandItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('demand_items', function (Blueprint $table) {
            $table->unsignedBigInteger('demand_assignment_id');
            $table->foreign('demand_assignment_id')
                ->references('id')
                ->on('demand_assignments')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('demand_items', function (Blueprint $table) {
            $table->dropColumn('demand_assignment_id');
        });
    }
}
