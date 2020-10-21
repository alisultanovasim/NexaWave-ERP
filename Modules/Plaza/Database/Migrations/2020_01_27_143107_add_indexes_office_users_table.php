<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesOfficeUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('office_users', function (Blueprint $table) {
            $table->index(['company_id' ,'user_id', 'office_id' ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('office_users', function (Blueprint $table) {
            $table->dropIndex(['company_id' ,'user_id', 'office_id' ]);
        });
    }
}
