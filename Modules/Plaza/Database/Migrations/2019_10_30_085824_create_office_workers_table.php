<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfficeWorkersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('office_workers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string("name");
            $table->text("description")->nullable();
            $table->unsignedInteger('role_id');
            $table->unsignedBigInteger('office_id');

            $table->unsignedBigInteger('card')->nullable();
            $table->unsignedSmallInteger('gender');

            $table->foreign('office_id')->references('id')->on('offices');
            $table->foreign('role_id')->references('id')->on('office_roles');
            $table->foreign('card')->references('id')->on('cards');

            $table->timestamp('created_at')->useCurrent();


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('office_workers');
    }
}
