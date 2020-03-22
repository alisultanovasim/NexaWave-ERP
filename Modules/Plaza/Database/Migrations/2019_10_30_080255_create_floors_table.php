<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFloorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('floors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal("common_size" , 10 , 2);
            $table->decimal("sold_size" , 10 , 2)->nullable();
            $table->unsignedBigInteger('company_id');
            $table->unsignedSmallInteger('number');

            $table->string("image")->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['company_id','number']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('floors');
    }
}
