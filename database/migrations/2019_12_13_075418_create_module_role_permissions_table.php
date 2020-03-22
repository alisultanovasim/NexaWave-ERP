<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModuleRolePermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('module_role_permissions', function (Blueprint $table) {
            $table->uuid('id')->primary()->unique();
            $table->unsignedBigInteger("role_id");
            $table->unsignedBigInteger("permission_id");
            $table->unsignedBigInteger('module_id')->nullable();
            $table->timestamps();
            $table->foreign("role_id")->references("id")->on("roles")->onDelete("cascade");
            $table->foreign("permission_id")->references("id")->on("permissions")->onDelete("cascade");
            $table->foreign("module_id")->references("id")->on("modules")->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('module_role_permissions');
    }
}
