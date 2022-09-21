<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProposesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proposes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('demand_id');
            $table->string('company_name');
            $table->unsignedBigInteger('company_id');
            $table->decimal('price');
            $table->double('amount');
            $table->string('offer_file');
            $table->text('description');
            $table->unsignedBigInteger('employee_id');
            $table->tinyInteger('status')->default(\Modules\Storage\Entities\Propose::STATUS_WAIT);
            $table->tinyInteger('progress_status')->default(1);
            $table->softDeletes();
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
        Schema::dropIfExists('proposes');
    }
}
