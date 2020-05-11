<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesToGlobalLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('global_logs', function (Blueprint $table) {
            $table->index(['company_id' , 'module' , 'method']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('global_logs', function (Blueprint $table) {
            $table->dropIndex(['company_id' , 'module' , 'method']);
        });
    }
}
