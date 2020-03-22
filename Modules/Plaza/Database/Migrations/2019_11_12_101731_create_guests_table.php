<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGuestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('office_id');
            $table->unsignedBigInteger('company_id');

            /**
             * 1 - geldi
             * 0 - gelmedi
             * 2 gozleyir
             */
            $table->unsignedBigInteger('status')->default(2);
            $table->timestamp('come_at');
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
        Schema::dropIfExists('guests');
    }
}
