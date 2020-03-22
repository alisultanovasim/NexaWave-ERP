<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfficesContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offices_contacts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('contact');
            $table->string('name')->nullable();
            $table->unsignedTinyInteger('type');
            $table->unsignedBigInteger('office_id');
            $table->foreign('office_id')->references('id')->on('offices')->onDelete('cascade');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('offices_contacts');
    }
}
