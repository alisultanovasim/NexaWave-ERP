<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDemandDraftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('demand_drafts', function (Blueprint $table) {
            $table->id();
            $table->string('name',55);
            $table->string('description');
            $table->string('attachment');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('company_id');
            $table->tinyInteger('status')->default(\Modules\Storage\Entities\DemandDraft::STATUS_WAIT);
            $table->tinyInteger('return_status')->default(false);
            $table->tinyInteger('is_sent')->default(false);
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
        Schema::dropIfExists('demand_drafts');
    }
}
