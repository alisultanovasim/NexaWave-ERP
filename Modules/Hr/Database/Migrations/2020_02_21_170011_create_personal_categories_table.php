<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonalCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('personal_categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->char('code', 50);
            $table->string('note', 1000)->nullable();
            $table->bigInteger('position');
            $table->unsignedBigInteger('company_id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('personal_categories');
    }
}
