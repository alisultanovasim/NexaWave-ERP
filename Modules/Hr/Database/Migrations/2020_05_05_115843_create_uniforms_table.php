<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUniformsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('uniforms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('uniform_type_id');
            $table->char('size', 150);
            $table->char('node')->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('uniform_type_id')->references('id')->on('uniform_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('uniforms');
    }
}
