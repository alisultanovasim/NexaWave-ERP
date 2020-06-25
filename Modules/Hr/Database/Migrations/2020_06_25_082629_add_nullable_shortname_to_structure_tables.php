<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNullableShortnameToStructureTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sectors', function (Blueprint $table) {
            $table->string('short_name')->nullable()->change();
        });
        Schema::table('departments', function (Blueprint $table) {
            $table->string('short_name')->nullable()->change();
        });
        Schema::table('sections', function (Blueprint $table) {
            $table->string('short_name')->nullable()->change();
        });
        Schema::table('countries', function (Blueprint $table) {
            $table->string('short_name')->nullable()->change();
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sectors', function (Blueprint $table) {
            $table->string('short_name')->change();
        });
        Schema::table('departments', function (Blueprint $table) {
            $table->string('short_name')->change();
        });
        Schema::table('sections', function (Blueprint $table) {
            $table->string('short_name')->change();
        });
        Schema::table('countries', function (Blueprint $table) {
            $table->string('short_name')->change();
        });
    }
}
