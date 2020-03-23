<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePositionModulePermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('position_module_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('position_id');
            $table->unsignedBigInteger('module_id');
            $table->unsignedBigInteger('permission_id');


            $table->foreign('position_id')->references('id')->on('positions')->onDelete('cascade');
            $table->foreign('module_id')->references('id')->on('modules')->onDelete('cascade');
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('position_module_permissions_talbe');
    }
}
