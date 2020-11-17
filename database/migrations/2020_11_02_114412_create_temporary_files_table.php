<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemporaryFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('temporary_files', function (Blueprint $table) {
            $table->uuid('uuid');
            $table->string('file');
            $table->string('disk_name');
            $table->string('name');
            $table->string('extension');
            $table->string('size');
            $table->enum('size_type', ['KB', 'MB', 'GB']);
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
        Schema::dropIfExists('temporary_files');
    }
}
