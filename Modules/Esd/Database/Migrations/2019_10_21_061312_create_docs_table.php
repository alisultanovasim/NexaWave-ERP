<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('docs', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->text("resource")->nullable();

            $table->unsignedBigInteger("document_id");

            $table->unsignedBigInteger("uploader");

            $table->unsignedBigInteger("parent_id")->nullable();

            $table->string('size')->nullable();

            /**
             * "resource":"url or text"
             * "type":1/2
             * "uploader":"user_id"
             */
            $table->json("versions")->nullable();

            /**
             * 1 - file
             * 2 - editor
             */
            $table->unsignedTinyInteger("type");

            $table->foreign("document_id")->references("id")->on("documents")->onDelete("cascade");
//            $table->foreign("parent_id")->references("id")->on("docs")->onDelete("cascade");
//            $table->foreign("uploader")->references("id")->on("workers")->onDelete("cascade");

            $table->timestamp("created_at")->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('docs');
    }
}
