<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToOffers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('offers', function (Blueprint $table) {

            $table->smallInteger('room_count')->nullable();
            $table->smallInteger('worker_count')->nullable();
            $table->smallInteger('car_count')->nullable();
            $table->unsignedInteger('specialization_id')->nullable();
            $table->foreign('specialization_id')->references('id')->on('specializations');
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn('room_count');
            $table->dropColumn('worker_count');
            $table->dropColumn('car_count');
            $table->dropIndex('specialization_id');
            $table->dropColumn('specialization_id');
        });
    }
}
