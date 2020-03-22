<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNulableToInCompanyDocs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('in_company_docs', function (Blueprint $table) {
            $table->unsignedBigInteger('from_in_our_company')->nullable()->change();
            $table->unsignedBigInteger('to_in_our_company')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('in_company_docs', function (Blueprint $table) {
            $table->unsignedBigInteger('from_in_our_company')->change();
            $table->unsignedBigInteger('to_in_our_company')->change();
        });
    }
}
