<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStructureDocsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('structure_docs', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedInteger('sender_company_id')->nullable();
            $table->unsignedInteger('sender_company_role_id')->nullable();
            $table->unsignedInteger('sender_company_user_id')->nullable();


            $table->unsignedBigInteger('document_id');

            $table->foreign("document_id")->references("id")->on("documents")->onDelete("cascade");


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('structure_docs');
    }
}
