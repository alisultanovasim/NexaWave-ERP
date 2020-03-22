<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInCompanyDocsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('in_company_docs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('from_in_our_company');
            $table->unsignedBigInteger('to_in_our_company');
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
        Schema::dropIfExists('in_company_docs');
    }
}
