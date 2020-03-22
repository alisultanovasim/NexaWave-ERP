<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArchivesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archives', function (Blueprint $table) {
            $table->bigIncrements('id');
//            $table->string("theme");
//            $table->json("data"); //derkenar meselesi
//            $table->string("url")->nullable();
//            $table->unsignedTinyInteger("accepted")->default(0);
//            $table->longText("description")->nullable();
//            $table->unsignedBigInteger("acceptor_id")->nullable();
//            $table->unsignedBigInteger("creator_id")->nullable();
//            $table->unsignedBigInteger("company_id")->nullable();
//            $table->unsignedBigInteger("document_id");
            $table->timestamp("created_at")->useCurrent();
//
//            $table->foreign("company_id")->references("id")->on("companies")->onDelete("set null");
//            $table->foreign("creator_id")->references("id")->on("workers")->onDelete("set null");
//            $table->foreign("acceptor_id")->references("id")->on("workers")->onDelete("set null");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('arxivs');
    }
}
