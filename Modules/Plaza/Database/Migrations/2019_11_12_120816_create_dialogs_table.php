<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDialogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dialogs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('theme');
            $table->unsignedBigInteger('company_id');

            $table->unsignedBigInteger('user_id')->nullable();

            $table->unsignedBigInteger('assigned_user')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->unsignedSmallInteger('status')->default(0);
            //true or false
            $table->unsignedTinyInteger('from_office')->default(0);
            $table->unsignedBigInteger('office_id');
            $table->unsignedSmallInteger('kind_id');
            $table->foreign('kind_id')->references('id')->on('kinds');
            $table->foreign('office_id')->references('id')->on('offices');
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
        Schema::dropIfExists('dialogs');
    }
}
