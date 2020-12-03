<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRoleIdColumnToProjectUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tm_project_users', function (Blueprint $table) {
            $table->unsignedBigInteger("role_id");
            $table->foreign("role_id")->references("id")->on("roles");

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tm_project_users', function (Blueprint $table) {
            $table->dropColumn("role_id");
            $table->dropForeign("tm_project_users_role_id_foreign");
        });
    }
}
