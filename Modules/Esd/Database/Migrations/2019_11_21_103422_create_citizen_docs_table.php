<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCitizenDocsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('citizen_docs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable();
            $table->unsignedInteger('region_id')->nullable();
            $table->unsignedBigInteger('document_id');
            $table->string('address')->nullable();
            $table->foreign("document_id")->references("id")->on("documents")->onDelete("cascade");
            $table->foreign("region_id")->references("id")->on("esd_regions")->onDelete("cascade");


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('citizen_docs');
    }
}
