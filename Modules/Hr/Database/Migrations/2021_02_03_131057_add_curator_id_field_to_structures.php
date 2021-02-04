<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCuratorIdFieldToStructures extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->unsignedBigInteger('curator_id')->nullable()->default(null);
            $table->foreign('curator_id')->references('id')->on('employees');
        });

        Schema::table('sections', function (Blueprint $table) {
            $table->unsignedBigInteger('curator_id')->nullable()->default(null);
            $table->foreign('curator_id')->references('id')->on('employees');
        });

        Schema::table('sectors', function (Blueprint $table) {
            $table->unsignedBigInteger('curator_id')->nullable()->default(null);
            $table->foreign('curator_id')->references('id')->on('employees');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('curator_id');
        });

        Schema::table('sections', function (Blueprint $table) {
            $table->dropColumn('curator_id');
        });

        Schema::table('sectors', function (Blueprint $table) {
            $table->dropColumn('curator_id');
        });
    }
}
