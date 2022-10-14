<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArchiveDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archive_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_id');
            $table->tinyInteger('document_type');
            $table->unsignedBigInteger('from_id');
            $table->unsignedBigInteger('role_id');
            $table->string('reason',77);
            $table->tinyInteger('status');
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
        Schema::dropIfExists('archive_documents');
    }
}
