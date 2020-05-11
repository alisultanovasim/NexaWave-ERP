<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOutcomesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('outcomes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
//            $table->unsignedBigInteger('consumer_id')->nullable();
//            $table->foreign('consumer_id')->references('id')->on('consumers')->onDelete('cascade');
            $table->float('amount');
            $table->timestamp('out_at');
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('company_id');

            $table->unsignedBigInteger('user_id')->nullable();

            $table->unsignedBigInteger('storage_id')->nullable();
            $table->foreign('storage_id')->references('id')->on('storages')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('outcomes');
    }
}
