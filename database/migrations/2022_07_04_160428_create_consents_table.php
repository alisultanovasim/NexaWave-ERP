<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConsentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('consents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('requester_id');
            $table->timestamp('start_date');
            $table->timestamp('end_date');
            $table->timestamp('work_date');
            $table->unsignedBigInteger('responsible_id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('office_id');
            $table->tinyInteger('status')->default(0);
            $table->string('reason');
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
        Schema::dropIfExists('consents');
    }
}
