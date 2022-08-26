<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStorageDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('storage_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('propose_id');
            $table->unsignedBigInteger('company_id');
            $table->string('barcode',77)->unique();
            $table->unsignedBigInteger('storage_id');
            $table->date('expiration_date');
            $table->integer('amount');
            $table->string('document',77);
            $table->softDeletes();
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
        Schema::dropIfExists('storage_documents');
    }
}
