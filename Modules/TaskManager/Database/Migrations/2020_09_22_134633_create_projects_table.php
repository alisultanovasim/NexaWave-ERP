<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tm_projects', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId("company_id")->constrained("companies");
            $table->unsignedBigInteger("contract_id")->nullable();
            $table->string("name", 255);
            $table->date("start_date");
            $table->date("end_date");
            $table->boolean("is_active");
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tm_projects');
    }
}
