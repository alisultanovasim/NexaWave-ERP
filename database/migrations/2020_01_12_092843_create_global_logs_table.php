<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGlobalLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('global_logs',
            function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('module')->nullable();
                $table->string('url');
                $table->mediumText('description')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->json('fields')->nullable();
                $table->string('method')->nullable();
                $table->unsignedBigInteger('company_id');
                $table->timestamp('created_at')->useCurrent();
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
        Schema::dropIfExists('global_logs');
    }
}
