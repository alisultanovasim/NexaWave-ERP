<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfessionLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('profession_links', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger("organization_link_id");
            $table->unsignedBigInteger("profession_id");
            $table->unsignedBigInteger("education_level_id");
            $table->double("profession_salary");
            $table->integer("vacancy_count");
            $table->integer("generation")->nullable();
            $table->unsignedBigInteger("index");
            $table->unsignedBigInteger("company_id")->nullable();
            $table->timestamps();

            $table->softDeletes();
            $table->foreign("organization_link_id")->references("id")->on("organization_links")
                ->onDelete("cascade");
            $table->foreign("profession_id")->references("id")->on("professions")
                ->onDelete("cascade");
            $table->foreign("education_level_id")->references("id")->on("education_levels")
                ->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('profession_links');
    }
}
