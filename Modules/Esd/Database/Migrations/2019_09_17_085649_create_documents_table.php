<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('document_no')->nullable();
            $table->string('register_number')->nullable();
            $table->string("theme")->nullable();
            $table->text("description")->nullable();
            $table->unsignedBigInteger("from");
            $table->unsignedBigInteger("company_id");
            $table->unsignedSmallInteger("section_id");
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedSmallInteger('send_type')->nullable();
            $table->unsignedSmallInteger('send_form')->nullable();

                /**
             * In /config/modules.php
             * in document key
             */
            $table->string('folder')->nullable();
            $table->unsignedSmallInteger('page_count')->nullable();
            $table->unsignedSmallInteger('copy_count')->nullable();
            $table->unsignedTinyInteger("status")->default(0);
            $table->unsignedTinyInteger('inner_inspect')->default(0);
            $table->unsignedTinyInteger('send_to_user')->default(0);
            $table->foreign("section_id")->references("id")->on("sections")->onDelete("restrict");
            $table->foreign("parent_id")->references("id")->on("documents")->onDelete("restrict");
            $table->foreign("send_type")->references("id")->on("send_types");
            $table->foreign("send_form")->references("id")->on("send_forms");
//            $table->foreign("from")->references("id")->on("workers")->onDelete("restrict");
            $table->timestamp("expire_time")->nullable();
            $table->timestamp('register_time')->nullable();
            $table->timestamp('document_time')->nullable();
            $table->unsignedBigInteger('company_user')->nullable();//in our company
            $table->timestamps();
            $table->softDeletes();
//            $table->unique('document_no');
            $table->unique('register_number');
        });

    }

    /**
     *
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('documents');
    }
}
